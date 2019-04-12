/* global module, require */
module.exports = function( grunt ) {
	'use strict';

	// Load all grunt tasks
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

	// Project configuration
	grunt.initConfig( {
		pkg:    grunt.file.readJSON( 'package.json' ),

		clean   : {
			dist: ['dist/']
		},

		copy: {
			dist: {
				files : [
					{
						expand: true,
						src: [
							'**',
							'!dist/**',
							'!AUTHORS.md',
							'!assets-wp-repo/**',
							'!code-coverage/**',
							'!codeception.yml',
							'!node_modules/**',
							'!assets/vendor/**',
							'assets/vendor/insertionQuery/insQ.min.js',
							'assets/vendor/jquery-ui/jquery-ui.min.js',
							'assets/vendor/jquery-ui/themes/base/jquery-ui.min.css',
							'!assets/js/src/**',
							'!assets/css/src/**',
							'!Gruntfile.js',
							'!bower.json',
							'!package.json',
							'!package-lock.json',
							'!composer.json',
							'!composer.lock',
							'!phpcs.xml',
							'!phpdoc.dist.xml',
							'!phpunit.xml.dist',
							'!bin/**',
							'!tests/**',
							'!.idea/**',
							'!tags',
							'!vendor/**',
							"vendor/sudar/wp-system-info/**"
						],
						dest: 'dist/'
					}
				]
			}
		},
		watch: {
			all: {
				files: ['**', '!dist/**'],
				tasks: ['build']
			}
		}
	} );

	require('time-grunt')(grunt);

	grunt.registerTask('build', ['clean', 'copy:dist']);

	grunt.util.linefeed = '\n';
};
