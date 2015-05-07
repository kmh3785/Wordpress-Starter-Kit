<?php

// Theme Supports & Filters
	// Hide admin bar when logged in
		add_filter('show_admin_bar', '__return_false');

	// Add featured image functionality
		add_theme_support( 'post-thumbnails' ); 

	// Hide default Rich Text Editor
		add_action('init', 'my_remove_editor_from_post_type');
		function my_remove_editor_from_post_type() {
		    remove_post_type_support( 'page', 'editor' );
		}
// Register menus
	function register_menus() {
	  register_nav_menus(
	    array(
	    	'header-menu' => __( 'Header Menu' ),
	    	'footer-menu' => __( 'Footer Menu' )
	    )
	  );
	}
	add_action( 'init', 'register_menus' );

// Add excerpts to pages
	add_action('init', 'my_custom_init');
	function my_custom_init() {
		add_post_type_support( 'page', 'excerpt' );
	}

// Scripts & Styles
	// Enqueue jQuery from google CDN
		if (!is_admin()) add_action("wp_enqueue_scripts", "my_jquery_enqueue", 11);
		function my_jquery_enqueue() {
			// Comment out the next two lines to use the local copy of jQuery
		   // wp_deregister_script('jquery');
		   // wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js", null, null, false );
		   // wp_register_script('jqueryui', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js", array('jquery'), null, false );

		   wp_enqueue_script('jquery');
		   // wp_enqueue_script('jqueryui');
		}

	// Custom Scripts		
		function scriptsAndStyles() {
			// Register Scripts
				wp_register_script( 'initScripts', get_template_directory_uri() . '/js/scripts.min.js', array('jquery'), null, true );
			// Enqueue Scripts		
				wp_enqueue_script( 'initScripts' );

			// Enqueue Styles
				wp_enqueue_style( 'customStyle', get_template_directory_uri() . '/css/style.min.css');
		};
		add_action( 'wp_enqueue_scripts', 'scriptsAndStyles' );

?>