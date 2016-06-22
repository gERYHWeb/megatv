var gulp 		= require('gulp'),
	$           = require('gulp-load-plugins')(),
	del         = require('del'),
	browserSync = require('browser-sync'),
    minifyCSS   = require('gulp-minify-css'),
	prefix      = require('gulp-autoprefixer'),
	sass        = require('gulp-sass'),
	sourcemaps  = require('gulp-sourcemaps');

var reload = browserSync.reload;


//var wiredep = require('wiredep').stream;

//var GulpSSH = require('gulp-ssh');
//var deployConfig = require('./deploy_config.json');
//var gulpSSH = new GulpSSH({
//  ignoreErrors: false,
//  sshConfig: deployConfig
//});



// Helpers
function handleError(err) {
    console.log(err.toString());
    this.emit('end');
}



// Styles Task
// Создаем sourcemaps
// Отлавливаем ошибки
// Добавляем префиксы
gulp.task('styles', function() {
	gulp.src('src/scss/main.scss')
		.pipe(sourcemaps.init())
		.pipe(sass().on('error', sass.logError))
		.on('error', handleError)
		.pipe(prefix({
			browsers: ['IE >= 10', 'last 2 versions']
		}))
		.pipe(sourcemaps.write())
		// .pipe(gulp.dest(_public + _css))
		.pipe(gulp.dest('.tmp/css/'))
		.on('error', handleError)
		.pipe(reload({stream: true}));
});


// Old

// gulp.task('styles', function () {
// 	return gulp.src('app/styles/stylus/main.styl')
// 		.pipe($.sourcemaps.init())
// 		.pipe($.stylus())
// 		.pipe($.postcss([
// 			require('postcss-font-family'),
// 			require('postcss-merge-rules'),
// 			require('postcss-minify-font-weight'),
// 			require('postcss-normalize-url'),
// 			require('postcss-discard-empty'),
// 			require('postcss-will-change'),
// 			require('autoprefixer')({browsers: ['IE >= 10', 'last 2 versions']}),
// 			require('css-mqpacker'),
// 			require('postcss-reporter')
// 		]))
// 		.pipe($.sourcemaps.write())
// 		.pipe(gulp.dest('.tmp/css'))
// 		.pipe(reload({stream: true}));
// });

gulp.task('jscodestyle', function () {
  //return gulp.src(['app/js/**/*.js', '!app/js/vendors/**/*.js'])
	//.pipe(reload({stream: true, once: true}))
	//.pipe($.jshint())
	//.pipe($.jshint.reporter('jshint-stylish'))
	//.pipe($.if(!browserSync.active, $.jshint.reporter('fail')))
	//.pipe($.jscs({fix: true}))
    //.pipe($.jscs.reporter())
    //.pipe($.jscs.reporter('fail'))
	//.pipe($.if(!browserSync.active, $.jshint.reporter('fail')))
});

// Html Task
// Ищем правила useref в .tmp и src
// Если есть CSS, то проходимся csscomb и сжимаем
gulp.task('html', ['styles'], function () {
	// var assets = $.useref.assets({searchPath: ['.tmp', 'app', '.']});

	return gulp.src('src/**/*.html')
		.pipe($.useref({searchPath: ['.tmp', 'src', '.']}))
		// .pipe(assets)
		//.pipe($.if('*.js', $.cache($.uglify())))
		.pipe($.if('*.css', $.csscomb()))
		.pipe($.if('*.css', $.csso()))
		// .pipe(assets.restore())
		.pipe(gulp.dest('public/'));
});


// Images Task
// Оптимизируем изображения
gulp.task('images', function () {
	return gulp.src(['src/img/**/*.{jpg,jpeg,gif,svg,png}', '!src/img/sprites/'])
		.pipe($.imagemin({
			optimizationLevel: 5,
			progressive: true,
			interlaced: true,
			// don't remove IDs from SVGs, they are often used
			// as hooks for embedding and styling
			svgoPlugins: [{cleanupIDs: false, mergePaths: false}]
		}))
		.pipe(gulp.dest('public/img/'));
});


// Svg-sprite Task
gulp.task('make-svg-sprite', ['update-svg-revision'], function () {
	return gulp.src(_src + _images + 'sprites/svg/**/*.svg')
		.pipe($.rename({ prefix: 'icon-' }))
		.pipe($.svgmin({ mergePaths: false }))
		.pipe($.svgstore({ inlineSvg: true }))
		.pipe($.rename({ basename: 'svg_sprite' }))
		.pipe($.size({title: 'make-svg-sprite'}))
		.pipe(gulp.dest('public/img/sprites/'));
});


// Update SVG revision
gulp.task('update-svg-revision', function () {
	return gulp.src('src/js/services/icon-loader.js')
		.pipe($.replace(/var revision = [0-9]{10}/g, 'var revision = ' + Math.floor(Date.now() / 1000)))
		.pipe(gulp.dest('public/js/services/'));
});


// Fonts Task
gulp.task('fonts', function () {
	return gulp.src('src/fonts/**/*.{eot,svg,ttf,woff,woff2}')
		.pipe(gulp.dest('.tmp/fonts/'))
		.pipe(gulp.dest('public/fonts/'));
});


// Flash Task
gulp.task('flash', function () {
	return gulp.src('src/js/vendors/**/*.swf')
		.pipe(gulp.dest('public/js/'));
});


// Other Task
// копируем остальные файлы, кроме html-шаблонов в корневой директории
gulp.task('extras', function () {
	return gulp.src([
		'src/*.*',
		'!src/*.html'
	], {
		dot: true
	}).pipe(gulp.dest('public/'));
});


// Clean Task
gulp.task('clean', del.bind(null, ['.tmp/', 'public/']));


// Serve Task
// Запуск локального сервера с вотчерами.
// До запуска компилируем стили и шрифты
gulp.task('serve', ['styles', 'fonts'], function () {
	browserSync.init({
        // browser  : 'chrome',
        notify   : false,
        port: 9090,
        server: {
            baseDir: ['.tmp/', 'src/'],
            routes: {
                '/bower_components': 'bower_components',
				'/node_modules': 'node_modules'
            }
        }
    });

	gulp.watch([
		'src/*.html', // html-шаблоны
		'src/js/**/*.js',
		'src/img/**/*',
		'.tmp/fonts/**/*'
	]).on('change', reload);

	gulp.watch('src/scss/**/*.scss', ['styles']);
	gulp.watch('src/img/sprites/svg/**/*.svg', ['make-svg-sprite']);
	gulp.watch('src/fonts/**/*', ['fonts']);
	//gulp.watch('bower.json', ['wiredep', 'fonts']);
});

// gulp.task('serve:dist', function () {
// 	browserSync({
// 		notify: false,
// 		port: 9000,
// 		server: {
// 			baseDir: ['dist']
// 		}
// 	});
// });

// gulp.task('serve:test', function () {
// 	browserSync({
// 		notify: false,
// 		port: 9000,
// 		ui: false,
// 		server: {
// 			baseDir: 'test',
// 			routes: {
// 				'/bower_components': 'bower_components'
// 			}
// 		}
// 	});
//
// 	gulp.watch('test/spec/**/*.js').on('change', reload);
// 	gulp.watch('test/spec/**/*.js', ['lint:test']);
// });

// inject bower components
// gulp.task('wiredep', function () {
// 	gulp.src('app/*.html')
// 		.pipe(wiredep({
// 			exclude: ['bootstrap.js'],
// 			ignorePath: /^(\.\.\/)*\.\./
// 		}))
// 		.pipe(gulp.dest('app'));
// });

gulp.task('build', ['jscodestyle', 'html', 'images', 'fonts', 'flash', 'extras'], function () {
	return gulp.src('public/**/*').pipe($.size({title: 'build', gzip: false}));
});

// gulp.task('deploy', $.shell.task([
//   'ssh ' + deployConfig.user + '@' + deployConfig.server,
//   deployConfig.password,
//   'cd ' + deployConfig.path,
//   'git status',
//   'exit'
// ], {
// 	interactive: true
// }));

//gulp.task('deploy', function () {
//  return gulpSSH
//    .shell(['cd ~/MEGATV/public_html/local/templates/megatv/megatv', 'git pull'], {filePath: 'shell.log'})
//    .pipe(gulp.dest('logs'))
//});

gulp.task('default', ['clean'], function () {
	gulp.start('build');
});
