<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

defineProps({ mobile: Boolean });
defineEmits(['close']);
const page = usePage();
const groups = [
    { label: 'COMMAND', items: [
        ['System home', 'dashboard', 'dashboard'], ['Missions', 'scroll', 'missions.index'],
        ['Workouts', 'dumbbell', 'workouts.index'], ['Exercises', 'library', 'exercises.index'], ['Meal plan', 'meal', 'meals.index'], ['Calendar', 'calendar', 'calendar.show'], ['Programs', 'clipboard', 'programs.index'],
    ] },
    { label: 'YOUR HUNTER', items: [
        ['Hunter profile', 'body', 'hunter.show'], ['Health', 'heart', 'health.index'], ['Progress', 'chart', 'progress.index'],
        ['Dungeons', 'castle', 'dungeons.index'], ['Inventory', 'briefcase', 'inventory.index'], ['Market', 'store', 'market.index', 'Soon'], ['Achievements', 'trophy', 'achievements.index'],
    ] },
];
const currentPath = computed(() => page.url.split('?')[0]);
const active = (name) => currentPath.value === new URL(route(name), window.location.origin).pathname;
</script>

<template>
    <aside :class="mobile ? 'w-full' : 'fixed inset-y-0 left-0 z-30 w-[248px]'" class="flex h-full flex-col border-r border-edge/15 bg-canvas">
        <div class="flex items-center gap-3 border-b border-edge/10 px-5 py-6">
            <Link :href="route('dashboard')" class="flex min-h-11 items-center gap-3" @click="$emit('close')">
                <span class="grid h-10 w-10 place-items-center border border-brand/40 bg-brand/10 text-brand"><Icon name="diamond" :size="24" /></span>
                <span><strong class="sys-display block text-sm tracking-[.14em]">HUNTER SYSTEM</strong><span class="mt-1 block text-[9px] tracking-[.2em] text-muted">YOUR NEXT EVOLUTION</span></span>
            </Link>
            <button v-if="mobile" type="button" class="ml-auto grid h-11 w-11 shrink-0 place-items-center text-muted" aria-label="Close navigation" @click="$emit('close')"><Icon name="x" :size="20" /></button>
        </div>
        <nav aria-label="Main navigation" class="flex-1 overflow-y-auto px-3 py-5">
            <div v-for="group in groups" :key="group.label" class="mb-6">
                <p class="mb-2 px-3 text-[9px] font-semibold tracking-[.2em] text-muted">{{ group.label }}</p>
                <Link v-for="[label, icon, name, badge] in group.items" :key="name" :href="route(name)" :aria-current="active(name) ? 'page' : undefined" class="mb-1 flex min-h-11 items-center gap-3 rounded-md border-l-2 px-3 py-2 text-[13px] transition-colors" :class="active(name) ? 'border-brand bg-brand/10 text-brand' : 'border-transparent text-muted hover:bg-surface-raised hover:text-content'" @click="$emit('close')"><Icon :name="icon" :size="18" />{{ label }}<span v-if="badge" class="ml-auto rounded-full border border-brand/30 bg-brand/10 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[.14em] text-brand">{{ badge }}</span></Link>
            </div>
        </nav>
        <div class="border-t border-edge/15 p-4 text-xs">
            <Link :href="route('activity.index')" class="flex min-h-11 items-center gap-3 px-2 text-muted hover:text-brand" @click="$emit('close')"><Icon name="bell" :size="17" /> System log</Link>
            <Link :href="route('profile.edit')" class="flex min-h-11 items-center gap-3 px-2 text-muted hover:text-brand" @click="$emit('close')"><Icon name="settings" :size="17" /> Account settings</Link>
            <Link :href="route('logout')" method="post" as="button" class="flex min-h-11 w-full items-center gap-3 px-2 text-muted hover:text-danger"><Icon name="arrowRight" :size="17" /> Sign out</Link>
        </div>
    </aside>
</template>
