/* eslint-env node */
module.exports = function ( grunt ) {
	var conf = grunt.file.readJSON( 'extension.json' );

	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			options: {
				cache: true
			},
			src: [
				'**/*.js',
				'!node_modules/**',
				'!vendor/**',
				'!tests/externals/**',
				'!docs/**'
			]
		},
		banana: conf.MessagesDirs,
		watch: {
			files: [
				'.{stylelintrc,eslintrc}.json',
				'<%= eslint.all %>',
				'<%= stylelint.all %>'
			],
			tasks: 'test'
		},
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**',
				'!vendor/**',
				'!docs/**'
			]
		},
		stylelint: {
			all: [
				'**/*.css',
				'!node_modules/**',
				'!vendor/**',
				'!docs/**'
			]
		}
	} );

	grunt.registerTask( 'lint', [ 'eslint', 'jsonlint', 'banana', 'stylelint' ] );
	grunt.registerTask( 'test', 'lint' );
	grunt.registerTask( 'default', 'test' );
};
