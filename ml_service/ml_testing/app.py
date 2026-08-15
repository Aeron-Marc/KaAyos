"""
KaAyos ML Microservice — Standalone Test Server
=================================================
Lightweight server that serves the test dashboard and provides mock API responses.
Run:  python ml_testing/app.py
Or:   python -m uvicorn ml_testing.app:app --host 127.0.0.1 --port 8000 --reload
"""

import math
import random
import numpy as np
from pathlib import Path
from typing import List

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
from fastapi.responses import FileResponse
from pydantic import BaseModel, Field

app = FastAPI(
    title="KaAyos ML — Test Server",
    description="Standalone test server for the KaAyos ML test dashboard",
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

HERE = Path(__file__).resolve().parent
INDEX_HTML = HERE / "index.html"

if INDEX_HTML.exists():
    app.mount("/static", StaticFiles(directory=str(HERE)), name="static")

    @app.get("/")
    def root():
        return FileResponse(str(INDEX_HTML))


# ---------------------------------------------------------------------------
# Schemas
# ---------------------------------------------------------------------------

class WorkerGeo(BaseModel):
    worker_id: int
    latitude: float = Field(..., ge=-90, le=90)
    longitude: float = Field(..., ge=-180, le=180)


class ClusterRequest(BaseModel):
    workers: List[WorkerGeo]
    eps_km: float = Field(default=2.0, ge=0.1, le=100)
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
    model_accuracy: float = 0.74
    model_trained_on: str = "demo"


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
    records: List[RetrainRecord] = None
    dataset_path: str = None


class RetrainResponse(BaseModel):
    status: str
    accuracy: float
    precision: float
    recall: float
    f1_score: float
    samples_trained: int
    feature_importances: dict


# ---------------------------------------------------------------------------
# Mock ML logic
# ---------------------------------------------------------------------------

CATEGORY_WEIGHTS = {
    "Plumbing":   {"bias": 0.10, "dist_weight": -0.08, "rating_weight": 0.18, "comp_weight": 0.006, "jobs_weight": 0.002, "new_penalty": -0.15},
    "Cleaning":   {"bias": 0.15, "dist_weight": -0.10, "rating_weight": 0.15, "comp_weight": 0.005, "jobs_weight": 0.003, "new_penalty": -0.10},
    "Electrical": {"bias": 0.08, "dist_weight": -0.07, "rating_weight": 0.20, "comp_weight": 0.007, "jobs_weight": 0.001, "new_penalty": -0.20},
    "AC Repair":  {"bias": 0.05, "dist_weight": -0.09, "rating_weight": 0.16, "comp_weight": 0.008, "jobs_weight": 0.002, "new_penalty": -0.12},
}


def _mock_predict(w: WorkerMatch) -> float:
    wg = CATEGORY_WEIGHTS.get(w.service_category, CATEGORY_WEIGHTS["Plumbing"])
    raw = (
        wg["bias"]
        + wg["dist_weight"] * (w.distance_km / 20.0)
        + wg["rating_weight"] * (w.worker_avg_rating / 5.0)
        + wg["comp_weight"] * (w.worker_completion_rate / 100.0)
        + wg["jobs_weight"] * min(w.jobs_completed_in_category / 200.0, 1.0)
        + (wg["new_penalty"] if w.is_new_worker else 0.0)
    )
    prob = 1.0 / (1.0 + math.exp(-raw * 3.0))
    prob += random.uniform(-0.04, 0.04)
    return max(0.01, min(0.99, prob))


# ---------------------------------------------------------------------------
# Endpoints
# ---------------------------------------------------------------------------

@app.get("/health")
def health():
    return {"status": "ok", "model_loaded": True, "model_accuracy": 0.74}


@app.post("/cluster", response_model=ClusterResponse)
def cluster_workers(req: ClusterRequest):
    if not req.workers:
        return ClusterResponse(clusters=[], total_clusters=0, noise_count=0, eps_km_used=req.eps_km)
    if len(req.workers) == 1:
        return ClusterResponse(
            clusters=[ClusterResult(worker_id=req.workers[0].worker_id, cluster_id=-1)],
            total_clusters=0, noise_count=1, eps_km_used=req.eps_km,
        )

    coords = np.radians([[w.latitude, w.longitude] for w in req.workers])
    eps_rad = req.eps_km / 6371.0

    n = len(req.workers)
    dists = np.zeros((n, n))
    for i in range(n):
        for j in range(n):
            dlat = coords[j][0] - coords[i][0]
            dlon = coords[j][1] - coords[i][1]
            a = math.sin(dlat / 2) ** 2 + math.cos(coords[i][0]) * math.cos(coords[j][0]) * math.sin(dlon / 2) ** 2
            dists[i][j] = 2 * 6371 * math.atan2(math.sqrt(a), math.sqrt(1 - a))

    labels = [-1] * n
    next_label = 0
    for i in range(n):
        if labels[i] != -1:
            continue
        neighbors = [j for j in range(n) if dists[i][j] <= req.eps_km]
        if len(neighbors) < req.min_samples:
            continue
        labels[i] = next_label
        for j in neighbors:
            if labels[j] == -1:
                labels[j] = next_label
        next_label += 1

    clusters = [ClusterResult(worker_id=w.worker_id, cluster_id=int(labels[i])) for i, w in enumerate(req.workers)]
    unique = set(l for l in labels if l != -1)
    return ClusterResponse(
        clusters=clusters, total_clusters=len(unique),
        noise_count=sum(1 for l in labels if l == -1), eps_km_used=req.eps_km,
    )


@app.post("/predict", response_model=PredictResponse)
def predict_matches(req: PredictRequest):
    rankings = []
    for w in req.workers:
        prob = _mock_predict(w)
        rankings.append(RankedWorker(worker_id=w.worker_id, probability=round(prob, 4)))
    rankings.sort(key=lambda r: r.probability, reverse=True)
    return PredictResponse(rankings=rankings)


@app.post("/retrain", response_model=RetrainResponse)
def retrain_model(req: RetrainRequest):
    samples = 0
    if req.records:
        samples = len(req.records)
    elif req.dataset_path:
        samples = 500
    else:
        samples = 500

    return RetrainResponse(
        status="success",
        accuracy=round(random.uniform(0.72, 0.78), 4),
        precision=round(random.uniform(0.70, 0.76), 4),
        recall=round(random.uniform(0.70, 0.76), 4),
        f1_score=round(random.uniform(0.70, 0.76), 4),
        samples_trained=int(samples * 0.8),
        feature_importances={
            "distance_km": round(random.uniform(0.35, 0.45), 4),
            "worker_avg_rating": round(random.uniform(0.15, 0.22), 4),
            "worker_completion_rate": round(random.uniform(0.12, 0.18), 4),
            "jobs_completed_in_category": round(random.uniform(0.10, 0.16), 4),
            "service_category_encoded": round(random.uniform(0.05, 0.08), 4),
            "is_new_worker": round(random.uniform(0.01, 0.03), 4),
        },
    )


if __name__ == "__main__":
    import uvicorn
    print("KaAyos ML Test Server running at http://127.0.0.1:8000")
    uvicorn.run("app:app", host="127.0.0.1", port=8000, reload=True)
