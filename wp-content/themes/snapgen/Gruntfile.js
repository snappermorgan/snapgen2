module.exports = function(grunt) {
  "use strict";

  var theme_name = 'snapgen';
  var user_path = '/Users/snapper/Documents/dev/snapgen2.metrixinteractive.com';
  var web_path = '/Users/snapper/Sites/snapgen2/snapgen2.metrixinteractive.com';
  var global_vars = {
    theme_name: theme_name,
    theme_css: 'css',
    theme_scss: 'scss',
    user_path: user_path,
    web_path: web_path
  }


  grunt.initConfig({
    global_vars: global_vars,
     sass: {
      dist: {
        options: {
          outputStyle: 'nested',
          includePaths: ['<%= global_vars.theme_scss %>']
        },
        files: {
          '<%= global_vars.theme_css %>/<%= global_vars.theme_name %>.css': '<%= global_vars.theme_scss %>/<%= global_vars.theme_name %>.scss'
        }
      }
    },

  
    copy: {
      dist: {
        files: [
          {expand:true, cwd: 'bower_components/validationEngine/js/', src: ['jquery.validationEngine.js'],dest:'<%= global_vars.user_path %>/wp-content/themes/snapgen/js/vendor/', filter: 'isFile'},
          {expand:true, cwd: 'bower_components/validationEngine/js/languages/', src: ['jquery.validationEngine-en.js'],dest:'<%= global_vars.user_path %>/wp-content/themes/snapgen/js/vendor/', filter: 'isFile'},
          {expand:true, cwd: 'bower_components/validationEngine/css/', src: ['validationEngine.jquery.css'],dest:'<%= global_vars.user_path %>/wp-content/themes/snapgen/css/vendor/', filter: 'isFile'},
          {expand:true, cwd: 'bower_components/chosen_v1.3.0/', src: ['chosen.jquery.js'],dest:'<%= global_vars.user_path %>/wp-content/themes/snapgen/js/vendor/', filter: 'isFile'},
          {expand:true, cwd: 'bower_components/chosen_v1.3.0/', src: ['chosen.css'],dest:'<%= global_vars.user_path %>/wp-content/themes/snapgen/css/vendor/', filter: 'isFile'},
          {expand:true, cwd: 'bower_components/chosen_v1.3.0/', src: ['*.png'],dest:'<%= global_vars.user_path %>/wp-content/themes/snapgen/css/vendor/', filter: 'isFile'},
          {expand:true, cwd: 'bower_components/bootstrap-sass-twbs/assets/stylesheets/', src: ['**/*'],dest:'<%= global_vars.user_path %>/wp-content/themes/snapgen/scss/components/bootstrap', filter: 'isFile'},
          {expand:true, cwd: 'bower_components/bootstrap-sass-twbs/assets/fonts/', src: ['**/*'],dest:'<%= global_vars.user_path %>/wp-content/themes/snapgen/fonts/', filter: 'isFile'},
          {expand:true, cwd: 'bower_components/bootstrap-sass-twbs/assets/stylesheets/', src: ['**/*'],dest:'<%= global_vars.user_path %>/wp-content/themes/snapgen/scss/components/bootstrap', filter: 'isFile'}
        ]
      }
    },

    sync: {
      dist : {
        files: [
          { cwd: '<%= global_vars.user_path %>/wp-content/plugins/snapgen/', src: ['**/*'], dest: '<%= global_vars.web_path %>/wp-content/plugins/snapgen/', verbose: true},
          { cwd: '<%= global_vars.user_path %>/wp-content/themes/snapgen/css/', src: ['**/*'], dest: '<%= global_vars.web_path %>/wp-content/themes/snapgen/css/', verbose: true},
          { cwd: '<%= global_vars.user_path %>/wp-content/themes/snapgen/js/', src: ['**/*'], dest: '<%= global_vars.web_path %>/wp-content/themes/snapgen/js/', verbose: true},
          { cwd: '<%= global_vars.user_path %>/wp-content/themes/snapgen/images/', src: ['**/*'], dest: '<%= global_vars.web_path %>/wp-content/themes/snapgen/images/', verbose: true},
          { cwd: '<%= global_vars.user_path %>/wp-content/themes/snapgen/fonts/', src: ['**/*'], dest: '<%= global_vars.web_path %>/wp-content/themes/snapgen/fonts/', verbose: true},
          { cwd: '<%= global_vars.user_path %>/wp-content/themes/snapgen/', src: ['functions.php'], dest: '<%= global_vars.web_path %>/wp-content/themes/snapgen/', verbose: true},
          { cwd: '<%= global_vars.user_path %>/wp-content/themes/snapgen/', src: ['**/*.php'], dest: '<%= global_vars.web_path %>/wp-content/themes/snapgen/', verbose: true},
          { cwd: '<%= global_vars.user_path %>/wp-content/themes/snapgen/', src: ['style.css'], dest: '<%= global_vars.web_path %>/wp-content/themes/snapgen/', verbose: true},
         
        ]
      }
    },
    watch: {
      grunt: { files: ['Gruntfile.js'] },

      snapgen: {
        files: '<%= global_vars.user_path %>/wp-content/plugins/snapgen/**/*',
        tasks: ['sync']
      },
      templates: {
        files: '<%= global_vars.user_path %>/wp-content/themes/snapgen/**/*.php',
        tasks: ['sync']
      },
       images: {
        files: '<%= global_vars.user_path %>/wp-content/themes/snapgen/images/**/*',
        tasks: ['sync']
      },
       css: {
        files: '<%= global_vars.user_path %>/wp-content/themes/snapgen/css/**/*',
        tasks: ['sync']
      },
       js: {
        files: '<%= global_vars.user_path %>/wp-content/themes/snapgen/js/**/*',
        tasks: ['sync']
      },
      fonts: {
        files: '<%= global_vars.user_path %>/wp-content/themes/snapgen/fonts/**/*',
        tasks: ['sync']
      },
      wp: {
        files: '<%= global_vars.user_path %>/wp-content/themes/snapgen/style.css',
        tasks: ['sync']
      },
      sass: {
        files: '<%= global_vars.theme_scss %>/**/*.scss',
        tasks: ['sass'],
        options: {
          livereload: true
        }
      }
    },

 
  });

  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-sync');
  grunt.registerTask('build', ['copy','sass']);
  grunt.registerTask('c9', ['watch:sass']);
  grunt.registerTask('default', ['build','sync','watch']);
 
  
}