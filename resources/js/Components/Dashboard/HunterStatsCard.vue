<script setup>
import { onMounted, ref } from 'vue';
import gsap from 'gsap';
import Icon from '@/Components/Icon.vue';
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
    <section ref="root" class="sys-panel sys-corners p-5">
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
                <div class="sys-track mt-1.5">
                    <div
                        data-stat-fill
                        class="sys-fill"
                        :style="{ width: barWidth(attribute.value) + '%' }"
                    />
                </div>
            </div>
        </dl>

        <!-- Next promotion -->
        <div v-if="nextRank" class="sys-divider mt-5 pt-4">
            <div class="flex items-baseline justify-between">
                <p class="sys-label-sm">Next Rank</p>
                <p class="text-[12px] text-muted">
                    <span class="sys-display text-[14px] text-brand">{{ nextRank }}</span>
                    <span class="ml-1.5">at level {{ nextRankLevel }}</span>
                </p>
            </div>
            <div class="sys-track mt-2">
                <div
                    class="h-full rounded-full"
                    :style="{
                        width: rankProgress + '%',
                        background: 'linear-gradient(90deg, rgb(118,87,255), rgb(161,140,255))',
                        boxShadow: '0 0 10px rgba(118,87,255,0.6)',
                    }"
                />
            </div>
        </div>

        <p v-else class="sys-divider mt-5 pt-4 text-center text-[12px] text-muted">
            Maximum rank reached.
        </p>
    </section>
</template>
