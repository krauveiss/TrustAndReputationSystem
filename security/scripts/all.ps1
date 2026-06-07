Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

& (Join-Path $PSScriptRoot "sast.ps1")
& (Join-Path $PSScriptRoot "sca.ps1")

Write-Host "Start Laravel separately before DAST:"
Write-Host "php artisan serve --host=0.0.0.0 --port=8000"
Write-Host "Then run:"
Write-Host "powershell -ExecutionPolicy Bypass -File security\scripts\dast.ps1"
