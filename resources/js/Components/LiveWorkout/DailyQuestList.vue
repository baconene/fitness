<script setup>
import { Link } from '@inertiajs/vue3';
import XpProgressBar from './XpProgressBar.vue';
import Icon from '@/Components/Icon.vue';
defineProps({ quests: { type: Array, default: () => [] } });
</script>

<template>
    <section class="p-5 sm:p-8" data-mission-reveal>
        <div class="flex flex-wrap items-center justify-between gap-2"><h3 class="mission-label text-muted">Daily quests</h3><Link :href="route('missions.index')" class="inline-flex min-h-11 items-center text-xs text-brand hover:underline">View quests →</Link></div>
        <p v-if="!quests.length" class="mt-3 text-sm leading-6 text-muted">No daily quests assigned yet. Visit Missions for your next objectives.</p>
        <ul v-else class="grid gap-x-8 sm:grid-cols-2">
            <li v-for="quest in quests" :key="quest.id" class="border-b border-edge/15 py-4">
                <div class="flex items-start gap-3"><Icon :name="quest.current >= quest.target && quest.target > 0 ? 'checkCircle' : 'circle'" :size="17" class="mt-0.5 shrink-0 text-brand" /><div class="min-w-0 flex-1"><p class="text-sm">{{ quest.name }}</p><div class="my-2 flex flex-wrap justify-between gap-2 text-xs tabular-nums text-muted"><span>{{ quest.current }} / {{ quest.target }} {{ quest.unit || '' }}</span><span class="text-violet-light">+{{ quest.xpReward }} XP</span></div><XpProgressBar :current="quest.current" :target="quest.target" :label="quest.name" /></div></div>
            </li>
        </ul>
    </section>
</template>
