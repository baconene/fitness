<script setup>
import { ref, watch } from 'vue';
import ExerciseAnimation from '@/Components/LiveWorkout/ExerciseAnimation.vue';

const props = defineProps({
    exercise: { type: Object, required: true },
    targetReps: { type: String, default: null },
    previousSet: { type: Object, default: null },
});
const showDemo = ref(false);
watch(() => props.exercise.id, () => { showDemo.value = false; });
</script>

<template>
    <div class="py-2 lg:py-5">
        <h2 class="text-lg leading-snug lg:text-2xl font-semibold uppercase tracking-wide text-content">{{ exercise.name }}</h2>

        <div class="mt-2 flex flex-wrap items-center gap-2">
            <span v-if="exercise.difficulty" class="sys-pill">{{ exercise.difficulty }}</span>
            <span v-for="muscle in Array.isArray(exercise.primary_muscle) ? exercise.primary_muscle : []" :key="muscle" class="sys-pill">{{ muscle.replaceAll('_', ' ') }}</span>
            <span v-if="targetReps" class="sys-pill sys-pill-active">Target {{ targetReps }} reps</span>
        </div>

        <p v-if="previousSet" class="mt-2 text-[12px] text-muted">
            Last time:
            <span class="tabular-nums text-content/85">
                {{ previousSet.reps_completed ?? previousSet.duration_seconds }}{{ previousSet.reps_completed ? ' reps' : 's' }}
                <template v-if="previousSet.weight_kg"> · {{ previousSet.weight_kg }} kg</template>
            </span>
        </p>

        <button type="button" class="mt-1 flex min-h-11 w-full items-center justify-between text-xs text-brand lg:hidden" :aria-expanded="showDemo" aria-controls="current-exercise-demo" @click="showDemo = !showDemo">
            <span>{{ showDemo ? 'Hide demo & guidance' : 'Show demo & guidance' }}</span><span aria-hidden="true">{{ showDemo ? '?' : '+' }}</span>
        </button>
        <div id="current-exercise-demo" :class="showDemo ? 'block' : 'hidden lg:block'">
        <ExerciseAnimation :slug="exercise.slug || exercise.name" :name="exercise.name" class="mt-4" />

        <details v-if="exercise.instructions" class="mt-2 text-xs leading-relaxed text-muted">
            <summary class="min-h-11 cursor-pointer py-3 text-brand">Exercise guidance</summary>
            <p class="pb-3">{{ exercise.instructions }}</p>
        </details>
        </div>
    </div>
</template>
