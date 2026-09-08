<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

defineProps({
    stepData: Object,
    isLoading: Boolean,
});

const emit = defineEmits(['complete']);

const goal = ref(null);

const options = ['Build Muscle', 'Lose Weight', 'Improve Endurance', 'General Fitness'];

const handleSelect = (option) => {
    goal.value = option;
};

const handleSubmit = () => {
    if (goal.value) {
        emit('complete', { primary_goal: goal.value });
    }
};
</script>

<template>
    <div>
        <h2 class="text-lg font-semibold text-content mb-4">Primary Goal</h2>
        <p class="text-content-secondary mb-6">What's your main fitness goal?</p>

        <div class="space-y-3 mb-6">
            <button
                v-for="option in options"
                :key="option"
                @click="handleSelect(option)"
                :class="[
                    'w-full p-4 rounded-lg border-2 transition-colors',
                    goal === option
                        ? 'border-brand bg-brand/10'
                        : 'border-edge hover:border-brand/50',
                ]"
            >
                <span class="text-content font-semibold">{{ option }}</span>
            </button>
        </div>

        <PrimaryButton :disabled="!goal || isLoading" @click="handleSubmit">
            {{ isLoading ? 'Loading...' : 'Next' }}
        </PrimaryButton>
    </div>
</template>
