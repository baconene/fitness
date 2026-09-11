<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    inventoryCount: { type: Number, default: 0 },
    skillsLearned: { type: Number, default: 0 },
    titlesUnlocked: { type: Number, default: 0 },
    activeDungeon: { type: [Object, null], default: null },
});

const dungeonProgress = computed(() => {
    if (!props.activeDungeon?.floorCount) {
        return 0;
    }

    return Math.min(
        100,
        Math.round((props.activeDungeon.floor / props.activeDungeon.floorCount) * 100)
    );
});
</script>

<template>
    <section class="sys-panel sys-corners p-5">
        <div class="flex items-center gap-2.5">
            <span class="text-brand"><Icon name="castle" :size="18" /></span>
            <h2 class="sys-label">Systems</h2>
        </div>

        <!-- Phase 2 inventories -->
        <div class="mt-5 grid grid-cols-3 gap-3 text-center">
            <div>
                <span class="text-muted"><Icon name="briefcase" :size="16" class="mx-auto" /></span>
                <p class="mt-2 text-[20px] font-semibold tabular-nums text-content">
                    {{ inventoryCount }}
                </p>
                <p class="mt-0.5 text-[10px] uppercase tracking-widest text-muted">Items</p>
            </div>
            <div>
                <span class="text-muted"><Icon name="sparkle" :size="16" class="mx-auto" /></span>
                <p class="mt-2 text-[20px] font-semibold tabular-nums text-content">
                    {{ skillsLearned }}
                </p>
                <p class="mt-0.5 text-[10px] uppercase tracking-widest text-muted">Skills</p>
            </div>
            <div>
                <span class="text-muted"><Icon name="trophy" :size="16" class="mx-auto" /></span>
                <p class="mt-2 text-[20px] font-semibold tabular-nums text-content">
                    {{ titlesUnlocked }}
                </p>
                <p class="mt-0.5 text-[10px] uppercase tracking-widest text-muted">Titles</p>
            </div>
        </div>

        <!-- Active dungeon run -->
        <div class="sys-divider mt-5 pt-4">
            <p class="sys-label-sm">Active Dungeon</p>

            <template v-if="activeDungeon">
                <div class="mt-2 flex items-baseline justify-between gap-3">
                    <p class="min-w-0 truncate text-[13.5px] text-content/90">
                        {{ activeDungeon.name }}
                    </p>
                    <p class="shrink-0 text-[12px] tabular-nums text-muted">
                        Floor {{ activeDungeon.floor }} / {{ activeDungeon.floorCount }}
                    </p>
                </div>
                <div class="sys-track mt-2">
                    <div class="sys-fill" :style="{ width: dungeonProgress + '%' }" />
                </div>
                <p class="mt-2 text-[11px] text-brand">+{{ activeDungeon.xpEarned }} XP this run</p>
            </template>

            <p v-else class="mt-2 text-[13px] text-muted">No run in progress.</p>
        </div>
    </section>
</template>
