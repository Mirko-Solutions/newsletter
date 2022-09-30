const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const concat = require('gulp-concat');

function buildBackendStyles() {
    return gulp.src('Resources/Public/sass/backend/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(concat('module.css').on('error', sass.logError))
        .pipe(gulp.dest('Resources/Public/Styles'));
}

function buildFrontendStyles() {
    return gulp.src('Resources/Public/sass/frontend/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(concat('app.css').on('error', sass.logError))
        .pipe(gulp.dest('Resources/Public/Styles'));
}

exports.buildBackendStyles = buildBackendStyles;
exports.buildFrontendStyles = buildFrontendStyles;
exports.watch = function () {
    gulp.watch('Resources/Public/sass/backend/*.scss', gulp.series(buildBackendStyles));
    gulp.watch('Resources/Public/sass/frontend/*.scss', gulp.series(buildFrontendStyles));
};

exports.build = gulp.series(buildBackendStyles, buildFrontendStyles);

exports.default = gulp.series(buildBackendStyles, buildFrontendStyles);