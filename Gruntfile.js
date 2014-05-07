module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev:{
                options:{
                    paths: "less/"
                },
                files:{
                    'css/style.css': 'sources/overrides/waboot.less',
                    'admin/css/tinymce.css': 'sources/less/tinymce.less',
                    'admin/css/admin.css': 'sources/less/admin.less',
                    'admin/css/waboot-optionsframework.css': 'sources/less/optionsframework.less'
                }
                //src funziona se non c'è distinzione production\dev, altrimenti funziona files
                /*src:{
                    expand: true,
                    cwd: "less",
                    src: ['style.less','admin.less','tinymce.less'],
                    dest: 'css/',
                    ext: '.css'
                }*/
            },
            production:{
                options:{
                    paths: "less/",
                    cleancss: true
                },
                files:{
                    'css/style.css': 'sources/overrides/waboot.less',
                    'admin/css/tinymce.css': 'sources/less/tinymce.less',
                    'admin/css/admin.css': 'sources/less/admin.less',
                    'admin/css/waboot-optionsframework.css': 'sources/less/optionsframework.less'
                }
                //src funziona se non c'è distinzione production\dev, altrimenti funziona files
                /*src:{
                    expand: true,
                    cwd: "less",
                    src: ['style.less','admin.less','tinymce.less'],
                    dest: 'css/',
                    ext: '.css'
                }*/
            }
        },
        recess:{ //Lint and minify CSS and LESS using Twitter RECESS [github.com/twitter/recess] module
          production:{
              options:{
                  compile:true,
                  compress:true
              },
              files:{
                  'css/style.css': 'sources/overrides/waboot.less',
                  'admin/css/tinymce.css': 'sources/less/tinymce.less',
                  'admin/css/admin.css': 'sources/less/admin.less',
                  'admin/css/waboot-optionsframework.css': 'sources/less/optionsframework.less'
              }
          }
        },
        copy:{
            dev:{
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
                },
                {
                    expand: true,
                    flatten: true,
                    cwd: "bower_components/bootstrap/dist",
                    src: ["css/*","!css/bootstrap.css","!css/bootstrap-theme.css"],
                    dest: "assets/css"
                },
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
                    src: ['mixins.less','variables.less'],
                    dest: "sources/less/bootstrap/"
                },
                {
                    expand: true,
                    flatten: true,
                    cwd: "bower_components/html5shiv/dist",
                    src: "html5shiv.min.js",
                    dest: "assets/js"
                }]
            }
        },
        watch: {
            less: {
                files: 'less/*.less',
                tasks: 'less:dev'
            }
        }
    });

    // Register tasks
    grunt.registerTask('setup', ['bower-install','copy:dev','less:dev']); //Setup task
    grunt.registerTask('default', ['watch']); // Default task
    grunt.registerTask('build', ['recess:production']); // Build task

    // Load up tasks
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-recess');
    grunt.loadNpmTasks('grunt-wp-version');
    grunt.loadNpmTasks('grunt-contrib-copy');

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
