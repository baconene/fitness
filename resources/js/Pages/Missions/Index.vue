<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    quests: { type: Object, required: true },
    today: { type: String, required: true },
    completedCount: { type: Number, default: 0 },
});

const percent = (quest) =>
    quest.target ? Math.min(100, Math.round((quest.current / quest.target) * 100)) : 0;

const isExpired = (quest) => Boolean(quest.expiresAt) && quest.expiresAt < props.today;

/** Claimable only once the objective is met and the quest is still live. */
const canClaim = (quest) =>
    quest.status === 'Active' &&
    quest.current >= quest.target &&
    quest.assignedDate <= props.today &&
    !isExpired(quest);

const active = computed(() => props.quests.data.filter((q) => q.status === 'Active'));

const claim = (quest) => {
    router.post(route('missions.claim', quest.id), {}, { preserveScroll: true });
};
</script>

<template>
    <HunterLayout title="Missions" subtitle="Objectives the system has set for you.">
        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            <div class="sys-panel sys-corners flex items-center gap-4 p-5">
                <span class="sys-badge text-brand"><Icon name="scroll" :size="20" /></span>
                <div>
                    <p class="sys-label-sm">Active</p>
                    <p class="text-2xl font-semibold tabular-nums text-content">{{ active.length }}</p>
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
                            :href="quest.actionHref"
                            class="sys-pill min-h-9 hover:border-brand/50"
                        >
                            Go log it <Icon name="arrowRight" :size="12" />
                        </Link>
                        <span v-else class="text-[11px] text-muted">Tracked manually</span>
                    </div>
                </div>
            </li>
        </ul>

        <p v-else class="sys-panel p-10 text-center text-sm text-muted">
            No missions assigned yet.
        </p>

        <Pagination :links="quests.links" />
    </HunterLayout>
</template>
