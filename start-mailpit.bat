@echo off
echo Starting Mailpit...
cd /d C:\Users\ichom\mailpit-windows-amd64
start mailpit.exe
echo.
echo Mailpit is starting!
echo Open http://localhost:8025 in your browser to view emails
echo.
echo Press any key to close this window (Mailpit will keep running)...
pause >nul

