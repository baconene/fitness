/**
 * Hand-authored two-pose exercise demonstrations.
 *
 * Each entry holds the start (`a`) and end (`b`) position of the movement as
 * plain SVG markup on a 100x120 stage. Matching GIFs in public/images/exercises
 * provide the animated preview; the first pose is the accessible still preview.
 *
 * Adding an exercise means adding its slug here. Slugs must match
 * ExerciseSeeder. Unlisted exercises display an explicit unavailable state.
 */
import { archetypeLabel } from './exerciseArchetypes.js';

export const EXERCISE_DEMOS = {
    'bench-press': {
        label: 'Bench press: feet planted, upper back on the bench. Lower the bar under control, then press up.',
        a: `<path d="M12 82 H66 M20 83 V110 M61 83 V110"/>
            <circle cx="21" cy="68" r="7"/>
            <path d="M28 73 L57 73 L75 86 L80 109 H91"/>
            <path d="M34 73 L37 51 L40 29"/>
            <path d="M18 29 H64"/>
            <rect x="22" y="22" width="6" height="14" rx="1"/>
            <rect x="54" y="22" width="6" height="14" rx="1"/>`,
        b: `<path d="M12 82 H66 M20 83 V110 M61 83 V110"/>
            <circle cx="21" cy="68" r="7"/>
            <path d="M28 73 L57 73 L75 86 L80 109 H91"/>
            <path d="M34 73 L54 81 L45 61"/>
            <path d="M23 61 H69"/>
            <rect x="27" y="54" width="6" height="14" rx="1"/>
            <rect x="59" y="54" width="6" height="14" rx="1"/>`,
    },
    'bodyweight-squat': {
        label: 'Squat: hips back and down, chest tall, knees tracking over toes.',
        a: `<circle cx="50" cy="16" r="8"/>
            <path d="M50 24 V60"/>
            <path d="M50 34 L34 48"/><path d="M50 34 L66 48"/>
            <path d="M50 60 L44 86 L44 112"/><path d="M50 60 L56 86 L56 112"/>`,
        b: `<circle cx="52" cy="38" r="8"/>
            <path d="M52 46 V72"/>
            <path d="M52 52 L78 46"/><path d="M52 52 L78 56"/>
            <path d="M52 72 L38 86 L44 112"/><path d="M52 72 L66 86 L56 112"/>`,
    },
    'push-up': {
        label: 'Push-up: body in one line, elbows tucked, chest to the floor.',
        a: `<circle cx="84" cy="62" r="7"/>
            <path d="M18 96 L48 84 L76 70"/>
            <path d="M76 70 V102"/>
            <path d="M18 96 L14 106"/>
            <path d="M76 102 H86"/>`,
        b: `<circle cx="84" cy="80" r="7"/>
            <path d="M18 100 L48 94 L76 88"/>
            <path d="M76 88 L64 98 L78 104"/>
            <path d="M18 100 L14 108"/>
            <path d="M78 104 H88"/>`,
    },
    'dumbbell-curl': {
        label: 'Curl: elbows pinned to your sides, lift with the biceps only.',
        a: `<circle cx="50" cy="16" r="8"/>
            <path d="M50 24 V64"/>
            <path d="M50 34 L38 54 L38 78"/><path d="M50 34 L62 54 L62 78"/>
            <path d="M50 64 L44 88 L44 112"/><path d="M50 64 L56 88 L56 112"/>
            <rect x="32" y="76" width="12" height="5" rx="2"/>
            <rect x="56" y="76" width="12" height="5" rx="2"/>`,
        b: `<circle cx="50" cy="16" r="8"/>
            <path d="M50 24 V64"/>
            <path d="M50 34 L38 54 L46 36"/><path d="M50 34 L62 54 L54 36"/>
            <path d="M50 64 L44 88 L44 112"/><path d="M50 64 L56 88 L56 112"/>
            <rect x="40" y="32" width="12" height="5" rx="2"/>
            <rect x="48" y="32" width="12" height="5" rx="2"/>`,
    },
    plank: {
        label: 'Plank: forearms under shoulders, ribs down, one straight line.',
        a: `<circle cx="82" cy="66" r="7"/>
            <path d="M16 96 L48 86 L74 74"/>
            <path d="M74 74 L70 92 H88"/>
            <path d="M16 96 L12 106"/>`,
        b: `<circle cx="82" cy="68" r="7"/>
            <path d="M16 97 L48 88 L74 76"/>
            <path d="M74 76 L70 93 H88"/>
            <path d="M16 97 L12 107"/>`,
    },
    'steady-state-run': {
        label: 'Run: tall posture, relaxed arms, land under your hips.',
        a: `<circle cx="50" cy="16" r="8"/>
            <path d="M50 24 V62"/>
            <path d="M50 34 L36 46 L42 60"/><path d="M50 34 L64 44 L58 30"/>
            <path d="M50 62 L36 78 L34 100"/><path d="M50 62 L62 82 L70 98"/>`,
        b: `<circle cx="50" cy="16" r="8"/>
            <path d="M50 24 V62"/>
            <path d="M50 34 L64 46 L58 60"/><path d="M50 34 L36 44 L42 30"/>
            <path d="M50 62 L64 78 L66 100"/><path d="M50 62 L38 82 L30 98"/>`,
    },
};

/**
 * The demonstration for a slug, or null when none exists.
 *
 * Six movements have bespoke poses in EXERCISE_DEMOS; the rest are rendered
 * from a movement archetype. Both produce artwork under the same name in
 * public/images/exercises, so they resolve identically here.
 */
export const demoFor = (slug, name = null) => {
    const key = typeof slug === 'string' ? slug.toLowerCase().trim().replaceAll('_', '-').replaceAll(' ', '-') : '';
    // Bump when replacing public assets so returning browsers fetch the new artwork.
    const artworkVersion = 'wireframe-3';

    const readableName = name ?? key.replaceAll('-', ' ').replace(/^./, (c) => c.toUpperCase());
    const hand = Object.hasOwn(EXERCISE_DEMOS, key) ? EXERCISE_DEMOS[key] : null;
    const label = hand?.label ?? archetypeLabel(key, readableName);

    if (!label) {
        return null;
    }

    return {
        ...(hand ?? {}),
        label,
        slug: key,
        gif: `/images/exercises/${key}.gif?v=${artworkVersion}`,
        poster: `/images/exercises/${key}.png?v=${artworkVersion}`,
    };
};
