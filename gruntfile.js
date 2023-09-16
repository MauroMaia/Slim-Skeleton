
module.exports = function(grunt) {
  'use strict';

  const autoprefixer = require('autoprefixer')({
    browsers: [
      'Chrome >= 45',
      'Firefox >= 40',
      'Edge >= 12',
      'Explorer >= 11',
      'iOS >= 9',
      'Safari >= 9',
      'Android 2.3',
      'Android >= 4',
      'Opera >= 30'
    ]
  });


  // Project configuration.
  grunt.initConfig({

    // Metadata.
    pkg: grunt.file.readJSON('package.json'),
    banner: '/*!\n' +
            ' * <%= pkg.banner_name %> v<%= pkg.version %> (<%= pkg.homepage %>)\n' +
            ' * Copyright <%= grunt.template.today("yyyy") %> <%= pkg.author %>\n' +
            ' * Licensed under the Themeforest Standard Licenses\n' +
            ' * <%= grunt.template.today("yyyy-mm-dd") %>\n' +
            ' */\n',


    // Task configuration
    // -------------------------------------------------------------------------------

    // Watch on SCSS and JS files
    //
    watch: {
      css: {
        files: ['assets/css/*.css', '!assets/css/*.min.css'],
        tasks: ['css']
      },
      js: {
        files: ['assets/js/*.js', '!assets/js/*.min.js'],
        tasks: ['js']
      },
      /*script_dir: {
        files: ['assets/js/script/*.js', 'assets/js/script/** /*.js'],
        tasks: ['neuter:js']
      },*/
    },

    // Clean files and directories
    //
    clean: {
     js:['assets/js/**.min.js','!assets/js/core.min.js'],
     css:['assets/css/*.min.css','!assets/css/core.min.css']
    },

    // Import file for script.js
    //
    neuter: {
      options: {
        template: "{%= src %}"
      },
      js: {
        src: 'assets/js/script/main.js',
        dest: 'assets/js/script.js'
      },
    },

    // Uglify JS files
    //
    uglify: {
      options: {
        mangle: true,
        preserveComments: /^!|@preserve|@license|@cc_on/i,
        banner: '<%= banner %>'
      },
      script: {
        expand: true,
        src: [
          'assets/js/**/*.js',
          '!assets/js/core.min.js'
        ],
        ext: '.min.js'
      }
    },

    // Import file for script.js
    //
    eslint: {
      browserFiles: {
        src: [
          "assets/js/**/*js",
          "!assets/js/**/*.min.js",
          "!assets/js/app.js",
          "!assets/js/script.js",
          "!assets/js/script/*.js",
        ]
      }
    },

    // Do some post processing on CSS files
    //
    postcss: {
      options: {
        processors: [
          autoprefixer,
          require('postcss-flexbugs-fixes')
        ]
      },
      style: {
        src: 'assets/css/style.min.css'
      },
    },


    // Minify CSS files
    //
    cssmin: {
      options: {
        sourceMap: false,
        advanced:  false
      },
      core: {
        src:  'assets/css/style.css',
        dest: 'assets/css/style.min.css'
      }
    },

    // -------------------------------------------------------------------------------
    // END Task configuration

  });

  // These plugins provide necessary tasks.
  require('load-grunt-tasks')(grunt, { scope: 'devDependencies', pattern: ['grunt-*','gruntify-*'] });
  require('autoprefixer')(grunt);
  require('time-grunt')(grunt);

  // Run "grunt" to watch SCSS and JS files as well as running browser-sync
  grunt.registerTask('default', ['js','css']);
  grunt.registerTask('dist', ['build']);

  // Run "grunt build" to publish the template in a ./dist folder
  grunt.registerTask('build',
    [
      'clean:dist',
      'css',
      'js',
      'clean:dist_copied'
    ]
  );

  grunt.registerTask( 'css', ['clean:css','cssmin', 'postcss'] );

  grunt.registerTask( 'js', ['clean:js','eslint','neuter:js', 'uglify'] );
};
