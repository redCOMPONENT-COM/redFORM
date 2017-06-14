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
	var mediaPath = extPath + '/media/plg_' + group + '_' + name;

	// Clean
	gulp.task('clean:' + baseTask, function() {
		del.sync(config.wwwDir + '/plugins/' + group + '/' + name, {force : true});
	});

	// Clean: Media
	gulp.task('clean:' + baseTask + ':media', function() {
		del.sync(config.wwwDir + '/media/' + name, {force: true});
	});

	// Copy
	gulp.task('copy:' + baseTask, ['clean:' + baseTask, 'copy:' + baseTask + ':media'], function() {
		return gulp.src( extPath + '/**')
			.pipe(gulp.dest(config.wwwDir + '/plugins/' + group + '/' + name));
	});

	// Copy: media
	gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
		console.log(mediaPath);
		return gulp.src([
			mediaPath + '/**'
		])
			.pipe(gulp.dest(config.wwwDir + '/media/plg_' + group + '_' + name))
			.pipe(browserSync.reload({stream:true}));
	});

	// Watch
	gulp.task('watch:' + baseTask,
		[
			'watch:' + baseTask + ':plugin',
			'watch:' + baseTask + ':media'
		],
		function() {
		});

	// Watch: plugin
	gulp.task('watch:' + baseTask + ':plugin', function() {
		gulp.watch(extPath + '/**', ['copy:' + baseTask]);
	});

	// Watch: media
	gulp.task('watch:' + baseTask + ':media', function() {
		gulp.watch([
			extPath + '/media/**'
		], ['copy:' + baseTask + ':media']);
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

	// Update site xml
	gulp.task('update-sites:' + baseTask, function(){
		fs.readFile( extPath + '/' + name + '.xml', function(err, data) {
			parser.parseString(data, function (err, result) {
				const version = result.extension.version[0];

				fs.readFile('plg_update_site_template.xml', 'utf-8', function(err, content){
					if (err)
					{
						console.log(err);

						return;
					}

					var text = content
						.replace(/(##FULLNAME##)/g, 'plg_' + group + '_' + name)
						.replace(/(##ELEMENT##)/g, name)
						.replace(/(##FOLDER##)/g, group)
						.replace(/(##VERSION##)/g, version);
					fs.writeFileSync('./update_server_xml/' + 'plg_' + group + '_' + name + '.xml', text);
				});
			});
		});
	});
};
