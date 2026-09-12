<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    program: { type: Object, default: null },
    exercises: { type: Array, default: () => [] },
});

const difficulties = [
    { value: 'beginner', label: 'Beginner' },
    { value: 'intermediate', label: 'Intermediate' },
    { value: 'advanced', label: 'Advanced' },
];

const focuses = [
    { value: 'strength', label: 'Strength' },
    { value: 'hypertrophy', label: 'Hypertrophy' },
    { value: 'endurance', label: 'Endurance' },
    { value: 'general_fitness', label: 'General fitness' },
    { value: 'fat_loss', label: 'Fat loss' },
];

const isEditing = computed(() => Boolean(props.program));

const blankExercise = () => ({
    exercise_id: props.exercises[0]?.id ?? null,
    target_sets: 3,
    target_reps_min: 8,
    target_reps_max: 12,
    target_weight_pct: null,
    rest_seconds: 90,
});

const blankDay = (isRestDay = false) => ({
    name: isRestDay ? 'Rest' : '',
    is_rest_day: isRestDay,
    exercises: isRestDay ? [] : [blankExercise()],
});

const blankWeek = () => ({
    deload: false,
    days: [blankDay(), blankDay(true)],
});

/** Maps the server's nested program into the flat shape the form posts back. */
const weeksFromProgram = (program) =>
    (program.program_weeks ?? []).map((week) => ({
        deload: Boolean(week.deload),
        days: (week.program_days ?? []).map((day) => ({
            name: day.name ?? '',
            is_rest_day: Boolean(day.is_rest_day),
            exercises: (day.program_exercises ?? []).map((exercise) => ({
                exercise_id: exercise.exercise_id,
                target_sets: exercise.target_sets,
                target_reps_min: exercise.target_reps_min,
                target_reps_max: exercise.target_reps_max,
                target_weight_pct: exercise.target_weight_pct,
                rest_seconds: exercise.rest_seconds,
            })),
        })),
    }));

const form = useForm({
    name: props.program?.name ?? '',
    description: props.program?.description ?? '',
    difficulty: props.program?.difficulty ?? 'beginner',
    focus: props.program?.focus ?? 'general_fitness',
    weeks: props.program ? weeksFromProgram(props.program) : [blankWeek()],
});

const addWeek = () => form.weeks.push(blankWeek());

const duplicateWeek = (index) => {
    form.weeks.splice(index + 1, 0, JSON.parse(JSON.stringify(form.weeks[index])));
};

const removeWeek = (index) => {
    if (form.weeks.length > 1) {
        form.weeks.splice(index, 1);
    }
};

const addDay = (week) => week.days.push(blankDay());

const removeDay = (week, index) => {
    if (week.days.length > 1) {
        week.days.splice(index, 1);
    }
};

const toggleRestDay = (day) => {
    day.is_rest_day = !day.is_rest_day;
    day.exercises = day.is_rest_day ? [] : [blankExercise()];
};

const addExercise = (day) => day.exercises.push(blankExercise());

const removeExercise = (day, index) => day.exercises.splice(index, 1);

const moveExercise = (day, index, offset) => {
    const target = index + offset;

    if (target < 0 || target >= day.exercises.length) {
        return;
    }

    const [moved] = day.exercises.splice(index, 1);
    day.exercises.splice(target, 0, moved);
};

const trainingDayCount = computed(
    () => form.weeks.flatMap((week) => week.days).filter((day) => !day.is_rest_day).length,
);

const exerciseCount = computed(() =>
    form.weeks.flatMap((week) => week.days).reduce((total, day) => total + day.exercises.length, 0),
);

/** Inertia keys nested errors as `weeks.0.days.1.exercises.2.field`. */
const errorFor = (path) => form.errors[path];

const submit = () => {
    if (isEditing.value) {
        form.patch(route('programs.update', props.program.id), { preserveScroll: true });

        return;
    }

    form.post(route('programs.store'), { preserveScroll: true });
};
</script>

<template>
    <HunterLayout
        :title="isEditing ? 'Edit program' : 'Build a program'"
        subtitle="Lay out the weeks, days and exercises the system will schedule for you."
    >
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <section class="sys-panel sys-corners p-5">
                <h2 class="sys-label-sm mb-4">Program details</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="mb-1.5 block text-[12px] text-muted">Name</span>
                        <input
                            v-model="form.name"
                            type="text"
                            maxlength="120"
                            required
                            placeholder="Upper / Lower Split"
                            class="w-full rounded-md border border-edge/20 bg-canvas p-3 text-[14px] text-content focus:border-brand"
                        />
                        <span v-if="errorFor('name')" class="mt-1 block text-[12px] text-danger">{{ errorFor('name') }}</span>
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="mb-1.5 block text-[12px] text-muted">Description</span>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            maxlength="2000"
                            class="w-full rounded-md border border-edge/20 bg-canvas p-3 text-[14px] text-content focus:border-brand"
                        />
                    </label>

                    <label class="block">
                        <span class="mb-1.5 block text-[12px] text-muted">Difficulty</span>
                        <select
                            v-model="form.difficulty"
                            class="w-full rounded-md border border-edge/20 bg-canvas p-3 text-[14px] text-content focus:border-brand"
                        >
                            <option v-for="option in difficulties" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-1.5 block text-[12px] text-muted">Focus</span>
                        <select
                            v-model="form.focus"
                            class="w-full rounded-md border border-edge/20 bg-canvas p-3 text-[14px] text-content focus:border-brand"
                        >
                            <option v-for="option in focuses" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>
                </div>

                <p class="mt-4 text-[12px] text-muted">
                    {{ form.weeks.length }} week{{ form.weeks.length === 1 ? '' : 's' }} ·
                    {{ trainingDayCount }} training day{{ trainingDayCount === 1 ? '' : 's' }} ·
                    {{ exerciseCount }} exercise{{ exerciseCount === 1 ? '' : 's' }}
                </p>
            </section>

            <p v-if="errorFor('weeks')" role="alert" class="rounded-md border border-danger/25 bg-danger/10 p-4 text-sm text-danger">
                {{ errorFor('weeks') }}
            </p>

            <section v-for="(week, weekIndex) in form.weeks" :key="weekIndex" class="sys-panel p-5">
                <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-[15px] font-medium text-content">Week {{ weekIndex + 1 }}</h3>

                    <div class="flex flex-wrap items-center gap-2">
                        <label class="sys-pill cursor-pointer select-none">
                            <input v-model="week.deload" type="checkbox" class="mr-1.5 accent-brand" />
                            Deload
                        </label>
                        <button type="button" class="sys-pill min-h-9 hover:border-brand/50" @click="duplicateWeek(weekIndex)">
                            Duplicate
                        </button>
                        <button
                            type="button"
                            class="sys-pill min-h-9 hover:border-danger/50 hover:text-danger"
                            :disabled="form.weeks.length === 1"
                            :class="form.weeks.length === 1 ? 'opacity-40' : ''"
                            @click="removeWeek(weekIndex)"
                        >
                            Remove week
                        </button>
                    </div>
                </header>

                <div class="flex flex-col gap-3">
                    <div
                        v-for="(day, dayIndex) in week.days"
                        :key="dayIndex"
                        class="rounded-md border border-edge/15 p-4"
                        :class="day.is_rest_day ? 'opacity-70' : ''"
                    >
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex min-w-0 flex-wrap items-center gap-2">
                                <span class="sys-pill shrink-0">Day {{ dayIndex + 1 }}</span>
                                <input
                                    v-model="day.name"
                                    type="text"
                                    maxlength="120"
                                    :placeholder="day.is_rest_day ? 'Rest' : 'Upper Volume'"
                                    class="min-w-0 flex-1 rounded-md border border-edge/20 bg-canvas px-3 py-2 text-[13px] text-content focus:border-brand"
                                />
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="sys-pill min-h-9"
                                    :class="day.is_rest_day ? 'sys-pill-active' : 'hover:border-brand/50'"
                                    @click="toggleRestDay(day)"
                                >
                                    Rest day
                                </button>
                                <button
                                    type="button"
                                    class="sys-pill min-h-9 hover:border-danger/50 hover:text-danger"
                                    :disabled="week.days.length === 1"
                                    :class="week.days.length === 1 ? 'opacity-40' : ''"
                                    @click="removeDay(week, dayIndex)"
                                >
                                    <Icon name="x" :size="12" />
                                </button>
                            </div>
                        </div>

                        <div v-if="!day.is_rest_day" class="flex flex-col gap-2">
                            <div
                                v-for="(exercise, exerciseIndex) in day.exercises"
                                :key="exerciseIndex"
                                class="rounded-md border border-edge/10 bg-canvas/40 p-3"
                            >
                                <div class="flex flex-wrap items-center gap-2">
                                    <select
                                        v-model.number="exercise.exercise_id"
                                        class="min-w-0 flex-1 rounded-md border border-edge/20 bg-canvas px-3 py-2 text-[13px] text-content focus:border-brand"
                                    >
                                        <option v-for="option in exercises" :key="option.id" :value="option.id">
                                            {{ option.name }}
                                        </option>
                                    </select>

                                    <div class="flex items-center gap-1">
                                        <button
                                            type="button"
                                            aria-label="Move up"
                                            class="sys-pill min-h-9"
                                            @click="moveExercise(day, exerciseIndex, -1)"
                                        >
                                            <Icon name="chevronDown" :size="12" class="rotate-180" />
                                        </button>
                                        <button
                                            type="button"
                                            aria-label="Move down"
                                            class="sys-pill min-h-9"
                                            @click="moveExercise(day, exerciseIndex, 1)"
                                        >
                                            <Icon name="chevronDown" :size="12" />
                                        </button>
                                        <button
                                            type="button"
                                            aria-label="Remove exercise"
                                            class="sys-pill min-h-9 hover:border-danger/50 hover:text-danger"
                                            @click="removeExercise(day, exerciseIndex)"
                                        >
                                            <Icon name="x" :size="12" />
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-5">
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] text-muted">Sets</span>
                                        <input
                                            v-model.number="exercise.target_sets"
                                            type="number"
                                            min="1"
                                            max="20"
                                            class="w-full rounded-md border border-edge/20 bg-canvas px-2 py-1.5 text-center text-[13px] tabular-nums text-content focus:border-brand"
                                        />
                                    </label>
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] text-muted">Reps min</span>
                                        <input
                                            v-model.number="exercise.target_reps_min"
                                            type="number"
                                            min="1"
                                            max="999"
                                            class="w-full rounded-md border border-edge/20 bg-canvas px-2 py-1.5 text-center text-[13px] tabular-nums text-content focus:border-brand"
                                        />
                                    </label>
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] text-muted">Reps max</span>
                                        <input
                                            v-model.number="exercise.target_reps_max"
                                            type="number"
                                            min="1"
                                            max="999"
                                            class="w-full rounded-md border border-edge/20 bg-canvas px-2 py-1.5 text-center text-[13px] tabular-nums text-content focus:border-brand"
                                        />
                                    </label>
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] text-muted">% 1RM</span>
                                        <input
                                            v-model.number="exercise.target_weight_pct"
                                            type="number"
                                            min="0"
                                            max="200"
                                            step="0.5"
                                            placeholder="—"
                                            class="w-full rounded-md border border-edge/20 bg-canvas px-2 py-1.5 text-center text-[13px] tabular-nums text-content focus:border-brand"
                                        />
                                    </label>
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] text-muted">Rest (s)</span>
                                        <input
                                            v-model.number="exercise.rest_seconds"
                                            type="number"
                                            min="5"
                                            max="900"
                                            class="w-full rounded-md border border-edge/20 bg-canvas px-2 py-1.5 text-center text-[13px] tabular-nums text-content focus:border-brand"
                                        />
                                    </label>
                                </div>

                                <p
                                    v-if="errorFor(`weeks.${weekIndex}.days.${dayIndex}.exercises.${exerciseIndex}.target_reps_max`)"
                                    class="mt-2 text-[12px] text-danger"
                                >
                                    {{ errorFor(`weeks.${weekIndex}.days.${dayIndex}.exercises.${exerciseIndex}.target_reps_max`) }}
                                </p>
                            </div>

                            <button type="button" class="sys-pill min-h-9 self-start hover:border-brand/50" @click="addExercise(day)">
                                + Add exercise
                            </button>
                        </div>
                    </div>

                    <button type="button" class="sys-pill min-h-9 self-start hover:border-brand/50" @click="addDay(week)">
                        + Add day
                    </button>
                </div>
            </section>

            <button type="button" class="sys-pill min-h-10 self-start hover:border-brand/50" @click="addWeek">
                + Add week
            </button>

            <div class="sticky bottom-20 z-10 flex flex-wrap items-center gap-2 rounded-md border border-edge/15 bg-canvas-deep/90 p-3 backdrop-blur-xl lg:bottom-4">
                <button type="submit" class="sys-cta" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : isEditing ? 'Save changes' : 'Create program' }}
                </button>
                <Link :href="route('programs.index')" class="sys-pill min-h-10 hover:border-brand/50">Cancel</Link>
                <span v-if="form.isDirty" class="text-[12px] text-muted">Unsaved changes</span>
            </div>
        </form>
    </HunterLayout>
</template>
