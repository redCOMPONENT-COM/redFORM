var gulp = require('gulp');

var extension   = require('./package.json');
var config      = require('./gulp-config.json');

var argv        = require('yargs').argv;

if (argv.wwwDir)
{
	config.wwwDir = argv.wwwDir;
}

var requireDir 	= require('require-dir');
var zip        	= require('gulp-zip');
var xml2js     	= require('xml2js');
var fs         	= require('fs');
var path       	= require('path');
var ghPages     = require('gulp-gh-pages');
var del         = require('del');

var parser      = new xml2js.Parser();

var jgulp   = requireDir('./node_modules/joomla-gulp', {recurse: true});
var redcore = requireDir('./node_modules/gulp-redcore', {recurse: true});
var dir = requireDir('./joomla-gulp-extensions', {recurse: true});

var skipVersion = argv.skipVersion;

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
				var fileName = skipVersion ? extension.name + '_ALL_UNZIP_FIRST.zip' : extension.name + '-v' + version + '_ALL_UNZIP_FIRST.zip';

				// We will output where release package is going so it is easier to find
				console.log('Creating all in one release file in: ' + path.join(config.release_dir, fileName));
				return gulp.src([
						config.release_dir + '/**'
					])
					.pipe(zip(fileName))
					.pipe(gulp.dest(config.release_dir));
			});
		});
	}
);

gulp.task('release:redform', function (cb) {
	fs.readFile( '../component/redform.xml', function(err, data) {
		parser.parseString(data, function (err, result) {
			var version = result.extension.version[0];
			var fileName = skipVersion ? extension.name + '.zip' : extension.name + '-v' + version + '.zip';

			// We will output where release package is going so it is easier to find
			console.log('Creating new release file in: ' + path.join(config.release_dir, fileName));
			return gulp.src([
					'../component/**/*',
					'../redCORE/component/**/*',
					'../redCORE/component/**/.gitkeep',
					'../redCORE/libraries/**/*',
					'../redCORE/libraries/**/.gitkeep',
					'../redCORE/media/**/*',
					'../redCORE/media/**/.gitkeep',
					'../redCORE/modules/**/*',
					'../redCORE/modules/**/.gitkeep',
					'../redCORE/plugins/**/*',
					'../redCORE/plugins/**/.gitkeep',
					'../redCORE/*(install.php|LICENSE|redcore.xml)'
				])
				.pipe(zip(fileName))
				.pipe(gulp.dest(config.release_dir))
				.on('end', cb);
		});
	});
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
				var fileName = skipVersion ? 'redform_' + lang + '.zip' : 'redform_' + lang + '-v' + version + '.zip';

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
