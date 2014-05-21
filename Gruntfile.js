module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev:{
                options:{
                },
                files:{
                    'assets/css/style.css': 'sources/overrides/waboot.less',
                    'admin/css/tinymce.css': 'sources/less/tinymce.less',
                    'admin/css/admin.css': 'sources/less/admin.less',
                    'admin/css/waboot-optionsframework.css': 'sources/less/optionsframework.less'
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
                    sourceMapFilename: "assets/css/style.css.map",
                    sourceMapBasepath: "assets/css/"                    
                },
                files: {
                    'assets/css/style.css': 'sources/overrides/waboot.less'
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
                src: ['sources/js/*.js'],
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
            setupAll:{
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
                    }
                ]
            }
        },
        watch: {
            less: {
                files: 'sources/overrides/**/*.less',
                tasks: ['less:dev']
            },
            scripts:{
                files: ['<%= jshint.all %>'],
                task: ['jshint']
            }
        }
    });

    // Register tasks
    grunt.registerTask('setup', ['bower-install','copy:setupAll','less:dev']); //Setup task
    grunt.registerTask('default', ['watch']); // Default task
    grunt.registerTask('build', ['less:production','less:waboot','concat','jsbeautifier','uglify']); // Build task
    grunt.registerTask('js', ['concat','jsbeautifier']); // Build task
    grunt.registerTask('jsmin', ['concat','jsbeautifier','uglify']); // Build task

    // Load up tasks
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-jsbeautifier');

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
