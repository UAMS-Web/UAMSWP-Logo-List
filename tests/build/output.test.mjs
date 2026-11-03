// The committed assets/css/app.css is what the build produces from assets/scss/.
//
// assets/scss/app.scss imports the uams-2020 theme (wp-content/themes/uams-2020) and its
// node_modules, which sit outside this repository. The test builds in place, so those relative
// imports resolve, and restores the committed file afterwards. It is skipped, with that reason,
// where the theme is not checked out beside the plugin.

import assert from 'node:assert/strict'
import { spawnSync } from 'node:child_process'
import { existsSync, readFileSync, writeFileSync } from 'node:fs'
import { join, resolve } from 'node:path'
import { test } from 'node:test'

import { gulpBin, gulpfile, root } from './helpers.mjs'

const theme = resolve(root, '..', '..', 'themes', 'uams-2020')
const hasTheme = existsSync(join(theme, 'assets', 'scss')) && existsSync(join(theme, 'node_modules', 'bootstrap'))

test(
    'building assets/scss reproduces the committed assets/css/app.css byte for byte',
    { skip: hasTheme ? false : `the uams-2020 theme (and its node_modules) is not at ${theme}` },
    () => {
        const committedPath = join(root, 'assets', 'css', 'app.css')
        const committed = readFileSync(committedPath)

        try {
            const result = spawnSync(process.execPath, [gulpBin, '--gulpfile', gulpfile, '--cwd', root], {
                encoding: 'utf8',
                env: { ...process.env, DISABLE_NOTIFIER: 'true' },
            })

            assert.equal(result.status, 0, `${result.stdout}${result.stderr}`)
            assert.ok(readFileSync(committedPath).equals(committed), 'the build changed assets/css/app.css')
        } finally {
            writeFileSync(committedPath, committed)
        }
    },
)
