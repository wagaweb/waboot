var pkg = require('./package.json');

var gulp = require('gulp'),
    concat = require('gulp-concat'),
    rename = require("gulp-rename"),
    sourcemaps = require('gulp-sourcemaps'),
    jsmin = require('gulp-jsmin'),
    uglify = require('gulp-uglify'),
    sass = require('gulp-sass'),
    less = require('gulp-less'),
    browserify = require('browserify'),
    source = require('vinyl-source-stream'), //https://www.npmjs.com/package/vinyl-source-stream
    buffer = require('vinyl-buffer'), //https://www.npmjs.com/package/vinyl-buffer
    babelify = require('babelify'),
    bower = require('gulp-bower'),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    cssnano = require('cssnano'),
    runSequence  = require('run-sequence'),
    wpPot = require('gulp-wp-pot'),
    sort = require('gulp-sort'),
    merge  = require('merge-stream'),
    path = require('path'); //Required by gulp-less

var theme_slug = "waboot-child";

var paths = {
    scripts: ['./assets/src/js/**/*.js'],
    main_js: ['./assets/src/js/main.js'],
    bundle_js: ['./assets/dist/js/waboot.js'],
    styles: './assets/src/less/**/*.less',
    main_style: './assets/src/less/theme.less'
};

/**
 * Compile .less into waboot.min.css
 */
gulp.task('compile_css',function(){
    var processors = [
        autoprefixer({browsers: ['last 1 version']}),
        cssnano()
    ];

    var frontend = gulp.src(paths.main_style)
        .pipe(sourcemaps.init())
        .pipe(less())
        .pipe(postcss(processors))
        .pipe(rename(theme_slug+'.min.css'))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest('./assets/dist/css'));

    var wp_style = gulp.src("./style.css")
        .pipe(postcss(processors))
        .pipe(gulp.dest('./'));

    return merge(frontend,wp_style);
});

/**
 * Creates and minimize bundle.js into <pluginslug>.min.js
 */
gulp.task('compile_js', ['browserify'] ,function(){
    return gulp.src(paths.bundle_js)
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(rename(theme_slug+'.min.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./assets/dist/js'));
});

/**
 * Browserify magic! Creates waboot.js
 */
gulp.task('browserify', function(){
    return browserify(paths.main_js,{
        insertGlobals : true,
        debug: true
    })
        .transform("babelify", {presets: ["es2015"]}).bundle()
        .pipe(source(theme_slug+'.js'))
        .pipe(buffer()) //This might be not required, it works even if commented
        .pipe(gulp.dest('./assets/dist/js'));
});

/**
 * Make the pot file
 */
gulp.task('make-pot', function () {
    return gulp.src(['*.php', 'src/**/*.php'])
        .pipe(sort())
        .pipe(wpPot( {
            domain: theme_slug,
            destFile: theme_slug+'.pot',
            team: 'Waga <info@waga.it>'
        } ))
        .pipe(gulp.dest('languages/'));
});

/**
 * Bower vendors Install
 */
gulp.task('bower-install',function(){
    return bower();
});

/**
 * Bower Update
 */
gulp.task('bower-update',function(){
    return bower({cmd: 'update'});
});

/**
 * Runs a build
 */
gulp.task('setup', function(callback) {
    runSequence(['compile_js', 'compile_css'], callback);
});

/**
 * Rerun the task when a file changes
 */
gulp.task('watch', function() {
    gulp.watch(paths.scripts, ['compile_js']);
    gulp.watch(paths.lesses, ['compile_css']);
});

/**
 * Default task
 */
gulp.task('default', function(callback){
    runSequence(['compile_js', 'compile_css'], 'watch', callback);
});