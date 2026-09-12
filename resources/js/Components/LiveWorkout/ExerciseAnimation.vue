<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { demoFor } from '@/Support/exerciseDemos';

const props = defineProps({
    slug: { type: String, required: true },
    name: { type: String, default: 'Exercise' },
});
const pose = computed(() => demoFor(props.slug));
const playing = ref(false);
const failed = ref(false);
let motionPreference;
const syncMotion = () => { playing.value = !motionPreference.matches; };
onMounted(() => {
    motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    syncMotion();
    motionPreference.addEventListener('change', syncMotion);
});
onUnmounted(() => motionPreference?.removeEventListener('change', syncMotion));
watch(() => props.slug, () => { failed.value = false; if (motionPreference) syncMotion(); });
</script>

<template>
    <figure class="exercise-demo overflow-hidden rounded border border-edge/20 bg-canvas">
        <div class="flex items-center justify-between gap-3 border-b border-edge/15 px-4 py-1">
            <p class="text-[10px] uppercase tracking-widest text-muted">Movement example</p>
            <button v-if="pose && !failed" type="button" class="min-h-11 px-2 text-xs font-semibold text-brand" :aria-label="(playing ? 'Pause' : 'Play') + ' exercise demonstration'" @click="playing = !playing">{{ playing ? 'Pause' : 'Play' }}</button>
            <span v-else class="flex min-h-11 items-center text-xs text-muted">{{ pose ? 'Still preview' : 'No demo yet' }}</span>
        </div>
        <div class="flex aspect-[5/3] max-h-64 items-center justify-center overflow-hidden bg-[#080e1c]">
            <img v-if="pose && playing && !failed" :key="pose.slug" :src="pose.gif" :alt="name + ' movement demonstration'" width="400" height="240" decoding="async" class="h-full w-full object-contain" @error="failed = true" />
            <svg v-else-if="pose" viewBox="0 0 200 120" class="h-full w-full" role="img" :aria-label="name + ' starting position'">
                <line x1="58" y1="113" x2="142" y2="113" stroke="#46556e" stroke-width=".6" />
                <g transform="translate(50 0)" class="demo-figure" v-html="pose.a" />
            </svg>
            <p v-else class="max-w-xs px-6 text-center text-sm leading-6 text-muted">A movement demonstration for {{ name }} is not available yet. Check the exercise guidance below.</p>
        </div>
        <figcaption v-if="pose" class="border-t border-edge/15 px-4 py-3 text-xs leading-6 text-muted">{{ pose.label }}</figcaption>
    </figure>
</template>

<style scoped>
.demo-figure :deep(*) { fill: none; stroke: #4ea7ff; stroke-width: 3.2; stroke-linecap: round; stroke-linejoin: round; }
.demo-figure :deep(rect) { fill: #9f8cff; stroke: none; }
</style>
