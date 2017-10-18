var gulp      = require('gulp');
var config    = require('../../../gulp-config.json');
var extension = require('../../../package.json');

// Dependencies
var browserSync = require('browser-sync');
var del         = require('del');
var fs          = require('fs');
var path        = require('path');
var rename      = require('gulp-rename');
var minifyCSS   = require('gulp-minify-css');
var uglify      = require('gulp-uglify');
var less        = require('gulp-less');

var modName   = "latest_submissions";
var modFolder = "mod_redform" + "_" + modName;
var modBase   = "admin";
var assetsFolder = '../src/assets/modules/' + modBase + "/" +  modFolder;

var baseTask  = 'modules.backend.' + modName;
var extPath   = '../component/modules/' + modBase + '/' + modFolder;
var mediaPath = extPath + '/media/' + modFolder;

function getFolders(dir){
    return fs.readdirSync(dir)
        .filter(function(file){
                return fs.statSync(path.join(dir, file)).isDirectory();
            }
        );
}

// Clean
gulp.task('clean:' + baseTask,
    [
        'clean:' + baseTask + ':module',
        'clean:' + baseTask + ':media',
        'clean:' + baseTask + ':language'
    ],
    function() {
    });

// Clean: Module
gulp.task('clean:' + baseTask + ':module', function() {
    return del(config.wwwDir + '/administrator/modules/' + modFolder, {force: true});
});

// Clean: Media
gulp.task('clean:' + baseTask + ':media', function() {
    return del(config.wwwDir + '/media/' + modFolder, {force: true});
});

// Clean: language. Here only to remove old translations
gulp.task('clean:' + baseTask + ':language', function() {
    return del(config.wwwDir + '/language/**/*.' + modFolder + '*', {force: true});
});

// Copy: Module
gulp.task('copy:' + baseTask,
    [
        'clean:' + baseTask,
        'copy:' + baseTask + ':module',
        'copy:' + baseTask + ':media'
    ],
    function() {
    });

// Copy: Module
gulp.task('copy:' + baseTask + ':module', ['clean:' + baseTask + ':module'], function() {
    console.log(extPath);
    return gulp.src([
            extPath + '/**',
            '!' + extPath + '/media',
            '!' + extPath + '/media/**'
        ])
        .pipe(gulp.dest(config.wwwDir + '/administrator/modules/' + modFolder));
});

// Copy: Media
gulp.task('copy:' + baseTask + ':media', ['clean:' + baseTask + ':media'], function() {
    return gulp.src([
            mediaPath + '/**'
        ])
        .pipe(gulp.dest(config.wwwDir + '/media/' + modFolder));
});

// Sass
gulp.task('less:' + baseTask, function () {
    return gulp.src(assetsFolder + '/less/style.less')
        .pipe(less({paths: [assetsFolder + '/less']}))
        .pipe(gulp.dest(mediaPath + '/css'))
        .pipe(gulp.dest(config.wwwDir + '/media/' + modFolder + '/css'))
        .pipe(minifyCSS())
        .pipe(rename(function (path) {
            path.basename += '.min';
        }))
        .pipe(gulp.dest(mediaPath + '/css'))
        .pipe(gulp.dest(config.wwwDir + '/media/' + modFolder + '/css'));
});

// Scripts
gulp.task('scripts:' + baseTask, function () {
    return gulp.src([
            assetsFolder + '/js/*.js'
        ])
        .pipe(gulp.dest(config.wwwDir + '/media/' + modFolder + '/js'))
        .pipe(uglify())
        .pipe(rename(function (path) {
            path.basename += '.min';
        }))
        .pipe(gulp.dest(mediaPath + '/js'))
        .pipe(gulp.dest(config.wwwDir + '/media/' + modFolder + '/js'));
});

// Watch
gulp.task('watch:' + baseTask,
    [
        'watch:' + baseTask + ':module',
        'watch:' + baseTask + ':less',
        'watch:' + baseTask + ':scripts',
        'watch:' + baseTask + ':media'
    ],
    function() {
    });

// Watch: Module
gulp.task('watch:' + baseTask + ':module', function() {
    gulp.watch([
            extPath + '/**/*',
            '!' + extPath + '/media',
            '!' + extPath + '/media/**/*'
        ],
        ['copy:' + baseTask + ':module', browserSync.reload]);
});

// Watch: Media
gulp.task('watch:' + baseTask + ':media', function() {
    gulp.watch([
            mediaPath + '/**'
        ],
        ['copy:' + baseTask + ':media', browserSync.reload]);
});

// Watch: LESS
gulp.task('watch:' + baseTask + ':less', function() {
    gulp.watch([
        assetsFolder + '/less/**/*.less'
    ], ['less:' + baseTask]);
});

// Watch: Scripts
gulp.task('watch:' + baseTask + ':scripts', function() {
    gulp.watch([
        assetsFolder + '/js/*.js'
    ], ['scripts:' + baseTask]);
});
