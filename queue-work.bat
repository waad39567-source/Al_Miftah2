@echo off
cd /d C:\Users\VICTUS\Desktop\‏‏‏‏‏‏al-miftah try email
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
