<script setup>
import { router } from '@inertiajs/vue3';
import HunterLayout from '@/Layouts/HunterLayout.vue';
import Icon from '@/Components/Icon.vue';

defineProps({
    items: { type: Array, default: () => [] },
});

const RARITY_CLASS = {
    common: 'text-muted',
    uncommon: 'text-success',
    rare: 'text-brand',
    epic: 'text-violet-light',
    legendary: 'text-amber-400',
};

const toggleEquip = (item) => {
    const name = item.equipped ? 'inventory.unequip' : 'inventory.equip';

    router.post(route(name, item.id), {}, { preserveScroll: true });
};
</script>

<template>
    <HunterLayout title="Inventory" subtitle="Gear recovered from your runs.">
        <ul v-if="items.length" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <li
                v-for="item in items"
                :key="item.id"
                class="sys-panel flex flex-col p-4"
                :class="item.equipped ? 'border-brand/45' : ''"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[14px] font-medium text-content">{{ item.name }}</p>
                        <p class="mt-0.5 text-[11px] uppercase tracking-widest" :class="RARITY_CLASS[item.rarity] || 'text-muted'">
                            {{ item.rarity }}<span v-if="item.slot" class="text-muted"> &middot; {{ item.slot }}</span>
                        </p>
                    </div>
                    <span class="shrink-0 text-[12px] tabular-nums text-muted">&times;{{ item.quantity }}</span>
                </div>

                <p v-if="item.description" class="mt-2 flex-1 text-[12px] leading-relaxed text-muted">
                    {{ item.description }}
                </p>

                <button
                    v-if="item.equippable"
                    type="button"
                    class="sys-pill mt-4 min-h-9 justify-center transition-colors"
                    :class="item.equipped ? 'sys-pill-active' : 'hover:border-brand/50'"
                    @click="toggleEquip(item)"
                >
                    <Icon :name="item.equipped ? 'checkCircle' : 'circle'" :size="13" />
                    {{ item.equipped ? 'Equipped' : 'Equip' }}
                </button>
            </li>
        </ul>

        <p v-else class="sys-panel p-10 text-center text-sm text-muted">
            Your inventory is empty. Clear a dungeon floor to find your first item.
        </p>
    </HunterLayout>
</template>
