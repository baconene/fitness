<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { ref } from 'vue';

defineProps({
    stepData: Object,
    isLoading: Boolean,
});

const emit = defineEmits(['complete']);

const daysPerWeek = ref(3);
const selectedDays = ref([]);

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

const toggleDay = (day) => {
    const index = selectedDays.value.indexOf(day);
    if (index > -1) {
        selectedDays.value.splice(index, 1);
    } else {
        selectedDays.value.push(day);
    }
};

const handleSubmit = () => {
    if (selectedDays.value.length > 0) {
        emit('complete', {
            days_per_week: daysPerWeek.value,
            preferred_days: selectedDays.value,
        });
    }
};
</script>

<template>
    <div>
        <h2 class="text-lg font-semibold text-content mb-4">Training Availability</h2>
        <p class="text-content-secondary mb-6">When can you train each week?</p>

        <div class="mb-6">
            <InputLabel for="daysPerWeek">Days Per Week</InputLabel>
            <input
                id="daysPerWeek"
                v-model.number="daysPerWeek"
                type="range"
                min="1"
                max="7"
                class="w-full mt-2"
            />
            <p class="text-content font-semibold mt-2">{{ daysPerWeek }} days/week</p>
        </div>

        <div class="mb-6">
            <p class="text-sm text-content-secondary mb-3">Select your preferred training days:</p>
            <div class="space-y-2">
                <label v-for="day in days" :key="day" class="flex items-center">
                    <Checkbox :checked="selectedDays.includes(day)" @change="toggleDay(day)" />
                    <span class="ml-2 text-content">{{ day }}</span>
                </label>
            </div>
        </div>

        <PrimaryButton :disabled="selectedDays.length === 0 || isLoading" @click="handleSubmit">
            {{ isLoading ? 'Loading...' : 'Next' }}
        </PrimaryButton>
    </div>
</template>
