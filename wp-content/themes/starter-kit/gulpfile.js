/*!
 * gulp
 * $ npm install gulp-ruby-sass gulp-autoprefixer gulp-minify-css gulp-jshint gulp-concat gulp-uglify gulp-imagemin gulp-notify gulp-rename gulp-livereload gulp-cache del --save-dev
 */
 
// Load plugins
  var gulp = require('gulp'),
      replace = require('gulp-replace'),
      htmlmin = require('gulp-htmlmin'),
      sass = require('gulp-ruby-sass'),
      autoprefixer = require('gulp-autoprefixer'),
      minifycss = require('gulp-minify-css'),
      jshint = require('gulp-jshint'),
      uglify = require('gulp-uglify'),
      imagemin = require('gulp-imagemin'),
      rename = require('gulp-rename'),
      concat = require('gulp-concat'),
      notify = require('gulp-notify'),
      cache = require('gulp-cache'),
      livereload = require('gulp-livereload'),
      del = require('del');
;

// Styles
  gulp.task('sass', function() {
    return sass('src/scss/style.scss', {style: 'expanded'})
      .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
      .pipe(gulp.dest('css'))
      .pipe(rename({ suffix: '.min' }))
      .pipe(minifycss())
      .pipe(gulp.dest('css'))
      .pipe(notify({ message: 'Styles task complete' }));
  });
 
// Scripts
  gulp.task('scripts', function() {
    return gulp.src('src/js/**/*.js')
      .pipe(jshint('.jshintrc'))
      .pipe(jshint.reporter('default'))
      // .pipe(concat('scripts.js'))
      .pipe(gulp.dest('js'))
      .pipe(rename({ suffix: '.min' }))
      .pipe(uglify())
      .pipe(gulp.dest('js'))
      .pipe(notify({ message: 'Scripts task complete' }));
  });
 
// Images
  gulp.task('images', function() {
    return gulp.src('src/img/**/*')
      .pipe(cache(imagemin({ optimizationLevel: 3, progressive: true, interlaced: true })))
      .pipe(gulp.dest('img'))
      .pipe(notify({ message: 'Images task complete' }));
  });

// Minify HTML
  gulp.task('minify', function() {
    return gulp.src('src/*.php')
      .pipe(htmlmin({collapseWhitespace: true}))
      .pipe(gulp.dest(''))
  });

// Replace
  gulp.task('replace', ['minify'], function(){
    gulp.src(['*.php'])
      .pipe(replace('data-bodyclass', '<?php body_class(); ?>'))
      .pipe(gulp.dest(''));
  });
 
// Clean
  gulp.task('clean', function(cb) {
      del(['css', 'js', 'img'], cb)
  });
 
// Default task
  gulp.task('build', ['clean'], function() {
      gulp.start('replace', 'sass', 'scripts', 'images');
  });
 
// Watch
  gulp.task('watch', function() {
   
    // Watch .scss files
    gulp.watch('src/scss/**/*.scss', ['sass']);
   
    // Watch .js files
    gulp.watch('src/js/**/*.js', ['scripts']);
   
    // Watch image files
    gulp.watch('src/img/**/*', ['images']);

    // Watch php files and run replace task [Minify, then replace strings]
    gulp.watch('src/*.php', ['replace']);
   
    // Create LiveReload server
    // livereload.listen();
   
    // Watch any files in dist/, reload on change
    // gulp.watch('src/scss/**/*' ,['sass']).on('change', livereload.changed);
   
  });