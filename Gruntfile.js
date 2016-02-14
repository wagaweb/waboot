module.exports = function(grunt) {

    // load all tasks
    require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});
    var fs = require('fs');

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev:{
                options:{
                },
                files:{
                    'assets/css/waboot.css': (function(){
                        if(fs.existsSync('assets/src/less/tmp_waboot.less')){
                            return 'assets/src/less/tmp_waboot.less';
                        }
                        return 'assets/src/less/waboot.less';
                    })(),
                    'assets/css/bootstrap-pagebuilder.css': 'src/less/bootstrap-pagebuilder.less',
                    'assets/css/theme-options.css': 'assets/src/less/theme-options-gui.less',
                    'assets/css/admin.css': 'assets/src/less/waboot-admin.less'
                }
            },
            production:{
                options:{
                    compress: true
                },
                files: ['<%= less.dev.files %>']
            },
            waboot:{
                options:{
                    compress: true,
                    sourceMap: true,
                    sourceMapFilename: "assets/dist/css/waboot.css.map",
                    sourceMapBasepath: "assets/dist/css"
                },
                files: {
                    'assets/css/waboot.css': (function(){
                        if(fs.existsSync('assets/src/less/tmp_waboot.less')){
                            return 'assets/src/less/tmp_waboot.less';
                        }
                        return 'assets/src/less/waboot.less';
                    })()
                }                
            }
        },
        jshint : {
            all : ['assets/src/js/**/*.js','!assets/src/js/waboot.js','!assets/src/js/vendor/offcanvas.js']
        },
        browserify: {
            dist: {
                src: ['assets/src/js/main.js'],
                dest: 'assets/src/js/waboot.js'
            }
        },
        "jsbeautifier" : {
            files : ['assets/src/js/main.js','assets/src/js/controllers/*.js','sassets/rc/js/views/*.js'],
            options : {
            }
        },
        uglify: {
            options: {
                // the banner is inserted at the top of the output
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    'assets/dist/js/waboot.min.js': ['assets/src/js/waboot.js']
                }
            }
        },
        copy:{
            all:{
                files:[
                    '<%= copy.bootstrap.files %>',
                    '<%= copy.bower_components.files %>'
                ]
            },
            bower_components:{
                files:[
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/src/bower_components/html5shiv/dist",
                        src: "html5shiv.min.js",
                        dest: "assets/dist/js"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/src/bower_components/respond/dest",
                        src: "respond.min.js",
                        dest: "assets/dist/js"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/src/bower_components/fontawesome",
                        src: "css/font-awesome.min.css",
                        dest: "assets/dist/css"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/src/bower_components/fontawesome",
                        src: "fonts/*",
                        dest: "assets/fonts"
                    }
                ]
            },
            bootstrap:{
                files:[
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/src/bower_components/bootstrap/dist",
                        src: "fonts/*",
                        dest: "assets/fonts"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/src/bower_components/bootstrap/dist",
                        src: "js/bootstrap.min.js",
                        dest: "assets/dist/js/"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "assets/src/bower_components/bootstrap/less",
                        src: ['**/*','.csscomb.json','.csslintrc'],
                        dest: "assets/src/less/bootstrap/"
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
                            "!Gruntfile.js",
                            "!package.json",
                            "!.jshintrc",
                            "!.bowerrc",
                            "!bower.json",
                            "!Movefile-sample",
                            "!Movefile",
                            "!builds/**",
                            "!node_modules/**",
                            "!components/**/node_modules/**",
                            "!assets/src/bower_components/**",
                            "!components/**/bower_components/**",
                            "!assets/cache/**",
                            "!wbf/options/*.css",
                            "!wbf/options/*.less",
                            "!_bak/**"
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
    grunt.registerTask('setup', ['bower-install','bower-update','copy:bower_components','less:dev']);

    //Generate waboot.js
    grunt.registerTask('js', ['browserify:dist']);

    //Concat, beautify and minify js
    grunt.registerTask('jsmin', ['js','uglify']);

    //Build task
    grunt.registerTask('build', ['bower-update','copy:bower_components','less:production','less:waboot','jsmin','pot','compress:build']);

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
