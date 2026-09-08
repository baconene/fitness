import { ref, watch, onUnmounted } from 'vue';

export function useRestTimer(initialSeconds = 60) {
    const timeRemaining = ref(initialSeconds);
    const isActive = ref(false);
    let intervalId = null;

    const start = (seconds = initialSeconds) => {
        timeRemaining.value = seconds;
        isActive.value = true;
    };

    const pause = () => {
        isActive.value = false;
    };

    const resume = () => {
        isActive.value = true;
    };

    const stop = () => {
        isActive.value = false;
        timeRemaining.value = initialSeconds;
    };

    watch(isActive, (active) => {
        if (active) {
            intervalId = setInterval(() => {
                timeRemaining.value--;
                if (timeRemaining.value <= 0) {
                    isActive.value = false;
                    timeRemaining.value = 0;
                    clearInterval(intervalId);
                }
            }, 1000);
        } else if (intervalId) {
            clearInterval(intervalId);
        }
    });

    onUnmounted(() => {
        if (intervalId) {
            clearInterval(intervalId);
        }
    });

    return {
        timeRemaining,
        isActive,
        start,
        pause,
        resume,
        stop,
    };
}
