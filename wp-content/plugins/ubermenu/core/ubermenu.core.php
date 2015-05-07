<?php

define('UBERMENU_NAV_LOCS', 	'wp-mega-menu-nav-locations');
define('UBERMENU_SETTINGS', 	'wp-mega-menu-settings' );
define('UBERMENU_STYLES', 		'wp-mega-menu-styles');


require_once( 'sparkoptions/SparkOptions.class.php' );	//SevenSpark Options Panel
require_once( 'UberOptions.class.php' );					//UberMenu-specific Option class
require_once( 'UberMenu.class.php' );
require_once( 'UberMenuWalkerCore.class.php' );

function uberMenu_direct( $theme_location = 'ubermenu' , $filter = false ){
	global $uberMenu;
	$uberMenu->directIntegration( $theme_location, $filter );
}

/*
 * Get the registered Style Presets as an array of options - for use in callbacks
 */
function ubermenu_getStylePresetOps( $src = '' ){
	global $uberMenu;	
	return $uberMenu->getStylePresetOps( $src );
}

/*
 * Register a style preset with UberMenu - to be called by outside plugins in the uberMenu_register_styles action hook
 */
function ubermenu_registerStylePreset($id, $name, $path , $top = true, $src = 'standard' ){
	global $uberMenu;
	$uberMenu->registerStylePreset( $id, $name, $path, $top , $src );
}

if( !function_exists( 'umssd' ) ):
function umssd($v){
	echo '<pre>';
	print_r( $v );
	echo '</pre>';
}
endif;


function uberMenu_insert_shortcode( $atts ){
	extract(shortcode_atts(array(
		'theme_location' => 'ubermenu'
	), $atts));

	return wp_nav_menu( array( 'theme_location' => $theme_location , 'echo' => false ) );
}
add_shortcode( 'insert_uberMenu' , 'uberMenu_insert_shortcode' );