/**
 * Movement archetypes for the demonstration renderer.
 *
 * Authoring a bespoke pose per exercise does not scale, and many exercises
 * share a motion path anyway: a back squat and a goblet squat differ in what
 * the arms hold, not in how the hips and knees travel. So each exercise maps to
 * an archetype (the motion) plus a variation (stance, grip, load), and
 * exerciseDemoScene renders that.
 *
 * The six originally hand-authored slugs are deliberately absent — they keep
 * their bespoke poses.
 */

/** Coaching line per archetype; `%s` is replaced with the exercise name. */
const LABELS = {
    squat: '%s: hips back and down, chest tall, knees tracking over toes.',
    hinge: '%s: push the hips back, keep a flat back, feel the hamstrings load.',
    lunge: '%s: long stride, rear knee drops straight down, front shin vertical.',
    bench: '%s: shoulder blades pinned, lower under control, press without flaring.',
    fly: '%s: soft elbows, open the chest wide, squeeze back to the middle.',
    pushup: '%s: body in one line, elbows tucked, chest toward the floor.',
    dip: '%s: lean slightly forward, lower to a comfortable depth, press up.',
    press: '%s: brace the ribs down, press overhead, finish with biceps by the ears.',
    row: '%s: hinge and hold, pull to the ribs, squeeze the shoulder blades.',
    pulldown: '%s: start from a long hang, drive the elbows down to the ribs.',
    curl: '%s: elbows pinned to your sides, lift with the biceps only.',
    extension: '%s: keep the upper arm still, extend through the elbow.',
    raise: '%s: lead with the elbows, stop at shoulder height, lower slowly.',
    bridge: '%s: drive through the heels, squeeze the glutes, ribs stay down.',
    legcurl: '%s: control the lowering, keep the hips pinned down.',
    legextension: '%s: extend the knees smoothly, pause at the top.',
    calf: '%s: rise onto the toes, pause, lower under control for a full stretch.',
    hold: '%s: hold the position, breathe steadily, keep everything braced.',
    crunch: '%s: curl the ribs toward the hips, do not pull on the neck.',
    twist: '%s: rotate from the ribs, keep the hips facing forward.',
    legraise: '%s: keep the lower back flat, lift with the abs not momentum.',
    rollout: '%s: ribs down and hips tucked, extend only as far as you can control.',
    run: '%s: tall posture, relaxed arms, land under your hips.',
    cycle: '%s: steady cadence, smooth circles, keep the torso quiet.',
    rowmachine: '%s: drive with the legs, then swing back, then pull.',
    jump: '%s: load the hips, explode up, land soft and quiet.',
    swing: '%s: snap the hips through, the arms just follow.',
    carry: '%s: stand tall, ribs down, walk with controlled steps.',
    quadruped: '%s: move slowly through the spine, keep the hips level.',
    stretch: '%s: ease into the position and breathe, never force the range.',
};

/**
 * slug → { motion, load, variant }
 *
 * motion  drives the pose path, load what the hands hold, variant tweaks
 * stance or angle within the motion.
 */
export const ARCHETYPES = {
    // ---- Chest ----
    'bench_press': { motion: 'bench', load: 'barbell' },
    'incline-bench-press': { motion: 'bench', load: 'barbell', variant: 'incline' },
    'decline-bench-press': { motion: 'bench', load: 'barbell', variant: 'decline' },
    'incline-dumbbell-press': { motion: 'bench', load: 'dumbbell', variant: 'incline' },
    'machine-chest-press': { motion: 'bench', load: 'machine', variant: 'seated' },
    'chest-fly': { motion: 'fly', load: 'dumbbell' },
    'cable-crossover': { motion: 'fly', load: 'cable', variant: 'standing' },
    'pec-deck': { motion: 'fly', load: 'machine', variant: 'seated' },
    'dumbbell-pullover': { motion: 'fly', load: 'dumbbell', variant: 'pullover' },
    'incline-push-up': { motion: 'pushup', variant: 'incline' },
    'close-grip-push-up': { motion: 'pushup', variant: 'narrow' },
    'dips': { motion: 'dip', variant: 'bars' },
    'triceps-bench-dip': { motion: 'dip', variant: 'bench' },

    // ---- Back ----
    'bent-over-row': { motion: 'row', load: 'barbell' },
    'pendlay-row': { motion: 'row', load: 'barbell', variant: 'floor' },
    't-bar-row': { motion: 'row', load: 'barbell', variant: 'narrow' },
    'single-arm-dumbbell-row': { motion: 'row', load: 'dumbbell', variant: 'single' },
    'seated-cable-row': { motion: 'row', load: 'cable', variant: 'seated' },
    'inverted-row': { motion: 'row', variant: 'supine' },
    'pull-up': { motion: 'pulldown', variant: 'bar' },
    'chin-up': { motion: 'pulldown', variant: 'bar' },
    'lat-pulldown': { motion: 'pulldown', load: 'machine', variant: 'seated' },
    'straight-arm-pulldown': { motion: 'raise', load: 'cable', variant: 'straightarm' },
    'deadlift': { motion: 'hinge', load: 'barbell', variant: 'floor' },
    'rack-pull': { motion: 'hinge', load: 'barbell', variant: 'high' },
    'sumo-deadlift': { motion: 'hinge', load: 'barbell', variant: 'wide' },
    'romanian-deadlift': { motion: 'hinge', load: 'barbell' },
    'single-leg-romanian-deadlift': { motion: 'hinge', load: 'dumbbell', variant: 'singleleg' },
    'good-morning': { motion: 'hinge', load: 'barbell', variant: 'back' },
    'reverse-hyperextension': { motion: 'hold', variant: 'prone' },
    'superman-hold': { motion: 'hold', variant: 'prone' },

    // ---- Shoulders ----
    'overhead-press': { motion: 'press', load: 'barbell' },
    'shoulder-press': { motion: 'press', load: 'dumbbell' },
    'arnold-press': { motion: 'press', load: 'dumbbell', variant: 'rotate' },
    'landmine-press': { motion: 'press', load: 'barbell', variant: 'single' },
    'pike-push-up': { motion: 'pushup', variant: 'pike' },
    'handstand-hold': { motion: 'hold', variant: 'inverted' },
    'lateral-raise': { motion: 'raise', load: 'dumbbell' },
    'front-raise': { motion: 'raise', load: 'dumbbell', variant: 'front' },
    'rear-delt-fly': { motion: 'raise', load: 'dumbbell', variant: 'bent' },
    'face-pull': { motion: 'raise', load: 'cable', variant: 'face' },
    'upright-row': { motion: 'raise', load: 'barbell', variant: 'upright' },

    // ---- Arms ----
    'barbell-curl': { motion: 'curl', load: 'barbell' },
    'hammer-curl': { motion: 'curl', load: 'dumbbell', variant: 'neutral' },
    'incline-dumbbell-curl': { motion: 'curl', load: 'dumbbell', variant: 'incline' },
    'preacher-curl': { motion: 'curl', load: 'barbell', variant: 'preacher' },
    'concentration-curl': { motion: 'curl', load: 'dumbbell', variant: 'seated' },
    'cable-curl': { motion: 'curl', load: 'cable' },
    'reverse-curl': { motion: 'curl', load: 'barbell', variant: 'pronated' },
    'wrist-curl': { motion: 'curl', load: 'dumbbell', variant: 'wrist' },
    'triceps-pushdown': { motion: 'extension', load: 'cable' },
    'overhead-triceps-extension': { motion: 'extension', load: 'dumbbell', variant: 'overhead' },
    'skull-crusher': { motion: 'extension', load: 'dumbbell', variant: 'supine' },
    'farmer-carry': { motion: 'carry', load: 'dumbbell' },
    'suitcase-carry': { motion: 'carry', load: 'dumbbell', variant: 'single' },

    // ---- Legs ----
    'back-squat': { motion: 'squat', load: 'barbell', variant: 'back' },
    'front-squat': { motion: 'squat', load: 'barbell', variant: 'front' },
    'goblet-squat': { motion: 'squat', load: 'dumbbell', variant: 'goblet' },
    'leg-press': { motion: 'squat', load: 'machine', variant: 'seated' },
    'cossack-squat': { motion: 'squat', variant: 'lateral' },
    'bulgarian-split-squat': { motion: 'lunge', load: 'dumbbell', variant: 'rear' },
    'walking-lunge': { motion: 'lunge', load: 'dumbbell' },
    'step-up': { motion: 'lunge', load: 'dumbbell', variant: 'step' },
    'glute-bridge': { motion: 'bridge' },
    'hip-thrust': { motion: 'bridge', load: 'barbell', variant: 'bench' },
    'leg-extension': { motion: 'legextension', load: 'machine' },
    'lying-leg-curl': { motion: 'legcurl', load: 'machine' },
    'nordic-hamstring-curl': { motion: 'legcurl', variant: 'kneeling' },
    'calf-raise': { motion: 'calf' },
    'seated-calf-raise': { motion: 'calf', variant: 'seated' },

    // ---- Core ----
    'side-plank': { motion: 'hold', variant: 'side' },
    'hollow-body-hold': { motion: 'hold', variant: 'hollow' },
    'dead-bug': { motion: 'hold', variant: 'deadbug' },
    'bird-dog': { motion: 'quadruped', variant: 'birddog' },
    'pallof-press': { motion: 'hold', load: 'cable', variant: 'pallof' },
    'bicycle-crunch': { motion: 'crunch', variant: 'bicycle' },
    'toe-touch-crunch': { motion: 'crunch', variant: 'toetouch' },
    'russian-twist': { motion: 'twist', variant: 'seated' },
    'cable-woodchop': { motion: 'twist', load: 'cable', variant: 'standing' },
    'hanging-leg-raise': { motion: 'legraise', variant: 'hanging' },
    'ab-wheel-rollout': { motion: 'rollout' },
    'mountain-climber': { motion: 'pushup', variant: 'climber' },

    // ---- Cardio ----
    'sprint-intervals': { motion: 'run', variant: 'sprint' },
    'incline-treadmill-walk': { motion: 'run', variant: 'walk' },
    'jump-rope': { motion: 'run', variant: 'rope' },
    'swimming': { motion: 'run', variant: 'swim' },
    'battle-ropes': { motion: 'raise', variant: 'battle' },
    'cycling': { motion: 'cycle' },
    'elliptical-trainer': { motion: 'cycle', variant: 'elliptical' },
    'stair-climber': { motion: 'cycle', variant: 'stairs' },
    'rowing-machine': { motion: 'rowmachine' },

    // ---- Plyometrics ----
    'box-jump': { motion: 'jump', variant: 'box' },
    'broad-jump': { motion: 'jump', variant: 'broad' },
    'jump-squat': { motion: 'jump' },
    'burpee': { motion: 'jump', variant: 'burpee' },
    'clap-push-up': { motion: 'pushup', variant: 'clap' },
    'kettlebell-swing': { motion: 'swing', load: 'dumbbell' },

    // ---- Mobility ----
    'cat-cow': { motion: 'quadruped' },
    'hamstring-stretch': { motion: 'stretch', variant: 'hamstring' },
    'hip-flexor-stretch': { motion: 'stretch', variant: 'hipflexor' },
    'shoulder-dislocate': { motion: 'stretch', variant: 'shoulder' },
    'thoracic-rotation': { motion: 'quadruped', variant: 'thoracic' },
    'worlds-greatest-stretch': { motion: 'stretch', variant: 'lunge' },
};

/**
 * Which muscle group the renderer tints for each motion, so a demonstration
 * shows what it trains rather than being uniformly grey.
 */
const FOCUS = {
    squat: 'legs', hinge: 'legs', lunge: 'legs', bridge: 'legs', legcurl: 'legs',
    legextension: 'legs', calf: 'legs', jump: 'legs', swing: 'legs', cycle: 'legs', run: 'legs',
    bench: 'chest', fly: 'chest', pushup: 'chest', dip: 'chest',
    press: 'arms', curl: 'arms', extension: 'arms', raise: 'arms', carry: 'arms',
    row: 'back', pulldown: 'back', rowmachine: 'back',
    hold: 'core', crunch: 'core', twist: 'core', legraise: 'core', rollout: 'core',
    quadruped: 'core', stretch: 'core',
};

/** The archetype for a slug, or null when it is hand-authored or unknown. */
export const archetypeFor = (slug) => ARCHETYPES[slug] ?? null;

/** Muscle group to tint, or null for the hand-authored slugs. */
export const focusFor = (slug) => {
    const spec = ARCHETYPES[slug];

    return spec ? (FOCUS[spec.motion] ?? null) : null;
};

/** What the hands are holding, so the renderer can draw it. */
export const loadFor = (slug) => ARCHETYPES[slug]?.load ?? null;

/** Coaching line for an archetype-driven exercise. */
const MOVEMENT_CUES = {
    'ab-wheel-rollout': 'knees grounded, roll the wheel forward with a braced trunk, then return under control.',
    'battle-ropes': 'hold a soft-knee stance and alternate your arms to send waves down both ropes.',
    'bicycle-crunch': 'alternate the bent knee and opposite shoulder while extending the other leg; keep your hands light at your temples.',
    'bird-dog': 'from hands and knees, extend opposite arm and leg, return, then switch sides with your trunk steady.',
    'box-jump': 'load your hips, jump onto the box, land softly, stand tall, then step down to reset.',
    'broad-jump': 'swing your arms, jump forward, land softly on both feet, then reset your stance.',
    'burpee': 'squat down, move to plank, lower and press up, bring your feet in, then jump and land softly.',
    'cable-crossover': 'stand between the pulleys and bring the handles together in front of your chest with soft elbows.',
    'face-pull': 'pull the cable handles toward your face, opening your elbows, then return under control.',
    'straight-arm-pulldown': 'keep a slight elbow bend as you draw the cable down toward your thighs.',
    'triceps-pushdown': 'keep your elbows beside your ribs and extend them to press the cable handles down.',
    'pallof-press': 'stand sideways to the cable and press forward without letting your torso rotate.',
    'cable-woodchop': 'draw the high cable diagonally across your body with control, then return.',
};

export const archetypeLabel = (slug, name) => {
    const spec = ARCHETYPES[slug];

    if (Object.hasOwn(MOVEMENT_CUES, slug)) return `${name}: ${MOVEMENT_CUES[slug]}`;

    return spec ? (LABELS[spec.motion] ?? '%s: move with control through the full range.').replace('%s', name) : null;
};
