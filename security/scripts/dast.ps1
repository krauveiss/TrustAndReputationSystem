Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$reports = Join-Path $root "security\reports"
New-Item -ItemType Directory -Force -Path $reports | Out-Null

docker run --rm -v "${root}:/zap/wrk:rw" -w /zap/wrk ghcr.io/zaproxy/zaproxy:stable zap-baseline.py -t http://host.docker.internal:8000 -c security/zap-rules.tsv -r security/reports/zap-report.html -J security/reports/zap-report.json

Write-Host "DAST reports generated:"
Write-Host "security/reports/zap-report.html"
Write-Host "security/reports/zap-report.json"
