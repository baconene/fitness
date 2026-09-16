/** Exercise-specific joint paths and apparatus, in metres (Y up, Z forward). */
export const PUSHUP_DEMOS = ['push-up', 'incline-push-up', 'close-grip-push-up', 'clap-push-up', 'pike-push-up'];
export const MOTION_DEMOS = [
    'burpee', 'concentration-curl', 'front-raise', 'goblet-squat', 'hamstring-stretch',
    'hollow-body-hold', 'incline-bench-press', 'incline-dumbbell-curl', 'incline-dumbbell-press',
    'inverted-row', 'jump-rope', 'kettlebell-swing', 'landmine-press', 'lat-pulldown',
    'lateral-raise', 'leg-extension', 'leg-press', 'lying-leg-curl', 'machine-chest-press',
    'rear-delt-fly', 'russian-twist', 'side-plank', ...PUSHUP_DEMOS,
];
const sides = [-1, 1];
const add = (a, b) => a.map((v, i) => v + b[i]);
const sub = (a, b) => a.map((v, i) => v - b[i]);
const mul = (a, s) => a.map(v => v * s);
const dot = (a, b) => a.reduce((sum, v, i) => sum + v * b[i], 0);
const unit = a => mul(a, 1 / (Math.hypot(...a) || 1));
const mix = (a, b, t) => add(a, mul(sub(b, a), t));
const smooth = t => t * t * (3 - 2 * t);
const tau = Math.PI * 2;
const cycle = phase => ((phase % 1) + 1) % 1;
function bend(a, b, upper, lower, pole) {
    const delta = sub(b, a), d = Math.max(.001, Math.hypot(...delta)), axis = unit(delta);
    const along = (upper ** 2 - lower ** 2 + d ** 2) / (2 * d);
    let normal = sub(pole, mul(axis, dot(pole, axis)));
    if (Math.hypot(...normal) < .001) normal = sub([1, 0, 0], mul(axis, axis[0]));
    return add(add(a, mul(axis, along)), mul(unit(normal), Math.sqrt(Math.max(0, upper ** 2 - along ** 2))));
}
function arm(shoulder, wrist, side, pole = [side, -.3, -.3]) {
    return { shoulder, elbow: bend(shoulder, wrist, .30, .28, pole), wrist };
}
function leg(hip, ankle, pole = [0, 0, 1]) {
    return { hip, knee: bend(hip, ankle, .43, .43, pole), ankle };
}
function trunk(pose, pelvis, up = [0, 1, 0], right = [1, 0, 0]) {
    pose.pelvis = pelvis;
    pose.neck = add(pelvis, mul(up, .63));
    pose.head = add(pose.neck, mul(up, .15));
    pose.right = right;
    pose.arms = sides.map(side => {
        const shoulder = add(add(pose.neck, mul(up, -.12)), mul(right, side * .23));
        return { shoulder, elbow: add(shoulder, [0, -.30, 0]), wrist: add(shoulder, [0, -.57, .05]) };
    });
}
function feet(pose, width = .17, z = .04, y = .08) {
    pose.legs = sides.map(side => leg(add(pose.pelvis, [side * .12, -.025, 0]), [side * width, y, z]));
}
function seated(pose, incline = 0) {
    trunk(pose, [0, .60, .12], [0, Math.cos(incline), -Math.sin(incline)]);
    pose.legs = sides.map(side => leg(add(pose.pelvis, [side * .12, 0, 0]), [side * .24, .08, .68]));
}
function pushup(pose, t, incline = 0, width = .27, flight = 0) {
    pose.prone = true;
    pose.palms = true;
    const angle = .34 + incline * .57 - .19 * t;
    const axis = [0, Math.sin(angle), -Math.cos(angle)];
    const ankle = [0, .08, 1];
    trunk(pose, add(add(ankle, mul(axis, .83)), [0, flight * .08, 0]), axis);
    pose.legs = sides.map(side => {
        const hip = add(pose.pelvis, [side * .12, 0, 0]);
        const foot = add(ankle, [side * .12, 0, 0]);
        return { hip, knee: mix(hip, foot, .5), ankle: foot };
    });
    pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * width * (1 - .96 * flight), .065 + incline + .26 * flight, -.43], sides[i], [sides[i] * .45, .45, .8]));
}
function sample(frames, phase) {
    const p = cycle(phase), index = frames.findIndex(f => f[0] > p);
    const a = frames[index - 1], b = frames[index], t = smooth((p - a[0]) / (b[0] - a[0]));
    return a.slice(1).map((value, i) => value + (b[i + 1] - value) * t);
}

export const motionGeometry = { sides, add, sub, mul, dot, unit, mix, smooth, cycle, bend, arm, leg, trunk, feet, seated, sample };

export function refineMotion(pose, slug, phase) {
    if (!MOTION_DEMOS.includes(slug)) return pose;
    const t = (1 - Math.cos(tau * phase)) / 2;
    pose.prone = false; pose.horizontal = false; pose.palms = false;
    delete pose.spineArch;
    trunk(pose, [0, .94, 0]); feet(pose);
    if (PUSHUP_DEMOS.includes(slug)) {
        if (slug === 'pike-push-up') {
            pose.prone = true; pose.palms = true;
            const angle = .53 + .32 * t;
            trunk(pose, [0, .81, .24], [0, -Math.sin(angle), -Math.cos(angle)]);
            feet(pose, .17, .58);
            pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .28, .065, -.43], sides[i], [sides[i], 0, .3]));
        } else {
            let drop = t, flight = 0;
            if (slug === 'clap-push-up') [drop, flight] = sample([[0, 0, 0], [.25, 1, 0], [.43, 0, 1], [.57, 0, 0], [1, 0, 0]], phase);
            pushup(pose, drop, slug === 'incline-push-up' ? .45 : 0, slug === 'close-grip-push-up' ? .09 : .27, flight);
        }
    } else if (slug === 'burpee') {
        const [hipY, hipZ, tilt, ankleZ, ankleY, handY, handZ, handUp] = sample([
            [0, .94, 0, 0, .04, .08, .88, .05, 0],
            [.14, .43, .12, 1.18, .04, .08, .065, .64, 0],
            [.28, .33, -.20, 1.24, -.98, .08, .065, .64, 0],
            [.39, .19, -.17, 1.47, -.98, .08, .065, .64, 0],
            [.50, .33, -.20, 1.24, -.98, .08, .065, .64, 0],
            [.64, .43, .12, 1.18, .04, .08, .065, .64, 0],
            [.78, 1.22, 0, 0, .04, .36, 2.20, .06, 1],
            [.91, .80, -.08, .18, .04, .08, 1.0, .16, 0],
            [1, .94, 0, 0, .04, .08, .88, .05, 0],
        ], phase);
        trunk(pose, [0, hipY, hipZ], [0, Math.cos(tilt), Math.sin(tilt)]);
        feet(pose, .18, ankleZ, ankleY);
        pose.legs.forEach(l => { l.knee = bend(l.hip, l.ankle, .43, .43, [0, 1, 1]); });
        pose.arms = pose.arms.map((a, i) => {
            let wrist = [sides[i] * .25, handY, handZ];
            const delta = sub(wrist, a.shoulder);
            if (Math.hypot(...delta) > .57) wrist = add(a.shoulder, mul(unit(delta), .57));
            return arm(a.shoulder, wrist, sides[i], [sides[i] * .4, .5, -.7]);
        });
        pose.palms = handY < .12;
    } else if (['incline-bench-press', 'incline-dumbbell-press', 'incline-dumbbell-curl'].includes(slug)) {
        const curl = slug === 'incline-dumbbell-curl';
        seated(pose, curl ? .42 : Math.PI / 3);
        pose.horizontal = !curl;
        pose.arms = pose.arms.map((a, i) => {
            if (curl) {
                const elbow = add(a.shoulder, [sides[i] * .02, -.30, -.015]), angle = .12 + 2.12 * t;
                return { shoulder: a.shoulder, elbow, wrist: add(elbow, [0, -.28 * Math.cos(angle), .28 * Math.sin(angle)]) };
            }
            return arm(a.shoulder, add(a.shoulder, [sides[i] * (.06 - (slug === 'incline-dumbbell-press' ? .16 * (1 - t) : 0)), .54 - .40 * t, .12 * t]), sides[i], [sides[i], -.3, .1]);
        });
    } else if (slug === 'concentration-curl') {
        trunk(pose, [0, .55, .12], unit([0, .65, .76]));
        const shoulder = pose.arms[1].shoulder, elbow = [.22, .58, .40], angle = .10 + 2.05 * t;
        pose.arms[1] = { shoulder, elbow, wrist: [0, 0, 0] };
        // Upper arm remains anchored against the inner thigh throughout the curl.
        pose.arms[1].elbow = add(shoulder, mul(unit(sub(elbow, shoulder)), .30));
        pose.arms[1].wrist = add(pose.arms[1].elbow, [0, -.28 * Math.cos(angle), .28 * Math.sin(angle)]);
        pose.arms[0] = arm(pose.arms[0].shoulder, [-.30, .56, .44], -1);
        pose.legs = sides.map(side => ({ hip: [side * .12, .55, .12], knee: [side * .29, .52, .51], ankle: [side * .38, .08, .47] }));
    } else if (['front-raise', 'lateral-raise', 'rear-delt-fly'].includes(slug)) {
        const rear = slug === 'rear-delt-fly';
        if (rear) { trunk(pose, [0, .87, -.20], [0, .40, .9165]); feet(pose, .21); }
        pose.arms = pose.arms.map((a, i) => {
            const angle = .09 + 1.40 * t;
            const direction = slug === 'front-raise' ? [0, -Math.cos(angle), Math.sin(angle)] : [sides[i] * Math.sin(angle), -Math.cos(angle), rear ? 0 : .08];
            const upper = unit(direction), elbow = add(a.shoulder, mul(upper, .30));
            const lower = unit(add(upper, [0, .03, .15]));
            return { shoulder: a.shoulder, elbow, wrist: add(elbow, mul(lower, .28)) };
        });
    } else if (slug === 'goblet-squat') {
        const angle = .10 + .25 * t;
        trunk(pose, [0, .94 - .37 * t, -.22 * t], [0, Math.cos(angle), Math.sin(angle)]); feet(pose, .25);
        const hold = add(pose.neck, [0, -.22, .22]);
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, add(hold, [sides[i] * .075, 0, 0]), sides[i], [sides[i] * .3, -1, 0]));
    } else if (slug === 'kettlebell-swing') {
        const hinge = 1 - t, angle = .95 * hinge;
        trunk(pose, [0, .94 - .16 * hinge, -.30 * hinge], [0, Math.cos(angle), Math.sin(angle)]); feet(pose, .27);
        const swing = -.28 + 1.80 * t;
        pose.arms = pose.arms.map((a, i) => {
            const direction = unit([-sides[i] * .27, -Math.cos(swing), Math.sin(swing)]);
            return { shoulder: a.shoulder, elbow: add(a.shoulder, mul(direction, .30)), wrist: add(a.shoulder, mul(direction, .575)) };
        });
    } else if (slug === 'landmine-press') {
        const angle = .66 + .30 * t, pivot = [0, .07, 1.5], grip = add(pivot, [0, 1.75 * Math.sin(angle), -1.75 * Math.cos(angle)]);
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, add(grip, [sides[i] * .045, 0, 0]), sides[i], [sides[i], -.5, 0]));
    } else if (['lat-pulldown', 'machine-chest-press', 'leg-extension'].includes(slug)) {
        seated(pose, slug === 'lat-pulldown' ? .08 : .12);
        if (slug === 'lat-pulldown') pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .36, 1.60 - .45 * t, .24], sides[i], [sides[i], -.3, -.2]));
        if (slug === 'machine-chest-press') {
            const angle = .05 + .38 * t;
            pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .30, 1.90 - .86 * Math.cos(angle), .20 + .86 * Math.sin(angle)], sides[i], [sides[i], -.1, -.7]));
        }
        if (slug === 'leg-extension') {
            pose.legs = sides.map(side => {
                const hip = add(pose.pelvis, [side * .12, 0, 0]), knee = add(hip, [0, -.045, Math.sqrt(.43 ** 2 - .045 ** 2)]), angle = .10 + 1.35 * t;
                return { hip, knee, ankle: add(knee, [0, -.43 * Math.cos(angle), .43 * Math.sin(angle)]) };
            });
        }
        if (slug !== 'lat-pulldown' && slug !== 'machine-chest-press') pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .28, .65, .16], sides[i]));
    } else if (slug === 'leg-press') {
        trunk(pose, [0, .43, -.28], [0, .7071, -.7071]);
        const travel = .26 * t;
        pose.legs = sides.map(side => leg(add(pose.pelvis, [side * .12, 0, 0]), [side * .20, .68 + travel, .21 + travel], [0, 1, 0]));
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .28, .47, -.16], sides[i]));
    } else if (slug === 'lying-leg-curl') {
        pose.prone = true; pose.palms = false;
        trunk(pose, [0, .65, .10], [0, 0, -1]);
        pose.legs = sides.map(side => {
            const hip = add(pose.pelvis, [side * .12, 0, 0]), knee = add(hip, [0, -.025, .4293]), angle = .08 + 1.72 * t;
            return { hip, knee, ankle: add(knee, [0, .43 * Math.sin(angle), .43 * Math.cos(angle)]) };
        });
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .27, .52, -.75], sides[i]));
    } else if (slug === 'inverted-row') {
        pose.horizontal = true;
        const angle = .19 + .26 * t, axis = [0, Math.sin(angle), -Math.cos(angle)], foot = [0, .08, .98];
        trunk(pose, add(foot, mul(axis, .83)), axis);
        pose.legs = sides.map(side => { const hip = add(pose.pelvis, [side * .12, 0, 0]), ankle = add(foot, [side * .12, 0, 0]); return { hip, knee: mix(hip, ankle, .5), ankle }; });
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .29, .93, -.30], sides[i], [sides[i], -.4, .5]));
    } else if (slug === 'jump-rope') {
        const lift = .065 * Math.sin(Math.PI * cycle(phase)) ** 2;
        trunk(pose, [0, .94 + lift, 0]); feet(pose, .12, .04, .08 + lift);
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .37, .94 + lift, .10], sides[i]));
    } else if (slug === 'hamstring-stretch' || slug === 'hollow-body-hold') {
        pose.horizontal = true;
        const hollow = slug === 'hollow-body-hold';
        trunk(pose, [0, .16, .08], hollow ? [0, .19, -.9818] : [0, 0, -1]);
        pose.legs = sides.map((side, i) => {
            const hip = add(pose.pelvis, [side * .12, 0, 0]), angle = hollow ? .13 : i ? 1.35 + .004 * t : -.07;
            const axis = [0, Math.sin(angle), Math.cos(angle)], knee = add(hip, mul(axis, .43));
            return { hip, knee, ankle: add(knee, mul(axis, .43)) };
        });
        if (hollow) pose.arms = pose.arms.map(a => ({ shoulder: a.shoulder, elbow: add(a.shoulder, [0, .04, -.2973]), wrist: add(a.shoulder, [0, .078, -.5747]) }));
        else pose.arms = pose.arms.map((a, i) => arm(a.shoulder, add(mix(pose.legs[1].hip, pose.legs[1].knee, .40), [sides[i] * .065, -.015, -.10]), sides[i]));
    } else if (slug === 'russian-twist') {
        pose.horizontal = true;
        const turn = .48 * Math.sin(tau * phase), right = [Math.cos(turn), 0, -Math.sin(turn)];
        trunk(pose, [0, .19, 0], [0, .80, -.60], right);
        pose.legs = sides.map(side => leg([side * .12, .19, 0], [side * .17, .08, .72], [0, 1, 0]));
        const hold = [.34 * Math.sin(turn), .52, .26 * Math.cos(turn)];
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, add(hold, mul(right, sides[i] * .035)), sides[i], [sides[i], -.5, .3]));
    } else if (slug === 'side-plank') {
        pose.horizontal = true;
        const up = [0, .28, -.96], right = [0, .96, .28];
        trunk(pose, [0, .443, .18], up, right);
        pose.legs = sides.map(side => { const hip = add(pose.pelvis, mul(right, side * .10)), ankle = [0, .08 + (side + 1) * .095, .98]; return { hip, knee: mix(hip, ankle, .5), ankle }; });
        const shoulder = pose.arms[0].shoulder;
        pose.arms[0] = { shoulder, elbow: add(shoulder, [0, -.30, 0]), wrist: add(shoulder, [.28, -.30, 0]) };
        pose.arms[1] = arm(pose.arms[1].shoulder, add(pose.pelvis, mul(right, .18)), 1, [0, 1, 0]);
    }
    if (['hollow-body-hold', 'side-plank'].includes(slug)) pose.spineArch = .003 * t;
    return pose;
}

/** The same equipment geometry drives rendering, camera fitting and contact tests. */
export function motionEquipment(pose, slug, phase) {
    if (!MOTION_DEMOS.includes(slug)) return [];
    const items = [];
    const line = (points, role = 'frame', width = 4, color = '#829bb2') => items.push({ type: 'line', points, role, width, color });
    const face = (points, role = 'pad', color = '#30495e') => items.push({ type: 'face', points, role, color });
    const ball = (center, radii, role = 'weight', color = [68, 92, 117]) => items.push({ type: 'ellipsoid', center, radii, role, color });
    const pad = (a, b, width = .21, role = 'bench') => {
        face([add(a, [-width, 0, 0]), add(a, [width, 0, 0]), add(b, [width, 0, 0]), add(b, [-width, 0, 0])], role);
        for (const point of [a, b]) { line([point, [point[0], .04, point[2]]]); line([[-.32, .04, point[2]], [.32, .04, point[2]]]); }
    };
    const dumbbell = (center, vertical = false) => {
        const axis = vertical ? [0, .10, 0] : [0, 0, .105];
        line([sub(center, axis), add(center, axis)], 'grip', 3, '#c2d6e8');
        for (const sign of sides) ball(add(center, mul(axis, sign)), vertical ? [.075, .03, .075] : [.07, .07, .03]);
    };
    const bar = (center, width = .62) => {
        line([add(center, [-width - .12, 0, 0]), add(center, [width + .12, 0, 0])], 'bar', 3, '#bdcfe2');
        for (const side of sides) ball(add(center, [side * width, 0, 0]), [.04, .14, .14]);
    };
    const seat = () => {
        pad([0, .49, -.13], [0, .49, .45], .23, 'seat');
        pad([0, .52, -.04], [0, 1.12, -.12], .20, 'backrest');
    };
    const stack = (x = .60, z = -.25) => {
        for (const side of sides) line([[x + side * .12, .04, z], [x + side * .12, 1.45, z]], 'machine-upright');
        for (let i = 0; i < 8; i++) face([[x - .13, .12 + i * .055, z - .08], [x + .13, .12 + i * .055, z - .08], [x + .13, .12 + i * .055, z + .08], [x - .13, .12 + i * .055, z + .08]], 'weight-stack', '#435668');
    };
    const mat = () => face([[-.50, -.008, -1.30], [.50, -.008, -1.30], [.50, -.008, 1.3], [-.50, -.008, 1.3]], 'ground', '#101e2c');
    if (PUSHUP_DEMOS.includes(slug) || ['burpee', 'hamstring-stretch', 'hollow-body-hold', 'russian-twist', 'side-plank'].includes(slug)) mat();
    if (slug === 'incline-push-up') pad([0, .49, -.67], [0, .49, -.27], .52, 'hand-support');
    if (slug.startsWith('incline-') && !slug.includes('push')) {
        const axis = unit(sub(pose.neck, pose.pelvis));
        pad(add(pose.pelvis, [0, -.11, .10]), add(pose.neck, add(mul(axis, .15), [0, -.11, 0])));
        pad([0, .49, .08], [0, .49, .38], .20, 'seat');
        if (slug === 'incline-bench-press') {
            bar(mix(pose.arms[0].wrist, pose.arms[1].wrist, .5));
            for (const side of sides) line([[side * .54, .04, -.52], [side * .54, 1.28, -.52], [side * .54, 1.28, -.40]], 'rack');
        } else pose.arms.forEach(a => dumbbell(a.wrist));
    }
    if (slug === 'concentration-curl') { pad([0, .49, -.17], [0, .49, .30], .38, 'seat'); dumbbell(pose.arms[1].wrist); }
    if (['front-raise', 'lateral-raise', 'rear-delt-fly'].includes(slug)) pose.arms.forEach(a => dumbbell(a.wrist));
    if (slug === 'goblet-squat') dumbbell(add(mix(pose.arms[0].wrist, pose.arms[1].wrist, .5), [0, -.07, 0]), true);
    if (slug === 'kettlebell-swing') {
        const grip = mix(pose.arms[0].wrist, pose.arms[1].wrist, .5), center = add(grip, [0, -.15, 0]);
        ball(center, [.12, .13, .10], 'kettlebell');
        line([add(grip, [-.075, -.075, 0]), add(grip, [-.075, .015, 0]), add(grip, [.075, .015, 0]), add(grip, [.075, -.075, 0])], 'handle', 4);
    }
    if (slug === 'landmine-press') {
        const pivot = [0, .07, 1.5], grip = mix(pose.arms[0].wrist, pose.arms[1].wrist, .5), axis = unit(sub(grip, pivot));
        line([pivot, add(grip, mul(axis, .12))], 'landmine-bar', 4, '#b5c9de');
        ball(add(grip, mul(axis, -.20)), [.15, .045, .15]);
        items.at(-1).basis = [[1, 0, 0], axis, [0, -axis[2], axis[1]]];
        face([[-.22, .015, 1.3], [.22, .015, 1.3], [.22, .015, 1.7], [-.22, .015, 1.7]], 'anchor');
    }
    if (slug === 'inverted-row') for (const side of sides) {
        line([[side * .56, .03, -.30], [side * .56, .93, -.30]], 'rack');
        line([[side * .56, .03, -.60], [side * .56, .03, 0]], 'base');
        if (side === 1) line([[-.56, .93, -.30], [.56, .93, -.30]], 'pull-up-bar');
    }
    if (slug === 'jump-rope') {
        const [left, right] = pose.arms.map(a => a.wrist), center = mix(left, right, .5), angle = tau * phase;
        const rope = Array.from({ length: 41 }, (_, i) => {
            const u = i / 40, bow = Math.sin(Math.PI * u);
            return [left[0] + (right[0] - left[0]) * u, center[1] + .99 * bow * Math.cos(angle), center[2] + .99 * bow * Math.sin(angle)];
        });
        line(rope, 'rope', 1.5, '#b0a2ed');
        pose.arms.forEach(a => line([add(a.wrist, [0, -.045, 0]), add(a.wrist, [0, .045, 0])], 'handle', 4));
    }
    if (['lat-pulldown', 'machine-chest-press', 'leg-extension'].includes(slug)) {
        seat(); stack();
        if (slug === 'lat-pulldown') {
            const grip = mix(pose.arms[0].wrist, pose.arms[1].wrist, .5);
            line([[-.48, grip[1] - .05, grip[2]], pose.arms[0].wrist, grip, pose.arms[1].wrist, [.48, grip[1] - .05, grip[2]]], 'bar', 3);
            line([[0, .04, -.45], [0, 1.95, -.45], [0, 1.95, .24]], 'machine-upright', 5);
            line([grip, [0, 1.95, .24], [0, 1.95, -.45], [.6, 1.45, -.25]], 'cable', 1.5, '#aab8c8');
            line([[-.32, .69, .36], [.32, .69, .36]], 'thigh-pad', 10, '#465770');
        } else if (slug === 'machine-chest-press') {
            for (const a of pose.arms) {
                const pivot = [a.wrist[0], 1.90, .20];
                line([[pivot[0], .04, -.16], pivot, a.wrist], 'press-lever', 4);
                line([add(a.wrist, [0, -.09, 0]), add(a.wrist, [0, .09, 0])], 'handle', 5);
            }
        } else {
            const pivot = mix(pose.legs[0].knee, pose.legs[1].knee, .5), roller = add(mix(pose.legs[0].ankle, pose.legs[1].ankle, .5), [0, .015, .075]);
            line([[.36, .04, .52], [.36, pivot[1], pivot[2]]], 'machine-upright');
            line([[.36, pivot[1], pivot[2]], [.36, roller[1], roller[2]], [-.30, roller[1], roller[2]]], 'leg-lever');
            ball(roller, [.29, .065, .065], 'shin-roller', [56, 76, 99]);
            line([[-.3, .65, .16], [.3, .65, .16]], 'handle');
        }
    }
    if (slug === 'leg-press') {
        pad([0, .31, -.13], [0, .91, -.86], .24, 'backrest');
        const ankle = mix(pose.legs[0].ankle, pose.legs[1].ankle, .5), center = add(ankle, [0, -.015, .085]);
        face([add(center, [-.42, -.20, .20]), add(center, [.42, -.20, .20]), add(center, [.42, .20, -.20]), add(center, [-.42, .20, -.20])], 'footplate', '#456079');
        for (const side of sides) { line([[side * .53, .17, -.20], [side * .53, 1.15, .78]], 'sled-rail', 5); ball(add(center, [side * .52, 0, 0]), [.045, .18, .18]); }
        line([[-.60, .03, -.85], [-.60, .03, .8], [.6, .03, .8], [.6, .03, -.85]], 'base');
        for (const side of sides) line([[side * .28, .34, -.16], [side * .28, .55, -.16]], 'handle');
    }
    if (slug === 'lying-leg-curl') {
        pad([0, .53, -.78], [0, .53, .50], .25); stack(.57, .2);
        const ankle = mix(pose.legs[0].ankle, pose.legs[1].ankle, .5), roller = add(ankle, [0, .06, 0]);
        line([[.35, .625, .5293], [.35, roller[1], roller[2]], [-.30, roller[1], roller[2]]], 'leg-lever');
        ball(roller, [.29, .065, .065], 'ankle-roller', [56, 76, 99]);
        for (const side of sides) line([[side * .27, .45, -.62], [side * .27, .52, -.75]], 'handle');
    }
    return items;
}
