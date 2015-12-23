var gulp = require('gulp'),
    exec = require('child_process').exec,
    sass = require('gulp-sass'),
    notify = require('gulp-notify'),
    livereload = require('gulp-livereload'),
    plumber = require('gulp-plumber'),
    argv = require('yargs').argv;

// Task di aggiornamento dei tool di sviluppo
gulp.task('dev',['dev-npm','dev-composer', 'dev-bower', 'dev-schema']);

// Task di aggiornamento del sito
gulp.task('design',['compile-jade', 'compile-jade-admin', 'compile-sass']);

// Task di controllo sui tool di sviluppo
gulp.task('dev-watch',function(){
    gulp.watch('package.json',['dev-npm']);
    gulp.watch('composer.json',['dev-composer']);
    gulp.watch('bower.json',['dev-bower']);
    gulp.watch(['classes/*.php','db/core.php'],['dev-schema']);
    gulp.watch('./viewsrc/jade/**/*.jade',['compile-jade']);
    gulp.watch('./viewsrc/jade-sbadmin2/**/*.jade',['compile-jade-admin']);
});

gulp.task('dev-npm',function (cb) {
  exec('npm update', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

gulp.task('dev-composer',function (cb) {
  exec('composer update', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

gulp.task('dev-bower',function (cb) {
  exec('bower update', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

gulp.task('dev-schema',function (cb) {
  exec('php scripts/checkschema.php', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

gulp.task('compile-jade',function (cb) {
  exec('jade2php --omit-php-runtime --omit-php-extractor --basedir viewsrc/jade --no-arrays-only --out view/ viewsrc/jade/pages', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

gulp.task('compile-jade-admin',function (cb) {
  exec('jade2php --omit-php-runtime --omit-php-extractor --basedir viewsrc/jade-sbadmin2 --no-arrays-only --out view/ viewsrc/jade-sbadmin2/pages', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

// Task che si occupa della compilazione degli stili scss e scatena il livereload
gulp.task('compile-sass', function() {
    gulp.src('./stylesheets/**/*.scss')
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        .pipe(sass({
            // outputStyle: 'compressed',
            errLogToConsole: true
        }))
        .pipe(gulp.dest('./stylesheets/'))
        .pipe(livereload());
});

// Funzione per avvisare livereload
function notifyLivereload(event) {
    gulp.src(event.path, {read: false})
        .pipe(plumber())
        .pipe(livereload());
}


gulp.task('watch',['dev-watch'], function(){
    livereload.listen();

    // quando viene modificato un file scss viene richiamato il task 'compile-sass'
    gulp.watch('./stylesheets/**/*.scss',['compile-sass']);
    gulp.watch('./viewsrc/jade/**/*.jade',['compile-jade']);
    gulp.watch('./viewsrc/jade-sbadmin2/**/*.jade',['compile-jade-admin']);
    if(!argv.nowatch){
        // quando viene modificato un file viene richiamato la funzione notifyLivereaload
        gulp.watch(['./view/*.php','./actions/**/*.php','./lib/*.php','./js/*.js','./css/*.css'],notifyLivereload);
    }
});

// Default Task
// Per sicurezza all'avvio lancio il task 'compile-sass' e inizio a monitorare i cambiamenti
gulp.task('default',['dev','design','watch']); 
