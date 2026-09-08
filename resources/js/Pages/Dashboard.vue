<script setup>
import { onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import gsap from 'gsap';
import HunterSidebar from '@/Components/Dashboard/HunterSidebar.vue';
import HunterBottomNav from '@/Components/Dashboard/HunterBottomNav.vue';
import HunterHero from '@/Components/Dashboard/HunterHero.vue';
import BmiCard from '@/Components/Dashboard/BmiCard.vue';
import CalorieCard from '@/Components/Dashboard/CalorieCard.vue';
import WaterCard from '@/Components/Dashboard/WaterCard.vue';
import MuscleTargetMap from '@/Components/Dashboard/MuscleTargetMap.vue';
import TodayMissionCard from '@/Components/Dashboard/TodayMissionCard.vue';
import DailyQuestCard from '@/Components/Dashboard/DailyQuestCard.vue';
import WeeklyProgressCard from '@/Components/Dashboard/WeeklyProgressCard.vue';
import UpcomingWorkoutCard from '@/Components/Dashboard/UpcomingWorkoutCard.vue';
import { prefersReducedMotion } from '@/Composables/useReducedMotion';

defineProps({
    hunter: { type: Object, required: true },
    health: { type: Object, required: true },
    todayWorkout: { type: Object, required: true },
    targetMuscles: { type: Object, required: true },
    dailyQuests: { type: Array, default: () => [] },
    weeklyProgress: { type: Object, required: true },
    upcomingWorkouts: { type: Array, default: () => [] },
});

const navOpen = ref(false);
const grid = ref(null);

onMounted(() => {
    if (prefersReducedMotion()) {
        return;
    }

    const ctx = gsap.context(() => {
        gsap.from('[data-stagger]', {
            y: 20,
            opacity: 0,
            duration: 0.6,
            ease: 'power2.out',
            stagger: 0.07,
            delay: 0.15,
        });
    }, grid.value);

    return () => ctx.revert();
});
</script>

<template>
    <div class="min-h-screen bg-canvas-deep">
        <Head title="Hunter Status" />

        <HunterSidebar :open="navOpen" @close="navOpen = false" />
        <HunterBottomNav />

        <div class="lg:pl-[248px]">
            <HunterHero
                :hunter-name="hunter.name"
                :rank="hunter.rank"
                :level="hunter.level"
                :current-xp="hunter.currentXp"
                :required-xp="hunter.requiredXp"
                @toggle-nav="navOpen = true"
            />

            <main ref="grid" class="px-5 pb-28 pt-6 sm:px-8 lg:pb-10">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                    <!-- Health metrics -->
                    <div
                        data-stagger
                        class="grid grid-cols-1 gap-5 sm:grid-cols-3 lg:col-span-8"
                    >
                        <BmiCard
                            :bmi="health.bmi"
                            :category="health.bmiCategory"
                            :healthy-range="health.healthyBmiRange"
                        />
                        <CalorieCard
                            :calorie-target="health.calorieTarget"
                            :carb-percent="health.carbPercent"
                            :protein-percent="health.proteinPercent"
                            :fat-percent="health.fatPercent"
                        />
                        <WaterCard
                            :consumed-litres="health.waterConsumed"
                            :target-litres="health.waterTarget"
                        />
                    </div>

                    <!-- Target muscles: spans both rows on desktop -->
                    <div data-stagger class="lg:col-span-4 lg:row-span-2">
                        <MuscleTargetMap
                            :focus-area="targetMuscles.focusArea"
                            :primary="targetMuscles.primary"
                            :secondary="targetMuscles.secondary"
                        />
                    </div>

                    <!-- Today's mission -->
                    <div data-stagger class="lg:col-span-8">
                        <TodayMissionCard
                            :name="todayWorkout.name"
                            :focus="todayWorkout.focus"
                            :duration-minutes="todayWorkout.durationMinutes"
                            :exercises="todayWorkout.exercises"
                            :start-href="todayWorkout.startHref"
                            :details-href="todayWorkout.detailsHref"
                        />
                    </div>

                    <!-- Bottom row -->
                    <div data-stagger class="lg:col-span-4">
                        <DailyQuestCard :quests="dailyQuests" />
                    </div>
                    <div data-stagger class="lg:col-span-4">
                        <WeeklyProgressCard
                            :completion-percent="weeklyProgress.completionPercent"
                            :workouts-completed="weeklyProgress.workoutsCompleted"
                            :workouts-target="weeklyProgress.workoutsTarget"
                            :training-time="weeklyProgress.trainingTime"
                            :calories-burned="weeklyProgress.caloriesBurned"
                            :streak-days="weeklyProgress.streakDays"
                        />
                    </div>
                    <div data-stagger class="lg:col-span-4">
                        <UpcomingWorkoutCard :workouts="upcomingWorkouts" />
                    </div>
                </div>

                <!-- Closing banner -->
                <div
                    data-stagger
                    class="sys-corners sys-corners-x relative mt-5 overflow-hidden rounded-md border border-edge/20"
                >
                    <div
                        class="absolute inset-0 bg-cover bg-center opacity-45"
                        style="
                            background-image: url('https://images.unsplash.com/photo-1504681869696-d977211a5f4c?auto=format&fit=crop&w=1400&q=70');
                        "
                    />
                    <div
                        class="absolute inset-0"
                        style="
                            background: linear-gradient(
                                180deg,
                                rgb(5 9 20 / 0.86) 0%,
                                rgb(3 6 14 / 0.94) 100%
                            );
                        "
                    />
                    <div class="relative px-6 py-8 text-center">
                        <p
                            class="sys-display text-[15px] text-content/95 sm:text-[17px]"
                            style="letter-spacing: 0.08em"
                        >
                            &ldquo;DISCIPLINE TODAY, A STRONGER TOMORROW.&rdquo;
                        </p>
                        <p class="mt-2 text-[11px] text-muted" style="letter-spacing: 0.16em">
                            — HUNTER SYSTEM
                        </p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
