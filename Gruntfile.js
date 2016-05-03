module.exports = function(grunt) {

    // load all tasks
    require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});
    var fs = require('fs');

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev:{
                options:{},
                files:{
                    'assets/dist/css/waboot.css': (function(){
                        /*if(fs.existsSync('assets/src/less/tmp_waboot.less')){
                            return 'assets/src/less/tmp_waboot.less';
                        }*/
                        return 'assets/src/less/waboot.less';
                    })(),
                    'assets/dist/css/bootstrap-pagebuilder.css': 'assets/src/less/waboot/admin/bootstrap-pagebuilder.less',
                    'assets/dist/css/theme-options.css': 'assets/src/less/waboot/admin/theme-options-gui.less',
                    'assets/dist/css/admin.css': 'assets/src/less/waboot/admin/waboot-admin.less'
                }
            }
        },
        postcss: {
            options: {
                map: {
                    inline: false,
                    annotation: 'assets/dist/css/'
                },
                processors: [
                    require('pixrem')(),
                    require('autoprefixer')({browsers: 'last 2 versions'}),
                    require('cssnano')()
                ]
            },
            dist: {
                src: 'assets/dist/css/waboot.css'
            }
        },
        jshint : {
            all : ['assets/src/js/**/*.js','!assets/src/js/vendor/offcanvas.js']
        },
        browserify: {
            dist: {
                src: ['assets/src/js/main.js'],
                dest: 'assets/dist/js/waboot.js'
            }
        },
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n' // the banner is inserted at the top of the output
            },
            dist: {
                files: {
                    'assets/dist/js/waboot.min.js': ['assets/dist/js/waboot.js']
                }
            }
        },
        copy:{
            all:{
                files:[
                    '<%= copy.bootstrap_styles.files %>',
                    '<%= copy.vendors.files %>'
                ]
            },
            vendors:{
                files:[
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/vendor/html5shiv/dist",
                        src: "html5shiv.min.js",
                        dest: "assets/dist/js"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/vendor/respond/dest",
                        src: "respond.min.js",
                        dest: "assets/dist/js"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/vendor/fontawesome",
                        src: "css/font-awesome.min.css",
                        dest: "assets/dist/css"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/vendor/fontawesome",
                        src: "fonts/*",
                        dest: "assets/fonts"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/vendor/bootstrap/dist",
                        src: "js/bootstrap.min.js",
                        dest: "assets/dist/js/"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/vendor/bootstrap/dist",
                        src: "fonts/*",
                        dest: "assets/fonts"
                    }
                ]
            },
            dist:{
                files:[
                    {
                        expand: true,
                        cwd: "./",
                        src: [
                            "**/*",
                            "!.*",
                            "!builds/**",
                            "!components/**/node_modules/**",
                            "!components/**/bower_components/**",
                            "!node_modules/**",
                            "!assets/vendor/**",
                            "!assets/cache/**",
                            "!Gruntfile.js",
                            "!package.json",
                            "!.jshintrc",
                            "!.bowerrc",
                            "!bower.json",
                            "!Movefile-sample",
                            "!Movefile",
                            "!_bak/**",
                            "assets/vendor/bootstrap/less"
                        ],
                        dest: "builds/waboot-<%= pkg.version %>/"
                    }
                ]
            }
        },
        pot:{
            options:{
                text_domain: 'waboot',
                dest: 'languages/',
                keywords: [
                    '__:1',
                    '_e:1',
                    '_x:1,2c',
                    'esc_html__:1',
                    'esc_html_e:1',
                    'esc_html_x:1,2c',
                    'esc_attr__:1',
                    'esc_attr_e:1',
                    'esc_attr_x:1,2c',
                    '_ex:1,2c',
                    '_n:1,2',
                    '_nx:1,2,4c',
                    '_n_noop:1,2',
                    '_nx_noop:1,2,3c'
                ]
            },
            files:{
                src: ['*.php','components/**/*.php','inc/**/*.php','templates/**/*.php'],
                expand: true
            }
        },
        compress:{
            build:{
                options:{
                    archive: "builds/waboot-<%= pkg.version %>.zip"
                },
                files:[
                    {
                        expand: true,
                        cwd: "./",
                        src: '<%= copy.dist.files.0.src %>',
                        dest: "waboot/"
                    }
                ]
            }
        },
        watch: {
            less: {
                files: 'assets/src/less/*.less',
                tasks: ['less:dev']
            },
            scripts:{
                files: ['<%= jshint.all %>'],
                tasks: ['jsmin']
            }
        }
    });

    /*
     *   Register tasks
     */

    //Default task
    grunt.registerTask('default', ['setup','watch']);

    //Setup task
    grunt.registerTask('setup', ['bower-install','bower-update','copy:vendors','compile_less','compile_js']);

    //Concat, beautify and minify js
    grunt.registerTask('compile_js', ['browserify:dist','uglify']);

    //Styles
    grunt.registerTask('compile_less', ['less:dev','postcss']);

    //Build task
    grunt.registerTask('build', ['bower-update','copy:vendors','compile_less','compile_js','pot','compress:build']);

    //Runs bower install
    grunt.registerTask('bower-install', function() {
        var exec = require('child_process').exec;
        var cb = this.async();
        exec('bower install', function(err, stdout, stderr) {
            console.log(stdout);
            cb();
        });
    });

    //Runs bower update
    grunt.registerTask('bower-update', function() {
        var exec = require('child_process').exec;
        var cb = this.async();
        exec('bower update', function(err, stdout, stderr) {
            console.log(stdout);
            cb();
        });
    });
};