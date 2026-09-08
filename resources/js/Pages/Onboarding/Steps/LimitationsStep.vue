<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { ref } from 'vue';

defineProps({
    stepData: Object,
    isLoading: Boolean,
});

const emit = defineEmits(['complete']);

const hasLimitations = ref(false);
const limitations = ref('');

const bodyAreas = ['Shoulders', 'Back', 'Knees', 'Ankles', 'Wrists', 'Elbows'];
const selectedAreas = ref([]);

const toggleArea = (area) => {
    const index = selectedAreas.value.indexOf(area);
    if (index > -1) {
        selectedAreas.value.splice(index, 1);
    } else {
        selectedAreas.value.push(area);
    }
};

const handleSubmit = () => {
    emit('complete', {
        has_limitations: hasLimitations.value,
        affected_areas: selectedAreas.value,
        limitation_description: limitations.value,
    });
};
</script>

<template>
    <div>
        <h2 class="text-lg font-semibold text-content mb-4">Health Limitations</h2>
        <p class="text-content-secondary mb-6">Do you have any injuries or health restrictions? (Optional)</p>

        <div class="mb-6">
            <label class="flex items-center">
                <input v-model="hasLimitations" type="checkbox" class="rounded" />
                <span class="ml-2 text-content">I have health limitations or injuries</span>
            </label>
        </div>

        <div v-if="hasLimitations" class="space-y-4 mb-6">
            <div>
                <p class="text-sm text-content-secondary mb-3">Affected body areas:</p>
                <div class="grid grid-cols-2 gap-2">
                    <label v-for="area in bodyAreas" :key="area" class="flex items-center">
                        <Checkbox :checked="selectedAreas.includes(area)" @change="toggleArea(area)" />
                        <span class="ml-2 text-content text-sm">{{ area }}</span>
                    </label>
                </div>
            </div>

            <div>
                <InputLabel for="limitations">Describe your limitations</InputLabel>
                <textarea
                    id="limitations"
                    v-model="limitations"
                    class="mt-1 block w-full rounded-md border-edge bg-surface text-content"
                    rows="4"
                    placeholder="e.g., shoulder impingement, avoid overhead pressing..."
                />
            </div>
        </div>

        <PrimaryButton @click="handleSubmit" :disabled="isLoading">
            {{ isLoading ? 'Loading...' : 'Next' }}
        </PrimaryButton>
    </div>
</template>
