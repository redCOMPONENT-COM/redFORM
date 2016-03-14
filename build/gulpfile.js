var gulp = require('gulp');

var config      = require('./config.js');
var extension   = require('./package.json');

var requireDir 	= require('require-dir');
var zip        	= require('gulp-zip');
var xml2js     	= require('xml2js');
var fs         	= require('fs');
var path       	= require('path');
var ghPages     = require('gulp-gh-pages');
var del         = require('del');

var parser      = new xml2js.Parser();

var jgulp   = requireDir('./node_modules/joomla-gulp', {recurse: true});
var redcore = requireDir('./redCORE/build/gulp-redcore', {recurse: true});
var dir = requireDir('./joomla-gulp-extensions', {recurse: true});

// Override of the release script
gulp.task('release',
	[
		'release:redform',
		'release:plugins',
		'release:languages'
	], function() {
		fs.readFile( '../component/redform.xml', function(err, data) {
			parser.parseString(data, function (err, result) {
				var version = result.extension.version[0];
				var fileName = config.skipVersion ? extension.name + '_ALL_UNZIP_FIRST.zip' : extension.name + '-v' + version + '_ALL_UNZIP_FIRST.zip';
				del.sync(path.join(config.release_dir, fileName), {force: true});

				// We will output where release package is going so it is easier to find
				console.log('Creating all in one release file in: ' + path.join(config.release_dir, fileName));
				return gulp.src([
						config.release_dir + '/**',
						'!' + fileName
					])
					.pipe(zip(fileName))
					.pipe(gulp.dest(config.release_dir));
			});
		});
	}
);

gulp.task('release:redform', ['release:prepare-redform', 'release:prepare-redcore'], function (cb) {
	fs.readFile( '../component/redform.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			var version = result.extension.version[0];
			var fileName = config.skipVersion ? extension.name + '.zip' : extension.name + '-v' + version + '.zip';

			// We will output where release package is going so it is easier to find
			console.log('Creating new release file in: ' + path.join(config.release_dir, fileName));
			return gulp.src('./tmp/**/*')
				.pipe(zip(fileName))
				.pipe(gulp.dest(config.release_dir))
				.on('end', function(){
					del(['tmp']);
					cb();
				});
		});
	});
});

gulp.task('release:prepare-redform', function () {
	return gulp.src([
			'../component/**/*'
		])
		.pipe(gulp.dest('tmp'));
});

gulp.task('release:prepare-redcore', function () {
	return gulp.src([
			'./redCORE/extensions/**/*'
		])
		.pipe(gulp.dest('tmp/redCORE'));
});

gulp.task('release:plugins',
	jgulp.src.plugins.getPluginsTasks('release:plugins')
);

gulp.task('release:languages', function() {
	var langPath = '../languages';
	var releaseDir = path.join(config.release_dir, 'language');

	var folders = fs.readdirSync(langPath)
		.map(function(file){
			return path.join(langPath, file);
		})
		.filter(function(file) {
			return fs.statSync(file).isDirectory();
		});

	var tasks = folders.map(function(directory) {
		return fs.readFile(path.join(directory, 'install.xml'), function(err, data) {
			parser.parseString(data, function (err, result) {
				var lang = path.basename(directory);
				var version = result.extension.version[0];
				var fileName = config.skipVersion ? 'redform_' + lang + '.zip' : 'redform_' + lang + '-v' + version + '.zip';

				return gulp.src([
						directory + '/**'
					])
					.pipe(zip(fileName))
					.pipe(gulp.dest(releaseDir));
			});
		});
	});

	return tasks;
});
