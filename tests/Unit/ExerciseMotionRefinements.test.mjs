import { test } from 'node:test';
import assert from 'node:assert/strict';
import { exercisePose, exerciseCamera } from '../../resources/js/Support/exerciseDemoScene.js';
import { exerciseEquipment } from '../../resources/js/Support/exerciseCorrections.js';
import { MOTION_DEMOS, PUSHUP_DEMOS } from '../../resources/js/Support/exerciseMotionRefinements.js';
import { demoFor } from '../../resources/js/Support/exerciseDemos.js';

const distance = (a, b) => Math.hypot(...a.map((v, i) => v - b[i]));
test('refined movements preserve anatomy, clear the floor, and loop without a jump', () => {
    const failures = [];
    for (const slug of MOTION_DEMOS) {
        const first = exercisePose(slug, 0), last = exercisePose(slug, 1);
        assert.ok(distance(first.head, last.head) < 1e-8, slug);
        for (let frame = 0; frame < 64; frame++) {
            const pose = exercisePose(slug, frame / 64);
            for (const [i, a] of pose.arms.entries()) {
                for (const [start, end, expected] of [['shoulder', 'elbow', .30], ['elbow', 'wrist', .28]]) {
                    const length = distance(a[start], a[end]);
                    if (Math.abs(length - expected) > .035) failures.push(`${slug} frame ${frame} arm ${i} ${start}: ${length.toFixed(3)}`);
                }
            }
            for (const [i, l] of pose.legs.entries()) {
                for (const [a, b] of [['hip', 'knee'], ['knee', 'ankle']]) {
                    const length = distance(l[a], l[b]);
                    if (Math.abs(length - .43) > .045) failures.push(`${slug} frame ${frame} leg ${i} ${a}: ${length.toFixed(3)}`);
                }
            }
            for (const point of [pose.head, ...pose.arms.flatMap(Object.values), ...pose.legs.flatMap(Object.values)]) {
                assert.ok(point.every(Number.isFinite), slug);
                if (point[1] < .025) failures.push(`${slug} frame ${frame} below floor ${point[1]}`);
            }
        }
        assert.match(demoFor(slug).gif, slug === 'burpee' ? /apparatus-8/ : /motion-7/);
    }
    assert.deepEqual([...new Set(failures.map(f => f.replace(/frame \d+/, 'frame')))], []);
});

test('machines include seats, resistance and rollers attached to the moving limbs', () => {
    for (let frame = 0; frame < 32; frame++) {
        for (const [slug, role] of [['leg-extension', 'shin-roller'], ['lying-leg-curl', 'ankle-roller']]) {
            const pose = exercisePose(slug, frame / 32), equipment = exerciseEquipment(pose, slug, frame / 32);
            const roller = equipment.find(e => e.role === role);
            assert.ok(roller);
            assert.ok(equipment.some(e => e.role === 'weight-stack'));
            assert.ok(equipment.some(e => ['seat', 'bench'].includes(e.role)));
            const midpoint = pose.legs[0].ankle.map((v, i) => (v + pose.legs[1].ankle[i]) / 2);
            assert.ok(distance(midpoint, roller.center) < .09);
        }
        for (const slug of ['machine-chest-press', 'lat-pulldown', 'leg-press']) {
            const equipment = exerciseEquipment(exercisePose(slug, frame / 32), slug, frame / 32);
            assert.ok(equipment.some(e => e.role === 'backrest'));
            assert.ok(equipment.some(e => ['weight-stack', 'weight'].includes(e.role)));
        }
    }
});

test('push-up variants keep supports planted and the landmine rotates about a fixed anchor', () => {
    for (const slug of PUSHUP_DEMOS.filter(slug => slug !== 'clap-push-up')) {
        const first = exercisePose(slug, 0);
        for (let frame = 0; frame < 32; frame++) {
            const pose = exercisePose(slug, frame / 32);
            pose.arms.forEach((a, i) => assert.deepEqual(a.wrist, first.arms[i].wrist));
            pose.legs.forEach((l, i) => assert.deepEqual(l.ankle, first.legs[i].ankle));
        }
    }
    for (let frame = 0; frame < 32; frame++) {
        const pose = exercisePose('landmine-press', frame / 32);
        const bar = exerciseEquipment(pose, 'landmine-press', frame / 32).find(e => e.role === 'landmine-bar');
        assert.deepEqual(bar.points[0], [0, .07, 1.5]);
        assert.ok(Math.abs(distance(...bar.points) - 1.87) < 1e-8);
    }
});

test('holds remain stable and all equipment fits a stationary camera', () => {
    for (const slug of ['hollow-body-hold', 'side-plank']) {
        const first = exercisePose(slug, 0);
        for (let frame = 0; frame < 32; frame++) assert.ok(distance(first.pelvis, exercisePose(slug, frame / 32).pelvis) < .01);
    }
    for (const slug of MOTION_DEMOS) {
        const camera = exerciseCamera(slug);
        for (let frame = 0; frame < 32; frame++) {
            for (const item of exerciseEquipment(exercisePose(slug, frame / 32), slug, frame / 32)) {
                for (const point of item.points ?? [item.center]) {
                    const p = camera.project(point), x = 200 + (p[0] - camera.center[0]) * camera.scale, y = 120 + (p[1] - camera.center[1]) * camera.scale;
                    assert.ok(x > 0 && x < 400 && y > 0 && y < 240, slug);
                }
            }
        }
    }
});

test('curls isolate the elbow, leg machines hinge at the knee, and rope ends follow the grips', () => {
    for (const slug of ['concentration-curl', 'incline-dumbbell-curl', 'leg-extension', 'lying-leg-curl']) {
        const first = exercisePose(slug, 0), top = exercisePose(slug, .5);
        assert.deepEqual(first.pelvis, top.pelvis, slug);
        if (slug.includes('curl') && !slug.includes('leg')) {
            first.arms.forEach((a, i) => assert.deepEqual(a.elbow, top.arms[i].elbow));
            assert.ok(distance(first.arms[1].wrist, top.arms[1].wrist) > .4);
        } else {
            first.legs.forEach((l, i) => assert.deepEqual(l.knee, top.legs[i].knee));
            assert.ok(distance(first.legs[0].ankle, top.legs[0].ankle) > .4);
        }
    }
    for (let frame = 0; frame < 32; frame++) {
        const pose = exercisePose('jump-rope', frame / 32);
        const rope = exerciseEquipment(pose, 'jump-rope', frame / 32).find(e => e.role === 'rope');
        assert.ok(distance(rope.points[0], pose.arms[0].wrist) < 1e-8);
        assert.ok(distance(rope.points.at(-1), pose.arms[1].wrist) < 1e-8);
    }
});
