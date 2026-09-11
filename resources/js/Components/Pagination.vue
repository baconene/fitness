<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    /** Laravel paginator `links` array. */
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <nav v-if="links.length > 3" aria-label="Pagination" class="mt-6 flex flex-wrap justify-center gap-1">
        <component
            :is="link.url ? Link : 'span'"
            v-for="link in links"
            :key="link.label"
            :href="link.url || undefined"
            preserve-scroll
            class="grid min-h-9 min-w-9 place-items-center rounded-md border px-3 text-[12px] transition-colors"
            :class="[
                link.active
                    ? 'border-brand/60 bg-brand/15 text-brand'
                    : link.url
                      ? 'border-edge/20 text-muted hover:border-brand/40 hover:text-content'
                      : 'border-edge/10 text-muted/40',
            ]"
            v-html="link.label"
        />
    </nav>
</template>
