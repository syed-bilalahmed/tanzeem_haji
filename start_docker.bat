@echo off
echo Starting Tanzeem Docker Environment...
cd /d "%~dp0"
docker-compose up -d
echo.
echo ====================================================
echo   SUCCESS! Your website is running.
echo ====================================================
echo.
echo   Website:      http://localhost:8080
echo   phpMyAdmin:   http://localhost:8081
echo.
echo ====================================================
echo Press any key to close this window...
pause >nul
