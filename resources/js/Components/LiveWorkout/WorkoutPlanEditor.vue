<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import ExercisePicker from '@/Components/ExercisePicker.vue';

const props = defineProps({
    workoutId: { type: Number, required: true },
    /** Each: { id, name, sets, completedSets } */
    exercises: { type: Array, default: () => [] },
    exerciseOptions: { type: Array, default: () => [] },
    currentExerciseId: { type: Number, default: null },
    /** Hides the add/remove controls once the mission is finished. */
    editable: { type: Boolean, default: true },
});

const isAdding = ref(false);
const isPickerOpen = ref(false);
const pending = ref(false);
const errorMessage = ref('');

const selectedExerciseId = ref(null);
const selectedSets = ref(3);

const usedIds = computed(() => new Set(props.exercises.map((exercise) => exercise.exerciseId)));

const availableOptions = computed(() => props.exerciseOptions.filter((option) => !usedIds.value.has(option.id)));

const exerciseById = computed(() => new Map(props.exerciseOptions.map((option) => [option.id, option])));

const openPicker = () => {
    errorMessage.value = '';
    isPickerOpen.value = true;
};

const chooseExercise = (exercise) => {
    if (!isAdding.value) {
        selectedSets.value = 3;
    }

    selectedExerciseId.value = exercise.id;
    isAdding.value = true;
    isPickerOpen.value = false;
};

const addExercise = () => {
    if (!selectedExerciseId.value || pending.value) {
        return;
    }

    pending.value = true;
    errorMessage.value = '';

    router.post(
        route('workouts.exercises.add', props.workoutId),
        { exercise_id: selectedExerciseId.value, sets: selectedSets.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                isAdding.value = false;
            },
            onError: (errors) => {
                errorMessage.value = Object.values(errors).flat().join(' ');
            },
            onFinish: () => {
                pending.value = false;
            },
        },
    );
};

const removeExercise = (exercise) => {
    if (pending.value) {
        return;
    }

    pending.value = true;
    errorMessage.value = '';

    router.delete(route('workouts.exercises.remove', { workout: props.workoutId, workoutExercise: exercise.id }), {
        preserveScroll: true,
        onError: (errors) => {
            errorMessage.value = Object.values(errors).flat().join(' ');
        },
        onFinish: () => {
            pending.value = false;
        },
    });
};
</script>

<template>
    <div class="flex flex-col gap-2">
        <ol class="flex flex-col gap-1.5">
            <li
                v-for="(exercise, index) in exercises"
                :key="exercise.id"
                class="flex items-center justify-between gap-3 rounded-md border p-3"
                :class="
                    exercise.exerciseId === currentExerciseId
                        ? 'border-brand/50 bg-brand/5'
                        : 'border-edge/10 bg-canvas/40'
                "
            >
                <div class="flex min-w-0 items-baseline gap-2">
                    <span class="shrink-0 text-[12px] tabular-nums text-muted">{{ index + 1 }}.</span>
                    <span
                        class="min-w-0 truncate text-[13px]"
                        :class="
                            exercise.completedSets >= exercise.sets
                                ? 'text-muted line-through'
                                : 'text-content/90'
                        "
                    >
                        {{ exercise.name }}
                    </span>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <span class="text-[12px] tabular-nums text-muted">
                        {{ exercise.completedSets }}/{{ exercise.sets }} sets
                    </span>
                    <button
                        v-if="editable && exercise.completedSets === 0 && exercises.length > 1"
                        type="button"
                        aria-label="Remove exercise"
                        class="sys-pill min-h-8 hover:border-danger/50 hover:text-danger"
                        :disabled="pending"
                        @click="removeExercise(exercise)"
                    >
                        <Icon name="x" :size="11" />
                    </button>
                </div>
            </li>
        </ol>

        <p v-if="errorMessage" role="alert" class="text-[12px] text-danger">{{ errorMessage }}</p>

        <template v-if="editable">
            <button
                v-if="!isAdding"
                type="button"
                class="sys-pill min-h-9 self-start hover:border-brand/50"
                :disabled="!availableOptions.length"
                :class="!availableOptions.length ? 'opacity-40' : ''"
                @click="openPicker"
            >
                + Add exercise
            </button>

            <div v-else class="flex flex-wrap items-end gap-2 rounded-md border border-edge/15 p-3">
                <div class="min-w-[160px] flex-1">
                    <span class="mb-1 block text-[11px] text-muted">Exercise</span>
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-2 rounded-md border border-edge/20 bg-canvas px-3 py-2 text-left text-[13px] text-content hover:border-brand/50"
                        @click="openPicker"
                    >
                        <span class="truncate">{{ exerciseById.get(selectedExerciseId)?.name ?? 'Choose an exercise' }}</span>
                        <span class="shrink-0 text-[11px] text-brand">Change</span>
                    </button>
                </div>

                <label class="w-20">
                    <span class="mb-1 block text-[11px] text-muted">Sets</span>
                    <input
                        v-model.number="selectedSets"
                        type="number"
                        min="1"
                        max="10"
                        class="w-full rounded-md border border-edge/20 bg-canvas px-2 py-2 text-center text-[13px] tabular-nums text-content focus:border-brand"
                    />
                </label>

                <button type="button" class="sys-pill sys-pill-active min-h-10" :disabled="pending" @click="addExercise">
                    {{ pending ? 'Adding…' : 'Add' }}
                </button>
                <button type="button" class="sys-pill min-h-10 hover:border-brand/50" @click="isAdding = false">
                    Cancel
                </button>
            </div>

            <ExercisePicker
                :show="isPickerOpen"
                :exercises="exerciseOptions"
                :excluded-ids="[...usedIds]"
                :selected-id="selectedExerciseId"
                @select="chooseExercise"
                @close="isPickerOpen = false"
            />
        </template>
    </div>
</template>
