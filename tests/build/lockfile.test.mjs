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

test('package.json overrides uuid for node-notifier, whose own range stops at 8', () => {
    assert.equal(manifest.overrides['node-notifier'].uuid, '^11.1.1')
})

test('every uuid the lockfile resolves is at or above the fixed 11.1.1', () => {
    const versions = Object.entries(lock.packages ?? {})
        .filter(([path]) => path.endsWith('node_modules/uuid'))
        .map(([, entry]) => entry.version)

    assert.ok(versions.length > 0, 'the lockfile resolves no uuid')

    for (const version of versions) {
        assert.ok(semver.gte(version, '11.1.1'), `uuid ${version} is below 11.1.1`)
    }
})

test('the notifier module that calls uuid loads and generates an identifier with the overridden copy', () => {
    const notifierRequire = createRequire(require.resolve('node-notifier/package.json', { paths: [root] }))
    const { v4 } = notifierRequire('uuid')

    assert.match(v4(), /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/)
    assert.doesNotThrow(() => notifierRequire('./notifiers/toaster.js'))
})
