/** Dedicated apparatus and coordinated movement cycles for the eighth artwork revision. */
import { motionGeometry as g } from './exerciseMotionRefinements.js';
const { sides, add, sub, mul, unit, mix, cycle, bend, arm, leg, trunk, feet, sample } = g;
const tau = Math.PI * 2;
export const APPARATUS_DEMOS = ['rowing-machine', 'shoulder-press', 'stair-climber', 'skull-crusher', 'mountain-climber', 'seated-calf-raise', 'pull-up', 'swimming', 't-bar-row', 'thoracic-rotation', 'triceps-bench-dip', 'glute-bridge', 'burpee', 'pec-deck', 'toe-touch-crunch'];
export const FORM_REVIEW_DEMOS = ['t-bar-row', 'pec-deck', 'toe-touch-crunch', 'mountain-climber', 'burpee'];

export function apparatusPose(pose, slug, phase) {
    const t = (1 - Math.cos(tau * phase)) / 2;
    pose.prone = false; pose.horizontal = false; pose.palms = false;
    pose.grip = !['swimming', 'thoracic-rotation', 'glute-bridge', 'mountain-climber', 'burpee', 'triceps-bench-dip'].includes(slug);
    trunk(pose, [0, .94, 0]); feet(pose);
    if (slug === 'rowing-machine') {
        const [slide, tilt, pull] = sample([[0, 0, .18, 0], [.26, 1, .18, 0], [.40, 1, -.18, 1], [.54, 1, -.18, 0], [.64, 1, .18, 0], [1, 0, .18, 0]], phase);
        trunk(pose, [0, .53, .20 - .40 * slide], [0, Math.cos(tilt), Math.sin(tilt)]);
        pose.legs = sides.map(side => leg(add(pose.pelvis, [side * .12, 0, 0]), [side * .14, .22, .58], [0, 1, 0]));
        const shoulderZ = pose.arms[0].shoulder[2];
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .08, .90, shoulderZ + .50 - .29 * pull], sides[i], [sides[i], -.3, -1]));
    } else if (slug === 'shoulder-press') {
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * (.47 - .31 * t), 1.53 + .46 * t, .04], sides[i], [sides[i], -1, 0]));
    } else if (slug === 'skull-crusher') {
        pose.horizontal = true;
        trunk(pose, [0, .61, .20], [0, 0, -1]); feet(pose, .23, .76);
        pose.arms = pose.arms.map(a => {
            const elbow = add(a.shoulder, [0, .30, -.015]), angle = .06 + 1.78 * t;
            return { shoulder: a.shoulder, elbow, wrist: add(elbow, [0, .28 * Math.cos(angle), -.28 * Math.sin(angle)]) };
        });
    } else if (slug === 'pull-up') {
        trunk(pose, [0, 1.12 + .42 * t, 0]); feet(pose, .14, .02, .285 + .42 * t);
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .31, 2.20, 0], sides[i], [sides[i], -.4, .15]));
    } else if (slug === 'pec-deck') {
        trunk(pose, [0, .62, -.04]);
        pose.legs = sides.map(side => leg([side * .12, .62, -.04], [side * .17, .08, .46], [0, 1, 0]));
        const sweep = .10 + 2.0 * t;
        pose.arms = pose.arms.map((a, i) => {
            const elbow = add(a.shoulder, [sides[i] * .30 * Math.cos(sweep), 0, .30 * Math.sin(sweep)]);
            return { shoulder: a.shoulder, elbow, wrist: add(elbow, [0, .28, 0]) };
        });
    } else if (slug === 'toe-touch-crunch') {
        pose.horizontal = true; pose.grip = false;
        const angle = .02 + .35 * t;
        trunk(pose, [0, .14, .20], [0, Math.sin(angle), -Math.cos(angle)]);
        pose.legs = sides.map(side => {
            const hip = add(pose.pelvis, [side * .12, 0, 0]);
            return { hip, knee: add(hip, [0, .43, 0]), ankle: add(hip, [0, .86, 0]) };
        });
        pose.footTips = pose.legs.map(l => add(l.ankle, [0, 0, -.15]));
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, add(a.shoulder, [0, .448, .336]), sides[i], [sides[i], 0, 0]));
    } else if (slug === 'seated-calf-raise') {
        trunk(pose, [0, .61, 0]);
        const angle = .20 + .70 * t;
        pose.footTips = sides.map(side => [side * .15, .15, .56]);
        pose.legs = sides.map((side, i) => {
            const ankle = add(pose.footTips[i], [0, .14 * Math.sin(angle), -.14 * Math.cos(angle)]);
            return { hip: [side * .12, .61, 0], knee: [side * .15, ankle[1] + .429, ankle[2] - .015], ankle };
        });
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, add(pose.legs[i].knee, [sides[i] * .07, .15, -.08]), sides[i]));
    } else if (slug === 'stair-climber') {
        trunk(pose, [0, 1.15 + .008 * Math.cos(tau * phase * 2), 0]);
        pose.legs = sides.map((side, i) => {
            const u = cycle(phase + i * .5), swing = Math.max(0, (u - .60) / .40);
            const progress = swing ? .60 * (1 - g.smooth(swing)) : u;
            const ankle = [side * .14, .56 - .36 * progress + .10 * Math.sin(Math.PI * swing), .30 - .45 * progress];
            return leg(add(pose.pelvis, [side * .12, -.025, 0]), ankle);
        });
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .34, 1.58, .48], sides[i], [sides[i], -.8, 0]));
    } else if (slug === 'mountain-climber') {
        pose.prone = true; pose.palms = true;
        const axis = unit([0, .16, -.987]);
        trunk(pose, [0, .52, .17], axis);
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .25, .065, -.37], sides[i], [sides[i], .2, .7]));
        pose.legs = sides.map((side, i) => {
            const drive = Math.max(0, Math.sin(tau * phase + i * Math.PI)) ** 2;
            const hip = add(pose.pelvis, [side * .12, 0, 0]), angle = 1.15 - 2.20 * drive;
            const knee = add(hip, [0, -.43 * Math.cos(angle), .43 * Math.sin(angle)]);
            const ankleY = .08 + .06 * drive;
            const ankle = [hip[0], ankleY, knee[2] + Math.sqrt(.43 ** 2 - (knee[1] - ankleY) ** 2)];
            return { hip, knee, ankle };
        });
    } else if (slug === 'swimming') {
        pose.prone = true; pose.horizontal = true;
        const roll = .10 * Math.sin(tau * phase);
        trunk(pose, [0, .51, .12], [0, 0, -1], [Math.cos(roll), Math.sin(roll), 0]);
        pose.arms = pose.arms.map((a, i) => {
            const u = cycle(phase + i * .5);
            const [y, z] = sample([[0, 0, -.56], [.20, -.23, -.36], [.45, -.12, .46], [.62, .22, .28], [.85, .16, -.40], [1, 0, -.56]], u);
            return arm(a.shoulder, add(a.shoulder, [sides[i] * .03, y, z]), sides[i], [sides[i] * .3, u < .45 ? -1 : 1, 0]);
        });
        pose.legs = sides.map((side, i) => {
            const hip = add(pose.pelvis, [side * .12, 0, 0]), kick = .10 * Math.sin(tau * phase * 2 + i * Math.PI);
            const axis = [0, Math.sin(kick), Math.cos(kick)], knee = add(hip, mul(axis, .43));
            return { hip, knee, ankle: add(knee, mul(axis, .43)) };
        });
        pose.footTips = pose.legs.map(l => add(l.ankle, [0, 0, .15]));
    } else if (slug === 't-bar-row') {
        trunk(pose, [0, .87, -.15], unit([0, .62, .785])); feet(pose, .25);
        const angle = .40 + .20 * t, pivot = [0, .055, -1.35], grip = add(pivot, [0, 1.75 * Math.sin(angle), 1.75 * Math.cos(angle)]);
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, add(grip, [sides[i] * .08, 0, 0]), sides[i], [sides[i], -.3, -1]));
    } else if (slug === 'thoracic-rotation') {
        pose.prone = true; pose.palms = true;
        const turn = 1.12 * t, right = [Math.cos(turn), Math.sin(turn), 0];
        trunk(pose, [0, .56, .28], [0, 0, -1], right);
        pose.pelvisRight = [1, 0, 0];
        pose.legs = sides.map(side => ({ hip: [side * .12, .56, .28], knee: [side * .13, .13, .28], ankle: [side * .13, .085, .705] }));
        pose.arms[0] = arm(pose.arms[0].shoulder, [-.23, .065, -.23], -1, [-1, 0, 0]);
        const a = pose.arms[1], wrist = add(pose.head, mul(right, .075));
        pose.arms[1] = arm(a.shoulder, wrist, 1, right);
    } else if (slug === 'triceps-bench-dip') {
        pose.palms = true;
        trunk(pose, [0, .54 - .20 * t, .16], unit([0, 1, -.08])); feet(pose, .18, .72);
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .31, .56, -.15], sides[i], [sides[i] * .2, -.1, -1]));
    } else if (slug === 'glute-bridge') {
        pose.horizontal = true;
        const angle = .05 + .55 * t, up = [0, -Math.sin(angle), -Math.cos(angle)], neck = [0, .13, -.38];
        trunk(pose, sub(neck, mul(up, .63)), up);
        pose.head = [0, .13, -.54];
        pose.legs = sides.map(side => leg(add(pose.pelvis, [side * .12, 0, 0]), [side * .18, .08, .73], [0, 1, 0]));
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .29, .065, .25], sides[i], [sides[i], .2, 0]));
    } else if (slug === 'burpee') {
        const [hipY, hipZ, tilt, ankleZ, ankleY, handY, handZ] = sample([
            [0, .94, 0, 0, .05, .08, .90, .05],
            [.12, .40, -.05, 1.25, .05, .08, .065, .55],
            [.20, .62, -.16, 1.62, -.48, .20, .065, .55],
            [.28, .346, -.214, 1.245, -1.035, .08, .065, .55],
            [.39, .21, -.175, 1.46, -1.025, .08, .065, .55],
            [.50, .346, -.214, 1.245, -1.035, .08, .065, .55],
            [.57, .62, -.16, 1.62, -.48, .20, .065, .55],
            [.64, .40, -.05, 1.25, .05, .08, .065, .55],
            [.78, 1.19, 0, 0, .05, .33, 2.22, .07],
            [.89, .80, -.06, .15, .05, .08, 1.15, .24],
            [1, .94, 0, 0, .05, .08, .90, .05],
        ], phase);
        trunk(pose, [0, hipY, hipZ], [0, Math.cos(tilt), Math.sin(tilt)]);
        feet(pose, .18, ankleZ, ankleY);
        pose.legs.forEach(l => {
            const delta = sub(l.ankle, l.hip);
            l.knee = bend(l.hip, l.ankle, .43, .43, [0, delta[2], -delta[1]]);
        });
        pose.arms = pose.arms.map((a, i) => arm(a.shoulder, [sides[i] * .25, handY, handZ], sides[i], [sides[i] * .65, .55, -.6]));
        pose.palms = handY < .12;
    }
    return pose;
}

export function apparatusEquipment(pose, slug, phase) {
    const items = [];
    const line = (points, role = 'frame', width = 4, color = '#839db5') => items.push({ type: 'line', points, role, width, color });
    const face = (points, role = 'pad', color = '#30495e') => items.push({ type: 'face', points, role, color });
    const ball = (center, radii, role = 'weight', color = [66, 92, 116], basis) => items.push({ type: 'ellipsoid', center, radii, role, color, basis });
    const pad = (y, z0, z1, width = .22, role = 'bench') => {
        face([[-width, y, z0], [width, y, z0], [width, y, z1], [-width, y, z1]], role);
        for (const z of [z0 + .06, z1 - .06]) {
            line([[0, .04, z], [0, y, z]]);
            line([[-width - .10, .04, z], [width + .10, .04, z]]);
        }
    };
    const dumbbell = center => {
        line([add(center, [0, 0, -.10]), add(center, [0, 0, .10])], 'grip', 3, '#c4d5e6');
        for (const sign of sides) ball(add(center, [0, 0, sign * .10]), [.065, .065, .03]);
    };
    if (['glute-bridge', 'thoracic-rotation', 'mountain-climber', 'burpee', 'toe-touch-crunch'].includes(slug)) face([[-.50, -.008, -1.30], [.50, -.008, -1.30], [.50, -.008, 1.30], [-.50, -.008, 1.30]], 'ground', '#101e2c');
    if (['shoulder-press', 'skull-crusher'].includes(slug)) pose.arms.forEach(a => dumbbell(a.wrist));
    if (slug === 'skull-crusher') pad(.49, -.75, .38);
    if (slug === 'pull-up') {
        line([[-.65, 2.20, 0], [.65, 2.20, 0]], 'pull-up-bar', 4, '#b9cfe5');
        for (const side of sides) { line([[side * .65, .04, 0], [side * .65, 2.20, 0]], 'upright'); line([[side * .65, .04, -.4], [side * .65, .04, .4]], 'base'); }
    }
    if (slug === 'triceps-bench-dip') pad(.495, -.45, -.10, .5, 'hand-support');
    if (slug === 'rowing-machine') {
        line([[0, .32, -.55], [0, .32, .78]], 'seat-rail', 6);
        for (const z of [-.55, .85]) line([[-.36, .06, z], [.36, .06, z]], 'base', 5);
        const seatZ = pose.pelvis[2];
        face([[-.23, .42, seatZ - .14], [.23, .42, seatZ - .14], [.23, .42, seatZ + .14], [-.23, .42, seatZ + .14]], 'sliding-seat');
        ball([0, .46, 1.04], [.13, .34, .34], 'flywheel', [53, 75, 95]);
        line([[0, .09, .85], [0, .65, 1.04], [0, .78, .97]], 'frame');
        face([[-.14, .79, .93], [.14, .79, .93], [.14, .91, 1.02], [-.14, .91, 1.02]], 'display', '#335676');
        for (const l of pose.legs) {
            const x = l.ankle[0];
            face([[x - .09, .12, .51], [x + .09, .12, .51], [x + .09, .22, .77], [x - .09, .22, .77]], 'footplate');
            line([[x - .09, .21, .64], [x + .09, .21, .64]], 'foot-strap', 3, '#98aec3');
        }
        line(pose.arms.map(a => a.wrist), 'handle', 5, '#c2d6e6');
        line([mix(pose.arms[0].wrist, pose.arms[1].wrist, .5), [0, .90, .99], [0, .70, 1.04]], 'chain', 1.5, '#b1c4d9');
    }
    if (slug === 'stair-climber') {
        for (const side of sides) {
            const x = side * .40;
            line([[x, .04, -.35], [x, .04, .8], [x, .70, .8]], 'machine-side', 5);
            line([[x, .60, .60], [x, 1.64, .60], [side * .34, 1.58, .48], [side * .34, 1.40, .10]], 'handrail', 4);
        }
        const offset = cycle(phase * 2) / 2;
        for (let i = 1; i < 5; i++) {
            const y = .12 + i * .18 - offset * .36, z = -.15 + i * .225 - offset * .45;
            face([[-.36, y, z - .11], [.36, y, z - .11], [.36, y, z + .115], [-.36, y, z + .115]], 'step', '#456077');
            face([[-.36, y - .18, z - .11], [.36, y - .18, z - .11], [.36, y, z - .11], [-.36, y, z - .11]], 'riser', '#25394c');
        }
        face([[-.20, 1.66, .56], [.20, 1.66, .56], [.20, 1.82, .70], [-.20, 1.82, .70]], 'display', '#335676');
    }
    if (slug === 'pec-deck') {
        pad(.50, -.30, .10, .24, 'seat');
        face([[-.24, .55, -.16], [.24, .55, -.16], [.24, 1.30, -.16], [-.24, 1.30, -.16]], 'back-pad', '#2b4358');
        line([[0, .04, -.30], [0, .60, -.30], [0, 1.65, -.30]], 'frame', 5);
        line([[-.34, .04, -.30], [.34, .04, -.30]], 'base');
        pose.arms.forEach(a => {
            const pivot = [a.shoulder[0], 1.65, a.shoulder[2]], end = [a.elbow[0], 1.65, a.elbow[2]];
            line([pivot, end, a.elbow], 'swing-arm', 4);
            ball(add(a.elbow, [0, .13, .035]), [.06, .15, .045], 'forearm-pad', [51, 72, 94]);
            line([a.wrist, add(a.wrist, [0, 0, .08])], 'grip', 4);
            line([[0, 1.65, -.30], pivot], 'frame', 4);
        });
        for (const y of [.86, .98, 1.10]) {
            face([[-.20, y, -.52], [.20, y, -.52], [.20, y + .09, -.52], [-.20, y + .09, -.52]], 'weight-stack', '#25394c');
        }
    }
    if (slug === 'seated-calf-raise') {
        pad(.50, -.24, .12, .25, 'seat');
        pad(.115, .49, .65, .35, 'toe-platform');
        const knee = mix(pose.legs[0].knee, pose.legs[1].knee, .5), top = add(knee, [0, .065, 0]);
        ball(top, [.29, .055, .11], 'thigh-pad', [51, 72, 94]);
        line([[0, .04, -.32], [0, .70, -.32], top, add(top, [0, 0, .28])], 'loaded-lever', 5);
        line([add(top, [-.42, 0, .20]), add(top, [.42, 0, .20])], 'weight-axle');
        for (const side of sides) ball(add(top, [side * .34, 0, .20]), [.035, .13, .13]);
        pose.arms.forEach(a => line([add(a.wrist, [0, -.08, 0]), a.wrist], 'handle', 4));
    }
    if (slug === 't-bar-row') {
        const pivot = [0, .055, -1.35], grip = mix(pose.arms[0].wrist, pose.arms[1].wrist, .5), axis = unit(sub(grip, pivot));
        line([pivot, add(grip, mul(axis, .23))], 'anchored-bar', 4, '#b7cde1');
        line(pose.arms.map(a => a.wrist), 'handle', 5);
        ball(add(grip, mul(axis, .10)), [.18, .045, .18], 'weight', [63, 84, 109], [[1, 0, 0], axis, [0, -axis[2], axis[1]]]);
        face([[-.22, .01, -1.54], [.22, .01, -1.54], [.22, .01, -1.18], [-.22, .01, -1.18]], 'anchor');
    }
    if (slug === 'swimming') {
        face([[-.65, .39, -1.55], [.65, .39, -1.55], [.65, .39, 1.50], [-.65, .39, 1.50]], 'ground', '#122c40');
        for (const side of sides) line([[side * .57, .40, -1.55], [side * .57, .40, 1.5]], 'lane-rope', 2, '#477e9f');
        for (let i = 0; i < 4; i++) {
            const z = -.9 + i * .6 + .05 * Math.sin(tau * phase);
            line([[-.48, .40, z], [-.36, .40, z + .04], [-.26, .40, z]], 'ripple', 1, '#31546c');
        }
    }
    return items;
}
