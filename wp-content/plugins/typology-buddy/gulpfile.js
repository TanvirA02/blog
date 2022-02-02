

var pkg = require('./package.json');
var gulp = require('gulp');
var sort = require('gulp-sort');
var wpPot = require('gulp-wp-pot');



gulp.task('translate', function() {

    var translate_path = './languages/' + pkg.name + '.pot';
    var translatable_files = ['./**/*.php', '!node_modules/**', '!.git/**', '!html/**'];
    gulp.src(translatable_files)

        .pipe(sort())
        .pipe(wpPot({
            domain: pkg.name
        }))
        .pipe(gulp.dest(translate_path));

});
