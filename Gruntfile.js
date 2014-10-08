module.exports = function(grunt) {

    // load all tasks
    require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev:{
                options:{
                },
                files:{
                    'assets/css/waboot.css': 'sources/less/waboot.less',
                    'admin/css/tinymce.css': 'sources/admin/tinymce.less',
                    'admin/css/admin.css': 'sources/admin/admin.less',
                    'admin/css/waboot-optionsframework.css': 'sources/admin/optionsframework.less'
                }
            },
            production:{
                options:{
                    cleancss: true
                },
                files: ['<%= less.dev.files %>']
            },
            waboot:{
                options:{
                    cleancss: true,
                    sourceMap: true,
                    sourceMapFilename: "assets/css/waboot.css.map",
                    sourceMapBasepath: "assets/css"
                },
                files: {
                    'assets/css/waboot.css': 'sources/less/waboot.less'
                }                
            }
        },
        // JsHint your javascript
        jshint : {
            all : ['sources/js/*.js'],
            options : {
                browser: true,
                curly: false,
                eqeqeq: false,
                eqnull: true,
                expr: true,
                immed: true,
                newcap: true,
                noarg: true,
                smarttabs: true,
                sub: true,
                undef: false
            }
        },
        concat: {
            options: {
                // define a string to put between each file in the concatenated output
                separator: '\n'
            },
            dist: {
                files:{
                    'assets/js/waboot.js': ['sources/js/waboot-helper.js'],
                    'assets/js/waboot-mobile.js': ['sources/js/waboot-helper-mobile.js'],
                    'assets/js/plugins.js': ['sources/js/vendor/*.js'],
                    'assets/js/plugins-mobile.js': ['sources/js/vendor-mobile/*.js']
                }
            }
        },
        uglify: {
            options: {
                // the banner is inserted at the top of the output
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    'assets/js/waboot.min.js': ['assets/js/waboot.js'],
                    'assets/js/waboot-mobile.min.js': ['assets/js/waboot-mobile.js'],
                    'assets/js/plugins.min.js': ['assets/js/plugins.js'],
                    'assets/js/plugins-mobile.min.js': ['assets/js/plugins-mobile.js']
                }
            }
        },
        "jsbeautifier" : {
            files : ['assets/js/*.js'],
            options : {
            }
        },
        copy:{
            all:{
                files:[
                    '<%= copy.fontawesome.files %>',
                    '<%= copy.bootstrap.files %>',
                    {
                        expand: true,
                        flatten: true,
                        cwd: "bower_components/html5shiv/dist",
                        src: "html5shiv.min.js",
                        dest: "assets/js"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "bower_components/respond/dest",
                        src: "respond.min.js",
                        dest: "assets/js"
                    }
                ]
            },
            fontawesome:{
                files:[
                    {
                        expand: true,
                        flatten: true,
                        cwd: "bower_components/fontawesome",
                        src: "css/font-awesome.min.css",
                        dest: "assets/css"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "bower_components/fontawesome",
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
                        cwd: "bower_components/bootstrap/dist",
                        src: "fonts/*",
                        dest: "assets/fonts"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "bower_components/bootstrap/dist",
                        src: "js/bootstrap.min.js",
                        dest: "assets/js/"
                    },
                    {
                        expand: true,
                        flatten: true,
                        cwd: "bower_components/bootstrap/less",
                        src: ['**/*'],
                        dest: "sources/bootstrap/"
                    }
                ]
            },
            dist:{
                files:[
                    {
                        expand: true,
                        cwd: "./",
                        src: ["**/*","!.*","!Gruntfile.js","!package.json","!bower.json","!builds/**","!node_modules/**","!bower_components/**","!assets/cache/**","!_bak/**"],
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
                src: ['**/*.php'],
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
                files: 'sources/less/*.less',
                tasks: ['less:dev']
            },
            scripts:{
                files: ['<%= jshint.all %>'],
                task: ['jshint']
            }
        }
    });

    // Register tasks
    grunt.registerTask('setup', ['bower-install','copy:all','less:dev']); //Setup task
    grunt.registerTask('default', ['watch']); // Default task
    grunt.registerTask('build', ['less:production','less:waboot','concat','jsbeautifier','uglify','compress:build']); // Build task
    grunt.registerTask('js', ['concat','jsbeautifier']); // Concat and beautify js
    grunt.registerTask('jsmin', ['concat','jsbeautifier','uglify']); // Concat, beautify and minify js

    // Run bower install
    grunt.registerTask('bower-install', function() {
        var exec = require('child_process').exec;
        var cb = this.async();
        exec('bower install', function(err, stdout, stderr) {
            console.log(stdout);
            cb();
        });
    });
}
