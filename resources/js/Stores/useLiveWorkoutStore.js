import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useLiveWorkoutStore = defineStore('liveWorkout', () => {
    const workout = ref(null);
    const currentExerciseIndex = ref(0);
    const completingSetId = ref(null);
    const restTimeRemaining = ref(0);
    const isResting = ref(false);
    const completedSetIds = ref(new Set());

    const currentExercise = computed(() => {
        if (!workout.value || !workout.value.workout_exercises) {
            return null;
        }
        return workout.value.workout_exercises[currentExerciseIndex.value];
    });

    const currentSet = computed(() => {
        if (!currentExercise.value || !currentExercise.value.workout_sets) {
            return null;
        }
        return currentExercise.value.workout_sets.find((set) => !set.is_completed);
    });

    const isLastExercise = computed(() => {
        if (!workout.value || !workout.value.workout_exercises) {
            return false;
        }
        return currentExerciseIndex.value === workout.value.workout_exercises.length - 1;
    });

    const isWorkoutComplete = computed(() => {
        if (!workout.value || !workout.value.workout_exercises) {
            return false;
        }
        return workout.value.workout_exercises.length > 0 && workout.value.workout_exercises.every((exercise) =>
            exercise.workout_sets.length > 0 &&
            exercise.workout_sets.every((set) => set.is_completed)
        );
    });

    const setWorkout = (data, exerciseIndex = 0) => {
        workout.value = JSON.parse(JSON.stringify(data));
        currentExerciseIndex.value = exerciseIndex;
    };

    /**
     * Flips the set on the loaded workout so `currentSet` and `isWorkoutComplete`
     * advance. Without this the UI stays pinned to the first set forever.
     */
    const markSetCompleted = (setId, serverSet = null) => {
        completedSetIds.value.add(setId);

        if (!workout.value?.workout_exercises) {
            return;
        }

        workout.value.workout_exercises.forEach((exercise) => {
            const match = exercise.workout_sets?.find((set) => set.id === setId);

            if (match) {
                Object.assign(match, serverSet ?? {}, { is_completed: true });
            }
        });
    };

    /** Sets left on the current exercise, ignoring the one just logged. */
    const remainingSetsOnCurrentExercise = computed(
        () => currentExercise.value?.workout_sets?.filter((set) => !set.is_completed).length ?? 0,
    );

    /** Rest length the server configured for this exercise, falling back to a sane default. */
    const currentRestSeconds = computed(() => Number(currentExercise.value?.rest_seconds) || 60);

    const moveToNextExercise = () => {
        const entries = workout.value?.workout_exercises ?? [];
        const next = entries.findIndex((entry, index) => index > currentExerciseIndex.value && entry.workout_sets?.some((set) => !set.is_completed));
        const remaining = next >= 0 ? next : entries.findIndex((entry) => entry.workout_sets?.some((set) => !set.is_completed));
        if (remaining >= 0) currentExerciseIndex.value = remaining;
    };

    const startRest = (durationSeconds) => {
        isResting.value = true;
        restTimeRemaining.value = durationSeconds;
    };

    const clearRest = () => {
        isResting.value = false;
        restTimeRemaining.value = 0;
    };

    const setCompletingSet = (setId) => {
        completingSetId.value = setId;
    };

    const reset = () => {
        workout.value = null;
        currentExerciseIndex.value = 0;
        completingSetId.value = null;
        restTimeRemaining.value = 0;
        isResting.value = false;
        completedSetIds.value.clear();
    };

    return {
        workout,
        currentExerciseIndex,
        completingSetId,
        restTimeRemaining,
        isResting,
        completedSetIds,
        currentExercise,
        currentSet,
        isLastExercise,
        isWorkoutComplete,
        remainingSetsOnCurrentExercise,
        currentRestSeconds,
        setWorkout,
        markSetCompleted,
        moveToNextExercise,
        startRest,
        clearRest,
        setCompletingSet,
        reset,
    };
});
