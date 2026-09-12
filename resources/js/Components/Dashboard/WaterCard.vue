<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    consumedMl: { type: Number, default: 0 },
    consumedLitres: { type: Number, default: 0 },
    targetMl: { type: Number, default: 3000 },
    targetLitres: { type: Number, default: 3 },
    percent: { type: Number, default: 0 },
    /** Today's entries, newest first: { id, amountMl, loggedAt }. */
    logs: { type: Array, default: () => [] },
});

const PRESETS = [250, 500, 750];

const isDetailOpen = ref(false);
const pending = ref(false);
const errorMessage = ref('');
const customMl = ref(400);

const remainingMl = computed(() => Math.max(0, props.targetMl - props.consumedMl));

const targetReached = computed(() => props.consumedMl >= props.targetMl);

const request = (perform) => {
    if (pending.value) {
        return;
    }

    pending.value = true;
    errorMessage.value = '';

    perform({
        preserveScroll: true,
        only: ['health', 'dailyQuests'],
        onError: (errors) => {
            errorMessage.value = Object.values(errors).flat().join(' ');
        },
        onFinish: () => {
            pending.value = false;
        },
    });
};

const logAmount = (amountMl) => {
    if (!amountMl) {
        return;
    }

    request((options) => router.post(route('health.water.store'), { amount_ml: amountMl }, options));
};

const undo = (log) => request((options) => router.delete(route('health.water.destroy', log.id), options));
</script>

<template>
    <article class="sys-panel sys-panel-hover sys-corners sys-corners-x flex flex-col p-5">
        <!-- Summary opens the detail window. -->
        <button
            type="button"
            class="group flex w-full items-start gap-4 text-left"
            :aria-label="`Water intake details, ${consumedLitres} of ${targetLitres} litres`"
            @click="isDetailOpen = true"
        >
            <span class="sys-badge text-brand">
                <Icon name="droplet" :size="22" />
            </span>

            <div class="min-w-0 flex-1">
                <p class="sys-label-sm flex items-center gap-1.5">
                    Water Intake
                    <span class="text-muted/60 transition group-hover:text-brand">
                        <Icon name="arrowRight" :size="11" />
                    </span>
                </p>
                <p class="mt-1 text-[32px] font-semibold leading-none tabular-nums text-content">
                    {{ consumedLitres }} L
                </p>
                <p class="mt-1.5 text-[13px] text-muted">/ {{ targetLitres.toFixed(1) }} L</p>
            </div>
        </button>

        <div class="mt-4 flex items-center gap-3">
            <div class="sys-track flex-1">
                <div class="sys-fill" :style="{ width: percent + '%' }" />
            </div>
            <span class="text-[11px] font-medium tabular-nums text-content/80">{{ percent }}%</span>
        </div>

        <p class="mt-2 text-[11px]" :class="targetReached ? 'text-success' : 'text-muted'">
            <template v-if="targetReached">Daily target reached.</template>
            <template v-else>{{ remainingMl }} ml to go.</template>
        </p>

        <!-- Quick add stays on the card; everything else lives in the modal. -->
        <div class="mt-4 flex flex-wrap gap-1.5">
            <button
                v-for="preset in PRESETS"
                :key="preset"
                type="button"
                class="sys-pill min-h-9 hover:border-brand/50 disabled:opacity-40"
                :disabled="pending"
                @click="logAmount(preset)"
            >
                +{{ preset }} ml
            </button>
        </div>

        <p v-if="errorMessage" role="alert" class="mt-2 text-[11px] text-danger">{{ errorMessage }}</p>

        <Modal :show="isDetailOpen" max-width="md" @close="isDetailOpen = false">
            <div class="sys-panel sys-corners sys-corners-x p-6">
                <header class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="sys-badge text-brand"><Icon name="droplet" :size="20" /></span>
                        <div>
                            <h2 class="sys-label">Hydration</h2>
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
                            {{ consumedLitres }} L
                        </p>
                        <p class="text-[13px] tabular-nums text-muted">
                            of {{ targetLitres.toFixed(1) }} L · {{ percent }}%
                        </p>
                    </div>
                    <div class="sys-track mt-3"><div class="sys-fill" :style="{ width: percent + '%' }" /></div>
                    <p class="mt-2 text-[12px]" :class="targetReached ? 'text-success' : 'text-muted'">
                        <template v-if="targetReached">Daily target reached.</template>
                        <template v-else>{{ remainingMl }} ml to go.</template>
                    </p>
                </div>

                <div class="sys-divider mt-5 pt-4">
                    <p class="sys-label-sm mb-2">Log a drink</p>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="preset in PRESETS"
                            :key="preset"
                            type="button"
                            class="sys-pill min-h-9 hover:border-brand/50 disabled:opacity-40"
                            :disabled="pending"
                            @click="logAmount(preset)"
                        >
                            +{{ preset }} ml
                        </button>
                    </div>

                    <div class="mt-3 flex items-end gap-2">
                        <label class="flex-1">
                            <span class="mb-1 block text-[11px] text-muted">Custom amount (ml)</span>
                            <input
                                v-model.number="customMl"
                                type="number"
                                min="1"
                                max="5000"
                                inputmode="numeric"
                                class="w-full rounded-md border border-edge/20 bg-canvas px-3 py-2 text-center text-[14px] tabular-nums text-content focus:border-brand"
                                @keyup.enter="logAmount(customMl)"
                            />
                        </label>
                        <button
                            type="button"
                            class="sys-pill sys-pill-active min-h-10"
                            :disabled="pending"
                            @click="logAmount(customMl)"
                        >
                            Log
                        </button>
                    </div>
                </div>

                <div class="sys-divider mt-5 pt-4">
                    <p class="sys-label-sm mb-2">Today's entries</p>

                    <ul v-if="logs.length" class="max-h-60 overflow-y-auto pr-1">
                        <li
                            v-for="log in logs"
                            :key="log.id"
                            class="flex items-center justify-between gap-3 border-b border-edge/10 py-2 text-[13px] last:border-0"
                        >
                            <span class="tabular-nums text-content/85">{{ log.amountMl }} ml</span>
                            <span class="ml-auto tabular-nums text-muted">{{ log.loggedAt }}</span>
                            <button
                                type="button"
                                :aria-label="`Remove ${log.amountMl} ml entry`"
                                class="text-muted transition hover:text-danger disabled:opacity-40"
                                :disabled="pending"
                                @click="undo(log)"
                            >
                                <Icon name="x" :size="12" />
                            </button>
                        </li>
                    </ul>

                    <p v-else class="py-4 text-center text-[13px] text-muted">Nothing logged yet today.</p>
                </div>

                <p class="mt-4 text-[11px] leading-relaxed text-muted">
                    Your {{ targetLitres.toFixed(1) }} L target is scaled to your most recent recorded
                    bodyweight. Log a new measurement to update it.
                </p>

                <p v-if="errorMessage" role="alert" class="mt-3 text-[12px] text-danger">{{ errorMessage }}</p>
            </div>
        </Modal>
    </article>
</template>
