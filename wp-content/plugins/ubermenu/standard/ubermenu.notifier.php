<?php

/**
 * Provides a notification everytime the theme is updated
 * Original code courtesy of João Araújo of Unisphere Design - http://themeforest.net/user/unisphere
 */

define( 'UBERMENU_UPDATE_DELAY' , 129600 );
define( 'UBERMENU_NOTIFIER_URL' , 'http://sevensparklabs.com/updates/ubermenu/notifier.xml' );

//define( 'UBERMENU_UPDATE_DELAY' , 20 );
//define( 'UBERMENU_NOTIFIER_URL' , 'http://mavair.local/~chris/updates/test/ubermenu/notifier.xml' );


function ubermenu_update_notifier_bar_menu() {

	global $uberMenu;
	if( !$uberMenu->getSettings()->op( 'update-alerts' ) ) return;

	if( !function_exists( 'simplexml_load_string' ) ) return;
	global $wp_admin_bar, $wpdb;

	if ( !is_super_admin() || !is_admin_bar_showing() ) 
		return;
	
	$xml = ubermenu_get_latest_plugin_version( UBERMENU_UPDATE_DELAY ); //21600); // This tells the function to cache the remote call for 21600 seconds (6 hours)
	$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/ubermenu/ubermenu.php' ); // Get plugin data (current version is what we want)

	if( !$xml ){
		//we can't retrieve the XML file and parse it properly
		return;
	}

	if(version_compare($plugin_data['Version'], $xml->latest) == -1) {
		$wp_admin_bar->add_menu( array( 'id' => 'ubermenu_update_notifier', 'title' => '<span> '.__( 'UberMenu' , 'ubermenu' ).' <span id="ab-updates">'.__( 'New Updates' , 'ubermenu' ).'</span></span>', 'href' => get_admin_url() . 'themes.php?page=uber-menu&updates=1' ) );
	}
}

if( is_admin() ) add_action( 'admin_bar_menu', 'ubermenu_update_notifier_bar_menu', 1000 );


// This function retrieves a remote xml file on my server to see if there's a new update 
// For performance reasons this function caches the xml content in the database for XX seconds ($interval variable)
function ubermenu_get_latest_plugin_version($interval) {
	// remote xml file location
	$notifier_file_url = UBERMENU_NOTIFIER_URL;
	
	$db_cache_field = 'ubermenu-notifier-cache';
	$db_cache_field_last_updated = 'ubermenu-notifier-last-updated';
	$last = get_option( $db_cache_field_last_updated );
	$now = time();
	// check the cache
	if ( !$last || (( $now - $last ) > $interval) ) {
		// cache doesn't exist, or is old, so refresh it
		if( function_exists('curl_init') ) { // if cURL is available, use it...
			$ch = curl_init($notifier_file_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			$cache = curl_exec($ch);
			curl_close($ch);
		} else {
			$cache = file_get_contents($notifier_file_url); // ...if not, use the common file_get_contents()
		}
		
		if ($cache) {			
			// we got good results
			update_option( $db_cache_field, $cache );
			update_option( $db_cache_field_last_updated, time() );			
		}
		// read from the cache file
		$notifier_data = get_option( $db_cache_field );
	}
	else {
		// cache file is fresh enough, so read from it
		$notifier_data = get_option( $db_cache_field );
	}
	
	$use_errors = libxml_use_internal_errors(true);
	$xml = simplexml_load_string($notifier_data); 
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	
	return $xml;
}