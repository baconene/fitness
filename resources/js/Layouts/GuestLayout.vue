<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-slate-950 px-4 py-10 sm:px-6"
    >
        <div
            class="pointer-events-none absolute -top-24 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-orange-600/30 blur-3xl"
        ></div>
        <div
            class="pointer-events-none absolute bottom-0 right-0 h-64 w-64 translate-x-1/3 translate-y-1/3 rounded-full bg-orange-500/20 blur-3xl"
        ></div>

        <div class="relative flex w-full flex-col items-center">
            <Link href="/" class="flex flex-col items-center gap-2">
                <span
                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-600 shadow-lg shadow-orange-600/30"
                >
                    <ApplicationLogo class="h-8 w-8 fill-current text-white" />
                </span>
                <span
                    class="text-lg font-extrabold uppercase tracking-widest text-white"
                    >FitTrack</span
                >
            </Link>

            <div
                class="mt-8 w-full max-w-sm overflow-hidden rounded-2xl bg-white px-6 py-8 shadow-xl sm:max-w-md sm:px-8"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
