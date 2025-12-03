@echo off
echo ========================================
echo Reset MySQL trong XAMPP
echo ========================================
echo.

echo Dang dung MySQL...
taskkill /F /IM mysqld.exe 2>nul

echo Dang xoa du lieu cu...
timeout /t 2 >nul

cd C:\xampp\mysql\data
del /F /Q ibdata1 2>nul
del /F /Q ib_logfile0 2>nul
del /F /Q ib_logfile1 2>nul
del /F /Q ib_logfile* 2>nul

echo.
echo ========================================
echo Hoan tat! Hay start MySQL trong XAMPP
echo ========================================
pause
