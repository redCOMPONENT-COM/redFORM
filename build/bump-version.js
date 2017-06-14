const gulp        = require('gulp');
const replace     = require('gulp-replace');

gulp.task('bump-version', function(){
	const argv = require('yargs').argv;
	const version = (argv.version === undefined) ? false : argv.version;

	if (!version)
	{
	    console.log('Missing version tag, use --version to specify the version');

        return;
	}

	var paths = [
		"../component/**/*.php",
		"../plugins/**/*.php"
	];

	return gulp.src(paths, {base: "./"})
		.pipe(replace('__deploy_version__', version))
		.pipe(gulp.dest("./"));
});
