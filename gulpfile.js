var pkg = require("./package.json");

var gulp = require("gulp"),
  rename = require("gulp-rename"),
  sourcemaps = require("gulp-sourcemaps"),
  uglify = require("gulp-uglify"),
  //sass = require("gulp-sass"),
  sass = require("gulp-sass")(require("node-sass")),
  browserify = require("browserify"),
  source = require("vinyl-source-stream"), //https://www.npmjs.com/package/vinyl-source-stream
  buffer = require("vinyl-buffer"), //https://www.npmjs.com/package/vinyl-buffer
  babelify = require("babelify"),
  postcss = require("gulp-postcss"),
  autoprefixer = require("autoprefixer"),
  cssnano = require("cssnano"),
  merge = require("merge-stream");

var paths = {
  scripts: ["./assets/src/js/**/*.js"],
  main_js: ["./assets/src/js/main.js"],
  bundle_js: ["./assets/dist/js/main.pkg.js"],
  styles: ["./assets/src/sass/**/*.scss"],
  main_style: "./assets/src/sass/main.scss",
  admin_style: "./assets/src/sass/backend/gutenberg.scss",
};

function compileCss() {
  var processors = [
    autoprefixer({ browsers: ["last 1 version"] }),
    cssnano({ zindex: false }),
  ];

  var frontend = gulp
    .src(paths.main_style)
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(postcss(processors))
    .pipe(rename("main.min.css"))
    .pipe(sourcemaps.write("."))
    .pipe(gulp.dest("./assets/dist/css"));

  var backend = gulp
    .src(paths.admin_style)
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
    debug: true,
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

var compileJs = gulp.series(compileJsBundle, minifyJs);

function watch() {
  gulp.watch(paths.scripts, compileJs);
  gulp.watch(paths.styles, compileCss);
}

var build = gulp.series(gulp.parallel(compileJs, compileCss), watch);

exports.compile_css = compileCss;
exports.compile_js = compileJs;
exports.watch = watch;
exports.default = build;
