// package.json pins glob-parent through `resolutions`. gulp 5 resolves glob-parent 6 through glob-stream,
// outside that pin, so the lockfile check is the advisory boundary: no glob-parent below 5.1.2.

import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { createRequire } from 'node:module'
import { join } from 'node:path'
import { test } from 'node:test'

import { root } from './helpers.mjs'

const require = createRequire(import.meta.url)
const semver = require(require.resolve('semver', { paths: [root] }))

const manifest = JSON.parse(readFileSync(join(root, 'package.json'), 'utf8'))
const lock = JSON.parse(readFileSync(join(root, 'package-lock.json'), 'utf8'))

test('package.json pins glob-parent to ~5.1.2', () => {
    assert.equal(manifest.resolutions['glob-parent'], '~5.1.2')
})

test('every glob-parent the lockfile resolves is at or above the fixed 5.1.2', () => {
    const versions = []

    if (lock.packages) {
        for (const [path, entry] of Object.entries(lock.packages)) {
            if (path.endsWith('node_modules/glob-parent')) versions.push(entry.version)
        }
    } else {
        const walk = (dependencies) => {
            for (const [name, entry] of Object.entries(dependencies ?? {})) {
                if (name === 'glob-parent') versions.push(entry.version)
                walk(entry.dependencies)
            }
        }
        walk(lock.dependencies)
    }

    assert.ok(versions.length > 0, 'the lockfile resolves no glob-parent')

    for (const version of versions) {
        assert.ok(semver.gte(version, '5.1.2'), `glob-parent ${version} is below 5.1.2`)
    }
})
