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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                admin: {
                    primary: {
                        900: '#0A2E6F',
                        800: '#0F3A8C',
                        700: '#003FA8',
                        600: '#1A56DB',
                        500: '#2563EB',
                        100: '#E6F0FF',
                    },
                    gray: {
                        900: '#111827',
                        700: '#374151',
                        500: '#6B7280',
                        300: '#D1D5DB',
                        100: '#F3F4F6',
                    },
                    success: '#16A34A',
                    warning: '#F59E0B',
                    danger: '#DC2626',
                    info: '#0EA5E9',
                }
            }
        },
    },

    plugins: [forms],
};
