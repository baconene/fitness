<script setup>
import { onUnmounted, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import HunterSidebar from '@/Components/Dashboard/HunterSidebar.vue';
import HunterBottomNav from '@/Components/Dashboard/HunterBottomNav.vue';
import Icon from '@/Components/Icon.vue';
defineProps({ title: { type: String, default: 'System home' }, subtitle: { type: String, default: 'Every effort moves your story forward.' } });
const page = usePage();
const drawer = ref(null);
const closeDrawer = () => drawer.value?.close();
onUnmounted(closeDrawer);
</script>
<template>
    <div class="hunter-shell min-h-svh bg-canvas-deep text-content">
        <Head :title="title" />
        <a href="#system-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:bg-canvas focus:p-4">Skip to content</a>
        <HunterSidebar class="hidden lg:flex" />
        <dialog ref="drawer" class="hunter-drawer m-0 h-svh max-h-none w-[min(320px,90vw)] max-w-none bg-canvas p-0 text-content backdrop:bg-black/70" @click="(event) => { if (event.target === drawer) closeDrawer(); }"><HunterSidebar mobile @close="closeDrawer" /></dialog>
        <HunterBottomNav />
        <div class="min-w-0 lg:pl-[248px]">
            <header class="sticky top-0 z-20 flex min-h-20 items-center justify-between gap-4 border-b border-edge/15 bg-canvas-deep/90 px-4 backdrop-blur-xl sm:px-7">
                <div class="flex min-w-0 items-center gap-3"><button type="button" aria-label="Open navigation" class="grid h-11 w-11 shrink-0 place-items-center rounded-md border border-edge/20 text-brand lg:hidden" @click="drawer.showModal()"><Icon name="menu" :size="21" /></button><div class="min-w-0"><p class="text-[9px] tracking-[.2em] text-brand">SYSTEM ONLINE</p><h1 class="mt-1 truncate text-lg font-semibold sm:text-xl">{{ title }}</h1></div></div>
                <div class="flex shrink-0 items-center gap-2"><Link :href="route('activity.index')" aria-label="View system activity" class="grid h-11 w-11 place-items-center rounded-full border border-edge/20 text-muted hover:text-brand"><Icon name="bell" :size="18" /></Link><Link :href="route('hunter.show')" aria-label="Your hunter profile" class="grid h-10 w-10 place-items-center rounded-full border border-brand/30 bg-brand/10 text-sm font-bold text-brand">{{ page.props.auth?.user?.name?.charAt(0)?.toUpperCase() || 'H' }}</Link></div>
            </header>
            <main id="system-content" class="mx-auto max-w-[1600px] px-4 pb-28 pt-6 sm:px-7 lg:pb-10">
                <p v-if="subtitle" class="mb-6 text-sm leading-relaxed text-muted">{{ subtitle }}</p>
                <div v-if="page.props.flash?.success" role="status" class="mb-5 rounded-md border border-brand/25 bg-brand/10 p-4 text-sm text-content">{{ page.props.flash.success }}</div>
                <div v-if="page.props.flash?.error" role="alert" class="mb-5 rounded-md border border-danger/25 bg-danger/10 p-4 text-sm text-danger">{{ page.props.flash.error }}</div>
                <slot />
            </main>
        </div>
    </div>
</template>
<style scoped>
.hunter-shell { background-image: radial-gradient(ellipse at top right, rgb(var(--color-violet) / .065), transparent 60%); }
.hunter-drawer[open] { display: flex; }
.hunter-drawer::backdrop { backdrop-filter: blur(5px); }
</style>
