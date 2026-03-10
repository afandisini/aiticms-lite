# AGENT.md - AitiCore Flex (STRICT)

## Motto
AitiCore Flex - CI simplicity, Laravel security.

## Golden Rules
1. Secure by default: view escape ON, CSRF ON for web, DB binding only, hardened session cookie.
2. No magic overload: advanced features are opt-in.
3. Thin controllers: business logic in `app/Services`.
4. Public CLI surface is stable: only `php aiti ...`.
5. No breaking changes without changelog + migration guide + semver bump.

## Structure Contract
Keep these folders stable:
`app/`, `routes/`, `bootstrap/`, `public/`, `storage/`, `system/`, `tests/`, root `aiti`.

## Security Checklist
- XSS: escaped output by default.
- CSRF: enabled in `web` routes.
- SQL Injection: prepared statements/query binding only.
- Session: HttpOnly true, SameSite Lax default, Secure when HTTPS.
- Uploads: store in `storage/uploads`, random name, MIME/ext whitelist.
- Error handling: hide stack trace in production, log to `storage/logs`.

## CLI Contract
Must keep:
- `php aiti --version`
- `php aiti list`
- `php aiti serve` (`server` alias allowed)
- `php aiti route:list`
- `php aiti key:generate`
- `php aiti preset:bootstrap`

## Definition of Done
- App runs.
- Security defaults active.
- CLI commands work.
- Tests pass.
- Docs updated if public behavior changes.
