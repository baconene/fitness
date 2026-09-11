<script setup>
import { computed } from 'vue';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    weeks: { type: Array, default: () => [] },
    summary: { type: Object, required: true },
    records: { type: Array, default: () => [] },
    history: { type: Object, required: true },
    xpLedger: { type: Array, default: () => [] },
    statHistory: { type: Array, default: () => [] },
});

/** Bar heights are relative to the busiest week in the window. */
const peakWorkouts = computed(() => Math.max(1, ...props.weeks.map((w) => w.workouts)));

const barHeight = (week) => Math.max(4, (week.workouts / peakWorkouts.value) * 100);

const tiles = computed(() => [
    { label: 'Workouts', value: props.summary.workouts, icon: 'dumbbell', tone: 'text-brand' },
    { label: 'Quests', value: props.summary.quests, icon: 'scroll', tone: 'text-violet-light' },
    { label: 'Total XP', value: props.summary.xp.toLocaleString(), icon: 'sparkle', tone: 'text-brand' },
    { label: 'Streak', value: props.summary.streak + ' days', icon: 'flame', tone: 'text-orange-400' },
]);
</script>

<template>
    <HunterLayout title="Progress" subtitle="The last twelve weeks, and every record behind them.">
        <div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div v-for="tile in tiles" :key="tile.label" class="sys-panel flex items-center gap-4 p-5">
                <span class="sys-badge" :class="tile.tone"><Icon :name="tile.icon" :size="20" /></span>
                <div class="min-w-0">
                    <p class="sys-label-sm">{{ tile.label }}</p>
                    <p class="mt-0.5 text-xl font-semibold tabular-nums text-content">{{ tile.value }}</p>
                </div>
            </div>
        </div>

        <section class="sys-panel sys-corners mb-5 p-5">
            <h2 class="sys-label mb-5">Weekly volume</h2>
            <div class="flex h-40 items-end gap-2">
                <div v-for="week in weeks" :key="week.date" class="group flex flex-1 flex-col items-center gap-2">
                    <span class="text-[10px] tabular-nums text-muted opacity-0 transition-opacity group-hover:opacity-100">
                        {{ week.workouts }}
                    </span>
                    <div
                        class="w-full rounded-t-sm transition-colors"
                        :class="week.workouts ? 'bg-brand/70 group-hover:bg-brand' : 'bg-white/5'"
                        :style="{ height: barHeight(week) + '%' }"
                        :title="week.label + ': ' + week.workouts + ' workouts, ' + week.minutes + ' min, ' + week.xp + ' XP'"
                    />
                    <span class="text-[9px] text-muted">{{ week.label }}</span>
                </div>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="sys-panel p-5">
                <h2 class="sys-label mb-4">Personal records</h2>
                <ul v-if="records.length">
                    <li
                        v-for="(record, index) in records"
                        :key="record.id"
                        class="flex items-baseline justify-between gap-3 py-2.5"
                        :class="index ? 'sys-divider' : ''"
                    >
                        <span class="min-w-0 truncate text-[13.5px] text-content">
                            {{ record.exercise?.name || 'Exercise' }}
                        </span>
                        <span class="shrink-0 text-[13px] tabular-nums text-brand">
                            {{ record.value }} {{ record.unit }}
                        </span>
                    </li>
                </ul>
                <p v-else class="py-8 text-center text-sm text-muted">No records set yet.</p>
            </section>

            <section class="sys-panel p-5">
                <h2 class="sys-label mb-4">XP ledger</h2>
                <ul v-if="xpLedger.length">
                    <li
                        v-for="(entry, index) in xpLedger"
                        :key="entry.id"
                        class="flex items-baseline justify-between gap-3 py-2.5"
                        :class="index ? 'sys-divider' : ''"
                    >
                        <span class="min-w-0">
                            <span class="block truncate text-[13.5px] text-content">{{ entry.source }}</span>
                            <span class="text-[11px] text-muted">{{ entry.date }}</span>
                        </span>
                        <span class="shrink-0 text-[13px] tabular-nums text-brand">+{{ entry.amount }}</span>
                    </li>
                </ul>
                <p v-else class="py-8 text-center text-sm text-muted">No XP awarded yet.</p>
            </section>
        </div>

        <section class="sys-panel mt-5 p-5">
            <h2 class="sys-label mb-4">Workout history</h2>
            <ul v-if="history.data.length">
                <li
                    v-for="(workout, index) in history.data"
                    :key="workout.id"
                    class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 py-3"
                    :class="index ? 'sys-divider' : ''"
                >
                    <span class="min-w-0 flex-1 truncate text-[13.5px] text-content">{{ workout.name }}</span>
                    <span class="text-[12px] text-muted">{{ workout.date }}</span>
                    <span class="text-[12px] tabular-nums text-muted">{{ workout.minutes }} min</span>
                    <span class="text-[12px] tabular-nums text-brand">+{{ workout.xp }} XP</span>
                </li>
            </ul>
            <p v-else class="py-8 text-center text-sm text-muted">No completed workouts yet.</p>

            <Pagination :links="history.links" />
        </section>
    </HunterLayout>
</template>
