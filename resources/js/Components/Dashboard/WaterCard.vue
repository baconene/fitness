<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    consumedLitres: { type: Number, default: 0 },
    targetLitres: { type: Number, default: 3 },
});

const percent = computed(() => {
    if (!props.targetLitres) {
        return 0;
    }

    return Math.min(100, Math.round((props.consumedLitres / props.targetLitres) * 100));
});
</script>

<template>
    <article class="sys-panel sys-panel-hover sys-corners p-5">
        <div class="flex items-start gap-4">
            <span class="sys-badge text-brand">
                <Icon name="droplet" :size="22" />
            </span>

            <div class="min-w-0">
                <p class="sys-label-sm">Water Intake</p>
                <p class="mt-1 text-[32px] font-semibold leading-none text-content">
                    {{ consumedLitres }} L
                </p>
                <p class="mt-1.5 text-[13px] text-muted">/ {{ targetLitres.toFixed(1) }} L</p>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <div class="sys-track flex-1">
                <div class="sys-fill" :style="{ width: percent + '%' }" />
            </div>
            <span class="text-[11px] font-medium text-content/80">{{ percent }}%</span>
        </div>
    </article>
</template>
