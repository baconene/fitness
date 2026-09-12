<script setup>
import { computed, ref } from 'vue';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Modal from '@/Components/Modal.vue';
import Icon from '@/Components/Icon.vue';
import ExerciseAnimation from '@/Components/LiveWorkout/ExerciseAnimation.vue';
import { demoFor } from '@/Support/exerciseDemos';

const props = defineProps({
    exercises: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const search = ref('');
const activeCategory = ref('all');
const selected = ref(null);

const readable = (value) => (value ?? '').replaceAll('_', ' ');

const hasDemo = (slug) => Boolean(demoFor(slug));

const filtered = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.exercises.filter((exercise) => {
        if (activeCategory.value !== 'all' && exercise.categorySlug !== activeCategory.value) {
            return false;
        }

        if (!term) {
            return true;
        }

        return (
            exercise.name.toLowerCase().includes(term) ||
            exercise.primaryMuscles.some((muscle) => readable(muscle).includes(term)) ||
            exercise.equipment.some((item) => readable(item).includes(term))
        );
    });
});

/** Demonstrations exist for only a handful so far; surface how many. */
const demoCount = computed(() => props.exercises.filter((exercise) => hasDemo(exercise.slug)).length);
</script>

<template>
    <HunterLayout title="Exercises" subtitle="Every movement the system knows, and how to perform it.">
        <!-- Filters -->
        <div class="sys-panel sys-corners sys-corners-x mb-5 p-4">
            <label class="block">
                <span class="sr-only">Search exercises</span>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search by name, muscle or equipment…"
                    class="w-full rounded-md border border-edge/20 bg-canvas px-4 py-2.5 text-[14px] text-content focus:border-brand"
                />
            </label>

            <div class="mt-3 flex flex-wrap gap-1.5">
                <button
                    type="button"
                    class="sys-pill min-h-9"
                    :class="activeCategory === 'all' ? 'sys-pill-active' : 'hover:border-brand/50'"
                    @click="activeCategory = 'all'"
                >
                    All <span class="tabular-nums opacity-70">{{ exercises.length }}</span>
                </button>
                <button
                    v-for="category in categories"
                    :key="category.slug"
                    type="button"
                    class="sys-pill min-h-9"
                    :class="activeCategory === category.slug ? 'sys-pill-active' : 'hover:border-brand/50'"
                    @click="activeCategory = category.slug"
                >
                    {{ category.name }} <span class="tabular-nums opacity-70">{{ category.count }}</span>
                </button>
            </div>

            <p class="mt-3 text-[11px] text-muted">
                Showing {{ filtered.length }} of {{ exercises.length }} ·
                {{ demoCount }} with an animated demonstration so far.
            </p>
        </div>

        <!-- Card grid: demonstration on top, name beneath -->
        <ul
            v-if="filtered.length"
            class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
        >
            <li v-for="exercise in filtered" :key="exercise.id">
                <button
                    type="button"
                    class="sys-panel sys-panel-hover sys-corners group flex w-full flex-col overflow-hidden text-left"
                    @click="selected = exercise"
                >
                    <!-- Demonstration -->
                    <span class="relative block w-full">
                        <ExerciseAnimation
                            v-if="hasDemo(exercise.slug)"
                            :slug="exercise.slug"
                            :name="exercise.name"
                            compact
                        />
                        <span
                            v-else
                            class="grid aspect-[5/3] w-full place-items-center border-b border-edge/15 bg-[#080e1c] text-muted/30"
                            aria-hidden="true"
                        >
                            <Icon name="body" :size="34" />
                        </span>
                    </span>

                    <!-- Name -->
                    <span class="block w-full px-3 py-2.5">
                        <span class="block truncate text-[13px] font-medium text-content group-hover:text-brand">
                            {{ exercise.name }}
                        </span>
                        <span class="mt-0.5 block truncate text-[11px] capitalize text-muted">
                            {{ readable(exercise.primaryMuscles[0] ?? exercise.category) }}
                        </span>
                    </span>
                </button>
            </li>
        </ul>

        <p v-else class="sys-panel p-10 text-center text-sm text-muted">
            No exercises match that search.
        </p>

        <!-- Detail -->
        <Modal :show="Boolean(selected)" max-width="lg" @close="selected = null">
            <div v-if="selected" class="sys-panel sys-corners sys-corners-x p-6">
                <header class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="sys-display text-[20px] text-content">{{ selected.name }}</h2>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span v-if="selected.category" class="sys-pill">{{ selected.category }}</span>
                            <span v-if="selected.type" class="sys-pill capitalize">{{ readable(selected.type) }}</span>
                            <span v-if="selected.difficulty" class="sys-pill capitalize">{{ selected.difficulty }}</span>
                            <span class="sys-pill sys-pill-active">+{{ selected.xp }} XP</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        aria-label="Close"
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-md border border-edge/20 text-muted hover:text-brand"
                        @click="selected = null"
                    >
                        <Icon name="x" :size="14" />
                    </button>
                </header>

                <div class="mt-5 flex flex-col gap-5">
                    <ExerciseAnimation
                        v-if="hasDemo(selected.slug)"
                        :key="selected.slug"
                        :slug="selected.slug"
                        :name="selected.name"
                    />
                    <div
                        v-else
                        class="grid aspect-[5/3] max-h-52 place-items-center rounded border border-edge/20 bg-[#080e1c] text-muted/30"
                    >
                        <span class="text-center">
                            <Icon name="body" :size="34" />
                            <span class="mt-2 block px-3 text-[11px] leading-relaxed">
                                No demonstration yet
                            </span>
                        </span>
                    </div>

                    <dl class="flex flex-col gap-3 text-[13px]">
                        <div v-if="selected.primaryMuscles.length">
                            <dt class="sys-label-sm">Primary muscles</dt>
                            <dd class="mt-1 flex flex-wrap gap-1.5">
                                <span
                                    v-for="muscle in selected.primaryMuscles"
                                    :key="muscle"
                                    class="sys-pill sys-pill-active capitalize"
                                >
                                    {{ readable(muscle) }}
                                </span>
                            </dd>
                        </div>

                        <div v-if="selected.secondaryMuscles.length">
                            <dt class="sys-label-sm">Secondary</dt>
                            <dd class="mt-1 flex flex-wrap gap-1.5">
                                <span
                                    v-for="muscle in selected.secondaryMuscles"
                                    :key="muscle"
                                    class="sys-pill capitalize"
                                >
                                    {{ readable(muscle) }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="sys-label-sm">Equipment</dt>
                            <dd class="mt-1 flex flex-wrap gap-1.5">
                                <span
                                    v-for="item in selected.equipment"
                                    :key="item"
                                    class="sys-pill capitalize"
                                >
                                    {{ readable(item) }}
                                </span>
                                <span v-if="!selected.equipment.length" class="sys-pill">Bodyweight</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div v-if="selected.instructions" class="sys-divider mt-5 pt-4">
                    <p class="sys-label-sm mb-2">How to perform it</p>
                    <p class="text-[13px] leading-relaxed text-muted">{{ selected.instructions }}</p>
                </div>

                <div v-if="selected.contraindications.length" class="sys-divider mt-4 pt-4">
                    <p class="sys-label-sm mb-2 text-danger">Take care if</p>
                    <ul class="flex flex-wrap gap-1.5">
                        <li
                            v-for="item in selected.contraindications"
                            :key="item"
                            class="sys-pill capitalize text-danger"
                        >
                            {{ readable(item) }}
                        </li>
                    </ul>
                </div>
            </div>
        </Modal>
    </HunterLayout>
</template>
