<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

defineProps({
    /** Controls the off-canvas drawer on tablet/mobile. */
    open: { type: Boolean, default: false },
});

defineEmits(['close']);

/**
 * `href` is a plain path so the sidebar never throws on a route name that
 * has not been registered yet; `enabled` dims the not-yet-built sections.
 */
const menuItems = [
    { name: 'Dashboard', icon: 'dashboard', href: '/dashboard', enabled: true },
    { name: 'Missions', icon: 'scroll', href: '/quests', enabled: false },
    { name: 'Calendar', icon: 'calendar', href: '/calendar', enabled: true },
    { name: 'Workouts', icon: 'dumbbell', href: '/workouts', enabled: true },
    { name: 'Programs', icon: 'clipboard', href: '/programs', enabled: false },
    { name: 'Dungeons', icon: 'castle', href: '/dungeons', enabled: false },
    { name: 'Progress', icon: 'chart', href: '/progress', enabled: false },
    { name: 'Inventory', icon: 'briefcase', href: '/inventory', enabled: false },
    { name: 'Achievements', icon: 'trophy', href: '/achievements', enabled: false },
    { name: 'Community', icon: 'users', href: '/community', enabled: false },
    { name: 'Settings', icon: 'settings', href: '/profile', enabled: true },
];

const page = usePage();

const isActive = (href) => {
    const current = page.url.split('?')[0];

    return current === href || current.startsWith(`${href}/`);
};

const items = computed(() =>
    menuItems.map((item) => ({ ...item, active: isActive(item.href) }))
);
</script>

<template>
    <!-- Scrim for the mobile drawer -->
    <div
        v-if="open"
        class="fixed inset-0 z-40 bg-canvas-deep/70 backdrop-blur-sm lg:hidden"
        @click="$emit('close')"
    />

    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-[248px] flex-col border-r border-edge/20 bg-canvas transition-transform duration-300 lg:translate-x-0"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Ambient wash -->
        <div
            class="pointer-events-none absolute inset-0 opacity-70"
            style="
                background:
                    radial-gradient(
                        120% 40% at 0% 0%,
                        rgba(118, 87, 255, 0.16) 0%,
                        transparent 60%
                    ),
                    linear-gradient(180deg, rgba(11, 18, 32, 0.9) 0%, rgba(3, 6, 14, 1) 100%);
            "
        />

        <div class="relative flex h-full flex-col">
            <!-- Brand -->
            <div class="flex items-start gap-3 px-5 py-6">
                <span class="mt-0.5 text-brand">
                    <Icon name="diamond" :size="22" :stroke-width="1.25" />
                </span>
                <div>
                    <h1
                        class="sys-display text-[15px] leading-none text-content"
                        style="letter-spacing: 0.14em"
                    >
                        HUNTER SYSTEM
                    </h1>
                    <p
                        class="mt-2 text-[9px] font-medium leading-relaxed text-muted"
                        style="letter-spacing: 0.18em"
                    >
                        DISCIPLINE CREATES<br />A STRONGER YOU
                    </p>
                </div>
                <button
                    class="ml-auto text-muted transition hover:text-content lg:hidden"
                    aria-label="Close navigation"
                    @click="$emit('close')"
                >
                    <Icon name="x" :size="18" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-2">
                <component
                    :is="item.enabled ? Link : 'span'"
                    v-for="item in items"
                    :key="item.name"
                    :href="item.enabled ? item.href : undefined"
                    class="group relative flex items-center gap-3 rounded-md px-4 py-2.5 text-[13.5px] transition-colors"
                    :class="[
                        item.active
                            ? 'text-content'
                            : item.enabled
                              ? 'text-muted hover:bg-white/[0.03] hover:text-content'
                              : 'cursor-default text-muted/45',
                    ]"
                    :style="
                        item.active
                            ? 'background: linear-gradient(90deg, rgba(118,87,255,0.24) 0%, rgba(54,163,255,0.08) 55%, transparent 100%);'
                            : ''
                    "
                >
                    <!-- Illuminated left rail on the active item -->
                    <span
                        v-if="item.active"
                        class="absolute inset-y-1 left-0 w-[2px] rounded-full bg-brand"
                        style="box-shadow: 0 0 10px 1px rgba(54, 163, 255, 0.9)"
                    />
                    <span :class="item.active ? 'text-brand' : ''">
                        <Icon :name="item.icon" :size="18" />
                    </span>
                    <span :class="item.active ? 'font-medium' : ''">{{ item.name }}</span>
                </component>
            </nav>

            <!-- Hunter silhouette + sigil -->
            <div class="relative mt-auto">
                <div
                    class="h-52 bg-cover bg-center opacity-[0.55]"
                    style="
                        background-image: linear-gradient(
                                180deg,
                                rgb(3 6 14 / 0.95) 0%,
                                rgb(3 6 14 / 0.25) 35%,
                                rgb(3 6 14 / 0.98) 100%
                            ),
                            url('https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=500&q=70');
                    "
                />
                <div class="absolute inset-x-0 bottom-0 pb-6 text-center">
                    <p
                        class="sys-display text-[13px] text-content/90"
                        style="letter-spacing: 0.42em; text-indent: 0.42em"
                    >
                        ARISE
                    </p>
                    <p
                        class="mt-3 text-[9px] leading-relaxed text-muted/80"
                        style="letter-spacing: 0.2em"
                    >
                        A STRONGER YOU<br />TOMORROW
                    </p>
                </div>
            </div>
        </div>
    </aside>
</template>
