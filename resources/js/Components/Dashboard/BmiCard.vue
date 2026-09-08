<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    bmi: { type: [Number, null], default: null },
    category: { type: String, default: 'Normal' },
    healthyRange: { type: String, default: '18.5 - 24.9' },
});

/** Category colouring is presentational only — no medical advice is inferred. */
const categoryClass = computed(
    () =>
        ({
            Underweight: 'text-brand',
            Normal: 'text-success',
            Overweight: 'text-amber-400',
            'Obesity Class I': 'text-danger',
            'Obesity Class II': 'text-danger',
            Obese: 'text-danger',
        })[props.category] ?? 'text-muted'
);
</script>

<template>
    <article class="sys-panel sys-panel-hover sys-corners p-5">
        <div class="flex items-start gap-4">
            <span class="sys-badge text-content/80">
                <Icon name="scale" :size="22" />
            </span>

            <div class="min-w-0">
                <p class="sys-label-sm">BMI</p>
                <p class="mt-1 text-[32px] font-semibold leading-none text-content">
                    {{ bmi ?? '—' }}
                </p>
                <p :class="['mt-1.5 text-[13px] font-medium', categoryClass]">
                    {{ bmi === null ? 'No data' : category }}
                </p>
            </div>
        </div>

        <p class="mt-4 text-center text-[11px] text-muted/80">
            <span class="mr-1.5 text-muted/50">&#9702;</span>{{ healthyRange
            }}<span class="ml-1.5 text-muted/50">&#9702;</span>
        </p>
    </article>
</template>
