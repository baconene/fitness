import { test } from 'node:test';
import assert from 'node:assert/strict';
import { exerciseCamera, exercisePose, treadmillBeltHeight } from '../../resources/js/Support/exerciseDemoScene.js';
import { demoFor } from '../../resources/js/Support/exerciseDemos.js';

test('incline walking keeps a foot on the belt and connected legs throughout the stride', () => {
    for (let frame = 0; frame < 64; frame++) {
        const pose = exercisePose('incline-treadmill-walk', frame / 64);
        let contacts = 0;
        for (const leg of pose.legs) {
            const clearance = leg.ankle[1] - .08 - treadmillBeltHeight(leg.ankle[2]);
            assert.ok(clearance >= -1e-9, 'feet must not penetrate the belt');
            if (clearance < 1e-9) contacts++;
            for (const [a, b] of [[leg.hip, leg.knee], [leg.knee, leg.ankle]]) {
                assert.ok(Math.abs(Math.hypot(...a.map((v, i) => v - b[i])) - .43) < 1e-9);
            }
        }
        assert.ok(contacts >= 1, 'walking should never have an airborne phase');
    }
    const start = exercisePose('incline-treadmill-walk', 0);
    assert.ok(start.legs[0].ankle[2] > 0);
    assert.ok(start.arms[0].elbow[2] < start.arms[0].shoulder[2], 'arm swings opposite the forward leg');
});

test('treadmill equipment fits the frame and uses a fresh artwork URL', () => {
    const camera = exerciseCamera('incline-treadmill-walk');
    for (const point of [[-.5, .02, -.85], [.5, 1.44, 1]]) {
        const projected = camera.project(point);
        const x = 200 + (projected[0] - camera.center[0]) * camera.scale;
        const y = 120 + (projected[1] - camera.center[1]) * camera.scale;
        assert.ok(x > 0 && x < 400 && y > 0 && y < 240);
    }
    const demo = demoFor('incline-treadmill-walk');
    assert.notEqual(new URL(demo.gif, 'http://localhost').searchParams.get('v'), 'wireframe-3');
    assert.equal(new URL(demo.gif, 'http://localhost').search, new URL(demo.poster, 'http://localhost').search);
});
