<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    recent: { type: Array, default: () => [] },
    unlockedCount: { type: Number, default: 0 },
    totalCount: { type: Number, default: 0 },
    next: { type: [Object, null], default: null },
});

const nextProgress = computed(() => {
    if (!props.next?.target) {
        return 0;
    }

    return Math.min(100, Math.round((props.next.current / props.next.target) * 100));
});
</script>

<template>
    <section class="sys-panel sys-panel-hover sys-corners sys-corners-x p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-brand"><Icon name="trophy" :size="18" /></span>
                <h2 class="sys-label">Achievements</h2>
            </div>
            <span class="sys-pill sys-pill-active tabular-nums">
                {{ unlockedCount }}/{{ totalCount }}
            </span>
        </div>

        <!-- Recently unlocked -->
        <ul v-if="recent.length" class="mt-4">
            <li
                v-for="(achievement, index) in recent"
                :key="achievement.name"
                class="flex items-start gap-3 py-2.5"
                :class="index ? 'sys-divider' : ''"
            >
                <span class="mt-0.5 shrink-0 text-brand">
                    <Icon name="checkCircle" :size="16" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[13.5px] text-content/90">{{ achievement.name }}</p>
                    <p class="mt-0.5 text-[11px] text-muted">{{ achievement.unlockedAt }}</p>
                </div>
                <span class="shrink-0 text-[11px] tabular-nums text-brand">
                    +{{ achievement.xpReward }}
                </span>
            </li>
        </ul>

        <p v-else class="mt-4 text-[13px] text-muted">
            Nothing unlocked yet — your first workout will change that.
        </p>

        <!-- Closest locked achievement -->
        <div v-if="next" class="sys-divider mt-4 pt-4">
            <p class="sys-label-sm">Next Up</p>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <p class="min-w-0 truncate text-[13.5px] text-content/85">{{ next.name }}</p>
                <p class="shrink-0 text-[12px] tabular-nums text-muted">
                    {{ next.current }} / {{ next.target }}
                </p>
            </div>
            <div class="sys-track mt-2">
                <div class="sys-fill" :style="{ width: nextProgress + '%' }" />
            </div>
        </div>
    </section>
</template>
