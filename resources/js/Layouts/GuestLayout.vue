<script setup>
import BrandMark from '@/Components/BrandMark.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    eyebrow: { type: String, default: 'EVERY BODY. EVERY BEGINNING.' },
    headline: { type: String, default: 'YOUR NEXT' },
    accent: { type: String, default: 'CHAPTER.' },
    description: {
        type: String,
        default:
            'A little stronger. A little further. A little more you. Your journey is worth showing up for.',
    },
});
</script>

<template>
    <div class="guest-layout">
        <a href="#auth-content" class="guest-skip ui-button">Skip to form</a>
        <header class="guest-header">
            <Link href="/" aria-label="FitTrack home"><BrandMark /></Link>
            <Link href="/" class="guest-back"
                ><span aria-hidden="true">↖</span> Back to the movement</Link
            >
        </header>
        <main id="auth-content" class="guest-main">
            <aside class="guest-story" aria-label="A little motivation">
                <img
                    src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=1200&q=85"
                    alt=""
                    class="guest-photo"
                    fetchpriority="high"
                />
                <div class="guest-photo-shade"></div>
                <div class="guest-story-content">
                    <p class="ui-eyebrow">
                        <span class="guest-dot"></span>{{ eyebrow }}
                    </p>
                    <p class="guest-headline">
                        {{ headline }}<br /><span>{{ accent }}</span>
                    </p>
                    <p class="guest-description">{{ description }}</p>
                </div>
                <div class="guest-reminder">
                    <span aria-hidden="true">✳</span>
                    <p>PROGRESS OVER<br /><strong>PERFECTION.</strong></p>
                </div>
                <div class="guest-story-footer">
                    <span class="guest-check" aria-hidden="true">✓</span>
                    <p>
                        Showing up is a win.<small
                            >LET'S MAKE TODAY COUNT.</small
                        >
                    </p>
                    <span aria-hidden="true">↗</span>
                </div>
            </aside>
            <section
                class="guest-form-panel"
                aria-label="Your FitTrack account"
            >
                <div class="guest-form-content"><slot /></div>
                <p class="guest-form-note">
                    <span aria-hidden="true">✳</span> Made for your kind of
                    strong.
                </p>
            </section>
        </main>
        <footer class="guest-footer">
            <span>© {{ new Date().getFullYear() }} FitTrack.</span
            ><span>ONE STEP. THEN THE NEXT.</span>
        </footer>
    </div>
</template>

<style scoped>
.guest-layout {
    min-height: 100svh;
    width: min(1440px, 100%);
    margin-inline: auto;
    padding-inline: clamp(20px, 4vw, 64px);
}
.guest-skip {
    position: absolute;
    top: -100px;
    left: 20px;
    z-index: 10;
}
.guest-skip:focus {
    top: 15px;
}
.guest-header {
    min-height: 96px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}
.guest-back {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    color: rgb(var(--color-muted));
    font-size: 12px;
}
.guest-back:hover {
    color: rgb(var(--color-brand));
}
.guest-back span {
    font-size: 20px;
    color: rgb(var(--color-brand));
}
.guest-main {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: calc(100svh - 172px);
    gap: clamp(40px, 6vw, 96px);
    align-items: stretch;
}
.guest-story {
    position: relative;
    min-height: 600px;
    border-radius: var(--radius-panel);
    background: rgb(var(--color-surface));
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
    isolation: isolate;
}
.guest-photo {
    position: absolute;
    inset: 0;
    height: 100%;
    width: 100%;
    object-fit: cover;
    object-position: 57% center;
    z-index: -2;
}
.guest-photo-shade {
    position: absolute;
    inset: 0;
    z-index: -1;
    background:
        linear-gradient(
            0deg,
            rgb(var(--color-canvas) / 0.96),
            rgb(var(--color-canvas) / 0.3) 80%
        ),
        linear-gradient(90deg, rgb(var(--color-canvas) / 0.7), transparent);
}
.guest-story-content {
    padding: 120px clamp(28px, 4vw, 56px) 110px;
}
.guest-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgb(var(--color-brand));
    flex-shrink: 0;
}
.guest-headline {
    margin-top: 28px;
    font-size: clamp(44px, 4.6vw, 68px);
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.05em;
    overflow-wrap: anywhere;
}
.guest-headline span {
    color: rgb(var(--color-brand));
}
.guest-description {
    max-width: 320px;
    margin-top: 24px;
    font-size: 14px;
    line-height: 1.9;
    color: rgb(var(--color-content) / 0.78);
}
.guest-reminder {
    position: absolute;
    top: 28px;
    right: 24px;
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 14px 17px;
    background: rgb(var(--color-brand));
    color: rgb(var(--color-on-brand));
    transform: rotate(6deg);
}
.guest-reminder > span {
    font-size: 32px;
}
.guest-reminder p {
    font-size: 9px;
    letter-spacing: 1px;
    line-height: 1.7;
}
.guest-story-footer {
    position: absolute;
    inset: auto 32px 30px;
    padding-top: 22px;
    border-top: 1px solid rgb(var(--color-content) / 0.2);
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
}
.guest-check {
    display: grid;
    place-items: center;
    border: 1px solid rgb(var(--color-brand) / 0.4);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    color: rgb(var(--color-brand));
}
.guest-story-footer small {
    display: block;
    margin-top: 5px;
    letter-spacing: 1.4px;
    font-size: 8px;
    color: rgb(var(--color-muted));
}
.guest-story-footer > span:last-child {
    margin-left: auto;
    font-size: 26px;
    color: rgb(var(--color-brand));
}
.guest-form-panel {
    align-self: center;
    width: 100%;
    max-width: 440px;
    padding: 40px 0;
}
.guest-form-content {
    animation: form-enter 0.45s ease-out;
}
.guest-form-note {
    display: flex;
    justify-content: center;
    gap: 9px;
    align-items: center;
    margin-top: 30px;
    color: rgb(var(--color-muted));
    font-size: 10px;
}
.guest-form-note span {
    color: rgb(var(--color-brand));
    font-size: 16px;
}
.guest-footer {
    min-height: 76px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    font-size: 10px;
    color: rgb(var(--color-muted));
}
.guest-footer > span:last-child {
    letter-spacing: 1.5px;
    font-size: 8px;
}
@keyframes form-enter {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@media (max-width: 900px) {
    .guest-main {
        gap: 32px;
    }
    .guest-story-content {
        padding-inline: 26px;
    }
    .guest-headline {
        font-size: 45px;
    }
    .guest-story-footer {
        left: 24px;
        right: 24px;
    }
}
@media (max-width: 700px) {
    .guest-header {
        min-height: 80px;
    }
    .guest-back {
        font-size: 0;
        gap: 0;
    }
    .guest-back span {
        font-size: 23px;
    }
    .guest-main {
        display: block;
        min-height: auto;
    }
    .guest-story {
        min-height: 190px;
    }
    .guest-story-content {
        padding: 28px;
    }
    .guest-story-content .ui-eyebrow {
        max-width: 70%;
        font-size: 8px;
    }
    .guest-headline {
        font-size: 38px;
        margin-top: 14px;
        max-width: 80%;
    }
    .guest-description,
    .guest-story-footer {
        display: none;
    }
    .guest-reminder {
        top: 54px;
        right: 15px;
        padding: 10px;
        gap: 7px;
    }
    .guest-reminder > span {
        font-size: 23px;
    }
    .guest-reminder p {
        font-size: 7px;
    }
    .guest-form-panel {
        margin-inline: auto;
        padding-block: 36px 20px;
    }
    .guest-footer {
        min-height: 65px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .guest-form-content {
        animation: none;
    }
}
</style>
