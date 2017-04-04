var gulp = require('gulp');
var base = require('../baseplugin');
var path = require('path');
var composer = require('gulp-composer');
var fs          = require('fs');
var xml2js      = require('xml2js');
var parser      = new xml2js.Parser();
var path       	= require('path');
var zip         = require('gulp-zip');

// Load config
var config = require('../../../config.js');

var name = path.basename(__filename).replace('.js', '');
var group = path.basename(path.dirname(__filename));

base.addPlugin(group, name);

// Override Release: plugin
var baseTask  = 'plugins.' + group + '.' + name;
var extPath   = '../plugins/' + group + '/' + name;

gulp.task('release:' + baseTask, ['composer:' + baseTask], function (cb) {
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

gulp.task('composer:' + baseTask, function(){
	return composer({"working-dir": extPath});
});
