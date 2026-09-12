<script setup>
import { computed, onMounted, watch } from 'vue';
import { useRestTimer } from '@/Composables/useRestTimer';
import { useLiveWorkoutStore } from '@/Stores/useLiveWorkoutStore';

const emit = defineEmits(['complete']);

const store = useLiveWorkoutStore();
const { timeRemaining, isActive, start, pause, resume, addTime } = useRestTimer();

const initialSeconds = computed(() => store.restTimeRemaining || 0);

const progressPercent = computed(() =>
    initialSeconds.value > 0 ? Math.max(0, (timeRemaining.value / initialSeconds.value) * 100) : 0,
);

const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60);

    return `${minutes}:${(seconds % 60).toString().padStart(2, '0')}`;
};

const skip = () => {
    store.clearRest();
    emit('complete');
};

const extendRest = () => {
    store.restTimeRemaining += 30;
    addTime(30);
};

onMounted(() => {
    start(initialSeconds.value);
});

watch(timeRemaining, (remaining) => {
    if (remaining === 0) {
        store.clearRest();
        emit('complete');
    }
});
</script>

<template>
    <div class="sys-panel flex flex-col gap-5 p-6 text-center">
        <div>
            <p class="sys-label-sm">Rest</p>
            <p class="mt-2 font-mono text-5xl font-semibold tabular-nums text-brand">
                {{ formatTime(timeRemaining) }}
            </p>
        </div>

        <div class="sys-track"><div class="sys-fill" :style="{ width: progressPercent + '%' }" /></div>

        <div class="flex gap-2">
            <button type="button" class="sys-pill min-h-11 flex-1 justify-center" @click="extendRest">+30 sec</button>
            <button
                v-if="!isActive"
                type="button"
                class="sys-pill min-h-11 flex-1 justify-center hover:border-brand/50"
                @click="resume"
            >
                Resume
            </button>
            <button
                v-else
                type="button"
                class="sys-pill min-h-11 flex-1 justify-center hover:border-brand/50"
                @click="pause"
            >
                Pause
            </button>
            <button type="button" class="sys-pill sys-pill-active min-h-11 flex-1 justify-center" @click="skip">
                Skip rest
            </button>
        </div>
    </div>
</template>
