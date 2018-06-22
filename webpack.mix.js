let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js')
    .sourceMaps();

mix.js('resources/assets/js/vendor.js', 'public/js')
    .sourceMaps();

mix.sass('resources/assets/sass/app.scss', 'public/css')
    .options({
        processCssUrls: false
    })
    .sourceMaps();

mix.sass('resources/assets/sass/vendor.scss', 'public/css')
    .options({
        processCssUrls: false
    })
    .sourceMaps();

mix.copyDirectory('resources/assets/fonts', 'public/fonts');

if (mix.inProduction()) {
    mix.version();
} else {
    // Development mode
    // get app url from .env file
    mix.browserSync(process.env.APP_URL);
}
