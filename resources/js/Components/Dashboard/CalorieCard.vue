<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    /** Consumed today. */
    calories: { type: Number, default: 0 },
    protein: { type: Number, default: 0 },
    carbs: { type: Number, default: 0 },
    fat: { type: Number, default: 0 },
    percent: { type: Number, default: 0 },
    remaining: { type: Number, default: 0 },
    /** Derived daily targets, plus how they were worked out. */
    targets: { type: Object, required: true },
    /** Today's entries, newest first. */
    entries: { type: Array, default: () => [] },
});

const isDetailOpen = ref(false);
const pending = ref(false);
const errorMessage = ref('');

const form = ref({ name: '', calories: null, protein_g: null, carbs_g: null, fat_g: null });

const isOver = computed(() => props.remaining < 0);

const macros = computed(() => [
    { key: 'protein', label: 'Protein', eaten: props.protein, target: props.targets.protein, tint: 'bg-violet-light' },
    { key: 'carbs', label: 'Carbs', eaten: props.carbs, target: props.targets.carbs, tint: 'bg-brand' },
    { key: 'fat', label: 'Fat', eaten: props.fat, target: props.targets.fat, tint: 'bg-orange-400' },
]);

const share = (eaten, target) => (target > 0 ? Math.min(100, Math.round((eaten / target) * 100)) : 0);

const request = (perform) => {
    if (pending.value) {
        return;
    }

    pending.value = true;
    errorMessage.value = '';

    perform({
        preserveScroll: true,
        only: ['health'],
        onError: (errors) => {
            errorMessage.value = Object.values(errors).flat().join(' ');
        },
        onFinish: () => {
            pending.value = false;
        },
    });
};

const logMeal = () => {
    if (!form.value.name?.trim() || !form.value.calories) {
        errorMessage.value = 'A name and a calorie count are needed.';

        return;
    }

    request((options) =>
        router.post(route('health.food.store'), form.value, {
            ...options,
            onSuccess: () => {
                form.value = { name: '', calories: null, protein_g: null, carbs_g: null, fat_g: null };
            },
        }),
    );
};

const removeEntry = (entry) =>
    request((options) => router.delete(route('health.food.destroy', entry.id), options));
</script>

<template>
    <article class="sys-panel sys-panel-hover sys-corners sys-corners-x flex flex-col p-5">
        <button
            type="button"
            class="group flex w-full items-start gap-4 text-left"
            :aria-label="`Nutrition details, ${calories} of ${targets.calories} kcal`"
            @click="isDetailOpen = true"
        >
            <span class="sys-badge text-orange-400"><Icon name="flame" :size="22" /></span>

            <div class="min-w-0 flex-1">
                <p class="sys-label-sm flex items-center gap-1.5">
                    Calories
                    <span class="text-muted/60 transition group-hover:text-brand">
                        <Icon name="arrowRight" :size="11" />
                    </span>
                </p>
                <p class="mt-1 text-[32px] font-semibold leading-none tabular-nums text-content">
                    {{ calories.toLocaleString() }}
                </p>
                <p class="mt-1.5 text-[13px] text-muted">/ {{ targets.calories.toLocaleString() }} kcal</p>
            </div>
        </button>

        <div class="mt-4 flex items-center gap-3">
            <div class="sys-track flex-1">
                <div class="sys-fill" :style="{ width: percent + '%' }" />
            </div>
            <span class="text-[11px] font-medium tabular-nums text-content/80">{{ percent }}%</span>
        </div>

        <p class="mt-2 text-[11px]" :class="isOver ? 'text-danger' : 'text-muted'">
            <template v-if="isOver">{{ Math.abs(remaining) }} kcal over target.</template>
            <template v-else>{{ remaining }} kcal left today.</template>
        </p>

        <!-- Macros eaten against target. -->
        <div class="mt-4 grid grid-cols-3 gap-2">
            <div v-for="macro in macros" :key="macro.key">
                <div class="sys-track">
                    <div
                        class="h-full rounded-full"
                        :class="macro.tint"
                        :style="{ width: share(macro.eaten, macro.target) + '%' }"
                    />
                </div>
                <p class="mt-1.5 text-[10px] text-muted">
                    <span class="tabular-nums text-content/80">{{ macro.eaten }}</span>/{{ macro.target }}g
                    {{ macro.label }}
                </p>
            </div>
        </div>

        <Modal :show="isDetailOpen" max-width="md" @close="isDetailOpen = false">
            <div class="sys-panel sys-corners sys-corners-x p-6">
                <header class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="sys-badge text-orange-400"><Icon name="flame" :size="20" /></span>
                        <div>
                            <h2 class="sys-label">Nutrition</h2>
                            <p class="mt-0.5 text-[12px] text-muted">Today</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        aria-label="Close"
                        class="grid h-9 w-9 place-items-center rounded-md border border-edge/20 text-muted hover:text-brand"
                        @click="isDetailOpen = false"
                    >
                        <Icon name="x" :size="14" />
                    </button>
                </header>

                <div class="mt-5">
                    <div class="flex items-baseline justify-between">
                        <p class="text-[28px] font-semibold leading-none tabular-nums text-content">
                            {{ calories.toLocaleString() }}
                        </p>
                        <p class="text-[13px] tabular-nums text-muted">
                            of {{ targets.calories.toLocaleString() }} kcal · {{ percent }}%
                        </p>
                    </div>
                    <div class="sys-track mt-3"><div class="sys-fill" :style="{ width: percent + '%' }" /></div>
                    <p class="mt-2 text-[12px]" :class="isOver ? 'text-danger' : 'text-muted'">
                        <template v-if="isOver">{{ Math.abs(remaining) }} kcal over target.</template>
                        <template v-else>{{ remaining }} kcal left today.</template>
                    </p>
                </div>

                <!-- Log a meal -->
                <form class="sys-divider mt-5 flex flex-col gap-2 pt-4" @submit.prevent="logMeal">
                    <p class="sys-label-sm">Log a meal</p>

                    <div class="flex gap-2">
                        <input
                            v-model="form.name"
                            type="text"
                            maxlength="120"
                            placeholder="Chicken and rice"
                            class="min-w-0 flex-1 rounded-md border border-edge/20 bg-canvas px-3 py-2 text-[14px] text-content focus:border-brand"
                        />
                        <input
                            v-model.number="form.calories"
                            type="number"
                            min="1"
                            max="10000"
                            inputmode="numeric"
                            placeholder="kcal"
                            class="w-24 rounded-md border border-brand/40 bg-canvas px-2 py-2 text-center text-[14px] tabular-nums text-content focus:border-brand"
                        />
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <label v-for="macro in ['protein_g', 'carbs_g', 'fat_g']" :key="macro" class="block">
                            <span class="mb-1 block text-[10px] uppercase tracking-wider text-muted">
                                {{ { protein_g: 'Protein', carbs_g: 'Carbs', fat_g: 'Fat' }[macro] }}
                            </span>
                            <input
                                v-model.number="form[macro]"
                                type="number"
                                min="0"
                                max="1000"
                                inputmode="numeric"
                                placeholder="g"
                                class="w-full rounded-md border border-edge/20 bg-canvas px-2 py-2 text-center text-[13px] tabular-nums text-content focus:border-brand"
                            />
                        </label>
                    </div>

                    <button type="submit" class="sys-pill sys-pill-active min-h-10 self-start" :disabled="pending">
                        {{ pending ? 'Saving…' : 'Log meal' }}
                    </button>
                </form>

                <p v-if="errorMessage" role="alert" class="mt-2 text-[12px] text-danger">{{ errorMessage }}</p>

                <!-- Today's entries -->
                <div class="sys-divider mt-5 pt-4">
                    <p class="sys-label-sm mb-2">Today's entries</p>

                    <ul v-if="entries.length" class="max-h-56 overflow-y-auto pr-1">
                        <li
                            v-for="entry in entries"
                            :key="entry.id"
                            class="flex items-center justify-between gap-3 border-b border-edge/10 py-2 text-[13px] last:border-0"
                        >
                            <span class="min-w-0 flex-1 truncate text-content/85">{{ entry.name }}</span>
                            <span class="shrink-0 tabular-nums text-muted">{{ entry.calories }} kcal</span>
                            <span class="shrink-0 tabular-nums text-muted/70">{{ entry.loggedAt }}</span>
                            <button
                                type="button"
                                :aria-label="`Remove ${entry.name}`"
                                class="shrink-0 text-muted transition hover:text-danger disabled:opacity-40"
                                :disabled="pending"
                                @click="removeEntry(entry)"
                            >
                                <Icon name="x" :size="12" />
                            </button>
                        </li>
                    </ul>

                    <p v-else class="py-4 text-center text-[13px] text-muted">Nothing logged yet today.</p>
                </div>

                <p v-if="targets.basis" class="mt-4 text-[11px] leading-relaxed text-muted">
                    Target from {{ targets.basis.toLowerCase() }}.<template v-if="targets.isEstimated">
                        Log body fat for a closer estimate.</template>
                </p>
            </div>
        </Modal>
    </article>
</template>
