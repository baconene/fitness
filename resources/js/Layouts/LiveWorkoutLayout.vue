<template>
    <div class="live-workout-layout min-h-screen bg-surface flex flex-col">
        <header class="bg-edge border-b border-edge px-4 py-3">
            <div class="flex items-center justify-between">
                <Link :href="route('workouts.index')" class="text-edge hover:text-content">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h1 class="text-content font-bold">Live Workout</h1>
                <div class="w-6" />
            </div>
        </header>

        <main class="flex-1 flex flex-col px-4 py-6">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>
.live-workout-layout {
    font-family: system-ui, -apple-system, sans-serif;
}
</style>
