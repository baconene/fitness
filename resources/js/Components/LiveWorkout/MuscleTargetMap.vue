<script setup>
import { computed } from 'vue';
import MuscleFigure from '@/Components/Dashboard/MuscleFigure.vue';

const props = defineProps({ primary: { type: Array, default: () => [] }, secondary: { type: Array, default: () => [] }, recovery: Boolean });
const normalize = (keys) => keys.map((key) => key.replaceAll('-', '_'));
const primaryKeys = computed(() => normalize(props.primary));
const secondaryKeys = computed(() => normalize(props.secondary));
</script>

<template>
    <section class="mission-anatomy min-w-0 p-5 sm:p-8" data-mission-reveal>
        <div class="flex items-center justify-between gap-3"><h3 class="mission-label text-muted">Target body</h3><span class="text-[10px] tracking-widest text-brand">{{ recovery ? 'RECOVERY' : 'MUSCLE FOCUS' }}</span></div>
        <div class="mx-auto mt-5 flex max-w-xs justify-center gap-6">
            <div v-for="view in ['front', 'back']" :key="view" class="min-w-0 flex-1">
                <div class="h-52 sm:h-64"><MuscleFigure :view="view" :primary="primaryKeys" :secondary="secondaryKeys" /></div>
                <p class="mt-2 text-center text-[9px] uppercase tracking-[.2em] text-muted">{{ view }}</p>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4 text-xs leading-6">
            <div><p class="mission-label text-brand">Primary</p><p class="mt-2 capitalize">{{ primaryKeys.join(' / ').replaceAll('_', ' ') || (recovery ? 'Rest & restore' : 'Not specified') }}</p></div>
            <div><p class="mission-label text-violet-light">Secondary</p><p class="mt-2 capitalize text-muted">{{ secondaryKeys.join(' / ').replaceAll('_', ' ') || '—' }}</p></div>
        </div>
    </section>
</template>

<style scoped>
.mission-anatomy { background: radial-gradient(ellipse at 50% 40%, rgb(var(--color-brand) / .055), transparent 65%); }
</style>
