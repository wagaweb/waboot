module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dev:{
                options:{
                    paths: "less/"
                },
                files:{
                    'css/style.css': 'less/style.less',
                    'css/tinymce.css': 'less/tinymce.less',
                    'css/admin.css': 'less/admin.less'
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
                    'css/style.css': 'less/style.less',
                    'css/tinymce.css': 'less/tinymce.less',
                    'css/admin.css': 'less/admin.less'
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
                  'css/style.css': 'less/style.less',
                  'css/tinymce.css': 'less/tinymce.less',
                  'css/admin.css': 'less/admin.less'
              }
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
}
