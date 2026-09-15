<script setup>
import { Link } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';
</script>

<template>
    <HunterLayout title="Market" subtitle="">
        <section class="sys-panel sys-corners sys-corners-x mx-auto flex max-w-xl flex-col items-center p-10 text-center">
            <span class="sys-badge text-brand"><Icon name="store" :size="28" /></span>
            <p class="sys-label-sm mt-5 text-brand">Coming soon</p>
            <h2 class="sys-display mt-2 text-[22px] text-content">The Market is not open yet</h2>
            <p class="mt-3 max-w-md text-sm leading-relaxed text-muted">
                A place to spend what you earn on your hunter journey is on its way. Keep training — everything you
                earn now will still be yours when the doors open.
            </p>
            <Link :href="route('inventory.index')" class="sys-pill mt-6 min-h-11 px-5 hover:border-brand/50">
                View your inventory <Icon name="arrowRight" :size="12" />
            </Link>
        </section>
    </HunterLayout>
</template>
