@echo off
echo Stopping Tanzeem Docker Environment...
cd /d "%~dp0"
docker-compose down
echo.
echo ====================================================
echo   SHUTDOWN COMPLETE.
echo ====================================================
echo.
pause
