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
                    'assets/dist/css/waboot-child.css': (function(){
                        /*if(fs.existsSync('assets/src/less/tmp_waboot.less')){
                            return 'assets/src/less/tmp_waboot.less';
                        }*/
                        return 'assets/src/less/waboot-child.less';
                    })()
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
                src: 'assets/dist/css/waboot-child.css'
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
    grunt.registerTask('setup', ['compile_less']);

    //Styles
    grunt.registerTask('compile_less', ['less:dev','postcss']);
};