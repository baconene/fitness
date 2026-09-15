<template>
    <span class="fitbakes-brand">
        <svg viewBox="0 0 32 38" width="27" height="32" aria-hidden="true" fill="none">
            <path d="M16 2 29 10 25 28 16 36 7 28 3 10Z" stroke="currentColor" stroke-width="1.5"/>
            <path d="m10 26 3-16h10l-1 4h-6l-1 3h5l-1 4h-5l-1 5Z" fill="currentColor"/>
        </svg>
        <span>FITBAKES<span class="brand-dot">.</span>
        </span>
    </span>
</template>

<style scoped>
.fitbakes-brand {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #eaf2ff;
    font-size: 22px;
    font-weight: 850;
    letter-spacing: -.045em;
    white-space: nowrap;
}
.fitbakes-brand svg,.brand-dot {
    color: #9f8cff;
}
@media (max-width:600px) {
    .fitbakes-brand {
        font-size: 18px;
        gap: 7px;
    }
    .fitbakes-brand svg {
        width: 23px;
    }
}
</style>
