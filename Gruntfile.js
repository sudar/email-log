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
							'!assets-wp-repo/**',
							'!node_modules/**',
							'!assets/vendor/**',
							'!assets/js/src/**',
							'!assets/css/src/**',
							'!Gruntfile.js',
							'!bower.json',
							'!package.json',
							'!phpcs.xml',
							'!phpdoc.dist.xml',
							'!phpunit.xml.dist',
							'!bin/**',
							'!tests/**',
							'!.idea/**',
							'!tags'
						],
						dest: 'dist/'
					}
				]
			}
		}
	} );

	require('time-grunt')(grunt);

	grunt.registerTask('build', ['clean', 'copy:dist']);

	grunt.util.linefeed = '\n';
};
