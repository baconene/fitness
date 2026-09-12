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

const formData = ref({
    reps: props.previousSet?.reps_completed ?? 10,
    weight: Number(props.previousSet?.weight_kg ?? 0),
    durationSeconds: props.previousSet?.duration_seconds ?? 60,
    distanceKm: Number(props.previousSet?.distance_km ?? 0),
    rpe: 5,
});

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
    <form class="sys-panel flex flex-col gap-4 py-5" @submit.prevent="submit">
        <div class="text-center">
            <p class="sys-label-sm">Set {{ set.set_number }}</p>
        </div>

        <template v-if="isTimed">
            <label class="block">
                <span class="mb-2 block text-[12px] text-muted">Duration (seconds)</span>
                <input
                    v-model.number="formData.durationSeconds"
                    type="number"
                    required
                    min="1"
                    max="86400"
                    inputmode="numeric"
                    class="w-full rounded-md border-2 border-brand/40 bg-canvas p-3 text-center text-3xl font-semibold tabular-nums text-content focus:border-brand"
                />
            </label>

            <label class="block">
                <span class="mb-2 block text-[12px] text-muted">Distance (km, optional)</span>
                <input
                    v-model.number="formData.distanceKm"
                    type="number"
                    min="0"
                    max="1000"
                    step="0.1"
                    inputmode="decimal"
                    class="w-full rounded-md border-2 border-edge/20 bg-canvas p-3 text-center text-2xl font-semibold tabular-nums text-content focus:border-brand"
                />
            </label>
        </template>

        <template v-else>
            <label class="block">
                <span class="mb-2 block text-[12px] text-muted">Reps completed</span>
                <input
                    v-model.number="formData.reps"
                    type="number"
                    required
                    min="1"
                    max="999"
                    inputmode="numeric"
                    class="w-full rounded-md border-2 border-brand/40 bg-canvas p-3 text-center text-3xl font-semibold tabular-nums text-content focus:border-brand"
                />
            </label>

            <label class="block">
                <span class="mb-2 block text-[12px] text-muted">Weight (kg)</span>
                <input
                    v-model.number="formData.weight"
                    type="number"
                    min="0"
                    max="9999.99"
                    step="0.5"
                    inputmode="decimal"
                    class="w-full rounded-md border-2 border-edge/20 bg-canvas p-3 text-center text-3xl font-semibold tabular-nums text-content focus:border-brand"
                />
            </label>
        </template>

        <label class="block">
            <span class="mb-2 flex items-baseline justify-between text-[12px] text-muted">
                <span>Effort (RPE)</span>
                <span class="tabular-nums text-content/85">{{ formData.rpe }} / 10</span>
            </span>
            <input v-model.number="formData.rpe" type="range" min="1" max="10" class="w-full accent-brand" />
        </label>

        <button type="submit" class="sys-cta w-full disabled:opacity-50" :disabled="isSubmitting">
            {{ isSubmitting ? 'Saving…' : 'Complete set' }}
        </button>
    </form>
</template>
