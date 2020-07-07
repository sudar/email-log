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
							"vendor/sudar/wp-system-info/**",
							"vendor/collizo4sky/persist-admin-notices-dismissal/**"
						],
						dest: 'dist/'
					}
				]
			},
			jqueryUi: {
				files: [
					{
						src : "node_modules/components-jqueryui/jquery-ui.min.js",
						dest: "assets/vendor/jquery-ui/jquery-ui.min.js"
					},
					{
						src : "node_modules/components-jqueryui/themes/base/jquery-ui.min.css",
						dest: "assets/vendor/jquery-ui/themes/base/jquery-ui.min.css"
					},
					{
						expand: true,
						src: ["node_modules/components-jqueryui/themes/base/images/*"],
						dest: "assets/vendor/jquery-ui/themes/base/images/",
						flatten: true,
						filter: "isFile"
					}
				]
			},
			insertionQ: {
				files: [
					{
						src : "node_modules/insertion-query/insQ.min.js",
						dest: "assets/vendor/insertion-query/insQ.min.js"
					}
				]
			},
		},

		makepot: {
			target: {
				options: {
					exclude: ['vendor/.*', 'dist/.*'],
					updateTimestamp: false,
				}
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

	grunt.registerTask("vendor", ["copy:jqueryUi", "copy:insertionQ"]);
	grunt.registerTask("build", ["vendor", "makepot", "clean", "copy:dist"]);

	grunt.util.linefeed = '\n';
};
