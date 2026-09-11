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
    <HunterLayout title="Health" subtitle="Measurements and objectives the system tracks against.">
        <div class="mb-5 grid gap-3 sm:grid-cols-3">
            <div class="sys-panel flex items-center gap-4 p-5">
                <span class="sys-badge text-brand"><Icon name="scale" :size="20" /></span>
                <div>
                    <p class="sys-label-sm">Latest weight</p>
                    <p class="text-xl font-semibold tabular-nums text-content">
                        {{ latest && latest.weight_kg ? latest.weight_kg + ' kg' : '-' }}
                    </p>
                </div>
            </div>
            <div class="sys-panel flex items-center gap-4 p-5">
                <span class="sys-badge text-violet-light"><Icon name="body" :size="20" /></span>
                <div>
                    <p class="sys-label-sm">BMI</p>
                    <p class="text-xl font-semibold tabular-nums text-content">{{ bmi || '-' }}</p>
                </div>
            </div>
            <div class="sys-panel flex items-center gap-4 p-5">
                <span class="sys-badge text-success"><Icon name="target" :size="20" /></span>
                <div>
                    <p class="sys-label-sm">Active goals</p>
                    <p class="text-xl font-semibold tabular-nums text-content">{{ activeGoals }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="sys-panel sys-corners p-5">
                <h2 class="sys-label mb-4">Log a measurement</h2>
                <form class="space-y-3" @submit.prevent="submitMeasurement">
                    <div class="grid gap-3 sm:grid-cols-2">
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
                        </label>
                        <label class="block">
                            <span class="ui-label">Body fat (%)</span>
                            <input v-model="measurementForm.body_fat_pct" type="number" step="0.1" min="0" max="80" class="ui-input w-full" />
                        </label>
                    </div>
                    <label class="block">
                        <span class="ui-label">Resting heart rate</span>
                        <input v-model="measurementForm.resting_heart_rate" type="number" min="20" max="250" class="ui-input w-full" />
                    </label>
                    <label class="block">
                        <span class="ui-label">Notes</span>
                        <textarea v-model="measurementForm.notes" rows="2" maxlength="2000" class="ui-input w-full"></textarea>
                    </label>
                    <button type="submit" class="sys-cta" :disabled="measurementForm.processing">
                        {{ measurementForm.processing ? 'Saving' : 'Save measurement' }}
                    </button>
                </form>
            </section>

            <section class="sys-panel sys-corners p-5">
                <h2 class="sys-label mb-4">Set an objective</h2>
                <form class="space-y-3" @submit.prevent="submitGoal">
                    <label class="block">
                        <span class="ui-label">Goal type</span>
                        <select v-model="goalForm.goal_type" class="ui-input w-full" required>
                            <option v-for="type in goalTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                        </select>
                        <span v-if="goalForm.errors.goal_type" class="ui-error">{{ goalForm.errors.goal_type }}</span>
                    </label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="block">
                            <span class="ui-label">Target</span>
                            <input v-model="goalForm.target_value" type="number" step="0.1" min="0" class="ui-input w-full" />
                        </label>
                        <label class="block">
                            <span class="ui-label">Unit</span>
                            <select v-model="goalForm.target_unit" class="ui-input w-full">
                                <option v-for="unit in UNITS" :key="unit" :value="unit">{{ unit }}</option>
                            </select>
                        </label>
                    </div>
                    <label class="block">
                        <span class="ui-label">Target date</span>
                        <input v-model="goalForm.target_date" type="date" class="ui-input w-full" />
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="goalForm.is_primary" type="checkbox" class="ui-checkbox" />
                        <span class="text-[13px] text-content">Make this my primary objective</span>
                    </label>
                    <button type="submit" class="sys-cta" :disabled="goalForm.processing">
                        {{ goalForm.processing ? 'Saving' : 'Save objective' }}
                    </button>
                </form>
            </section>
        </div>

        <section v-if="goals.length" class="sys-panel mt-5 p-5">
            <h2 class="sys-label mb-4">Your objectives</h2>
            <ul>
                <li
                    v-for="(goal, index) in goals"
                    :key="goal.id"
                    class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 py-3"
                    :class="index ? 'sys-divider' : ''"
                >
                    <span class="text-[13.5px] text-content">
                        {{ goal.goal_type }}
                        <span v-if="goal.is_primary" class="sys-pill sys-pill-active ml-2">Primary</span>
                    </span>
                    <span class="text-[12px] tabular-nums text-muted">{{ goal.target_value }} {{ goal.target_unit }}</span>
                    <span class="text-[12px] text-muted">{{ goal.target_date || 'No deadline' }}</span>
                    <span class="sys-pill">{{ goal.status }}</span>
                </li>
            </ul>
        </section>

        <section v-if="history.length" class="sys-panel mt-5 p-5">
            <h2 class="sys-label mb-4">Measurement history</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead class="text-[11px] uppercase tracking-widest text-muted">
                        <tr>
                            <th class="py-2 pr-4 font-medium">Date</th>
                            <th class="py-2 pr-4 font-medium">Weight</th>
                            <th class="py-2 pr-4 font-medium">Body fat</th>
                            <th class="py-2 font-medium">Resting HR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="entry in history" :key="entry.id" class="sys-divider">
                            <td class="py-2.5 pr-4 text-content">{{ String(entry.measured_at).slice(0, 10) }}</td>
                            <td class="py-2.5 pr-4 tabular-nums text-muted">{{ entry.weight_kg }} kg</td>
                            <td class="py-2.5 pr-4 tabular-nums text-muted">{{ entry.body_fat_pct || '-' }}</td>
                            <td class="py-2.5 tabular-nums text-muted">{{ entry.resting_heart_rate || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </HunterLayout>
</template>
