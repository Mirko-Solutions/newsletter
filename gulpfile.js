const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const webpack = require("webpack-stream");
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const babel = require('gulp-babel');

const dist = 'Resources/Public/dist';

function buildBackendStyles() {
    return gulp.src('Resources/Public/sass/backend/**/*.{sass,scss,css}')
        .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
        .pipe(concat('module.css').on('error', sass.logError))
        .pipe(gulp.dest(dist));
}

function buildFrontendStyles() {
    return gulp.src('Resources/Public/sass/frontend/**/*.{sass,scss,css}')
        .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
        .pipe(concat('app.css').on('error', sass.logError))
        .pipe(gulp.dest(dist));
}

async function buildJs() {
    // return gulp.src("Resources/Public/JavaScript/**/**/*.js")
    //     .pipe(webpack({
    //         mode: 'production',
    //         output: {
    //             filename: 'script.min.js'
    //         },
    //         module: {
    //             rules: [
    //                 {
    //                     test: /\.m?js$/,
    //                     exclude: /(node_modules|bower_components)/,
    //                     use: {
    //                         loader: 'babel-loader',
    //                         options: {
    //                             presets: [['@babel/preset-env']]
    //                         }
    //                     }
    //                 }
    //             ]
    //         }
    //     }))
    //     .pipe(babel())
    //     .pipe(uglify())
    //     .pipe(gulp.dest(dist));

    return gulp.src(
        ['node_modules/chart.js/dist/*.min.js'],
        {base:'node_modules'}
    ).pipe(concat('Libraries.js').on('error', sass.logError))
        .pipe(gulp.dest('Resources/Public/JavaScript/Libraries/'))
}

exports.buildBackendStyles = buildBackendStyles;
exports.buildFrontendStyles = buildFrontendStyles;
exports.buildJs = buildJs;
exports.watch = function () {
    gulp.watch('Resources/Public/sass/backend/*.scss', gulp.series(buildBackendStyles));
    gulp.watch('Resources/Public/sass/frontend/*.scss', gulp.series(buildFrontendStyles));
};

exports.build = gulp.series(buildBackendStyles, buildFrontendStyles, buildJs);

exports.default = gulp.series(buildBackendStyles, buildFrontendStyles, buildJs);