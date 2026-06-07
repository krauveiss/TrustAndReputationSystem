# DevSecOps lab

This project uses three open-source security checks:

- SAST: Semgrep with custom rules in `security/semgrep.yml`.
- DAST: OWASP ZAP baseline scan with rules in `security/zap-rules.tsv`.
- SCA: Composer audit and npm audit.

Local SAST:

Windows cmd:

```bat
composer security:sast
```

PowerShell:

```powershell
composer security:sast
```

Generated reports:

- `security/reports/semgrep-report.json`
- `security/reports/semgrep-report.sarif`

Local DAST:

Windows cmd:

```bat
php artisan serve --host=0.0.0.0 --port=8000
composer security:dast
```

PowerShell:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
composer security:dast
```

Generated reports:

- `security/reports/zap-report.html`
- `security/reports/zap-report.json`

Local SCA:

```bash
composer security:sca
```

Generated reports:

- `security/reports/composer-audit.json`
- `security/reports/npm-audit.json`

CI/CD:

- `.github/workflows/devsecops.yml` runs SAST, DAST, and SCA on pull requests and pushes to `main` or `master`.
- SAST uploads `semgrep-sast-report`.
- DAST uploads `zap-dast-report`.
- SCA uploads `dependency-audit-reports`.

What was fixed:

- `.env.example`: debug mode disabled by default.
- `app/Http/Requests/RegisterRequest.php`: password minimum length increased to 8.
- `app/Http/Requests/LoginRequest.php`: password minimum length increased to 8.
- `app/Http/Middleware/SecurityHeaders.php`: common security headers added.
- `bootstrap/app.php`: security headers middleware enabled globally.

Intentional trigger:

- `security/fixtures/vulnerable.php` intentionally triggers Semgrep.
- `security/fixtures/fixed.php` shows the corresponding safe approach.
