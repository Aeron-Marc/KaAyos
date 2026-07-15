import os
import sys
import numpy as np
import pandas as pd
from pathlib import Path
from typing import List, Optional, Literal

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

from sklearn.cluster import DBSCAN
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score
import joblib

app = FastAPI(
    title="KaAyos ML Microservice",
    description="Geospatial clustering & AI worker matching for KaAyos marketplace",
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

BASE_DIR = Path(__file__).resolve().parent
MODELS_DIR = BASE_DIR / "models"
DATASET_PATH = BASE_DIR.parent / "database" / "data" / "kaayos_dataset.csv"
MODEL_PATH = MODELS_DIR / "matching_model.pkl"
LABEL_ENCODER_PATH = MODELS_DIR / "category_encoder.pkl"
MODEL_METADATA_PATH = MODELS_DIR / "model_metadata.pkl"

CATEGORY_COLUMNS = [
    "distance_km", "worker_avg_rating", "worker_completion_rate",
    "jobs_completed_in_category", "is_new_worker",
]

FEATURE_COLUMNS = CATEGORY_COLUMNS + ["service_category_encoded"]

# ---------------------------------------------------------------------------
# Pydantic schemas
# ---------------------------------------------------------------------------

class WorkerGeo(BaseModel):
    worker_id: int
    latitude: float = Field(..., ge=-90, le=90)
    longitude: float = Field(..., ge=-180, le=180)


class ClusterRequest(BaseModel):
    workers: List[WorkerGeo]
    eps_km: float = Field(default=2.0, ge=0.1, le=100, description="DBSCAN epsilon in kilometers")
    min_samples: int = Field(default=2, ge=1, le=100)


class ClusterResult(BaseModel):
    worker_id: int
    cluster_id: int


class ClusterResponse(BaseModel):
    clusters: List[ClusterResult]
    total_clusters: int
    noise_count: int
    eps_km_used: float


class WorkerMatch(BaseModel):
    worker_id: int
    service_category: str
    distance_km: float = Field(..., ge=0)
    worker_avg_rating: float = Field(..., ge=0, le=5)
    worker_completion_rate: float = Field(..., ge=0, le=100)
    jobs_completed_in_category: int = Field(..., ge=0)
    is_new_worker: int = Field(..., ge=0, le=1)


class PredictRequest(BaseModel):
    workers: List[WorkerMatch]


class RankedWorker(BaseModel):
    worker_id: int
    probability: float


class PredictResponse(BaseModel):
    rankings: List[RankedWorker]
    model_accuracy: Optional[float] = None
    model_trained_on: Optional[str] = None


class RetrainRecord(BaseModel):
    worker_id: int
    service_category: str
    distance_km: float
    worker_avg_rating: float
    worker_completion_rate: float
    jobs_completed_in_category: int
    is_new_worker: int
    match_success: int


class RetrainRequest(BaseModel):
    records: Optional[List[RetrainRecord]] = None
    dataset_path: Optional[str] = None


class RetrainResponse(BaseModel):
    status: str
    accuracy: float
    precision: float
    recall: float
    f1_score: float
    samples_trained: int
    feature_importances: dict


# ---------------------------------------------------------------------------
# Model utilities
# ---------------------------------------------------------------------------

_model: Optional[RandomForestClassifier] = None
_label_encoder: Optional[LabelEncoder] = None
_model_metadata: Optional[dict] = None


def _train_model_from_df(df: pd.DataFrame) -> dict:
    df = df.dropna(subset=CATEGORY_COLUMNS + ["service_category", "match_success"])

    le = LabelEncoder()
    df["service_category_encoded"] = le.fit_transform(df["service_category"])

    X = df[FEATURE_COLUMNS]
    y = df["match_success"]

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )

    clf = RandomForestClassifier(
        n_estimators=200,
        max_depth=12,
        min_samples_split=5,
        min_samples_leaf=2,
        class_weight="balanced",
        random_state=42,
        n_jobs=-1,
    )
    clf.fit(X_train, y_train)

    y_pred = clf.predict(X_test)
    metadata = {
        "accuracy": float(accuracy_score(y_test, y_pred)),
        "precision": float(precision_score(y_test, y_pred, zero_division=0)),
        "recall": float(recall_score(y_test, y_pred, zero_division=0)),
        "f1_score": float(f1_score(y_test, y_pred, zero_division=0)),
        "samples_trained": len(X_train),
        "samples_tested": len(X_test),
        "feature_importances": dict(zip(FEATURE_COLUMNS, clf.feature_importances_.tolist())),
        "categories": le.classes_.tolist(),
    }

    joblib.dump(clf, MODEL_PATH)
    joblib.dump(le, LABEL_ENCODER_PATH)
    joblib.dump(metadata, MODEL_METADATA_PATH)

    global _model, _label_encoder, _model_metadata
    _model = clf
    _label_encoder = le
    _model_metadata = metadata

    return metadata


def _load_or_train_model() -> dict:
    if MODEL_PATH.exists():
        clf = joblib.load(MODEL_PATH)
        le = joblib.load(LABEL_ENCODER_PATH) if LABEL_ENCODER_PATH.exists() else LabelEncoder()
        metadata = joblib.load(MODEL_METADATA_PATH) if MODEL_METADATA_PATH.exists() else {}
        global _model, _label_encoder, _model_metadata
        _model = clf
        _label_encoder = le
        _model_metadata = metadata
        return metadata

    if DATASET_PATH.exists():
        df = pd.read_csv(DATASET_PATH)
        return _train_model_from_df(df)

    return {}


@app.on_event("startup")
def startup():
    MODELS_DIR.mkdir(parents=True, exist_ok=True)
    meta = _load_or_train_model()
    if meta:
        print(f"Model loaded. Accuracy: {meta.get('accuracy', 'N/A')}")
    else:
        print("WARNING: No dataset found. Train a model via POST /retrain before using /predict.")


def _predict_worker_proba(workers: List[WorkerMatch]) -> List[RankedWorker]:
    if _model is None or _label_encoder is None:
        raise HTTPException(status_code=503, detail="Model not trained yet. Call POST /retrain first.")

    records = []
    for w in workers:
        try:
            cat_enc = _label_encoder.transform([w.service_category])[0]
        except ValueError:
            cat_enc = -1
        records.append({
            "distance_km": w.distance_km,
            "worker_avg_rating": w.worker_avg_rating,
            "worker_completion_rate": w.worker_completion_rate,
            "jobs_completed_in_category": w.jobs_completed_in_category,
            "is_new_worker": w.is_new_worker,
            "service_category_encoded": cat_enc,
        })

    X = pd.DataFrame(records)[FEATURE_COLUMNS]
    probas = _model.predict_proba(X)

    results = []
    for i, w in enumerate(workers):
        prob_success = float(probas[i][1]) if probas.shape[1] > 1 else float(probas[i][0])
        results.append(RankedWorker(worker_id=w.worker_id, probability=round(prob_success, 4)))

    results.sort(key=lambda r: r.probability, reverse=True)
    return results


# ---------------------------------------------------------------------------
# Endpoints
# ---------------------------------------------------------------------------

@app.get("/health")
def health():
    return {
        "status": "ok",
        "model_loaded": _model is not None,
        "model_accuracy": _model_metadata.get("accuracy") if _model_metadata else None,
    }


@app.post("/cluster", response_model=ClusterResponse)
def cluster_workers(req: ClusterRequest):
    if len(req.workers) < 2:
        return ClusterResponse(
            clusters=[ClusterResult(worker_id=w.worker_id, cluster_id=-1) for w in req.workers],
            total_clusters=0,
            noise_count=len(req.workers),
            eps_km_used=req.eps_km,
        )

    coords = np.radians([[w.latitude, w.longitude] for w in req.workers])
    eps_rad = req.eps_km / 6371.0

    db = DBSCAN(eps=eps_rad, min_samples=req.min_samples, metric="haversine")
    labels = db.fit_predict(coords)

    clusters = []
    unique_labels = set()
    for w, label in zip(req.workers, labels):
        clusters.append(ClusterResult(worker_id=w.worker_id, cluster_id=int(label)))
        if label != -1:
            unique_labels.add(label)

    noise_count = sum(1 for l in labels if l == -1)

    return ClusterResponse(
        clusters=clusters,
        total_clusters=len(unique_labels),
        noise_count=noise_count,
        eps_km_used=req.eps_km,
    )


@app.post("/predict", response_model=PredictResponse)
def predict_matches(req: PredictRequest):
    if not req.workers:
        return PredictResponse(rankings=[], model_accuracy=None, model_trained_on=None)

    rankings = _predict_worker_proba(req.workers)
    return PredictResponse(
        rankings=rankings,
        model_accuracy=_model_metadata.get("accuracy") if _model_metadata else None,
        model_trained_on=str(DATASET_PATH) if DATASET_PATH.exists() else None,
    )


@app.post("/retrain", response_model=RetrainResponse)
def retrain_model(req: RetrainRequest):
    if req.records:
        rows = [r.model_dump() for r in req.records]
        df = pd.DataFrame(rows)
    elif req.dataset_path:
        path = Path(req.dataset_path)
        if not path.exists():
            raise HTTPException(status_code=404, detail=f"Dataset not found: {req.dataset_path}")
        df = pd.read_csv(path)
    elif DATASET_PATH.exists():
        df = pd.read_csv(DATASET_PATH)
    else:
        raise HTTPException(
            status_code=400,
            detail="No data provided. Send 'records', 'dataset_path', or ensure kaayos_dataset.csv is in database/data/.",
        )

    required = set(CATEGORY_COLUMNS + ["service_category", "match_success"])
    missing = required - set(df.columns)
    if missing:
        raise HTTPException(
            status_code=400,
            detail=f"Missing columns in dataset: {missing}. Required: {required}",
        )

    metadata = _train_model_from_df(df)

    return RetrainResponse(
        status="success",
        accuracy=metadata["accuracy"],
        precision=metadata["precision"],
        recall=metadata["recall"],
        f1_score=metadata["f1_score"],
        samples_trained=metadata["samples_trained"],
        feature_importances=metadata["feature_importances"],
    )


if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="127.0.0.1", port=8000, reload=True)
