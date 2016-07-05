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
    zip = require('gulp-zip'),
    bower = require('gulp-bower'),
    copy = require('copy'),
    csso = require('gulp-csso'),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    cssnano = require('cssnano'),
    runSequence  = require('run-sequence'),
    wpPot = require('gulp-wp-pot'),
    sort = require('gulp-sort'),
    path = require('path'); //Required by gulp-less

var theme_slug = "waboot";

var paths = {
    builddir: "./builds",
    scripts: ['./assets/src/js/**/*.js'],
    mainjs: ['./assets/src/js/main.js'],
    bundlejs: ['./assets/dist/js/waboot.js'],
    lesses: './assets/src/less/**/*.less',
    mainless: './assets/src/less/waboot.less',
    build: [
        "**/*",
        "!.*" ,
        "!Gruntfile.js",
        "!gulpfile.js",
        "!package.json",
        "!bower.json",
        "!Movefile-sample",
        "!{builds,builds/**}",
        "!{node_modules,node_modules/**}",
        "!{bower_components,bower_components/**}"
    ]
};

/**
 * Compile .less into waboot.min.css
 */
gulp.task('compile_css',function(){
    var processors = [
        autoprefixer({browsers: ['last 1 version']}),
        cssnano()
    ];
    return gulp.src(paths.mainless)
        .pipe(sourcemaps.init())
        .pipe(less())
        .pipe(postcss(processors))
        .pipe(rename(theme_slug+'.min.css'))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest('./assets/dist/css'));
});

/**
 * Creates and minimize bundle.js into <pluginslug>.min.js
 */
gulp.task('compile_js', ['browserify'] ,function(){
    return gulp.src(paths.bundlejs)
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
    return browserify(paths.mainjs,{
        insertGlobals : true,
        debug: true
    })
        .transform("babelify", {presets: ["es2015"]}).bundle()
        .pipe(source('waboot.js'))
        .pipe(buffer()) //This might be not required, it works even if commented
        .pipe(gulp.dest('./assets/dist/js'));
});

/**
 * Creates the theme package
 */
gulp.task('make-package', function(){
    return gulp.src(paths.build)
        .pipe(copy(paths.builddir+"/pkg/"+theme_slug));
});

/**
 * Compress che package directory
 */
gulp.task('archive', function(){
    return gulp.src(paths.builddir+"/pkg/**")
        .pipe(zip(theme_slug+'-'+pkg.version+'.zip'))
        .pipe(gulp.dest("./builds"));
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
 * Copy vendors to destinations
 */
gulp.task('copy-vendors',function() {
    let cb = function(err,files){
        if(err) return console.error(err);
        files.forEach(function(file) {
            console.log("Copied: "+file.relative);
        });
    };

    //@see https://github.com/jonschlinkert/copy/tree/master/examples

    //Copy scripts
    copy([
        'assets/vendor/html5shiv/dist/html5shiv.min.js',
        'assets/vendor/respond/dest/respond.min.js',
        'assets/vendor/bootstrap/dist/js/bootstrap.min.js'
    ],'assets/dist/js',{flatten: true},cb);

    //Copy fonts
    copy([
        '*.*'
    ],'assets/fonts',{cwd: 'assets/vendor/bootstrap/dist/fonts'},cb);
    copy([
        '*.*',
        '!4.4.0'
    ],'assets/fonts',{cwd: 'assets/vendor/fontawesome/fonts'},cb);

    //Copy styles
    copy([
        'font-awesome.min.css'
    ],'assets/dist/css',{cwd: 'assets/vendor/fontawesome/css'},cb);
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
    runSequence('bower-update', 'copy-vendors', ['compile_js', 'compile_css'], callback);
});

/**
 * Gets the theme ready
 */
gulp.task('build', function(callback) {
    runSequence('bower-update', 'copy-vendors',['compile_js', 'compile_css'], 'make-package', 'archive', callback);
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
    runSequence('bower-install', ['compile_js', 'compile_css'], 'watch', callback);
});