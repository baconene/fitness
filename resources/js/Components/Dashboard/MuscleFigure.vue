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

/** Structural outlines (head, neck) never light up. */
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
        <ellipse cx="60" cy="20" rx="11" ry="13" v-bind="inert" />
        <path d="M53 31 L53 41 Q60 45.5 67 41 L67 31 Z" v-bind="inert" />

        <!-- Mirrored half -->
        <g v-for="(transform, i) in sides" :key="i" :transform="transform">
            <!-- Trapezius reads as upper back from behind, shoulder line from the front -->
            <path
                d="M58 41 L37 53 Q44 46 50 42.5 Q54 40.5 58 41 Z"
                v-bind="bind(isFront ? 'shoulders' : 'upper_back')"
            />

            <!-- Deltoid -->
            <path
                d="M50 46 Q36 47 30 57 Q27 66 31 73 Q40 72 45 63 Q47 53 50 46 Z"
                v-bind="bind('shoulders')"
            />

            <!-- Pectoral (front) / Latissimus (back) -->
            <path
                v-if="isFront"
                d="M51 48 Q58 46 59 51.5 L59 79 Q49 80 44 72 Q41.5 58 46 50 Q48 48 51 48 Z"
                v-bind="bind('chest')"
            />
            <path
                v-else
                d="M47 56 Q40 71 42 89 Q44 103 50 113 L53 61 Q50 57 47 56 Z"
                v-bind="bind('lats')"
            />

            <!-- Upper back detail, back view only -->
            <path
                v-if="!isFront"
                d="M53 48 Q47 52 46 60 L52 60 Q54 53 56 49 Z"
                v-bind="bind('upper_back')"
            />

            <!-- Biceps (front) / Triceps (back) -->
            <path
                v-if="isFront"
                d="M31 73 Q26 82 26 93 L35 95 Q37.5 84 35 73 Z"
                v-bind="bind('biceps')"
            />
            <path
                v-else
                d="M30.5 72 Q24.5 82 25.5 94 L34.5 96 Q36.5 84 34 72 Z"
                v-bind="bind('triceps')"
            />

            <!-- Forearm -->
            <path
                d="M26 96 Q22 110 24 125 L32 127 Q34.5 111 34 98 Z"
                v-bind="bind('forearms')"
            />

            <!-- Hand -->
            <ellipse cx="27.5" cy="134" rx="4.6" ry="7.5" v-bind="inert" />

            <!-- Obliques (front only) -->
            <path
                v-if="isFront"
                d="M45 79 Q42 94 45 110 L51 124 L51 85 Q48 82 45 79 Z"
                v-bind="bind('obliques')"
            />

            <!-- Glutes (back only) -->
            <path
                v-if="!isFront"
                d="M46 121 Q38.5 128 40 139 Q42 148 49.5 149 L58 147 L58 123 Z"
                v-bind="bind('glutes')"
            />

            <!-- Quadriceps (front) / Hamstrings (back) -->
            <path
                v-if="isFront"
                d="M47 132 Q40 152 41 176 Q42 197 47 207 L57 205 Q58 180 57 156 L56 133 Z"
                v-bind="bind('quads')"
            />
            <path
                v-else
                d="M47 148 Q40.5 165 42 183 Q43.5 199 47 208 L57 206 Q58 184 57 162 L56.5 148 Z"
                v-bind="bind('hamstrings')"
            />

            <!-- Calf -->
            <path
                d="M45.5 211 Q39.5 226 42 243 Q44 255 48 259 L55 257 Q56.5 236 55.5 213 Z"
                v-bind="bind('calves')"
            />

            <!-- Foot -->
            <path d="M46.5 262 L44.5 276 Q43.5 282 48.5 283 L56 282 L56 262 Z" v-bind="inert" />
        </g>

        <!-- Abdominals: three stacked pairs -->
        <template v-if="isFront">
            <g v-for="row in 3" :key="`ab-${row}`">
                <rect
                    :x="52.5"
                    :y="83 + (row - 1) * 14"
                    width="6.5"
                    height="11.5"
                    rx="2.4"
                    v-bind="bind('abs')"
                />
                <rect
                    :x="61"
                    :y="83 + (row - 1) * 14"
                    width="6.5"
                    height="11.5"
                    rx="2.4"
                    v-bind="bind('abs')"
                />
            </g>
        </template>

        <!-- Lower back + spine -->
        <template v-else>
            <path
                d="M52.5 100 L67.5 100 L66 126 Q60 130 54 126 Z"
                v-bind="bind('lower_back')"
            />
            <path
                d="M60 48 L60 100"
                fill="none"
                stroke="rgba(130,144,170,0.35)"
                stroke-width="0.7"
            />
        </template>
    </svg>
</template>
