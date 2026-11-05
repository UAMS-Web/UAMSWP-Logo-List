// Shared helpers for the stylesheet build tests.
//
// The gulpfile reads and writes paths relative to the working directory, so each test builds a
// small project in a temporary directory and runs the repository's own gulpfile against it with
// `--cwd`. The gulpfile and its packages stay the repository's; only the sources are fixtures.

import { spawnSync } from 'node:child_process'
import { createRequire } from 'node:module'
import { existsSync, mkdirSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs'
import { tmpdir } from 'node:os'
import { dirname, join, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const require = createRequire(import.meta.url)

export const root = resolve(dirname(fileURLToPath(import.meta.url)), '..', '..')
export const gulpfile = join(root, 'gulpfile.js')
export const gulpBin = require.resolve('gulp/bin/gulp.js', { paths: [root] })

/**
 * Build a fixture project and run the gulpfile with the given gulp arguments.
 *
 * @param {Record<string, string>} files Fixture files, keyed by path under the project.
 * @param {string[]} [args] Extra gulp arguments (none runs the default task).
 */
export function build(files, args = []) {
    const cwd = mkdtempSync(join(tmpdir(), 'logo-list-build-'))

    for (const [path, contents] of Object.entries(files)) {
        const target = join(cwd, path)
        mkdirSync(dirname(target), { recursive: true })
        writeFileSync(target, contents)
    }

    const result = spawnSync(process.execPath, [gulpBin, '--gulpfile', gulpfile, '--cwd', cwd, ...args], {
        encoding: 'utf8',
        // No desktop notification from the notify step while a test runs.
        env: { ...process.env, DISABLE_NOTIFIER: 'true' },
    })

    const output = join(cwd, 'assets', 'css', 'app.css')

    return {
        cwd,
        status: result.status,
        log: `${result.stdout}${result.stderr}`,
        css: existsSync(output) ? readFileSync(output, 'utf8') : null,
        cleanup: () => rmSync(cwd, { recursive: true, force: true }),
    }
}

/** Build a project whose only source is `app.scss`, pass the result to `inspect`, then clean up. */
export function buildScss(scss, extra, inspect) {
    const result = build({ 'assets/scss/app.scss': scss, ...extra })

    try {
        return inspect(result)
    } finally {
        result.cleanup()
    }
}

/** Collapse formatting, so a test asserts on rules rather than on the stringifier's indentation. */
export const compact = (css) => css.replace(/\s*([{}:;,])\s*/g, '$1').replace(/\s+/g, ' ').trim()
