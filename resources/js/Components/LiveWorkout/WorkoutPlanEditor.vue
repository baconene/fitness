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

const request = (perform) => {
    if (pending.value) {
        return;
    }

    pending.value = true;
    errorMessage.value = '';

    perform({
        preserveScroll: true,
        onError: (errors) => {
            errorMessage.value = Object.values(errors).flat().join(' ');
        },
        onFinish: () => {
            pending.value = false;
        },
    });
};

const moveExercise = (exercise, direction) =>
    request((options) =>
        router.patch(
            route('workouts.exercises.move', { workout: props.workoutId, workoutExercise: exercise.id }),
            { direction },
            options,
        ),
    );

/**
 * Touch drag-to-reorder.
 *
 * Pointer events rather than HTML5 drag-and-drop, which does not fire on touch.
 * The list is reordered optimistically while dragging so the row follows the
 * finger, then the final position is sent once on release.
 */
const listEl = ref(null);
const dragFrom = ref(null);
const dragTo = ref(null);

/** The order shown while dragging; the server order at rest. */
const orderedExercises = computed(() => {
    if (dragFrom.value === null || dragTo.value === null || dragFrom.value === dragTo.value) {
        return props.exercises;
    }

    const rows = [...props.exercises];
    const [moved] = rows.splice(dragFrom.value, 1);
    rows.splice(dragTo.value, 0, moved);

    return rows;
});

const startDrag = (index, event) => {
    if (!props.editable || pending.value || props.exercises.length < 2) {
        return;
    }

    dragFrom.value = index;
    dragTo.value = index;
    event.currentTarget.setPointerCapture?.(event.pointerId);
};

const onDragMove = (event) => {
    if (dragFrom.value === null) {
        return;
    }

    // Stops the page scrolling under the finger mid-drag.
    event.preventDefault();

    const rows = [...(listEl.value?.querySelectorAll('[data-row]') ?? [])];
    const landed = rows.findIndex((row) => event.clientY < row.getBoundingClientRect().bottom);

    dragTo.value = landed === -1 ? rows.length - 1 : landed;
};

const endDrag = () => {
    const from = dragFrom.value;
    const to = dragTo.value;
    const exercise = from === null ? null : props.exercises[from];

    dragFrom.value = null;
    dragTo.value = null;

    if (!exercise || to === null || from === to) {
        return;
    }

    request((options) =>
        router.patch(
            route('workouts.exercises.move', { workout: props.workoutId, workoutExercise: exercise.id }),
            { position: to + 1 },
            options,
        ),
    );
};

/** Keyboard equivalent, so the handle is not pointer-only. */
const nudgeFromHandle = (exercise, index, event) => {
    const direction = event.key === 'ArrowUp' ? 'up' : 'down';

    if ((direction === 'up' && index === 0) || (direction === 'down' && index === props.exercises.length - 1)) {
        return;
    }

    event.preventDefault();
    moveExercise(exercise, direction);
};

/** Sets cannot drop below what has already been logged, so the floor moves up. */
const changeSets = (exercise, delta) => {
    const sets = exercise.sets + delta;

    if (sets < Math.max(1, exercise.completedSets) || sets > 10) {
        return;
    }

    request((options) =>
        router.patch(
            route('workouts.exercises.sets', { workout: props.workoutId, workoutExercise: exercise.id }),
            { sets },
            options,
        ),
    );
};
</script>

<template>
    <div class="flex flex-col gap-2">
        <ol
            ref="listEl"
            class="flex flex-col gap-1.5"
            @pointermove="onDragMove"
            @pointerup="endDrag"
            @pointercancel="endDrag"
        >
            <li
                v-for="(exercise, index) in orderedExercises"
                :key="exercise.id"
                data-row
                class="flex items-center justify-between gap-2 rounded-md border p-3 transition-colors"
                :class="[
                    exercise.exerciseId === currentExerciseId
                        ? 'border-brand/50 bg-brand/5'
                        : 'border-edge/10 bg-canvas/40',
                    dragFrom !== null && orderedExercises[dragTo]?.id === exercise.id ? 'border-brand bg-brand/10' : '',
                ]"
            >
                <div class="flex min-w-0 items-center gap-2">
                    <!-- Drag handle: the primary reorder affordance on touch. -->
                    <button
                        v-if="editable && exercises.length > 1"
                        type="button"
                        class="drag-handle sm:hidden"
                        :aria-label="`Reorder ${exercise.name}. Use arrow keys, or drag.`"
                        :disabled="pending"
                        @pointerdown="startDrag(index, $event)"
                        @keydown.up="nudgeFromHandle(exercise, index, $event)"
                        @keydown.down="nudgeFromHandle(exercise, index, $event)"
                    >
                        <span aria-hidden="true">⠿</span>
                    </button>
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

                <div class="flex shrink-0 items-center gap-1.5">
                    <!-- Set count; the floor is whatever has already been logged. -->
                    <template v-if="editable">
                        <button
                            type="button"
                            :aria-label="`Remove a set from ${exercise.name}`"
                            class="sys-pill min-h-8 px-2 hover:border-brand/50 disabled:opacity-30"
                            :disabled="pending || exercise.sets <= Math.max(1, exercise.completedSets)"
                            @click="changeSets(exercise, -1)"
                        >
                            −
                        </button>
                        <span class="min-w-[54px] text-center text-[12px] tabular-nums text-muted">
                            {{ exercise.completedSets }}/{{ exercise.sets }}
                        </span>
                        <button
                            type="button"
                            :aria-label="`Add a set to ${exercise.name}`"
                            class="sys-pill min-h-8 px-2 hover:border-brand/50 disabled:opacity-30"
                            :disabled="pending || exercise.sets >= 10"
                            @click="changeSets(exercise, 1)"
                        >
                            +
                        </button>
                    </template>
                    <span v-else class="text-[12px] tabular-nums text-muted">
                        {{ exercise.completedSets }}/{{ exercise.sets }} sets
                    </span>

                    <!-- Reorder on pointer devices; phones use the drag handle. -->
                    <template v-if="editable && exercises.length > 1">
                        <button
                            type="button"
                            :aria-label="`Move ${exercise.name} earlier`"
                            class="sys-pill hidden min-h-8 px-2 hover:border-brand/50 disabled:opacity-30 sm:inline-flex"
                            :disabled="pending || index === 0"
                            @click="moveExercise(exercise, 'up')"
                        >
                            <Icon name="chevronDown" :size="11" class="rotate-180" />
                        </button>
                        <button
                            type="button"
                            :aria-label="`Move ${exercise.name} later`"
                            class="sys-pill hidden min-h-8 px-2 hover:border-brand/50 disabled:opacity-30 sm:inline-flex"
                            :disabled="pending || index === exercises.length - 1"
                            @click="moveExercise(exercise, 'down')"
                        >
                            <Icon name="chevronDown" :size="11" />
                        </button>
                    </template>

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

<style scoped>
/*
 * touch-action: none is what makes the drag work on a phone — without it the
 * browser claims the gesture for scrolling before pointermove ever fires.
 */
.drag-handle {
    flex: none;
    display: grid;
    place-items: center;
    width: 2.25rem;
    min-height: 2.25rem;
    margin-left: -0.25rem;
    border-radius: 0.375rem;
    color: rgb(var(--color-muted));
    font-size: 1rem;
    line-height: 1;
    touch-action: none;
    cursor: grab;
}

.drag-handle:active {
    background: rgb(var(--color-brand) / 0.12);
    color: rgb(var(--color-brand));
    cursor: grabbing;
}

.drag-handle:disabled {
    opacity: 0.4;
}
</style>
