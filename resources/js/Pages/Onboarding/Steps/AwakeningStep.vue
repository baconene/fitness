<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, onMounted } from 'vue';

defineProps({
    stepData: Object,
    isLoading: Boolean,
});

const emit = defineEmits(['complete']);

const revealed = ref(false);
const displayedStats = ref({
    strength: 0,
    endurance: 0,
    agility: 0,
    vitality: 0,
    willpower: 0,
});

onMounted(() => {
    setTimeout(() => {
        revealed.value = true;
        animateStats();
    }, 500);
});

const animateStats = () => {
    const finalStats = { strength: 10, endurance: 10, agility: 10, vitality: 10, willpower: 10 };
    const steps = 20;
    let current = 0;

    const interval = setInterval(() => {
        current++;
        const progress = current / steps;
        Object.keys(finalStats).forEach((stat) => {
            displayedStats.value[stat] = Math.floor(finalStats[stat] * progress);
        });

        if (current === steps) {
            clearInterval(interval);
        }
    }, 30);
};

const handleComplete = () => {
    emit('complete', {});
};
</script>

<template>
    <div class="text-center">
        <div v-if="!revealed" class="py-12">
            <p class="text-xl text-content-secondary">Awakening your hunter...</p>
        </div>

        <div v-else class="space-y-8 py-8">
            <!-- Title -->
            <div class="animate-fade-in">
                <h2 class="text-4xl font-bold text-brand mb-2">AWAKENING COMPLETE</h2>
                <p class="text-lg text-content-secondary">Your journey as a Hunter begins</p>
            </div>

            <!-- Starting Stats -->
            <div class="grid grid-cols-5 gap-4">
                <div class="p-4 bg-edge rounded-lg">
                    <p class="text-sm text-content-secondary mb-2">Strength</p>
                    <p class="text-3xl font-bold text-content">{{ displayedStats.strength }}</p>
                </div>
                <div class="p-4 bg-edge rounded-lg">
                    <p class="text-sm text-content-secondary mb-2">Endurance</p>
                    <p class="text-3xl font-bold text-content">{{ displayedStats.endurance }}</p>
                </div>
                <div class="p-4 bg-edge rounded-lg">
                    <p class="text-sm text-content-secondary mb-2">Agility</p>
                    <p class="text-3xl font-bold text-content">{{ displayedStats.agility }}</p>
                </div>
                <div class="p-4 bg-edge rounded-lg">
                    <p class="text-sm text-content-secondary mb-2">Vitality</p>
                    <p class="text-3xl font-bold text-content">{{ displayedStats.vitality }}</p>
                </div>
                <div class="p-4 bg-edge rounded-lg">
                    <p class="text-sm text-content-secondary mb-2">Willpower</p>
                    <p class="text-3xl font-bold text-content">{{ displayedStats.willpower }}</p>
                </div>
            </div>

            <!-- Rank -->
            <div>
                <p class="text-lg text-content-secondary mb-2">Starting Rank</p>
                <p class="text-4xl font-bold text-gray-400">E</p>
            </div>

            <!-- CTA -->
            <div>
                <p class="text-content-secondary mb-4">You are now ready to begin your training.</p>
                <PrimaryButton @click="handleComplete" :disabled="isLoading" class="w-full">
                    {{ isLoading ? 'Awakening...' : 'Enter the Dashboard' }}
                </PrimaryButton>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.6s ease-out;
}
</style>
