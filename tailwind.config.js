import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                slate: colors.teal,
                indigo: colors.emerald,
            },
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'xs': ['0.85rem', { lineHeight: '1.25rem' }],
                'sm': ['0.95rem', { lineHeight: '1.5rem' }],
                'base': ['1.05rem', { lineHeight: '1.75rem' }],
                'lg': ['1.15rem', { lineHeight: '1.75rem' }],
                'xl': ['1.35rem', { lineHeight: '1.75rem' }],
            },
            animation: {
                'gradient-x': 'gradient-x 15s ease infinite',
                'float': 'float 6s ease-in-out infinite',
            },
            keyframes: {
                'gradient-x': {
                    '0%, 100%': {
                        'background-size': '200% 200%',
                        'background-position': 'left center'
                    },
                    '50%': {
                        'background-size': '200% 200%',
                        'background-position': 'right center'
                    },
                },
                'float': {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-20px)' },
                }
            }
        },
    },

    plugins: [forms],
};
