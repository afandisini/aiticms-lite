# AitiCore Flex Changelog

## v0.1.0 - 2026-02-20
- Initial framework skeleton with CI-ish app structure.
- Added bootstrap lifecycle (`public/index.php` -> kernel -> response).
- Added `.env` loader and config reader.
- Added router, route collection, and middleware pipeline.
- Added view renderer with escaped output by default.
- Added CSRF token generation + verification for web middleware group.
- Added single CLI entrypoint `php aiti` and core commands:
  - `--version`
  - `list`
  - `serve` (`server` alias)
  - `route:list`
  - `key:generate`
  - `preset:bootstrap`
- Added local bootstrap preset copier from `node_modules` to `public/assets/vendor`.
- Added initial feature tests (router, escaping, csrf).
