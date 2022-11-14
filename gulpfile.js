const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const concat = require('gulp-concat');

const dist = 'Resources/Public/dist';

function buildBackendStyles() {
    return gulp.src('Resources/Public/sass/backend/**/*.{sass,scss,css}')
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(concat('module.css').on('error', sass.logError))
        .pipe(gulp.dest(dist));
}

function buildFrontendStyles() {
    return gulp.src('Resources/Public/sass/frontend/**/*.{sass,scss,css}')
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(concat('app.css').on('error', sass.logError))
        .pipe(gulp.dest(dist));
}

async function buildJs() {
    return gulp.src(
        ['node_modules/chart.js/dist/*.min.js'],
        {base: 'node_modules'}
    ).pipe(concat('Libraries.js'))
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