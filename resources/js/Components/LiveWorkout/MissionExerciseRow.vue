<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({ entry: { type: Object, required: true }, index: Number, active: Boolean, disabled: Boolean });
defineEmits(['select']);
const sets = computed(() => props.entry.workout_sets || []);
const completed = computed(() => sets.value.filter((set) => set.is_completed));
const state = computed(() => {
    if (props.entry.is_pr || sets.value.some((set) => set.is_pr)) return 'PR achieved';
    if (sets.value.length && completed.value.length === sets.value.length) return 'Completed';
    if (props.entry.status === 'skipped') return 'Skipped';
    if (props.entry.status === 'failed_target') return 'Failed target';
    return props.active ? 'Active' : completed.value.length ? 'In progress' : 'Next';
});
const previous = computed(() => props.entry.previous_set);
const formatSet = (set) => set.duration_seconds != null
    ? `${set.duration_seconds} sec${set.distance_km ? ' · ' + set.distance_km + ' km' : ''}`
    : `${Number(set.weight_kg || 0)} kg × ${set.reps_completed ?? '—'}`;
</script>

<template>
    <li class="mission-exercise relative pl-12" data-exercise-row>
        <span class="absolute left-0 top-4 z-10 grid h-8 w-8 place-items-center border bg-[#080e1c] text-xs tabular-nums" :class="state === 'Completed' ? 'border-success/50 text-success' : active ? 'border-brand text-brand' : 'border-edge/25 text-muted'">
            <Icon v-if="state === 'Completed'" name="check" :size="15" /><template v-else>{{ String(index + 1).padStart(2, '0') }}</template>
        </span>
        <button type="button" class="w-full border-b border-edge/15 py-4 text-left disabled:cursor-default" :class="active ? 'text-content' : 'text-content/80'" :disabled="disabled" :aria-current="active ? 'step' : undefined" @click="$emit('select')">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h4 class="break-words text-sm font-semibold uppercase tracking-wide">{{ entry.exercise?.name || 'Exercise' }}</h4>
                <span class="text-[10px] uppercase tracking-widest" :class="state === 'Completed' ? 'text-success' : active ? 'text-brand' : 'text-muted'">{{ state }}</span>
            </div>
            <p class="mt-2 text-xs text-muted">{{ completed.length }} / {{ sets.length }} sets complete <span v-if="entry.target_reps">· {{ entry.target_reps }} reps</span></p>
            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs leading-5 text-muted">
                <p v-if="previous">Previous: <span class="tabular-nums text-content/75">{{ formatSet(previous) }}</span></p>
                <p v-if="entry.target_weight_kg != null">Target: <span class="tabular-nums text-brand">{{ entry.target_weight_kg }} kg <template v-if="entry.target_reps">× {{ entry.target_reps }}</template></span></p>
            </div>
            <ul v-if="completed.length" class="mt-3 flex flex-wrap gap-2" aria-label="Logged sets"><li v-for="set in completed" :key="set.id" class="border border-edge/15 px-2 py-1 text-xs tabular-nums text-content/70">{{ formatSet(set) }}<span v-if="set.is_pr" class="ml-2 text-violet-light">PR</span></li></ul>
        </button>
    </li>
</template>

<style scoped>
.mission-exercise:not(:last-child)::after { content: ''; position: absolute; left: 15px; top: 44px; bottom: -16px; width: 1px; background: rgb(var(--color-edge) / .2); }
</style>
