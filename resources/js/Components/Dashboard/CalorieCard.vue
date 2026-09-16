<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    calories: { type: Number, default: 0 },
    protein: { type: Number, default: 0 },
    carbs: { type: Number, default: 0 },
    fat: { type: Number, default: 0 },
    carbPercent: { type: Number, default: 0 },
    proteinPercent: { type: Number, default: 0 },
    fatPercent: { type: Number, default: 0 },
    /** How the figure was derived, so the card does not imply false precision. */
    basis: { type: String, default: '' },
    isEstimated: { type: Boolean, default: false },
});

const macros = computed(() => [
    { key: 'carbs', label: 'Carbs', grams: props.carbs, percent: props.carbPercent, tint: 'bg-brand' },
    { key: 'protein', label: 'Protein', grams: props.protein, percent: props.proteinPercent, tint: 'bg-violet-light' },
    { key: 'fat', label: 'Fat', grams: props.fat, percent: props.fatPercent, tint: 'bg-orange-400' },
]);
</script>

<template>
    <article class="sys-panel sys-panel-hover sys-corners sys-corners-x p-5">
        <div class="flex items-start gap-4">
            <span class="sys-badge text-orange-400">
                <Icon name="flame" :size="22" />
            </span>

            <div class="min-w-0">
                <p class="sys-label-sm">Daily Target</p>
                <p class="mt-1 text-[32px] font-semibold leading-none tabular-nums text-content">
                    {{ calories.toLocaleString() }}
                </p>
                <p class="mt-1.5 text-[13px] text-muted">kcal / day</p>
            </div>
        </div>

        <!-- Macro split, in grams rather than percentages alone. -->
        <div class="mt-4 grid grid-cols-3 gap-2">
            <div v-for="macro in macros" :key="macro.key">
                <div class="sys-track">
                    <div class="h-full rounded-full" :class="macro.tint" :style="{ width: macro.percent + '%' }" />
                </div>
                <p class="mt-1.5 text-[10px] text-muted">
                    <span class="tabular-nums text-content/80">{{ macro.grams }}g</span>
                    {{ macro.label }}
                </p>
            </div>
        </div>

        <p v-if="basis" class="mt-3 text-[10px] leading-relaxed text-muted/80">
            {{ basis }}.<template v-if="isEstimated"> Log body fat for a closer estimate.</template>
        </p>
    </article>
</template>
