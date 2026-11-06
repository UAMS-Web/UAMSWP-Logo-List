# UAMSWP-Logo-List

## Amp orbs

`.agents/setup` installs PHP 8.4, PHP 8.2 for floor linting, Composer 2, Node 22,
both locked Composer dependency trees, and the locked npm dependencies. Amp caches
the prepared machine for fresh orbs; a stale snapshot reruns setup safely.

Setup also downloads the WordPress version matching the integration test lockfile
and creates a disposable MariaDB database. `.agents/resume` restarts MariaDB after
activation or wake-up without reinstalling dependencies. New login shells receive
the `WP_TESTS_*` configuration, so `composer test` can run directly. These settings
are only for the orb's disposable test database, never a production database.

The full stylesheet comparison still requires the sibling `uams-2020` theme and
its dependencies, as described below. Setup does not fetch that separate project.

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

The runner runs the same checks as `ci.yml` on a local machine, before a push.


## Remote CI

`.github/workflows/ci.yml` runs the same checks in GitHub Actions and follows the shared template: the `CI` job `needs:` every other job, and `Attribution footer` and `Closing keywords` are their own workflows that read the pull request body. This repository is public, so the workflows run on every pull request at no cost. Every job in `ci.yml` is still gated on `vars.RUN_REMOTE_CI != 'false'`: with the variable unset the jobs run, and set to `false` they report `skipped`. `CI` is the check to require. The stylesheet build is not an Actions job: `assets/scss/app.scss` imports from the `uams-2020` theme and its `node_modules`, which sit outside this repository, so a runner with only this checkout cannot compile it.
