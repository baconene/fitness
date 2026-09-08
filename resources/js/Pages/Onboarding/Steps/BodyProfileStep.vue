<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { ref } from 'vue';

defineProps({
    stepData: Object,
    isLoading: Boolean,
});

const emit = defineEmits(['complete']);

const height = ref(null);
const weight = ref(null);

const handleSubmit = () => {
    if (height.value && weight.value) {
        emit('complete', {
            height: parseFloat(height.value),
            weight: parseFloat(weight.value),
        });
    }
};
</script>

<template>
    <div>
        <h2 class="text-lg font-semibold text-content mb-4">Body Metrics</h2>
        <p class="text-content-secondary mb-6">Help us understand your starting point.</p>

        <div class="space-y-4">
            <div>
                <InputLabel for="height">Height (cm)</InputLabel>
                <TextInput
                    id="height"
                    v-model="height"
                    type="number"
                    class="mt-1 block w-full"
                    placeholder="170"
                />
            </div>
            <div>
                <InputLabel for="weight">Weight (kg)</InputLabel>
                <TextInput
                    id="weight"
                    v-model="weight"
                    type="number"
                    class="mt-1 block w-full"
                    placeholder="70"
                />
            </div>

            <PrimaryButton :disabled="!height || !weight || isLoading" @click="handleSubmit">
                {{ isLoading ? 'Loading...' : 'Next' }}
            </PrimaryButton>
        </div>
    </div>
</template>
