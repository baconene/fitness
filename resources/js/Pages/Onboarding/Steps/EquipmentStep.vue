<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { ref } from 'vue';

defineProps({
    stepData: Object,
    isLoading: Boolean,
});

const emit = defineEmits(['complete']);

const selectedEquipment = ref([]);

const equipment = [
    'Dumbbells',
    'Barbell',
    'Kettlebell',
    'Pull-up Bar',
    'Resistance Bands',
    'Yoga Mat',
    'Treadmill',
    'Bench',
];

const toggleEquipment = (item) => {
    const index = selectedEquipment.value.indexOf(item);
    if (index > -1) {
        selectedEquipment.value.splice(index, 1);
    } else {
        selectedEquipment.value.push(item);
    }
};

const handleSubmit = () => {
    emit('complete', {
        available_equipment: selectedEquipment.value.length > 0 ? selectedEquipment.value : ['Bodyweight'],
    });
};
</script>

<template>
    <div>
        <h2 class="text-lg font-semibold text-content mb-4">Available Equipment</h2>
        <p class="text-content-secondary mb-6">What equipment do you have access to? (Optional - bodyweight only is fine)</p>

        <div class="grid grid-cols-2 gap-3 mb-6">
            <label v-for="item in equipment" :key="item" class="flex items-center p-3 border border-edge rounded-lg cursor-pointer hover:bg-edge">
                <Checkbox :checked="selectedEquipment.includes(item)" @change="toggleEquipment(item)" />
                <span class="ml-2 text-content text-sm">{{ item }}</span>
            </label>
        </div>

        <PrimaryButton @click="handleSubmit" :disabled="isLoading">
            {{ isLoading ? 'Loading...' : 'Next' }}
        </PrimaryButton>
    </div>
</template>
