<script setup>
/**
 * Anatomical hunter figure drawn entirely from SVG paths so any muscle
 * group can be lit up from data.
 *
 * The body is symmetrical: one half is authored and mirrored across the
 * x = 60 centreline, with centred groups (head, neck, abs, spine) drawn once.
 */
import { computed, useId } from 'vue';

const props = defineProps({
    view: { type: String, default: 'front' }, // 'front' | 'back'
    primary: { type: Array, default: () => [] },
    secondary: { type: Array, default: () => [] },
});

const uid = useId();
const glowId = `muscle-glow-${uid}`;

const state = (muscle) => {
    if (props.primary.includes(muscle)) {
        return 'primary';
    }

    return props.secondary.includes(muscle) ? 'secondary' : 'idle';
};

const FILL = {
    primary: 'rgba(54, 163, 255, 0.45)',
    secondary: 'rgba(118, 87, 255, 0.26)',
    idle: 'rgba(180, 200, 235, 0.035)',
};

const STROKE = {
    primary: 'rgb(96, 190, 255)',
    secondary: 'rgba(161, 140, 255, 0.85)',
    idle: 'rgba(130, 144, 170, 0.45)',
};

/** Binds fill/stroke/filter for a muscle group in one go. */
const bind = (muscle) => {
    const s = state(muscle);

    return {
        fill: FILL[s],
        stroke: STROKE[s],
        'stroke-width': s === 'idle' ? 0.7 : 1,
        filter: s === 'primary' ? `url(#${glowId})` : undefined,
    };
};

/** Structural outlines (head, neck, hands, feet) never light up. */
const inert = {
    fill: 'rgba(180, 200, 235, 0.03)',
    stroke: 'rgba(130, 144, 170, 0.4)',
    'stroke-width': 0.7,
};

const isFront = computed(() => props.view === 'front');

/** Mirror the authored half onto the other side of the figure. */
const sides = ['', 'translate(120,0) scale(-1,1)'];
</script>

<template>
    <svg viewBox="0 0 120 300" class="h-full w-full" role="img" :aria-label="`${view} view muscle map`">
        <defs>
            <filter :id="glowId" x="-60%" y="-60%" width="220%" height="220%">
                <feGaussianBlur stdDeviation="2.4" result="blur" />
                <feMerge>
                    <feMergeNode in="blur" />
                    <feMergeNode in="SourceGraphic" />
                </feMerge>
            </filter>
        </defs>

        <!-- Head + neck -->
        <ellipse cx="60" cy="17" rx="9.5" ry="11.5" v-bind="inert" />
        <path d="M53 28 L53 38 Q60 42.5 67 38 L67 28 Z" v-bind="inert" />

        <!-- Mirrored half -->
        <g v-for="(transform, i) in sides" :key="i" :transform="transform">
            <!-- Trapezius: shoulder slope from the front, neck-to-blade diamond half from behind -->
            <path
                v-if="isFront"
                d="M59 38 Q52 39 47 42 Q40 46 36 52 Q42 44 48 40 Q54 37.5 59 38 Z"
                v-bind="bind('shoulders')"
            />
            <path v-else d="M61 38 Q57 46 55.5 56 Q60.5 59 65 56 Q63.5 46 61 38 Z" v-bind="bind('upper_back')" />

            <!-- Deltoid cap -->
            <path
                d="M50 42 Q35 44.5 30 54 Q26 64 30 72.5 Q40 71.5 46 61.5 Q49.5 51.5 50 42 Z"
                v-bind="bind('shoulders')"
            />

            <!-- Pectoral (front) / Latissimus dorsi (back) -->
            <path
                v-if="isFront"
                d="M51 46 Q60 44 61.5 52 L61 77 Q50 79 45 70 Q42 57 46 49 Q48 46 51 46 Z"
                v-bind="bind('chest')"
            />
            <path
                v-else
                d="M46 55 Q38 72 41 92 Q43.5 106.5 50.5 116.5 L54.5 60 Q50 56 46 55 Z"
                v-bind="bind('lats')"
            />

            <!-- Biceps (front) / Triceps (back) -->
            <path
                v-if="isFront"
                d="M30 72.5 Q25 82.5 25.5 94.5 L35 96.5 Q37.5 84.5 34.5 72.5 Z"
                v-bind="bind('biceps')"
            />
            <path
                v-else
                d="M30 72.5 Q24 82.5 24.5 95.5 L34.5 97.5 Q37 84.5 34 72.5 Z"
                v-bind="bind('triceps')"
            />

            <!-- Forearm -->
            <path
                d="M25 97.5 Q20.5 112.5 23 128.5 L32 130.5 Q35 113.5 34 99.5 Z"
                v-bind="bind('forearms')"
            />

            <!-- Hand -->
            <ellipse cx="27" cy="137" rx="4.8" ry="7.8" v-bind="inert" />

            <!-- Obliques (front only) -->
            <path
                v-if="isFront"
                d="M45 79 Q41.5 95 44.5 112 L51 126 L51 86 Q48 82 45 79 Z"
                v-bind="bind('obliques')"
            />

            <!-- Glutes (back only) -->
            <path
                v-if="!isFront"
                d="M45 120 Q37 127 38.5 138 Q40.5 148 49 149.5 L58 147.5 L58 122 Z"
                v-bind="bind('glutes')"
            />

            <!-- Quadriceps (front) / Hamstrings (back) -->
            <path
                v-if="isFront"
                d="M46 128 Q39 148 40 172 Q41 194 46 205 L57 203 Q58.5 178 57.5 154 L56.5 129 Z"
                v-bind="bind('quads')"
            />
            <path
                v-else
                d="M46 145 Q39.5 163 41 182 Q42.5 199 46 208 L57 206 Q58.5 184 57.5 161 L56.5 146 Z"
                v-bind="bind('hamstrings')"
            />

            <!-- Calf (gastrocnemius bulge tapering to ankle) -->
            <path
                d="M45.5 211 Q37.5 220 39 232 Q40.5 246 44 256 Q46 260 49 260 L55.5 258 Q58 238 56.5 213 Z"
                v-bind="bind('calves')"
            />

            <!-- Foot -->
            <path d="M46 262 L44 277 Q43 283 48.5 284 L56.5 283 L56.5 262 Z" v-bind="inert" />
        </g>

        <!-- Abdominals: three stacked pairs with a sternum-line gap at centre -->
        <template v-if="isFront">
            <g v-for="row in 3" :key="`ab-${row}`">
                <rect
                    :x="52"
                    :y="80 + (row - 1) * 14.5"
                    width="6.5"
                    height="12"
                    rx="2.4"
                    v-bind="bind('abs')"
                />
                <rect
                    :x="61.5"
                    :y="80 + (row - 1) * 14.5"
                    width="6.5"
                    height="12"
                    rx="2.4"
                    v-bind="bind('abs')"
                />
            </g>
        </template>

        <!-- Lower back + spine -->
        <template v-else>
            <path d="M52 100 L68 100 L66.5 122 Q60 126 53.5 122 Z" v-bind="bind('lower_back')" />
            <path d="M60 45 L60 100" fill="none" stroke="rgba(130,144,170,0.35)" stroke-width="0.7" />
        </template>
    </svg>
</template>
