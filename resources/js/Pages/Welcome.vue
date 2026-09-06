<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const features = [
    {
        title: 'Track Workouts',
        description:
            'Log every set, rep, and rest day so you always know what you did last time.',
    },
    {
        title: 'See Your Progress',
        description:
            'Watch your strength, endurance, and consistency build up week over week.',
    },
    {
        title: 'Stay Motivated',
        description:
            'Set goals, hit streaks, and celebrate every personal best along the way.',
    },
];
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-svh bg-slate-950 text-white">
        <div class="relative overflow-hidden">
            <div
                class="pointer-events-none absolute -top-24 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-orange-600/30 blur-3xl"
            ></div>
            <div
                class="pointer-events-none absolute right-0 top-1/2 h-64 w-64 translate-x-1/3 rounded-full bg-orange-500/20 blur-3xl"
            ></div>

            <div class="relative mx-auto max-w-5xl px-4 sm:px-6">
                <header
                    class="flex items-center justify-between gap-2 py-6"
                >
                    <Link href="/" class="flex items-center gap-2">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-600 shadow-lg shadow-orange-600/30"
                        >
                            <ApplicationLogo
                                class="h-6 w-6 fill-current text-white"
                            />
                        </span>
                        <span
                            class="text-base font-extrabold uppercase tracking-widest"
                            >FitTrack</span
                        >
                    </Link>

                    <nav class="flex items-center gap-2">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-lg px-3 py-2 text-sm font-semibold text-white transition hover:text-orange-400"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="rounded-lg px-3 py-2 text-sm font-semibold text-white transition hover:text-orange-400"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-lg bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-lg shadow-orange-600/30 transition hover:bg-orange-500"
                            >
                                Sign up
                            </Link>
                        </template>
                    </nav>
                </header>

                <main
                    class="flex flex-col items-center gap-6 py-16 text-center sm:py-24"
                >
                    <h1
                        class="text-4xl font-extrabold leading-tight tracking-tight sm:text-6xl"
                    >
                        Reach Your
                        <span class="text-orange-500">Fitness Goals</span>
                    </h1>
                    <p class="max-w-xl text-base text-white/70 sm:text-lg">
                        Plan your workouts, track every rep, and watch your
                        progress add up. One simple app to keep you moving
                        forward.
                    </p>

                    <div
                        class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row"
                    >
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="w-full rounded-xl bg-orange-600 px-6 py-3 text-center text-sm font-semibold uppercase tracking-widest text-white shadow-lg shadow-orange-600/30 transition hover:bg-orange-500 sm:w-auto"
                        >
                            Go to Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="w-full rounded-xl bg-orange-600 px-6 py-3 text-center text-sm font-semibold uppercase tracking-widest text-white shadow-lg shadow-orange-600/30 transition hover:bg-orange-500 sm:w-auto"
                            >
                                Get Started
                            </Link>
                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="w-full rounded-xl border border-white/20 px-6 py-3 text-center text-sm font-semibold uppercase tracking-widest text-white transition hover:border-white/40 sm:w-auto"
                            >
                                Log in
                            </Link>
                        </template>
                    </div>
                </main>
            </div>
        </div>

        <section class="mx-auto max-w-5xl px-4 pb-16 sm:px-6 sm:pb-24">
            <div class="grid gap-4 sm:grid-cols-3 sm:gap-6">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10"
                >
                    <div
                        class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-orange-600/20"
                    >
                        <span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span>
                    </div>
                    <h2 class="text-lg font-semibold text-white">
                        {{ feature.title }}
                    </h2>
                    <p class="mt-2 text-sm leading-relaxed text-white/60">
                        {{ feature.description }}
                    </p>
                </div>
            </div>
        </section>

        <footer class="border-t border-white/10 py-8 text-center text-sm text-white/40">
            &copy; {{ new Date().getFullYear() }} FitTrack. Keep moving.
        </footer>
    </div>
</template>
