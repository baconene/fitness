import { test } from 'node:test';
import assert from 'node:assert/strict';
import { exercisePose, exerciseCamera } from '../../resources/js/Support/exerciseDemoScene.js';
import { FORM_DEMOS, formEquipment } from '../../resources/js/Support/exerciseFormCorrections.js';
import { demoFor } from '../../resources/js/Support/exerciseDemos.js';

test('chin-ups lift the whole body under fixed grips and hanging raises keep the bar fixed', () => {
    const start = exercisePose('chin-up', 0), top = exercisePose('chin-up', .5);
    assert.ok(top.legs[0].ankle[1] > start.legs[0].ankle[1] + .4);
    for (const slug of ['chin-up', 'hanging-leg-raise', 'dips']) {
        const first = exercisePose(slug, 0);
        for (let frame = 0; frame < 32; frame++) {
            const pose = exercisePose(slug, frame / 32);
            pose.arms.forEach((arm, i) => assert.deepEqual(arm.wrist, first.arms[i].wrist));
            assert.ok(pose.legs.every(leg => leg.ankle[1] > .15), `${slug} must clear the floor`);
            const bars = formEquipment(pose, slug, frame / 32).filter(item => ['pull-up-bar', 'dip-bar'].includes(item.role));
            assert.ok(bars.length > 0);
            pose.arms.forEach(arm => assert.equal(arm.wrist[1], bars[0].points[0][1]));
        }
    }
});

test('cat cow holds its supports while curving the spine and dead bug alternates opposite limbs', () => {
    const cat = exercisePose('cat-cow', 0), cow = exercisePose('cat-cow', .5);
    assert.ok(cat.spineArch > 0 && cow.spineArch < 0);
    assert.deepEqual(cat.arms, cow.arms);
    assert.deepEqual(cat.legs, cow.legs);
    for (const [phase, active] of [[.25, 0], [.75, 1]]) {
        const pose = exercisePose('dead-bug', phase);
        assert.ok(pose.legs[active].ankle[1] < pose.legs[1 - active].ankle[1]);
        assert.ok(pose.arms[1 - active].wrist[1] < pose.arms[active].wrist[1]);
        assert.equal(pose.pelvis[1], .16);
    }
});

test('clap push-up leaves the floor and brings hands together while close grip stays planted', () => {
    const clap = exercisePose('clap-push-up', .43);
    assert.ok(clap.arms.every(arm => arm.wrist[1] > .25));
    assert.ok(Math.abs(clap.arms[0].wrist[0] - clap.arms[1].wrist[0]) < .04);
    for (let frame = 0; frame < 32; frame++) {
        const close = exercisePose('close-grip-push-up', frame / 32);
        assert.equal(close.arms[0].wrist[1], .065);
        assert.equal(close.arms[1].wrist[1], .065);
        assert.ok(Math.abs(close.arms[0].wrist[0] - close.arms[1].wrist[0]) < .2);
    }
});

test('cardio feet track pedals and bench exercises have the correct supports and load', () => {
    for (const slug of ['cycling', 'elliptical-trainer']) {
        for (let frame = 0; frame < 32; frame++) {
            const pose = exercisePose(slug, frame / 32);
            const pedals = formEquipment(pose, slug, frame / 32).filter(item => item.role === 'pedal');
            assert.equal(pedals.length, 2);
            pedals.forEach((pedal, index) => assert.ok(Math.abs(pedal.points[0][1] - (pose.legs[index].ankle[1] - .075)) < 1e-9));
        }
    }
    for (const slug of ['chest-fly', 'decline-bench-press', 'dumbbell-pullover', 'hip-thrust']) {
        const pose = exercisePose(slug, .5);
        const equipment = formEquipment(pose, slug, .5);
        assert.ok(equipment.some(item => item.role === 'bench'), slug);
        assert.ok(equipment.some(item => item.role === 'weight'), slug);
    }
    const decline = exercisePose('decline-bench-press', .5);
    assert.ok(decline.head[1] < decline.pelvis[1]);
    const hip = exercisePose('hip-thrust', .5);
    assert.equal(formEquipment(hip, 'hip-thrust', .5).find(item => item.role === 'bar').points[0][1], hip.pelvis[1] + .12);
});

test('new form loops close, equipment stays in view and artwork versions are refreshed', () => {
    for (const slug of FORM_DEMOS) {
        const camera = exerciseCamera(slug), start = exercisePose(slug, 0), end = exercisePose(slug, 1);
        for (const group of ['arms', 'legs']) start[group].forEach((limb, i) => {
            for (const key of Object.keys(limb)) limb[key].forEach((value, axis) => assert.ok(Math.abs(value - end[group][i][key][axis]) < 1e-8, slug));
        });
        for (let frame = 0; frame < 32; frame++) {
            for (const item of formEquipment(exercisePose(slug, frame / 32), slug, frame / 32)) {
                for (const point of item.points ?? [item.center]) {
                    const projected = camera.project(point);
                    const x = 200 + (projected[0] - camera.center[0]) * camera.scale;
                    const y = 120 + (projected[1] - camera.center[1]) * camera.scale;
                    assert.ok(x > 0 && x < 400 && y > 0 && y < 240, slug);
                }
            }
        }
        assert.notEqual(new URL(demoFor(slug).gif, 'https://example.test').searchParams.get('v'), 'wireframe-3');
    }
});
