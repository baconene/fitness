<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    programs: { type: Array, default: () => [] },
    enrollments: { type: Array, default: () => [] },
});

const enrolledIds = computed(() => new Set(props.enrollments.map((e) => e.training_program_id)));

const dayCount = (program) =>
    (program.program_weeks || []).reduce((total, week) => total + (week.program_days?.length || 0), 0);

const enroll = (program) => {
    router.post(route('programs.enroll', program.id), {}, { preserveScroll: true });
};
</script>

<template>
    <HunterLayout title="Programs" subtitle="Structured training plans you can follow week by week.">
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

                <div class="sys-divider mt-4 flex items-center justify-between pt-4">
                    <span class="text-[12px] text-muted">{{ dayCount(program) }} training days</span>

                    <span v-if="enrolledIds.has(program.id)" class="sys-pill sys-pill-active">
                        <Icon name="checkCircle" :size="12" /> Enrolled
                    </span>
                    <button
                        v-else
                        type="button"
                        class="sys-pill min-h-9 hover:border-brand/50"
                        @click="enroll(program)"
                    >
                        Enroll <Icon name="arrowRight" :size="12" />
                    </button>
                </div>
            </li>
        </ul>

        <p v-else class="sys-panel p-10 text-center text-sm text-muted">
            No programs are available yet.
        </p>
    </HunterLayout>
</template>
