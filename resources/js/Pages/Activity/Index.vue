<script setup>
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    entries: { type: Object, required: true },
});
</script>

<template>
    <HunterLayout title="System log" subtitle="Everything the system has recorded, newest first.">
        <div class="sys-panel sys-corners p-5">
            <ul v-if="entries.data.length">
                <li
                    v-for="(entry, index) in entries.data"
                    :key="entry.id"
                    class="flex items-start gap-3 py-3"
                    :class="index ? 'sys-divider' : ''"
                >
                    <span class="mt-0.5 shrink-0 text-brand"><Icon name="sparkle" :size="15" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13.5px] text-content">{{ entry.action }}</p>
                        <p class="mt-0.5 text-[11px] text-muted">
                            {{ entry.occurredAt }}<span v-if="entry.subject"> &middot; {{ entry.subject }}</span>
                        </p>
                    </div>
                </li>
            </ul>

            <p v-else class="py-10 text-center text-sm text-muted">
                Nothing logged yet. Complete a set and the system starts keeping score.
            </p>
        </div>

        <Pagination :links="entries.links" />
    </HunterLayout>
</template>
