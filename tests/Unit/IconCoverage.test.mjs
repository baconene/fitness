import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';

const ROOT = new URL('../../resources/js', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');

const vueFiles = (dir) =>
    readdirSync(dir).flatMap((entry) => {
        const path = join(dir, entry);

        return statSync(path).isDirectory() ? vueFiles(path) : path.endsWith('.vue') ? [path] : [];
    });

/** Icon names declared in the `paths` map of Icon.vue. */
const declaredIcons = () => {
    const source = readFileSync(join(ROOT, 'Components/Icon.vue'), 'utf8');
    const map = source.slice(source.indexOf('const paths = {'), source.indexOf('\n};'));

    return new Set([...map.matchAll(/^\s{4}([a-zA-Z]+):/gm)].map((match) => match[1]));
};

/**
 * Icon names actually requested, from both usage shapes in this codebase:
 * `<Icon name="x" />` and the nav tuples `['Label', 'icon', 'route.name']`.
 */
const requestedIcons = () => {
    const requests = new Map();

    for (const file of vueFiles(ROOT)) {
        const source = readFileSync(file, 'utf8');
        const found = [
            ...[...source.matchAll(/<Icon[^>]*\sname="([a-zA-Z]+)"/g)].map((m) => m[1]),
            // Nav tuples only: a capitalised label distinguishes them from
            // plain string lists such as ['cardio', 'mobility', 'stretching'].
            ...[...source.matchAll(/\['[A-Z][^']*',\s*'([a-z][a-zA-Z]*)',\s*'[a-z][a-zA-Z.]*'\]/g)].map((m) => m[1]),
        ];

        for (const name of found) {
            if (!requests.has(name)) {
                requests.set(name, file.replace(ROOT, ''));
            }
        }
    }

    return requests;
};

test('every icon the UI asks for is actually drawn', () => {
    const declared = declaredIcons();
    const requested = requestedIcons();

    assert.ok(declared.size > 20, 'the icon map should have parsed');
    assert.ok(requested.size > 10, 'icon usages should have parsed');

    const missing = [...requested.entries()].filter(([name]) => !declared.has(name));

    assert.deepEqual(
        missing,
        [],
        `Icon.vue has no path for: ${missing.map(([n, f]) => `${n} (${f})`).join(', ')}`,
    );
});

test('the navigation icons resolve, including Health', () => {
    const declared = declaredIcons();

    // Regression: the sidebar asked for `heart` before it was drawn, so the
    // Health item rendered a blank square.
    for (const name of ['heart', 'dashboard', 'scroll', 'dumbbell', 'calendar', 'clipboard', 'body', 'chart']) {
        assert.ok(declared.has(name), `navigation icon "${name}" is not drawn`);
    }
});
