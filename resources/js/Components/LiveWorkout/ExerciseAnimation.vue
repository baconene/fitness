<script setup>
import { computed } from 'vue';
import { demoFor } from '@/Support/exerciseDemos';

const props = defineProps({
    /** Exercise slug, e.g. 'bodyweight-squat'. */
    slug: { type: String, required: true },
});

const pose = computed(() => demoFor(props.slug));

</script>

<template>
    <figure v-if="pose" class="exercise-demo sys-panel sys-corners sys-corners-x relative overflow-hidden">
        <svg viewBox="0 0 100 120" class="block h-auto w-full" role="img" :aria-label="pose.label">
            <!-- Ground line, so the figure reads as standing rather than floating. -->
            <line x1="8" y1="113" x2="92" y2="113" class="ground" />

            <g class="figure pose-a" v-html="pose.a" />
            <g class="figure pose-b" v-html="pose.b" />
        </svg>

        <figcaption class="px-3 pb-3 text-center text-[11px] leading-relaxed text-muted">
            {{ pose.label }}
        </figcaption>
    </figure>
</template>

<style scoped>
.exercise-demo {
    background:
        radial-gradient(70% 60% at 50% 35%, rgba(54, 163, 255, 0.09) 0%, transparent 70%),
        linear-gradient(160deg, rgba(16, 25, 44, 0.92), rgba(8, 13, 26, 0.9));
}

.figure :deep(*) {
    fill: none;
    stroke: rgb(var(--color-brand));
    stroke-width: 3.2;
    stroke-linecap: round;
    stroke-linejoin: round;
    filter: drop-shadow(0 0 4px rgb(var(--color-brand) / 0.55));
}

/* Dumbbells read as solid weight rather than outline. */
.figure :deep(rect) {
    fill: rgb(var(--color-violet-light));
    stroke: none;
}

.ground {
    stroke: rgb(var(--color-edge) / 0.25);
    stroke-width: 1.5;
    stroke-dasharray: 3 4;
}

.pose-a,
.pose-b {
    animation: demo-swap 1.8s steps(1, end) infinite;
}

.pose-b {
    animation-delay: 0.9s;
}

@keyframes demo-swap {
    0%,
    50% {
        opacity: 1;
    }
    50.01%,
    100% {
        opacity: 0;
    }
}

/* Without motion, show only the end position so the shape is still teachable. */
@media (prefers-reduced-motion: reduce) {
    .pose-a,
    .pose-b {
        animation: none;
    }
    .pose-a {
        opacity: 0;
    }
    .pose-b {
        opacity: 1;
    }
}
</style>
