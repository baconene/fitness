<script setup>
/**
 * Responsive navigation: a fixed rail from `lg` up, and a collapsible drawer
 * below it.
 *
 * Both the dashboard and HunterLayout mount this rather than placing
 * HunterSidebar themselves, so the mobile behaviour cannot drift between them.
 */
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import HunterSidebar from '@/Components/Dashboard/HunterSidebar.vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const drawer = ref(null);
let previousOverflow = null;
let desktop;

const lockScroll = (locked) => {
    if (locked && previousOverflow === null) {
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
    } else if (!locked && previousOverflow !== null) {
        document.body.style.overflow = previousOverflow;
        previousOverflow = null;
    }
};

watch(
    () => props.open,
    (open) => {
        const dialog = drawer.value;

        if (!dialog) {
            return;
        }

        if (open && !dialog.open && !desktop?.matches) {
            dialog.showModal();
            lockScroll(true);

            return;
        }

        if (!open && dialog.open) {
            dialog.close();
        }
    },
);

/** Fires for Escape and for close() alike, so state resyncs either way. */
const onDialogClose = () => {
    lockScroll(false);
    emit('close');
};

const onDialogClick = (event) => {
    const bounds = drawer.value?.getBoundingClientRect();
    if (event.target === drawer.value && bounds && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) {
        emit('close');
    }
};

const onBreakpointChange = () => {
    if (desktop.matches) {
        drawer.value?.close();
        lockScroll(false);
        emit('close');
    }
};

onMounted(() => {
    desktop = window.matchMedia('(min-width: 1024px)');
    desktop.addEventListener('change', onBreakpointChange);
    if (props.open && !desktop.matches) {
        drawer.value.showModal();
        lockScroll(true);
    }
});
onBeforeUnmount(() => {
    desktop?.removeEventListener('change', onBreakpointChange);
    drawer.value?.close();
    lockScroll(false);
});
</script>

<template>
    <div>
        <!-- Desktop rail -->
        <HunterSidebar class="hidden lg:flex" />

        <!-- Mobile drawer -->
        <dialog
            id="hunter-navigation-drawer"
            ref="drawer"
            class="hunter-drawer m-0 h-svh max-h-none w-[min(320px,85vw)] max-w-none bg-canvas p-0 text-content backdrop:bg-black/70"
            aria-label="Main navigation"
            @close="onDialogClose"
            @click="onDialogClick"
        >
            <HunterSidebar mobile @close="emit('close')" />
        </dialog>
    </div>
</template>

<style scoped>
.hunter-drawer[open] {
    display: flex;
}

.hunter-drawer::backdrop {
    backdrop-filter: blur(5px);
}
</style>
