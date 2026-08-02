import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/livewire/livewire/src/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    DEFAULT: '#2196F3',
                    50: '#EBF5FE',
                    100: '#D5EAFD',
                    200: '#AAD4FB',
                    300: '#7FBDF8',
                    400: '#55A7F6',
                    500: '#2196F3',
                    600: '#0D7BD2',
                    700: '#0A61A8',
                    800: '#07487E',
                    900: '#052F54',
                },
                gold: {
                    DEFAULT: '#D4A017',
                    50: '#FBF5E3',
                    100: '#F5E8BC',
                    200: '#EED98F',
                    300: '#E7CA62',
                    400: '#DDB43C',
                    500: '#D4A017',
                    600: '#A87E12',
                    700: '#7C5C0D',
                    800: '#503A08',
                    900: '#241A04',
                },
            },
            keyframes: {
                marquee: {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-100%)' },
                },
            },
            animation: {
                marquee: 'marquee 30s linear infinite',
            },
        },
    },
    plugins: [forms, typography],
};
