<template>
    <div class="bg-edge rounded-lg p-8 text-center space-y-6">
        <div>
            <p class="text-edge text-sm mb-2">Rest Time</p>
            <div class="text-6xl font-bold text-brand font-mono">
                {{ formatTime(timeRemaining) }}
            </div>
        </div>

        <div class="w-full bg-canvas rounded-full h-2 overflow-hidden">
            <div
                class="bg-brand h-full transition-all duration-1000"
                :style="{ width: progressPercent + '%' }"
            />
        </div>

        <div class="flex gap-4">
            <button
                v-if="!isActive"
                @click="resume"
                class="flex-1 bg-brand text-canvas font-bold py-3 px-4 rounded-lg"
            >
                Resume
            </button>
            <button
                v-else
                @click="pause"
                class="flex-1 bg-edge text-content font-bold py-3 px-4 rounded-lg"
            >
                Pause
            </button>
            <button
                @click="skip"
                class="flex-1 bg-edge text-content font-bold py-3 px-4 rounded-lg"
            >
                Skip
            </button>
        </div>
    </div>
</template>

<script setup>
import { useRestTimer } from '@/Composables/useRestTimer';
import { useLiveWorkoutStore } from '@/Stores/useLiveWorkoutStore';
import { computed, onMounted, watch } from 'vue';

const emit = defineEmits(['complete']);
const store = useLiveWorkoutStore();
const { timeRemaining, isActive, start, pause, resume } = useRestTimer();

const initialSeconds = computed(() => store.restTimeRemaining);

const progressPercent = computed(() => {
    return (timeRemaining.value / initialSeconds.value) * 100;
});

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const skip = () => {
    store.clearRest();
    emit('complete');
};

onMounted(() => {
    start(initialSeconds.value);
});

watch(timeRemaining, (newVal) => {
    if (newVal === 0) {
        store.clearRest();
        emit('complete');
    }
});
</script>
