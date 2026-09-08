<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-content leading-tight">Workouts</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-edge overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-content">
                        <div v-if="workouts.data.length" class="space-y-4">
                            <div
                                v-for="workout in workouts.data"
                                :key="workout.id"
                                class="flex items-center justify-between p-4 bg-surface rounded-lg border border-edge"
                            >
                                <div>
                                    <h3 class="font-bold text-lg">{{ workout.name }}</h3>
                                    <p class="text-edge text-sm">{{ workout.workout_exercises.length }} exercises</p>
                                </div>
                                <Link
                                    :href="route('workouts.live.show', workout.id)"
                                    class="px-4 py-2 bg-brand text-canvas rounded font-bold hover:bg-opacity-90"
                                >
                                    Enter Live Mode
                                </Link>
                            </div>
                        </div>
                        <div v-else class="text-center text-edge">
                            <p>No workouts yet. Start your first workout!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    workouts: Object,
});
</script>
