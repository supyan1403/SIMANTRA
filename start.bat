@echo off
title SIMANTRA - BPS Kabupaten Tasikmalaya
echo ========================================================
echo   SIMANTRA - SISTEM INFORMASI KEMITRAAN
echo   Badan Pusat Statistik Kabupaten Tasikmalaya
echo ========================================================
echo.
echo Menjalankan server aplikasi di http://127.0.0.1:8000 ...
echo Portal Akses Admin/Operator: http://127.0.0.1:8000/portal-akses-tasik
echo.
echo Tekan CTRL+C untuk menghentikan server.
echo ========================================================
echo.
php artisan serve
pause
