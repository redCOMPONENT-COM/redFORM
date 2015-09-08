var gulp = require('gulp');

var config    = require('./gulp-config.json');

var requireDir = require('require-dir');
var browserSync = require('browser-sync');

var jgulp = requireDir('./node_modules/joomla-gulp/src', {recurse: true});
var dir = requireDir('./joomla-gulp-extensions', {recurse: true});

// Browser sync
gulp.task('browser-sync', function() {
	return browserSync({
		proxy: config.browserSyncProxy,
		browser: config.browser
	});
});
