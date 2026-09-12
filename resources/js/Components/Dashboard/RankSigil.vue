<script setup>
import { computed } from 'vue';
import { rankClass, rankLetter } from '@/Support/rank';

const props = defineProps({
    rank: { type: String, default: 'E' },
    /** Renders the letter larger for hero placements. */
    large: { type: Boolean, default: false },
});

const letter = computed(() => rankLetter(props.rank));
</script>

<template>
    <span
        :class="[rankClass(rank), large ? 'h-[4.25rem] w-[4.25rem]' : '']"
        class="sys-sigil"
        role="img"
        :aria-label="`${letter} rank`"
    >
        <span
            class="sys-rank-text sys-display leading-none"
            :class="large ? 'text-[30px]' : 'text-[22px]'"
        >
            {{ letter }}
        </span>
    </span>
</template>
