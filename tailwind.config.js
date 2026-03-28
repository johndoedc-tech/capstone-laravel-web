import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Custom color palette
                'primary': {
                    DEFAULT: '#7AAACE',
                    'dark': '#355872',
                    'light': '#9CD5FF',
                    50: '#EBF5FC',
                    100: '#D7EBFA',
                    200: '#BFDFFA',
                    300: '#9CD5FF',
                    400: '#7AAACE',
                    500: '#5B8FB7',
                    600: '#4A7399',
                    700: '#355872',
                    800: '#2A4559',
                    900: '#1F3340',
                },
                'cream': {
                    DEFAULT: '#F7F8F0',
                    50: '#FCFCFB',
                    100: '#F7F8F0',
                    200: '#F0F1E8',
                    300: '#E9EAE0',
                },
                'sage': {
                    light: '#C5D89D',
                    DEFAULT: '#9CAB84',
                    dark: '#89986D',
                    50: '#E8EFD9',
                    100: '#D9E4C2',
                    200: '#C5D89D',
                    300: '#9CAB84',
                    400: '#89986D',
                    500: '#768560',
                    600: '#636E50',
                    700: '#505840',
                },
            },
        },
    },

    plugins: [forms],
};
