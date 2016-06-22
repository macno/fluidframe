var gulp = require('gulp'),
    exec = require('child_process').exec,
    sass = require('gulp-sass'),
    notify = require('gulp-notify'),
    livereload = require('gulp-livereload'),
    plumber = require('gulp-plumber'),
    sourcemaps = require('gulp-sourcemaps'),
    cleanCSS = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    argv = require('yargs').argv;

// Task di aggiornamento dei tool di sviluppo
gulp.task('dev',['dev-npm','dev-composer', 'dev-bower', 'dev-schema', 'dev-menu']);

// Task di aggiornamento del sito
gulp.task('design-dev',['compile-jade', 'compile-sass']);

// Task di controllo sui tool di sviluppo
gulp.task('dev-watch',function(){
    gulp.watch('package.json',['dev-npm']);
    gulp.watch('composer.json',['dev-composer']);
    gulp.watch('bower.json',['dev-bower']);
    gulp.watch(['model/*.php'],['dev-schema']);
    gulp.watch('viewsrc/jade/**/*.jade',['compile-jade']);
    gulp.watch('scripts/initMenu.php',['dev-menu']);
});

gulp.task('dev-npm',function (cb) {
  exec('md5sum --status -c package.json.md5 2>/dev/null || (md5sum package.json > package.json.md5; npm install)', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

gulp.task('dev-composer', ['dev-npm'], function (cb) {
  exec('md5sum --status -c composer.json.md5 2>/dev/null || (md5sum composer.json > composer.json.md5; composer install)', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

gulp.task('dev-bower',function (cb) {
  exec('md5sum --status -c bower.json.md5 2>/dev/null || (md5sum bower.json > bower.json.md5; bower install)', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

gulp.task('dev-schema',['dev-composer'],function (cb) {
  exec('php scripts/checkschema.php', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

gulp.task('dev-menu',['dev-composer'],function (cb) {
  exec('php scripts/initMenu.php', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

gulp.task('compile-jade', function (cb) {
  exec('node node_modules/jade2php/bin/jade2php --omit-php-runtime --omit-php-extractor --basedir viewsrc/jade --no-arrays-only --out view/ viewsrc/jade/pages', function (err, stdout, stderr) {
      console.log(stdout);
      console.log(stderr);
      cb(err);
    });
});

// Task che si occupa della compilazione degli stili scss e scatena il livereload
gulp.task('compile-sass', function(done) {
    gulp.src('./stylesheets/**/*.scss')
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        .pipe(sourcemaps.init())
        .pipe(sass())
        .on('error', sass.logError)
        .pipe(cleanCSS({ sourceMap: true }))
        .pipe(rename({ extname: '.min.css' }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./stylesheets/'))
        .on('end', done);
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
    gulp.watch('stylesheets/**/*.scss',['compile-sass']);
    gulp.watch('viewsrc/jade-sbadmin2/**/*.jade',['compile-jade']);
    if(!argv.nowatch){
        // quando viene modificato un file viene richiamato la funzione notifyLivereaload
        gulp.watch(['view/*.php','actions/**/*.php','lib/*.php','javascripts/*.js','stylesheets/*.css'],notifyLivereload);
    }
});

// Default Task
// Per sicurezza all'avvio lancio il task 'compile-sass' e inizio a monitorare i cambiamenti
gulp.task('default',['dev','design-dev','watch']); 
