/**
 * Default gulpfile for HALMA projects
 * 
 * Version 2019-03-27
 *
 * @see https://www.sitepoint.com/introduction-gulp-js/
 * @see https://nystudio107.com/blog/a-gulp-workflow-for-frontend-development-automation
 * @see https://nystudio107.com/blog/a-better-package-json-for-the-frontend
 */
'use strict';

// Read command line paramters (arguments)
const argv = require('yargs').argv;

// Check if we want a prodcution build
// Call like `gulp build --production` (or a single task instead of `build`)
const isProduction = (argv.production !== undefined);

// package vars
const pkg = require('./package.json');
const fs = require('fs');

// gulp
const gulp = require('gulp');
const {series} = require('gulp');

// Load all plugins in 'devDependencies' into the variable $
const $ = require('gulp-load-plugins')({
	pattern: ['*'],
	scope: ['devDependencies'],
	rename: {
		'gulp-strip-debug': 'stripdebug',
		'fancy-log' : 'log'
	}
});

// Default error handler: Log to console
const onError = (err) => {
	console.log(err);
};

// A banner to output as header for dist files
const banner = [
	"/**",
	" * @project       <%= pkg.name %>",
	" * @author        <%= pkg.author %>",
	" * @build         " + $.moment().format("llll") + " ET",
	" * @release       " + $.gitRevSync.long() + " [" + $.gitRevSync.branch() + "]",
	" * @copyright     Copyright (c) " + $.moment().format("YYYY") + ", <%= pkg.copyright %>",
	" *",
	" */",
	""
].join("\n");


var svgoOptions = {
	plugins: [
		{ cleanupIDs: false },
		{ mergePaths: false },
		{ removeViewBox: false },
		{ convertStyleToAttrs: false },
		{ removeUnknownsAndDefaults: false },
		{ cleanupAttrs: false }
	]
};

// Project settings
var settings = {

	browserSync: {
		proxy:'https://' + pkg.name + '.localhost',
		open: false,	// Don't open browser, change to "local" if you want or see https://browsersync.io/docs/options#option-open
		notify: false,	// Don't notify on every change
		https: {
			key: require('os').homedir() + '/server.key',
			cert: require('os').homedir() + '/server.crt'
		}
	},

	css: {
		src: 'src/css/**/*.scss',
		dest: pkg.project_settings.prefix + 'css/',
		srcMain: [
			'src/css/main.scss',
			'src/css/market_be.scss',
			'src/css/seller_be.scss',
			'src/css/cart_edit.scss'
			// You can add more files here that will be built seperately,
			// f.e. newsletter.scss
		],
		options: {
			sass: {
				outputStyle: 'compact',
				precision: 3,
				errLogToConsole: true,
			}
		},
		optionsProd: {
			sass: {
				outputStyle: 'compressed',
				precision: 3,
				errLogToConsole: true
			}
		}
	},

	js: {
		src: [
			'src/js/main.js',
			'src/js/compose_sheet.js'
			// 'src/js/cart_edit.js'
		],
		dest:	pkg.project_settings.prefix + 'js/',
		destFile:	'main.min.js'
	},

	jscheckout: {
		src: [
			'src/js/checkout/*.js',
		],
		dest: pkg.project_settings.prefix + 'js/',
		destFile: 'checkout.js'
	},

	jsevaluation: {
		src: [
			'src/js/evaluation/*.js',
		],
		dest: pkg.project_settings.prefix + 'js/',
		destFile: 'evaluation.js'
	},

	jsvendor: {
		src: [
			'src/js/vendor/**/*.js',
			// Add single vendor files here,
			// they will be copied as is to `{prefix}/js/vendor/`, 
			// e.g. 'node_modules/flickity/dist/flickity.pkgd.min.js',
			'node_modules/quagga/dist/quagga.min.js',
			'node_modules/jquery.appendgrid/jquery.appendGrid-1.7.1.min.js',
			'node_modules/dialog-polyfill/dist/dialog-polyfill.js',
			'node_modules/sprintf-js/dist/sprintf.min.js'
		],
		dest:	pkg.project_settings.prefix + 'js/vendor/'
	},

	cssvendor: {
		src:	[
			'src/css/vendor/**/*.css',
			// Add single vendor files here,
			// they will be copied as is to `{prefix}/css/vendor/`, 
			// e.g. 'node_modules/flickity/dist/flickity.min.css'
			'node_modules/jquery.appendgrid/jquery.appendGrid-1.7.1.min.css',
			'node_modules/dialog-polyfill/dist/dialog-polyfill.css'

		],
		dest:	pkg.project_settings.prefix + 'css/vendor/'
	},

	fonts: {
		src:	'src/fonts/**/*',
		dest:	pkg.project_settings.prefix + 'fonts/'
	},
	
	images: {
		src:	'src/img/**/*',
		dest:	pkg.project_settings.prefix + 'img/',
		options: [ 
			$.imagemin.optipng({ optimizationLevel: 5 }),
			$.imagemin.svgo(svgoOptions)
		]
	},

	icons: {
		src:	'src/icons/**/*.svg',
		dest:	pkg.project_settings.prefix + 'img/icons/',
		options: [
			$.imagemin.svgo(svgoOptions)
		]
	},

	sprite: {
		src: 'src/icons/*.svg',
		dest: pkg.project_settings.prefix + 'img/',
		destFile:	'icons.svg',
		options: [
			$.imagemin.svgo(svgoOptions)
		]
	},

	favicons: {
		src: 'src/img/favicon.svg',
		dest: pkg.project_settings.prefix + 'img/favicons/',
		background: '#ffffff'
	}
}



// Clean dist before building
function cleanDist() {
	return $.del([
		pkg.project_settings.prefix + '/'
	]);
}

/*
 *  Task: Compile SASS to CSS
 */
function css() {

	$.log("Building CSS" + ((isProduction) ? " [production build]" : " [development build]"));

	var stream = 
		gulp.src(settings.css.srcMain)
		.pipe($.plumber({ errorHandler: onError}))
	;

	if (!isProduction) {
		stream = stream.pipe($.sourcemaps.init());
	}

	var options = (isProduction) ? settings.css.optionsProd.sass : settings.css.options.sass;
	$.log(options);
	stream = stream
		.pipe($.sass(options).on('error', $.sass.logError))
		.pipe($.autoprefixer(settings.css.options.autoprefixer))
	;

	if (!isProduction) {
		stream = stream.pipe($.sourcemaps.write('./'))
	}
	else {
		// stream = stream.pipe($.minifycss())
		stream = stream.pipe($.header(banner, { pkg: pkg }))
	}

	stream = stream.pipe(gulp.dest(settings.css.dest))
		.pipe($.browserSync.stream());

	return stream;
}


/*
 * Task: Concat and uglify Javascript
 */
function js() {
	return doJs(settings.js);
}


function jsCheckout() {
	return doJs(settings.jscheckout);
}


function jsEvaluation() {
	return doJs(settings.jsevaluation);
}


function doJs(settings) {

	$.log("Building Javascript: " + settings.destFile + ((isProduction) ? " [production build]" : " [development build]"));

	var stream = gulp
		.src(settings.src)
		.pipe($.jsvalidate().on('error', function(jsvalidate) { console.log(jsvalidate.message); this.emit('end') }));

	if (!isProduction) {
		stream = stream.pipe($.sourcemaps.init());
	}

	stream = stream.pipe($.concat(settings.destFile));

	if (isProduction) {
 		stream = stream.pipe($.stripdebug())
		.pipe($.terser())
		// .pipe($.uglify().on('error', function(uglify) { console.log(uglify.message); this.emit('end') }))
 		.pipe($.header(banner, { pkg: pkg }));
	}
	else {
		stream = stream.pipe($.sourcemaps.write('./'));
	}

	stream = stream.pipe(gulp.dest(settings.dest))
		.pipe($.browserSync.stream());

	return stream;
}



/*
 * Task: Uglify vendor Javascripts
 */
function jsVendor() {
	return gulp.src(settings.jsvendor.src)
		.pipe(gulp.dest(settings.jsvendor.dest));
}



function cssVendor() {
	return gulp.src(settings.cssvendor.src)
		.pipe(gulp.dest(settings.cssvendor.dest));
}


function fonts() {
	return gulp.src(settings.fonts.src)
		.pipe(gulp.dest(settings.fonts.dest));
}


/*
 * Task: create images
 * TODO: Check if optimization is more effectiv when it is done separately for all different image types(png, svg, jpg)
 */
function images() {
	// optimize all other images
	// TODO: It seems that plugin in don't overwrites existing files in destination folder!??
	return gulp.src(settings.images.src)
		.pipe($.newer(settings.images.dest))
		.pipe($.imagemin(settings.images.options, { verbose: true }))
		.pipe(gulp.dest(settings.images.dest));
}



function icons() {
	return gulp.src(settings.icons.src)
		.pipe($.newer(settings.icons.dest))
		.pipe($.imagemin(settings.icons.options))
		.pipe(gulp.dest(settings.icons.dest));
}


/*
 * Task: create sprites(SVG): optimize and concat SVG icons
 */
function sprite() {
	return gulp.src(settings.sprite.src)
		.pipe($.imagemin(settings.sprite.options))
		.pipe($.svgstore({
			inlineSvg: true
		}))
		.pipe($.rename(settings.sprite.destFile))
		.pipe(gulp.dest(settings.sprite.dest));
}



/*
 * Default TASK: Watch SASS and JAVASCRIPT files for changes,
 * build CSS file and inject into browser
 */
function gulpDefault(done) {

	$.browserSync.init(settings.browserSync);

	gulp.watch(settings.css.src, css);
	gulp.watch(settings.js.src, js);
	gulp.watch(settings.jscheckout.src, jsCheckout);
	gulp.watch(settings.jsevaluation.src, jsEvaluation);
	done();
}


/**
 * Generate favicons
 */
function favicon() {
	return gulp.src(settings.favicons.src)
		.pipe($.favicons({
			appName: pkg.name,
			appShortName: pkg.name,
			appDescription: pkg.description,
			developerName: pkg.author,
			developerUrl: pkg.repository.url,
			background: settings.favicons.background,
			path: settings.favicons.dest,
			url: pkg.project_settings.url,
			display: "standalone",
			orientation: "portrait",
			scope: "/",
			start_url: "/",
			version: pkg.version,
			logging: false,
			pipeHTML: false,
			replace: true,
			icons: {
				android: false,
				appleIcon: false,
				appleStartup: false,
				coast: false,
				firefox: false,
				windows: false,
				yandex: false,
				favicons: true
			}
		}))
		.pipe(gulp.dest(settings.favicons.dest));
}

var exec = require('child_process').exec;

/*
 * Task: Build all
 */
exports.build = series(cleanDist, js, jsVendor, jsCheckout, jsEvaluation, css, cssVendor, images, sprite, icons, fonts, favicon);

exports.default = gulpDefault;
exports.cleanDist = cleanDist;
exports.css = css;
exports.js = js;
exports.jsVendor = jsVendor;
exports.jsCheckout = jsCheckout;
exports.jsEvaluation = jsEvaluation;
exports.cssVendor = cssVendor;
exports.fonts = fonts;
exports.images = images;
exports.icons = icons;
exports.sprite = sprite;
exports.favicon = favicon;

