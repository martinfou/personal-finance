import defaultTheme from 'tailwindcss/defaultTheme';
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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
            fontSize: {
                '2xs': ['0.625rem', { lineHeight: '0.875rem', letterSpacing: '0.08em' }],
            },
            colors: {
                ink: {
                    50: '#f7f6f4',
                    100: '#eceae6',
                    200: '#d8d4cd',
                    300: '#b8b2a8',
                    400: '#8f8779',
                    500: '#6b6358',
                    600: '#524b42',
                    700: '#3f3933',
                    800: '#2c2723',
                    900: '#1c1917',
                    950: '#0f0e0d',
                },
                brand: {
                    50: '#f0f7f6',
                    100: '#d9ebe8',
                    200: '#b3d7d1',
                    300: '#85bdb4',
                    400: '#5a9d93',
                    500: '#3f8278',
                    600: '#326a62',
                    700: '#2b574f',
                    800: '#264743',
                    900: '#223c39',
                    950: '#102220',
                },
                surface: {
                    50: '#faf9f7',
                    75: '#f5f3f0',
                    100: '#ece9e4',
                    150: '#e2ded7',
                    200: '#d6d0c7',
                },
            },
            boxShadow: {
                soft: '0 1px 2px rgba(28, 25, 23, 0.04), 0 8px 24px rgba(28, 25, 23, 0.06)',
                lift: '0 4px 12px rgba(28, 25, 23, 0.08), 0 16px 40px rgba(28, 25, 23, 0.06)',
            },
            spacing: {
                4.5: '1.125rem',
                5.5: '1.375rem',
                7.5: '1.875rem',
                13: '3.25rem',
                15: '3.75rem',
            },
        },
    },

    plugins: [forms],
};
