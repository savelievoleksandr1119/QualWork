const _ = require("lodash");
//const theme = require('./theme.json');
const tailpress = require("@jeffreyvr/tailwindcss-tailpress");

module.exports = {
    mode: 'jit',
    purge: {
        content: [
            './*/*.php',
            './**/*.php',
            './assets/css/*.css',
            './assets/js/*.js',
            './safelist.txt'
        ],
    },
    theme: {
        screens: {
            'xs': '480px',
            'sm': '640px',
            'md': '768px',
            'lg': '1024px',
            'xl': '1140px'
        }
    },
    plugins: [
        tailpress.tailwind
    ]
};
