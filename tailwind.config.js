import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['var(--font-ui)'],
                display: ['var(--font-display)'],
            },
            colors: {
                canvas: {
                    DEFAULT: 'rgb(var(--color-canvas) / <alpha-value>)',
                    deep: 'rgb(var(--color-canvas-deep) / <alpha-value>)',
                },
                surface: {
                    DEFAULT: 'rgb(var(--color-surface) / <alpha-value>)',
                    raised: 'rgb(var(--color-surface-raised) / <alpha-value>)',
                },
                edge: 'rgb(var(--color-edge) / <alpha-value>)',
                content: 'rgb(var(--color-content) / <alpha-value>)',
                muted: 'rgb(var(--color-muted) / <alpha-value>)',
                brand: {
                    DEFAULT: 'rgb(var(--color-brand) / <alpha-value>)',
                    hover: 'rgb(var(--color-brand-hover) / <alpha-value>)',
                },
                /** Hunter rank identity, E (steel) through S (crimson). */
                rank: {
                    e: 'rgb(var(--color-rank-e) / <alpha-value>)',
                    d: 'rgb(var(--color-rank-d) / <alpha-value>)',
                    c: 'rgb(var(--color-rank-c) / <alpha-value>)',
                    b: 'rgb(var(--color-rank-b) / <alpha-value>)',
                    a: 'rgb(var(--color-rank-a) / <alpha-value>)',
                    s: 'rgb(var(--color-rank-s) / <alpha-value>)',
                },
                violet: {
                    DEFAULT: 'rgb(var(--color-violet) / <alpha-value>)',
                    light: 'rgb(var(--color-violet-light) / <alpha-value>)',
                },
                'on-brand': 'rgb(var(--color-on-brand) / <alpha-value>)',
                danger: 'rgb(var(--color-danger) / <alpha-value>)',
                success: 'rgb(var(--color-success) / <alpha-value>)',
            },
        },
    },

    plugins: [forms],
};
