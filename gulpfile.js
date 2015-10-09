var gulp = require('gulp');

var requireDir = require('require-dir');
var browserSync = require('browser-sync');

var jgulp = requireDir('./node_modules/joomla-gulp/src', {recurse: true});
var dir = requireDir('./joomla-gulp-extensions', {recurse: true});

