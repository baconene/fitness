<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

defineProps({
    stepData: Object,
    isLoading: Boolean,
});

const emit = defineEmits(['complete']);

const experience = ref(null);

const options = ['Beginner', 'Intermediate', 'Advanced'];

const handleSelect = (option) => {
    experience.value = option;
};

const handleSubmit = () => {
    if (experience.value) {
        emit('complete', { experience_level: experience.value });
    }
};
</script>

<template>
    <div>
        <h2 class="text-lg font-semibold text-content mb-4">Fitness Experience</h2>
        <p class="text-content-secondary mb-6">What's your current fitness level?</p>

        <div class="space-y-3 mb-6">
            <button
                v-for="option in options"
                :key="option"
                @click="handleSelect(option)"
                :class="[
                    'w-full p-4 rounded-lg border-2 transition-colors',
                    experience === option
                        ? 'border-brand bg-brand/10'
                        : 'border-edge hover:border-brand/50',
                ]"
            >
                <span class="text-content font-semibold">{{ option }}</span>
            </button>
        </div>

        <PrimaryButton :disabled="!experience || isLoading" @click="handleSubmit">
            {{ isLoading ? 'Loading...' : 'Next' }}
        </PrimaryButton>
    </div>
</template>
