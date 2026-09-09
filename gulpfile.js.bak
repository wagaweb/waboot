const pkg = require("./package.json");

const gulp = require("gulp");
const rename = require("gulp-rename");
const sourcemaps = require("gulp-sourcemaps");
const uglify = require("gulp-terser");
const sass = require("gulp-sass")(require("sass"));
const browserify = require("browserify");
const source = require("vinyl-source-stream"); //https://www.npmjs.com/package/vinyl-source-stream
const buffer = require("vinyl-buffer"); //https://www.npmjs.com/package/vinyl-buffer
const babelify = require("babelify");
const postcss = require("gulp-postcss");
const autoprefixer = require("autoprefixer");
const cssnano = require("cssnano");
const merge = require("merge-stream");

const paths = {
    scripts: ["./assets/src/js/**/*.js"],
    main_js: ["./assets/src/js/main.js"],
    bundle_js: ["./assets/dist/js/main.pkg.js"],
    styles: ["./assets/src/sass/**/*.scss"],
    main_style: "./assets/src/sass/main.scss",
    gutenberg: "./assets/src/sass/backend/gutenberg.scss"
};

function compileCss() {
    const processors = [
        // autoprefixer will use "browserlist" from package.json.
        // To see compatibilities: https://browsersl.ist/#q=%22browserslist%22%3A+%5B%0A++%22defaults+and+fully+supports+es6-module%22%2C%0A++%22maintained+node+versions%22%0A%5D
        // General docs: https://github.com/browserslist/browserslist?tab=readme-ov-file#readme
        autoprefixer(),
        cssnano({ zindex: false })
    ];

    const frontend = gulp
        .src(paths.main_style)
        .pipe(sourcemaps.init())
        .pipe(sass())
        .pipe(postcss(processors))
        .pipe(rename("main.min.css"))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest("./assets/dist/css"));

    const backend = gulp
        .src(paths.gutenberg)
        .pipe(sourcemaps.init())
        .pipe(sass())
        .pipe(postcss(processors))
        .pipe(rename("gutenberg.min.css"))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest("./assets/dist/css"));

    return merge(frontend, backend);
}

function compileJsBundle() {
    return browserify(paths.main_js, {
        insertGlobals: true,
        debug: true
    })
        .transform("babelify", { presets: ["@babel/preset-env"] })
        .bundle()
        .pipe(source("main.pkg.js"))
        .pipe(buffer()) //This might be not required, it works even if commented
        .pipe(gulp.dest("./assets/dist/js"));
}

function minifyJs() {
    return gulp
        .src(paths.bundle_js)
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(rename("main.min.js"))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest("./assets/dist/js"));
}

const compileJs = gulp.series(compileJsBundle,minifyJs);

function watch() {
    gulp.watch(paths.scripts, compileJs);
    gulp.watch(paths.styles, compileCss);
}

const build = gulp.series(gulp.parallel(compileJs,compileCss),watch);

exports.compile_css = compileCss;
exports.compile_js = compileJs;
exports.watch = watch;
exports.default = build;
