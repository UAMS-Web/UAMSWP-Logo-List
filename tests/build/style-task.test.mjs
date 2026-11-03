// What the gulpfile's `style` task does to a stylesheet, one stage at a time.
//
// Each test feeds a small fixture through the repository's own gulpfile (see helpers.mjs) and
// checks the stage's visible effect on the compiled output, so a failure names the stage.

import assert from 'node:assert/strict'
import { existsSync, readdirSync } from 'node:fs'
import { join } from 'node:path'
import { test } from 'node:test'

import { build, buildScss, compact } from './helpers.mjs'

test('compiles variables, nesting and partial imports', () => {
    buildScss(
        '$color: #ff0000;\n@import "layouts";\n.card { .title { color: $color; } }\n',
        { 'assets/scss/_layouts.scss': '.from-partial { margin: 0; }\n' },
        (result) => {
            assert.equal(result.status, 0, result.log)
            assert.match(compact(result.css), /\.card \.title\{color:red;?\}/)
            assert.match(compact(result.css), /\.from-partial\{margin:0;?\}/)
        },
    )
})

test('logs a Sass syntax error and writes no stylesheet, without failing the run', () => {
    buildScss('.broken { color: ; \n', {}, (result) => {
        assert.equal(result.status, 0, result.log)
        assert.equal(result.css, null)
        assert.match(result.log, /error/i)
    })
})

test('adds vendor prefixes through autoprefixer', () => {
    buildScss('.a { user-select: none; }\n', {}, (result) => {
        assert.equal(result.status, 0, result.log)
        assert.match(compact(result.css), /-webkit-user-select:none/)
    })
})

test('minifies through cssnano', () => {
    buildScss('.a {\n  color: #ff0000;\n  margin: 0px 0px 0px 0px;\n}\n', {}, (result) => {
        assert.equal(result.status, 0, result.log)
        assert.equal(compact(result.css), '.a{color:red;margin:0;}')
    })
})

test('keeps the first comment and drops every other', () => {
    buildScss('/*! first */\n.a { color: red; }\n/*! second */\n.b { color: blue; }\n', {}, (result) => {
        assert.equal(result.status, 0, result.log)
        assert.match(result.css, /\/\*! first \*\//)
        assert.doesNotMatch(result.css, /second/)
    })
})

test('groups repeated media queries into one block', () => {
    const scss =
        '@media (min-width: 768px) { .a { color: red; } }\n' +
        '.b { color: blue; }\n' +
        '@media (min-width: 768px) { .c { color: green; } }\n'

    buildScss(scss, {}, (result) => {
        assert.equal(result.status, 0, result.log)
        assert.equal(result.css.match(/@media/g).length, 1)
        assert.match(compact(result.css), /@media[^{]*\{\.a\{[^}]*\}\.c\{[^}]*\}\}/)
    })
})

test('writes one file, app.css, into assets/css', () => {
    buildScss('.a { color: red; }\n', {}, (result) => {
        assert.equal(result.status, 0, result.log)
        assert.deepEqual(readdirSync(join(result.cwd, 'assets', 'css')), ['app.css'])
    })
})

test('rebuilds an existing app.css even when it is newer than the source', () => {
    // gulp-changed compares against `assets/css/app.scss`, which never exists, so every run writes.
    const result = build({
        'assets/scss/app.scss': '.fresh { color: red; }\n',
        'assets/css/app.css': '.stale{color:blue}\n',
    })

    try {
        assert.equal(result.status, 0, result.log)
        assert.match(result.css, /\.fresh/)
        assert.doesNotMatch(result.css, /stale/)
    } finally {
        result.cleanup()
    }
})

test('registers only the default task, which runs the style function', () => {
    const listed = build({}, ['--tasks-simple'])

    try {
        assert.equal(listed.status, 0, listed.log)
        assert.deepEqual(listed.log.trim().split(/\r?\n/), ['default'])
        assert.equal(existsSync(join(listed.cwd, 'assets', 'css', 'app.css')), false)
    } finally {
        listed.cleanup()
    }

    buildScss('.a { color: red; }\n', {}, (built) => {
        assert.equal(built.status, 0, built.log)
        assert.match(built.log, /Finished 'style'/)
        assert.match(built.log, /Finished 'default'/)
    })
})
