<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    dungeons: { type: Array, default: () => [] },
    activeRun: { type: [Object, null], default: null },
    runs: { type: Array, default: () => [] },
    bosses: { type: Array, default: () => [] },
    encounter: { type: [Object, null], default: null },
});

const challenging = ref(null);
const lastChallenged = ref(null);
const challengeError = ref('');

/**
 * Starting an encounter only arms it; damage comes from completing workouts,
 * so the page just needs to reflect that it is now active.
 */
const challengeBoss = (boss) => {
    if (challenging.value) {
        return;
    }

    challenging.value = boss.id;
    lastChallenged.value = boss.id;
    challengeError.value = '';

    router.post(route('bosses.challenge', boss.id), {}, {
        preserveScroll: true,
        onError: (errors) => {
            challengeError.value = Object.values(errors).flat().join(' ') || 'That boss could not be challenged.';
        },
        onFinish: () => {
            challenging.value = null;
        },
    });
};

const runProgress = () => {
    if (!props.activeRun || !props.activeRun.dungeon) {
        return 0;
    }

    const total = props.activeRun.dungeon.floor_count || 1;

    return Math.min(100, Math.round((props.activeRun.current_floor / total) * 100));
};

const enter = (dungeon) => {
    router.post(route('dungeons.enter', dungeon.id), {}, { preserveScroll: true });
};

const abandon = () => {
    router.post(route('dungeons.abandon', props.activeRun.id), {}, { preserveScroll: true });
};
</script>

<template>
    <HunterLayout title="Dungeons" subtitle="Timed gauntlets and the bosses waiting at the end.">
        <section v-if="activeRun" class="sys-panel sys-corners sys-corners-x mb-6 border-brand/40 p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="sys-label-sm">Run in progress</p>
                    <h2 class="sys-display mt-1 text-[22px] text-content">
                        {{ activeRun.dungeon ? activeRun.dungeon.name : 'Dungeon' }}
                    </h2>
                    <p class="mt-2 text-[13px] text-muted">
                        Floor {{ activeRun.current_floor }} of
                        {{ activeRun.dungeon ? activeRun.dungeon.floor_count : '?' }}
                        <span class="text-brand"> &middot; +{{ activeRun.total_xp_earned }} XP</span>
                    </p>
                </div>
                <button type="button" class="sys-pill min-h-9 hover:border-danger/50 hover:text-danger" @click="abandon">
                    Abandon run
                </button>
            </div>
            <div class="sys-track mt-4"><div class="sys-fill" :style="{ width: runProgress() + '%' }" /></div>
        </section>

        <section v-if="encounter" class="sys-panel mb-6 border-danger/35 p-5">
            <p class="sys-label-sm">Boss encounter active</p>
            <h2 class="sys-display mt-1 text-[20px] text-content">
                {{ encounter.boss ? encounter.boss.name : 'Boss' }}
            </h2>
            <p class="mt-2 text-[13px] text-muted">
                {{ encounter.current_health }} HP remaining
            </p>
        </section>

        <h2 class="sys-label mb-3">Available dungeons</h2>
        <ul v-if="dungeons.length" class="mb-8 grid gap-3 lg:grid-cols-2">
            <li v-for="dungeon in dungeons" :key="dungeon.id" class="sys-panel flex flex-col p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-[15px] font-medium text-content">{{ dungeon.name }}</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="sys-pill">Rank {{ dungeon.rank }}</span>
                            <span class="sys-pill">{{ dungeon.floors }} floors</span>
                            <span class="sys-pill">{{ dungeon.difficulty }}</span>
                        </div>
                    </div>
                    <span class="sys-badge shrink-0 text-violet-light"><Icon name="castle" :size="20" /></span>
                </div>

                <p v-if="dungeon.description" class="mt-3 flex-1 text-[12.5px] leading-relaxed text-muted">
                    {{ dungeon.description }}
                </p>

                <div class="sys-divider mt-4 flex items-center justify-between pt-4">
                    <span v-if="dungeon.paidEntry" class="text-[11px] text-muted">Entry fee required</span>
                    <span v-else-if="!dungeon.available" class="text-[11px] text-muted">Rank too low</span>
                    <span v-else class="text-[11px] text-muted">Ready</span>

                    <button
                        type="button"
                        class="sys-pill min-h-9"
                        :class="dungeon.available && !activeRun ? 'sys-pill-active' : 'opacity-50'"
                        :disabled="!dungeon.available || Boolean(activeRun)"
                        @click="enter(dungeon)"
                    >
                        Enter <Icon name="arrowRight" :size="12" />
                    </button>
                </div>
            </li>
        </ul>
        <p v-else class="sys-panel mb-8 p-10 text-center text-sm text-muted">No dungeons available yet.</p>

        <h2 class="sys-label mb-3">Bosses</h2>
        <ul v-if="bosses.length" class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <li v-for="boss in bosses" :key="boss.id" class="sys-panel p-4" :class="boss.available ? '' : 'opacity-60'">
                <h3 class="text-[14px] font-medium text-content">{{ boss.name }}</h3>
                <div class="mt-2 flex flex-wrap gap-2">
                    <span class="sys-pill">Rank {{ boss.rank }}</span>
                    <span class="sys-pill">{{ boss.health }} HP</span>
                    <span class="sys-pill text-brand">+{{ boss.xp }} XP</span>
                </div>
                <p v-if="boss.description" class="mt-2 text-[12px] leading-relaxed text-muted">{{ boss.description }}</p>

                <!-- Damage is dealt by completing workouts, so only one encounter runs at a time. -->
                <div class="mt-3">
                    <button
                        v-if="boss.available && !encounter"
                        type="button"
                        class="sys-pill sys-pill-active min-h-9"
                        :disabled="challenging === boss.id"
                        @click="challengeBoss(boss)"
                    >
                        {{ challenging === boss.id ? 'Starting…' : 'Challenge' }}
                        <Icon name="arrowRight" :size="12" />
                    </button>
                    <span v-else-if="encounter && encounter.boss_id === boss.id" class="sys-pill sys-pill-active">
                        <Icon name="flame" :size="12" /> Engaged
                    </span>
                    <span v-else-if="encounter" class="text-[11px] text-muted">
                        Finish your active encounter first.
                    </span>
                    <span v-else class="text-[11px] text-muted">Reach rank {{ boss.rank }} to challenge.</span>
                </div>

                <p v-if="challengeError && challenging === null && lastChallenged === boss.id" class="mt-2 text-[12px] text-danger">
                    {{ challengeError }}
                </p>
            </li>
        </ul>
        <p v-else class="sys-panel mb-8 p-8 text-center text-sm text-muted">No bosses available yet.</p>

        <section v-if="runs.length" class="sys-panel p-5">
            <h2 class="sys-label mb-4">Past runs</h2>
            <ul>
                <li
                    v-for="(run, index) in runs"
                    :key="run.id"
                    class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 py-3"
                    :class="index ? 'sys-divider' : ''"
                >
                    <span class="text-[13.5px] text-content">{{ run.dungeon ? run.dungeon.name : 'Dungeon' }}</span>
                    <span class="text-[12px] tabular-nums text-muted">Floor {{ run.current_floor }}</span>
                    <span class="text-[12px] tabular-nums text-brand">+{{ run.total_xp_earned }} XP</span>
                    <span class="sys-pill">{{ run.status }}</span>
                </li>
            </ul>
        </section>
    </HunterLayout>
</template>
