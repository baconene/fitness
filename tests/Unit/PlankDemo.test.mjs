import { test } from 'node:test';
import assert from 'node:assert/strict';
import { exercisePose } from '../../resources/js/Support/exerciseDemoScene.js';
import { demoFor } from '../../resources/js/Support/exerciseDemos.js';

test('plank keeps elbows under shoulders and floor contacts fixed throughout the hold', () => {
    const start = exercisePose('plank', 0);
    for (let frame = 0; frame <= 32; frame++) {
        const pose = exercisePose('plank', frame / 32);
        pose.arms.forEach((arm, index) => {
            assert.equal(arm.elbow[0], arm.shoulder[0]);
            assert.equal(arm.elbow[2], arm.shoulder[2]);
            assert.deepEqual(arm.elbow, start.arms[index].elbow);
            assert.deepEqual(arm.wrist, start.arms[index].wrist);
            assert.ok(arm.wrist[2] < arm.elbow[2]);
        });
        pose.legs.forEach((leg, index) => {
            assert.deepEqual(leg.ankle, start.legs[index].ankle);
            assert.ok(leg.hip[1] > leg.knee[1]);
            assert.ok(leg.knee[1] > leg.ankle[1]);
        });
    }
});

test('the wireframe plank bypasses the previous artwork cache for animation and poster', () => {
    const demo = demoFor('plank');
    for (const kind of ['gif', 'poster']) {
        const version = new URL(demo[kind], 'http://localhost').searchParams.get('v');
        assert.ok(version);
        assert.notEqual(version, '3d-1');
    }
});
