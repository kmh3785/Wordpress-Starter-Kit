<?php

/* Load Required Files */
require_once( 'UberMenuStandard.class.php' );
require_once( 'UberMenuWalker.class.php' );					//Handles Menu Walkers for UberMenu front end and Menu Management Backend
require_once( 'ubermenu.shortcodes.php' );					//Adds useful shortcodes
require_once( 'stylegenerator/StyleGenerator.class.php' ); 	//Helps generate user-defined CSS styles

require_once( 'tiptour/TipTour.class.php' );				//WordPress ToolTips
require_once( 'ubermenu.notifier.php' );



/*
 * For backwards compatibility
 */
function uberMenu_easyIntegrate(){
	UberMenuStandard::easyIntegrate();	
}
