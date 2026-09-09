<script setup>
import { computed, onMounted, ref } from 'vue';
import gsap from 'gsap';
import Icon from '@/Components/Icon.vue';
import MuscleFigure from '@/Components/Dashboard/MuscleFigure.vue';
import { prefersReducedMotion } from '@/Composables/useReducedMotion';

const props = defineProps({
    focusArea: { type: String, default: 'Chest' },
    primary: { type: Array, default: () => [] },
    secondary: { type: Array, default: () => [] },
});

const LABELS = {
    chest: 'Chest',
    shoulders: 'Shoulders',
    biceps: 'Biceps',
    triceps: 'Triceps',
    forearms: 'Forearms',
    abs: 'Core',
    obliques: 'Obliques',
    upper_back: 'Upper Back',
    lats: 'Lats',
    lower_back: 'Lower Back',
    glutes: 'Glutes',
    quads: 'Quads',
    hamstrings: 'Hamstrings',
    calves: 'Calves',
};

const label = (key) => LABELS[key] ?? key;

const primaryLabels = computed(() => props.primary.map(label));
const secondaryLabels = computed(() => props.secondary.map(label));

const root = ref(null);

onMounted(() => {
    if (prefersReducedMotion()) {
        return;
    }

    const ctx = gsap.context(() => {
        // Slow breathing pulse on the lit muscle groups.
        gsap.to('[data-figure]', {
            opacity: 0.72,
            duration: 2.4,
            ease: 'sine.inOut',
            repeat: -1,
            yoyo: true,
            stagger: 0.3,
        });
    }, root.value);

    return () => ctx.revert();
});
</script>

<template>
    <section ref="root" class="sys-panel sys-corners sys-corners-x flex h-full flex-col p-5">
        <!-- Header -->
        <div class="flex items-center gap-2.5">
            <span class="text-brand"><Icon name="target" :size="18" /></span>
            <h2 class="sys-label">Today’s Target</h2>
        </div>

        <div class="mt-3 flex items-center gap-3">
            <span class="sys-label-sm flex items-center gap-1.5">
                <Icon name="crosshair" :size="12" />
                Focus Area
            </span>
            <span class="sys-pill sys-pill-active">{{ focusArea }}</span>
        </div>

        <!-- Figures -->
        <div class="mt-5 flex flex-1 items-center justify-center gap-3 sm:gap-5">
            <div data-figure class="h-[260px] w-[110px] shrink-0 sm:h-[290px] sm:w-[124px]">
                <MuscleFigure view="front" :primary="primary" :secondary="secondary" />
            </div>
            <div data-figure class="h-[260px] w-[110px] shrink-0 sm:h-[290px] sm:w-[124px]">
                <MuscleFigure view="back" :primary="primary" :secondary="secondary" />
            </div>
        </div>

        <!-- State legend -->
        <div class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5">
            <span class="flex items-center gap-1.5 text-[10.5px] text-muted">
                <span
                    class="h-2.5 w-2.5 shrink-0 rounded-full border-[1.5px]"
                    style="border-color: rgb(150, 222, 255); box-shadow: 0 0 5px rgba(70, 190, 255, 0.8)"
                />
                Primary Target
            </span>
            <span class="flex items-center gap-1.5 text-[10.5px] text-muted">
                <span
                    class="h-2.5 w-2.5 shrink-0 rounded-full border-[1.5px]"
                    style="border-color: rgb(196, 178, 255); box-shadow: 0 0 5px rgba(146, 118, 255, 0.6)"
                />
                Secondary Target
            </span>
            <span class="flex items-center gap-1.5 text-[10.5px] text-muted">
                <span class="h-2.5 w-2.5 shrink-0 rounded-full border-[1.5px] border-content/30" />
                Inactive
            </span>
        </div>

        <!-- Muscle lists -->
        <div class="mt-5 grid grid-cols-2 gap-4">
            <div>
                <p class="sys-label-sm">Primary Muscles</p>
                <ul class="mt-2.5 space-y-1.5">
                    <li
                        v-for="name in primaryLabels"
                        :key="name"
                        class="flex items-center gap-2 text-[13px] text-content/90"
                    >
                        <span
                            class="h-1.5 w-1.5 shrink-0 rounded-full bg-brand"
                            style="box-shadow: 0 0 6px rgba(54, 163, 255, 0.9)"
                        />
                        {{ name }}
                    </li>
                    <li v-if="!primaryLabels.length" class="text-[13px] text-muted">—</li>
                </ul>
            </div>

            <div>
                <p class="sys-label-sm">Secondary Muscles</p>
                <ul class="mt-2.5 space-y-1.5">
                    <li
                        v-for="name in secondaryLabels"
                        :key="name"
                        class="flex items-center gap-2 text-[13px] text-content/75"
                    >
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-violet-light/80" />
                        {{ name }}
                    </li>
                    <li v-if="!secondaryLabels.length" class="text-[13px] text-muted">—</li>
                </ul>
            </div>
        </div>

        <p
            class="sys-divider mt-5 pt-4 text-center text-[10.5px] italic text-muted/75"
            style="letter-spacing: 0.1em"
        >
            &ldquo;SCULPT A STRONGER TOMORROW.&rdquo;
        </p>
    </section>
</template>
