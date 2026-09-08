<template>
    <LiveWorkoutLayout>
        <div class="max-w-md mx-auto">
            <div v-if="!store.isWorkoutComplete" class="space-y-6">
                <ExerciseHeader v-if="store.currentExercise" :exercise="store.currentExercise.exercise" />

                <SetCard
                    v-if="store.currentSet"
                    :set="store.currentSet"
                    :exercise="store.currentExercise.exercise"
                    @complete="completeSet"
                />

                <div v-if="store.isResting" class="space-y-4">
                    <RestTimer @complete="onRestComplete" />
                </div>
            </div>

            <div v-else class="text-center py-12">
                <div class="mb-6">
                    <svg class="w-16 h-16 mx-auto text-brand" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-content mb-2">Workout Complete!</h2>
                <p class="text-edge mb-6">Great effort! You've completed all exercises.</p>
                <Link :href="route('workouts.index')" class="inline-block px-6 py-2 bg-brand text-canvas rounded-lg font-bold">
                    Back to Workouts
                </Link>
            </div>
        </div>
    </LiveWorkoutLayout>
</template>

<script setup>
import { useLiveWorkoutStore } from '@/Stores/useLiveWorkoutStore';
import LiveWorkoutLayout from '@/Layouts/LiveWorkoutLayout.vue';
import ExerciseHeader from '@/Components/LiveWorkout/ExerciseHeader.vue';
import SetCard from '@/Components/LiveWorkout/SetCard.vue';
import RestTimer from '@/Components/LiveWorkout/RestTimer.vue';
import { router, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    workout: Object,
    currentExerciseIndex: Number,
});

const store = useLiveWorkoutStore();

onMounted(() => {
    store.setWorkout(props.workout, props.currentExerciseIndex);
});

const completeSet = async (data) => {
    const set = store.currentSet;
    const workoutId = props.workout.id;

    try {
        store.setCompletingSet(set.id);

        const response = await fetch(route('workouts.sets.complete', { workout: workoutId, set: set.id }), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        });

        const result = await response.json();

        if (response.ok) {
            store.markSetCompleted(set.id);
            const nextSet = store.currentExercise.workout_sets.find((s) => !s.is_completed);

            if (nextSet) {
                store.startRest(60);
            } else if (!store.isLastExercise.value) {
                store.moveToNextExercise();
                store.startRest(120);
            }
        }
    } finally {
        store.setCompletingSet(null);
    }
};

const onRestComplete = () => {
    store.clearRest();
};
</script>
