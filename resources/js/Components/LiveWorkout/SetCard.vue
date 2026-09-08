<template>
    <div class="bg-edge rounded-lg p-6 space-y-4">
        <div class="text-center mb-4">
            <p class="text-edge text-sm">Set {{ set.set_number }}</p>
            <p class="text-2xl font-bold text-content">Rep Counter</p>
        </div>

        <div class="space-y-4">
            <div class="bg-surface rounded-lg p-4">
                <label class="block text-edge text-sm mb-2">Reps Completed</label>
                <input
                    v-model.number="formData.reps"
                    type="number"
                    min="0"
                    max="999"
                    class="w-full text-3xl font-bold text-center bg-canvas border-2 border-brand rounded-lg p-3 text-content"
                    inputmode="numeric"
                />
            </div>

            <div class="bg-surface rounded-lg p-4">
                <label class="block text-edge text-sm mb-2">Weight (kg)</label>
                <input
                    v-model.number="formData.weight"
                    type="number"
                    min="0"
                    step="0.5"
                    class="w-full text-3xl font-bold text-center bg-canvas border-2 border-brand rounded-lg p-3 text-content"
                    inputmode="decimal"
                />
            </div>

            <div class="bg-surface rounded-lg p-4">
                <label class="block text-edge text-sm mb-2">RPE (1-10)</label>
                <input
                    v-model.number="formData.rpe"
                    type="range"
                    min="1"
                    max="10"
                    class="w-full"
                />
                <div class="text-center text-content font-bold text-lg mt-2">{{ formData.rpe }}</div>
            </div>
        </div>

        <button
            @click="submit"
            :disabled="isSubmitting"
            class="w-full bg-brand text-canvas font-bold py-4 px-6 rounded-lg text-lg disabled:opacity-50"
        >
            {{ isSubmitting ? 'Submitting...' : 'Complete Set' }}
        </button>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useLiveWorkoutStore } from '@/Stores/useLiveWorkoutStore';

const props = defineProps({
    set: Object,
    exercise: Object,
});

const emit = defineEmits(['complete']);

const store = useLiveWorkoutStore();
const isSubmitting = ref(false);
const formData = ref({
    reps: 0,
    weight: 0,
    rpe: 5,
});

const submit = async () => {
    isSubmitting.value = true;
    try {
        emit('complete', {
            reps: formData.value.reps,
            weight: formData.value.weight,
            rpe: formData.value.rpe,
            idempotency_key: crypto.randomUUID(),
        });
        formData.value = { reps: 0, weight: 0, rpe: 5 };
    } finally {
        isSubmitting.value = false;
    }
};
</script>
