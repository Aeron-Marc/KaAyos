@echo off
setlocal
cd /d "%~dp0"

rem Shared secret with the Laravel app (kaayos/.env ML_API_KEY) for /cluster, /predict, /retrain
if "%ML_API_KEY%"=="" set "ML_API_KEY=ba469543eef62dbd16d423f15d5c636eb6ce862af4a220b8"

rem Create the virtual environment on first run
if not exist ".venv\Scripts\python.exe" (
    echo Creating virtual environment...
    python -m venv .venv
)

echo Installing dependencies...
".venv\Scripts\python.exe" -m pip install -r requirements.txt

echo.
echo Starting KaAyos ML Microservice on http://127.0.0.1:8001
echo API Docs available at http://127.0.0.1:8001/docs
echo.
".venv\Scripts\python.exe" -m uvicorn main:app --host 127.0.0.1 --port 8001 --reload
pause
