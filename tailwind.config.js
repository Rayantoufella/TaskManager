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
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                mint: {
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    200: '#D1FAE5',
                    300: '#6EE7B7',
                    500: '#10B981',
                    600: '#059669',
                    700: '#047857',
                    900: '#064E3B',
                },
                teal: {
                    400: '#2DD4BF',
                    500: '#14B8A6',
                },
            },
            boxShadow: {
                'mint': '0 4px 20px rgba(16,185,129,0.10), 0 1px 4px rgba(16,185,129,0.06)',
                'mint-lg': '0 24px 80px rgba(16,185,129,0.12), 0 4px 16px rgba(16,185,129,0.08)',
                'glow': '0 0 12px rgba(16,185,129,0.25)',
                'glow-hover': '0 0 24px rgba(16,185,129,0.45)',
            },
            backgroundImage: {
                'mint-grad': 'linear-gradient(135deg,#10B981,#14B8A6)',
                'mint-page': 'linear-gradient(160deg,#ECFDF5 0%,#F0FDF4 40%,#CCFBF1 100%)',
            },
        },
    },

    plugins: [forms],
};
