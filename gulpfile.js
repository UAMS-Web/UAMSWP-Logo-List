const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const rename = require('gulp-rename');
const concat = require('gulp-concat');
const notify = require('gulp-notify');
const cssnano = require('cssnano');
const changed = require('gulp-changed');
const comments = require('postcss-discard-comments');
const groupmq = require('gulp-group-css-media-queries');

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