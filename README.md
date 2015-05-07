# Wordpress-Starter-Kit

A starter kit for Wordpress development built with Bower, Bourbon, Neat & Gulp.

## What is it?

This is not a starter theme, but rather a framework to quickly build a theme off of.

## What's included?

### Wordpress
- A clean Wordpress core separated from the wp-content directory
- A fresh wp-config.php file
- Commonly used plugins
-- Gravity Forms
-- UberMenu

### Build tools
#### Bower
- Bourbon
- Neat
- normalize.css

#### Gulp
- del
- autoprefixer
- cache
- concat
- htmlmin
- imagemin
- jshint
- livereload
- minify-css
- notify
- rename
- replace
- ruby-sass
- uglify

## Installation
- Clone or download repo
- cd into directory
- npm install

## How to use it
- A basic empty Wordpress theme is created in wp-content/themes/starter-kit
- Do all development in /starter-kit

### Commands

#### gulp sass
Compine and minify style.scss

#### gulp scripts
Minify all scripts in src/js, compile into a single scripts.js file, and minify

#### gulp images
Optimize all images

#### gulp minify
Minify all HTML/PHP
- Note: this command also runs replace to get around some of html-min's quirks with PHP
- To use <?php body_class(); ?>, instead use data-bodyClass and it will be replaced once compiled

#### gulp build
Delete all CSS, JS, and images and do a fresh build

#### gulp watch
Watch all files in src, and compile on change
