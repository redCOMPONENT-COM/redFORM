var gulp = require('gulp');

// Load config
var config = require('../../gulp-config.json');

// Dependencies
var browserSync = require('browser-sync');
var minifyCSS   = require('gulp-minify-css');
var rename      = require('gulp-rename');
var del         = require('del');
var zip         = require('gulp-zip');
var uglify      = require('gulp-uglify');

module.exports.addPlugin = function (group, name) {
	var baseTask  = 'plugins.' + group + '.' + name;
	var extPath   = '../plugins/' + group + '/' + name;

	// Clean
	gulp.task('clean:' + baseTask, function(cb) {
		del(config.wwwDir + '/plugins/' + group + '/' + name, {force : true}, cb);
	});

	// Copy
	gulp.task('copy:' + baseTask, ['clean:' + baseTask], function() {
		return gulp.src( extPath + '/**')
			.pipe(gulp.dest(config.wwwDir + '/plugins/' + group + '/' + name));
	});

	// Watch
	gulp.task('watch:' + baseTask,
		[
			'watch:' + baseTask + ':plugin'
		],
		function() {
		});

	// Watch: plugin
	gulp.task('watch:' + baseTask + ':plugin', function() {
		gulp.watch(extPath + '/**', ['copy:' + baseTask]);
	});

}
