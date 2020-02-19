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

// package vars
const pkg = require('./package.json');

// gulp
const gulp = require('gulp');

// Load all plugins in 'devDependencies' into the variable $
const $ = require('gulp-load-plugins')({
	pattern: ['*'],
	scope: ['devDependencies'],
	rename: {
		'gulp-strip-debug': 'stripdebug'
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



//array of gulp task names that should be included in "gulp build" task
var build_dev  = ['clean:dist', 'js-dev', 'jsvendor', 'js-checkout', 'js-evaluation', 'css-dev', 'cssvendor', 'images', 'sprite', 'icons', 'fonts', 'favicons'];
var build_prod = ['clean:dist', 'js-prod', 'jsvendor', 'js-checkout', 'js-evaluation', 'css-prod', 'cssvendor', 'images', 'sprite', 'icons', 'fonts', 'favicons'];



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
			// key: '/etc/ssl/private/ssl-cert-snakeoil.key',
			// cert: '/etc/ssl/certs/ssl-cert-snakeoil.pem'
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
			'src/js/cart_edit.js'
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
gulp.task('clean:dist', function() {
	return $.del([
		pkg.project_settings.prefix + '/'
	]);
})

/*
 *  Task: process SASS 
 */
gulp.task('css-dev', function(done) {
	return gulp
		.src(settings.css.srcMain)
		.pipe($.plumber({ errorHandler: onError}))
		.pipe($.sourcemaps.init())
		.pipe($.sass(settings.css.options.sass).on('error', $.sass.logError))
		.pipe($.autoprefixer(settings.css.options.autoprefixer))
		.pipe($.sourcemaps.write('./'))
		.pipe(gulp.dest(settings.css.dest))
		.pipe($.browserSync.stream())
	;	
	done();
});

gulp.task('css-prod', function(done) {
	return gulp
		.src(settings.css.srcMain)
		.pipe($.plumber({ errorHandler: onError }))
		.pipe($.sass(settings.css.optionsProd.sass).on('error', $.sass.logError))
		.pipe($.autoprefixer(settings.css.options.autoprefixer))
		.pipe($.header(banner, { pkg: pkg }))
		.pipe(gulp.dest(settings.css.dest))
	;
});

/*
 * Task: Concat and uglify Javascript
 */
gulp.task('js-dev', function(done) {
	return gulp
		.src(settings.js.src)
		.pipe($.jsvalidate().on('error', function(jsvalidate) { console.log(jsvalidate.message); this.emit('end') }))
		.pipe($.sourcemaps.init())
		// .pipe($.concat(settings.js.destFile))
		.pipe($.uglify().on('error', function(uglify) { console.log(uglify.message); this.emit('end') }))
		.pipe($.sourcemaps.write('./'))
		.pipe(gulp.dest(settings.js.dest))
		.pipe($.browserSync.stream())
	;
	done();
});

gulp.task('js-prod', function(done) {
	return gulp
		.src(settings.js.src)
		.pipe($.jsvalidate().on('error', function(jsvalidate) { console.log(jsvalidate.message); this.emit('end') }))
		.pipe($.concat(settings.js.destFile))
		.pipe($.stripdebug())
		.pipe($.uglify().on('error', function(uglify) { console.log(uglify.message); this.emit('end') }))
		.pipe($.header(banner, { pkg: pkg }))
		.pipe(gulp.dest(settings.js.dest))
	;
	done();
});

gulp.task('js-checkout', function(done) {
	return gulp
		.src(settings.jscheckout.src)
		.pipe($.jsvalidate().on('error', function(jsvalidate) { console.log(jsvalidate.message); this.emit('end') }))
		.pipe($.concat(settings.jscheckout.destFile))
		// .pipe($.stripdebug())
		// .pipe($.uglify().on('error', function(uglify) { console.log(uglify.message); this.emit('end') }))
		.pipe($.header(banner, { pkg: pkg }))
		.pipe(gulp.dest(settings.jscheckout.dest))
		.pipe($.browserSync.stream())
	;
	done();
});

gulp.task('js-evaluation', function(done) {
	return gulp
		.src(settings.jsevaluation.src)
		.pipe($.jsvalidate().on('error', function(jsvalidate) { console.log(jsvalidate.message); this.emit('end') }))
		.pipe($.concat(settings.jsevaluation.destFile))
		// .pipe($.stripdebug())
		// .pipe($.uglify().on('error', function(uglify) { console.log(uglify.message); this.emit('end') }))
		.pipe($.header(banner, { pkg: pkg }))
		.pipe(gulp.dest(settings.jsevaluation.dest))
		.pipe($.browserSync.stream())
	;
	done();
});




/*
 * Task: Uglify vendor Javascripts
 */
gulp.task('jsvendor', function() {
	return gulp.src(settings.jsvendor.src)
		.pipe(gulp.dest(settings.jsvendor.dest))
	;
});



gulp.task('cssvendor', function() {
	return gulp.src(settings.cssvendor.src)
		.pipe(gulp.dest(settings.cssvendor.dest))
	;
});



gulp.task('fonts', function() {
	return gulp.src(settings.fonts.src)
		.pipe(gulp.dest(settings.fonts.dest))
	;
});


/*
 * Task: create images
 * TODO: Check if optimization is more effectiv when it is done separately for all different image types(png, svg, jpg)
 */
gulp.task('images', function(done) {
	// optimize all other images
	// TODO: It seems that plugin in don't overwrites existing files in destination folder!??
	return gulp.src(settings.images.src)
		.pipe($.newer(settings.images.dest))
		.pipe($.imagemin(settings.images.options, { verbose: true }))
		.pipe(gulp.dest(settings.images.dest))
	;
	done();
});



gulp.task('icons', function(done) {
	return gulp.src(settings.icons.src)
		.pipe($.newer(settings.icons.dest))
		.pipe($.imagemin(settings.icons.options))
		.pipe(gulp.dest(settings.icons.dest))
	;
	done();
});


/*
 * Task: create sprites(SVG): optimize and concat SVG icons
 */
gulp.task('sprite', function(done) {
	return gulp.src(settings.sprite.src)
		.pipe($.imagemin(settings.sprite.options))
		.pipe($.svgstore({
			inlineSvg: true
		}))
		.pipe($.rename(settings.sprite.destFile))
		.pipe(gulp.dest(settings.sprite.dest))
	;
	done();
});



/*
 * Default TASK: Watch SASS and JAVASCRIPT files for changes,
 * build CSS file and inject into browser
 */
gulp.task('default', gulp.series('css-dev', function() {

	$.browserSync.init(settings.browserSync);

	gulp.watch(settings.css.src, gulp.series('css-dev'));
	gulp.watch(settings.js.src, gulp.series('js-dev'));
	gulp.watch(settings.jscheckout.src, gulp.series('js-checkout'));
	gulp.watch(settings.jsevaluation.src, gulp.series('js-evaluation'));

}));


/**
 * Generate favicons
 */
gulp.task('favicons', function(done) {
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
		.pipe(gulp.dest(settings.favicons.dest))
	;
	done();
});

var exec = require('child_process').exec;

/*
 * Task: Build all
 */
gulp.task('build-dev', gulp.series(build_dev));
gulp.task('build-prod', gulp.series(build_prod));

