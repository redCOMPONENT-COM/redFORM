var gulp = require('gulp');

var config = require('../../gulp-config.json');

// Dependencies
var browserSync = require('browser-sync');
var concat      = require('gulp-concat');
var del         = require('del');
var fs          = require('fs');
var less        = require('gulp-less');
var minifyCSS   = require('gulp-minify-css');
var rename      = require('gulp-rename');
var symlink     = require('gulp-symlink');
var sass        = require('gulp-ruby-sass');
var uglify      = require('gulp-uglify');
var zip         = require('gulp-zip');

var baseTask  = 'components.redform';
var extPath   = './component';
var libPath = extPath + '/libraries/redform';
var mediaPath = extPath + '/media/com_redform';
var pluginsPath = extPath + '/plugins';

// Clean
gulp.task('clean:' + baseTask,
	[
		'clean:' + baseTask + ':frontend',
		'clean:' + baseTask + ':backend',
		'clean:' + baseTask + ':libraries',
		'clean:' + baseTask + ':media',
		'clean:' + baseTask + ':plugins'
	],
	function() {
		return true;
});

// Clean: frontend
gulp.task('clean:' + baseTask + ':frontend', function(cb) {
	del(config.wwwDir + '/components/com_redform', {force : true}, cb);
});

// Clean: backend
gulp.task('clean:' + baseTask + ':backend', function(cb) {
	del(config.wwwDir + '/administrator/components/com_redform', {force : true}, cb);
});

// Clean: lib
gulp.task('clean:' + baseTask + ':libraries', function(cb) {
	del(config.wwwDir + '/libraries/redform', {force : true}, cb);
});

// Clean: media
gulp.task('clean:' + baseTask + ':media', function(cb) {
	del(config.wwwDir + '/media/com_redform', {force : true}, cb);
});

// Clean: plugins
gulp.task('clean:' + baseTask + ':plugins', function(cb) {
	del(config.wwwDir + '/plugins/content/com_redform', {force : true}, cb);
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
gulp.task('copy:' + baseTask + ':backend', ['clean:' + baseTask + ':backend'], function(cb) {
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
	],
	function() {
		return true;
});

// Watch: frontend
gulp.task('watch:' + baseTask + ':frontend', function() {
	gulp.watch(extPath + '/site/**',
	['copy:' + baseTask + ':frontend']);
});

// Watch: backend
gulp.task('watch:' + baseTask + ':backend', function() {
	gulp.watch([
		extPath + '/admin/**',
		extPath + '/redform.xml',
		extPath + '/install.php'
	],
	['copy:' + baseTask + ':backend']);
});

// Watch: libraries
gulp.task('watch:' + baseTask + ':libraries', function() {
	gulp.watch(extPath + '/libraries/**',
		['copy:' + baseTask + ':libraries']);
});

// Watch: media
gulp.task('watch:' + baseTask + ':media', function() {
	gulp.watch(extPath + '/media/**',
		['copy:' + baseTask + ':media']);
});

// Watch: plugins
gulp.task('watch:' + baseTask + ':plugins', function() {
	gulp.watch(extPath + '/plugins/**',
		['copy:' + baseTask + ':plugins']);
});
