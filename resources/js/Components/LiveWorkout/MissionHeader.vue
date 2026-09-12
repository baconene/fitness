<script setup>
import XpProgressBar from './XpProgressBar.vue';
import MissionSystemMessage from './MissionSystemMessage.vue';

defineProps({ hunter: { type: Object, default: null }, state: { type: String, default: 'READY' } });
</script>

<template>
    <section class="grid items-center gap-6 py-2 sm:py-4 lg:grid-cols-[1.25fr_1fr] lg:gap-12" data-mission-reveal>
        <div class="min-w-0">
            <p class="mission-label text-muted">Hunter detected</p>
            <h2 class="mt-2 break-words text-2xl font-semibold uppercase tracking-wider sm:text-3xl">{{ hunter?.name || 'Hunter' }}</h2>
            <div v-if="hunter" class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs tracking-wider">
                <span class="text-violet-light">{{ hunter.rank }}-RANK HUNTER</span>
                <span>LV. {{ hunter.level }}</span>
                <span class="text-muted tabular-nums">{{ hunter.currentXp.toLocaleString() }} / {{ hunter.requiredXp.toLocaleString() }} XP</span>
            </div>
            <XpProgressBar v-if="hunter" class="mt-3 max-w-md" :current="hunter.currentXp" :target="hunter.requiredXp" label="Hunter level experience" />
        </div>
        <MissionSystemMessage :state="state" />
    </section>
</template>
