<script setup>
import MissionObjective from './MissionObjective.vue';

defineProps({ mission: { type: Object, required: true }, rewards: { type: Object, default: () => ({}) }, exerciseCount: Number, totalSets: Number, recovery: Boolean });
</script>

<template>
    <section class="min-w-0 p-5 sm:p-8 lg:p-10" data-mission-reveal>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="mission-label text-brand">Today’s mission</p>
            <span class="border border-brand/30 bg-brand/5 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-widest" :class="mission.status === 'COMPLETED' ? 'text-success' : 'text-brand'">{{ mission.status }}</span>
        </div>
        <h2 class="sys-display mt-5 break-words text-3xl uppercase leading-tight sm:text-4xl lg:text-5xl">{{ mission.title }}</h2>
        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs uppercase tracking-widest text-muted">
            <span>{{ mission.type }} mission</span>
            <span v-if="mission.difficulty" class="text-violet-light">{{ mission.difficulty }}-Rank</span>
            <span v-if="rewards.xp" class="font-semibold text-brand">+{{ rewards.xp.toLocaleString() }} XP</span>
        </div>
        <p class="my-6 max-w-xl text-sm leading-7 text-muted">{{ mission.description }}</p>
        <MissionObjective :mission="mission" :exercise-count="exerciseCount" :total-sets="totalSets" :recovery="recovery" />
        <p v-if="mission.goal" class="mt-5 text-xs text-muted">Your goal: <span class="text-content">{{ mission.goal }}</span></p>
    </section>
</template>
