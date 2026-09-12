<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    weeks: { type: Array, default: () => [] },
    currentYear: { type: [Number, String], required: true },
    currentMonth: { type: [Number, String], required: true },
    heatmapData: { type: Object, default: () => ({}) },
});

const year = computed(() => Number(props.currentYear));
const month = computed(() => Number(props.currentMonth));

const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const monthLabel = computed(() =>
    new Date(year.value, month.value - 1, 1).toLocaleDateString(undefined, {
        month: 'long',
        year: 'numeric',
    }),
);

const previousMonth = computed(() =>
    month.value === 1 ? { year: year.value - 1, month: 12 } : { year: year.value, month: month.value - 1 },
);

const nextMonth = computed(() =>
    month.value === 12 ? { year: year.value + 1, month: 1 } : { year: year.value, month: month.value + 1 },
);

const daysInMonth = computed(() => props.weeks.flat().filter((day) => day.isCurrentMonth));

const sessionCount = computed(
    () => daysInMonth.value.flatMap((day) => day.events).filter((event) => event.type === 'Workout').length,
);

const eventCount = computed(() => daysInMonth.value.reduce((total, day) => total + day.events.length, 0));

const activeDaysThisYear = computed(() => Object.values(props.heatmapData).filter((value) => value > 0).length);

/**
 * Contribution-style columns: one column per week, Monday at the top.
 *
 * @return {array<int, array<int, ?array{date: string, value: int}>>}
 */
const heatmapWeeks = computed(() => {
    const dates = Object.keys(props.heatmapData).sort();
    const columns = [];
    let column = Array(7).fill(null);

    dates.forEach((date) => {
        const weekday = (new Date(date + 'T00:00:00').getDay() + 6) % 7;
        column[weekday] = { date, value: props.heatmapData[date] };

        if (weekday === 6) {
            columns.push(column);
            column = Array(7).fill(null);
        }
    });

    if (column.some(Boolean)) {
        columns.push(column);
    }

    return columns;
});

const selectedDate = ref(null);
const agendaItems = ref([]);
const agendaState = ref('idle');

/** Guards against a slow earlier request overwriting a newer selection. */
let latestAgendaRequest = 0;

const selectDay = async (day) => {
    const request = ++latestAgendaRequest;

    selectedDate.value = day.date;
    agendaItems.value = [];
    agendaState.value = 'loading';

    try {
        const response = await fetch(route('calendar.agenda', { date: day.date }), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }

        const payload = await response.json();

        if (request !== latestAgendaRequest) {
            return;
        }

        agendaItems.value = payload.items ?? [];
        agendaState.value = 'ready';
    } catch {
        if (request === latestAgendaRequest) {
            agendaState.value = 'error';
        }
    }
};

const selectedLabel = computed(() =>
    selectedDate.value
        ? new Date(selectedDate.value + 'T00:00:00').toLocaleDateString(undefined, {
              weekday: 'long',
              month: 'long',
              day: 'numeric',
          })
        : '',
);
</script>

<template>
    <HunterLayout title="Calendar" subtitle="Every session you have logged, and everything the system still expects.">
        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            <div class="sys-panel sys-corners flex items-center gap-4 p-5">
                <span class="sys-badge text-brand"><Icon name="dumbbell" :size="20" /></span>
                <div>
                    <p class="sys-label-sm">Sessions this month</p>
                    <p class="text-2xl font-semibold tabular-nums text-content">{{ sessionCount }}</p>
                </div>
            </div>
            <div class="sys-panel flex items-center gap-4 p-5">
                <span class="sys-badge text-muted"><Icon name="calendar" :size="20" /></span>
                <div>
                    <p class="sys-label-sm">Entries this month</p>
                    <p class="text-2xl font-semibold tabular-nums text-content">{{ eventCount }}</p>
                </div>
            </div>
            <div class="sys-panel flex items-center gap-4 p-5">
                <span class="sys-badge text-success"><Icon name="flame" :size="20" /></span>
                <div>
                    <p class="sys-label-sm">Active days in {{ year }}</p>
                    <p class="text-2xl font-semibold tabular-nums text-content">{{ activeDaysThisYear }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
            <section class="sys-panel p-4 sm:p-5">
                <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-[15px] font-medium text-content">{{ monthLabel }}</h2>

                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('calendar.show', previousMonth)"
                            preserve-scroll
                            aria-label="Previous month"
                            class="sys-pill min-h-9 hover:border-brand/50"
                        >
                            <Icon name="chevronDown" :size="12" class="rotate-90" />
                        </Link>
                        <Link :href="route('calendar.show')" preserve-scroll class="sys-pill min-h-9 hover:border-brand/50">
                            Today
                        </Link>
                        <Link
                            :href="route('calendar.show', nextMonth)"
                            preserve-scroll
                            aria-label="Next month"
                            class="sys-pill min-h-9 hover:border-brand/50"
                        >
                            <Icon name="chevronDown" :size="12" class="-rotate-90" />
                        </Link>
                    </div>
                </header>

                <div class="grid grid-cols-7 gap-1 sm:gap-1.5">
                    <p
                        v-for="weekday in weekdays"
                        :key="weekday"
                        class="pb-1 text-center text-[10px] uppercase tracking-[.14em] text-muted"
                    >
                        {{ weekday.charAt(0) }}<span class="hidden sm:inline">{{ weekday.slice(1) }}</span>
                    </p>

                    <template v-for="(week, weekIndex) in weeks" :key="weekIndex">
                        <button
                            v-for="day in week"
                            :key="day.date"
                            type="button"
                            :aria-label="day.date"
                            :aria-pressed="selectedDate === day.date"
                            class="flex min-h-14 flex-col rounded-md border p-1 text-left transition sm:min-h-20 sm:p-1.5"
                            :class="[
                                day.isCurrentMonth ? 'border-edge/15 bg-canvas/40' : 'border-transparent opacity-40',
                                day.isToday ? 'border-brand/60' : '',
                                selectedDate === day.date ? 'border-brand bg-brand/10' : 'hover:border-brand/40',
                            ]"
                            @click="selectDay(day)"
                        >
                            <span
                                class="text-[11px] tabular-nums sm:text-[12px]"
                                :class="day.isToday ? 'font-semibold text-brand' : 'text-muted'"
                            >
                                {{ day.day }}
                            </span>

                            <span v-if="day.events.length" class="mt-auto flex flex-wrap gap-0.5 pt-1">
                                <span
                                    v-for="event in day.events.slice(0, 3)"
                                    :key="event.id"
                                    :title="event.title"
                                    class="text-[10px] leading-none sm:text-[12px]"
                                >
                                    {{ event.icon }}
                                </span>
                                <span v-if="day.events.length > 3" class="text-[9px] leading-none text-muted">
                                    +{{ day.events.length - 3 }}
                                </span>
                            </span>
                        </button>
                    </template>
                </div>
            </section>

            <section class="sys-panel flex flex-col p-5">
                <h2 class="sys-label-sm mb-3">{{ selectedDate ? selectedLabel : 'Agenda' }}</h2>

                <p v-if="!selectedDate" class="py-8 text-center text-sm text-muted">
                    Pick a day to see what happened, or what is still queued.
                </p>

                <div v-else-if="agendaState === 'loading'" class="flex flex-col gap-3" aria-hidden="true">
                    <div v-for="placeholder in 2" :key="placeholder" class="animate-pulse rounded-md border border-edge/15 p-3">
                        <div class="h-3 w-2/3 rounded-full bg-edge/15" />
                        <div class="mt-2 h-2.5 w-1/3 rounded-full bg-edge/10" />
                    </div>
                </div>

                <p v-else-if="agendaState === 'error'" class="py-8 text-center text-sm text-danger">
                    Could not load that day. Try again.
                </p>

                <p v-else-if="!agendaItems.length" class="py-8 text-center text-sm text-muted">
                    Nothing logged or scheduled.
                </p>

                <ul v-else class="flex flex-col gap-3">
                    <li
                        v-for="(item, index) in agendaItems"
                        :key="item.type + '-' + index"
                        class="rounded-md border border-edge/15 p-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-[14px] font-medium text-content">
                                    <span class="mr-1">{{ item.icon }}</span>{{ item.title }}
                                </p>
                                <p v-if="item.details" class="mt-1 text-[12px] text-muted">{{ item.details }}</p>
                            </div>
                            <span v-if="item.time" class="sys-pill shrink-0 tabular-nums">{{ item.time }}</span>
                        </div>

                        <div v-if="item.progress !== undefined" class="mt-3">
                            <div class="sys-track">
                                <div class="sys-fill" :style="{ width: Math.min(100, item.progress) + '%' }" />
                            </div>
                        </div>
                    </li>
                </ul>
            </section>
        </div>

        <section class="sys-panel mt-4 p-5">
            <h2 class="sys-label-sm mb-4">Training activity in {{ year }}</h2>

            <div class="overflow-x-auto pb-1">
                <div class="flex gap-[3px]">
                    <div v-for="(column, columnIndex) in heatmapWeeks" :key="columnIndex" class="flex flex-col gap-[3px]">
                        <span
                            v-for="(cell, cellIndex) in column"
                            :key="cellIndex"
                            :title="cell ? cell.date + (cell.value ? ': trained' : ': rest') : ''"
                            class="h-[11px] w-[11px] rounded-[2px]"
                            :class="!cell ? 'bg-transparent' : cell.value ? 'bg-brand' : 'bg-edge/10'"
                        />
                    </div>
                </div>
            </div>

            <div class="mt-3 flex items-center gap-2 text-[11px] text-muted">
                <span class="h-[11px] w-[11px] rounded-[2px] bg-edge/10" />
                <span>Rest</span>
                <span class="ml-2 h-[11px] w-[11px] rounded-[2px] bg-brand" />
                <span>Trained</span>
            </div>
        </section>
    </HunterLayout>
</template>
