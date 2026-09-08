<template>
    <span class="brand-mark">
        <span class="brand-mark-icon" aria-hidden="true">↗</span>
        <span>FITTRACK<span class="text-brand">.</span></span>
    </span>
</template>

<style scoped>
.brand-mark {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: rgb(var(--color-content));
    font-size: 1.4375rem;
    font-weight: 900;
    letter-spacing: -0.04em;
    white-space: nowrap;
}
.brand-mark-icon {
    display: grid;
    place-items: center;
    width: 1.9375rem;
    height: 1.9375rem;
    background: rgb(var(--color-brand));
    color: rgb(var(--color-on-brand));
    font-size: 1.875rem;
    line-height: 1;
    transform: skew(-8deg);
}
@media (max-width: 700px) {
    .brand-mark {
        font-size: 1.1875rem;
    }
    .brand-mark-icon {
        width: 1.625rem;
        height: 1.625rem;
        font-size: 1.5rem;
    }
}
</style>
