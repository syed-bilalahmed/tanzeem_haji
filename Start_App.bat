@echo off
echo Starting Tanzeem Software...
:: Try to open in Chrome App Mode
start chrome --app=http://localhost/software/index.php
if %errorlevel% neq 0 (
    :: If Chrome fails, try Edge
    start msedge --app=http://localhost/software/index.php
)
exit
