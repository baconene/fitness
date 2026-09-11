<script setup>
import { computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    hunter: { type: Object, required: true },
    skills: { type: Array, default: () => [] },
    titles: { type: Array, default: () => [] },
    ledger: { type: Array, default: () => [] },
});

const ATTRIBUTES = [
    ['strength', 'Strength'],
    ['endurance', 'Endurance'],
    ['agility', 'Agility'],
    ['vitality', 'Vitality'],
    ['willpower', 'Willpower'],
];

const identityForm = useForm({ codename: props.hunter.codename });

const pointsAvailable = computed(() => props.hunter.stats?.stat_points_available ?? 0);

const xpPercent = computed(() => {
    const { current, required } = props.hunter.progress || {};

    return required ? Math.min(100, Math.round((current / required) * 100)) : 0;
});

const allocate = (stat) => {
    router.post(route('hunter.stats.allocate'), { stat }, { preserveScroll: true });
};

const learn = (skill) => {
    router.post(route('hunter.skills.learn', skill.id), {}, { preserveScroll: true });
};

const equipTitle = (title) => {
    router.post(route('hunter.titles.equip', title.id), {}, { preserveScroll: true });
};
</script>

<template>
    <HunterLayout title="Hunter profile" subtitle="Who you are inside the system.">
        <section class="sys-panel sys-corners sys-corners-x mb-5 p-6">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="min-w-0">
                    <h2 class="sys-display text-[30px] leading-none text-content">{{ hunter.codename }}</h2>
                    <p class="sys-display mt-2 text-[14px] tracking-[.1em] text-brand">
                        {{ hunter.rank }}-RANK &middot; LEVEL {{ hunter.level }}
                    </p>
                </div>
                <div class="grid grid-cols-3 gap-5 text-center">
                    <div>
                        <p class="text-xl font-semibold tabular-nums text-content">{{ hunter.workouts }}</p>
                        <p class="mt-1 text-[10px] uppercase tracking-widest text-muted">Workouts</p>
                    </div>
                    <div>
                        <p class="text-xl font-semibold tabular-nums text-content">{{ hunter.quests }}</p>
                        <p class="mt-1 text-[10px] uppercase tracking-widest text-muted">Quests</p>
                    </div>
                    <div>
                        <p class="text-xl font-semibold tabular-nums text-content">{{ hunter.bosses }}</p>
                        <p class="mt-1 text-[10px] uppercase tracking-widest text-muted">Bosses</p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <div class="mb-1.5 flex items-baseline justify-between text-[12px]">
                    <span class="text-muted">Experience</span>
                    <span class="tabular-nums text-content/85">
                        {{ hunter.progress.current }} / {{ hunter.progress.required }}
                    </span>
                </div>
                <div class="sys-track"><div class="sys-fill" :style="{ width: xpPercent + '%' }" /></div>
                <p v-if="hunter.nextRank" class="mt-2 text-[11px] text-muted">
                    Next rank <span class="text-brand">{{ hunter.nextRank }}</span> at level {{ hunter.nextRankLevel }}
                </p>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="sys-panel p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="sys-label">Attributes</h2>
                    <span v-if="pointsAvailable > 0" class="sys-pill sys-pill-active">{{ pointsAvailable }} to spend</span>
                </div>
                <ul>
                    <li v-for="attribute in ATTRIBUTES" :key="attribute[0]" class="flex items-center justify-between gap-3 py-2.5">
                        <span class="text-[13.5px] text-content">{{ attribute[1] }}</span>
                        <span class="flex items-center gap-3">
                            <span class="text-[15px] font-semibold tabular-nums text-content">
                                {{ hunter.stats ? hunter.stats[attribute[0]] : 10 }}
                            </span>
                            <button
                                type="button"
                                class="grid h-8 w-8 place-items-center rounded-md border text-brand transition-colors"
                                :class="pointsAvailable > 0 ? 'border-brand/40 hover:bg-brand/10' : 'border-edge/15 opacity-40'"
                                :disabled="pointsAvailable < 1"
                                :aria-label="'Increase ' + attribute[1]"
                                @click="allocate(attribute[0])"
                            >+</button>
                        </span>
                    </li>
                </ul>
            </section>

            <section class="sys-panel p-5">
                <h2 class="sys-label mb-4">Identity</h2>
                <form class="space-y-3" @submit.prevent="identityForm.patch(route('hunter.update'), { preserveScroll: true })">
                    <label class="block">
                        <span class="ui-label">Codename</span>
                        <input v-model="identityForm.codename" type="text" maxlength="30" class="ui-input w-full" required />
                        <span v-if="identityForm.errors.codename" class="ui-error">{{ identityForm.errors.codename }}</span>
                    </label>
                    <button type="submit" class="sys-cta" :disabled="identityForm.processing">
                        {{ identityForm.processing ? 'Saving' : 'Save identity' }}
                    </button>
                </form>

                <div class="sys-divider mt-5 pt-4">
                    <p class="sys-label-sm mb-2">Total XP earned</p>
                    <p class="text-2xl font-semibold tabular-nums text-brand">{{ hunter.totalXp.toLocaleString() }}</p>
                </div>
            </section>
        </div>

        <section class="sys-panel mt-5 p-5">
            <h2 class="sys-label mb-4">Skills</h2>
            <ul class="grid gap-3 sm:grid-cols-2">
                <li
                    v-for="skill in skills"
                    :key="skill.id"
                    class="rounded-md border border-edge/15 p-4"
                    :class="skill.level > 0 ? 'border-brand/35' : ''"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[14px] font-medium text-content">{{ skill.name }}</p>
                            <p class="mt-1 text-[12px] leading-relaxed text-muted">{{ skill.description }}</p>
                        </div>
                        <span v-if="skill.level > 0" class="sys-pill sys-pill-active shrink-0">
                            Lv {{ skill.level }}/{{ skill.maxLevel }}
                        </span>
                    </div>

                    <div v-if="skill.level === 0" class="mt-3 flex items-center justify-between gap-3">
                        <span class="text-[11px] text-muted">{{ skill.requirement.description }}</span>
                        <button
                            type="button"
                            class="sys-pill min-h-9 shrink-0"
                            :class="skill.requirement.eligible ? 'sys-pill-active' : 'opacity-50'"
                            :disabled="!skill.requirement.eligible"
                            @click="learn(skill)"
                        >Learn</button>
                    </div>
                </li>
            </ul>
        </section>

        <section class="sys-panel mt-5 p-5">
            <h2 class="sys-label mb-4">Titles</h2>
            <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <li
                    v-for="title in titles"
                    :key="title.id"
                    class="rounded-md border border-edge/15 p-4"
                    :class="[title.equipped ? 'border-brand/50' : '', title.owned ? '' : 'opacity-55']"
                >
                    <p class="text-[14px] font-medium text-content">{{ title.name }}</p>
                    <p class="mt-1 text-[11px] uppercase tracking-widest text-muted">{{ title.rarity }}</p>
                    <p v-if="title.description" class="mt-2 text-[12px] leading-relaxed text-muted">{{ title.description }}</p>
                    <button
                        v-if="title.owned"
                        type="button"
                        class="sys-pill mt-3 min-h-9 w-full justify-center"
                        :class="title.equipped ? 'sys-pill-active' : 'hover:border-brand/50'"
                        @click="equipTitle(title)"
                    >{{ title.equipped ? 'Equipped' : 'Equip' }}</button>
                    <p v-else class="mt-3 text-[11px] text-muted">Locked</p>
                </li>
            </ul>
        </section>

        <section v-if="ledger.length" class="sys-panel mt-5 p-5">
            <h2 class="sys-label mb-4">Recent XP</h2>
            <ul>
                <li
                    v-for="(entry, index) in ledger"
                    :key="entry.id"
                    class="flex items-baseline justify-between gap-3 py-2.5"
                    :class="index ? 'sys-divider' : ''"
                >
                    <span class="text-[13.5px] text-content">{{ entry.source }}</span>
                    <span class="text-[13px] tabular-nums text-brand">+{{ entry.amount }}</span>
                </li>
            </ul>
        </section>
    </HunterLayout>
</template>
