@echo off
echo ========================================
echo   Spreadsheet Notes - Local Server
echo ========================================
echo.
echo Menjalankan PHP built-in server...
echo Server akan berjalan di: http://localhost:8000
echo.
echo Tekan Ctrl+C untuk menghentikan server
echo.
echo ========================================
echo.

cd /d "%~dp0"
php -S localhost:8000

pause
