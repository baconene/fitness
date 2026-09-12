<script setup>
defineProps({ mission: { type: Object, required: true }, exerciseCount: Number, totalSets: Number, recovery: Boolean });
</script>

<template>
    <div class="border-t border-edge/20 pt-5">
        <h3 class="mission-label text-muted">Mission objective</h3>
        <p class="mt-2 text-sm">{{ recovery ? 'Complete your recovery routine.' : 'Complete all assigned exercises.' }}</p>
        <ul v-if="recovery" class="mt-4 space-y-2 text-sm text-muted">
            <li v-for="objective in mission.recoveryObjectives" :key="objective" class="flex gap-3"><span class="text-brand" aria-hidden="true">◇</span>{{ objective }}</li>
        </ul>
        <dl v-else class="mt-5 grid grid-cols-3 gap-3">
            <div><dd class="text-2xl font-medium tabular-nums">{{ exerciseCount }}</dd><dt class="mt-1 text-xs text-muted">Exercises</dt></div>
            <div><dd class="text-2xl font-medium tabular-nums">{{ totalSets }}</dd><dt class="mt-1 text-xs text-muted">Total sets</dt></div>
            <div><dd class="text-2xl font-medium tabular-nums">{{ mission.durationMinutes || '—' }}<span v-if="mission.durationMinutes" class="ml-1 text-xs text-muted">min</span></dd><dt class="mt-1 text-xs text-muted">Est. duration</dt></div>
        </dl>
        <p v-if="mission.attributes?.length" class="mt-5 text-xs text-muted">Attribute focus <span class="ml-3 font-semibold tracking-widest text-violet-light">{{ mission.attributes.join(' / ') }}</span></p>
    </div>
</template>
