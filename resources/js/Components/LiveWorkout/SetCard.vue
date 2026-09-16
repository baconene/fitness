<script setup>
import { computed, ref } from 'vue';
import { useLiveWorkoutStore } from '@/Stores/useLiveWorkoutStore';

const props = defineProps({
    set: { type: Object, required: true },
    exercise: { type: Object, required: true },
    previousSet: { type: Object, default: null },
});

const emit = defineEmits(['complete']);

const store = useLiveWorkoutStore();

/** Cardio, mobility and stretching are logged by duration; the server requires it. */
const isTimed = computed(() => ['cardio', 'mobility', 'stretching'].includes(props.exercise.exercise_type));

const isSubmitting = computed(() => store.completingSetId === props.set.id);

/**
 * RPE is optional and rarely adjusted, so it stays folded away. On a phone a
 * full-width slider plus its label costs a whole row of screen.
 */
const showEffort = ref(false);

const formData = ref({
    reps: props.previousSet?.reps_completed ?? 10,
    weight: Number(props.previousSet?.weight_kg ?? 0),
    durationSeconds: props.previousSet?.duration_seconds ?? 60,
    distanceKm: Number(props.previousSet?.distance_km ?? 0),
    rpe: 5,
});

const LIMITS = {
    reps: { min: 1, max: 999, step: 1, label: 'Reps', required: true },
    weight: { min: 0, max: 9999.5, step: 2.5, label: 'Weight (kg)', required: false },
    durationSeconds: { min: 1, max: 86400, step: 15, label: 'Seconds', required: true },
    distanceKm: { min: 0, max: 1000, step: 0.5, label: 'Distance (km)', required: false },
};

const fields = computed(() => (isTimed.value ? ['durationSeconds', 'distanceKm'] : ['reps', 'weight']));

/**
 * Steppers keep the numeric keyboard shut. On a phone that keyboard covers half
 * the viewport and pushes the submit button out of reach, which is most of the
 * scrolling this screen used to demand.
 */
const nudge = (field, direction) => {
    const { min, max, step } = LIMITS[field];
    const next = Number((Number(formData.value[field] || 0) + direction * step).toFixed(2));

    formData.value[field] = Math.min(max, Math.max(min, next));
};

const submit = () => {
    if (isSubmitting.value) {
        return;
    }

    const payload = {
        weight: formData.value.weight || 0,
        rpe: formData.value.rpe,
        idempotency_key: crypto.randomUUID(),
    };

    if (isTimed.value) {
        payload.duration_seconds = formData.value.durationSeconds;

        if (formData.value.distanceKm > 0) {
            payload.distance_km = formData.value.distanceKm;
        }
    } else {
        payload.reps = formData.value.reps;
    }

    emit('complete', payload);
};
</script>

<template>
    <form class="set-entry flex flex-col gap-2 py-2 sm:gap-3 sm:py-4" @submit.prevent="submit">
        <div class="flex items-center justify-between gap-3">
            <p class="sys-label-sm">Set {{ set.set_number }}</p>
            <button
                type="button"
                class="min-h-8 text-[11px] uppercase tracking-wider text-muted hover:text-brand"
                :aria-expanded="showEffort"
                aria-controls="set-effort"
                @click="showEffort = !showEffort"
            >
                RPE {{ formData.rpe }}
            </button>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:gap-3">
            <label v-for="field in fields" :key="field" class="block">
                <span class="mb-1 block text-[11px] text-muted">{{ LIMITS[field].label }}</span>
                <div class="flex items-stretch gap-1">
                    <button
                        type="button"
                        class="stepper"
                        :aria-label="`Decrease ${LIMITS[field].label}`"
                        tabindex="-1"
                        @click="nudge(field, -1)"
                    >
                        −
                    </button>
                    <input
                        v-model.number="formData[field]"
                        type="number"
                        :required="LIMITS[field].required"
                        :min="LIMITS[field].min"
                        :max="LIMITS[field].max"
                        :step="LIMITS[field].step"
                        inputmode="decimal"
                        class="w-full min-w-0 rounded-md border bg-canvas px-1 text-center text-xl font-semibold tabular-nums text-content focus:border-brand"
                        :class="LIMITS[field].required ? 'border-brand/40' : 'border-edge/20'"
                    />
                    <button
                        type="button"
                        class="stepper"
                        :aria-label="`Increase ${LIMITS[field].label}`"
                        tabindex="-1"
                        @click="nudge(field, 1)"
                    >
                        +
                    </button>
                </div>
            </label>
        </div>

        <label v-show="showEffort" id="set-effort" class="block">
            <span class="mb-1 flex items-baseline justify-between text-[11px] text-muted">
                <span>Effort (RPE)</span>
                <span class="tabular-nums text-content/85">{{ formData.rpe }} / 10</span>
            </span>
            <input v-model.number="formData.rpe" type="range" min="1" max="10" class="w-full accent-brand" />
        </label>

        <button type="submit" class="mission-button w-full disabled:opacity-50" :disabled="isSubmitting">
            {{ isSubmitting ? 'Saving…' : 'Complete set' }}
        </button>
    </form>
</template>

<style scoped>
/* Wide enough for a thumb, short enough not to add a row's height. */
.stepper {
    flex: none;
    width: 2.75rem;
    min-height: 2.75rem;
    border-radius: 0.375rem;
    border: 1px solid rgb(var(--color-edge) / 0.25);
    background: rgb(var(--color-brand) / 0.08);
    color: rgb(var(--color-content));
    font-size: 1.125rem;
    line-height: 1;
    touch-action: manipulation;
}

.stepper:active {
    background: rgb(var(--color-brand) / 0.2);
}

/* Native spinners duplicate the steppers and steal width on a phone. */
.set-entry input[type='number'] {
    -moz-appearance: textfield;
    appearance: textfield;
}

.set-entry input[type='number']::-webkit-outer-spin-button,
.set-entry input[type='number']::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
