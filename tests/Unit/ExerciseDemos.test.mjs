import { test } from 'node:test';
import assert from 'node:assert/strict';
import { EXERCISE_DEMOS, demoFor } from '../../resources/js/Support/exerciseDemos.js';

test('an unknown slug yields no demonstration, so the component renders nothing', () => {
    assert.equal(demoFor('not-a-real-exercise'), null);
    assert.equal(demoFor(undefined), null);
    assert.equal(demoFor(''), null);
});

test('a known slug yields both poses and a coaching label', () => {
    const squat = demoFor('bodyweight-squat');

    assert.ok(squat, 'bodyweight-squat should have a demonstration');
    assert.match(squat.label, /squat/i);
    assert.ok(squat.a.includes('<circle'), 'the start pose should draw a figure');
    assert.ok(squat.b.includes('<circle'), 'the end pose should draw a figure');
    assert.notEqual(squat.a, squat.b, 'the two poses must differ or nothing appears to move');
});

test('every authored demonstration is complete and well formed', () => {
    const slugs = Object.keys(EXERCISE_DEMOS);

    assert.ok(slugs.length > 0, 'at least one demonstration should ship');

    for (const slug of slugs) {
        const demo = EXERCISE_DEMOS[slug];

        assert.equal(slug, slug.toLowerCase(), `${slug} should be a lowercase slug`);
        assert.ok(demo.label?.length > 10, `${slug} needs a coaching label`);
        assert.notEqual(demo.a, demo.b, `${slug} has two identical poses`);

        for (const pose of ['a', 'b']) {
            const markup = demo[pose];

            assert.ok(markup?.trim().length > 0, `${slug}.${pose} is empty`);

            // Every tag opened is self-closed: the markup is injected with v-html.
            const opened = (markup.match(/</g) ?? []).length;
            const closed = (markup.match(/\/>/g) ?? []).length;
            assert.equal(opened, closed, `${slug}.${pose} has unbalanced SVG tags`);

            assert.ok(
                !/<(script|foreignObject|image)/i.test(markup),
                `${slug}.${pose} must contain drawing primitives only`,
            );
        }
    }
});
