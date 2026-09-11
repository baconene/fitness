<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
const items = [ ['Home', 'dashboard', 'dashboard'], ['Missions', 'scroll', 'missions.index'], ['Workout', 'dumbbell', 'workouts.index'], ['Calendar', 'calendar', 'calendar.show'], ['Hunter', 'body', 'hunter.show'] ];
const page = usePage();
const active = (name) => page.url.split('?')[0] === new URL(route(name), window.location.origin).pathname;
</script>
<template>
    <nav aria-label="Quick navigation" class="fixed inset-x-0 bottom-0 z-30 border-t border-edge/20 bg-canvas/95 backdrop-blur-xl lg:hidden" style="padding-bottom: env(safe-area-inset-bottom)">
        <ul class="grid grid-cols-5"><li v-for="[label, icon, name] in items" :key="name"><Link :href="route(name)" :aria-current="active(name) ? 'page' : undefined" class="flex min-h-16 flex-col items-center justify-center gap-1 text-[10px]" :class="active(name) ? 'text-brand' : 'text-muted'"><Icon :name="icon" :size="20" />{{ label }}</Link></li></ul>
    </nav>
</template>
