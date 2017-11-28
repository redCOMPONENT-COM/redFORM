const gulp        = require('gulp');
const requireDir  = require('require-dir');
const fs          = require('fs');
const path        = require('path');
const through      = require('through2');
const pd          = require('pretty-data').pd;
const debug       = require('gulp-debug');

var iniJsons = [];

var stripPrefix = function(name) {
    return name.substr(6);
};

gulp.task('crowdin-conf', ['getAdminFiles', 'getSiteFiles'], function(){
    var content = "\"preserve_hierarchy\": true\n\n";
    content += "\"files\": " + pd.json(JSON.stringify(iniJsons));
	fs.writeFileSync('../crowdin.yml', content)
});

gulp.task('getAdminFiles', function(){
	return gulp.src(['../component/admin/**/*.ini', '../component/modules/admin/**/*.ini', '../component/plugins/**/*.ini', '../plugins/**/*.ini', '../modules/backend/**/*.ini'], {base: '../'})
		.pipe(through.obj(function (file, enc, cb) {
			iniJsons.push({
                "source": "/" + file.relative,
				"translation": "/languages/%locale%/admin/%locale%/%locale%." + stripPrefix(path.basename(file.path))
            });
			cb(null, file);
		}))
});

gulp.task('getSiteFiles', function(){
	return gulp.src(['../component/libraries/**/*.ini', '../component/modules/site/**/*', '../modules/frontend/**/*.ini'], {base: '../'})
		.pipe(through.obj(function (file, enc, cb) {
			iniJsons.push({
				"source": "/" + file.relative,
				"translation": "/languages/%locale%/site/%locale%/%locale%." + stripPrefix(path.basename(file.path))
			});
			cb(null, file);
		}))
});

