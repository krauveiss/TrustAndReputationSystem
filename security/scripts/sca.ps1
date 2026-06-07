Set-StrictMode -Version Latest
$ErrorActionPreference = "Continue"

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$reports = Join-Path $root "security\reports"
New-Item -ItemType Directory -Force -Path $reports | Out-Null
Set-Location $root

composer audit --locked --format=json | Out-File -Encoding utf8 "security\reports\composer-audit.json"
$composerExit = $LASTEXITCODE

if (Test-Path "package.json") {
    npm install
    npm audit --json | Out-File -Encoding utf8 "security\reports\npm-audit.json"
    $npmExit = $LASTEXITCODE
} else {
    "{}" | Out-File -Encoding utf8 "security\reports\npm-audit.json"
    $npmExit = 0
}

Write-Host "SCA reports generated:"
Write-Host "security/reports/composer-audit.json"
Write-Host "security/reports/npm-audit.json"

if ($composerExit -ne 0 -or $npmExit -ne 0) {
    Write-Host "Audit found vulnerabilities or warnings. Reports were still generated."
}
