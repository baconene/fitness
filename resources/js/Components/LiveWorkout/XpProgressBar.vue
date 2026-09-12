<script setup>
import { computed } from 'vue';

const props = defineProps({
    current: { type: Number, default: 0 },
    target: { type: Number, default: 0 },
    label: { type: String, default: 'Experience' },
});
const percent = computed(() => props.target > 0 ? Math.max(0, Math.min(100, props.current / props.target * 100)) : 0);
</script>

<template>
    <div role="progressbar" :aria-label="label" :aria-valuenow="Math.round(percent)" :aria-valuemin="0" :aria-valuemax="100" class="h-1 overflow-hidden bg-edge/10">
        <div class="mission-bar h-full origin-left bg-brand" :style="{ width: percent + '%' }" />
    </div>
</template>

<style scoped>
.mission-bar { transition: width .5s ease; box-shadow: 0 0 10px rgb(var(--color-brand) / .3); }
@media (prefers-reduced-motion: reduce) { .mission-bar { transition: none; } }
</style>
