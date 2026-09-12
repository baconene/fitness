<script setup>
import XpProgressBar from './XpProgressBar.vue';
defineProps({ completedSets: Number, totalSets: Number, completedExercises: Number, exerciseCount: Number, elapsed: String, earnedXp: Number, calories: { type: Number, default: null } });
</script>

<template>
    <section aria-label="Mission progress">
        <div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
            <h3 class="mission-label text-muted">Mission progress</h3>
            <p class="text-sm tabular-nums"><span class="text-content">{{ completedSets }} / {{ totalSets }} sets</span><span class="ml-3 text-brand">{{ totalSets ? Math.round(completedSets / totalSets * 100) : 0 }}%</span></p>
        </div>
        <XpProgressBar :current="completedSets" :target="totalSets" label="Mission set progress" />
        <dl class="mt-4 grid grid-cols-2 gap-4 text-xs sm:grid-cols-4">
            <div><dt class="text-muted">Exercises</dt><dd class="mt-1 tabular-nums">{{ completedExercises }} / {{ exerciseCount }}</dd></div>
            <div><dt class="text-muted">Elapsed time</dt><dd class="mt-1 tabular-nums">{{ elapsed }}</dd></div>
            <div><dt class="text-muted">Calories · est.</dt><dd class="mt-1 tabular-nums">{{ calories == null ? 'Not tracked' : calories + ' kcal' }}</dd></div>
            <div><dt class="text-muted">Set XP earned</dt><dd class="mt-1 tabular-nums text-brand">+{{ earnedXp }}</dd></div>
        </dl>
    </section>
</template>
