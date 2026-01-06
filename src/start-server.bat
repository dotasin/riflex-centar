@echo off
echo Pokretanje PHP servera...
echo.
echo Server ce biti dostupan na: http://localhost:8000
echo.
echo Pritisnite Ctrl+C da zaustavite server
echo.
cd /d "%~dp0"
php -S localhost:8000

