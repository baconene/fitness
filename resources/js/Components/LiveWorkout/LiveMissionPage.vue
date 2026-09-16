<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import gsap from 'gsap';
import MissionHeader from './MissionHeader.vue';
import MissionOverview from './MissionOverview.vue';
import MissionProgress from './MissionProgress.vue';
import MuscleTargetMap from './MuscleTargetMap.vue';
import MissionRewards from './MissionRewards.vue';
import HealthTargets from './HealthTargets.vue';
import DailyQuestList from './DailyQuestList.vue';
import MissionSystemMessage from './MissionSystemMessage.vue';
import SystemPanel from './SystemPanel.vue';

const props = defineProps({ loggingActive: Boolean, hunter: Object, mission: { type: Object, required: true }, targetMuscles: { type: Object, default: () => ({}) }, missionRewards: { type: Object, default: () => ({}) }, healthTargets: { type: Object, default: () => ({}) }, dailyQuests: { type: Array, default: () => [] }, systemMessage: String, completedSets: Number, totalSets: Number, completedExercises: Number, exerciseCount: Number, elapsed: String, earnedXp: Number });
const recovery = computed(() => ['REST DAY', 'RECOVERY'].includes(props.mission.status));
const root = ref(null);
const showSummary = ref(false);
let media;

onMounted(() => {
    media = gsap.matchMedia();
    media.add('(prefers-reduced-motion: no-preference)', () => {
        gsap.from(root.value.querySelectorAll('[data-mission-reveal]'), { opacity: 0, y: 10, duration: .55, stagger: .07, clearProps: 'all' });
        gsap.from(root.value.querySelectorAll('.mission-bar'), { scaleX: 0, duration: .8, ease: 'power2.out', clearProps: 'transform' });
        gsap.from(root.value.querySelectorAll('.muscle-primary, .muscle-secondary'), { opacity: .15, duration: .9, clearProps: 'opacity' });
        gsap.from(root.value.querySelectorAll('[data-exercise-row]'), { opacity: 0, y: 6, duration: .35, stagger: .04, clearProps: 'all' });
    }, root.value);
});
onUnmounted(() => media?.revert());
</script>

<template>
    <div ref="root" class="live-mission space-y-3 lg:space-y-6" :class="{ 'is-logging': loggingActive, 'show-summary': showSummary }">
        <div v-if="loggingActive" class="mobile-mission-status flex items-center justify-between gap-3 lg:hidden">
            <div class="min-w-0"><h2 class="truncate text-sm font-semibold text-content">{{ mission.title }}</h2><p class="mt-1 text-xs tabular-nums text-muted">{{ completedSets }} / {{ totalSets }} sets ? {{ elapsed }}</p></div>
            <button type="button" class="min-h-11 shrink-0 px-2 text-xs text-brand" :aria-expanded="showSummary" @click="showSummary = !showSummary">{{ showSummary ? 'Hide details' : 'Mission details' }}</button>
        </div>
        <MissionHeader class="mission-hunter" :hunter="hunter" :state="mission.status" />
        <SystemPanel class="mission-command">
            <MissionOverview class="mission-overview" :mission="mission" :rewards="missionRewards" :exercise-count="exerciseCount" :total-sets="totalSets" :recovery="recovery" />
            <div class="mission-action space-y-5 px-5 pb-6 sm:px-8 lg:px-10">
                <MissionProgress v-if="!recovery" :completed-sets="completedSets" :total-sets="totalSets" :completed-exercises="completedExercises" :exercise-count="exerciseCount" :elapsed="elapsed" :earned-xp="earnedXp" :calories="mission.calories" />
                <slot name="action" />
            </div>
            <MuscleTargetMap class="mission-target border-t border-edge/20" :primary="targetMuscles.primary" :secondary="targetMuscles.secondary" :recovery="recovery" />
            <div v-if="!recovery" class="mission-current border-t border-edge/20 p-3 sm:p-8 lg:p-10"><slot name="current" /></div>
            <div v-if="!recovery" class="mission-sequence border-t border-edge/20 p-3 sm:p-8 lg:p-10"><slot name="sequence" /></div>
            <MissionRewards class="mission-rewards border-t border-edge/20" :rewards="missionRewards" :completed="mission.status === 'COMPLETED'" />
            <HealthTargets class="mission-health border-t border-edge/20" :targets="healthTargets" />
            <DailyQuestList class="mission-quests border-t border-edge/20" :quests="dailyQuests" />
        </SystemPanel>
        <div class="mission-footer flex flex-wrap items-center justify-between gap-5 px-1 pb-6">
            <MissionSystemMessage :state="mission.status" :message="systemMessage" />
            <p v-if="mission.recentWorkouts != null" class="text-xs text-muted">{{ mission.recentWorkouts }} missions completed in the last 7 days</p>
        </div>
    </div>
</template>

<style>
.live-mission { --color-brand: 78 167 255; --color-violet: 123 97 255; --color-violet-light: 159 140 255; --color-success: 92 225 185; }
.live-mission .mission-label { font-size: 10px; font-weight: 600; line-height: 1.6; letter-spacing: .18em; text-transform: uppercase; }
.live-mission .mission-button { display: inline-flex; min-height: 48px; width: 100%; justify-content: center; align-items: center; gap: 12px; padding: 12px 20px; border: 1px solid rgb(var(--color-brand) / .6); border-radius: 3px; background: linear-gradient(100deg, rgb(var(--color-brand) / .12), rgb(var(--color-violet) / .1)); color: rgb(var(--color-content)); font-size: 12px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; transition: background .2s, border-color .2s; }
.live-mission .mission-button:hover:not(:disabled) { background-color: rgb(var(--color-brand) / .1); border-color: rgb(var(--color-brand)); }
.live-mission .mission-button:disabled { opacity: .5; }
.live-mission .sys-cta { border-radius: 3px; min-height: 48px; background: rgb(var(--color-brand) / .15); border: 1px solid rgb(var(--color-brand) / .6); color: rgb(var(--color-content)); box-shadow: none; font-size: 12px; }
.live-mission .sys-cta:hover { transform: none; background: rgb(var(--color-brand) / .25); }
.live-mission .sys-panel { background: transparent; border: 0; box-shadow: none; }
.live-mission input { min-width: 0; min-height: 44px; }
.live-mission input[type='range'] { min-height: 44px; }
.mission-command { display: grid; grid-template-columns: minmax(0, 1fr); }
@media (max-width: 1023px) {
    .live-mission .mission-button { min-height: 44px; padding: 10px 12px; font-size: 11px; letter-spacing: .08em; }
    .is-logging .mission-current { order: -1; border-top: 0; }
    .is-logging:not(.show-summary) .mission-hunter,
    .is-logging:not(.show-summary) .mission-overview,
    .is-logging:not(.show-summary) .mission-action,
    .is-logging:not(.show-summary) .mission-target,
    .is-logging:not(.show-summary) .mission-rewards,
    .is-logging:not(.show-summary) .mission-health,
    .is-logging:not(.show-summary) .mission-quests,
    .is-logging:not(.show-summary) .mission-footer { display: none; }
}
@media (min-width: 1024px) {
    .mission-command { grid-template-columns: minmax(0, 1.5fr) minmax(300px, 1fr); grid-template-areas: 'overview target' 'action rewards' 'current sequence' 'health health' 'quests quests'; }
    .mission-overview { grid-area: overview; }
    .mission-action { grid-area: action; }
    .mission-target { grid-area: target; border-top: 0; border-left: 1px solid rgb(var(--color-edge) / .2); }
    .mission-rewards { grid-area: rewards; border-left: 1px solid rgb(var(--color-edge) / .2); }
    .mission-current { grid-area: current; }
    .mission-sequence { grid-area: sequence; border-left: 1px solid rgb(var(--color-edge) / .2); }
    .mission-health { grid-area: health; }
    .mission-quests { grid-area: quests; }
}
</style>
