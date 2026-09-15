<script setup>
import FitbakesBrand from '@/Components/FitbakesBrand.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    eyebrow: { type: String, default: 'SYSTEM / ACCOUNT ACCESS' },
    headline: { type: String, default: 'YOUR NEXT' },
    accent: { type: String, default: 'ASCENT AWAITS.' },
    description: {
        type: String,
        default: 'Your missions, your progress, your next level. Reconnect with your training System.',
    },
    systemMessage: { type: String, default: 'Your progression is waiting. Take the next step.' },
});
</script>

<template>
    <div class="guest-layout">
        <a href="#auth-content" class="guest-skip">Skip to form</a>
        <header class="guest-header">
            <Link href="/" aria-label="Fitbakes home">
                <FitbakesBrand />
            </Link>
            <Link href="/" class="guest-back">
                <span aria-hidden="true">↖</span> Back to Fitbakes</Link>
        </header>
        <main class="guest-main">
            <aside class="guest-story" aria-label="Your fitness System">
                <img src="/images/welcome/shadow-hunter.jpg" alt="" class="guest-photo" width="1200" height="1800" />
                <div class="guest-grid" aria-hidden="true">
                </div>
                <div class="guest-story-content">
                    <p class="system-eyebrow">
                        <span class="guest-dot" aria-hidden="true">
                        </span>{{ eyebrow }}</p>
                    <p class="guest-headline">{{ headline }}<br />
                        <span>{{ accent }}</span>
                    </p>
                    <p class="guest-description">{{ description }}</p>
                    <div class="system-path" aria-label="Your journey">
                        <span>01 <b>AWAKEN</b>
                        </span>
                        <span>02 <b>TRAIN</b>
                        </span>
                        <span>03 <b>ASCEND</b>
                        </span>
                    </div>
                </div>
                <img src="/images/welcome/beru.png" alt="" class="guest-shadow" width="800" height="439" />
                <div class="guest-story-footer">
                    <span class="system-seal" aria-hidden="true">!</span>
                    <div>
                        <p class="system-eyebrow">SYSTEM NOTIFICATION</p>
                        <p>{{ systemMessage }}</p>
                    </div>
                </div>
            </aside>
            <section id="auth-content" class="guest-form-panel" aria-label="Your Fitbakes account" tabindex="-1">
                <div class="panel-top">
                    <span>FITBAKES / HUNTER ACCESS</span>
                    <span class="panel-signal" aria-hidden="true">◆</span>
                </div>
                <div class="guest-form-content">
                    <slot />
                </div>
                <p class="guest-form-note">REAL EFFORT. EXTRAORDINARY PROGRESSION.</p>
            </section>
        </main>
        <footer class="guest-footer">
            <span>© {{ new Date().getFullYear() }} Fitbakes.</span>
            <span>Independent fitness app. Beru artwork: Solo Leveling: ARISE / Netmarble. No official affiliation.</span>
        </footer>
    </div>
</template>

<style scoped>
.guest-layout {
    min-height: 100svh;
    background: #050914;
    color: #eaf2ff;
    padding-inline: max(22px, calc((100vw - 1240px) / 2));
    --radius-control: 3px;
    --color-muted: 154 169 191;
}
.guest-skip {
    position: absolute;
    top: -100px;
    left: 22px;
    z-index: 10;
    padding: 12px 18px;
    background: #eaf2ff;
    color: #050914;
}
.guest-skip:focus {
    top: 15px;
}
.guest-header {
    min-height: 90px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    border-bottom: 1px solid #657db72b;
}
.guest-back {
    display: inline-flex;
    align-items: center;
    min-height: 44px;
    gap: 12px;
    color: #acbdd8;
    font-size: 12px;
}
.guest-back:hover {
    color: #c8bfff;
}
.guest-back span {
    color: #9f8cff;
    font-size: 20px;
}
.guest-main {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: calc(100svh - 170px);
    gap: clamp(28px, 6vw, 90px);
    align-items: center;
    padding-block: 38px;
}
.guest-story {
    position: relative;
    min-height: 630px;
    align-self: stretch;
    isolation: isolate;
    display: flex;
    align-items: flex-start;
    overflow: hidden;
}
.guest-photo {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: .3;
    z-index: -2;
    mask-image: linear-gradient(0deg, transparent, #000 25%, #000 75%, transparent);
}
.guest-grid {
    position: absolute;
    inset: 7% 12% 18%;
    border: 1px solid #8063d02b;
    transform: skewY(-8deg);
    z-index: -1;
    pointer-events: none;
}
.guest-story-content {
    padding: 46px 10px;
    position: relative;
    z-index: 1;
}
.system-eyebrow {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 9px;
    letter-spacing: .17em;
    color: #acbce6;
    line-height: 1.7;
}
.guest-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #5ce1b9;
    box-shadow: 0 0 10px #5ce1b966;
    flex-shrink: 0;
}
.guest-headline {
    margin-top: 24px;
    font-size: clamp(36px, 4.2vw, 60px);
    font-weight: 800;
    line-height: 1.08;
    letter-spacing: -.045em;
}
.guest-headline span {
    color: #9f8cff;
}
.guest-description {
    max-width: 350px;
    margin-top: 23px;
    font-size: 14px;
    line-height: 1.9;
    color: #adbed5;
}
.system-path {
    display: flex;
    flex-wrap: wrap;
    gap: 26px;
    margin-top: 28px;
    font-size: 9px;
    letter-spacing: .12em;
    color: #8190a7;
}
.system-path b {
    color: #b2c4e4;
    margin-left: 7px;
    font-weight: 500;
}
.guest-shadow {
    position: absolute;
    width: 95%;
    bottom: 82px;
    left: 0;
    max-height: 240px;
    object-fit: contain;
    opacity: .8;
    pointer-events: none;
}
.guest-story-footer {
    position: absolute;
    inset: auto 10px 8px;
    display: flex;
    gap: 16px;
    align-items: center;
    border-top: 1px solid #657db73b;
    padding-top: 20px;
}
.system-seal {
    display: grid;
    place-items: center;
    border: 1px solid #8d80d5;
    color: #b5a7ff;
    width: 30px;
    height: 34px;
    flex-shrink: 0;
}
.guest-story-footer div>p:last-child {
    font-size: 12px;
    color: #b8c8df;
    margin-top: 7px;
    line-height: 1.6;
}
.guest-form-panel {
    width: 100%;
    border: 1px solid #657db74d;
    background: #080e1ceb;
    position: relative;
}
.guest-form-panel:before {
    content: '';
    position: absolute;
    top: -1px;
    left: 30px;
    width: 64px;
    height: 2px;
    background: #9f8cff;
}
.panel-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 17px 30px;
    border-bottom: 1px solid #657db72b;
    color: #9badcf;
    font-size: 8px;
    letter-spacing: .17em;
}
.panel-signal {
    color: #7faaff;
}
.guest-form-content {
    padding: 30px 34px 20px;
    animation: form-enter .4s ease-out;
}
.guest-form-note {
    border-top: 1px solid #657db72b;
    margin-top: 12px;
    padding: 18px 20px;
    text-align: center;
    font-size: 8px;
    letter-spacing: .12em;
    color: #8190a7;
}
.guest-footer {
    min-height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    color: #8190a7;
    font-size: 10px;
    line-height: 1.6;
}
.guest-footer>span:last-child {
    max-width: 460px;
    text-align: right;
    font-size: 9px;
}
.guest-layout :deep(.ui-eyebrow) {
    color: #a99ae9;
    font-size: 9px;
    letter-spacing: .16em;
}
.guest-layout :deep(.ui-title) {
    color: #eaf2ff;
    font-size: clamp(25px, 2.7vw, 34px);
    font-family: Inter, system-ui, sans-serif;
    font-weight: 700;
    letter-spacing: -.035em;
    line-height: 1.2;
}
.guest-layout :deep(.ui-description) {
    color: #a4b4cd;
    font-size: 13px;
    line-height: 1.8;
}
.guest-layout :deep(.ui-label) {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .08em;
    color: #bdcbe1;
    text-transform: uppercase;
}
.guest-layout :deep(.ui-input) {
    min-height: 50px;
    background: #050a15;
    border: 1px solid #6278a747;
    color: #eaf2ff;
    font-size: 16px;
    box-shadow: none;
}
.guest-layout :deep(.ui-input:focus) {
    border-color: #8b8fe8;
    box-shadow: 0 0 0 3px #7b61ff18;
    outline: none;
}
.guest-layout :deep(.ui-input[aria-invalid='true']) {
    border-color: #ff8b95;
}
.guest-layout :deep(.ui-button) {
    min-height: 52px;
    border: 1px solid #6e86d7;
    background: linear-gradient(115deg, #214b83, #443479);
    color: #f4f6ff;
    font-size: 12px;
    letter-spacing: .05em;
    gap: 12px;
}
.guest-layout :deep(.ui-button:hover:not(:disabled)) {
    background: #34528e;
    transform: none;
    border-color: #a3b9fa;
}
.guest-layout :deep(.ui-link) {
    color: #b7a9ff;
}
.guest-layout :deep(.auth-form) {
    margin-top: 26px;
    gap: 20px;
}
.guest-layout :deep(.auth-switch) {
    margin-top: 24px;
    font-size: 12px;
    color: #9aa9bf;
}
.guest-layout :deep(.auth-options) {
    gap: 10px;
    flex-wrap: wrap;
}
.guest-layout :deep(.auth-options label), .guest-layout :deep(.auth-options a) {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    font-size: 12px;
}
.guest-layout :deep(.system-feedback) {
    min-height: 20px;
    font-size: 11px;
    color: #a7b8d5;
    line-height: 1.6;
    margin-top: 14px;
}
.guest-layout :deep(.system-feedback.is-error) {
    color: #ff9ba5;
}
.guest-layout :deep(.ui-status) {
    border-color: #5ce1b947;
    background: #5ce1b910;
    color: #a3eed7;
    font-size: 12px;
}
@keyframes form-enter {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@media (max-width: 850px) {
    .guest-main {
        gap: 28px;
    }
    .guest-form-content {
        padding: 26px 22px 20px;
    }
    .guest-headline {
        font-size: 38px;
    }
    .system-path {
        gap: 12px;
    }
}
@media (max-width: 700px) {
    .guest-layout {
        padding-inline: 18px;
    }
    .guest-header {
        min-height: 76px;
    }
    .guest-main {
        display: block;
        padding-block: 0 20px;
        min-height: auto;
    }
    .guest-story {
        min-height: 165px;
    }
    .guest-story-content {
        padding: 28px 0;
    }
    .guest-headline {
        font-size: 28px;
        margin-top: 12px;
    }
    .guest-story-content .system-eyebrow {
        font-size: 8px;
    }
    .guest-description, .system-path, .guest-story-footer, .guest-photo {
        display: none;
    }
    .guest-shadow {
        width: 47%;
        height: 125px;
        bottom: 0;
        left: auto;
        right: -20px;
        opacity: .3;
    }
    .guest-grid {
        inset: 15% 0 10% 55%;
    }
    .guest-form-panel {
        max-width: 480px;
        margin-inline: auto;
    }
    .guest-form-content {
        padding: 24px 20px 18px;
    }
    .panel-top {
        padding-inline: 20px;
    }
    .guest-back {
        font-size: 11px;
        gap: 6px;
    }
    .guest-footer {
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        padding-block: 20px;
        gap: 8px;
    }
    .guest-footer>span:last-child {
        text-align: left;
        font-size: 8px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .guest-form-content {
        animation: none;
    }
    .guest-layout :deep(*) {
        transition: none !important;
    }
}
</style>
