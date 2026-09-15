<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import Icon from '@/Components/Icon.vue';
import ExerciseAnimation from '@/Components/LiveWorkout/ExerciseAnimation.vue';
import { demoFor } from '@/Support/exerciseDemos';
import { exerciseTypesIn, filterExercises, readable } from '@/Support/exercisePicker';

const props = defineProps({
    show: { type: Boolean, default: false },
    /** Catalogue entries as shaped by ExerciseCatalogService. */
    exercises: { type: Array, default: () => [] },
    /** Exercises that cannot be chosen again, such as ones already in the workout. */
    excludedIds: { type: Array, default: () => [] },
    selectedId: { type: [Number, String], default: null },
    title: { type: String, default: 'Choose an exercise' },
});

const emit = defineEmits(['close', 'select']);

const search = ref('');
const activeType = ref('all');
const expandedId = ref(null);
const searchInput = ref(null);

const available = computed(() => filterExercises(props.exercises, { excludedIds: props.excludedIds }));

const types = computed(() => exerciseTypesIn(available.value));

const filtered = computed(() => filterExercises(available.value, { search: search.value, type: activeType.value }));

const list = (items) => (items ?? []).map(readable).join(', ');

const hasDetails = (exercise) =>
    Boolean(exercise.instructions) || exercise.secondaryMuscles?.length > 0 || exercise.contraindications?.length > 0;

const isSelected = (exercise) => Number(props.selectedId) === exercise.id;

const toggleDetails = (exercise) => {
    expandedId.value = expandedId.value === exercise.id ? null : exercise.id;
};

watch(
    () => props.show,
    async (isShown) => {
        if (!isShown) {
            return;
        }

        search.value = '';
        activeType.value = 'all';
        expandedId.value = null;

        await nextTick();
        searchInput.value?.focus();
    },
);
</script>

<template>
    <Modal :show="show" max-width="5xl" @close="emit('close')">
        <div class="sys-panel sys-corners sys-corners-x flex max-h-[85vh] flex-col p-5 sm:p-6">
            <header class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="sys-display text-[18px] text-content">{{ title }}</h2>
                    <p class="mt-1 text-[12px] text-muted">Showing {{ filtered.length }} of {{ available.length }}</p>
                </div>
                <button
                    type="button"
                    aria-label="Close"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-md border border-edge/20 text-muted hover:text-brand"
                    @click="emit('close')"
                >
                    <Icon name="x" :size="14" />
                </button>
            </header>

            <label class="mt-4 block">
                <span class="sr-only">Search exercises</span>
                <!-- The picker can sit inside a form; Enter must not submit it. -->
                <input
                    ref="searchInput"
                    v-model="search"
                    type="search"
                    placeholder="Search by name, muscle or equipment…"
                    class="w-full rounded-md border border-edge/20 bg-canvas px-4 py-2.5 text-[14px] text-content focus:border-brand"
                    @keydown.enter.prevent
                />
            </label>

            <div role="group" aria-label="Filter by exercise type" class="mt-3 flex flex-wrap gap-1.5">
                <button
                    type="button"
                    class="sys-pill min-h-9"
                    :class="activeType === 'all' ? 'sys-pill-active' : 'hover:border-brand/50'"
                    :aria-pressed="activeType === 'all'"
                    @click="activeType = 'all'"
                >
                    All <span class="tabular-nums opacity-70">{{ available.length }}</span>
                </button>
                <button
                    v-for="type in types"
                    :key="type.value"
                    type="button"
                    class="sys-pill min-h-9"
                    :class="activeType === type.value ? 'sys-pill-active' : 'hover:border-brand/50'"
                    :aria-pressed="activeType === type.value"
                    @click="activeType = type.value"
                >
                    {{ type.label }} <span class="tabular-nums opacity-70">{{ type.count }}</span>
                </button>
            </div>

            <ul
                v-if="filtered.length"
                class="mt-4 grid min-h-0 flex-1 grid-cols-1 content-start gap-3 overflow-y-auto pr-1 sm:grid-cols-2 lg:grid-cols-3"
            >
                <li
                    v-for="exercise in filtered"
                    :key="exercise.id"
                    class="flex flex-col overflow-hidden rounded-md border"
                    :class="isSelected(exercise) ? 'border-brand/60 bg-brand/5' : 'border-edge/15 bg-canvas/40'"
                >
                    <ExerciseAnimation
                        v-if="demoFor(exercise.slug)"
                        :slug="exercise.slug"
                        :name="exercise.name"
                        compact
                    />
                    <span
                        v-else
                        class="grid aspect-[5/3] max-h-40 w-full place-items-center border-b border-edge/15 bg-[#080e1c] text-muted/30"
                        aria-hidden="true"
                    >
                        <Icon name="body" :size="30" />
                    </span>

                    <div class="flex flex-1 flex-col p-3">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="min-w-0 text-[14px] font-medium text-content">{{ exercise.name }}</h3>
                            <span class="shrink-0 text-[11px] tabular-nums text-brand">+{{ exercise.xp }} XP</span>
                        </div>

                        <div class="mt-2 flex flex-wrap gap-1">
                            <span v-if="exercise.type" class="sys-pill capitalize">{{ readable(exercise.type) }}</span>
                            <span v-if="exercise.difficulty" class="sys-pill capitalize">{{ exercise.difficulty }}</span>
                        </div>

                        <dl class="mt-3 flex flex-col gap-1 text-[12px]">
                            <div class="flex gap-2">
                                <dt class="w-20 shrink-0 text-muted">Targets</dt>
                                <dd class="min-w-0 capitalize text-content/85">
                                    {{ list(exercise.primaryMuscles) || exercise.category || '—' }}
                                </dd>
                            </div>
                            <div class="flex gap-2">
                                <dt class="w-20 shrink-0 text-muted">Equipment</dt>
                                <dd class="min-w-0 capitalize text-content/85">{{ list(exercise.equipment) || 'Bodyweight' }}</dd>
                            </div>
                        </dl>

                        <div
                            v-if="expandedId === exercise.id"
                            class="mt-3 flex flex-col gap-2 border-t border-edge/10 pt-3 text-[12px] leading-relaxed"
                        >
                            <p v-if="exercise.secondaryMuscles?.length" class="capitalize text-muted">
                                Also works: {{ list(exercise.secondaryMuscles) }}
                            </p>
                            <p v-if="exercise.instructions" class="text-muted">{{ exercise.instructions }}</p>
                            <p v-if="exercise.contraindications?.length" class="capitalize text-danger">
                                Take care if: {{ list(exercise.contraindications) }}
                            </p>
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-2 pt-3">
                            <button
                                v-if="hasDetails(exercise)"
                                type="button"
                                class="sys-pill min-h-9 hover:border-brand/50"
                                :aria-expanded="expandedId === exercise.id"
                                @click="toggleDetails(exercise)"
                            >
                                {{ expandedId === exercise.id ? 'Hide details' : 'Details' }}
                            </button>
                            <span v-else />
                            <button type="button" class="sys-pill sys-pill-active min-h-9" @click="emit('select', exercise)">
                                {{ isSelected(exercise) ? 'Selected' : 'Choose' }}
                            </button>
                        </div>
                    </div>
                </li>
            </ul>

            <p v-else class="mt-4 rounded-md border border-dashed border-edge/25 p-8 text-center text-sm text-muted">
                No exercises match that search.
            </p>
        </div>
    </Modal>
</template>
