Write-Host "Pokretanje PHP servera..." -ForegroundColor Green
Write-Host ""
Write-Host "Server ce biti dostupan na: http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "Pritisnite Ctrl+C da zaustavite server" -ForegroundColor Yellow
Write-Host ""
Set-Location $PSScriptRoot
php -S localhost:8000

