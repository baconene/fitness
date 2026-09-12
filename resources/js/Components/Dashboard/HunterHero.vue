<script setup>
import { computed, onMounted, ref } from 'vue';
import gsap from 'gsap';
import Icon from '@/Components/Icon.vue';
import RankSigil from '@/Components/Dashboard/RankSigil.vue';
import { rankClass } from '@/Support/rank';
import { prefersReducedMotion } from '@/Composables/useReducedMotion';

const props = defineProps({
    hunterName: { type: String, default: 'HUNTER' },
    rank: { type: String, default: 'E' },
    level: { type: Number, default: 1 },
    currentXp: { type: Number, default: 0 },
    requiredXp: { type: Number, default: 100 },
    title: { type: [String, null], default: null },
    quote: {
        type: String,
        default: 'THE ONLY LIMIT IS THE ONE YOU ACCEPT.',
    },
    systemMessage: {
        type: String,
        default:
            'A NEW DAY, A NEW OPPORTUNITY. COMPLETE TODAY’S MISSIONS AND BECOME STRONGER.',
    },
});

defineEmits(['toggle-nav']);

const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'GOOD MORNING,';
    }

    return hour < 18 ? 'GOOD AFTERNOON,' : 'GOOD EVENING,';
});

const today = new Date().toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
});

const initial = computed(() => (props.hunterName?.[0] ?? 'H').toUpperCase());

/** Scopes --rank-rgb so the rank line and title pill share the rank's colour. */
const rankTheme = computed(() => rankClass(props.rank));

const xpPercent = computed(() => {
    if (!props.requiredXp) {
        return 0;
    }

    return Math.min(100, Math.round((props.currentXp / props.requiredXp) * 100));
});

const formatNumber = (value) => Number(value ?? 0).toLocaleString();

const root = ref(null);
const xpFill = ref(null);

onMounted(() => {
    if (prefersReducedMotion()) {
        // Snap to the final state rather than tweening to it.
        if (xpFill.value) {
            xpFill.value.style.width = `${xpPercent.value}%`;
        }

        return;
    }

    const ctx = gsap.context(() => {
        gsap.from('[data-hero-line]', {
            y: 18,
            opacity: 0,
            duration: 0.7,
            ease: 'power2.out',
            stagger: 0.09,
        });

        gsap.from('[data-hero-system]', {
            x: 28,
            opacity: 0,
            duration: 0.8,
            delay: 0.25,
            ease: 'power3.out',
        });

        gsap.fromTo(
            xpFill.value,
            { width: '0%' },
            { width: `${xpPercent.value}%`, duration: 1.2, delay: 0.4, ease: 'power2.out' }
        );
    }, root.value);

    return () => ctx.revert();
});
</script>

<template>
    <header ref="root" class="relative overflow-hidden border-b border-edge/20">
        <!-- Cinematic backdrop -->
        <div
            class="absolute inset-0 bg-cover bg-center"
            style="
                background-image: url('https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1600&q=75');
            "
        />
        <!-- Readability + colour grade -->
        <div
            class="absolute inset-0"
            style="
                background:
                    linear-gradient(
                        90deg,
                        rgb(3 6 14 / 0.97) 0%,
                        rgb(5 9 20 / 0.86) 42%,
                        rgb(8 13 30 / 0.72) 100%
                    ),
                    radial-gradient(
                        70% 90% at 62% 20%,
                        rgba(118, 87, 255, 0.35) 0%,
                        transparent 70%
                    );
            "
        />

        <div class="sys-scanlines" />

        <div class="relative px-5 pb-10 pt-5 sm:px-8">
            <!-- Utility bar -->
            <div class="flex items-center justify-between">
                <button
                    class="text-muted transition hover:text-content lg:hidden"
                    aria-label="Open navigation"
                    @click="$emit('toggle-nav')"
                >
                    <Icon name="menu" :size="22" />
                </button>

                <div class="ml-auto flex items-center gap-5">
                    <span class="hidden text-brand sm:block">
                        <Icon name="sparkle" :size="16" />
                    </span>
                    <span class="text-[13px] text-content/85">{{ today }}</span>
                    <button class="relative text-muted transition hover:text-content" aria-label="Notifications">
                        <Icon name="bell" :size="18" />
                        <span
                            class="absolute -right-0.5 -top-0.5 h-1.5 w-1.5 rounded-full bg-danger"
                            style="box-shadow: 0 0 6px rgba(255, 118, 118, 0.9)"
                        />
                    </button>
                    <span
                        class="grid h-8 w-8 place-items-center rounded-full border border-edge/40 bg-white/[0.04] text-[12px] font-semibold text-content/90"
                    >
                        {{ initial }}
                    </span>
                </div>
            </div>

            <!-- Identity + system panel -->
            <div class="mt-6 flex flex-col gap-8 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                    <p
                        data-hero-line
                        class="sys-display text-[13px] text-content/70"
                        style="letter-spacing: 0.16em"
                    >
                        {{ greeting }}
                    </p>

                    <h1
                        data-hero-line
                        class="sys-display mt-2 truncate text-[clamp(2.25rem,6vw,3.75rem)] leading-[1.05] text-content"
                        style="text-shadow: 0 0 42px rgba(120, 160, 255, 0.35)"
                    >
                        {{ hunterName.toUpperCase() }}
                    </h1>

                    <div data-hero-line class="mt-4 flex flex-wrap items-center gap-4">
                        <RankSigil :rank="rank" large />

                        <div :class="rankTheme" class="flex flex-wrap items-center gap-2">
                            <p
                                class="sys-rank-text sys-display text-[15px]"
                                style="letter-spacing: 0.1em"
                            >
                                {{ rank }}-RANK HUNTER
                            </p>
                            <span v-if="title" class="sys-pill sys-pill-rank">
                                <Icon name="trophy" :size="11" />
                                {{ title }}
                            </span>
                        </div>
                    </div>

                    <p
                        data-hero-line
                        class="mt-3 max-w-md text-[11px] italic text-muted"
                        style="letter-spacing: 0.12em"
                    >
                        &ldquo;{{ quote }}&rdquo;
                    </p>

                    <!-- Level + XP -->
                    <div data-hero-line class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3">
                        <p class="sys-display text-[26px] leading-none text-content">
                            LV. <span class="text-[30px]">{{ level }}</span>
                        </p>

                        <div class="h-1.5 w-full max-w-[230px] overflow-hidden rounded-full bg-white/[0.08]">
                            <div
                                ref="xpFill"
                                class="h-full rounded-full"
                                :style="{
                                    width: xpPercent + '%',
                                    background:
                                        'linear-gradient(90deg, rgb(54,163,255), rgb(161,140,255))',
                                    boxShadow: '0 0 12px rgba(90,150,255,0.75)',
                                }"
                            />
                        </div>

                        <p class="text-[13px] text-content/75">
                            {{ formatNumber(currentXp) }} / {{ formatNumber(requiredXp) }} XP
                        </p>
                    </div>
                </div>

                <!-- SYSTEM notification -->
                <div
                    data-hero-system
                    class="sys-corners sys-corners-x relative w-full max-w-sm border border-edge/30 p-5 backdrop-blur-md xl:w-[340px]"
                    style="background: rgba(8, 14, 30, 0.66)"
                >
                    <div class="flex items-center gap-2">
                        <span class="sys-pulse h-1.5 w-1.5 rounded-full bg-brand" aria-hidden="true" />
                        <p class="sys-display text-[13px] text-content" style="letter-spacing: 0.2em">
                            SYSTEM
                        </p>
                        <span class="ml-auto text-[9px] tracking-[.2em] text-brand/70">ONLINE</span>
                    </div>
                    <div class="my-3 h-px bg-gradient-to-r from-brand/50 via-edge/20 to-transparent" />
                    <p
                        class="text-[11.5px] uppercase leading-[1.9] text-muted"
                        style="letter-spacing: 0.08em"
                    >
                        {{ systemMessage }}
                    </p>
                </div>
            </div>
        </div>
    </header>
</template>
