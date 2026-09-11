<script setup>
import { computed } from 'vue';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    achievements: { type: Array, default: () => [] },
});

const unlockedCount = computed(() => props.achievements.filter((a) => a.unlocked).length);

const groups = computed(() => {
    const byCategory = {};

    for (const achievement of props.achievements) {
        const key = achievement.category || 'General';
        byCategory[key] = byCategory[key] || [];
        byCategory[key].push(achievement);
    }

    return Object.entries(byCategory).sort((a, b) => a[0].localeCompare(b[0]));
});

const percent = computed(() =>
    props.achievements.length ? (unlockedCount.value / props.achievements.length) * 100 : 0
);
</script>

<template>
    <HunterLayout title="Achievements" subtitle="Proof of the work already behind you.">
        <div class="sys-panel sys-corners mb-6 flex flex-wrap items-center gap-5 p-5">
            <span class="sys-badge text-brand"><Icon name="trophy" :size="22" /></span>
            <div>
                <p class="sys-label-sm">Unlocked</p>
                <p class="text-2xl font-semibold tabular-nums text-content">
                    {{ unlockedCount }}
                    <span class="text-base text-muted">/ {{ achievements.length }}</span>
                </p>
            </div>
            <div class="min-w-[180px] flex-1">
                <div class="sys-track"><div class="sys-fill" :style="{ width: percent + '%' }" /></div>
            </div>
        </div>

        <section v-for="group in groups" :key="group[0]" class="mb-8">
            <h2 class="sys-label mb-3">{{ group[0] }}</h2>
            <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <li
                    v-for="achievement in group[1]"
                    :key="achievement.id"
                    class="sys-panel flex items-start gap-3 p-4"
                    :class="achievement.unlocked ? 'border-brand/35' : 'opacity-60'"
                >
                    <span class="mt-0.5 shrink-0" :class="achievement.unlocked ? 'text-brand' : 'text-muted/50'">
                        <Icon :name="achievement.unlocked ? 'checkCircle' : 'circle'" :size="18" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[14px] font-medium text-content">{{ achievement.name }}</p>
                        <p class="mt-1 text-[12px] leading-relaxed text-muted">{{ achievement.description }}</p>
                    </div>
                    <span class="shrink-0 text-[11px] tabular-nums text-brand">+{{ achievement.xp }}</span>
                </li>
            </ul>
        </section>

        <p v-if="!achievements.length" class="sys-panel p-10 text-center text-sm text-muted">
            No achievements are available yet.
        </p>
    </HunterLayout>
</template>
