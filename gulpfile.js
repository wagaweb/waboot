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
    zip = require('gulp-zip'),
    bower = require('gulp-bower'),
    copy = require('copy'),
    gcopy = require('gulp-copy'),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    cssnano = require('cssnano'),
    runSequence  = require('run-sequence'),
    wpPot = require('gulp-wp-pot'),
    sort = require('gulp-sort'),
    merge  = require('merge-stream'),
    shell = require('gulp-shell');

let theme_slug = "waboot";

let paths = {
    builddir: "./builds",
    scripts: ['./assets/src/js/**/*.js'],
    mainjs: ['./assets/src/js/main.js'],
    admin_mainjs: ['./assets/src/js/main-dashboard.js'],
    bundlejs: ['./assets/dist/js/waboot.pkg.js'],
    admin_bundlejs: ['./assets/dist/js/waboot-dashboard.pkg.js'],
    scsses: './assets/src/sass/**/*.scss',
    main_scss: './assets/src/sass/waboot.scss',
    main_admin_scss: './assets/src/sass/waboot-admin.scss',
    tinymce_admin_scss: './assets/src/sass/admin/tinymce.scss',
    gutenberg_admin_scss: './assets/src/sass/admin/gutenberg.scss',
    build: [
        "**/*",
        "./components/.gitkeep",
        "!./components/**",
        "!.*" ,
        "!./gulpfile.js",
        "!./package.json",
        "!./package-lock.json",
        "!phpunit.xml",
        "!phpunit-wp.xml",
        "!composer.json",
        "!composer.lock",
        "!bower.json",
        "!Movefile-sample",
        "!yarn.lock",
        "!*.log",
        "!{tests,tests/**}",
        "!{vendor,vendor/**}",
        "!{builds,builds/**}",
        "!{node_modules,node_modules/**}",
        "!{bower_components,bower_components/**}",
        "!assets/cache/*",
        "!{waboot-child/node_modules,waboot-child/node_modules/**}"
    ]
};

let available_components = [
    'admin_tweaks',
    'blog_timeline',
    'blog_masonry',
    'bootstrap',
    'breadcrumb',
    'footer_flex',
    'header_splitted_menu',
    'header_flex',
    'image_modal',
    'legal_data',
    'navbar_vertical',
    'sample',
    'topNavWrapper',
    'woocommerce_standard',
    'style',
    'font_awesome'
];

/**
 * Compile .less into waboot.min.css
 */
gulp.task('compile_css',function(){
    let processors = [
        autoprefixer({browsers: ['last 1 version']}),
        cssnano({ zindex: false })
    ];

    let frontend = gulp.src(paths.main_scss)
        .pipe(sourcemaps.init())
        .pipe(sass({includePaths: ["assets/vendor/bootstrap-sass/assets/stylesheets"]}).on('error', sass.logError))
        .pipe(postcss(processors))
        .pipe(rename(theme_slug+'.min.css'))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest('./assets/dist/css'));

    let backend = gulp.src(paths.main_admin_scss)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(postcss(processors))
        .pipe(rename(theme_slug+'-admin.min.css'))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest('./assets/dist/css'));

    let tinymce = gulp.src(paths.tinymce_admin_scss)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(postcss(processors))
        .pipe(rename(theme_slug+'-admin-tinymce.min.css'))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest('./assets/dist/css'));
    
    let gutenberg = gulp.src(paths.gutenberg_admin_scss)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(postcss(processors))
        .pipe(rename(theme_slug+'-admin-gutenberg.min.css'))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest('./assets/dist/css'));

    //Components

    let comp_woocommerce_standard = gulp.src("./components/woocommerce_standard/assets/src/sass/woocommerce-standard.scss")
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(postcss(processors))
        .pipe(rename('woocommerce-standard.min.css'))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest('./components/woocommerce_standard/assets/dist/css'));

    return merge(frontend,backend,tinymce,gutenberg,comp_woocommerce_standard);
});

/**
 * Creates and minimize bundle.js into <pluginslug>.min.js
 */
gulp.task('compile_js', ['browserify'] ,function(){
    let backend = gulp.src(paths.admin_bundlejs)
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(rename(theme_slug+'-dashboard.min.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./assets/dist/js'));

    return merge(backend);
});

/**
 * Browserify magic! Creates waboot.js
 */
gulp.task('browserify', function(){
    let backend = browserify(paths.admin_mainjs,{
        insertGlobals : true,
        debug: true
    })
        .transform("babelify", {presets: ["env"]}).bundle()
        .pipe(source(theme_slug+'-dashboard.pkg.js'))
        .pipe(buffer()) //This might be not required, it works even if commented
        .pipe(gulp.dest('./assets/dist/js'));

    return merge(backend);
});

/**
 * Creates the theme package
 */
gulp.task('make-package', function(){
    let del = require('del');
    del(paths.builddir+'/pkg');
    return gulp.src(paths.build)
        .pipe(gcopy(paths.builddir+"/pkg/"+theme_slug));
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
        'bower_components/html5shiv/dist/html5shiv.min.js',
        'bower_components/respond/dest/respond.min.js'
    ],'assets/dist/js',{flatten: true},cb);

    //Copy fa fonts
    copy([
        'bower_components/fontawesome/webfonts/*.*',
    ],'components/font_awesome/assets/dist/webfonts',{flatten: true},cb);

    //Copy fa styles
    copy([
        'bower_components/fontawesome/css/fontawesome.min.css',
        'bower_components/fontawesome/css/all.min.css',
        'bower_components/fontawesome/css/brands.min.css',
        'bower_components/fontawesome/css/regular.min.css',
        'bower_components/fontawesome/css/solid.min.css',
    ],'components/font_awesome/assets/dist/css',{flatten: true},cb);
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
    return bower({ cmd: 'update' });
});

/**
 * Initial setup
 */
gulp.task('setup', function(callback) {
    runSequence('bower-update', 'copy-vendors', ['compile_js', 'compile_css'], callback);
});

/**
 * Gets the theme ready
 */
gulp.task('build', ['clean'], function(callback) {
    runSequence('bower-update', 'copy-vendors',['compile_js', 'compile_css'], 'make-package', 'archive', callback);
});

/*
 * Clean builds
 */
gulp.task('clean', shell.task([
    'rm -rf builds/pkg',
    'rm -rf builds/components',
    'rm -rf waboot-child/node_modules'
]));

/**
 * Rerun the task when a file changes
 */
gulp.task('watch', function() {
    gulp.watch(paths.scripts, ['compile_js']);
    gulp.watch(paths.scsses, ['compile_css']);
});

/**
 * Default task
 */
gulp.task('default', function(callback){
    runSequence('bower-install', ['compile_js', 'compile_css'], 'watch', callback);
});

/*
 * COMPONENTS: BEGIN
 */

/**
 * Create directories for components
 */
gulp.task('components-add-dirs', function(){
    let exec = require('child_process').exec;
    let components = available_components;
    for(var i = 0, len = components.length; i < len; i++){
        console.log("*** Exec mkdir "+components[i]);
        exec('mkdir components/'+components[i], function(err, stdout, stderr) {
            if(err){
                console.log(stderr);
                return;
            }
        });
    }
});

/**
 * Create directories for components
 */
gulp.task('components-pull-remotes', function(){
    let exec = require('child_process').exec;
    let components = available_components;
    for(let i = 0, len = components.length; i < len; i++){
        console.log("*** Pulling "+components[i]);
        exec('cd components/'+components[i]+' && git clone git@github.com:wagaweb/waboot-component-'+components[i]+'.git .', function(err, stdout, stderr) {
            if(err){
                console.log(stderr);
                return;
            }
        });
    }
});

/**
 * Default task
 */
gulp.task('setup-components', function(callback){
    runSequence('components-add-dirs', 'components-pull-remotes', callback);
});

/**
 * Builds all components
 */
gulp.task('build-components', function(callback){
    let components = available_components;
    let pkg_tasks = [];
    let zip_tasks = [];
    let fs = require('fs');
    let del = require('del');
    for(let i = 0, len = components.length; i < len; i++){
        let current_directory = './components/'+components[i];
        console.log(current_directory+'/.version ...');
        try{
            fs.lstatSync(current_directory+'/.version').isFile();
            let current_version = fs.readFileSync(current_directory+'/.version', 'utf8', function(err,data){
                if(err){
                    console.log(err);
                    return false;
                }
                return data;
            });
            if(current_version){
                let current_pkg_name = components[i];
                del.sync('./builds/components/'+current_pkg_name);
                let current_pkg_task = gulp.src(current_directory+'/**/*').pipe(gulp.dest('./builds/components/'+current_pkg_name));
                pkg_tasks.push(current_pkg_task);
            }
        }catch(e){}
    }
    return merge(...pkg_tasks);
});

gulp.task('zip-components', ['build-components'], function(callback){
    let components = available_components;
    let zip_tasks = [];
    let fs = require('fs');
    let del = require('del');
    for(let i = 0, len = components.length; i < len; i++){
        let current_directory = './components/'+components[i];
        try{
            fs.lstatSync(current_directory+'/.version').isFile();
            let current_version = fs.readFileSync(current_directory+'/.version', 'utf8', function(err,data){
                if(err){
                    console.log(err);
                    return false;
                }
                return data;
            });
            if(current_version){
                let current_zip_filename = components[i]+'-'+current_version+'.zip';
                let current_pkg_name = components[i];
                del.sync('./builds/components/'+current_zip_filename);
                let current_zip_task = gulp.src('./builds/components/'+current_pkg_name+'/**/*', {base: './builds/components/'}).pipe(zip(current_zip_filename)).pipe(gulp.dest('./builds/components'));
                zip_tasks.push(current_zip_task);
            }
        }catch(e){}
    }
    return merge(...zip_tasks);
});

/*
 * COMPONENTS: END
 */