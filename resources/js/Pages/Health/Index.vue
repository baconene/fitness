<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    measurements: { type: Array, default: () => [] },
    goals: { type: Array, default: () => [] },
    goalTypes: { type: Array, default: () => [] },
    today: { type: String, required: true },
    hydration: { type: Object, default: null },
});

const UNITS = ['kg', 'km', 'minutes', 'sessions', 'reps', '%'];

const measurementForm = useForm({
    measured_at: props.today,
    weight_kg: '',
    height_cm: '',
    body_fat_pct: '',
    resting_heart_rate: '',
    notes: '',
});

const goalForm = useForm({
    goal_type: props.goalTypes[0] ? props.goalTypes[0].value : '',
    target_value: '',
    target_unit: 'kg',
    target_date: '',
    is_primary: false,
    status: 'active',
});

const submitMeasurement = () => {
    measurementForm.post(route('health.measurements.store'), {
        preserveScroll: true,
        onSuccess: () => measurementForm.reset('weight_kg', 'body_fat_pct', 'resting_heart_rate', 'notes'),
    });
};

const submitGoal = () => {
    goalForm.post(route('health.goals.store'), {
        preserveScroll: true,
        onSuccess: () => goalForm.reset('target_value', 'target_date'),
    });
};

const latest = computed(() => props.measurements[props.measurements.length - 1] || null);

const activeGoals = computed(() => props.goals.filter((goal) => goal.status === 'active').length);

const bmi = computed(() => {
    if (!latest.value || !latest.value.height_cm || !latest.value.weight_kg) {
        return null;
    }

    const metres = latest.value.height_cm / 100;

    return (latest.value.weight_kg / (metres * metres)).toFixed(1);
});

/** Newest first for the history table. */
const history = computed(() => [...props.measurements].reverse());
</script>

<template>
    <HunterLayout title="Health" subtitle="">
        <div class="health-page space-y-5 sm:space-y-6">
            <section class="health-banner sys-panel sys-corners overflow-hidden p-5 sm:p-8">
                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="ui-eyebrow"><Icon name="crosshair" :size="14" /> Player status / Health</p>
                        <h2 class="sys-display mt-3 text-3xl sm:text-4xl">Hunter vitals</h2>
                        <p class="mt-3 max-w-lg text-sm leading-relaxed text-muted">Know your baseline. Track your changes. Build toward your next objective.</p>
                    </div>
                    <a href="#measurement-form" class="inline-flex min-h-12 shrink-0 items-center justify-center gap-3 rounded-md border border-brand/40 bg-brand/10 px-5 py-3 text-sm font-semibold text-brand hover:bg-brand/20">
                        <Icon name="scale" :size="18" /> Log measurement <Icon name="arrowRight" :size="16" />
                    </a>
                </div>
            </section>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                <div class="sys-panel col-span-2 flex items-center gap-4 p-4 sm:col-span-1 sm:p-5">
                    <span class="sys-badge text-brand"><Icon name="scale" :size="20" /></span>
                    <div>
                        <p class="sys-label-sm">Latest weight</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums text-content sm:text-3xl">
                            {{ latest && latest.weight_kg ? latest.weight_kg + ' kg' : '-' }}
                        </p>
                        <p class="mt-1 text-xs text-muted">{{ latest ? 'Logged ' + String(latest.measured_at).slice(0, 10) : 'Awaiting your first entry' }}</p>
                    </div>
                </div>
                <div class="sys-panel flex flex-col items-start gap-3 p-4 sm:flex-row sm:items-center sm:gap-4 sm:p-5">
                    <span class="sys-badge text-violet-light"><Icon name="body" :size="20" /></span>
                    <div>
                        <p class="sys-label-sm">BMI</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums text-content sm:text-3xl">{{ bmi || '—' }}</p>
                        <p class="mt-1 text-xs text-muted">Body mass index</p>
                    </div>
                </div>
                <div class="sys-panel flex flex-col items-start gap-3 p-4 sm:flex-row sm:items-center sm:gap-4 sm:p-5">
                    <span class="sys-badge text-success"><Icon name="target" :size="20" /></span>
                    <div>
                        <p class="sys-label-sm">Active goals</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums text-content sm:text-3xl">{{ activeGoals }}</p>
                        <p class="mt-1 text-xs text-muted">In pursuit</p>
                    </div>
                </div>
            </div>

            <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] sm:gap-6">
                <section id="measurement-form" class="sys-panel sys-corners min-w-0 scroll-mt-28 p-5 sm:p-6">
                    <div class="mb-6 flex items-start gap-3">
                        <span class="sys-badge text-brand"><Icon name="scale" :size="20" /></span>
                        <div><p class="ui-eyebrow mb-1">Vitals log</p><h2 class="sys-label">Log a measurement</h2></div>
                    </div>
                    <form class="space-y-5" @submit.prevent="submitMeasurement">
                        <p class="text-xs leading-relaxed text-muted">Date and weight are required. Add other measurements when available.</p>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="block">
                                <span class="ui-label">Date</span>
                                <input v-model="measurementForm.measured_at" type="date" :max="today" class="ui-input w-full" required />
                                <span v-if="measurementForm.errors.measured_at" class="ui-error">{{ measurementForm.errors.measured_at }}</span>
                            </label>
                            <label class="block">
                                <span class="ui-label">Weight (kg)</span>
                                <input v-model="measurementForm.weight_kg" type="number" step="0.1" min="1" max="600" class="ui-input w-full" required />
                                <span v-if="measurementForm.errors.weight_kg" class="ui-error">{{ measurementForm.errors.weight_kg }}</span>
                            </label>
                            <label class="block">
                                <span class="ui-label">Height (cm)</span>
                                <input v-model="measurementForm.height_cm" type="number" step="0.1" min="30" max="300" class="ui-input w-full" />
                                <span v-if="measurementForm.errors.height_cm" class="ui-error" role="alert">{{ measurementForm.errors.height_cm }}</span>
                            </label>
                            <label class="block">
                                <span class="ui-label">Body fat (%)</span>
                                <input v-model="measurementForm.body_fat_pct" type="number" step="0.1" min="0" max="80" class="ui-input w-full" />
                                <span v-if="measurementForm.errors.body_fat_pct" class="ui-error" role="alert">{{ measurementForm.errors.body_fat_pct }}</span>
                            </label>
                        </div>
                        <label class="block">
                            <span class="ui-label">Resting heart rate</span>
                            <input v-model="measurementForm.resting_heart_rate" type="number" min="20" max="250" class="ui-input w-full" />
                            <span v-if="measurementForm.errors.resting_heart_rate" class="ui-error" role="alert">{{ measurementForm.errors.resting_heart_rate }}</span>
                        </label>
                        <label class="block">
                            <span class="ui-label">Notes</span>
                            <textarea v-model="measurementForm.notes" rows="2" maxlength="2000" class="ui-input w-full"></textarea>
                            <span v-if="measurementForm.errors.notes" class="ui-error" role="alert">{{ measurementForm.errors.notes }}</span>
                        </label>
                        <button type="submit" class="sys-cta min-h-12 disabled:opacity-50" :disabled="measurementForm.processing">
                            {{ measurementForm.processing ? 'Saving' : 'Save measurement' }}
                        </button>
                    </form>
                </section>

                <section class="sys-panel sys-corners min-w-0 p-5 sm:p-6">
                    <div class="mb-6 flex items-start gap-3">
                        <span class="sys-badge text-violet-light"><Icon name="target" :size="20" /></span>
                        <div><p class="ui-eyebrow mb-1 text-violet-light">Next milestone</p><h2 class="sys-label">Set an objective</h2></div>
                    </div>
                    <form class="space-y-5" @submit.prevent="submitGoal">
                        <label class="block">
                            <span class="ui-label">Goal type</span>
                            <select v-model="goalForm.goal_type" class="ui-input w-full" required>
                                <option v-for="type in goalTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                            </select>
                            <span v-if="goalForm.errors.goal_type" class="ui-error">{{ goalForm.errors.goal_type }}</span>
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="block">
                                <span class="ui-label">Target</span>
                                <input v-model="goalForm.target_value" type="number" step="0.1" min="0" class="ui-input w-full" />
                                <span v-if="goalForm.errors.target_value" class="ui-error" role="alert">{{ goalForm.errors.target_value }}</span>
                            </label>
                            <label class="block">
                                <span class="ui-label">Unit</span>
                                <select v-model="goalForm.target_unit" class="ui-input w-full">
                                    <option v-for="unit in UNITS" :key="unit" :value="unit">{{ unit }}</option>
                                </select>
                                <span v-if="goalForm.errors.target_unit" class="ui-error" role="alert">{{ goalForm.errors.target_unit }}</span>
                            </label>
                        </div>
                        <label class="block">
                            <span class="ui-label">Target date</span>
                            <input v-model="goalForm.target_date" type="date" class="ui-input w-full" />
                            <span v-if="goalForm.errors.target_date" class="ui-error" role="alert">{{ goalForm.errors.target_date }}</span>
                        </label>
                        <label class="flex min-h-14 cursor-pointer items-center gap-3 rounded-md border border-violet/25 bg-violet/5 p-4">
                            <input v-model="goalForm.is_primary" type="checkbox" class="ui-checkbox shrink-0" />
                            <span v-if="goalForm.errors.is_primary" class="ui-error" role="alert">{{ goalForm.errors.is_primary }}</span>
                            <span class="text-[13px] text-content">Make this my primary objective</span>
                        </label>
                        <button type="submit" class="sys-cta min-h-12 disabled:opacity-50" :disabled="goalForm.processing">
                            {{ goalForm.processing ? 'Saving' : 'Save objective' }}
                        </button>
                    </form>
                </section>
            </div>

            <!-- Hydration trend -->
            <section v-if="hydration" class="sys-panel sys-corners p-5 sm:p-6">
                <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="ui-eyebrow mb-1 text-brand">Hydration</p>
                        <h2 class="sys-label">Last {{ hydration.history.days }} days</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="sys-pill">Target {{ hydration.history.targetLitres.toFixed(1) }} L</span>
                        <span class="sys-pill">Avg {{ hydration.history.averageLitres.toFixed(1) }} L</span>
                        <span class="sys-pill" :class="hydration.history.daysMet ? 'sys-pill-active' : ''">
                            {{ hydration.history.daysMet }}/{{ hydration.history.days }} met
                        </span>
                    </div>
                </div>

                <!-- Column per day, scaled against the daily target -->
                <ol class="flex items-end justify-between gap-2" :aria-label="`Water intake over the last ${hydration.history.days} days`">
                    <li
                        v-for="day in hydration.history.series"
                        :key="day.date"
                        class="flex min-w-0 flex-1 flex-col items-center gap-2"
                    >
                        <span class="text-[11px] tabular-nums text-muted">{{ day.litres }}</span>
                        <div class="flex h-24 w-full items-end justify-center">
                            <div
                                class="w-full max-w-8 rounded-t transition-[height] duration-500"
                                :class="day.met ? 'bg-success' : day.percent ? 'bg-brand' : 'bg-edge/15'"
                                :style="{ height: Math.max(3, day.percent) + '%' }"
                                :title="`${day.date}: ${day.litres} L (${day.percent}%)`"
                            />
                        </div>
                        <span
                            class="text-[11px] uppercase tracking-wider"
                            :class="day.date === today ? 'text-brand' : 'text-muted'"
                        >
                            {{ day.label }}
                        </span>
                    </li>
                </ol>

                <p class="mt-4 text-[12px] leading-relaxed text-muted">
                    Today you have logged
                    <span class="tabular-nums text-content/85">{{ hydration.consumedLitres }} L</span>
                    of {{ hydration.targetLitres.toFixed(1) }} L. Log water from the dashboard card; your target
                    scales with your most recent recorded weight.
                </p>
            </section>

            <section class="sys-panel p-5 sm:p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="sys-label">Your objectives</h2>
                    <span class="sys-pill">{{ activeGoals }} active</span>
                </div>
                <p v-if="!goals.length" class="rounded-md border border-dashed border-edge/25 p-6 text-center text-sm leading-relaxed text-muted">Every hunter starts somewhere. Set your first objective above.</p>
                <ul v-else class="grid gap-3 md:grid-cols-2 2xl:grid-cols-3">
                    <li
                        v-for="goal in goals"
                        :key="goal.id"
                        class="min-w-0 rounded-md border p-4 sm:p-5"
                        :class="goal.is_primary ? 'border-brand/40 bg-brand/5' : 'border-edge/20 bg-canvas/40'"
                    >
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <span class="sys-pill" :class="{ 'sys-pill-active': goal.status === 'active' }">{{ goal.status }}</span>
                            <span v-if="goal.is_primary" class="text-xs font-medium text-brand">Primary objective</span>
                        </div>
                        <h3 class="break-words text-base font-semibold capitalize text-content">{{ goalTypes.find((type) => type.value === goal.goal_type)?.label || goal.goal_type.replaceAll('_', ' ') }}</h3>
                        <p class="mt-2 break-words text-xl font-semibold tabular-nums text-content">{{ goal.target_value ?? '—' }} <span v-if="goal.target_value != null" class="text-sm font-normal text-muted">{{ goal.target_unit }}</span></p>
                        <p class="mt-4 flex items-center gap-2 text-xs text-muted"><Icon name="calendar" :size="14" />{{ goal.target_date ? 'Due ' + String(goal.target_date).slice(0, 10) : 'No deadline' }}</p>
                    </li>
                </ul>
            </section>

            <section class="sys-panel min-w-0 p-5 sm:p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="sys-label">Measurement history</h2>
                    <span class="text-xs text-muted">{{ history.length }} entries · Newest first</span>
                </div>
                <p v-if="!history.length" class="rounded-md border border-dashed border-edge/25 p-6 text-center text-sm leading-relaxed text-muted">Your record begins with one entry. Log a measurement to start tracking your vitals.</p>
                <ul v-if="history.length" class="space-y-3 md:hidden">
                    <li v-for="entry in history" :key="entry.id" class="rounded-md border border-edge/20 bg-canvas/40 p-4">
                        <p class="mb-4 flex items-center gap-2 text-sm font-medium text-content"><Icon name="calendar" :size="16" class="text-brand" />{{ String(entry.measured_at).slice(0, 10) }}</p>
                        <dl class="grid grid-cols-2 gap-4 min-[400px]:grid-cols-3">
                            <div><dt class="text-xs text-muted">Weight</dt><dd class="mt-1 text-sm font-semibold tabular-nums">{{ entry.weight_kg }} <span class="font-normal text-muted">kg</span></dd></div>
                            <div><dt class="text-xs text-muted">Body fat</dt><dd class="mt-1 text-sm font-semibold tabular-nums">{{ entry.body_fat_pct != null ? entry.body_fat_pct + ' %' : '—' }}</dd></div>
                            <div><dt class="text-xs text-muted">Resting HR</dt><dd class="mt-1 text-sm font-semibold tabular-nums">{{ entry.resting_heart_rate != null ? entry.resting_heart_rate + ' bpm' : '—' }}</dd></div>
                        </dl>
                    </li>
                </ul>
                <div v-if="history.length" class="hidden overflow-x-auto md:block">
                    <table class="w-full text-left text-[13px]">
                        <caption class="sr-only">Measurements, newest first</caption>
                        <thead class="text-[11px] uppercase tracking-widest text-muted">
                            <tr>
                                <th scope="col" class="pb-4 pr-4 font-medium">Date</th>
                                <th scope="col" class="pb-4 pr-4 font-medium">Weight</th>
                                <th scope="col" class="pb-4 pr-4 font-medium">Body fat</th>
                                <th scope="col" class="pb-4 font-medium">Resting HR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="entry in history" :key="entry.id" class="sys-divider">
                                <td class="py-4 pr-4 text-content">{{ String(entry.measured_at).slice(0, 10) }}</td>
                                <td class="py-4 pr-4 font-medium tabular-nums text-content">{{ entry.weight_kg }} kg</td>
                                <td class="py-4 pr-4 tabular-nums text-muted">{{ entry.body_fat_pct != null ? entry.body_fat_pct + ' %' : '—' }}</td>
                                <td class="py-4 tabular-nums text-muted">{{ entry.resting_heart_rate != null ? entry.resting_heart_rate + ' bpm' : '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </HunterLayout>
</template>

<style scoped>
.health-banner {
    background:
        radial-gradient(ellipse at top right, rgb(var(--color-violet) / .2), transparent 65%),
        linear-gradient(120deg, rgb(var(--color-surface)), rgb(var(--color-canvas)));
}

.health-page .ui-input {
    min-width: 0;
    min-height: 3rem;
    border-color: rgb(var(--color-edge) / .3);
}

.health-page label {
    min-width: 0;
}

.health-page textarea {
    min-height: 6rem;
    resize: vertical;
}

.health-page .sys-cta {
    border-radius: var(--radius-control);
    letter-spacing: .06em;
    font-size: .8125rem;
}

.health-page .sys-cta:disabled {
    transform: none;
    box-shadow: none;
}
</style>
