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
                // the files to concatenate
                src: ['sources/js/*.js','!sources/js/unused/*.js'],
                // the location of the resulting JS file
                dest: 'assets/js/waboot.js'
            }
        },
        uglify: {
            options: {
                // the banner is inserted at the top of the output
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    'assets/js/waboot.min.js': ['<%= concat.dist.dest %>']
                }
            }
        },
        "jsbeautifier" : {
            files : ['<%= concat.dist.dest %>'],
            options : {
            }
        },
        copy:{
            all:{
                files:[
                    '<%= copy.fontawesome.files %>',
                    '<%= copy.bootstrapDist.files %>',
                    {
                        expand: true,
                        flatten: true,
                        cwd: "bower_components/bootstrap/less",
                        src: ['*'],
                        dest: "sources/bootstrap/"
                    },
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
            bootstrapDist:{
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
