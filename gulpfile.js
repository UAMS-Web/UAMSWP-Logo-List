var gulp = require('gulp');
    sass = require('gulp-sass');
    postcss = require('gulp-postcss');
    autoprefixer = require('autoprefixer'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    cssnano = require('cssnano'),
    changed = require('gulp-changed'),
    comments = require('postcss-discard-comments'),
    groupmq = require('gulp-group-css-media-queries');

var plugins = [
    autoprefixer,
    cssnano,
    // cmq,
    comments({
        removeAllButFirst: true
    })
]

var paths = {
    styles: {
        src: 'assets/scss/app.scss',
        dest: 'assets/css'
    }
}

function style() {
    return gulp.src(paths.styles.src)
        .pipe(changed(paths.styles.dest))
        .pipe(sass.sync().on('error', sass.logError))
        .pipe(concat('app.scss'))
        .pipe(postcss(plugins))
        .pipe(groupmq()) // Group media queries!
        .pipe(rename('app.css'))
        .pipe(gulp.dest(paths.styles.dest))
        .pipe(notify({ message: 'Styles task complete' }));
}

/**
 * Default task executed by running `gulp`
 */
// gulp.task('default', ['watch:sass']);
gulp.task('default', gulp.parallel(style));