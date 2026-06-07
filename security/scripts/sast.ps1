Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
$reports = Join-Path $root "security\reports"
New-Item -ItemType Directory -Force -Path $reports | Out-Null

docker run --rm -v "${root}:/src" -w /src semgrep/semgrep:latest semgrep scan --config security/semgrep.yml --json --output security/reports/semgrep-report.json .
docker run --rm -v "${root}:/src" -w /src semgrep/semgrep:latest semgrep scan --config security/semgrep.yml --sarif --output security/reports/semgrep-report.sarif .

Write-Host "SAST reports generated:"
Write-Host "security/reports/semgrep-report.json"
Write-Host "security/reports/semgrep-report.sarif"
