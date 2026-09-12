<script setup>
/**
 * Inline stroke-icon set (Lucide-style geometry, hand-inlined so the
 * dashboard needs no extra icon dependency).
 *
 * Every path is authored on a 24x24 grid and inherits `currentColor`.
 */
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, required: true },
    size: { type: [Number, String], default: 20 },
    strokeWidth: { type: [Number, String], default: 1.5 },
});

const paths = {
    // --- navigation ---
    dashboard:
        '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    scroll: '<path d="M8 3h9a2 2 0 0 1 2 2v13a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V6"/><path d="M4 6a2 2 0 0 1 2-2h2v3H4z"/><path d="M9 8h6M9 12h6M9 16h3"/>',
    calendar:
        '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
    dumbbell:
        '<path d="M6.5 6.5v11M3.5 9v6M17.5 6.5v11M20.5 9v6M6.5 12h11"/>',
    clipboard:
        '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1H9z"/><path d="M9 11h6M9 15h4"/>',
    castle: '<path d="M4 21V9l2-1 2 1V6l2-1 2 1V4l2-1 2 1v2l2-1 2 1v12"/><path d="M3 21h18M10 21v-5h4v5"/>',
    chart: '<path d="M4 4v16h16"/><path d="M8 15l3-4 3 3 4-6"/>',
    heart: '<path d="M12 20.5 4.2 12.9a4.7 4.7 0 0 1 0-6.7 4.7 4.7 0 0 1 6.7 0l1.1 1.1 1.1-1.1a4.7 4.7 0 0 1 6.7 0 4.7 4.7 0 0 1 0 6.7z"/>',
    briefcase:
        '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 12h18"/>',
    trophy: '<path d="M8 4h8v5a4 4 0 0 1-8 0z"/><path d="M8 5H5v2a3 3 0 0 0 3 3M16 5h3v2a3 3 0 0 1-3 3"/><path d="M10 13v3h4v-3M8 20h8M12 16v4"/>',
    users: '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 5.5a3 3 0 0 1 0 5.8M17 14.5a5.5 5.5 0 0 1 4 5.5"/>',
    settings:
        '<circle cx="12" cy="12" r="3"/><path d="M12 2.5l1.2 2.3 2.5-.5.4 2.6 2.3 1.2-1.4 2.2 1.4 2.2-2.3 1.2-.4 2.6-2.5-.5L12 21.5l-1.2-2.3-2.5.5-.4-2.6-2.3-1.2 1.4-2.2-1.4-2.2 2.3-1.2.4-2.6 2.5.5z"/>',

    // --- metrics ---
    scale: '<rect x="3" y="4" width="18" height="17" rx="3"/><path d="M12 8a4 4 0 0 0-3.6 5.7h7.2A4 4 0 0 0 12 8z"/><path d="M12 8v3"/>',
    flame: '<path d="M12 3s4.5 3.6 4.5 8a4.5 4.5 0 0 1-9 0c0-1.6.7-2.9 1.5-3.8 0 1.5.8 2.3 1.6 2.3 1 0 1.6-.9 1.4-2.2A8 8 0 0 0 12 3z"/><path d="M12 21a6 6 0 0 0 6-6"/><path d="M12 21a6 6 0 0 1-6-6"/>',
    droplet: '<path d="M12 3.5c3 3.6 5.5 6.6 5.5 9.6a5.5 5.5 0 0 1-11 0c0-3 2.5-6 5.5-9.6z"/>',
    target: '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"/>',
    crosshair:
        '<circle cx="12" cy="12" r="8"/><path d="M12 2v4M12 18v4M2 12h4M18 12h4"/>',

    // --- state / actions ---
    check: '<path d="M4.5 12.5l5 5 10-11"/>',
    checkCircle: '<circle cx="12" cy="12" r="9"/><path d="M8.5 12.3l2.4 2.4 4.6-5"/>',
    circle: '<circle cx="12" cy="12" r="9"/>',
    arrowRight: '<path d="M4 12h15M13 6l6 6-6 6"/>',
    chevronDown: '<path d="M6 9.5l6 6 6-6"/>',
    bell: '<path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6z"/><path d="M10.5 20a1.8 1.8 0 0 0 3 0"/>',
    clock: '<circle cx="12" cy="12" r="8.5"/><path d="M12 7v5.2l3.2 2"/>',
    menu: '<path d="M4 7h16M4 12h16M4 17h16"/>',
    x: '<path d="M6 6l12 12M18 6L6 18"/>',
    sparkle:
        '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/><path d="M18.5 15.5l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7z"/>',
    diamond: '<path d="M12 2.5l4.2 4.2L12 21.5 7.8 6.7z"/><path d="M7.8 6.7h8.4"/>',

    // --- workout categories ---
    leg: '<path d="M9 3h6l-.5 7.5 1.5 10h-3l-1-7-1 7H8l1.5-10z"/>',
    run: '<circle cx="15" cy="4.5" r="2"/><path d="M13.5 9L10 11l-1 4M13.5 9l3 2 1.5 4M13.5 9l-2.5 5 2.5 3v4M11 15l-3.5 1-2 4"/>',
    leaf: '<path d="M4 20c0-8 5-14 16-15 1 10-4 15-11 15-2 0-3-1-3-1z"/><path d="M9 15c2-3 5-5 8-6"/>',
    body: '<circle cx="12" cy="4.5" r="2"/><path d="M6 9.5h12M12 8v6M8.5 20l2-6M15.5 20l-2-6"/>',
    steps: '<path d="M4 20h5v-6h4V8h4V4"/><path d="M4 20h16"/>',
    meal: '<path d="M5 3v8a2 2 0 0 0 4 0V3M7 11v10"/><path d="M16 3c-1.5 2-2 4-2 6a2 2 0 0 0 4 0V3"/><path d="M17 9v12"/>',
};

const svgBody = computed(() => paths[props.name] ?? paths.circle);
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        :stroke-width="strokeWidth"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        focusable="false"
        v-html="svgBody"
    />
</template>
