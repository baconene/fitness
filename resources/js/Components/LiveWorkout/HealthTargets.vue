<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import XpProgressBar from './XpProgressBar.vue';

const props = defineProps({ targets: { type: Object, default: () => ({}) } });
const rows = computed(() => [
    { label: 'Calories', target: props.targets.calorieTarget, current: props.targets.calorieCurrent, unit: 'kcal' },
    { label: 'Water', target: props.targets.waterTargetLiters, current: props.targets.waterCurrentLiters, unit: 'L' },
    { label: 'Steps', target: props.targets.stepTarget, current: props.targets.stepsCurrent, unit: 'steps' },
    { label: 'Sleep', target: props.targets.sleepTargetMinutes, current: props.targets.sleepCurrentMinutes, unit: 'min' },
]);
const format = (value, unit) => value == null ? 'Not recorded' : unit === 'min' ? `${Math.floor(value / 60)}h ${value % 60}m` : `${Number(value).toLocaleString()} ${unit}`;
</script>

<template>
    <section class="p-5 sm:p-8" data-mission-reveal>
        <div class="flex flex-wrap items-center justify-between gap-2"><h3 class="mission-label text-muted">Today’s requirements</h3><Link :href="route('health.index')" class="inline-flex min-h-11 items-center text-xs text-brand hover:underline">Health profile →</Link></div>
        <dl class="mt-3 grid grid-cols-2 gap-x-6 gap-y-6 sm:grid-cols-4">
            <div v-for="row in rows" :key="row.label" class="min-w-0">
                <dt class="text-[10px] uppercase tracking-widest text-muted">{{ row.label }}</dt>
                <dd class="mt-2 text-sm font-medium tabular-nums">{{ row.target == null ? 'No target set' : format(row.target, row.unit) }}</dd>
                <dd class="mt-1 text-xs text-muted">{{ row.label === 'Sleep' ? 'Last' : 'Current' }}: {{ format(row.current, row.unit) }}</dd>
                <XpProgressBar v-if="row.target > 0" class="mt-3" :current="Number(row.current || 0)" :target="Number(row.target)" :label="row.label + ' target progress'" />
            </div>
        </dl>
        <p v-if="targets.goal" class="mt-6 border-t border-edge/15 pt-4 text-xs text-muted">Current goal · <span class="text-content">{{ targets.goal.name }}<template v-if="targets.goal.target != null"> · {{ targets.goal.target }} {{ targets.goal.unit }}</template></span></p>
    </section>
</template>
