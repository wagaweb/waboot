let pkg = require('./package.json');

let gulp = require('gulp'),
    rename = require("gulp-rename"),
    sourcemaps = require('gulp-sourcemaps'),
    jsmin = require('gulp-jsmin'),
    uglify = require('gulp-uglify'),
    sass = require('gulp-sass'),
    browserify = require('browserify'),
    source = require('vinyl-source-stream'), //https://www.npmjs.com/package/vinyl-source-stream
    buffer = require('vinyl-buffer'), //https://www.npmjs.com/package/vinyl-buffer
    babelify = require('babelify'),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    cssnano = require('cssnano'),
    runSequence  = require('run-sequence'),
    merge  = require('merge-stream'),
    plumber = require('gulp-plumber'),
    shell = require('gulp-shell'),
    browserSync = require('browser-sync');

let paths = {
    scripts: ['./assets/src/js/**/*.js'],
    main_js: ['./assets/src/js/main.js'],
    adm_main_js: ['./assets/src/js/admin.js'],
    bundle_js: ['./assets/dist/js/main.pkg.js'],
    adm_bundle_js: ['./assets/dist/js/admin.pkg.js'],
    styles: './assets/src/sass/**/*.scss',
    main_style: './assets/src/sass/main.scss'
};

gulp.task('browserSync', function() {
    //@see https://browsersync.io/docs/options
    browserSync({
        proxy: {
            target: 'waboot-child.local', //Change this
        },
        options: {
            reloadDelay: 250
        },
        notify: false
    });
});

/**
 * Compile .scss into main.min.css
 */
gulp.task('compile_css',function(){
    let processors = [
        autoprefixer({browsers: ['last 1 version']}),
        cssnano({ zindex: false })
    ];

    let frontend = gulp.src(paths.main_style)
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(sass())
        .pipe(postcss(processors))
        .pipe(rename('main.min.css'))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest('./assets/dist/css'))
        .pipe(browserSync.reload({ stream: true }));

    return merge(frontend);
});

/**
 * Creates and minimize bundle.js into <pluginslug>.min.js
 */
gulp.task('compile_js', ['browserify'] ,function(){
    let frontend = gulp.src(paths.bundle_js)
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(rename('main.min.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./assets/dist/js'))
        .pipe(browserSync.reload({ stream: true }));

    /*let dashboard = gulp.src(paths.adm_bundle_js)
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(rename('admin.min.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./assets/dist/js'))
        .pipe(browserSync.reload({ stream: true }));*/

    //return merge(frontend,dashboard);
    return merge(frontend);
});

/**
 * Browserify magic!
 */
gulp.task('browserify', function(){
    let frontend = browserify(paths.main_js,{
        insertGlobals : true,
        debug: true
    })
        .transform("babelify", { presets: ['env'] }).bundle()
        .pipe(plumber())
        .pipe(source('main.pkg.js'))
        .pipe(buffer())
        .pipe(gulp.dest('./assets/dist/js'));

    /*let dashboard = browserify(paths.adm_main_js,{
        insertGlobals : true,
        debug: true
    })
        .transform("babelify", { presets: ['env'] }).bundle()
        .pipe(source('adm.pkg.js'))
        .pipe(buffer())
        .pipe(gulp.dest('./assets/dist/js'));*/

    //return merge(frontend,dashboard);
    return merge(frontend);
});

/*
 * Clean build files
 */
gulp.task('clean', shell.task([
    'rm -rf assets/dist'
]));

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
    gulp.watch(paths.styles, ['compile_css']);
});

/**
 * Default task
 */
gulp.task('default', function(callback){
    runSequence(['compile_js', 'compile_css', 'browserSync'], 'watch', callback);
});