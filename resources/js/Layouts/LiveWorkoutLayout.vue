<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

defineProps({ title: { type: String, default: 'Live workout' } });

const page = usePage();
</script>

<template>
    <div class="flex min-h-svh flex-col bg-canvas-deep text-content">
        <Head :title="title" />

        <header
            class="sticky top-0 z-20 flex min-h-16 items-center justify-between gap-3 border-b border-edge/15 bg-canvas-deep/90 px-4 backdrop-blur-xl"
        >
            <Link
                :href="route('workouts.index')"
                aria-label="Leave the live workout"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-md border border-edge/20 text-muted hover:text-brand"
            >
                <Icon name="chevronDown" :size="18" class="rotate-90" />
            </Link>

            <div class="min-w-0 text-center">
                <p class="text-[9px] tracking-[.2em] text-brand">HUNTER SYSTEM</p>
                <h1 class="mt-0.5 truncate text-[15px] font-semibold">{{ title }}</h1>
            </div>

            <span class="h-11 w-11 shrink-0" aria-hidden="true" />
        </header>

        <main class="mx-auto w-full max-w-[1280px] flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div
                v-if="page.props.flash?.success"
                role="status"
                class="mb-5 rounded-md border border-brand/25 bg-brand/10 p-4 text-sm text-content"
            >
                {{ page.props.flash.success }}
            </div>

            <slot />
        </main>
    </div>
</template>
