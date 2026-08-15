@echo off
title KaAyos ML Test Dashboard
cd /d "%~dp0"
echo Installing / updating dependencies...
pip install fastapi uvicorn pydantic -q
echo.
echo Starting KaAyos ML Test Dashboard...
echo.
echo   Dashboard: http://127.0.0.1:8000
echo.
start http://127.0.0.1:8000
python -m uvicorn ml_testing.app:app --host 127.0.0.1 --port 8000 --reload
pause
