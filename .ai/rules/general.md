---
paths:
  - '**'
---

# General

## Follow Laravel best practices
All implemented code must follow Laravel best practices and the Laravel Boost guidelines (AGENTS.md and the project skills). Confirm APIs against the installed version and prefer the pattern the application already uses.

## Commit only when asked, then push
Do not create commits unless the user explicitly asks. When the user asks for a commit, commit and then push.

## Run tests as the container user
Run Pest, Pint, and PHPStan inside the stats-app container as devuser, not as root. Use docker exec -u devuser -w /var/www/html stats-app. Root-owned files in /tmp/phpstan make PHPStan fail when devuser later uses the same cache.
