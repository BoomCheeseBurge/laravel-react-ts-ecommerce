import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    daisyui: {
        themes: [
          {
            light: {
                ...require("daisyui/src/theming/themes")["light"], // Include existing light themes from Daisyui
                // "primary": "#6936F5",
                "primary": "#F4511E", // Override the primary color
              },
            },
          ],
        },

    plugins: [require('daisyui')],
};
