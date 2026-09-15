<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';
import WorkoutPlanEditor from '@/Components/LiveWorkout/WorkoutPlanEditor.vue';

const props = defineProps({
    quests: { type: Object, required: true },
    today: { type: String, required: true },
    completedCount: { type: Number, default: 0 },
    activeCount: { type: Number, default: 0 },
    questType: { type: String, default: 'all' },
    upcomingWorkouts: { type: Array, default: () => [] },
    exerciseOptions: { type: Array, default: () => [] },
});

const expandedWorkoutId = ref(null);

const toggleWorkout = (workout) => {
    expandedWorkoutId.value = expandedWorkoutId.value === workout.id ? null : workout.id;
};

const startWorkout = (workout) => {
    if (workout.status === 'in_progress') {
        router.visit(route('workouts.live.show', workout.id));

        return;
    }

    router.post(route('workouts.start', workout.id));
};

const percent = (quest) =>
    quest.target ? Math.min(100, Math.round((quest.current / quest.target) * 100)) : 0;

const isExpired = (quest) => Boolean(quest.expiresAt) && quest.expiresAt < props.today;

/** Claimable only once the objective is met and the quest is still live. */
const canClaim = (quest) =>
    quest.status === 'Active' &&
    quest.current >= quest.target &&
    quest.assignedDate <= props.today &&
    !isExpired(quest);

const missionFilters = [{ value: 'all', label: 'All missions' }, { value: 'daily', label: 'Daily' }, { value: 'weekly', label: 'Weekly' }];

const claim = (quest) => {
    router.post(route('missions.claim', quest.id), {}, { preserveScroll: true });
};
</script>

<template>
    <HunterLayout title="Missions" subtitle="Objectives the system has set for you.">
        <nav aria-label="Mission frequency" class="mb-5 flex flex-wrap gap-2">
            <Link v-for="filter in missionFilters" :key="filter.value"
                :href="route('missions.index', filter.value === 'all' ? {} : { type: filter.value })"
                :aria-current="questType === filter.value ? 'page' : undefined"
                class="sys-pill min-h-11 px-5" :class="questType === filter.value ? 'sys-pill-active' : 'hover:border-brand/50'"
                preserve-scroll>
                {{ filter.label }}
            </Link>
        </nav>
        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            <div class="sys-panel sys-corners flex items-center gap-4 p-5">
                <span class="sys-badge text-brand"><Icon name="scroll" :size="20" /></span>
                <div>
                    <p class="sys-label-sm">Active</p>
                    <p class="text-2xl font-semibold tabular-nums text-content">{{ activeCount }}</p>
                </div>
            </div>
            <div class="sys-panel flex items-center gap-4 p-5">
                <span class="sys-badge text-success"><Icon name="checkCircle" :size="20" /></span>
                <div>
                    <p class="sys-label-sm">Completed</p>
                    <p class="text-2xl font-semibold tabular-nums text-content">{{ completedCount }}</p>
                </div>
            </div>
            <div class="sys-panel flex items-center gap-4 p-5">
                <span class="sys-badge text-muted"><Icon name="calendar" :size="20" /></span>
                <div>
                    <p class="sys-label-sm">Today</p>
                    <p class="text-[15px] font-medium text-content">{{ today }}</p>
                </div>
            </div>
        </div>

        <section class="sys-panel mb-6 p-5">
            <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-[15px] font-medium text-content">Next sessions</h2>
                    <p class="mt-1 text-[12px] text-muted">Check what a mission involves before you start it.</p>
                </div>
                <Link :href="route('programs.index')" class="sys-pill min-h-9 hover:border-brand/50">
                    <Icon name="clipboard" :size="12" /> Manage programs
                </Link>
            </header>

            <ul v-if="upcomingWorkouts.length" class="flex flex-col gap-2">
                <li v-for="workout in upcomingWorkouts" :key="workout.id" class="rounded-md border border-edge/15 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[14px] font-medium text-content">{{ workout.name }}</p>
                            <p class="mt-1 text-[12px] text-muted">
                                <span v-if="workout.scheduledDate">{{ workout.scheduledDate }}</span>
                                <span v-if="workout.programName"> · {{ workout.programName }}</span>
                                <span> · {{ workout.exercises.length }} exercises · {{ workout.setCount }} sets</span>
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span v-if="workout.status === 'in_progress'" class="sys-pill sys-pill-active">In progress</span>
                            <button type="button" class="sys-pill min-h-9 hover:border-brand/50" @click="toggleWorkout(workout)">
                                {{ expandedWorkoutId === workout.id ? 'Hide' : 'View' }} exercises
                            </button>
                            <button type="button" class="sys-pill sys-pill-active min-h-9" @click="startWorkout(workout)">
                                {{ workout.status === 'in_progress' ? 'Resume' : 'Start' }}
                                <Icon name="arrowRight" :size="12" />
                            </button>
                        </div>
                    </div>

                    <div v-if="expandedWorkoutId === workout.id" class="mt-3 border-t border-edge/10 pt-3">
                        <p class="mb-2 text-[11px] uppercase tracking-[.14em] text-muted">
                            Session plan — adjust it before you start
                        </p>
                        <WorkoutPlanEditor
                            :workout-id="workout.id"
                            :exercises="workout.exercises"
                            :exercise-options="exerciseOptions"
                        />
                    </div>
                </li>
            </ul>

            <p v-else class="py-6 text-center text-sm text-muted">
                Nothing scheduled. Build or enroll in a program to get missions on your calendar.
            </p>
        </section>

        <ul v-if="quests.data.length" class="grid gap-3 lg:grid-cols-2">
            <li
                v-for="quest in quests.data"
                :key="quest.id"
                class="sys-panel flex flex-col p-5"
                :class="quest.status === 'Completed' ? 'opacity-70' : ''"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[15px] font-medium text-content">{{ quest.name }}</p>
                        <p v-if="quest.description" class="mt-1 text-[12px] leading-relaxed text-muted">
                            {{ quest.description }}
                        </p>
                    </div>
                    <span class="sys-pill shrink-0">{{ quest.type }}</span>
                </div>

                <div class="mt-4">
                    <div class="mb-1.5 flex items-baseline justify-between text-[12px]">
                        <span class="text-muted">Progress</span>
                        <span class="tabular-nums text-content/85">{{ quest.current }} / {{ quest.target }}</span>
                    </div>
                    <div class="sys-track"><div class="sys-fill" :style="{ width: percent(quest) + '%' }" /></div>
                    <p v-if="quest.tracking && quest.status === 'Active'" class="mt-2 text-[11px] text-muted">
                        {{ quest.tracking.hint }} Progress updates automatically.
                    </p>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <span class="text-[12px] text-brand">+{{ quest.xp }} XP</span>

                    <div class="flex items-center gap-2">
                        <span v-if="quest.status === 'Completed'" class="sys-pill sys-pill-active">
                            <Icon name="checkCircle" :size="12" /> Claimed
                        </span>
                        <span v-else-if="isExpired(quest)" class="sys-pill text-danger">Expired</span>
                        <button
                            v-else-if="canClaim(quest)"
                            type="button"
                            class="sys-pill sys-pill-active min-h-9"
                            @click="claim(quest)"
                        >
                            Claim reward
                        </button>
                        <Link
                            v-else-if="quest.tracking"
                            :href="quest.tracking.href"
                            class="sys-pill min-h-9 hover:border-brand/50"
                        >
                            {{ quest.tracking.label }} <Icon name="arrowRight" :size="12" />
                        </Link>
                        <span v-else class="text-[11px] text-muted">Progress not tracked yet</span>
                    </div>
                </div>
            </li>
        </ul>

        <p v-else class="sys-panel p-10 text-center text-sm text-muted">
            {{ questType === 'all' ? 'No missions assigned yet.' : `No ${questType} missions assigned yet.` }}
        </p>

        <Pagination :links="quests.links" />
    </HunterLayout>
</template>
