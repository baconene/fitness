import { ref, onUnmounted } from 'vue';

export function useRestTimer(initialSeconds = 60) {
    const timeRemaining = ref(initialSeconds);
    const isActive = ref(false);
    let intervalId = null;
    let deadline = null;

    const tick = () => {
        timeRemaining.value = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
        if (timeRemaining.value === 0) {
            isActive.value = false;
            clearInterval(intervalId);
        }
    };

    const run = () => {
        clearInterval(intervalId);
        deadline = Date.now() + timeRemaining.value * 1000;
        isActive.value = timeRemaining.value > 0;
        if (isActive.value) intervalId = setInterval(tick, 250);
    };

    const start = (seconds = initialSeconds) => {
        timeRemaining.value = seconds;
        run();
    };

    const pause = () => {
        if (isActive.value) tick();
        clearInterval(intervalId);
        isActive.value = false;
    };

    const resume = () => {
        run();
    };

    const stop = () => {
        clearInterval(intervalId);
        isActive.value = false;
        timeRemaining.value = initialSeconds;
    };

    const addTime = (seconds) => {
        timeRemaining.value += seconds;
        if (isActive.value) deadline += seconds * 1000;
    };

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
        addTime,
    };
}
