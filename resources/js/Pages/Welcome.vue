<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import gsap from 'gsap';
import FitbakesBrand from '@/Components/FitbakesBrand.vue';

const props = defineProps({ canLogin: Boolean, canRegister: Boolean });
const inertia = usePage();
const page = ref(null);
const completedSets = ref(0);
const entryRoute = computed(() => {
    if (inertia.props.auth?.user) return 'dashboard';
    if (props.canRegister) return 'register';
    return props.canLogin ? 'login' : null;
});
const entryLabel = computed(() =>
    inertia.props.auth?.user ? 'Enter your System' : 'Begin your awakening',
);
const features = [
    {
        number: '01',
        label: 'LIVE MISSIONS',
        title: 'Every workout. A clear objective.',
        text: 'Follow your exercise sequence, log weight and reps, and use rest timers between sets. Your next move is always in focus.',
    },
    {
        number: '02',
        label: 'HUNTER PROGRESSION',
        title: 'Make your effort visible.',
        text: 'Earn XP, advance your level, develop your attributes, and unlock achievements. Build a record of the work you put in.',
    },
    {
        number: '03',
        label: 'DAILY + WEEKLY QUESTS',
        title: 'Give consistency a purpose.',
        text: 'Track your daily and weekly objectives. Complete quests and claim rewards as your routine becomes a habit.',
    },
    {
        number: '04',
        label: 'HEALTH + RECOVERY',
        title: 'Strength needs recovery.',
        text: 'Keep nutrition, hydration, body measurements, and health goals alongside your training. Progress goes beyond the gym.',
    },
];
let animation;
onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    animation = gsap.context(() => {
        gsap.from('[data-reveal]', {
            y: 18,
            opacity: 0,
            duration: 0.8,
            stagger: 0.12,
            ease: 'power2.out',
            clearProps: 'all',
        });
    }, page.value);
});
onUnmounted(() => animation?.revert());
</script>

<template>
    <div ref="page" class="fitbakes">
        <Head title="Fitbakes — Turn your training into a mission">
            <meta name="description" content="Interactive fitness with a progression System. Train with live missions, track your health, complete quests, and level up with Fitbakes." />
            <meta name="theme-color" content="#050914" />
        </Head>
        <a href="#main" class="skip-link">Skip to content</a>
        <header class="site-header shell">
            <Link href="/" aria-label="Fitbakes home">
                <FitbakesBrand />
            </Link>
            <nav class="explore-nav" aria-label="Explore Fitbakes">
                <a href="#system">The System</a>
                <a href="#mission">Try a mission</a>
                <a href="#awakening">Your awakening</a>
            </nav>
            <nav class="account-nav" aria-label="Account">
                <Link v-if="inertia.props.auth?.user" :href="route('dashboard')" class="button small">Enter System <span aria-hidden="true">↗</span>
                </Link>
                <template v-else>
                    <Link v-if="canLogin" :href="route('login')" class="login">Log in</Link>
                    <Link v-if="canRegister" :href="route('register')" class="button small">Join Fitbakes <span aria-hidden="true">↗</span>
                    </Link>
                </template>
            </nav>
        </header>
        <main id="main">
            <section class="hero shell" aria-labelledby="hero-title">
                <div class="hero-art" aria-hidden="true">
                    <div class="gate">
                    </div>
                    <img src="/images/welcome/shadow-hunter.jpg" alt="" fetchpriority="high" width="1200" height="1800" />
                    <svg class="shadow-army" viewBox="0 0 640 300" fill="none">
                        <defs>
                            <g id="fitbakes-sentinel">
                                <path d="M-18 61 Q-39 71 -39 109 L-32 150 -44 210 -20 199 -8 205 4 191 18 205 38 208 28 153 34 111 Q35 77 17 63Z" fill="#0b1026" stroke="#5f5186" stroke-width=".7"/>
                                <path d="M-10 61 -24 66 -34 62 -38 76 -28 87 -20 82 -18 101 -14 118 -21 147 -16 171 -19 193 -26 198 -25 204 -9 204 -4 171 0 147 5 171 8 204 24 204 25 198 18 193 15 171 20 147 14 118 18 99 20 81 28 86 39 77 34 61 24 65 11 61Z" fill="#0a1021" stroke="#8170b3" stroke-width=".8"/>
                                <path d="M-28 84 -32 111 -42 122 -44 116 -40 107 -38 82 M28 83 34 105 32 125 25 126 24 117 25 105 20 88" fill="#10182b" stroke="#8170b3" stroke-width=".8"/>
                                <path d="M-10 60 -13 42 Q-13 28 0 24 Q14 28 13 42 L10 60 0 66Z" fill="#101729" stroke="#9080ba" stroke-width="1"/>
                                <path d="M0 25 0 57 M-10 39 0 43 10 39 M-18 78 0 87 18 78 M-14 101 0 108 14 101 M-13 112 13 112 M-19 148 -6 152 M6 152 19 148" stroke="#5e5484" stroke-width=".9"/>
                                <path d="M-9 44 -3 46 M9 44 3 46" stroke="#94baff" stroke-width="2"/>
                                <path d="M-44 63 -44 206 M-48 67 -44 43 -40 67Z" fill="#15213c" stroke="#647bcb" stroke-width="1"/>
                            </g>
                        </defs>
                        <g opacity=".45">
                            <use href="#fitbakes-sentinel" transform="translate(65 60) scale(.8)"/>
                            <use href="#fitbakes-sentinel" transform="translate(580 60) scale(.8)"/>
                        </g>
                        <g opacity=".8">
                            <use href="#fitbakes-sentinel" transform="translate(140 35)"/>
                            <use href="#fitbakes-sentinel" transform="translate(500 35)"/>
                        </g>
                        <use href="#fitbakes-sentinel" transform="translate(230 30) scale(1.15)"/>
                        <use href="#fitbakes-sentinel" transform="translate(405 30) scale(1.15)"/>
                    </svg>
                    <span class="art-caption">THE SHADOW WITHIN / AWAKEN IT</span>
                </div>
                <div class="hero-copy" data-reveal>
                    <p class="eyebrow">
                        <span class="status-dot">
                        </span> SYSTEM ONLINE. HUNTER DETECTED.</p>
                    <h1 id="hero-title">YOUR BODY.<br />YOUR QUEST.<br />
                        <em>YOUR ASCENT.</em>
                    </h1>
                    <p class="lead">Turn real-world training into an interactive fitness journey. Take on missions. Build your strength. Watch your hunter evolve.</p>
                    <div class="actions">
                        <Link v-if="entryRoute" :href="route(entryRoute)" class="button">{{ entryLabel }} <span aria-hidden="true">↗</span>
                        </Link>
                        <a href="#mission" class="secondary-link">Experience the System <span aria-hidden="true">↓</span>
                        </a>
                    </div>
                    <p class="hero-note">REAL TRAINING. VISIBLE PROGRESSION. YOUR OWN STORY.</p>
                </div>
                <div class="system-notice" data-reveal>
                    <span class="notice-icon" aria-hidden="true">!</span>
                    <div>
                        <span class="eyebrow">SYSTEM NOTIFICATION</span>
                        <p>You have the potential to grow stronger.</p>
                        <small>Your first mission begins with you.</small>
                    </div>
                </div>
            </section>
            <div class="signal-strip">
                <div class="shell">
                    <span>TRAIN WITH PURPOSE</span>
                    <b aria-hidden="true">✦</b>
                    <span>COMPLETE YOUR QUESTS</span>
                    <b aria-hidden="true">✦</b>
                    <span>LEVEL UP IN REAL LIFE</span>
                </div>
            </div>
            <section id="system" class="system-section shell" aria-labelledby="system-title">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">01 / MORE THAN A WORKOUT LOG</p>
                        <h2 id="system-title">Fitness, with a<br />
                            <em>System behind it.</em>
                        </h2>
                    </div>
                    <p>A dark progression world. A practical training companion. Everything you need to turn “I should” into your next completed objective.</p>
                </div>
                <div class="feature-grid">
                    <article v-for="feature in features" :key="feature.number">
                        <div class="feature-label">
                            <span>{{ feature.number }}</span>
                            <p class="eyebrow">{{ feature.label }}</p>
                        </div>
                        <h3>{{ feature.title }}</h3>
                        <p>{{ feature.text }}</p>
                    </article>
                </div>
            </section>
            <section id="mission" class="mission-section shell" aria-labelledby="mission-title">
                <div class="mission-copy">
                    <p class="eyebrow">02 / EXPERIENCE THE SYSTEM</p>
                    <h2 id="mission-title">One mission.<br />
                        <em>One stronger you.</em>
                    </h2>
                    <p>Forget figuring out what comes next between sets. Your Live Mission brings your workout, target muscles, and progress into one focused view.</p>
                    <ul>
                        <li>Exercise demonstrations that show the movement</li>
                        <li>Set logging and built-in rest timers</li>
                        <li>Muscle targets and mission rewards</li>
                    </ul>
                    <p class="demo-hint">Try the preview <span aria-hidden="true">→</span>
                        <small>No account needed. This demo doesn’t save a workout.</small>
                    </p>
                </div>
                <div class="mission-preview" aria-label="Interactive mission preview">
                    <div class="preview-top">
                        <span class="eyebrow">LIVE MISSION</span>
                        <span class="preview-badge">DEMO</span>
                    </div>
                    <div class="preview-title">
                        <h3>IRON ASCENT</h3>
                        <span>STRENGTH / E-RANK</span>
                    </div>
                    <p class="target-label">TARGET: CHEST · SHOULDERS · TRICEPS</p>
                    <div class="preview-metrics">
                        <div>
                            <strong>3</strong>
                            <span>EXERCISES</span>
                        </div>
                        <div>
                            <strong>9</strong>
                            <span>SETS</span>
                        </div>
                        <div>
                            <strong>30</strong>
                            <span>MINUTES</span>
                        </div>
                    </div>
                    <div class="preview-progress">
                        <span>CURRENT EXERCISE</span>
                        <strong>{{ completedSets }} / 3 SETS</strong>
                    </div>
                    <progress :value="completedSets" max="3" aria-label="Demo sets completed">
                    </progress>
                    <div class="exercise-demo">
                        <span class="sequence-number">01</span>
                        <div>
                            <h4>BENCH PRESS</h4>
                            <p>3 sets × 8 reps</p>
                        </div>
                        <span class="exercise-status">{{ completedSets === 3 ? 'COMPLETE' : 'ACTIVE' }}</span>
                    </div>
                    <p class="demo-feedback" aria-live="polite">{{ completedSets === 3 ? 'OBJECTIVE COMPLETE. Every rep is progress.' : completedSets ? 'SET LOGGED. Your effort moves you forward.' : 'SYSTEM: Your next objective is ready.' }}</p>
                    <button class="button demo-button" type="button" @click="completedSets = completedSets === 3 ? 0 : completedSets + 1">{{ completedSets === 3 ? 'Restart preview' : 'Complete a demo set' }} <span aria-hidden="true">{{ completedSets === 3 ? '↺' : '+' }}</span>
                    </button>
                </div>
            </section>
            <section id="awakening" class="awakening shell" aria-labelledby="awakening-title">
                <img src="/images/welcome/training-ground.jpg" alt="Weight machines and equipment in a gym" width="1400" height="934" loading="lazy" />
                <div class="awakening-copy">
                    <p class="eyebrow">03 / YOUR TRAINING GROUND AWAITS</p>
                    <h2 id="awakening-title">YOU DON'T NEED<br />TO START STRONG.<br />
                        <em>JUST START.</em>
                    </h2>
                    <p>Choose your goal. Build your program. Let your next mission be the beginning of something stronger.</p>
                    <Link v-if="entryRoute" :href="route(entryRoute)" class="button">{{ entryLabel }} <span aria-hidden="true">↗</span>
                    </Link>
                    <a v-else href="#mission" class="button">Try a mission <span aria-hidden="true">↑</span>
                    </a>
                </div>
            </section>
        </main>
        <footer class="shell site-footer">
            <div class="footer-main">
                <Link href="/" aria-label="Fitbakes home">
                    <FitbakesBrand />
                </Link>
                <span>REAL EFFORT. EXTRAORDINARY PROGRESSION.</span>
                <small>© {{ new Date().getFullYear() }} Fitbakes.</small>
            </div>
            <div class="credits">
                <span>Original hunter and shadow-soldier concept inspired by Solo Leveling’s Jinwoo. Independent fitness app; no official affiliation.</span>
                <span>Stock photography: <a href="https://www.pexels.com/photo/man-in-hood-in-smoke-on-black-background-10999002/" target="_blank" rel="noopener noreferrer">Pexels</a> / <a href="https://unsplash.com/photos/woman-standing-surrounded-by-exercise-equipment-CQfNt66ttZM" target="_blank" rel="noopener noreferrer">Unsplash</a>
                </span>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.fitbakes {
    background: #050914;
    color: #eaf2ff;
    overflow: clip;
    font-family: Inter,ui-sans-serif,system-ui,sans-serif;
    --accent: #4ea7ff;
    --muted: #9aa9bf;
    --line: rgba(116,140,204,.23);
}
.shell {
    width: min(1240px,calc(100% - 80px));
    margin-inline: auto;
}
.fitbakes a {
    text-decoration: none;
}
.fitbakes :is(a,button):focus-visible {
    outline: 2px solid #9f8cff;
    outline-offset: 5px;
}
.skip-link {
    position: fixed;
    top: -100px;
    left: 20px;
    background: #eaf2ff;
    color: #050914;
    padding: 16px;
    z-index: 20;
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
    border-bottom: 1px solid var(--line);
}
.explore-nav,.account-nav {
    display: flex;
    gap: 28px;
    align-items: center;
}
.explore-nav a,.login {
    font-size: 13px;
    color: #b8c6db;
    min-height: 44px;
    display: inline-flex;
    align-items: center;
}
.button {
    min-height: 52px;
    padding: 15px 24px;
    border: 1px solid #658cef;
    background: linear-gradient(115deg,#1f4b88,#443479);
    color: #f3f7ff;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 26px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .025em;
    transition: background .2s,border-color .2s;
    cursor: pointer;
}
.button:hover {
    background: #315698;
    border-color: #a6caff;
}
.button.small {
    min-height: 44px;
    padding: 10px 16px;
    font-size: 12px;
}
.hero {
    position: relative;
    min-height: 720px;
    display: flex;
    align-items: center;
    padding-block: 65px 160px;
}
.hero-copy {
    position: relative;
    z-index: 2;
    max-width: 650px;
}
.eyebrow {
    font-size: 10px;
    letter-spacing: .18em;
    line-height: 1.7;
    color: #9baee0;
    font-weight: 600;
}
.hero-copy>.eyebrow {
    display: flex;
    gap: 10px;
    align-items: center;
    color: #99caff;
}
.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #5ce1b9;
    box-shadow: 0 0 12px #5ce1b970;
}
.hero h1 {
    font-size: clamp(48px,5.8vw,82px);
    font-weight: 850;
    letter-spacing: -.055em;
    line-height: 1.04;
    margin: 25px 0;
}
.fitbakes em {
    font-style: normal;
    color: #9f8cff;
}
.lead {
    font-size: 16px;
    line-height: 1.9;
    max-width: 440px;
    color: #b1bfd3;
}
.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 22px;
    align-items: center;
    margin-top: 32px;
}
.secondary-link {
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    font-size: 12px;
    color: #ccdaef;
    gap: 14px;
}
.hero-note {
    font-size: 9px;
    letter-spacing: .14em;
    color: #8190a7;
    margin-top: 25px;
}
.hero-art {
    position: absolute;
    width: 60%;
    right: -40px;
    top: 0;
    bottom: 0;
    overflow: hidden;
    background: radial-gradient(ellipse at 60% 60%,#29204888,transparent 70%);
}
.hero-art>img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 40%;
    opacity: .7;
    filter: sepia(.4) saturate(1.8) hue-rotate(186deg);
    mask-image: linear-gradient(to right,transparent,#000 30%),linear-gradient(to bottom,#000 65%,transparent);
    mask-composite: intersect;
}
.gate {
    position: absolute;
    inset: 11% 15% 10% 24%;
    border: 1px solid #6c59bc55;
    transform: rotate(12deg);
    box-shadow: 0 0 70px #6945af18,inset 0 0 70px #6945af18;
}
.shadow-army {
    position: absolute;
    width: 100%;
    bottom: 15px;
    left: 0;
    filter: drop-shadow(0 0 8px #7961ff33);
}
.art-caption {
    position: absolute;
    bottom: 25px;
    right: 50px;
    color: #9b8bc2;
    font-size: 8px;
    letter-spacing: .25em;
}
.system-notice {
    position: absolute;
    bottom: 37px;
    left: 0;
    display: flex;
    gap: 18px;
    align-items: center;
    border: 1px solid var(--line);
    border-left: 2px solid #7970e0;
    background: #080e1ceb;
    padding: 18px 24px;
    width: min(465px,100%);
    z-index: 2;
}
.notice-icon {
    border: 1px solid #829ad9;
    color: #a1bcf6;
    font-size: 23px;
    width: 32px;
    height: 36px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.system-notice p {
    font-size: 13px;
    margin: 4px 0;
}
.system-notice small {
    font-size: 11px;
    color: var(--muted);
}
.signal-strip {
    border-block: 1px solid var(--line);
    background: #080e1b;
}
.signal-strip>.shell {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding-block: 23px;
    font-size: 10px;
    letter-spacing: .18em;
    color: #afbfda;
}
.signal-strip b {
    color: #7063a8;
}
.system-section {
    padding-block: 100px 75px;
}
.section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 40px;
    margin-bottom: 48px;
}
h2 {
    font-size: clamp(32px,3.5vw,48px);
    font-weight: 700;
    letter-spacing: -.04em;
    line-height: 1.13;
    margin-top: 16px;
}
.section-heading>p {
    max-width: 360px;
    color: var(--muted);
    font-size: 14px;
    line-height: 1.85;
}
.feature-grid {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    border-block: 1px solid var(--line);
}
.feature-grid article {
    padding: 30px 25px;
    border-right: 1px solid var(--line);
}
.feature-grid article:first-child {
    padding-left: 0;
}
.feature-grid article:last-child {
    border: 0;
    padding-right: 0;
}
.feature-label {
    display: flex;
    align-items: center;
    gap: 14px;
}
.feature-label>span {
    color: #6b7995;
    font-size: 12px;
}
.feature-label .eyebrow {
    font-size: 9px;
}
.feature-grid h3 {
    font-size: 18px;
    line-height: 1.4;
    margin: 22px 0 14px;
    font-weight: 600;
}
.feature-grid article>p {
    font-size: 13px;
    line-height: 1.9;
    color: var(--muted);
}
.mission-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 100px;
    align-items: center;
    padding-block: 25px 100px;
}
.mission-copy>p:not(.eyebrow) {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.9;
    margin-top: 24px;
    max-width: 420px;
}
.mission-copy ul {
    padding: 0;
    list-style: none;
    margin-top: 25px;
    color: #c7d4e8;
    font-size: 13px;
}
.mission-copy li {
    padding: 10px 0;
}
.mission-copy li:before {
    content: '+';
    color: #9f8cff;
    margin-right: 12px;
}
.demo-hint {
    color: #b8acff !important;
}
.demo-hint>span {
    margin-left: 16px;
}
.demo-hint small {
    display: block;
    font-size: 11px;
    color: var(--muted);
}
.mission-preview {
    background: #080e1c;
    border: 1px solid #5977bb66;
    padding: 28px;
    position: relative;
    box-shadow: 0 16px 70px #02040a66;
}
.mission-preview:before {
    content: '';
    position: absolute;
    top: -1px;
    left: 28px;
    width: 60px;
    height: 2px;
    background: #8b91ff;
}
.preview-top,.preview-progress {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}
.preview-badge {
    font-size: 9px;
    letter-spacing: .14em;
    color: #a3b4d3;
    border: 1px solid var(--line);
    padding: 4px 8px;
}
.preview-title {
    margin-top: 25px;
}
.preview-title h3 {
    font-size: 30px;
    font-weight: 750;
    letter-spacing: -.025em;
}
.preview-title>span {
    font-size: 10px;
    letter-spacing: .12em;
    color: #a1b5dc;
}
.target-label {
    font-size: 9px;
    letter-spacing: .08em;
    color: #9c8ded;
    margin-top: 20px;
}
.preview-metrics {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    border-block: 1px solid var(--line);
    margin-block: 22px;
    padding-block: 18px;
}
.preview-metrics>div {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.preview-metrics strong {
    font-size: 24px;
    font-weight: 600;
}
.preview-metrics span {
    font-size: 8px;
    letter-spacing: .12em;
    color: var(--muted);
}
.preview-progress {
    font-size: 9px;
    letter-spacing: .08em;
}
.preview-progress>span {
    color: var(--muted);
}
progress {
    appearance: none;
    width: 100%;
    height: 4px;
    display: block;
    margin-top: 12px;
    background: #182237;
    border: 0;
}
progress::-webkit-progress-bar {
    background: #182237;
}
progress::-webkit-progress-value {
    background: #7b8fff;
    transition: width .25s;
}
progress::-moz-progress-bar {
    background: #7b8fff;
}
.exercise-demo {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 25px 0 20px;
}
.sequence-number {
    font-size: 12px;
    color: #93a7cf;
}
.exercise-demo h4 {
    font-size: 12px;
    letter-spacing: .06em;
    font-weight: 700;
}
.exercise-demo p {
    font-size: 12px;
    color: var(--muted);
    margin-top: 5px;
}
.exercise-status {
    margin-left: auto;
    color: #5ce1b9;
    font-size: 9px;
}
.demo-feedback {
    font-size: 10px;
    color: #a6b5d1;
    min-height: 30px;
    line-height: 1.6;
}
.demo-button {
    width: 100%;
    margin-top: 10px;
}
.awakening {
    position: relative;
    min-height: 480px;
    overflow: hidden;
    display: flex;
    align-items: center;
    padding: 55px;
    margin-bottom: 75px;
    border: 1px solid var(--line);
}
.awakening>img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: .3;
}
.awakening:after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg,#080e1cf5,#080e1c88,transparent);
}
.awakening-copy {
    position: relative;
    z-index: 1;
}
.awakening h2 {
    font-size: clamp(32px,4vw,52px);
}
.awakening-copy>p:not(.eyebrow) {
    max-width: 390px;
    font-size: 14px;
    color: #b0bfd4;
    line-height: 1.8;
    margin-block: 20px 25px;
}
.site-footer {
    border-top: 1px solid var(--line);
    padding-block: 30px;
}
.footer-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
}
.footer-main>span {
    font-size: 9px;
    letter-spacing: .14em;
    color: var(--muted);
}
.footer-main small {
    font-size: 11px;
    color: var(--muted);
}
.credits {
    display: flex;
    justify-content: space-between;
    gap: 25px;
    margin-top: 25px;
    font-size: 10px;
    line-height: 1.7;
    color: #8796ae;
}
.credits a {
    color: #b1c4e8;
    text-decoration: underline;
}
@media (min-width:1500px) {
    .hero-art {
        right: 0;
    }
}
@media (max-width:1050px) {
    .explore-nav {
        gap: 16px;
    }
    .explore-nav a {
        font-size: 11px;
    }
    .mission-section {
        gap: 45px;
    }
    .feature-grid {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }
    .feature-grid article {
        padding: 25px !important;
    }
    .feature-grid article:nth-child(2) {
        border-right: 0;
    }
    .feature-grid article:nth-child(-n+2) {
        border-bottom: 1px solid var(--line);
    }
    .hero-art {
        width: 67%;
        opacity: .8;
    }
    .hero-copy {
        max-width: 580px;
    }
}
@media (max-width:760px) {
    .shell {
        width: calc(100% - 36px);
    }
    .site-header {
        height: 80px;
        gap: 10px;
    }
    .explore-nav {
        display: none;
    }
    .account-nav {
        gap: 13px;
    }
    .button.small {
        font-size: 10px;
        padding: 10px;
        gap: 9px;
    }
    .login {
        font-size: 12px;
    }
    .hero {
        min-height: 830px;
        align-items: flex-start;
        padding-top: 52px;
        padding-bottom: 145px;
    }
    .hero h1 {
        font-size: clamp(43px,10vw,70px);
    }
    .hero-art {
        width: 100%;
        right: -18px;
        top: 230px;
        opacity: .7;
    }
    .hero-art>img {
        mask-image: linear-gradient(to bottom,transparent,#000 35%,transparent);
    }
    .hero-copy {
        max-width: 100%;
    }
    .lead {
        font-size: 14px;
        max-width: 380px;
    }
    .hero-note {
        font-size: 8px;
    }
    .actions {
        gap: 10px;
        align-items: flex-start;
        flex-direction: column;
    }
    .hero .eyebrow {
        font-size: 9px;
    }
    .system-notice {
        bottom: 22px;
        padding: 15px;
    }
    .shadow-army {
        bottom: 90px;
    }
    .art-caption {
        display: none;
    }
    .signal-strip>.shell {
        font-size: 8px;
        letter-spacing: .08em;
        gap: 10px;
        text-align: center;
        align-items: center;
    }
    .signal-strip b {
        display: none;
    }
    .section-heading {
        display: block;
        margin-bottom: 30px;
    }
    .section-heading>p {
        margin-top: 22px;
    }
    .system-section {
        padding-block: 65px 45px;
    }
    .feature-grid article {
        padding: 22px 14px !important;
    }
    .feature-label {
        gap: 8px;
        align-items: flex-start;
    }
    .feature-label .eyebrow {
        font-size: 8px;
    }
    .feature-grid h3 {
        font-size: 16px;
    }
    .feature-grid article>p {
        font-size: 12px;
    }
    .mission-section {
        grid-template-columns: 1fr;
        gap: 30px;
        padding-bottom: 60px;
    }
    .mission-preview {
        padding: 22px;
    }
    .awakening {
        padding: 30px 23px;
        min-height: 440px;
        margin-bottom: 45px;
    }
    .footer-main {
        flex-wrap: wrap;
        gap: 18px;
    }
    .footer-main>span {
        order: 3;
        width: 100%;
        font-size: 8px;
    }
    .credits {
        flex-direction: column;
        gap: 12px;
    }
    .footer-main small {
        font-size: 10px;
    }
}
@media (prefers-reduced-motion:reduce) {
    .fitbakes * {
        transition: none !important;
        scroll-behavior: auto !important;
    }
}
</style>
