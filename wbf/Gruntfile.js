module.exports = function (grunt) {

    // load all tasks
    require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev: {
                options: {},
                files: {
                    'admin/css/tinymce.css': 'sources/admin/tinymce.less',
                    'admin/css/admin.css': 'sources/admin/admin.less',
                    'admin/css/waboot-optionsframework.css': 'sources/admin/optionsframework.less',
                    'admin/css/waboot-componentsframework.css': 'sources/admin/componentsframework.less',
                    'admin/css/waboot-pagebuilder.css': 'wbf/sources/less/pagebuilder.less'
                }
            },
            production: {
                options: {
                    cleancss: true
                },
                files: ['<%= less.dev.files %>']
            }
        },
        // JsHint your javascript
        jshint: {
            all: ['sources/js/*.js','sources/js/**/*.js'],
            options: {
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
        uglify: {
            options: {
                // the banner is inserted at the top of the output
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    'admin/js/admin.min.js': ['sources/js/admin/admin.js'],
                    'admin/js/code-editor.min.js': ['sources/js/admin/code-editor.js'],
                    'admin/js/components-page.min.js': ['sources/js/admin/components-page.js'],
                    'admin/js/font-selector.min.js': ['sources/js/admin/font-selector.js'],
                    'includes/scripts/wbfgmap.min.js': ['sources/js/includes/wbfgmap/markerclusterer.js','sources/js/includes/wbfgmap/acfmap.js']
                }
            }
        },
        "jsbeautifier": {
            files: ['admin/js/*.js', 'public/js/*.js','includes/scripts/*.js','includes/scripts/**/*.js'],
            options: {}
        },
        pot: {
            options: {
                text_domain: 'wbf',
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
            files: {
                src: ['*.php','admin/**/*.php','includes/**/*.php','public/**/*.php'],
                expand: true
            }
        },
        copy: {
            dist: {
                files: [
                    {
                        expand: true,
                        cwd: "./",
                        src: ["**/*", "!.*", "!Gruntfile.js", "!package.json", "!bower.json", "!builds/**", "!node_modules/**", "!bower_components/**", "!assets/cache/**", "!_bak/**"],
                        dest: "builds/wbf-<%= pkg.version %>/"
                    }
                ]
            }
        },
        compress: {
            build: {
                options: {
                    archive: "builds/wbf-<%= pkg.version %>.zip"
                },
                files: [
                    {
                        expand: true,
                        cwd: "./",
                        src: '<%= copy.dist.files.0.src %>',
                        dest: "wbf/"
                    }
                ]
            }
        },
        watch: {
            less: {
                files: 'sources/less/*.less',
                tasks: ['less:dev']
            },
            scripts: {
                files: ['<%= jshint.all %>'],
                task: ['jshint']
            }
        }
    });

    // Register tasks
    grunt.registerTask('setup', ['bower-install', 'copy:all', 'less:dev']); //Setup task
    grunt.registerTask('default', ['watch']); // Default task
    grunt.registerTask('build', ['less:production', 'jsbeautifier', 'uglify', 'compress:build']); // Build task
    grunt.registerTask('js', ['jsbeautifier']); // Concat and beautify js
    grunt.registerTask('jsmin', ['jsbeautifier', 'uglify']); // Concat, beautify and minify js

    // Run bower install
    grunt.registerTask('bower-install', function () {
        var exec = require('child_process').exec;
        var cb = this.async();
        exec('bower install', function (err, stdout, stderr) {
            console.log(stdout);
            cb();
        });
    });
}