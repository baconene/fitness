<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

defineProps({
    workouts: { type: Array, default: () => [] },
    calendarHref: { type: String, default: '/calendar' },
});
</script>

<template>
    <section class="sys-panel sys-corners p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-brand"><Icon name="calendar" :size="18" /></span>
                <h2 class="sys-label">Upcoming</h2>
            </div>

            <Link
                :href="calendarHref"
                class="group flex items-center gap-1.5 text-[13px] text-brand transition hover:text-brand-hover"
            >
                View Calendar
                <span class="transition-transform group-hover:translate-x-0.5">
                    <Icon name="arrowRight" :size="14" />
                </span>
            </Link>
        </div>

        <ul class="mt-4">
            <li
                v-for="(workout, index) in workouts"
                :key="workout.date + workout.name"
                class="flex items-center gap-4 py-3"
                :class="index ? 'sys-divider' : ''"
            >
                <span class="w-[86px] shrink-0 text-[12.5px] text-muted">{{ workout.date }}</span>
                <span class="shrink-0 text-content/70">
                    <Icon :name="workout.icon ?? 'body'" :size="17" />
                </span>
                <span class="min-w-0 flex-1 truncate text-[14px] text-content/90">
                    {{ workout.name }}
                </span>
            </li>

            <li v-if="!workouts.length" class="py-3 text-[13px] text-muted">
                Nothing scheduled yet.
            </li>
        </ul>
    </section>
</template>
