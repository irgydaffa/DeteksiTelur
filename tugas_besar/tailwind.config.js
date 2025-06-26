module.exports = {
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
        "./resources/css/**/*.css",
        "./public/**/*.html",
    ],
    theme: {
        extend: {
            colors: {
                "egg-orange": "#F8A057",
                "egg-orange-dark": "#E58B3D",
                "egg-orange-light": "rgba(248, 160, 87, 0.1)",
            },
            fontFamily: {
                sans: [
                    "Instrument Sans",
                    "ui-sans-serif",
                    "system-ui",
                    "sans-serif",
                    "Apple Color Emoji",
                    "Segoe UI Emoji",
                    "Segoe UI Symbol",
                    "Noto Color Emoji",
                ],
            },
        },
    },
    plugins: [],
};
