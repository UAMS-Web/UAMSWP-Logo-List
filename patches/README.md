# Temporary development dependency repairs

Tracked by [UAMS-Web/UAMSWP-Logo-List#82](https://github.com/UAMS-Web/UAMSWP-Logo-List/issues/82).
Retirement is tracked by [UAMS-Web/UAMSWP-Logo-List#85](https://github.com/UAMS-Web/UAMSWP-Logo-List/issues/85).

These patches change development dependencies only. Their versions remain locked.
Registration lives in root-only `patches.json`, not `extra.patches`, so installing
this plugin as another project's dependency does not export these repairs.
`patches.lock.json` pins the patch contents by SHA-256. GitHub downloads exclude
all tooling and patches through the existing `.gitattributes` keep-list.

## Patchwork directory delegation

`patchwork-directory-wrapper.patch` repairs Patchwork 2.2.4 when it delegates
directory operations to another file stream wrapper. Retain that wrapper object
for subsequent reads and closes, rather than retaining its boolean open result.
Native directory operations still retain their native resource.

The file is carried by the development dependency
[`uams-web/patches-patchwork`](https://github.com/UAMS-Web/patches-patchwork), read
through the `vcs` entry in `composer.json`'s `repositories`, and `patches.json`
registers it at `vendor/uams-web/patches-patchwork/patches/`. A change to the
patch is made in that package and arrives here through a version bump, then
`composer patches-relock` and `composer patches-repatch`: bumping the package alone
does not repatch Patchwork. When it retires, remove the package from `require-dev`
and its `repositories` entry.

## Isolated mutation processes and missing test reports

The repairs to Pest Mutate 5.0.2 come from the development dependency
[`uams-web/patches-pest-plugin-mutate`](https://github.com/UAMS-Web/patches-pest-plugin-mutate),
read through the `vcs` entry in `composer.json`'s `repositories`. The package applies
its eleven patches as an ordered set (a later patch is cut against the output of the
earlier ones), so `patches.json` registers all eleven at
`vendor/uams-web/patches-pest-plugin-mutate/patches/`, in the package's order. A change
arrives through a version bump, then `composer patches-relock` and
`composer patches-repatch`. The repair this repository added is the package's
`pest-plugin-mutate-isolated-processes.patch`, described below; the other ten are the
package's other repairs.

`pest-plugin-mutate-isolated-processes.patch` repairs Pest Mutate 5.0.2. Activate mutation
source replacement through a package-owned Composer autoload file, which runs in
both Pest and PHPUnit's isolated children. Remove the duplicate Pest-kernel boot
activation. The root `autoload-dev.files` entry is necessary because modifying a
dependency's manifest does not change the autoload metadata already in our lock.
Do not substitute an edit to `vendor/composer/installed.json`.

Each mutant gets a unique temporary JUnit report. An absent, invalid or empty
report aborts with child diagnostics instead of being scored as a killed mutant.
Normal completion, timeout and abandoned mutant objects remove the report.
This check does not distinguish
every possible infrastructure error from a test error inside a reported testcase;
inspect error diagnostics during preflight before accepting mutation results.

## Verification and retirement

`composer test:unit` includes guards for the patched dependency installation.
The mutation preflight must also prove that original source passes, a deliberate
mutant fails the expected assertion in an isolated child, and a child that fails
before reporting tests aborts rather than increasing the killed count. A zero
mutation count is not a passing preflight.

Retire each repair only when an installable upstream version provides equivalent
behavior and the same positive and negative controls pass without its patch.
Remove the patch registration before updating its package, then:

- Remove the corresponding `.patch` file (for Patchwork and Pest Mutate, the package instead) and
  installation guards.
- Remove `tests/Unit/MutationCompatibilityTest.php` and its four
  `tests/Fixtures/MutationProbe*.php` / `MutationReportProcess.php` fixtures
  when neither repair needs them. Keep the shared isolated-test autoloader
  constant in `tests/bootstrap.php` for other isolated tests.
- Remove the root `autoload-dev.files` entry when Pest Mutate supplies its own
  early autoload bootstrap.
- Regenerate `patches.lock.json`; refresh `composer.lock` if the manifest changes.
- If no patches remain, remove Composer Patches and its unused dependencies,
  its `allow-plugins` entry, `patches.json` and `patches.lock.json`.
- Cold-install, run the ordinary suites and mutation preflight, and verify the
  runtime download still excludes development files.
- Remove this narrative when both repairs retire.
