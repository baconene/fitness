<script setup>
defineProps({ rewards: { type: Object, default: () => ({}) }, completed: Boolean });
</script>

<template>
    <section class="p-5 sm:p-8" data-mission-reveal>
        <h3 class="mission-label text-muted">Mission rewards</h3>
        <div class="mt-4 flex flex-wrap items-baseline gap-x-5 gap-y-2">
            <p class="text-3xl font-medium tabular-nums text-brand">+{{ (rewards.xp ?? 0).toLocaleString() }} <span class="text-sm">XP</span></p>
            <p v-if="rewards.gold != null" class="text-sm text-violet-light">+{{ rewards.gold }} gold</p>
            <p v-for="attribute in rewards.attributes || []" :key="attribute.label" class="text-sm text-violet-light">+{{ attribute.amount }} {{ attribute.label }}</p>
        </div>
        <p class="mt-2 text-xs text-muted">{{ completed ? 'Base set rewards recorded' : 'Available across your assigned sets' }}</p>
        <p v-if="rewards.possibleDrop" class="mt-4 text-sm">Possible drop: {{ rewards.possibleDrop }}</p>
        <p v-if="rewards.boss" class="mt-4 text-sm text-violet-light">Boss reward · {{ rewards.boss }}</p>
        <p v-if="rewards.description" class="mt-4 text-xs leading-6 text-muted">{{ rewards.description }}</p>
    </section>
</template>
