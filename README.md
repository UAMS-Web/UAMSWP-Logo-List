# UAMSWP-Logo-List

## Credits

Developed and maintained by UAMS Web Services.

Developers:

- Josh Daugherty
- Todd McKee
- Brent Passmore

## Amp orbs

Amp runs the executable `.agents/setup` on a fresh orb and caches the result in
the project snapshot. It installs PHP 8.4 and its test extensions, PHP 8.2 for
floor linting, Composer 2, Node 22 (matching CI), both locked Composer dependency
sets, and the locked npm dependencies.
Run `.agents/setup` again after dependency lockfiles change.
The script targets Debian 12 orbs with passwordless `sudo`.

The npm install uses `--ignore-scripts`, as the build-test instructions below do,
so the legacy `preinstall` hook cannot fetch an unpinned package or rewrite the
lockfile. `.agents/resume` only checks tool availability; it never reinstalls
dependencies or authenticates a user into the shared snapshot.

Unit tests and stylesheet fixture tests need no services. The integration suite
currently contains configuration and bootstrap files but no test cases. Its
dependencies are ready, but running it still requires a separate WordPress core
copy (`WP_TESTS_ABSPATH`) and a disposable MySQL database (`WP_TESTS_DB_*`, read
by `tests/Integration/wp-tests-config.php`). Setup does not create a live site
or start an unused database. The full stylesheet rebuild also needs the sibling
`uams-2020` theme; without it, that one build test is skipped.


## Tests

The supported PHP floor is 8.2. Development and tests run on PHP 8.4 (`config.platform.php` is `8.4.27`). The unit suite runs on Pest 5 with Brain Monkey; the WordPress integration suite runs on PHPUnit 9.6 from its own Composer setup in `tests/Integration/`.

```bash
composer install
composer test:unit            # Pest, no WordPress and no database
composer setup:integration    # one time: installs tests/Integration/vendor
composer test:integration     # PHPUnit 9.6 over wp-phpunit, needs the test database
```

The stylesheet build has its own tests, run with `npm ci --ignore-scripts` then `npm run test:build` (Node's built-in test runner, no extra packages). They feed fixtures through `gulpfile.js` stage by stage; one test also rebuilds `assets/css/app.css` and compares it byte for byte, and is skipped where the `uams-2020` theme that `assets/scss/app.scss` imports is not checked out beside the plugin.

A floor lint (`composer lint:floor`, and the `floor-lint` input of the `checks` job in `ci.yml`) runs `php -l` under PHP 8.2 over every shipped PHP file, the files the download keep-list in `.gitattributes` leaves in the archive.


## Before a push

```bash
composer test:unit
composer lint:floor
```

These run the unit suite and the floor lint on a local machine. Remote CI, below, is the gate.


## Remote CI

`.github/workflows/ci.yml` runs the checks in GitHub Actions. The lint and unit chain is the shared one in `UAMS-Web/uams-ci-workflows`, called by the `checks` job and pinned to a release tag; `guard` and `lint` stay as plain jobs beside it because the chain has no job for them. The `CI` job `needs:` the call and each plain job, and `Attribution footer` and `Closing keywords` are their own workflows that read the pull request body. This repository is public, so the workflows run on every pull request at no cost. `CI` is the check to require. The stylesheet build is not an Actions job: `assets/scss/app.scss` imports from the `uams-2020` theme and its `node_modules`, which sit outside this repository, so a runner with only this checkout cannot compile it.
