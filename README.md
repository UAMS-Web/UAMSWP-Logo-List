# UAMSWP-Logo-List


## Tests

The supported PHP floor is 8.2. Development and tests run on PHP 8.4 (`config.platform.php` is `8.4.27`). The unit suite runs on Pest 5 with Brain Monkey; the WordPress integration suite runs on PHPUnit 9.6 from its own Composer setup in `tests/Integration/`.

```bash
composer install
composer test:unit            # Pest, no WordPress and no database
composer setup:integration    # one time: installs tests/Integration/vendor
composer test:integration     # PHPUnit 9.6 over wp-phpunit, needs the test database
```

The stylesheet build has its own tests, run with `npm ci --ignore-scripts` then `npm run test:build` (Node's built-in test runner, no extra packages). They feed fixtures through `gulpfile.js` stage by stage; one test also rebuilds `assets/css/app.css` and compares it byte for byte, and is skipped where the `uams-2020` theme that `assets/scss/app.scss` imports is not checked out beside the plugin.

A floor lint (`lint-floor` in `local-ci.json`) runs `php -l` under PHP 8.2 over every shipped PHP file, outside `vendor/` and `tests/`.


## Local CI

```bash
node .claude/skills/local-ci/local-ci.mjs --dry-run
node .claude/skills/local-ci/local-ci.mjs --all
```

Private repositories validate with this runner rather than billed Actions minutes.


## Remote CI

`.github/workflows/ci.yml` runs the same checks in GitHub Actions and follows the shared template: the `CI` job `needs:` every other job, and `Attribution footer` and `Closing keywords` are their own workflows. This repository is private, so every job in `ci.yml` is gated on `vars.RUN_REMOTE_CI != 'false'`. While that variable is `false`, the jobs report `skipped` and the local runner above is the gate. When it is on, `ci.yml` is the gate and `CI` is the check to require. The stylesheet build is not an Actions job: `assets/scss/app.scss` imports from the `uams-2020` theme and its `node_modules`, which sit outside this repository, so a runner with only this checkout cannot compile it.
