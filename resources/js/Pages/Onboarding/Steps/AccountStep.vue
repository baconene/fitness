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

const hunterName = ref(null);

const handleSubmit = () => {
    if (hunterName.value?.trim()) {
        emit('complete', {
            hunterName: hunterName.value.trim(),
        });
    }
};
</script>

<template>
    <div>
        <h2 class="text-lg font-semibold text-content mb-4">Create Your Hunter Identity</h2>
        <p class="text-content-secondary mb-6">Choose a codename for your hunter journey.</p>

        <div class="space-y-4">
            <div>
                <InputLabel for="hunterName">Hunter Codename</InputLabel>
                <TextInput
                    id="hunterName"
                    v-model="hunterName"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Enter your hunter name"
                />
            </div>

            <PrimaryButton :disabled="!hunterName?.trim() || isLoading" @click="handleSubmit">
                {{ isLoading ? 'Loading...' : 'Next' }}
            </PrimaryButton>
        </div>
    </div>
</template>
