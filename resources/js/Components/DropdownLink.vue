<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-3 text-start text-sm leading-5 text-content transition duration-150 ease-in-out hover:bg-brand/10 hover:text-brand focus:bg-brand/10 focus:text-brand focus:outline-none"
    >
        <slot />
    </Link>
</template>
