import { test } from 'node:test';
import assert from 'node:assert/strict';
import { EXERCISE_DEMOS, demoFor } from '../../resources/js/Support/exerciseDemos.js';
import { exercisePose } from '../../resources/js/Support/exerciseDemoScene.js';
import { readFileSync } from 'node:fs';

test('an unknown slug yields no demonstration rather than an unrelated movement', () => {
    assert.equal(demoFor('not-a-real-exercise'), null);
    assert.equal(demoFor(undefined), null);
    assert.equal(demoFor(''), null);
    assert.equal(demoFor('__proto__'), null);
    assert.equal(demoFor('incline-bench-press'), null);
});

test('bench press resolves the same local GIF for seeded and legacy slugs', () => {
    const bench = demoFor('bench-press');
    assert.equal(bench.gif, '/images/exercises/bench-press.gif');
    assert.equal(bench.poster, '/images/exercises/bench-press.png');
    assert.match(bench.label, /bench press/i);
    assert.deepEqual(demoFor('bench_press'), bench);
    assert.deepEqual(demoFor('Bench Press'), bench);
    assert.notEqual(bench.a, bench.b);
});

test('every seeded demo ships an animated GIF and matching PNG poster', () => {
    for (const slug of Object.keys(EXERCISE_DEMOS)) {
        const demo = demoFor(slug);
        const gif = readFileSync(new URL(`../../public${demo.gif}`, import.meta.url));
        const poster = readFileSync(new URL(`../../public${demo.poster}`, import.meta.url));
        assert.equal(gif.subarray(0, 6).toString(), 'GIF89a');
        assert.equal(gif.readUInt16LE(6), 400);
        assert.equal(gif.readUInt16LE(8), 240);
        assert.equal(poster.subarray(1, 4).toString(), 'PNG');
        assert.equal(poster.readUInt32BE(16), 400);
        assert.equal(poster.readUInt32BE(20), 240);
    }
});

test('3D movement loops join continuously and keep bench feet planted', () => {
    for (const slug of Object.keys(EXERCISE_DEMOS)) {
        const start = exercisePose(slug, 0);
        const end = exercisePose(slug, 1);
        for (const group of ['arms', 'legs']) {
            start[group].forEach((limb, index) => {
                for (const joint of Object.keys(limb)) {
                    limb[joint].forEach((value, axis) => {
                        assert.ok(Math.abs(value - end[group][index][joint][axis]) < 0.000001);
                    });
                }
            });
        }
    }
    const top = exercisePose('bench-press', 0);
    const bottom = exercisePose('bench-press', 0.5);
    assert.deepEqual(top.legs, bottom.legs);
    assert.ok(top.arms[0].wrist[1] > bottom.arms[0].wrist[1]);
    assert.equal(bottom.arms[0].wrist[1], bottom.arms[1].wrist[1]);
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
