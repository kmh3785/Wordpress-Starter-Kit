<?php

require_once UBERMENU_DIR . 'pro/menuitems/menuitems.pro.php';
require_once UBERMENU_DIR . 'pro/search.php';
require_once UBERMENU_DIR . 'pro/maps.php';
require_once UBERMENU_DIR . 'pro/shortcodes.php';
require_once UBERMENU_DIR . 'pro/admin/admin.pro.php';
require_once UBERMENU_DIR . 'pro/toolbar.php';
require_once UBERMENU_DIR . 'pro/fonts.php';
require_once UBERMENU_DIR . 'pro/widgets.php';
require_once UBERMENU_DIR . 'pro/widget.php';
require_once UBERMENU_DIR . 'pro/diagnostics/diagnostics.php';
//require_once UBERMENU_DIR . 'pro/admin/migration.php';

if( is_admin() ) require_once UBERMENU_DIR . 'pro/updates/updater.php';



function ubermenu_pro_load_assets(){

	$assets = UBERMENU_URL . 'pro/assets/';
	
	//Load Core UberMenu CSS unless disabled
	if( ubermenu_op( 'load_ubermenu_css' , 'general' ) != 'off' ){
		wp_deregister_style( 'ubermenu' );
		//wp_dequeue_style( 'ubermenu' );
		wp_enqueue_style( 'ubermenu' , $assets.'css/ubermenu.min.css' , false , UBERMENU_VERSION );
	}
}

add_action( 'wp_enqueue_scripts' , 'ubermenu_pro_load_assets' , 22 );