<script setup>
import { computed, onMounted, ref } from 'vue';
import gsap from 'gsap';
import Icon from '@/Components/Icon.vue';
import { prefersReducedMotion } from '@/Composables/useReducedMotion';

const props = defineProps({
    quests: { type: Array, default: () => [] },
});

const isDone = (quest) => quest.current >= quest.target;

const completedCount = computed(() => props.quests.filter(isDone).length);

const percent = (quest) => {
    if (!quest.target) {
        return 0;
    }

    return Math.min(100, Math.round((quest.current / quest.target) * 100));
};

/** "2.5 / 3L" — keeps the unit attached to the target only, as in the design. */
const readout = (quest) =>
    `${quest.current.toLocaleString()} / ${quest.target.toLocaleString()}${quest.unit ?? ''}`;

const root = ref(null);

onMounted(() => {
    if (prefersReducedMotion()) {
        return;
    }

    const ctx = gsap.context(() => {
        gsap.from('[data-quest-fill]', {
            scaleX: 0,
            transformOrigin: 'left center',
            duration: 0.9,
            ease: 'power2.out',
            stagger: 0.08,
            delay: 0.2,
        });
    }, root.value);

    return () => ctx.revert();
});
</script>

<template>
    <section ref="root" class="sys-panel sys-corners p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-brand"><Icon name="target" :size="18" /></span>
                <h2 class="sys-label">Daily Quests</h2>
            </div>
            <span class="sys-pill sys-pill-active tabular-nums">
                {{ completedCount }}/{{ quests.length }}
            </span>
        </div>

        <ul class="mt-5 space-y-4">
            <li v-for="quest in quests" :key="quest.name">
                <div class="flex items-center gap-2.5">
                    <span :class="isDone(quest) ? 'text-brand' : 'text-muted/50'" class="shrink-0">
                        <Icon :name="isDone(quest) ? 'checkCircle' : 'circle'" :size="16" />
                    </span>
                    <span
                        class="min-w-0 flex-1 truncate text-[13.5px]"
                        :class="isDone(quest) ? 'text-content/90' : 'text-content/75'"
                    >
                        {{ quest.name }}
                    </span>
                    <span class="shrink-0 text-[12px] tabular-nums text-muted">
                        {{ readout(quest) }}
                    </span>
                </div>

                <div class="sys-track mt-2 ml-[26px]">
                    <div data-quest-fill class="sys-fill" :style="{ width: percent(quest) + '%' }" />
                </div>
            </li>

            <li v-if="!quests.length" class="text-[13px] text-muted">
                No quests assigned today.
            </li>
        </ul>
    </section>
</template>
