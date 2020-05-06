/* eslint-disable */
/**
 * Gulp File
 *
 * 1) Make sure you have node and npm installed locally
 *
 * 2) Install all the modules from package.json:
 * $ npm install
 *
 * 3) Run gulp to minify javascript and css using the 'gulp' command.
 */

var webpack 		= require( 'webpack-stream' );
var babel           = require( 'gulp-babel' );
var checktextdomain = require( 'gulp-checktextdomain' );
var chmod           = require( 'gulp-chmod' );
var del             = require( 'del' );
var exec            = require( 'child_process' ).exec;
var gulp            = require( 'gulp' );
var minifyCSS       = require( 'gulp-minify-css' );
// var phpunit         = require( 'gulp-phpunit' );
var rename          = require( 'gulp-rename' );
var sass            = require( 'gulp-sass' );
var sort            = require( 'gulp-sort' );
var uglify          = require( 'gulp-uglify' );
var zip             = require( 'gulp-zip' );
var browsersync 	= require( 'browser-sync' );

var paths = {
	admin: {
		blocks: {
			art_gallery: {
				js: [ 'assets/js/admin/blocks/art-gallery/**/*.js' ]
			},
			artwork: {
				js: [ 'assets/js/admin/blocks/artwork/**/*.js' ],
				css: [ 'assets/css/admin/blocks/artwork/**/*.scss' ],
			}
			
		},
		css: {

		}
	},
	frontend: {
		js: [ 'assets/js/frontend/**/*.js' ],
		css: {
			artworker: [ 'assets/css/public/artworker/**/*.scss' ]
		},
	},
	
	
};

gulp.task( 'clean', gulp.series( function( cb ) {
	return del( [
		'assets/js/**/*.min.js',
		'assets/js/**/*.min.js',
		'assets/js/admin/**/*.min.js',
		'assets/js/admin/blocks/**/block.js',
		'assets/js/public/**/*.min.js',
		'assets/css/**/*.min.css',
		'assets/css/**/*.css',
		'!assets/css/public/artworker/justifiedGallery.css',
		'!assets/css/public/artworker/justifiedGallery.min.css',
		'!assets/css/public/artworker/jquery.mosaic.css',
		'!assets/css/public/artworker/jquery.mosaic.min.css',
		'!assets/js/public/jquery.mosaic.js',
		'!assets/js/public/jquery.mosaic.min.js',
		'!assets/js/public/jquery.flex-images.min.js',
		'!assets/js/public/jquery.justifiedGallery.js',
		'!assets/js/public/jquery.justifiedGallery.min.js',
		'!assets/js/public/photoswipe-ui-default.min.js',
		'!assets/js/public/photoswipe.min.js',
		'!assets/js/public/jquery.unveil.min.js'
	], cb );
} ) );

gulp.task( 'art-gallery-block', gulp.series( function( cb ) {
	return gulp.src( paths.admin.blocks.art_gallery.js )
		.pipe( babel( { presets: ['@babel/preset-env','@babel/preset-react'] } ) )
		.pipe( webpack( require( './webpack.config.js' ) ) )
		.pipe( gulp.dest( 'assets/js/admin/blocks/art-gallery' ) )
		// This will minify and rename to *.min.js
		.pipe( uglify() )
		.pipe( rename( { extname: '.min.js' } ) )
		.pipe( chmod( 0o644 ) )
		.pipe( gulp.dest( 'assets/js/admin/blocks/art-gallery' ) );	
} ) );

gulp.task( 'artwork-block', gulp.series( function( cb ) {
	return gulp.src( paths.admin.blocks.artwork.js )
		.pipe( babel( { presets: ['@babel/preset-env','@babel/preset-react'] } ) )
		.pipe( webpack( require( './webpack.config.js' ) ) )
		.pipe( rename( { basename: 'block' } ) )
		.pipe( gulp.dest( 'assets/js/admin/blocks/artwork/' ) )
		// This will minify and rename to *.min.js
		.pipe( uglify() )
		.pipe( rename( { extname: '.min.js' } ) )
		.pipe( chmod( 0o644 ) )
		.pipe( gulp.dest( 'assets/js/admin/blocks/artwork' ) );	
} ) );

gulp.task( 'blocks', gulp.series( 'artwork-block' ) );

gulp.task( 'adminjs', gulp.series( 'blocks' ) );
gulp.task( 'frontendjs', gulp.series( function() {
	return gulp.src( paths.frontend.js )
		.pipe( babel() )
		// This will minify and rename to *.min.js
		.pipe( uglify() )
		.pipe( rename( { extname: '.min.js' } ) )
		.pipe( chmod( 0o644 ) )
		.pipe( gulp.dest( 'assets/js/frontend' ) );
}  )  );

gulp.task( 'artwork-block-scss', gulp.series( function() {
	return gulp.src( paths.admin.blocks.artwork.css )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( minifyCSS( { keepBreaks: false } ) )
		.pipe( gulp.dest( 'assets/css/admin/blocks/artwork/' ) );
} ) );

gulp.task( 'artworker-style', gulp.series( function() {
	return gulp.src( paths.frontend.css.artworker )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( minifyCSS( { keepBreaks: false } ) )
		.pipe( gulp.dest( 'assets/css/public/artworker' ) );
} ) );

gulp.task( 'admincss', gulp.series( 'artwork-block-scss' ) );

gulp.task( 'frontendcss', gulp.series( 'artworker-style' ) );

gulp.task( 'CSS', gulp.series( 'admincss', 'frontendcss' ) );

gulp.task( 'JS', gulp.series( 'adminjs', 'frontendjs' ) );

gulp.task( 'pot', gulp.series( function() {
	return gulp.src( [ '**/**.php', '!node_modules/**', '!build/**' ] )
		.pipe( sort() )
		.pipe( gulp.dest( 'languages/artworker.pot' ) );
} ) );

gulp.task( 'textdomain', gulp.series( function() {
	return gulp.src( [ '**/*.php', '!node_modules/**', '!build/**' ] )
		.pipe( checktextdomain( {
			text_domain: 'artworker',
			keywords: [
				'__:1,2d',
				'_e:1,2d',
				'_x:1,2c,3d',
				'esc_html__:1,2d',
				'esc_html_e:1,2d',
				'esc_html_x:1,2c,3d',
				'esc_attr__:1,2d',
				'esc_attr_e:1,2d',
				'esc_attr_x:1,2c,3d',
				'_ex:1,2c,3d',
				'_n:1,2,4d',
				'_nx:1,2,4c,5d',
				'_n_noop:1,2,3d',
				'_nx_noop:1,2,3c,4d'
			]
		} ) );
} ) );

gulp.task( 'test', function() {
	return gulp.src( 'phpunit.xml.dist' )
		.pipe( phpunit() );
} );

gulp.task( 'build', gulp.series( 'clean', 'CSS', 'JS' ) );
gulp.task( 'build-unsafe', gulp.series( 'clean', 'CSS', 'JS' ) );


gulp.task( 'package', gulp.series( 'build' ) );
gulp.task( 'package-unsafe', gulp.series( 'build-unsafe' ) );

// Browsersync options
const syncOpts = {
  open        : false,
  notify      : false,
  ghostMode   : false,
  ui: {
    port: 8001
  }
};

// browser-sync
gulp.task( 'browsersync', () => {
	if (browsersync === false) {
		browsersync = browsersync.create();
		browsersync.init(syncOpts);
	}
} );

// watch for file changes
gulp.task( 'watch', gulp.series( 'browsersync' ), () => {

    // JS changes
  gulp.watch( paths.js, ['JS'] );

    // CSS changes
  gulp.watch( paths.css, ['CSS'] );


} );

gulp.task( 'default', gulp.series( 'build', 'watch' ) );