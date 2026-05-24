const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                paper:       '#f7f3ec',
                paper2:      '#efe7d8',
                ink:         '#22201c',
                'ink-soft':  '#6b6358',
                seal:        '#c0392b',
                'seal-deep': '#9c2b20',
                line:        '#e3dccd',
            },

            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                serifsc: ['"Noto Serif SC"', 'serif'],
            },

            keyframes: {
                rise: {
                    '0%':   { opacity: '0', transform: 'translateY(14px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },

            animation: {
                rise: 'rise .9s cubic-bezier(.2,.7,.2,1) both',
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};