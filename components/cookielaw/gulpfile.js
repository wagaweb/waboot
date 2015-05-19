'use strict';

var gulp = require('gulp'),
    csso = require('gulp-csso'),
    concat = require('gulp-concat'),
    rename = require("gulp-rename"),
    sourcemaps = require('gulp-sourcemaps'),
    jsmin = require('gulp-jsmin'),
    uglify = require('gulp-uglify'),
    browserify = require('gulp-browserify');

var plugin_slug = "cookielaw";

var paths = {
    scripts: ['./js/**/*.js'],
    js: ['./js/vendor/cookiechoices.js','./js/cookielaw.js'],
    bundlejs: ['./js/bundle.js'],
    scss: './scss/*.scss'
};

gulp.task('cssmin',function(){
    return gulp.src(paths.scss)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(concat(plugin_slug+'.min.css'))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest('./css'));
});

gulp.task('concat', function(){
    return gulp.src(paths.js)
        .pipe(concat('bundle.js'))
        .pipe(gulp.dest('./js'));
});

gulp.task('jsmin', ['concat'] ,function(){
    return gulp.src(paths.bundlejs)
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(rename(plugin_slug+'.min.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./js'));
});

// Rerun the task when a file changes
gulp.task('watch', function() {
    gulp.watch(paths.js, ['jsmin']);
    gulp.watch(paths.scss, ['cssmin']);
});

gulp.task('default', ['jsmin', 'cssmin', 'watch']);