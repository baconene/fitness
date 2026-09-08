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
        return workout.value.workout_exercises.every((exercise) =>
            exercise.workout_sets.every((set) => set.is_completed)
        );
    });

    const setWorkout = (data, exerciseIndex = 0) => {
        workout.value = data;
        currentExerciseIndex.value = exerciseIndex;
    };

    const markSetCompleted = (setId) => {
        completedSetIds.value.add(setId);
    };

    const moveToNextExercise = () => {
        if (!isLastExercise.value) {
            currentExerciseIndex.value++;
        }
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
        setWorkout,
        markSetCompleted,
        moveToNextExercise,
        startRest,
        clearRest,
        setCompletingSet,
        reset,
    };
});
