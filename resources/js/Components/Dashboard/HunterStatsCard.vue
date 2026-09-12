<script setup>
import { computed, onMounted, ref } from 'vue';
import gsap from 'gsap';
import Icon from '@/Components/Icon.vue';
import { rankClass } from '@/Support/rank';
import { prefersReducedMotion } from '@/Composables/useReducedMotion';

const props = defineProps({
    attributes: { type: Array, default: () => [] },
    pointsAvailable: { type: Number, default: 0 },
    nextRank: { type: [String, null], default: null },
    nextRankLevel: { type: [Number, null], default: null },
    rankProgress: { type: Number, default: 0 },
});

/**
 * Attributes start at 10 and have no hard ceiling; 40 gives the bar a
 * meaningful early-game scale without pinning at full immediately.
 */
const BAR_CEILING = 40;

const barWidth = (value) => Math.min(100, (value / BAR_CEILING) * 100);

/** Tints the promotion track with the rank being climbed towards. */
const nextRankClass = computed(() => rankClass(props.nextRank));

const root = ref(null);

onMounted(() => {
    if (prefersReducedMotion()) {
        return;
    }

    const ctx = gsap.context(() => {
        gsap.from('[data-stat-fill]', {
            scaleX: 0,
            transformOrigin: 'left center',
            duration: 0.8,
            ease: 'power2.out',
            stagger: 0.07,
            delay: 0.25,
        });
    }, root.value);

    return () => ctx.revert();
});
</script>

<template>
    <section ref="root" class="sys-panel sys-panel-hover sys-corners sys-corners-x p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-brand"><Icon name="diamond" :size="18" /></span>
                <h2 class="sys-label">Attributes</h2>
            </div>
            <span v-if="pointsAvailable > 0" class="sys-pill sys-pill-active">
                +{{ pointsAvailable }} to spend
            </span>
        </div>

        <dl class="mt-5 space-y-3.5">
            <div v-for="attribute in attributes" :key="attribute.key">
                <div class="flex items-baseline justify-between">
                    <dt class="flex items-baseline gap-2">
                        <span class="w-9 text-[11px] font-semibold tracking-widest text-muted">
                            {{ attribute.label }}
                        </span>
                        <span class="text-[13px] text-content/80">{{ attribute.name }}</span>
                    </dt>
                    <dd class="text-[15px] font-semibold tabular-nums text-content">
                        {{ attribute.value }}
                    </dd>
                </div>
                <div class="sys-seg mt-1.5">
                    <div
                        data-stat-fill
                        class="sys-seg-fill"
                        :style="{ width: barWidth(attribute.value) + '%' }"
                    />
                </div>
            </div>
        </dl>

        <!-- Next promotion -->
        <div v-if="nextRank" :class="nextRankClass" class="sys-divider mt-5 pt-4">
            <div class="flex items-center justify-between gap-3">
                <p class="sys-label-sm">Next Rank</p>
                <div class="flex items-baseline gap-2">
                    <span class="sys-rank-text sys-display text-[16px]">{{ nextRank }}</span>
                    <span class="text-[12px] text-muted">at level {{ nextRankLevel }}</span>
                </div>
            </div>
            <div class="sys-track mt-2">
                <div
                    class="h-full rounded-full transition-[width] duration-700"
                    :style="{
                        width: rankProgress + '%',
                        background:
                            'linear-gradient(90deg, rgb(var(--rank-rgb) / 0.55), rgb(var(--rank-rgb)))',
                        boxShadow: '0 0 10px rgb(var(--rank-rgb) / 0.65)',
                    }"
                />
            </div>
        </div>

        <p v-else class="sys-divider mt-5 pt-4 text-center text-[12px] text-muted">
            Maximum rank reached.
        </p>
    </section>
</template>
