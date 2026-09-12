<script setup>
import { computed } from 'vue';

const props = defineProps({ state: { type: String, default: 'READY' }, message: { type: String, default: '' } });
const messages = {
    READY: 'A new mission has been assigned.',
    ACTIVE: 'Mission in progress. Your effort is being recorded.',
    PAUSED: 'Mission paused. Continue when you are ready.',
    COMPLETED: 'Mission complete. Your rewards have been recorded.',
    MISSED: 'Mission window passed. Your next objective is still ahead.',
    'REST DAY': 'No combat mission assigned. Recovery is today’s objective.',
    RECOVERY: 'Recovery protocol active. Make space to restore your energy.',
};
const text = computed(() => props.message || messages[props.state] || messages.READY);
</script>

<template>
    <div class="border-l-2 border-brand/50 pl-4" role="status">
        <p class="mission-label text-brand">System</p>
        <p class="mt-2 max-w-md text-xs uppercase leading-6 tracking-wider text-content/80">{{ text }}</p>
    </div>
</template>
