import { test } from 'node:test';
import assert from 'node:assert/strict';
import { exercisePose, exerciseCamera } from '../../resources/js/Support/exerciseDemoScene.js';
import { exerciseEquipment } from '../../resources/js/Support/exerciseCorrections.js';
import { APPARATUS_DEMOS, FORM_REVIEW_DEMOS } from '../../resources/js/Support/exerciseApparatusRefinements.js';
import { demoFor } from '../../resources/js/Support/exerciseDemos.js';

const distance = (a, b) => Math.hypot(...a.map((v, i) => v - b[i]));
test('refined movements preserve anatomy, clear the floor, and loop without a jump', () => {
    const failures = [];
    for (const slug of APPARATUS_DEMOS) {
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
        assert.match(demoFor(slug).gif, FORM_REVIEW_DEMOS.includes(slug) ? /form-9/ : /apparatus-8/);
    }
    assert.deepEqual([...new Set(failures.map(f => f.replace(/frame \d+/, 'frame')))], []);
});


test('equipment supports stay attached throughout the movement', () => {
    for (let frame = 0; frame < 64; frame++) {
        const phase = frame / 64;
        for (const slug of ['pull-up', 'triceps-bench-dip', 'mountain-climber']) {
            const pose = exercisePose(slug, phase), first = exercisePose(slug, 0);
            pose.arms.forEach((a, i) => assert.deepEqual(a.wrist, first.arms[i].wrist, slug));
        }
        const bridge = exercisePose('glute-bridge', phase), rest = exercisePose('glute-bridge', 0);
        assert.deepEqual(bridge.head, rest.head);
        bridge.legs.forEach((l, i) => assert.deepEqual(l.ankle, rest.legs[i].ankle));
        const calf = exercisePose('seated-calf-raise', phase);
        assert.deepEqual(calf.footTips, exercisePose('seated-calf-raise', 0).footTips);
        const pad = exerciseEquipment(calf, 'seated-calf-raise', phase).find(e => e.role === 'thigh-pad');
        assert.ok(Math.abs(pad.center[1] - calf.legs[0].knee[1] - .065) < 1e-8);
        const row = exercisePose('rowing-machine', phase), equipment = exerciseEquipment(row, 'rowing-machine', phase);
        const handle = equipment.find(e => e.role === 'handle');
        assert.deepEqual(handle.points, row.arms.map(a => a.wrist));
        assert.ok(equipment.some(e => e.role === 'foot-strap'));
        const seat = equipment.find(e => e.role === 'sliding-seat');
        assert.ok(Math.abs((seat.points[0][2] + seat.points[2][2]) / 2 - row.pelvis[2]) < 1e-8);
        const bar = exerciseEquipment(exercisePose('t-bar-row', phase), 't-bar-row', phase).find(e => e.role === 'anchored-bar');
        assert.deepEqual(bar.points[0], [0, .055, -1.35]);
        assert.ok(Math.abs(distance(...bar.points) - 1.98) < 1e-8);
    }
});

test('burpee has planted hands during floor phases and an airborne jump', () => {
    const low = exercisePose('burpee', .39), high = exercisePose('burpee', .50);
    assert.ok(low.neck[1] < high.neck[1] - .1);
    low.arms.forEach((a, i) => assert.deepEqual(a.wrist, high.arms[i].wrist));
    assert.ok(exercisePose('burpee', .78).legs.every(l => l.ankle[1] > .3));
});

test('apparatus fits the camera and fixed bars and machines are present', () => {
    for (const [slug, role] of [['pull-up', 'pull-up-bar'], ['stair-climber', 'step'], ['seated-calf-raise', 'toe-platform'], ['skull-crusher', 'bench']]) {
        assert.ok(exerciseEquipment(exercisePose(slug, 0), slug, 0).some(e => e.role === role));
    }
    for (const slug of APPARATUS_DEMOS) {
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

test('reviewed knees keep their bend direction without snapping between frames', () => {
    for (const slug of ['mountain-climber', 'burpee']) {
        let previous = exercisePose(slug, 0);
        for (let frame = 1; frame <= 256; frame++) {
            const pose = exercisePose(slug, frame / 256);
            pose.legs.forEach((l, i) => {
                assert.ok(distance(l.knee, previous.legs[i].knee) < .08, `${slug} knee jumps at ${frame}`);
                const dy = l.ankle[1] - l.hip[1], dz = l.ankle[2] - l.hip[2];
                const bendDirection = (l.knee[1] - l.hip[1]) * dz - (l.knee[2] - l.hip[2]) * dy;
                assert.ok(slug === 'burpee' ? bendDirection >= -1e-8 : bendDirection <= 1e-8, `${slug} knee reverses`);
                assert.ok(l.knee[1] >= .06, `${slug} knee touches floor`);
            });
            if (slug === 'mountain-climber') {
                assert.ok(pose.legs.some(l => l.ankle[2] > .85), 'one leg remains extended during the knee drive');
            }
            previous = pose;
        }
    }
});

test('toe-touch legs remain vertical and both feet point toward the head', () => {
    const first = exercisePose('toe-touch-crunch', 0);
    for (let frame = 0; frame < 64; frame++) {
        const pose = exercisePose('toe-touch-crunch', frame / 64);
        assert.deepEqual(pose.legs, first.legs);
        pose.legs.forEach((l, i) => {
            assert.equal(l.hip[2], l.ankle[2]);
            assert.equal(l.hip[0], l.ankle[0]);
            assert.equal(pose.footTips[i][1], l.ankle[1]);
            assert.ok(pose.footTips[i][2] < l.ankle[2]);
        });
    }
    assert.ok(exercisePose('toe-touch-crunch', .5).neck[1] > first.neck[1] + .15);
});

test('pec-deck levers rotate at fixed pivots and T-bar load sits beyond the grip', () => {
    for (let frame = 0; frame < 64; frame++) {
        const phase = frame / 64, pose = exercisePose('pec-deck', phase);
        const equipment = exerciseEquipment(pose, 'pec-deck', phase);
        const arms = equipment.filter(e => e.role === 'swing-arm');
        arms.forEach((a, i) => {
            assert.ok(Math.abs(distance(a.points[0], a.points[1]) - .30) < 1e-8);
            assert.deepEqual(a.points[2], pose.arms[i].elbow);
        });
        assert.ok(equipment.some(e => e.role === 'back-pad'));
        const row = exercisePose('t-bar-row', phase), items = exerciseEquipment(row, 't-bar-row', phase);
        const anchor = items.find(e => e.role === 'anchored-bar').points[0];
        const plate = items.find(e => e.role === 'weight').center;
        assert.ok(anchor[2] < row.legs[0].ankle[2]);
        assert.ok(plate[2] > row.arms[0].wrist[2]);
    }
});
