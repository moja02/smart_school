@echo off
title Smart School - Online Live Tunnel
echo ======================================================
echo    Smart School - Online Live Tunnel Launcher
echo ======================================================
echo.
echo 1. Starting Laravel local server on port 8000...
start /b php artisan serve --host=127.0.0.1 --port=8000
timeout /t 2 /nobreak >nul
echo.
echo 2. Launching secure public tunnel...
echo    Your live online URL will appear below:
echo ======================================================
echo.
npx --yes localtunnel --port 8000
pause
