const gulp = require('gulp');

const config = require('../../config.js');

// Dependencies
const browserSync = require('browser-sync');
const concat      = require('gulp-concat');
const del         = require('del');
const fs          = require('fs');
const less        = require('gulp-less');
const minifyCSS   = require('gulp-minify-css');
const rename      = require('gulp-rename');
const symlink     = require('gulp-symlink');
const sass        = require('gulp-ruby-sass');
const uglify      = require('gulp-uglify');
const zip         = require('gulp-zip');
const util        = require("gulp-util");
const xml2js      = require('xml2js');
const parser      = new xml2js.Parser();
const replace     = require('gulp-replace');

const baseTask  = 'components.redform';
const extPath   = '../component';
const libPath = extPath + '/libraries/redform';
const mediaPath = extPath + '/media/com_redform';
const pluginsPath = extPath + '/plugins';

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':frontend',
		'clean:' + baseTask + ':backend',
		'clean:' + baseTask + ':libraries',
		'clean:' + baseTask + ':media',
		'clean:' + baseTask + ':plugins'
	]
);

// Clean: frontend
gulp.task('clean:' + baseTask + ':frontend', function() {
	return del(config.wwwDir + '/components/com_redform', {force : true});
});

// Clean: backend
gulp.task('clean:' + baseTask + ':backend', function() {
	return del(config.wwwDir + '/administrator/components/com_redform', {force : true});
});

// Clean: lib
gulp.task('clean:' + baseTask + ':libraries', function() {
	return del(config.wwwDir + '/libraries/redform', {force : true});
});

// Clean: media
gulp.task('clean:' + baseTask + ':media', function() {
	return del(config.wwwDir + '/media/com_redform', {force : true});
});

// Clean: plugins
gulp.task('clean:' + baseTask + ':plugins', function() {
	return del(config.wwwDir + '/plugins/content/redform', {force : true});
});

// Copy
gulp.task('copy:' + baseTask,
	[
		'copy:' + baseTask + ':frontend',
		'copy:' + baseTask + ':backend',
		'copy:' + baseTask + ':libraries',
		'copy:' + baseTask + ':media',
		'copy:' + baseTask + ':plugins'
	],
	function() {
		return true;
});

// Copy: frontend
gulp.task('copy:' + baseTask + ':frontend', ['clean:' + baseTask + ':frontend'], function() {
	return gulp.src(extPath + '/site/**')
		.pipe(gulp.dest(config.wwwDir + '/components/com_redform'));
});

// Copy: backend
gulp.task('copy:' + baseTask + ':backend', ['clean:' + baseTask + ':backend'], function() {
	return (
		gulp.src([
			extPath + '/admin/**'
		])
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redform')) &&
		gulp.src(extPath + '/redform.xml')
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redform')) &&
		gulp.src(extPath + '/install.php')
		.pipe(gulp.dest(config.wwwDir + '/administrator/components/com_redform'))
	);
});

// Copy: libraries
gulp.task('copy:' + baseTask + ':libraries', ['clean:' + baseTask + ':libraries'], function() {
	return gulp.src(libPath + '/**')
		.pipe(gulp.dest(config.wwwDir + '/libraries/redform'));
});

// Copy: media
gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
	return gulp.src(mediaPath + '/**')
		.pipe(gulp.dest(config.wwwDir + '/media/com_redform'));
});

// Copy: plugins
gulp.task('copy:' + baseTask + ':plugins', ['clean:' + baseTask + ':plugins'], function() {
	return gulp.src(pluginsPath + '/**')
		.pipe(gulp.dest(config.wwwDir + '/plugins'));
});


// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:' + baseTask + ':frontend',
		'watch:' + baseTask + ':backend',
		'watch:' + baseTask + ':libraries',
		'watch:' + baseTask + ':media',
		'watch:' + baseTask + ':plugins'
		//'watch:' + baseTask + ':scripts',
		//'watch:' + baseTask + ':less'
	]
);

// Watch: frontend
gulp.task('watch:' + baseTask + ':frontend', function() {
	return gulp.watch(extPath + '/site/**',
	['copy:' + baseTask + ':frontend']);
});

// Watch: backend
gulp.task('watch:' + baseTask + ':backend', function() {
	return gulp.watch([
		extPath + '/admin/**',
		extPath + '/redform.xml',
		extPath + '/install.php'
	],
	['copy:' + baseTask + ':backend']);
});

// Watch: libraries
gulp.task('watch:' + baseTask + ':libraries', function() {
	return gulp.watch(extPath + '/libraries/**',
		['copy:' + baseTask + ':libraries']);
});

// Watch: media
gulp.task('watch:' + baseTask + ':media', function() {
	return gulp.watch(extPath + '/media/**',
		['copy:' + baseTask + ':media']);
});

// Watch: plugins
gulp.task('watch:' + baseTask + ':plugins', function() {
	return gulp.watch(extPath + '/plugins/**',
		['copy:' + baseTask + ':plugins']);
});

gulp.task('update-sites:' + baseTask, function(){
	fs.readFile( extPath + '/redform.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			const version = result.extension.version[0];
			gulp.src(['./update_server_xml/com_redform.xml'])
				.pipe(replace(/<version>(.*)<\/version>/g, "<version>" + version + "</version>"))
				.pipe(gulp.dest('./update_server_xml'));
		});
	});
});