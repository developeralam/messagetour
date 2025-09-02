/** @type {import('tailwindcss').Config} */
export default {
    content: [
        // You will probably also need these lines
        "./resources/**/**/*.blade.php",
        "./resources/**/**/**/*.blade.php",
        "./resources/**/**/*.js",
        "./app/View/Components/**/**/*.php",
        "./app/Livewire/**/**/*.php",

        // Add mary
        "./vendor/robsontenorio/mary/src/View/Components/**/*.php",
    ],
    theme: {
        extend: {
            fontFamily: {
                poppins: ["Poppins", "sans-serif"],
            },
            colors: {
                default: "#05357c",
                "default-hover": "#FFF8E4",
                "light-yellow": "#FFFDF6",
                sidebar: "#393a3e",
                "sidebar-active": "#e0e1e5",
            },
        },
    },
    daisyui: {
        themes: [
            {
                mytheme: {
                    primary: "#393a3e",

                    secondary: "#9ca3af",

                    accent: "#00ffff",

                    neutral: "#9ca3af",

                    "base-100": "#ffffff",

                    info: "#1f4ab7",

                    success: "#00ff00",

                    warning: "#ffeb00",

                    error: "#ab0b0b",
                },
            },
        ],
    },

    // Add daisyUI
    plugins: [require("daisyui")],
};
