var gulp = require('gulp'),
    jade = require('gulp-jade2php');

gulp.task('compile-page', function() {
    // From cli: jade2php --omit-php-runtime --omit-php-extractor --basedir jade --no-arrays-only --out view/ jade
    gulp.src('jade/**/*.jade')
        .pipe(jade({
            omitPhpRuntime: true,
            omitPhpExtractor: true,
            basedir: __dirname+'/jade',
            arraysOnly: false
        }))
        .pipe(gulp.dest('view'));
});

gulp.task('default', ['compile-page']);