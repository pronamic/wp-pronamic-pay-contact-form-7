/* jshint node:true */
/* eslint-env node */
module.exports = function( grunt ) {
	require( 'load-grunt-tasks' )( grunt );

	// Project configuration.
	grunt.initConfig( {
		// Package
		pkg: grunt.file.readJSON( 'package.json' ),

		// PHPLint
		phplint: {
			all: [ 'src/**/*.php' ]
		},

		// PHP Mess Detector
		phpmd: {
			application: {
				dir: 'src'
			},
			options: {
				bin: 'vendor/bin/phpmd',
				exclude: 'node_modules',
				reportFormat: 'xml',
				rulesets: 'phpmd.ruleset.xml'
			}
		},

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
	grunt.registerTask( 'default', [ 'jshint', 'phplint', 'phpmd' ] );
	grunt.registerTask( 'assets', [ 'jshint', 'uglify' ] );
};
