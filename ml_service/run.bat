@echo off
cd /d "%~dp0"
echo Installing dependencies...
pip install -r requirements.txt
echo Starting KaAyos ML Microservice on http://127.0.0.1:8000
echo API Docs available at http://127.0.0.1:8000/docs
uvicorn main:app --host 127.0.0.1 --port 8000 --reload
pause
