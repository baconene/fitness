<script setup>
import MissionExerciseRow from './MissionExerciseRow.vue';
defineProps({ exercises: { type: Array, default: () => [] }, currentId: { type: Number, default: null }, disabled: Boolean });
defineEmits(['select']);
</script>

<template>
    <section>
        <div class="flex flex-wrap items-center justify-between gap-2"><h3 class="mission-label text-muted">Mission sequence</h3><span class="text-xs text-muted">{{ exercises.length }} objectives</span></div>
        <p class="mt-2 text-xs leading-5 text-muted">Select any exercise to view its sets.</p>
        <p v-if="!exercises.length" class="mt-5 text-sm text-muted">No exercises assigned. Add an exercise to build your mission.</p>
        <ol v-else class="mt-3"><MissionExerciseRow v-for="(entry, index) in exercises" :key="entry.id" :entry="entry" :index="index" :active="entry.id === currentId" :disabled="disabled" @select="$emit('select', index)" /></ol>
    </section>
</template>
