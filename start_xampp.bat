@echo off
echo Starting XAMPP services...
echo =========================

REM Try to start Apache service
net start apache >nul 2>&1
if %errorlevel% == 0 (
    echo [OK] Apache service started
) else (
    echo [INFO] Apache service not found or already running
)

REM Try to start MySQL service
net start mysql >nul 2>&1
if %errorlevel% == 0 (
    echo [OK] MySQL service started
) else (
    echo [INFO] MySQL service not found or already running
)

echo.
echo Services startup attempt completed.
echo.
echo Please open XAMPP Control Panel manually if services didn't start.
echo.
pause