<script setup>
import ExerciseAnimation from '@/Components/LiveWorkout/ExerciseAnimation.vue';

defineProps({
    exercise: { type: Object, required: true },
    targetReps: { type: String, default: null },
    previousSet: { type: Object, default: null },
});
</script>

<template>
    <div class="py-5">
        <h2 class="text-2xl font-semibold uppercase tracking-wide text-content">{{ exercise.name }}</h2>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span v-if="exercise.difficulty" class="sys-pill">{{ exercise.difficulty }}</span>
            <span v-for="muscle in Array.isArray(exercise.primary_muscle) ? exercise.primary_muscle : []" :key="muscle" class="sys-pill">{{ muscle.replaceAll('_', ' ') }}</span>
            <span v-if="targetReps" class="sys-pill sys-pill-active">Target {{ targetReps }} reps</span>
        </div>

        <p v-if="previousSet" class="mt-3 text-[12px] text-muted">
            Last time:
            <span class="tabular-nums text-content/85">
                {{ previousSet.reps_completed ?? previousSet.duration_seconds }}{{ previousSet.reps_completed ? ' reps' : 's' }}
                <template v-if="previousSet.weight_kg"> · {{ previousSet.weight_kg }} kg</template>
            </span>
        </p>

        <!-- Renders only for slugs that have a demonstration authored. -->
        <ExerciseAnimation v-if="exercise.slug" :slug="exercise.slug" class="mt-4 max-w-[200px]" />

        <details v-if="exercise.instructions" class="mt-3 text-xs leading-relaxed text-muted">
            <summary class="min-h-11 cursor-pointer py-3 text-brand">Exercise guidance</summary>
            <p class="pb-3">{{ exercise.instructions }}</p>
        </details>
    </div>
</template>
