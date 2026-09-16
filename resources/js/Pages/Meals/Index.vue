<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Modal from '@/Components/Modal.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    plan: { type: Object, required: true },
    meals: { type: Array, default: () => [] },
    foods: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    today: { type: String, required: true },
});

const MEAL_LABELS = { breakfast: 'Breakfast', lunch: 'Lunch', dinner: 'Dinner', snack: 'Snacks' };

const search = ref('');
const activeCategory = ref('all');
const pickerMeal = ref(null);
const selectedFood = ref(null);
const portionMode = ref('servings');
const servings = ref(1);
const grams = ref(100);

const form = useForm({});

const isLogged = computed(() => Boolean(props.plan.loggedAt));

const filteredFoods = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.foods.filter((food) => {
        if (activeCategory.value !== 'all' && food.category !== activeCategory.value) {
            return false;
        }

        return !term || food.name.toLowerCase().includes(term);
    });
});

/** Live macro preview for the portion being chosen, before it is added. */
const preview = computed(() => {
    const food = selectedFood.value;

    if (!food) {
        return null;
    }

    const factor = portionMode.value === 'grams' && food.servingGrams
        ? grams.value / food.servingGrams
        : servings.value;

    return {
        calories: Math.round(food.calories * factor),
        protein: Math.round(food.protein * factor * 10) / 10,
        carbs: Math.round(food.carbs * factor * 10) / 10,
        fat: Math.round(food.fat * factor * 10) / 10,
    };
});

const openPicker = (meal) => {
    pickerMeal.value = meal;
    selectedFood.value = null;
    search.value = '';
    servings.value = 1;
    grams.value = 100;
    portionMode.value = 'servings';
};

const addToPlan = () => {
    if (!selectedFood.value) {
        return;
    }

    const payload = {
        date: props.plan.date,
        meal: pickerMeal.value,
        food_id: selectedFood.value.id,
    };

    if (portionMode.value === 'grams') {
        payload.grams = grams.value;
    } else {
        payload.servings = servings.value;
    }

    router.post(route('meals.items.store'), payload, {
        preserveScroll: true,
        onSuccess: () => {
            pickerMeal.value = null;
        },
    });
};

const removeItem = (item) =>
    router.delete(route('meals.items.destroy', item.id), { preserveScroll: true });

const changeDate = (event) =>
    router.get(route('meals.index'), { date: event.target.value }, { preserveScroll: true, preserveState: true });

const logPlan = () => form.post(route('meals.log'), { preserveScroll: true });

const macroRow = computed(() => [
    { key: 'protein', label: 'Protein', eaten: props.plan.totals.protein, target: props.plan.targets.protein, tint: 'bg-violet-light' },
    { key: 'carbs', label: 'Carbs', eaten: props.plan.totals.carbs, target: props.plan.targets.carbs, tint: 'bg-brand' },
    { key: 'fat', label: 'Fat', eaten: props.plan.totals.fat, target: props.plan.targets.fat, tint: 'bg-orange-400' },
]);

const share = (eaten, target) => (target > 0 ? Math.min(100, Math.round((eaten / target) * 100)) : 0);
</script>

<template>
    <HunterLayout title="Meal plan" subtitle="Plan the day's food and see it against your targets.">
        <!-- Day summary -->
        <section class="sys-panel sys-corners sys-corners-x mb-5 p-5">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="sys-label-sm mb-1">Planned</p>
                    <p class="text-[32px] font-semibold leading-none tabular-nums text-content">
                        {{ plan.totals.calories.toLocaleString() }}
                        <span class="text-[15px] font-normal text-muted">
                            / {{ plan.targets.calories.toLocaleString() }} kcal
                        </span>
                    </p>
                    <p class="mt-1.5 text-[12px]" :class="plan.remaining.calories < 0 ? 'text-danger' : 'text-muted'">
                        <template v-if="plan.remaining.calories < 0">
                            {{ Math.abs(plan.remaining.calories) }} kcal over target
                        </template>
                        <template v-else>{{ plan.remaining.calories }} kcal still to plan</template>
                    </p>
                </div>

                <div class="flex flex-wrap items-end gap-2">
                    <label class="block">
                        <span class="mb-1 block text-[11px] text-muted">Date</span>
                        <input
                            :value="plan.date"
                            type="date"
                            class="rounded-md border border-edge/20 bg-canvas px-3 py-2 text-[13px] text-content focus:border-brand"
                            @change="changeDate"
                        />
                    </label>
                    <button
                        v-if="!isLogged"
                        type="button"
                        class="sys-pill sys-pill-active min-h-10"
                        :disabled="form.processing || !plan.totals.calories"
                        :class="!plan.totals.calories ? 'opacity-40' : ''"
                        @click="logPlan"
                    >
                        {{ form.processing ? 'Logging…' : 'Log this plan' }}
                    </button>
                    <span v-else class="sys-pill sys-pill-active min-h-10">
                        <Icon name="checkCircle" :size="12" /> Logged
                    </span>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <div class="sys-track flex-1"><div class="sys-fill" :style="{ width: plan.percent + '%' }" /></div>
                <span class="text-[11px] tabular-nums text-content/80">{{ plan.percent }}%</span>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3">
                <div v-for="macro in macroRow" :key="macro.key">
                    <div class="sys-track">
                        <div class="h-full rounded-full" :class="macro.tint" :style="{ width: share(macro.eaten, macro.target) + '%' }" />
                    </div>
                    <p class="mt-1.5 text-[11px] text-muted">
                        <span class="tabular-nums text-content/85">{{ macro.eaten }}</span>/{{ macro.target }}g
                        {{ macro.label }}
                    </p>
                </div>
            </div>

            <p v-if="isLogged" class="mt-3 text-[11px] text-muted">
                This plan has been added to your food log, so it counts as intake for {{ plan.date }}.
            </p>
        </section>

        <!-- Meals -->
        <div class="grid gap-4 lg:grid-cols-2">
            <section v-for="meal in meals" :key="meal" class="sys-panel p-5">
                <header class="mb-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-brand"><Icon name="meal" :size="16" /></span>
                        <h2 class="sys-label">{{ MEAL_LABELS[meal] }}</h2>
                    </div>
                    <span class="sys-pill tabular-nums">{{ plan.meals[meal].calories }} kcal</span>
                </header>

                <ul v-if="plan.meals[meal].items.length" class="mb-3 flex flex-col gap-1.5">
                    <li
                        v-for="item in plan.meals[meal].items"
                        :key="item.id"
                        class="flex flex-col gap-1 rounded-md border border-edge/10 bg-canvas/40 p-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-[13px] text-content/90">{{ item.name }}</p>
                            <p class="text-[11px] tabular-nums text-muted">
                                {{ item.servings }}× · P{{ item.protein }} C{{ item.carbs }} F{{ item.fat }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center justify-end gap-2">
                            <span class="text-[12px] tabular-nums text-muted">{{ item.calories }} kcal</span>
                            <button
                                v-if="!isLogged"
                                type="button"
                                :aria-label="`Remove ${item.name}`"
                                class="sys-pill min-h-8 hover:border-danger/50 hover:text-danger"
                                @click="removeItem(item)"
                            >
                                <Icon name="x" :size="11" />
                            </button>
                        </div>
                    </li>
                </ul>

                <p v-else class="mb-3 py-3 text-center text-[12px] text-muted">Nothing planned.</p>

                <button
                    v-if="!isLogged"
                    type="button"
                    class="sys-pill min-h-9 hover:border-brand/50"
                    @click="openPicker(meal)"
                >
                    + Add food
                </button>
            </section>
        </div>

        <!-- Food picker -->
        <Modal :show="Boolean(pickerMeal)" max-width="lg" @close="pickerMeal = null">
            <div class="sys-panel sys-corners sys-corners-x p-6">
                <header class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="sys-label">Add to {{ MEAL_LABELS[pickerMeal] }}</h2>
                        <p class="mt-0.5 text-[12px] text-muted">{{ foods.length }} Filipino foods and staples</p>
                    </div>
                    <button
                        type="button"
                        aria-label="Close"
                        class="grid h-9 w-9 place-items-center rounded-md border border-edge/20 text-muted hover:text-brand"
                        @click="pickerMeal = null"
                    >
                        <Icon name="x" :size="14" />
                    </button>
                </header>

                <input
                    v-model="search"
                    type="search"
                    placeholder="Search adobo, sinigang, rice…"
                    class="mt-4 w-full rounded-md border border-edge/20 bg-canvas px-4 py-2.5 text-[14px] text-content focus:border-brand"
                />

                <div class="mt-3 flex flex-wrap gap-1.5">
                    <button
                        type="button"
                        class="sys-pill min-h-8"
                        :class="activeCategory === 'all' ? 'sys-pill-active' : 'hover:border-brand/50'"
                        @click="activeCategory = 'all'"
                    >
                        All
                    </button>
                    <button
                        v-for="category in categories"
                        :key="category"
                        type="button"
                        class="sys-pill min-h-8 capitalize"
                        :class="activeCategory === category ? 'sys-pill-active' : 'hover:border-brand/50'"
                        @click="activeCategory = category"
                    >
                        {{ category }}
                    </button>
                </div>

                <ul class="mt-3 max-h-60 overflow-y-auto pr-1">
                    <li v-for="food in filteredFoods" :key="food.id">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 border-b border-edge/10 py-2 text-left"
                            :class="selectedFood?.id === food.id ? 'text-brand' : 'text-content/85 hover:text-brand'"
                            @click="selectedFood = food"
                        >
                            <span class="min-w-0">
                                <span class="block truncate text-[13px]">{{ food.name }}</span>
                                <span class="block text-[11px] text-muted">
                                    {{ food.servingLabel }} · {{ food.servingGrams }}g
                                </span>
                            </span>
                            <span class="shrink-0 text-[12px] tabular-nums text-muted">{{ food.calories }} kcal</span>
                        </button>
                    </li>
                    <li v-if="!filteredFoods.length" class="py-4 text-center text-[13px] text-muted">
                        No food matches that search.
                    </li>
                </ul>

                <!-- Portion -->
                <div v-if="selectedFood" class="sys-divider mt-4 pt-4">
                    <p class="sys-label-sm mb-2">{{ selectedFood.name }}</p>

                    <div class="flex flex-wrap items-end gap-2">
                        <div class="flex gap-1">
                            <button
                                v-for="mode in ['servings', 'grams']"
                                :key="mode"
                                type="button"
                                class="sys-pill min-h-9 capitalize"
                                :class="portionMode === mode ? 'sys-pill-active' : 'hover:border-brand/50'"
                                @click="portionMode = mode"
                            >
                                {{ mode }}
                            </button>
                        </div>

                        <label v-if="portionMode === 'servings'" class="block">
                            <span class="mb-1 block text-[11px] text-muted">{{ selectedFood.servingLabel }}</span>
                            <input
                                v-model.number="servings"
                                type="number"
                                min="0.25"
                                max="20"
                                step="0.25"
                                class="w-24 rounded-md border border-edge/20 bg-canvas px-2 py-2 text-center text-[14px] tabular-nums text-content focus:border-brand"
                            />
                        </label>
                        <label v-else class="block">
                            <span class="mb-1 block text-[11px] text-muted">Grams</span>
                            <input
                                v-model.number="grams"
                                type="number"
                                min="1"
                                max="5000"
                                step="5"
                                class="w-24 rounded-md border border-edge/20 bg-canvas px-2 py-2 text-center text-[14px] tabular-nums text-content focus:border-brand"
                            />
                        </label>

                        <button type="button" class="sys-pill sys-pill-active min-h-10" @click="addToPlan">
                            Add to plan
                        </button>
                    </div>

                    <p v-if="preview" class="mt-3 text-[12px] tabular-nums text-muted">
                        <span class="text-content/85">{{ preview.calories }} kcal</span>
                        · P{{ preview.protein }}g · C{{ preview.carbs }}g · F{{ preview.fat }}g
                    </p>
                    <p v-if="selectedFood.notes" class="mt-1 text-[11px] leading-relaxed text-muted/80">
                        {{ selectedFood.notes }}
                    </p>
                </div>
            </div>
        </Modal>
    </HunterLayout>
</template>
