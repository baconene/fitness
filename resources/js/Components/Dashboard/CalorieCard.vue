<script setup>
import Icon from '@/Components/Icon.vue';

defineProps({
    calorieTarget: { type: Number, default: 0 },
    carbPercent: { type: Number, default: 55 },
    proteinPercent: { type: Number, default: 25 },
    fatPercent: { type: Number, default: 20 },
});
</script>

<template>
    <article class="sys-panel sys-panel-hover sys-corners p-5">
        <div class="flex items-start gap-4">
            <span class="sys-badge text-orange-400">
                <Icon name="flame" :size="22" />
            </span>

            <div class="min-w-0">
                <p class="sys-label-sm">Calorie Intake</p>
                <p class="mt-1 text-[32px] font-semibold leading-none text-content">
                    {{ calorieTarget.toLocaleString() }}
                </p>
                <p class="mt-1.5 text-[13px] text-muted">kcal / day</p>
            </div>
        </div>

        <!-- Macro split -->
        <div class="mt-4 grid grid-cols-3 gap-2">
            <div>
                <div class="sys-track">
                    <div
                        class="h-full rounded-full bg-brand"
                        :style="{ width: carbPercent + '%' }"
                    />
                </div>
                <p class="mt-1.5 text-[10px] text-muted">{{ carbPercent }}% Carbs</p>
            </div>
            <div>
                <div class="sys-track">
                    <div
                        class="h-full rounded-full bg-violet-light"
                        :style="{ width: proteinPercent + '%' }"
                    />
                </div>
                <p class="mt-1.5 text-[10px] text-muted">{{ proteinPercent }}% Protein</p>
            </div>
            <div>
                <div class="sys-track">
                    <div
                        class="h-full rounded-full bg-orange-400"
                        :style="{ width: fatPercent + '%' }"
                    />
                </div>
                <p class="mt-1.5 text-[10px] text-muted">{{ fatPercent }}% Fat</p>
            </div>
        </div>
    </article>
</template>
