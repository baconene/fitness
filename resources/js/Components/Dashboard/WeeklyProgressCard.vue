<script setup>
import { computed, onMounted, ref } from 'vue';
import gsap from 'gsap';
import Icon from '@/Components/Icon.vue';
import { prefersReducedMotion } from '@/Composables/useReducedMotion';

const props = defineProps({
    completionPercent: { type: Number, default: 0 },
    workoutsCompleted: { type: Number, default: 0 },
    workoutsTarget: { type: Number, default: 0 },
    trainingTime: { type: String, default: '0m' },
    caloriesBurned: { type: Number, default: 0 },
    streakDays: { type: Number, default: 0 },
});

const RADIUS = 42;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

const dashOffset = computed(
    () => CIRCUMFERENCE - (Math.min(100, props.completionPercent) / 100) * CIRCUMFERENCE
);

const root = ref(null);
const ring = ref(null);
const shownPercent = ref(props.completionPercent);

onMounted(() => {
    if (prefersReducedMotion()) {
        return;
    }

    shownPercent.value = 0;

    const ctx = gsap.context(() => {
        gsap.fromTo(
            ring.value,
            { strokeDashoffset: CIRCUMFERENCE },
            {
                strokeDashoffset: dashOffset.value,
                duration: 1.3,
                ease: 'power2.out',
                delay: 0.2,
            }
        );

        // Count the label up in step with the ring sweep.
        const counter = { value: 0 };

        gsap.to(counter, {
            value: props.completionPercent,
            duration: 1.3,
            ease: 'power2.out',
            delay: 0.2,
            onUpdate: () => {
                shownPercent.value = counter.value;
            },
        });
    }, root.value);

    return () => ctx.revert();
});
</script>

<template>
    <section ref="root" class="sys-panel sys-corners p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-brand"><Icon name="chart" :size="18" /></span>
                <h2 class="sys-label">Progress</h2>
            </div>
            <button class="sys-pill flex items-center gap-1.5 transition hover:border-brand/50">
                This Week
                <Icon name="chevronDown" :size="12" />
            </button>
        </div>

        <div class="mt-5 grid grid-cols-[auto_minmax(0,1fr)] items-center gap-5">
            <!-- Completion ring -->
            <div class="relative h-[116px] w-[116px] shrink-0">
                <svg viewBox="0 0 100 100" class="h-full w-full -rotate-90">
                    <circle
                        cx="50"
                        cy="50"
                        :r="RADIUS"
                        fill="none"
                        stroke="rgba(255,255,255,0.07)"
                        stroke-width="7"
                    />
                    <circle
                        ref="ring"
                        cx="50"
                        cy="50"
                        :r="RADIUS"
                        fill="none"
                        stroke="url(#ringGradient)"
                        stroke-width="7"
                        stroke-linecap="round"
                        :stroke-dasharray="CIRCUMFERENCE"
                        :stroke-dashoffset="dashOffset"
                        style="filter: drop-shadow(0 0 5px rgba(54, 163, 255, 0.6))"
                    />
                    <defs>
                        <linearGradient id="ringGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#7657FF" />
                            <stop offset="100%" stop-color="#36A3FF" />
                        </linearGradient>
                    </defs>
                </svg>

                <div class="absolute inset-0 grid place-items-center">
                    <p class="text-[24px] font-semibold tabular-nums text-content">
                        {{ Math.round(shownPercent) }}%
                    </p>
                </div>
            </div>

            <!-- Supporting stats -->
            <dl class="space-y-3.5">
                <div class="flex items-start gap-2.5">
                    <span class="mt-0.5 shrink-0 text-muted"><Icon name="clock" :size="15" /></span>
                    <div class="min-w-0">
                        <dt class="text-[11px] text-muted">Total Training Time</dt>
                        <dd class="text-[15px] font-semibold text-content">{{ trainingTime }}</dd>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <span class="mt-0.5 shrink-0 text-orange-400">
                        <Icon name="flame" :size="15" />
                    </span>
                    <div class="min-w-0">
                        <dt class="text-[11px] text-muted">Total Calories Burned</dt>
                        <dd class="text-[15px] font-semibold text-content">
                            {{ caloriesBurned.toLocaleString() }} kcal
                        </dd>
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <span class="mt-0.5 shrink-0 text-orange-400">
                        <Icon name="flame" :size="15" />
                    </span>
                    <div class="min-w-0">
                        <dt class="text-[11px] text-muted">Current Streak</dt>
                        <dd class="text-[15px] font-semibold text-content">
                            {{ streakDays }} days
                        </dd>
                    </div>
                </div>
            </dl>
        </div>

        <p class="sys-divider mt-5 pt-4 text-center text-[12px] text-muted">
            Weekly Goal ·
            <span class="text-content/85">
                {{ workoutsCompleted }} / {{ workoutsTarget }} Workouts
            </span>
        </p>
    </section>
</template>
