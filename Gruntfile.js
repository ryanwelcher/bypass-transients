module.exports = function( grunt ) {
	'use strict';

	// Load all grunt tasks
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

	// Project configuration
	grunt.initConfig( {
		sass: {
			dist: {
				files: [{
					expand: true,
					cwd: 'assets/css/src',
					src: ['*.scss'],
					dest: 'assets/css',
					ext: '.src.css'
				}]
			}
		},
		cssmin: {
			dist: {
				files: [{
					expand: true,
					cwd: 'assets/css',
					src: ['*.src.css'],
					dest: 'assets/css',
					ext: '.min.css'
				}]
			}
		},
		watch: {
			scripts: {
				files: ['assets/css/**'],
				tasks: ['sass','cssmin'],
				options: {
					spawn: false
				}
			}
		}
	});

	// Default task.
	grunt.registerTask( 'default', [
		'sass',
		'cssmin'
	]);
};