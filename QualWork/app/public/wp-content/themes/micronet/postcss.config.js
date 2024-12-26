module.exports = {
    syntax: 'postcss-scss',
    plugins: [
        require('postcss-nested-ancestors'),
        require('postcss-nested'),
        require('postcss-import'),
        require('tailwindcss')
    ]
}