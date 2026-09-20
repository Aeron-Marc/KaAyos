"""
KaAyos ML Microservice — Test Suite
=====================================
Tests all endpoints without needing a running server (uses FastAPI TestClient).
Run with:  python test_ml.py
"""

import sys
import os
import json
from pathlib import Path
from typing import Any

os.environ.setdefault("ML_API_KEY", "test-key-12345")

sys.path.insert(0, str(Path(__file__).resolve().parent))

from main import app, _model, _label_encoder, _model_metadata, _train_model_from_df
from fastapi.testclient import TestClient

if _model is None:
    DATASET_PATH = Path(__file__).resolve().parent.parent / "kaayos" / "storage" / "app" / "data" / "kaayos_dataset.csv"
    if DATASET_PATH.exists():
        import pandas as pd
        df = pd.read_csv(DATASET_PATH)
        _train_model_from_df(df)

client = TestClient(app, headers={"X-API-Key": os.environ["ML_API_KEY"]})

PASS = 0
FAIL = 0


def section(title: str) -> None:
    sep = "=" * 60
    print(f"\n{sep}")
    print(f"  {title}")
    print(sep)


def check(name: str, got: Any, expected: Any, detail: str = "") -> None:
    global PASS, FAIL
    ok = got == expected
    status = "PASS" if ok else "FAIL"
    if ok:
        PASS += 1
    else:
        FAIL += 1
    sym = "[OK]" if ok else "[FAIL]"
    extra = f"  ({detail})" if detail else ""
    print(f"  {sym} {name}: expected={expected!r}, got={got!r}{extra}")


def check_near(name: str, got: float, expected: float, tol: float = 1e-3) -> None:
    global PASS, FAIL
    ok = abs(got - expected) <= tol
    status = "PASS" if ok else "FAIL"
    if ok:
        PASS += 1
    else:
        FAIL += 1
    sym = "[OK]" if ok else "[FAIL]"
    print(f"  {sym} {name}: expected~{expected:.4f}, got={got:.4f}")


def check_gt(name: str, got: float, bound: float) -> None:
    global PASS, FAIL
    ok = got > bound
    if ok:
        PASS += 1
    else:
        FAIL += 1
    sym = "[OK]" if ok else "[FAIL]"
    print(f"  {sym} {name}: {got} > {bound}")


def check_contains(name: str, obj: Any, key: str) -> None:
    global PASS, FAIL
    ok = key in obj
    if ok:
        PASS += 1
    else:
        FAIL += 1
    sym = "[OK]" if ok else "[FAIL]"
    print(f"  {sym} {name}: key '{key}' in response = {ok}")


# ---------------------------------------------------------------------------
#  1. HEALTH
# ---------------------------------------------------------------------------
section("1. GET /health")

r = client.get("/health")
check("status code", r.status_code, 200)
data = r.json()
check_contains("has 'status'", data, "status")
check("status value", data["status"], "ok")
check_contains("has 'model_loaded'", data, "model_loaded")
check("model_loaded is bool", isinstance(data["model_loaded"], bool), True)

# ---------------------------------------------------------------------------
#  2. PREDICT — Normal case, verify ranking order
# ---------------------------------------------------------------------------
section("2. POST /predict — 4 workers, verify ranking")

payload = {
    "workers": [
        {
            "worker_id": 1000, "service_category": "Plumbing",
            "distance_km": 3.81, "worker_avg_rating": 4.8,
            "worker_completion_rate": 89, "jobs_completed_in_category": 139,
            "is_new_worker": 0,
        },
        {
            "worker_id": 1001, "service_category": "Plumbing",
            "distance_km": 10.78, "worker_avg_rating": 1.0,
            "worker_completion_rate": 97, "jobs_completed_in_category": 71,
            "is_new_worker": 0,
        },
        {
            "worker_id": 1005, "service_category": "Cleaning",
            "distance_km": 1.2, "worker_avg_rating": 4.2,
            "worker_completion_rate": 95, "jobs_completed_in_category": 45,
            "is_new_worker": 0,
        },
        {
            "worker_id": 1006, "service_category": "AC Repair",
            "distance_km": 5.5, "worker_avg_rating": 3.5,
            "worker_completion_rate": 80, "jobs_completed_in_category": 12,
            "is_new_worker": 1,
        },
    ]
}
r = client.post("/predict", json=payload)
check("status code", r.status_code, 200)
data = r.json()
check_contains("has 'rankings'", data, "rankings")
check("4 rankings returned", len(data["rankings"]), 4)
check_contains("model_accuracy present", data, "model_accuracy")
check("accuracy is float > 0", data["model_accuracy"] > 0, True)

probs = [item["probability"] for item in data["rankings"]]
check("sorted descending", all(probs[i] >= probs[i + 1] for i in range(len(probs) - 1)), True)
ids_ordered = [item["worker_id"] for item in data["rankings"]]
check("1000 ranked first (highest rating, close)", ids_ordered[0], 1000)
check("1001 ranked last (lowest rating, farthest)", ids_ordered[-1], 1001)

# ---------------------------------------------------------------------------
#  3. PREDICT — Empty workers
# ---------------------------------------------------------------------------
section("3. POST /predict — empty list")

r = client.post("/predict", json={"workers": []})
check("status code", r.status_code, 200)
data = r.json()
check("empty rankings", data["rankings"], [])

# ---------------------------------------------------------------------------
#  4. PREDICT — Unknown category
# ---------------------------------------------------------------------------
section("4. POST /predict — unknown service_category")

payload = {
    "workers": [
        {
            "worker_id": 999, "service_category": "UnknownCategoryXYZ",
            "distance_km": 1.0, "worker_avg_rating": 3.0,
            "worker_completion_rate": 50, "jobs_completed_in_category": 5,
            "is_new_worker": 0,
        }
    ]
}
r = client.post("/predict", json=payload)
check("status code", r.status_code, 200)
data = r.json()
check("1 ranking returned", len(data["rankings"]), 1)
check("worker_id preserved", data["rankings"][0]["worker_id"], 999)
check("probability is float 0-1", 0 <= data["rankings"][0]["probability"] <= 1, True)

# ---------------------------------------------------------------------------
#  5. PREDICT — Missing required fields
# ---------------------------------------------------------------------------
section("5. POST /predict — missing fields => 422")

payloads_bad = [
    {"workers": [{"worker_id": 1}]},
    {"workers": [{"worker_id": 1, "service_category": "Plumbing"}]},
    {"workers": [{"worker_id": 1, "service_category": "Plumbing", "distance_km": 1.0}]},
    {},  # missing workers key entirely
]
for i, p in enumerate(payloads_bad):
    r = client.post("/predict", json=p)
    ok = r.status_code == 422
    if ok:
        PASS += 1
    else:
        FAIL += 1
    sym = "[OK]" if ok else "[FAIL]"
    print(f"  {sym} missing-fields payload #{i + 1}: status={r.status_code} (expected 422)")

# ---------------------------------------------------------------------------
#  6. RETRAIN — From default dataset
# ---------------------------------------------------------------------------
section("6. POST /retrain — from default dataset")

r = client.post("/retrain", json={"dataset_path": "../kaayos/storage/app/data/kaayos_dataset.csv"})
check("status code", r.status_code, 200)
data = r.json()
check("status = success", data["status"], "success")
check_gt("accuracy > 0.5", data["accuracy"], 0.5)
check_gt("samples_trained > 0", data["samples_trained"], 0)
check_contains("feature_importances present", data, "feature_importances")
feat = data["feature_importances"]
expected_feats = {"distance_km", "worker_avg_rating", "worker_completion_rate",
                  "jobs_completed_in_category", "is_new_worker", "service_category_encoded"}
check("all 6 features in importances", set(feat.keys()), expected_feats)
check_gt("distance_km is top feature", feat["distance_km"], 0.3)

# ---------------------------------------------------------------------------
#  7. RETRAIN — From inline records
# ---------------------------------------------------------------------------
section("7. POST /retrain — from inline records")

records = [
    {"worker_id": 1, "service_category": "Plumbing", "distance_km": 1.0,
     "worker_avg_rating": 4.5, "worker_completion_rate": 95,
     "jobs_completed_in_category": 50, "is_new_worker": 0, "match_success": 1},
    {"worker_id": 2, "service_category": "Plumbing", "distance_km": 10.0,
     "worker_avg_rating": 1.0, "worker_completion_rate": 50,
     "jobs_completed_in_category": 2, "is_new_worker": 1, "match_success": 0},
    {"worker_id": 3, "service_category": "Cleaning", "distance_km": 0.5,
     "worker_avg_rating": 5.0, "worker_completion_rate": 100,
     "jobs_completed_in_category": 200, "is_new_worker": 0, "match_success": 1},
    {"worker_id": 4, "service_category": "Cleaning", "distance_km": 8.0,
     "worker_avg_rating": 2.0, "worker_completion_rate": 30,
     "jobs_completed_in_category": 1, "is_new_worker": 0, "match_success": 0},
    {"worker_id": 5, "service_category": "Electrical", "distance_km": 2.0,
     "worker_avg_rating": 4.0, "worker_completion_rate": 85,
     "jobs_completed_in_category": 30, "is_new_worker": 0, "match_success": 1},
    {"worker_id": 6, "service_category": "Electrical", "distance_km": 15.0,
     "worker_avg_rating": 1.5, "worker_completion_rate": 40,
     "jobs_completed_in_category": 0, "is_new_worker": 1, "match_success": 0},
]
r = client.post("/retrain", json={"records": records})
check("status code", r.status_code, 200)
data = r.json()
check("status = success", data["status"], "success")
check("samples_trained (80% of 6)", data["samples_trained"], 4)

# ---------------------------------------------------------------------------
#  8. RETRAIN — Missing everything => falls back to CSV
# ---------------------------------------------------------------------------
section("8. POST /retrain — no data falls back to default CSV")

r = client.post("/retrain", json={})
check("returns 200 (uses default CSV)", r.status_code, 200)
data = r.json()
check("status = success", data["status"], "success")
check_gt("accuracy > 0.5", data["accuracy"], 0.5)

# ---------------------------------------------------------------------------
#  9. RETRAIN — Dataset has wrong columns
# ---------------------------------------------------------------------------
section("9. POST /retrain — bad columns => 422 (Pydantic validation)")

r = client.post("/retrain", json={"records": [{"worker_id": 1, "foo": "bar"}]})
check("returns 422", r.status_code, 422)

# ---------------------------------------------------------------------------
# SUMMARY
# ---------------------------------------------------------------------------
section("SUMMARY")
total = PASS + FAIL
print(f"  Passed: {PASS}/{total}")
print(f"  Failed: {FAIL}/{total}")
if FAIL == 0:
    print("  ALL TESTS PASSED")
else:
    print(f"  {FAIL} TEST(S) FAILED")

sys.exit(0 if FAIL == 0 else 1)
