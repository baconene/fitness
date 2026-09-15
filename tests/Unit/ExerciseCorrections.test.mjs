import { test } from 'node:test';
import assert from 'node:assert/strict';
import { exercisePose, exerciseCamera } from '../../resources/js/Support/exerciseDemoScene.js';
import { CABLE_DEMOS, CORRECTED_DEMOS, exerciseEquipment } from '../../resources/js/Support/exerciseCorrections.js';
import { demoFor } from '../../resources/js/Support/exerciseDemos.js';

test('wheel, ropes and cables stay attached to the hands throughout their movements', () => {
    for (let frame = 0; frame < 32; frame++) {
        const phase = frame / 32;
        const wheelPose = exercisePose('ab-wheel-rollout', phase);
        const wheelItems = exerciseEquipment(wheelPose, 'ab-wheel-rollout', phase);
        const wheel = wheelItems.find(item => item.role === 'wheel');
        assert.ok(Math.abs(wheel.center[1] - wheel.radii[1]) < 1e-9, 'wheel rests on the ground');
        assert.deepEqual(wheelItems.find(item => item.role === 'axle').points, wheelPose.arms.map(arm => arm.wrist));
        const ropePose = exercisePose('battle-ropes', phase);
        const ropes = exerciseEquipment(ropePose, 'battle-ropes', phase).filter(item => item.role === 'rope');
        assert.equal(ropes.length, 2);
        ropes.forEach((rope, index) => assert.deepEqual(rope.points[0], ropePose.arms[index].wrist));
        for (const slug of CABLE_DEMOS) {
            const pose = exercisePose(slug, phase);
            const cables = exerciseEquipment(pose, slug, phase).filter(item => item.role === 'cable');
            assert.equal(cables.length, 2, slug);
            cables.forEach((cable, index) => assert.deepEqual(cable.points.at(-1), pose.arms[index].wrist));
        }
    }
});

test('bird dog alternates opposite arm and leg while the supporting hand and knee remain grounded', () => {
    for (const [phase, legIndex] of [[.25, 0], [.75, 1]]) {
        const pose = exercisePose('bird-dog', phase);
        assert.equal(pose.legs[1 - legIndex].knee[1], .1);
        assert.equal(pose.arms[legIndex].wrist[1], .07);
        assert.equal(pose.legs[legIndex].ankle[1], pose.legs[legIndex].hip[1]);
        assert.equal(pose.arms[1 - legIndex].wrist[1], pose.arms[1 - legIndex].shoulder[1]);
    }
});

test('bicycle crunch alternates the bent knee and keeps both leg segments connected', () => {
    const start = exercisePose('bicycle-crunch', 0);
    const halfway = exercisePose('bicycle-crunch', .5);
    assert.ok(start.legs[1].knee[2] < start.legs[0].knee[2]);
    assert.ok(halfway.legs[0].knee[2] < halfway.legs[1].knee[2]);
    for (let frame = 0; frame < 32; frame++) {
        for (const leg of exercisePose('bicycle-crunch', frame / 32).legs) {
            for (const [a, b] of [[leg.hip, leg.knee], [leg.knee, leg.ankle]]) {
                assert.ok(Math.abs(Math.hypot(...a.map((v, i) => v - b[i])) - .43) < 1e-9);
            }
        }
    }
});

test('box jump lands on a box, broad jump lands forward on the floor, and burpee includes a push-up', () => {
    const box = exercisePose('box-jump', .55);
    const broad = exercisePose('broad-jump', .55);
    assert.ok(Math.abs(box.legs[0].ankle[1] - .5) < 1e-9);
    assert.ok(Math.abs(broad.legs[0].ankle[1] - .08) < 1e-9);
    assert.ok(broad.pelvis[2] > 1);
    assert.ok(exerciseEquipment(box, 'box-jump', .55).some(item => item.type === 'face'));
    const low = exercisePose('burpee', .39);
    const high = exercisePose('burpee', .50);
    assert.ok(low.neck[1] < high.neck[1]);
    low.arms.forEach((arm, index) => assert.deepEqual(arm.wrist, high.arms[index].wrist));
    assert.ok(exercisePose('burpee', .78).legs[0].ankle[1] > .3);
});

test('corrected loops close smoothly, equipment fits the camera and URLs bypass old artwork', () => {
    for (const slug of CORRECTED_DEMOS) {
        const start = exercisePose(slug, 0), end = exercisePose(slug, 1);
        for (const group of ['arms', 'legs']) {
            start[group].forEach((limb, index) => {
                for (const key of Object.keys(limb)) limb[key].forEach((v, axis) => assert.ok(Math.abs(v - end[group][index][key][axis]) < 1e-8, slug));
            });
        }
        const camera = exerciseCamera(slug);
        for (let frame = 0; frame < 32; frame++) {
            for (const item of exerciseEquipment(exercisePose(slug, frame / 32), slug, frame / 32)) {
                for (const point of item.points ?? [item.center]) {
                    const projected = camera.project(point);
                    const x = 200 + (projected[0] - camera.center[0]) * camera.scale;
                    const y = 120 + (projected[1] - camera.center[1]) * camera.scale;
                    assert.ok(x > 0 && x < 400 && y > 0 && y < 240, slug);
                }
            }
        }
        assert.notEqual(new URL(demoFor(slug).gif, 'http://localhost').searchParams.get('v'), 'wireframe-3');
    }
});
