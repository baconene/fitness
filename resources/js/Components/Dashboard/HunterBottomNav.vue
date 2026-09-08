<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

const items = [
    { name: 'Home', icon: 'dashboard', href: '/dashboard' },
    { name: 'Quest', icon: 'scroll', href: '/quests' },
    { name: 'Workout', icon: 'dumbbell', href: '/workouts' },
    { name: 'Calendar', icon: 'calendar', href: '/calendar' },
    { name: 'Hunter', icon: 'users', href: '/profile' },
];

const page = usePage();

const isActive = (href) => page.url.split('?')[0].startsWith(href);
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-40 border-t border-edge/20 bg-canvas-deep/95 backdrop-blur-md lg:hidden"
        style="padding-bottom: env(safe-area-inset-bottom)"
    >
        <ul class="grid grid-cols-5">
            <li v-for="item in items" :key="item.name">
                <Link
                    :href="item.href"
                    class="flex flex-col items-center gap-1 py-2.5 text-[10px] transition-colors"
                    :class="isActive(item.href) ? 'text-brand' : 'text-muted'"
                >
                    <Icon :name="item.icon" :size="19" />
                    {{ item.name }}
                </Link>
            </li>
        </ul>
    </nav>
</template>
