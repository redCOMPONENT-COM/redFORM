const gulp        = require('gulp');
const requireDir  = require('require-dir');
const fs          = require('fs');
const path        = require('path');
const xml2js      = require('xml2js');
const parser      = new xml2js.Parser();
const replace     = require('gulp-replace');
const jgulp       = requireDir('./node_modules/joomla-gulp', {recurse: true});

gulp.task('update-sites', [
    'update-sites:components.redform',
    'update-sites:plugins',
    'update-sites:languages'
]);

gulp.task('update-sites:components',
    jgulp.src.components.getComponentsTasks('update-sites:components')
);

gulp.task('update-sites:plugins',
    jgulp.src.plugins.getPluginsTasks('update-sites:plugins')
);

gulp.task('update-sites:languages', function(){
    const langPath = '../languages';

    fs.readdir(langPath, function(err, files){
        files
            .map(function(file){
                return path.join(langPath, file);
            })
            .filter(function(file) {
                return fs.existsSync(path.join(langPath, file) + '/install.xml');
            })
            .forEach(function(file) {
                const lang = path.basename(file);

                fs.readFile(path.join(langPath, file) + '/install.xml', function(err, data) {
                    parser.parseString(data, function (err, result) {
                        const name = result.extension.name[0];
                        const version = result.extension.version[0];

                        fs.readFile('language_update_site_template.xml', 'utf-8', function(err, content){
                            const text = content
                                .replace(/(##NAME##)/g, name)
                                .replace(/(##VERSION##)/g, version)
                                .replace(/(##LANG##)/g, lang);
                            fs.writeFileSync('./update_server_xml/' + 'redevent_' + lang + '.xml', text);
                        });
                    });
                });
            });
    });
});