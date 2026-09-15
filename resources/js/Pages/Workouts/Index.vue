<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';
import ExercisePicker from '@/Components/ExercisePicker.vue';

const props = defineProps({
    workouts: { type: Object, required: true },
    exercises: { type: Array, default: () => [] },
    activeWorkout: { type: [Object, null], default: null },
});

const showForm = ref(false);

/**
 * Leaving `scheduled_date` empty starts the mission immediately; the server
 * then redirects straight into live mode.
 */
const form = useForm({
    name: '',
    scheduled_date: '',
    exercises: [{ exercise_id: '', sets: 3 }],
});

/** Index of the exercise row the picker is choosing for, or null when closed. */
const pickerRowIndex = ref(null);

const exerciseById = computed(() => new Map(props.exercises.map((exercise) => [exercise.id, exercise])));

const addRow = () => {
    if (form.exercises.length < 20) {
        form.exercises.push({ exercise_id: '', sets: 3 });
        pickerRowIndex.value = form.exercises.length - 1;
    }
};

const chooseExercise = (exercise) => {
    form.exercises[pickerRowIndex.value].exercise_id = exercise.id;
    pickerRowIndex.value = null;
};

const removeRow = (index) => {
    if (form.exercises.length > 1) {
        form.exercises.splice(index, 1);
    }
};

/** An exercise may only be chosen once; the server enforces `distinct` too. */
const takenIdsFor = (index) =>
    form.exercises
        .filter((row, rowIndex) => rowIndex !== index && row.exercise_id)
        .map((row) => Number(row.exercise_id));

const canSubmit = computed(
    () => form.name.trim().length > 0 && form.exercises.some((row) => row.exercise_id)
);

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            scheduled_date: data.scheduled_date || null,
            exercises: data.exercises
                .filter((row) => row.exercise_id)
                .map((row) => ({ exercise_id: Number(row.exercise_id), sets: Number(row.sets) })),
        }))
        .post(route('workouts.store'), {
            onSuccess: () => {
                form.reset();
                form.exercises = [{ exercise_id: '', sets: 3 }];
                showForm.value = false;
            },
        });
};

const start = (workout) => {
    router.post(route('workouts.start', workout.id));
};

const setCount = (workout) =>
    (workout.workout_exercises || []).reduce(
        (total, entry) => total + (entry.workout_sets || []).length,
        0
    );

const STATUS_LABEL = {
    planned: 'Planned',
    in_progress: 'In progress',
    completed: 'Completed',
    skipped: 'Skipped',
};
</script>

<template>
    <HunterLayout title="Training log" subtitle="Build a mission, then step into it.">
        <section
            v-if="activeWorkout"
            class="sys-panel sys-corners sys-corners-x mb-5 flex flex-wrap items-center justify-between gap-4 border-brand/45 p-5"
        >
            <div class="min-w-0">
                <p class="sys-label-sm">Mission in progress</p>
                <p class="sys-display mt-1 text-[20px] text-content">{{ activeWorkout.name }}</p>
            </div>
            <a :href="route('workouts.live.show', activeWorkout.id)" class="sys-cta max-w-xs">
                Resume <Icon name="arrowRight" :size="16" :stroke-width="2" />
            </a>
        </section>

        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 class="sys-label">Your missions</h2>
            <button
                type="button"
                class="sys-pill min-h-11 px-5"
                :class="showForm ? '' : 'sys-pill-active'"
                @click="showForm = !showForm"
            >
                <Icon :name="showForm ? 'x' : 'sparkle'" :size="13" />
                {{ showForm ? 'Cancel' : 'New mission' }}
            </button>
        </div>

        <section v-if="showForm" class="sys-panel sys-corners mb-6 p-5">
            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                        <span class="ui-label">Mission name</span>
                        <input v-model="form.name" type="text" maxlength="120" class="ui-input w-full" required />
                        <span v-if="form.errors.name" class="ui-error">{{ form.errors.name }}</span>
                    </label>
                    <label class="block">
                        <span class="ui-label">Schedule for (leave empty to start now)</span>
                        <input v-model="form.scheduled_date" type="date" class="ui-input w-full" />
                        <span v-if="form.errors.scheduled_date" class="ui-error">{{ form.errors.scheduled_date }}</span>
                    </label>
                </div>

                <div>
                    <span class="ui-label">Exercises</span>
                    <ul class="space-y-2">
                        <li v-for="(row, index) in form.exercises" :key="index" class="flex items-center gap-2">
                            <button
                                type="button"
                                class="ui-input flex min-w-0 flex-1 items-center justify-between gap-2 text-left"
                                @click="pickerRowIndex = index"
                            >
                                <span class="truncate" :class="row.exercise_id ? 'text-content' : 'text-muted'">
                                    {{ exerciseById.get(Number(row.exercise_id))?.name ?? 'Choose an exercise' }}
                                </span>
                                <Icon name="chevronDown" :size="14" class="shrink-0 text-muted" />
                            </button>
                            <label class="shrink-0">
                                <span class="sr-only">Sets</span>
                                <input
                                    v-model.number="row.sets"
                                    type="number"
                                    min="1"
                                    max="10"
                                    class="ui-input w-20"
                                    aria-label="Sets"
                                />
                            </label>
                            <button
                                type="button"
                                class="grid h-11 w-11 shrink-0 place-items-center rounded-md border border-edge/20 text-muted hover:text-danger"
                                :disabled="form.exercises.length < 2"
                                aria-label="Remove exercise"
                                @click="removeRow(index)"
                            >
                                <Icon name="x" :size="16" />
                            </button>
                        </li>
                    </ul>
                    <span v-if="form.errors.exercises" class="ui-error">{{ form.errors.exercises }}</span>

                    <button
                        type="button"
                        class="sys-pill mt-3 min-h-9"
                        :disabled="form.exercises.length >= 20"
                        @click="addRow"
                    >
                        Add exercise
                    </button>
                </div>

                <button type="submit" class="sys-cta" :disabled="!canSubmit || form.processing">
                    {{ form.processing ? 'Saving' : form.scheduled_date ? 'Schedule mission' : 'Start mission now' }}
                </button>
            </form>

            <ExercisePicker
                :show="pickerRowIndex !== null"
                :exercises="exercises"
                :excluded-ids="pickerRowIndex === null ? [] : takenIdsFor(pickerRowIndex)"
                :selected-id="pickerRowIndex === null ? null : form.exercises[pickerRowIndex]?.exercise_id || null"
                @select="chooseExercise"
                @close="pickerRowIndex = null"
            />
        </section>

        <ul v-if="workouts.data.length" class="grid gap-3 lg:grid-cols-2">
            <li
                v-for="workout in workouts.data"
                :key="workout.id"
                class="sys-panel flex flex-col p-5"
                :class="workout.status === 'in_progress' ? 'border-brand/45' : ''"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-[15px] font-medium text-content">{{ workout.name }}</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="sys-pill" :class="workout.status === 'in_progress' ? 'sys-pill-active' : ''">
                                {{ STATUS_LABEL[workout.status] || workout.status }}
                            </span>
                            <span class="sys-pill">
                                {{ (workout.workout_exercises || []).length }} exercises
                            </span>
                            <span class="sys-pill">{{ setCount(workout) }} sets</span>
                        </div>
                    </div>
                    <span v-if="workout.total_xp_awarded" class="shrink-0 text-[12px] tabular-nums text-brand">
                        +{{ workout.total_xp_awarded }} XP
                    </span>
                </div>

                <p v-if="workout.scheduled_date" class="mt-3 text-[12px] text-muted">
                    Scheduled {{ String(workout.scheduled_date).slice(0, 10) }}
                </p>

                <div class="sys-divider mt-4 flex items-center justify-end pt-4">
                    <a
                        v-if="workout.status === 'in_progress'"
                        :href="route('workouts.live.show', workout.id)"
                        class="sys-pill sys-pill-active min-h-11 px-5"
                    >
                        Resume <Icon name="arrowRight" :size="12" />
                    </a>
                    <a
                        v-else-if="workout.status === 'completed'"
                        :href="route('workouts.live.show', workout.id)"
                        class="sys-pill min-h-11 px-5 hover:border-brand/50"
                    >
                        Review <Icon name="arrowRight" :size="12" />
                    </a>
                    <button
                        v-else
                        type="button"
                        class="sys-pill min-h-11 px-5"
                        :class="activeWorkout ? 'opacity-50' : 'sys-pill-active'"
                        :disabled="Boolean(activeWorkout)"
                        :title="activeWorkout ? 'Finish your current mission first' : undefined"
                        @click="start(workout)"
                    >
                        Start <Icon name="arrowRight" :size="12" />
                    </button>
                </div>
            </li>
        </ul>

        <p v-else class="sys-panel p-10 text-center text-sm text-muted">
            No missions yet. Create one above, or enroll in a program to have them scheduled for you.
        </p>

        <Pagination :links="workouts.links" />
    </HunterLayout>
</template>
