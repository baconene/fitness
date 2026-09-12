<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useLiveWorkoutStore } from '@/Stores/useLiveWorkoutStore';
import LiveWorkoutLayout from '@/Layouts/LiveWorkoutLayout.vue';
import ExerciseHeader from '@/Components/LiveWorkout/ExerciseHeader.vue';
import SetCard from '@/Components/LiveWorkout/SetCard.vue';
import RestTimer from '@/Components/LiveWorkout/RestTimer.vue';
import WorkoutPlanEditor from '@/Components/LiveWorkout/WorkoutPlanEditor.vue';
import Icon from '@/Components/Icon.vue';
import LiveMissionPage from '@/Components/LiveWorkout/LiveMissionPage.vue';
import MissionExerciseList from '@/Components/LiveWorkout/MissionExerciseList.vue';

const props = defineProps({
    workout: { type: Object, required: true },
    currentExerciseIndex: { type: Number, default: 0 },
    exerciseOptions: { type: Array, default: () => [] },
    hunter: { type: Object, default: null },
    mission: { type: Object, default: () => ({}) },
    targetMuscles: { type: Object, default: () => ({ primary: [], secondary: [] }) },
    dailyQuests: { type: Array, default: () => [] },
    healthTargets: { type: Object, default: () => ({}) },
    missionRewards: { type: Object, default: () => ({}) },
    systemMessage: { type: String, default: '' },
});

const store = useLiveWorkoutStore();
const errorMessage = ref('');
const isFinishing = ref(false);
const isPlanOpen = ref(false);
const isStarting = ref(false);
const currentPanel = ref(null);
const liveHunter = ref(props.hunter);
const setFeedback = ref('');
const now = ref(Date.now());
let clockInterval;

const missionData = computed(() => ({
    title: props.workout.name,
    type: 'Training',
    status: props.workout.status === 'completed' ? 'COMPLETED' : props.workout.status === 'in_progress' ? 'ACTIVE' : 'READY',
    ...props.mission,
}));
const canTrain = computed(() => props.workout.status === 'in_progress' && !['REST DAY', 'RECOVERY', 'MISSED', 'PAUSED'].includes(missionData.value.status));
const isCompleted = computed(() => props.workout.status === 'completed');
const liveExercises = computed(() => store.workout?.workout_exercises ?? props.workout.workout_exercises ?? []);
const completedExercises = computed(() => liveExercises.value.filter((entry) => entry.workout_sets?.length && entry.workout_sets.every((set) => set.is_completed)).length);
const earnedXp = computed(() => liveExercises.value.flatMap((entry) => entry.workout_sets ?? []).reduce((xp, set) => xp + (set.is_completed ? Number(set.xp_awarded || 0) : 0), 0));
const elapsed = computed(() => {
    if (!props.workout.started_at) return '00:00';
    const end = props.workout.completed_at ? Date.parse(props.workout.completed_at) : now.value;
    const seconds = Math.max(0, Math.floor((end - Date.parse(props.workout.started_at)) / 1000));
    if (!Number.isFinite(seconds)) return '00:00';
    return `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
});

const focusCurrent = async () => {
    await nextTick();
    currentPanel.value?.focus({ preventScroll: true });
    currentPanel.value?.scrollIntoView({ block: 'start', behavior: 'auto' });
};

const selectExercise = (index) => {
    if (store.completingSetId || isFinishing.value) return;
    store.currentExerciseIndex = index;
    focusCurrent();
};

const startMission = () => {
    if (isStarting.value) return;
    isStarting.value = true;
    errorMessage.value = '';
    router.post(route('workouts.start', props.workout.id), {}, {
        preserveScroll: true,
        onSuccess: focusCurrent,
        onError: (errors) => { errorMessage.value = Object.values(errors).flat().join(' ') || 'Mission could not be started.'; },
        onFinish: () => { isStarting.value = false; },
    });
};

onMounted(() => {
    store.reset();
    store.setWorkout(props.workout, props.currentExerciseIndex);
    clockInterval = setInterval(() => { now.value = Date.now(); }, 1000);
});
onUnmounted(() => { clearInterval(clockInterval); store.reset(); });
watch(() => props.hunter, (hunter) => { liveHunter.value = hunter; });

/**
 * Adding or removing an exercise redirects back here, and Inertia reuses this
 * component, so `onMounted` will not fire again — resync the store from the
 * refreshed props instead.
 */
watch(
    () => props.workout,
    (refreshed) => store.setWorkout(refreshed, props.currentExerciseIndex),
);

/** Server-truth plan, so it refreshes after an exercise is added or removed. */
const planExercises = computed(() =>
    liveExercises.value.map((workoutExercise) => ({
        id: workoutExercise.id,
        exerciseId: workoutExercise.exercise_id,
        name: workoutExercise.exercise?.name ?? 'Exercise',
        sets: workoutExercise.workout_sets?.length ?? 0,
        completedSets: (workoutExercise.workout_sets ?? []).filter((set) => set.is_completed).length,
    })),
);

const totalSets = computed(
    () => props.workout.workout_exercises?.flatMap((exercise) => exercise.workout_sets ?? []).length ?? 0,
);

const completedSets = computed(
    () =>
        store.workout?.workout_exercises
            ?.flatMap((exercise) => exercise.workout_sets ?? [])
            .filter((set) => set.is_completed).length ?? 0,
);

/** Laravel's CSRF token, injected by the root Blade view. */
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const completeSet = async (data) => {
    const set = store.currentSet;

    if (!set || store.completingSetId || !canTrain.value || isFinishing.value) {
        return;
    }

    errorMessage.value = '';
    store.setCompletingSet(set.id);

    try {
        const response = await fetch(
            route('workouts.sets.complete', { workout: props.workout.id, set: set.id }),
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-Token': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(data),
            },
        );

        const result = await response.json().catch(() => ({}));

        if (!response.ok) {
            errorMessage.value =
                result.message ||
                Object.values(result.errors ?? {})
                    .flat()
                    .join(' ') ||
                'That set could not be saved. Try again.';

            return;
        }

        store.markSetCompleted(set.id, result.set);
        liveHunter.value = result.hunter ?? liveHunter.value;
        setFeedback.value = `Set ${set.set_number} complete. +${result.xpAwarded ?? 0} XP recorded.`;
        router.reload({ only: ['dailyQuests'], preserveScroll: true });

        if (store.isWorkoutComplete) {
            await finishWorkout();

            return;
        }

        if (store.remainingSetsOnCurrentExercise > 0) {
            store.startRest(store.currentRestSeconds);

            return;
        }

        const restSeconds = store.currentRestSeconds;
        store.moveToNextExercise();
        store.startRest(restSeconds);
    } catch {
        errorMessage.value = 'Connection interrupted. Check your mission before retrying this set.';
    } finally {
        store.setCompletingSet(null);
    }
};

/** Persists the finished mission so XP, streaks and program advancement actually run. */
const finishWorkout = async () => {
    if (isFinishing.value || !canTrain.value || !store.isWorkoutComplete) {
        return;
    }

    isFinishing.value = true;
    store.clearRest();

    router.post(
        route('workouts.complete', props.workout.id),
        {},
        {
            preserveScroll: true,
            onError: (errors) => {
                errorMessage.value = Object.values(errors).flat().join(' ') || 'Could not close out this mission.';
            },
            onFinish: () => {
                isFinishing.value = false;
            },
        },
    );
};

const onRestComplete = () => {
    store.clearRest();
};
</script>

<template>
    <LiveWorkoutLayout title="Live Mission">
        <LiveMissionPage
            :hunter="liveHunter" :mission="missionData" :target-muscles="targetMuscles"
            :mission-rewards="missionRewards" :health-targets="healthTargets" :daily-quests="dailyQuests"
            :system-message="systemMessage" :completed-sets="completedSets" :total-sets="totalSets"
            :completed-exercises="completedExercises" :exercise-count="liveExercises.length" :elapsed="elapsed" :earned-xp="earnedXp"
        >
            <template #action>
                <p v-if="errorMessage && (!canTrain || store.isWorkoutComplete)" role="alert" class="border border-danger/30 bg-danger/5 p-4 text-sm text-danger">{{ errorMessage }}</p>
                <Link v-if="['REST DAY', 'RECOVERY', 'MISSED'].includes(missionData.status)" :href="route('missions.index')" class="mission-button">View recovery & daily quests <Icon name="arrowRight" :size="16" /></Link>
                <Link v-else-if="isCompleted" :href="route('workouts.index')" class="mission-button"><Icon name="checkCircle" :size="18" /> Mission complete · View training log</Link>
                <button v-else-if="canTrain && store.isWorkoutComplete" type="button" class="mission-button" :disabled="isFinishing" @click="finishWorkout">{{ isFinishing ? 'Recording mission completion…' : 'Finalize mission' }}</button>
                <button v-else-if="canTrain && totalSets" type="button" class="mission-button" @click="focusCurrent">Continue mission <Icon name="arrowRight" :size="16" /></button>
                <button v-else-if="totalSets" type="button" class="mission-button" :disabled="isStarting" @click="startMission">{{ isStarting ? 'Starting mission…' : missionData.status === 'PAUSED' ? 'Continue mission' : 'Start mission' }} <Icon name="arrowRight" :size="16" /></button>
                <button v-else type="button" class="mission-button" @click="isPlanOpen = true">Build mission · Add exercises</button>
                <p v-if="!hunter" class="text-xs text-muted">Complete your <Link :href="route('onboarding.show')" class="text-brand underline">Hunter awakening</Link> before logging sets.</p>
            </template>
            <template #current>
                <div ref="currentPanel" tabindex="-1" class="scroll-mt-24 outline-none">
                    <p v-if="errorMessage && canTrain && !store.isWorkoutComplete" role="alert" class="mb-4 border border-danger/30 bg-danger/5 p-4 text-sm text-danger">{{ errorMessage }}</p>
                    <p class="mission-label text-brand">{{ isCompleted ? 'Mission report' : 'Current objective' }}</p>
                    <p v-if="setFeedback" role="status" class="mt-4 border-l-2 border-success/60 pl-3 text-sm text-success">{{ setFeedback }}</p>
                    <div v-if="isCompleted" class="py-8">
                        <Icon name="checkCircle" :size="32" class="text-success" />
                        <h3 class="mt-4 text-xl font-semibold">Mission cleared</h3>
                        <p class="mt-3 text-sm leading-7 text-muted">Every set is logged. Your Hunter progress has been saved.</p>
                    </div>
                    <div v-else-if="store.isWorkoutComplete" class="py-8">
                        <h3 class="text-xl font-semibold">All sets recorded</h3>
                        <p class="mt-3 text-sm leading-7 text-muted">{{ isFinishing ? 'Saving your mission result…' : 'Finalize your mission above to record completion and advance your program.' }}</p>
                    </div>
                    <template v-else-if="store.currentExercise">
                        <ExerciseHeader :exercise="store.currentExercise.exercise" :target-reps="store.currentExercise.target_reps" :previous-set="store.currentExercise.previous_set" />
                        <p class="mb-3 text-xs text-muted">{{ store.currentSet ? 'Set ' + store.currentSet.set_number + ' / ' + store.currentExercise.workout_sets.length : 'All sets recorded for this exercise.' }}</p>
                        <RestTimer v-if="canTrain && store.isResting" @complete="onRestComplete" />
                        <SetCard v-else-if="canTrain && store.currentSet && hunter" :key="store.currentSet.id" :set="store.currentSet" :exercise="store.currentExercise.exercise" :previous-set="store.currentExercise.previous_set" @complete="completeSet" />
                        <p v-else-if="!canTrain" class="py-4 text-sm text-muted">Start this mission to begin recording sets.</p>
                        <button v-else-if="!store.currentSet" type="button" class="mission-button" @click="store.moveToNextExercise()">Next unfinished exercise <Icon name="arrowRight" :size="16" /></button>
                    </template>
                    <p v-else class="mt-4 text-sm text-muted">Add your first exercise using the mission plan.</p>
                </div>
            </template>
            <template #sequence>
                <MissionExerciseList :exercises="liveExercises" :current-id="store.currentExercise?.id" :disabled="!!store.completingSetId || isFinishing" @select="selectExercise" />
                <div v-if="!isCompleted" class="mt-5">
                    <button type="button" class="flex min-h-11 w-full items-center justify-between gap-3 text-xs text-brand" :aria-expanded="isPlanOpen" aria-controls="mission-plan-editor" @click="isPlanOpen = !isPlanOpen"><span>{{ isPlanOpen ? 'Close' : 'Edit' }} mission plan</span><Icon name="chevronDown" :size="16" :class="isPlanOpen ? 'rotate-180' : ''" /></button>
                    <fieldset v-if="isPlanOpen" id="mission-plan-editor" class="mt-3" :disabled="!!store.completingSetId || isFinishing"><WorkoutPlanEditor :workout-id="workout.id" :exercises="planExercises" :exercise-options="exerciseOptions" :current-exercise-id="store.currentExercise?.exercise_id ?? null" /></fieldset>
                </div>
            </template>
        </LiveMissionPage>
    </LiveWorkoutLayout>
</template>
