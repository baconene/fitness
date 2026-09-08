import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
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
                brand: 'rgb(var(--color-brand) / <alpha-value>)',
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
