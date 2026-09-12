<script setup>
/**
 * Anatomical hunter figure drawn entirely from SVG paths so any muscle
 * group can be lit up from data.
 *
 * The body is symmetrical: one half is authored and mirrored across the
 * x = 80 centreline, with centred groups (head, neck, abs, spine) drawn once.
 */
import { computed, useId } from 'vue';

const props = defineProps({
    view: { type: String, default: 'front' }, // 'front' | 'back'
    primary: { type: Array, default: () => [] },
    secondary: { type: Array, default: () => [] },
});

const uid = useId();
const glowId = `muscle-glow-${uid}`;
const surfaceId = `body-surface-${uid}`;
const muscleId = (state) => `body-${state}-${uid}`;

const state = (muscle) => {
    if (props.primary.includes(muscle)) {
        return 'primary';
    }

    return props.secondary.includes(muscle) ? 'secondary' : 'idle';
};

const STROKE = {
    primary: 'rgb(96, 190, 255)',
    secondary: 'rgba(161, 140, 255, 0.85)',
    idle: 'rgba(150, 170, 198, 0.32)',
};

/** Binds fill/stroke/filter for a muscle group in one go. */
const bind = (muscle) => {
    const s = state(muscle);

    return {
        class: `muscle-region ${muscle.replaceAll('_', '-')} muscle-${s}`,
        'data-muscle': muscle,
        fill: `url(#${muscleId(s)})`,
        stroke: STROKE[s],
        'stroke-width': s === 'idle' ? 0.55 : 0.8,
        filter: s === 'primary' ? `url(#${glowId})` : undefined,
    };
};

const isFront = computed(() => props.view === 'front');

/** Mirror the authored half onto the other side of the figure. */
const sides = ['', 'translate(160,0) scale(-1,1)'];
</script>

<template>

    <svg viewBox="0 0 160 360" class="h-full w-full" role="img" :aria-label="view + ' view muscle map'" stroke-linecap="round" stroke-linejoin="round">
        <defs>
            <linearGradient :id="surfaceId" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="#111b2b" /><stop offset=".48" stop-color="#28374b" /><stop offset="1" stop-color="#101a2a" />
            </linearGradient>
            <linearGradient :id="muscleId('idle')" x1="0" y1="0" x2="1" y2=".8">
                <stop stop-color="#34445b" /><stop offset=".5" stop-color="#253449" /><stop offset="1" stop-color="#172337" />
            </linearGradient>
            <linearGradient :id="muscleId('primary')" x1="0" y1="0" x2="1" y2="1">
                <stop stop-color="#79c6ff" /><stop offset=".45" stop-color="#389de9" /><stop offset="1" stop-color="#1a508b" />
            </linearGradient>
            <linearGradient :id="muscleId('secondary')" x1="0" y1="0" x2="1" y2="1">
                <stop stop-color="#aa9ae8" /><stop offset=".5" stop-color="#7060b5" /><stop offset="1" stop-color="#37305e" />
            </linearGradient>
            <filter :id="glowId" x="-30%" y="-30%" width="160%" height="160%">
                <feGaussianBlur stdDeviation="1.2" result="blur" />
                <feMerge><feMergeNode in="blur" /><feMergeNode in="SourceGraphic" /></feMerge>
            </filter>
        </defs>

        <!-- A continuous silhouette connects the shoulders, arms, pelvis and legs. -->
        <g :fill="'url(#' + surfaceId + ')'" stroke="#687c98" stroke-opacity=".45" stroke-width=".65">
            <path d="M69 47 Q70 56 66 60 L49 65 Q40 67 36 76 Q31 87 31 100 L28 118 Q22 132 21 146 L19 160 Q15 168 16 175 L18 184 Q20 187 21 183 L20 175 L23 184 Q25 187 26 182 L25 174 L28 181 Q30 181 29 176 L27 166 Q32 161 32 155 L38 137 Q43 124 42 118 Q49 107 49 96 Q54 110 56 123 Q58 136 54 151 Q49 165 51 183 Q50 203 53 221 L57 246 Q56 256 57 263 Q53 278 57 296 L62 322 L61 335 Q57 342 57 347 Q64 351 74 347 L76 338 L75 324 Q79 299 76 282 L75 264 Q78 254 76 244 L78 213 L80 193 L82 213 L84 244 Q82 254 85 264 L84 282 Q81 299 85 324 L84 338 L86 347 Q96 351 103 347 Q103 342 99 335 L98 322 L103 296 Q107 278 103 263 Q104 256 103 246 L107 221 Q110 203 109 183 Q111 165 106 151 Q102 136 104 123 Q106 110 111 96 Q111 107 118 118 Q117 124 122 137 L128 155 Q128 161 133 166 L131 176 Q130 181 132 181 L135 174 L134 182 Q135 187 137 184 L140 175 L139 183 Q140 187 142 184 L144 175 Q145 168 141 160 L139 146 Q138 132 132 118 L129 100 Q129 87 124 76 Q120 67 111 65 L94 60 Q90 56 91 47 Z" />
            <path d="M67 27 Q65 13 74 10 Q81 7 89 12 Q96 16 93 28 Q97 27 95 35 L92 38 Q90 47 84 51 L76 51 Q69 46 68 38 Q65 36 65 31 Q64 27 67 27 Z" />
        </g>

        <g v-for="(transform, side) in sides" :key="side" :transform="transform">
            <!-- Neutral neck tendons and clavicle. -->
            <path d="M70 48 Q71 57 77 66 M67 61 Q72 65 78 66 M48 69 Q60 65 75 69" fill="none" stroke="#90a2b9" stroke-opacity=".3" stroke-width=".7" />
            <path d="M47 68 Q36 70 35 84 L34 94 Q40 94 46 87 Q51 80 52 71 Z" v-bind="bind('shoulders')" />

            <template v-if="isFront">
                <path d="M54 70 Q66 68 78 72 L78 93 Q69 101 56 96 L49 89 Q51 78 54 70 Z" v-bind="bind('chest')" />
                <path d="M37 95 Q43 91 46 90 Q48 100 42 111 L37 120 Q33 116 33 109 Z" v-bind="bind('biceps')" />
                <path d="M32 96 L34 96 Q31 111 35 120 L31 123 Q28 115 32 96 Z" v-bind="bind('triceps')" />
                <path d="M53 101 L64 107 Q65 121 65 137 L57 151 Q60 138 58 124 Z" v-bind="bind('obliques')" />
                <path d="M68 102 Q73 100 78 101 L78 111 Q73 114 68 110 Z" v-bind="bind('abs')" />
                <path d="M68 114 Q73 116 78 114 L78 126 Q72 128 68 124 Z" v-bind="bind('abs')" />
                <path d="M68 128 Q73 131 78 129 L78 140 Q73 144 68 138 Z" v-bind="bind('abs')" />
                <path d="M67 142 Q72 147 78 144 L78 162 Q73 158 67 150 Z" v-bind="bind('abs')" />
                <path d="M54 154 Q62 158 76 170 L79 188 Q67 180 57 176 Z" :fill="'url(#' + muscleId('idle') + ')'" stroke="#657892" stroke-opacity=".25" stroke-width=".5" />
                <!-- Outer, central and inner quadriceps bellies. -->
                <path d="M54 184 Q51 208 57 233 L62 248 Q65 235 61 215 L60 189 Z" v-bind="bind('quads')" />
                <path d="M62 185 Q69 188 72 195 L72 221 Q69 240 66 247 Q63 239 64 221 Z" v-bind="bind('quads')" />
                <path d="M74 213 Q78 229 74 244 L70 251 Q66 249 69 240 Z" v-bind="bind('quads')" />
                <path d="M72 182 L78 193 L76 215 L71 230 Q74 207 67 187 Z" :fill="'url(#' + muscleId('idle') + ')'" stroke="#7b8da7" stroke-opacity=".25" stroke-width=".5" />
                <!-- Neutral patella and tibial ridge. -->
                <path d="M62 251 Q68 247 73 252 L72 261 Q66 266 61 260 Z" :fill="'url(#' + surfaceId + ')'" stroke="#8da0ba" stroke-opacity=".35" stroke-width=".6" />
                <path d="M59 267 Q55 281 60 299 L63 315 Q65 298 64 282 Z" v-bind="bind('calves')" />
                <path d="M70 266 Q77 281 73 300 L69 321 Q66 306 68 290 Z" :fill="'url(#' + muscleId('idle') + ')'" stroke="#687d98" stroke-opacity=".3" stroke-width=".5" />
            </template>
            <template v-else>
                <path d="M68 61 L77 66 L78 103 Q67 100 58 87 L49 74 Q57 69 68 61 Z" v-bind="bind('upper_back')" />
                <path d="M49 91 Q58 100 70 105 L69 120 L62 139 Q55 128 54 113 Z" v-bind="bind('lats')" />
                <path d="M72 105 L78 109 L78 156 Q72 154 68 147 Q73 129 72 105 Z" v-bind="bind('lower_back')" />
                <path d="M37 94 Q42 92 46 91 Q47 102 43 112 L37 122 Q31 116 33 107 Z" v-bind="bind('triceps')" />
                <path d="M54 156 Q66 151 78 160 L78 180 Q66 193 55 181 Q50 172 54 156 Z" v-bind="bind('glutes')" />
                <path d="M55 187 Q62 194 64 207 L64 236 L61 250 Q56 236 55 217 Z" v-bind="bind('hamstrings')" />
                <path d="M67 191 L76 185 Q77 205 73 227 L69 249 Q65 241 67 223 Z" v-bind="bind('hamstrings')" />
                <path d="M61 251 Q67 255 72 251 M62 256 L63 264 M70 256 L70 264" fill="none" stroke="#8396b1" stroke-opacity=".3" stroke-width=".7" />
                <path d="M60 265 Q54 276 58 292 Q61 301 65 301 L66 280 Q65 270 60 265 Z" v-bind="bind('calves')" />
                <path d="M69 265 Q76 272 75 287 Q74 298 68 305 L67 282 Z" v-bind="bind('calves')" />
                <path d="M65 302 Q64 314 65 330 M69 306 L68 330" fill="none" stroke="#94a6be" stroke-opacity=".35" stroke-width=".7" />
            </template>

            <path d="M31 125 Q36 122 39 122 Q38 136 31 149 L26 160 L23 158 Q23 143 31 125 Z" v-bind="bind('forearms')" />
            <path d="M35 130 Q31 144 26 154 M37 128 L30 148" fill="none" stroke="#050914" stroke-opacity=".45" stroke-width=".65" />
            <path d="M24 165 L22 174 M26 163 L25 172 M63 334 Q68 337 72 333 M61 343 L70 343" fill="none" stroke="#93a4bb" stroke-opacity=".3" stroke-width=".65" />

            <!-- Fine contours suggest muscle fiber direction. -->
            <g fill="none" stroke="#07101e" stroke-opacity=".4" stroke-width=".65">
                <path d="M40 75 Q38 83 36 89 M43 75 Q43 83 39 89" />
                <path v-if="isFront" d="M55 76 Q66 79 75 76 M53 82 Q65 87 75 82 M54 89 Q65 94 75 88 M58 197 Q57 215 60 229 M68 197 L68 221" />
                <path v-else d="M66 71 L74 89 M60 78 L72 94 M56 103 L65 115 M57 111 L64 122 M57 164 Q66 160 74 165 M57 171 Q65 166 74 172 M60 195 L60 220 M71 197 L70 222" />
            </g>
        </g>
        <path v-if="!isFront" d="M80 62 L80 151 M78 151 L80 157 L82 151" fill="none" stroke="#9bacc2" stroke-opacity=".3" stroke-width=".7" />
        <path v-else d="M80 74 L80 96 M79 137 Q80 139 81 137" fill="none" stroke="#060d18" stroke-width=".8" />
    </svg>
</template>
