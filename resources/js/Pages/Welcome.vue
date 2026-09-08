<script setup>
import BrandMark from '@/Components/BrandMark.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

defineProps({ canLogin: Boolean, canRegister: Boolean });
const page = ref(null);
const selectedFocus = ref(0);
const focuses = [
    {
        name: 'Build strength',
        label: 'STRONGER, ONE REP AT A TIME',
        title: 'Find your strong.',
        description:
            'Your first rep matters just as much as your next personal best. Show up, find your rhythm, and give yourself something to build on.',
        image: 'photo-1534438327276-14e5300c3a48',
        alt: 'Weights and training equipment in a spacious gym',
        tags: [
            'Lift with intention',
            'Celebrate small wins',
            'Keep showing up',
        ],
    },
    {
        name: 'Go the distance',
        label: 'YOUR PACE. YOUR PROGRESS.',
        title: 'Keep moving forward.',
        description:
            'A walk around the block. One more lap. A finish line you once thought was out of reach. Every bit of movement is a step worth taking.',
        image: 'photo-1538805060514-97d9cc17730c',
        alt: 'Athlete taking a break during an outdoor workout',
        tags: ['Find your pace', 'Enjoy the journey', 'Build consistency'],
    },
    {
        name: 'Feel your best',
        label: 'MAKE SPACE FOR YOURSELF',
        title: 'More energy for life.',
        description:
            'Movement can be a moment that belongs to you. Make room to stretch, recharge, and enjoy a routine that feels like your own.',
        image: 'photo-1518611012118-696072aa579a',
        alt: 'Athlete stretching in a bright fitness studio',
        tags: ['Move your way', 'Make room for rest', 'Stay curious'],
    },
];
const focus = computed(() => focuses[selectedFocus.value]);
const photo = (id, width = 1400) =>
    `https://images.unsplash.com/${id}?auto=format&fit=crop&w=${width}&q=85`;
const steps = [
    {
        number: '01',
        title: 'Start where you are.',
        text: 'New to training or finding your way back? You belong here. Pick a goal that feels meaningful to you.',
    },
    {
        number: '02',
        title: 'Make it your own.',
        text: 'Weights, fresh air, or a little space on your mat. Find movement you look forward to coming back to.',
    },
    {
        number: '03',
        title: 'Keep the promise.',
        text: 'Progress isn’t a perfect streak. It’s choosing to begin again, and giving yourself credit along the way.',
    },
];
let observer;
onMounted(() => {
    if (
        window.matchMedia('(prefers-reduced-motion: reduce)').matches ||
        !('IntersectionObserver' in window)
    )
        return;
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('reveal-pending');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12 },
    );
    page.value.querySelectorAll('[data-reveal]').forEach((element) => {
        element.classList.add('reveal-pending');
        observer.observe(element);
    });
});
onUnmounted(() => observer?.disconnect());
</script>

<template>
    <div ref="page" class="fitness-page">
        <Head title="FitTrack — Find your strong">
            <meta
                name="description"
                content="Your next chapter starts with one move. Find your motivation and make fitness your own with FitTrack."
            />
        </Head>
        <a class="skip-link" href="#main">Skip to content</a>
        <header class="site-header shell">
            <Link href="/" aria-label="FitTrack home"><BrandMark /></Link>
            <nav class="section-nav" aria-label="Explore">
                <a href="#your-way">Find your focus</a
                ><a href="#mindset">The mindset</a>
            </nav>
            <nav class="account-nav" aria-label="Account">
                <Link
                    v-if="$page.props.auth?.user"
                    :href="route('dashboard')"
                    class="ui-button ui-button-small"
                    >Dashboard <span aria-hidden="true">↗</span></Link
                >
                <template v-else
                    ><Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="login-link"
                        >Log in</Link
                    ><Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="ui-button ui-button-small"
                        >Join the movement
                        <span aria-hidden="true">↗</span></Link
                    ></template
                >
            </nav>
        </header>
        <main id="main">
            <section class="hero shell" aria-labelledby="hero-title">
                <img
                    class="hero-backdrop"
                    :src="photo('photo-1534438327276-14e5300c3a48', 2000)"
                    alt=""
                    fetchpriority="high"
                />
                <div class="hero-shade"></div>
                <div class="hero-copy">
                    <p class="ui-eyebrow">
                        <span class="status-dot"></span> EVERY BODY. EVERY
                        BEGINNING.
                    </p>
                    <h1 id="hero-title">
                        YOUR NEXT<br />CHAPTER.<br /><span>STRONGER.</span>
                    </h1>
                    <p class="hero-description">
                        You don’t have to be at your best to begin.<br />
                        Just bring yourself. We’ll bring the motivation.
                    </p>
                    <div class="hero-actions">
                        <Link
                            v-if="$page.props.auth?.user"
                            :href="route('dashboard')"
                            class="ui-button"
                            >Keep it going
                            <span aria-hidden="true">↗</span></Link
                        >
                        <Link
                            v-else-if="canRegister"
                            :href="route('register')"
                            class="ui-button"
                            >Start your journey
                            <span aria-hidden="true">↗</span></Link
                        >
                        <Link
                            v-else-if="canLogin"
                            :href="route('login')"
                            class="ui-button"
                            >Start your journey
                            <span aria-hidden="true">↗</span></Link
                        >
                        <a href="#your-way" class="text-link"
                            >Find your motivation
                            <span aria-hidden="true">↓</span></a
                        >
                    </div>
                    <div class="hero-note">
                        <span class="note-line"></span> A little stronger. A
                        little further. A little more you.
                    </div>
                </div>
                <div class="photo-stack" aria-label="Fitness inspiration">
                    <div class="stack-outline"></div>
                    <figure class="athlete-photo">
                        <img
                            :src="
                                photo('photo-1538805060514-97d9cc17730c', 800)
                            "
                            alt="Athlete outdoors in training clothes"
                            fetchpriority="high"
                        />
                        <figcaption>
                            <span>THE ONLY COMPETITION?</span
                            ><strong>Yesterday’s you.</strong>
                        </figcaption>
                    </figure>
                    <div class="motivation-sticker">
                        <span aria-hidden="true">✳</span>
                        <div>
                            PROGRESS OVER<br /><strong>PERFECTION.</strong>
                        </div>
                    </div>
                    <div class="daily-reminder">
                        <span class="reminder-icon" aria-hidden="true">✓</span>
                        <div>
                            <small>YOUR DAILY REMINDER</small>
                            <p>Showing up is a win.</p>
                        </div>
                    </div>
                </div>
                <a href="#your-way" class="scroll-cue"
                    ><span>SCROLL TO GET INSPIRED</span
                    ><span aria-hidden="true">↓</span></a
                >
                <span class="hero-index" aria-hidden="true"
                    >01 / THE BEGINNING</span
                >
            </section>
            <div class="manifesto-strip">
                <span>MOVE WITH PURPOSE</span><b aria-hidden="true">✳</b
                ><span>BUILD YOUR MOMENTUM</span><b aria-hidden="true">✳</b
                ><span>BE YOUR OWN REASON</span><b aria-hidden="true">✳</b>
            </div>
            <section
                id="your-way"
                class="focus-section shell"
                aria-labelledby="focus-heading"
            >
                <div class="section-heading" data-reveal>
                    <div>
                        <p class="ui-eyebrow">01 / FIND YOUR FOCUS</p>
                        <h2 id="focus-heading">
                            Different goals.<br /><span>Same drive.</span>
                        </h2>
                    </div>
                    <p>
                        There’s no single way to be fit.<br />Find what moves
                        you, and make it yours.
                    </p>
                </div>
                <div
                    class="focus-buttons"
                    role="group"
                    aria-label="Choose your fitness focus"
                    data-reveal
                >
                    <button
                        v-for="(item, index) in focuses"
                        :key="item.name"
                        type="button"
                        :aria-pressed="selectedFocus === index"
                        :class="{ selected: selectedFocus === index }"
                        @click="selectedFocus = index"
                    >
                        <span class="focus-number">0{{ index + 1 }}</span
                        >{{ item.name }}<span aria-hidden="true">↗</span>
                    </button>
                </div>
                <div class="focus-panel" data-reveal>
                    <img
                        :key="focus.image"
                        :src="photo(focus.image)"
                        :alt="focus.alt"
                        loading="lazy"
                        class="focus-image"
                    />
                    <div class="focus-gradient"></div>
                    <div
                        class="focus-copy"
                        aria-live="polite"
                        aria-atomic="true"
                    >
                        <p class="ui-eyebrow">{{ focus.label }}</p>
                        <h3>{{ focus.title }}</h3>
                        <p class="focus-description">{{ focus.description }}</p>
                        <div class="focus-tags">
                            <span v-for="tag in focus.tags" :key="tag">{{
                                tag
                            }}</span>
                        </div>
                    </div>
                    <span class="panel-index" aria-hidden="true"
                        >0{{ selectedFocus + 1 }}</span
                    >
                </div>
            </section>
            <section
                id="mindset"
                class="mindset-section shell"
                aria-labelledby="mindset-heading"
            >
                <div class="section-heading" data-reveal>
                    <div>
                        <p class="ui-eyebrow">02 / BUILT FOR THE LONG RUN</p>
                        <h2 id="mindset-heading">
                            Small steps.<br /><span>Real momentum.</span>
                        </h2>
                    </div>
                    <p>
                        Forget the all-or-nothing mindset.<br />Your next step
                        is the one that matters.
                    </p>
                </div>
                <div class="steps-grid">
                    <article
                        v-for="(step, index) in steps"
                        :key="step.number"
                        data-reveal
                        :style="{ '--reveal-delay': `${index * 100}ms` }"
                    >
                        <span class="step-number"
                            >{{ step.number
                            }}<span aria-hidden="true">↗</span></span
                        >
                        <h3>{{ step.title }}</h3>
                        <p>{{ step.text }}</p>
                    </article>
                </div>
            </section>
            <section
                class="closing-section shell"
                aria-labelledby="closing-heading"
                data-reveal
            >
                <img
                    :src="photo('photo-1517836357463-d25dfeac3438', 1600)"
                    alt=""
                    loading="lazy"
                />
                <div class="closing-shade"></div>
                <div class="closing-copy">
                    <p class="ui-eyebrow">03 / THIS IS YOUR MOMENT</p>
                    <h2 id="closing-heading">
                        ONE DAY.<br />OR <span>DAY ONE.</span>
                    </h2>
                    <p>You don’t need a perfect plan. Just a place to start.</p>
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="ui-button"
                        >Make today count
                        <span aria-hidden="true">↗</span></Link
                    ><Link
                        v-else-if="canRegister"
                        :href="route('register')"
                        class="ui-button"
                        >Let’s make it day one
                        <span aria-hidden="true">↗</span></Link
                    ><Link
                        v-else-if="canLogin"
                        :href="route('login')"
                        class="ui-button"
                        >Let’s make it day one
                        <span aria-hidden="true">↗</span></Link
                    ><a v-else href="#your-way" class="ui-button"
                        >Find your focus <span aria-hidden="true">↗</span></a
                    >
                </div>
            </section>
        </main>
        <footer class="site-footer shell">
            <Link href="/" aria-label="FitTrack home"><BrandMark /></Link>
            <p>Made for your kind of strong.</p>
            <small
                >© {{ new Date().getFullYear() }} FitTrack. Keep moving.</small
            >
        </footer>
    </div>
</template>

<style scoped>
.fitness-page {
    background: rgb(var(--color-canvas));
    color: rgb(var(--color-content));
    overflow: clip;
}
.shell {
    width: min(1280px, calc(100% - 96px));
    margin-inline: auto;
}
a,
button {
    -webkit-tap-highlight-color: transparent;
}
a:focus-visible,
button:focus-visible {
    outline: 3px solid rgb(var(--color-brand));
    outline-offset: 6px;
}
.skip-link {
    position: fixed;
    top: -100px;
    left: 20px;
    z-index: 50;
    padding: 12px;
    background: rgb(var(--color-brand));
    color: rgb(var(--color-canvas));
}
.skip-link:focus {
    top: 12px;
}
.site-header {
    height: 96px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
}
.section-nav,
.account-nav {
    display: flex;
    align-items: center;
    gap: 30px;
    font-size: 13px;
    font-weight: 600;
}
.section-nav {
    color: rgb(var(--color-content) / 0.78);
}
.section-nav a:hover,
.login-link:hover {
    color: rgb(var(--color-brand));
}
.hero {
    min-height: 720px;
    position: relative;
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    align-items: center;
    padding-bottom: 70px;
}
.hero-backdrop {
    position: absolute;
    width: calc(100% + 96px);
    max-width: none;
    height: 100%;
    left: -48px;
    top: 0;
    object-fit: cover;
    opacity: 0.23;
}
.hero-shade {
    position: absolute;
    inset: 0 -48px;
    background:
        linear-gradient(90deg, rgb(var(--color-canvas)) 2%, transparent 85%),
        linear-gradient(
            0deg,
            rgb(var(--color-canvas)),
            transparent 50%,
            rgb(var(--color-canvas))
        );
}
.hero-copy {
    position: relative;
    z-index: 2;
    padding-block: 42px;
}
.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgb(var(--color-brand));
    box-shadow: 0 0 0 4px rgb(var(--color-brand) / 0.07);
}
h1 {
    font-size: clamp(60px, 6.9vw, 98px);
    font-weight: 900;
    line-height: 0.99;
    letter-spacing: -5px;
    margin: 29px 0 24px;
}
h1 span {
    color: rgb(var(--color-brand));
}
.hero-description {
    color: rgb(var(--color-muted));
    line-height: 1.8;
    font-size: 15px;
}
.hero-actions {
    display: flex;
    align-items: center;
    gap: 26px;
    margin-top: 30px;
}
.text-link {
    font-size: 12px;
    display: flex;
    gap: 17px;
    align-items: center;
}
.text-link:hover {
    color: rgb(var(--color-brand));
}
.hero-note {
    font-size: 10px;
    color: rgb(var(--color-muted));
    margin-top: 36px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.note-line {
    height: 1px;
    width: 24px;
    background: rgb(var(--color-brand));
}
.photo-stack {
    position: relative;
    width: 80%;
    justify-self: center;
    margin: 20px 0 0 20px;
}
.athlete-photo {
    position: relative;
    height: 452px;
    transform: rotate(5deg);
    border: 5px solid rgb(var(--color-edge));
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 20px 65px #0006;
}
.athlete-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 35%;
}
.athlete-photo::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(transparent 40%, rgb(var(--color-canvas) / 0.93));
}
.athlete-photo figcaption {
    position: absolute;
    z-index: 1;
    bottom: 30px;
    left: 24px;
}
.athlete-photo figcaption span {
    display: block;
    font-size: 9px;
    letter-spacing: 2px;
    color: rgb(var(--color-brand));
    margin-bottom: 6px;
}
.athlete-photo strong {
    font-size: 25px;
    letter-spacing: -1px;
}
.stack-outline {
    position: absolute;
    inset: 8px -10px -6px 10px;
    border: 1px solid rgb(var(--color-brand) / 0.38);
    transform: rotate(-5deg);
    border-radius: 5px;
    background: rgb(var(--color-surface));
}
.motivation-sticker {
    position: absolute;
    top: -30px;
    right: -38px;
    background: rgb(var(--color-brand));
    color: rgb(var(--color-on-brand));
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 17px 18px;
    transform: rotate(9deg);
    font-size: 10px;
    letter-spacing: 0.8px;
    line-height: 1.5;
}
.motivation-sticker > span {
    font-size: 36px;
}
.daily-reminder {
    position: absolute;
    bottom: -24px;
    left: -40px;
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 16px 22px;
    background: rgb(var(--color-surface-raised));
    border: 1px solid rgb(var(--color-edge));
    border-radius: 6px;
    box-shadow: 0 10px 35px #0005;
}
.reminder-icon {
    color: rgb(var(--color-brand));
    border: 1px solid rgb(var(--color-brand) / 0.25);
    border-radius: 50%;
    width: 33px;
    height: 33px;
    display: grid;
    place-items: center;
}
.daily-reminder small {
    font-size: 8px;
    letter-spacing: 1.5px;
    color: rgb(var(--color-muted));
}
.daily-reminder p {
    font-size: 13px;
    margin-top: 3px;
}
.scroll-cue {
    position: absolute;
    bottom: 30px;
    left: 0;
    display: flex;
    align-items: center;
    gap: 20px;
    font-size: 8px;
    letter-spacing: 1.8px;
    color: rgb(var(--color-muted));
}
.scroll-cue > span:last-child {
    font-size: 22px;
    color: rgb(var(--color-brand));
}
.hero-index {
    position: absolute;
    bottom: 36px;
    right: 0;
    font-size: 8px;
    color: rgb(var(--color-muted));
    letter-spacing: 1.6px;
}
.manifesto-strip {
    display: flex;
    justify-content: space-evenly;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    background: rgb(var(--color-brand));
    color: rgb(var(--color-on-brand));
    padding: 22px 24px;
    font-size: clamp(11px, 1.4vw, 18px);
    font-weight: 900;
    letter-spacing: 1px;
}
.manifesto-strip b {
    font-size: 24px;
    line-height: 1;
}
.focus-section {
    padding-top: 100px;
    scroll-margin-top: 35px;
}
.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 30px;
    margin-bottom: 36px;
}
.section-heading h2 {
    font-size: clamp(36px, 4vw, 54px);
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -2px;
    margin-top: 17px;
}
.section-heading h2 span {
    color: rgb(var(--color-muted));
}
.section-heading > p {
    font-size: 13px;
    line-height: 1.8;
    color: rgb(var(--color-muted));
    padding-bottom: 3px;
}
.focus-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 22px;
}
.focus-buttons button {
    flex: 1;
    border: 1px solid rgb(var(--color-edge));
    background: rgb(var(--color-surface));
    padding: 18px 22px;
    border-radius: 4px;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 13px;
    transition:
        background 0.2s,
        border-color 0.2s;
}
.focus-buttons button > span:last-child {
    margin-left: auto;
    font-size: 20px;
}
.focus-buttons button.selected {
    background: rgb(var(--color-brand));
    color: rgb(var(--color-on-brand));
    border-color: rgb(var(--color-brand));
}
.focus-buttons button:hover:not(.selected) {
    border-color: rgb(var(--color-brand));
}
.focus-number {
    font-size: 10px;
    opacity: 0.6;
}
.focus-panel {
    min-height: 410px;
    position: relative;
    border-radius: 7px;
    overflow: hidden;
    background: rgb(var(--color-surface-raised));
    display: flex;
    align-items: center;
}
.focus-image {
    position: absolute;
    inset: 0;
    height: 100%;
    width: 100%;
    object-fit: cover;
    object-position: center 42%;
    animation: image-in 0.5s ease-out;
}
.focus-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        90deg,
        rgb(var(--color-canvas) / 0.96),
        rgb(var(--color-canvas) / 0.77) 35%,
        rgb(var(--color-canvas) / 0.13) 100%
    );
}
.focus-copy {
    position: relative;
    padding: 55px;
    max-width: 590px;
}
.focus-copy h3 {
    font-size: clamp(30px, 3vw, 42px);
    font-weight: 800;
    letter-spacing: -1.5px;
    margin: 19px 0 15px;
}
.focus-description {
    color: rgb(var(--color-content) / 0.8);
    font-size: 14px;
    line-height: 1.9;
}
.focus-tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 26px;
}
.focus-tags span {
    border: 1px solid rgb(var(--color-edge) / 0.7);
    background: rgb(var(--color-canvas) / 0.53);
    padding: 7px 10px;
    border-radius: 3px;
    font-size: 9px;
    color: rgb(var(--color-content) / 0.88);
}
.panel-index {
    position: absolute;
    right: 30px;
    bottom: 8px;
    font-size: 100px;
    font-weight: 900;
    color: rgb(var(--color-content) / 0.25);
    letter-spacing: -7px;
}
.mindset-section {
    padding-block: 100px;
    scroll-margin-top: 30px;
}
.steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
}
.steps-grid article {
    border-top: 1px solid rgb(var(--color-edge));
    padding-top: 23px;
}
.step-number {
    color: rgb(var(--color-brand));
    font-size: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.step-number span {
    font-size: 22px;
    color: rgb(var(--color-muted));
}
.steps-grid h3 {
    font-size: 21px;
    font-weight: 700;
    letter-spacing: -0.5px;
    margin: 24px 0 12px;
}
.steps-grid p {
    font-size: 13px;
    line-height: 1.9;
    color: rgb(var(--color-muted));
    max-width: 330px;
}
.closing-section {
    min-height: 420px;
    position: relative;
    overflow: hidden;
    border-radius: 7px;
    background: rgb(var(--color-surface-raised));
}
.closing-section > img {
    width: 100%;
    height: 100%;
    position: absolute;
    object-fit: cover;
    object-position: center 40%;
}
.closing-shade {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        90deg,
        rgb(var(--color-canvas) / 0.96),
        rgb(var(--color-canvas) / 0.6) 60%,
        rgb(var(--color-canvas) / 0.19)
    );
}
.closing-copy {
    position: relative;
    padding: 54px;
}
.closing-copy h2 {
    font-size: clamp(44px, 5vw, 68px);
    font-weight: 900;
    line-height: 1.03;
    letter-spacing: -3px;
    margin-top: 20px;
}
.closing-copy h2 span {
    color: rgb(var(--color-brand));
}
.closing-copy > p:not(.ui-eyebrow) {
    color: rgb(var(--color-muted));
    font-size: 13px;
    margin: 20px 0 25px;
}
.site-footer {
    padding-block: 42px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}
.site-footer p,
.site-footer small {
    font-size: 10px;
    color: rgb(var(--color-muted));
}
[data-reveal] {
    transition:
        opacity 0.7s ease,
        transform 0.7s cubic-bezier(0.2, 0.65, 0.3, 1);
    transition-delay: var(--reveal-delay, 0ms);
}
.reveal-pending {
    opacity: 0;
    transform: translateY(28px);
}
@keyframes image-in {
    from {
        opacity: 0.4;
        transform: scale(1.035);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
@media (min-width: 900px) and (prefers-reduced-motion: no-preference) {
    .photo-stack {
        transition: transform 0.45s ease;
    }
    .photo-stack:hover {
        transform: translateY(-8px) rotate(-2deg);
    }
}
@media (max-width: 1050px) {
    .shell {
        width: calc(100% - 56px);
    }
    .section-nav {
        display: none;
    }
    .hero {
        min-height: 670px;
    }
    h1 {
        font-size: 72px;
    }
    .hero-actions {
        align-items: flex-start;
        flex-direction: column;
        gap: 20px;
    }
    .photo-stack {
        width: 83%;
    }
    .athlete-photo {
        height: 400px;
    }
    .motivation-sticker {
        right: -18px;
    }
    .hero-copy {
        padding-right: 10px;
    }
}
@media (max-width: 700px) {
    .shell {
        width: calc(100% - 40px);
    }
    .site-header {
        height: 80px;
        gap: 12px;
    }
    .account-nav {
        gap: 15px;
        font-size: 12px;
    }
    .ui-button-small {
        padding: 10px 12px;
        font-size: 11px;
        gap: 10px;
    }
    .account-nav .ui-button-small span {
        display: none;
    }
    .hero {
        grid-template-columns: 1fr;
        padding-bottom: 80px;
    }
    .hero-copy {
        padding-top: 35px;
    }
    h1 {
        font-size: clamp(58px, 13vw, 88px);
        letter-spacing: -3px;
    }
    .ui-eyebrow {
        font-size: 9px;
        letter-spacing: 1.5px;
    }
    .hero-description {
        font-size: 14px;
    }
    .hero-actions {
        flex-direction: row;
        align-items: center;
        gap: 20px;
    }
    .hero-actions .ui-button {
        font-size: 12px;
        gap: 18px;
        padding-inline: 18px;
    }
    .text-link {
        font-size: 11px;
        gap: 8px;
    }
    .hero-note {
        font-size: 9px;
    }
    .photo-stack {
        width: min(75%, 330px);
        margin: 12px 0 45px;
    }
    .athlete-photo {
        height: 345px;
    }
    .motivation-sticker {
        top: -10px;
        right: -25px;
        padding: 11px 13px;
        font-size: 9px;
    }
    .daily-reminder {
        left: -20px;
    }
    .hero-index {
        font-size: 7px;
    }
    .manifesto-strip {
        gap: 12px;
        padding-block: 17px;
        font-size: 10px;
    }
    .manifesto-strip span:last-of-type,
    .manifesto-strip b:last-child {
        display: none;
    }
    .manifesto-strip b {
        font-size: 18px;
    }
    .focus-section {
        padding-top: 60px;
    }
    .section-heading {
        display: block;
    }
    .section-heading > p {
        margin-top: 20px;
    }
    .focus-buttons {
        gap: 7px;
    }
    .focus-buttons button {
        padding: 13px 10px;
        justify-content: center;
        font-size: 11px;
        text-align: center;
    }
    .focus-number,
    .focus-buttons button > span:last-child {
        display: none;
    }
    .focus-panel {
        min-height: 390px;
    }
    .focus-copy {
        padding: 30px 25px;
    }
    .focus-description {
        font-size: 13px;
    }
    .focus-gradient {
        background: linear-gradient(
            90deg,
            rgb(var(--color-canvas) / 0.95),
            rgb(var(--color-canvas) / 0.61)
        );
    }
    .panel-index {
        font-size: 65px;
        right: 20px;
        opacity: 0.5;
    }
    .focus-tags {
        max-width: 250px;
    }
    .mindset-section {
        padding-block: 60px;
    }
    .steps-grid {
        grid-template-columns: 1fr;
        gap: 28px;
    }
    .steps-grid h3 {
        margin-top: 12px;
    }
    .steps-grid p {
        max-width: none;
    }
    .closing-copy {
        padding: 35px 25px;
    }
    .closing-section {
        min-height: 390px;
    }
    .site-footer {
        flex-wrap: wrap;
        padding-block: 30px;
        gap: 15px;
    }
    .site-footer small {
        width: 100%;
    }
}
@media (max-width: 380px) {
    .account-nav {
        gap: 10px;
    }
    .account-nav .ui-button-small {
        max-width: 100px;
        text-align: center;
    }
    .hero-actions {
        flex-direction: column;
        align-items: flex-start;
    }
    .hero-index {
        display: none;
    }
}
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation: none !important;
        transition: none !important;
        scroll-behavior: auto !important;
    }
    .reveal-pending {
        opacity: 1;
        transform: none;
    }
}
</style>
