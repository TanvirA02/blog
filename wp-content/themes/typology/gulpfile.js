var gulp = require('gulp'),
	gutil = require('gulp-util'),
	minifycss = require('gulp-minify-css'),
	less = require('gulp-less'),
	notify = require('gulp-notify'),
	zip = require('gulp-zip'),
	autoprefixer = require('gulp-autoprefixer'),
	sort = require('gulp-sort'),
	wpPot = require('gulp-wp-pot');
    var remToPx = require('gulp-rem-to-px');

var plugins = require("gulp-load-plugins")({
	pattern: ['gulp-*', 'gulp.*'],
	replaceString: /\bgulp[\-.]/
});

var pkg = require('./package.json');

var browserSync = require('browser-sync');
var reload = browserSync.reload;

var paths = {

	scripts: {
		src: 'src/js/',
		dest: 'assets/js/',
		watch: 'src/js/**/*.js'
	},

	styles: {
		src: 'src/css/',
		dest: 'assets/css/',
		vendor: 'src/css/*.css',
	},

	less: {
		src: 'src/less/',
		dest: 'assets/css/',
		watch: 'src/less/**/*.less',
	},

    editor:{
        dest: 'assets/css/admin'
    }

};

var appFiles = {
	scripts: [paths.scripts.src + 'vendor/*.js'],
	mainScript: paths.scripts.src + 'main.js',
	styles: [paths.styles.src + '*.css'],
	less: paths.less.src + "/**/*.less",
	lessStyle: paths.less.src + "main.less",
	mainStyle: paths.styles.dest + "main.css",
    editorStyle: paths.editor.dest + "editor-style.css",
    editorLess: paths.less.src + "editor.less"
};


gulp.task('vendorScripts', function() {

	return gulp.src(appFiles.scripts)
		.pipe(gulp.dest(paths.scripts.dest))
		.pipe(notify({
			message: 'Vendor Scripts',
			onLast: true
		}));

});

gulp.task('mainScript', function() {

	return gulp.src(appFiles.mainScript)
		.pipe(gulp.dest(paths.scripts.dest))
		.pipe(notify({
			message: 'Main Script',
			onLast: true
		}));

});

gulp.task('minScripts', function() {

	var arr = appFiles.scripts;
	arr.push(appFiles.mainScript);

	return gulp.src(arr)
		.pipe(plugins.concat('min.js'))
		.pipe(plugins.uglify())
		.pipe(gulp.dest(paths.scripts.dest))
		.pipe(notify({
			message: 'Min Scripts',
			onLast: true
		}));

});

gulp.task('vendorStyles', function() {

	return gulp.src(appFiles.styles)
		.pipe(gulp.dest(paths.styles.dest))
		.pipe(notify({
			message: 'Vendor Styles',
			onLast: true
		}));

});

gulp.task('mainStyle', function() {

	return gulp.src(appFiles.lessStyle)
		.pipe(less())
		.pipe(plugins.concat('main.css'))
		.pipe(autoprefixer({
			remove: false,
			browsers: ['last 4 version', '> 1%', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4']
		}))
		.pipe(gulp.dest(paths.styles.dest))
		.pipe(notify({
			message: 'Main Style',
			onLast: true
		}));

});

gulp.task('minStyles', function() {



	
		var arr = '';
		arr = appFiles.styles;
		arr.push(appFiles.mainStyle);



		return gulp.src(arr)
			.pipe(plugins.concat('min.css'))
			.pipe(minifycss())
			.pipe(gulp.dest(paths.styles.dest))
			.pipe(reload({
				stream: true
			}))
			.pipe(notify({
				message: 'Min Styles',
				onLast: true
			}));



});

gulp.task('editorStyle', function() {

    return gulp.src(appFiles.editorLess)
        .pipe(less())
        .pipe(plugins.concat('editor-style.css'))
        .pipe(autoprefixer({
            remove: false,
            add: true,
            browsers: ['last 10 version', '> 1%', 'Safari 5', 'Safari 8', 'ie 8', 'ie 9', 'ie 10', 'opera 12.1', 'ios 6', 'android 4']
        }))
        .pipe(remToPx({
    		fontSize : 10
    	}))
        .pipe(gulp.dest(paths.editor.dest))
        .pipe(notify({
            message: 'Editor Styles',
            onLast: true
        }));

});


// browser-sync task for starting the server.
gulp.task('browser-sync', function() {
    //watch files
    var files = [
    './assets/css/min.css'
    ];

    //initialize browsersync
    browserSync.init(files, {
    //browsersync with a php server
    proxy: "localhost/typology/",
    notify: false,
    injectChanges: true // this is new
    });
});


// gulp.task('watch', function() {
// 	gulp.watch(appFiles.mainScript, ['mainScript', 'minScripts']);
// 	gulp.watch(appFiles.less, ['minStyles','editorStyle']);
// });

gulp.task('translate', function() {

    var translate_path = './languages/' + pkg.name + '.pot';
    var translatable_files = ['./**/*.php', '!node_modules/**', '!.git/**', '!html/**'];
    return gulp.src(translatable_files)

        .pipe(sort())
        .pipe(wpPot({
            domain: pkg.name
        }))
        .pipe(gulp.dest(translate_path));

});

// gulp.task('default', function() {
// 	gulp.start('vendorScripts');
// 	gulp.start('mainScript');
// 	gulp.start('minScripts');
// 	gulp.start('vendorStyles');
// 	// gulp.start('mainStyle');
// 	gulp.start('minStyles');
//     gulp.start('editorStyle');
// 	gulp.start('watch');
// 	//gulp.start('browser-sync');
// });

gulp.task('zip', function () {
    return gulp.src(['**', '!.git', '!.git/**', '!src', '!src/**', '!node_modules', '!node_modules/**', '!.gitignore', '!package.json', '!gulpfile.js'],  {base: '../.'})
        .pipe(zip('typology.zip'))
        .pipe(gulp.dest(''));
});


/* Task - styles */
gulp.task('styles', gulp.series(gulp.parallel('vendorStyles', 'mainStyle'), 'editorStyle', 'minStyles'));

/* Task - scripts */
gulp.task('scripts', gulp.series(gulp.parallel('vendorScripts', 'mainScript'), 'minScripts'));


/* Task - watch */
gulp.task('watch', function() {
    gulp.watch(paths.less.watch, gulp.series('styles'));
    gulp.watch(paths.styles.vendor, gulp.series('styles'));
    gulp.watch(paths.scripts.watch, gulp.series('scripts'));
});


/* Task - default */
gulp.task('default', gulp.series(gulp.parallel('scripts', 'styles'), 'watch'));