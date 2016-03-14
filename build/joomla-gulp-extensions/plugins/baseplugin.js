var gulp = require('gulp');

// Load config
var config = require('../../config.js');

// Dependencies
var browserSync = require('browser-sync');
var minifyCSS   = require('gulp-minify-css');
var rename      = require('gulp-rename');
var del         = require('del');
var zip         = require('gulp-zip');
var uglify      = require('gulp-uglify');
var fs          = require('fs');
var xml2js      = require('xml2js');
var parser      = new xml2js.Parser();
var path       	= require('path');

module.exports.addPlugin = function (group, name) {
	var baseTask  = 'plugins.' + group + '.' + name;
	var extPath   = '../plugins/' + group + '/' + name;

	// Clean
	gulp.task('clean:' + baseTask, function() {
		del.sync(config.wwwDir + '/plugins/' + group + '/' + name, {force : true});
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

	// Release: plugin
	gulp.task('release:' + baseTask, function (cb) {
		fs.readFile(extPath + '/' + name + '.xml', function(err, data) {
			parser.parseString(data, function (err, result)	 {
				var version = result.extension.version[0];
				var fileName = config.skipVersion ? 'plg_' + group + '_' + name + '.zip' : 'plg_' + group + '_' + name + '-v' + version + '.zip';

				// We will output where release package is going so it is easier to find
				var releasePath = path.join(config.release_dir, 'plugins');
				console.log('Creating new release file in: ' + releasePath);
				return gulp.src([
						extPath + '/**'
					])
					.pipe(zip(fileName))
					.pipe(gulp.dest(releasePath)).
					on('end', cb);
			});
		});
	});
}
