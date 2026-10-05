# UAMSWP-Logo-List


## Tests

```bash
composer install
composer test:unit
```

The supported PHP floor is 7.4, so this repository uses PHPUnit 9 from the shared `tests` skill templates (Pest requires PHP 8.2+).


## Local CI

```bash
node .claude/skills/local-ci/local-ci.mjs --dry-run
node .claude/skills/local-ci/local-ci.mjs --all
```

Private repositories validate with this runner rather than billed Actions minutes.
