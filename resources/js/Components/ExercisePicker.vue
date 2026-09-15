<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import Icon from '@/Components/Icon.vue';
import ExerciseAnimation from '@/Components/LiveWorkout/ExerciseAnimation.vue';
import { demoFor } from '@/Support/exerciseDemos';
import { exerciseTypesIn, filterExercises, musclesIn, readable } from '@/Support/exercisePicker';

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
const activeMuscle = ref('all');
/** Filters start collapsed on small screens so the cards stay in view; always shown from `sm` up. */
const filtersOpen = ref(false);
const expandedId = ref(null);
const searchInput = ref(null);

const available = computed(() => filterExercises(props.exercises, { excludedIds: props.excludedIds }));

/** Each filter's counts respect the other filter, so a pill never promises more than it shows. */
const muscleScoped = computed(() => filterExercises(available.value, { muscle: activeMuscle.value }));

const typeScoped = computed(() => filterExercises(available.value, { type: activeType.value }));

const types = computed(() => exerciseTypesIn(muscleScoped.value));

const muscles = computed(() => musclesIn(typeScoped.value));

const filtered = computed(() =>
    filterExercises(available.value, { search: search.value, type: activeType.value, muscle: activeMuscle.value }),
);

const activeFilterCount = computed(() => [activeType.value, activeMuscle.value].filter((value) => value !== 'all').length);

const clearFilters = () => {
    activeType.value = 'all';
    activeMuscle.value = 'all';
};

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
        clearFilters();
        filtersOpen.value = false;
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
                    <p class="mt-1 text-[12px] text-muted">
                        Showing {{ filtered.length }} of {{ available.length }}
                        <button
                            v-if="activeFilterCount"
                            type="button"
                            class="ml-1 text-brand hover:underline"
                            @click="clearFilters"
                        >
                            · Clear filters
                        </button>
                    </p>
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

            <div class="mt-4 flex gap-2">
                <label class="block min-w-0 flex-1">
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
                <button
                    type="button"
                    class="sys-pill min-h-11 shrink-0 sm:hidden"
                    :class="filtersOpen || activeFilterCount ? 'sys-pill-active' : ''"
                    :aria-expanded="filtersOpen"
                    @click="filtersOpen = !filtersOpen"
                >
                    {{ filtersOpen ? 'Hide filters' : 'Filters' }}
                    <span v-if="activeFilterCount" class="tabular-nums">{{ activeFilterCount }}</span>
                </button>
            </div>

            <div class="mt-3 flex-col gap-2.5" :class="filtersOpen ? 'flex' : 'hidden sm:flex'">
                <div>
                    <p class="sys-label-sm mb-1.5">Type</p>
                    <div role="group" aria-label="Filter by exercise type" class="flex gap-1.5 overflow-x-auto pb-1">
                        <button
                            type="button"
                            class="sys-pill min-h-9 shrink-0 whitespace-nowrap"
                            :class="activeType === 'all' ? 'sys-pill-active' : 'hover:border-brand/50'"
                            :aria-pressed="activeType === 'all'"
                            @click="activeType = 'all'"
                        >
                            All <span class="tabular-nums opacity-70">{{ muscleScoped.length }}</span>
                        </button>
                        <button
                            v-for="type in types"
                            :key="type.value"
                            type="button"
                            class="sys-pill min-h-9 shrink-0 whitespace-nowrap"
                            :class="activeType === type.value ? 'sys-pill-active' : 'hover:border-brand/50'"
                            :aria-pressed="activeType === type.value"
                            @click="activeType = type.value"
                        >
                            {{ type.label }} <span class="tabular-nums opacity-70">{{ type.count }}</span>
                        </button>
                    </div>
                </div>

                <div>
                    <p class="sys-label-sm mb-1.5">Target muscle</p>
                    <div role="group" aria-label="Filter by target muscle" class="flex gap-1.5 overflow-x-auto pb-1">
                        <button
                            type="button"
                            class="sys-pill min-h-9 shrink-0 whitespace-nowrap"
                            :class="activeMuscle === 'all' ? 'sys-pill-active' : 'hover:border-brand/50'"
                            :aria-pressed="activeMuscle === 'all'"
                            @click="activeMuscle = 'all'"
                        >
                            All <span class="tabular-nums opacity-70">{{ typeScoped.length }}</span>
                        </button>
                        <button
                            v-for="muscle in muscles"
                            :key="muscle.value"
                            type="button"
                            class="sys-pill min-h-9 shrink-0 whitespace-nowrap"
                            :class="activeMuscle === muscle.value ? 'sys-pill-active' : 'hover:border-brand/50'"
                            :aria-pressed="activeMuscle === muscle.value"
                            @click="activeMuscle = muscle.value"
                        >
                            {{ muscle.label }} <span class="tabular-nums opacity-70">{{ muscle.count }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!--
                Scroll on a wrapper, not the grid: a height-constrained grid shrinks
                overflow-hidden cards to nothing.
            -->
            <div v-if="filtered.length" class="mt-4 min-h-0 flex-1 overflow-y-auto pr-1">
                <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
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
            </div>

            <p v-else class="mt-4 rounded-md border border-dashed border-edge/25 p-8 text-center text-sm text-muted">
                No exercises match those filters.
            </p>
        </div>
    </Modal>
</template>
