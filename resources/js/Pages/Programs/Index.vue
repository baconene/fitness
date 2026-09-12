<script setup>
import { computed, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    programs: { type: Array, default: () => [] },
    enrollments: { type: Array, default: () => [] },
    today: { type: String, required: true },
});

const enrolledIds = computed(() => new Set(props.enrollments.map((e) => e.training_program_id)));

/** Enrolling schedules every training day, so the start date matters. */
const startDates = reactive(
    Object.fromEntries(props.programs.map((program) => [program.id, props.today]))
);

const errors = reactive({});
const pending = reactive({});

const trainingDays = (program) =>
    (program.program_weeks || []).reduce(
        (total, week) =>
            total + (week.program_days || []).filter((day) => !day.is_rest_day && (day.program_exercises || []).length).length,
        0
    );

const enroll = (program) => {
    errors[program.id] = null;
    pending[program.id] = true;

    router.post(
        route('programs.enroll', program.id),
        { start_date: startDates[program.id] },
        {
            preserveScroll: true,
            onError: (bag) => {
                errors[program.id] = bag.start_date || 'Could not enroll in this program.';
            },
            onFinish: () => {
                pending[program.id] = false;
            },
        }
    );
};

const duplicate = (program) => {
    router.post(route('programs.duplicate', program.id), {}, { preserveScroll: true });
};

const destroy = (program) => {
    if (window.confirm(`Delete "${program.name}"? This cannot be undone.`)) {
        router.delete(route('programs.destroy', program.id), { preserveScroll: true });
    }
};
</script>

<template>
    <HunterLayout title="Programs" subtitle="Structured plans that schedule your missions for you.">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-[13px] text-muted">
                Build your own plan, or copy a system program and make it yours.
            </p>
            <Link :href="route('programs.create')" class="sys-pill sys-pill-active min-h-10">
                + Build a program
            </Link>
        </div>

        <ul v-if="programs.length" class="grid gap-4 lg:grid-cols-2">
            <li
                v-for="program in programs"
                :key="program.id"
                class="sys-panel sys-corners flex flex-col p-5"
                :class="enrolledIds.has(program.id) ? 'border-brand/40' : ''"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="sys-display text-[19px] text-content">{{ program.name }}</h2>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="sys-pill">{{ program.difficulty }}</span>
                            <span v-if="program.focus" class="sys-pill">{{ program.focus }}</span>
                            <span class="sys-pill">{{ program.duration_weeks }} weeks</span>
                        </div>
                    </div>
                    <span class="sys-badge shrink-0 text-violet-light"><Icon name="clipboard" :size="20" /></span>
                </div>

                <p v-if="program.description" class="mt-3 flex-1 text-[13px] leading-relaxed text-muted">
                    {{ program.description }}
                </p>

                <p class="mt-3 text-[12px] text-muted">
                    {{ trainingDays(program) }} training days will be scheduled.
                </p>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <Link
                        v-if="program.can_edit"
                        :href="route('programs.edit', program.id)"
                        class="sys-pill min-h-9 hover:border-brand/50"
                    >
                        Edit plan
                    </Link>
                    <button type="button" class="sys-pill min-h-9 hover:border-brand/50" @click="duplicate(program)">
                        Copy &amp; edit
                    </button>
                    <button
                        v-if="program.can_delete"
                        type="button"
                        class="sys-pill min-h-9 hover:border-danger/50 hover:text-danger"
                        @click="destroy(program)"
                    >
                        Delete
                    </button>
                    <span v-if="!program.can_edit && enrolledIds.has(program.id)" class="text-[11px] text-muted">
                        Locked while enrolled — copy it to make changes.
                    </span>
                </div>

                <div class="sys-divider mt-4 pt-4">
                    <div v-if="enrolledIds.has(program.id)" class="flex items-center justify-between">
                        <span class="text-[12px] text-muted">Missions already in your training log.</span>
                        <span class="sys-pill sys-pill-active">
                            <Icon name="checkCircle" :size="12" /> Enrolled
                        </span>
                    </div>

                    <div v-else class="flex flex-wrap items-end gap-3">
                        <label class="min-w-[150px] flex-1">
                            <span class="ui-label">Start date</span>
                            <input
                                v-model="startDates[program.id]"
                                type="date"
                                :min="today"
                                class="ui-input w-full"
                            />
                        </label>
                        <button
                            type="button"
                            class="sys-pill sys-pill-active min-h-11 px-5"
                            :disabled="pending[program.id]"
                            @click="enroll(program)"
                        >
                            {{ pending[program.id] ? 'Scheduling' : 'Enroll' }}
                            <Icon name="arrowRight" :size="12" />
                        </button>
                    </div>

                    <p v-if="errors[program.id]" class="ui-error mt-2">{{ errors[program.id] }}</p>
                </div>
            </li>
        </ul>

        <p v-else class="sys-panel p-10 text-center text-sm text-muted">
            No programs are available yet.
        </p>
    </HunterLayout>
</template>
