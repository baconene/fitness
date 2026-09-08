import { ref, onMounted } from 'vue';

/**
 * Synchronous check, for call sites that must decide before a tween is
 * built (GSAP timelines) rather than reacting to a ref.
 */
export function prefersReducedMotion() {
    if (typeof window === 'undefined' || !window.matchMedia) {
        return false;
    }

    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

export function useReducedMotion() {
    const prefersReducedMotion = ref(false);

    onMounted(() => {
        const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        prefersReducedMotion.value = mediaQuery.matches;

        mediaQuery.addEventListener('change', (e) => {
            prefersReducedMotion.value = e.matches;
        });
    });

    return {
        prefersReducedMotion,
    };
}
