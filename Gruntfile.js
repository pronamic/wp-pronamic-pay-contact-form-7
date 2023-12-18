/* jshint node:true */
/* eslint-env node */
module.exports = function( grunt ) {
	require( 'load-grunt-tasks' )( grunt );

	// Project configuration.
	grunt.initConfig( {
		// Package
		pkg: grunt.file.readJSON( 'package.json' ),

		// JSHint
		jshint: {
			options: grunt.file.readJSON( '.jshintrc' ),
			grunt: [ 'Gruntfile.js' ],
			admin: [
				'js/admin.js'
			]
		},

		// Uglify
		uglify: {
			options: {
				sourceMap: true
			},
			scripts: {
				files: {
					// Admin
					'js/admin.min.js': 'js/admin.js'
				}
			}
		}
	} );

	// Default task(s).
	grunt.registerTask( 'default', [ 'jshint' ] );
	grunt.registerTask( 'assets', [ 'jshint', 'uglify' ] );
};
