<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

defineProps({
    name: { type: String, default: 'Rest Day' },
    focus: { type: String, default: 'RECOVERY' },
    durationMinutes: { type: Number, default: 0 },
    exercises: { type: Array, default: () => [] },
    startHref: { type: String, default: '/workouts' },
    detailsHref: { type: String, default: '/workouts' },
});

/** Renders "4 × 8" or "3 × 60s" depending on the unit. */
const prescription = (exercise) =>
    `${exercise.sets} × ${exercise.reps}${exercise.unit ?? ''}`;
</script>

<template>
    <section class="sys-panel sys-corners sys-corners-x flex h-full flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-edge/15 px-5 py-4">
            <div class="flex items-center gap-2.5">
                <span class="text-brand"><Icon name="sparkle" :size="18" /></span>
                <h2 class="sys-label">Today’s Mission</h2>
            </div>

            <Link
                :href="detailsHref"
                class="group flex items-center gap-1.5 text-[13px] text-brand transition hover:text-brand-hover"
            >
                View Details
                <span class="transition-transform group-hover:translate-x-0.5">
                    <Icon name="arrowRight" :size="14" />
                </span>
            </Link>
        </div>

        <div class="grid flex-1 gap-6 p-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <!-- Prescription -->
            <div>
                <div class="flex items-start gap-4">
                    <span class="sys-badge text-violet-light">
                        <Icon name="dumbbell" :size="22" />
                    </span>
                    <div>
                        <h3 class="sys-display text-[26px] leading-none text-content">
                            {{ name }}
                        </h3>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="sys-pill">{{ focus }}</span>
                            <span v-if="durationMinutes" class="sys-pill">
                                {{ durationMinutes }} min
                            </span>
                        </div>
                    </div>
                </div>

                <ul class="mt-6 space-y-3">
                    <li
                        v-for="exercise in exercises"
                        :key="exercise.name"
                        class="flex items-center gap-3"
                    >
                        <span
                            :class="exercise.completed ? 'text-brand' : 'text-muted/55'"
                            class="shrink-0"
                        >
                            <Icon
                                :name="exercise.completed ? 'checkCircle' : 'circle'"
                                :size="17"
                            />
                        </span>
                        <span class="min-w-0 flex-1 truncate text-[14px] text-content/90">
                            {{ exercise.name }}
                        </span>
                        <span class="shrink-0 text-[13px] tabular-nums text-muted">
                            {{ prescription(exercise) }}
                        </span>
                    </li>

                    <li v-if="!exercises.length" class="text-[13px] text-muted">
                        No exercises scheduled — enjoy the recovery.
                    </li>
                </ul>
            </div>

            <!-- Cinematic panel -->
            <div class="relative min-h-[220px] overflow-hidden rounded-md border border-edge/20">
                <div
                    class="absolute inset-0 bg-cover bg-center"
                    style="
                        background-image: url('https://images.unsplash.com/photo-1509023464722-18d996393ca8?auto=format&fit=crop&w=900&q=75');
                    "
                />
                <div
                    class="absolute inset-0"
                    style="
                        background:
                            radial-gradient(
                                60% 55% at 50% 42%,
                                rgba(140, 110, 255, 0.42) 0%,
                                transparent 72%
                            ),
                            linear-gradient(
                                180deg,
                                rgb(5 9 20 / 0.55) 0%,
                                rgb(5 9 20 / 0.5) 45%,
                                rgb(3 6 14 / 0.94) 100%
                            );
                    "
                />
                <div class="absolute inset-x-0 bottom-0 p-5 text-center">
                    <p
                        class="sys-display text-[13px] text-content/95"
                        style="letter-spacing: 0.34em; text-indent: 0.34em"
                    >
                        ANOTHER REP
                    </p>
                    <p
                        class="sys-display mt-1.5 text-[13px] text-content/70"
                        style="letter-spacing: 0.34em; text-indent: 0.34em"
                    >
                        A STRONGER YOU
                    </p>
                </div>
            </div>
        </div>

        <!-- Primary action -->
        <div class="px-5 pb-5">
            <Link :href="startHref" class="sys-cta group mx-auto max-w-md">
                Start Workout
                <span class="transition-transform group-hover:translate-x-1">
                    <Icon name="arrowRight" :size="16" :stroke-width="2" />
                </span>
            </Link>
        </div>
    </section>
</template>
