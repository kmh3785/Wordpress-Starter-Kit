<?php

class UberMenuStandard extends UberMenu{



	/**
	 * Set up the framework
	 *
	 * In addition to the core functionality, set up special functions for the plugin walkthrough,
	 * Style Generator, Control Panel, thumbnails, sidebars, Easy Integration
	 */
	function __construct(){
		
		$this->editionURL = plugins_url().'/'.str_replace(basename( __FILE__ ),"",plugin_basename( __FILE__ ) );

		parent::__construct();



		//ADMIN
		if( is_admin() ){

			//Save Style Generator
			add_action( 'sparkoptions_update_settings_'.UBERMENU_SETTINGS , array( $this , 'saveStyleGenerator' ) , 10 , 1 );
			
			//Create the welcome tour
			$this->createTour();


			//Add "Settings" and "Support Guide" links to the Plugins page
			add_filter( 'plugin_action_links', array( $this , 'pluginActionLinks' ), 10, 2);

			//AJAX clear of show thanks box
			add_action( 'wp_ajax_ubermenu_showThanksCleared', array( $this , 'showThanksCleared_callback' ) );
			
			//UberMenu Thank You panel
			add_action( 'sparkoptions_before_settings_panel_'. UBERMENU_SETTINGS , array( $this , 'showThanks' ) );
			
			
		}


		//Add Thumbnail Support
		add_action( 'after_setup_theme', array( $this , 'addThumbnailSupport' ), 500 );	//go near the end, so we don't get overridden


		//Add Sidebars
		add_action( 'init', array( $this , 'registerSidebars' ), 500);	//Note that on the admin side, this runs before settings are updated

		//UberMenu Easy Integration
		add_shortcode( 'uberMenu_easyIntegrate' , array( 'UberMenuStandard' , 'easyIntegrate' ) );
		
		if( $this->settings->op( 'wpmega-easyintegrate' ) ){ 
			add_action( 'init', array( $this , 'registerThemeLocation' ) );
		}
		
	}

	function init(){
		parent::init();
		
		//Filters
		add_filter( 'wp_nav_menu_args' , array( $this , 'megaMenuFilter' ), 2000 );  	//filters arguments passed to wp_nav_menu

		//load_plugin_textdomain( 'ubermenu' );
	}


	/**
	 * Handle loading the CSS and JS assets.
	 *
	 * In addition to core functionality, handle Style Generator CSS, loading on login pages, 
	 * and optional IE Fix
	 * 
	 */
	function loadAssets(){

		parent::loadAssets();

		if( !is_admin() && 
		  ( $this->settings->op( 'wpmega-load-on-login' ) || !in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) ) {
			

			//CSS in HEAD
			add_action( 'wp_head', array( $this  , 'insertCSS' ), 100 );
			

			//IE Fix
			if( $this->settings->op( 'wpmega-iefix' ) ) add_action( 'wp_head', array( $this , 'ieFix' ) );	//You can safely disable this if you are including it elsewhere
		}
	}


	/**
	 * In addition to preset, load custom stylesheet or Style Generator
	 */
	function loadCSS(){

		parent::loadCSS();

		//Load Custom stylesheet
		if( $this->settings->op( 'wpmega-style' ) == 'custom' ){
			wp_enqueue_style('ubermenu-custom', $this->baseURL.'custom/custom.css', false, UBERMENU_VERSION, 'all');
		}		
		// Load Generated Stylesheet 
		else if(   $this->settings->op( 'wpmega-style' ) == 'inline'	//Using Generator 
				&& $this->settings->op( 'save-style-gen-to-file' )		//Saving to file
				&& $this->settings->op( 'use-gen-skin' ) ){				//File Generated Successfully
		
			wp_enqueue_style('ubermenu-generated-skin', $this->editionURL.'stylegenerator/skin.css', false, UBERMENU_VERSION, 'all');
		}

	}

	/* Load additional javascript */
	function loadJS(){

		parent::loadJS();

		if( $this->settings->op( 'load-google-maps') )
			wp_enqueue_script( 'google-maps', 'http://maps.googleapis.com/maps/api/js?sensor=false' , array( 'jquery' ), false, true ); 
	
		//Load Custom stylesheet
		if( $this->settings->op( 'custom-js' ) ){
			wp_enqueue_script('ubermenu-custom', $this->baseURL.'custom/custom.js', array( 'jquery' ), false, true );
		}


	}

	/*
	 * Insert StyleGenerator-generated CSS in the site head
	 */
	function insertCSS(){
		
		$css = '';
		$from = array();
	
		//Gather special CSS settings
		
		$menuW = $this->settings->op( 'wpmega-container-w' );
		if( !empty( $menuW ) ) {
			$css.= "\n\n/* Menu Width - UberMenu Advanced Settings */\n";
			$css.= '#megaMenu{ width: '.$menuW.'px; max-width:100%; }';
		}
		
		$innerMenuW = $this->settings->op( 'inner-menu-width' );
		if( !empty( $innerMenuW ) ){
			$css.= "\n\n/* Inner Menu Width - used for centering - UberMenu Advanced Settings */\n";
			$css.= '#megaMenu ul.megaMenu{ max-width: '.$innerMenuW.'px; }';
		}
		
		
		$verticalSubmenuWidth = $this->settings->op( 'vertical-submenu-w');
		if( !empty( $verticalSubmenuWidth ) ){
			$css.= "\n/* Vertical Submenu Width */\n";
			$css.= '#megaMenu.megaMenuVertical ul.megaMenu li.ss-nav-menu-mega.ss-nav-menu-item-depth-0 ul.sub-menu-1{ width: '.$verticalSubmenuWidth.'px; max-width: '.$verticalSubmenuWidth. 'px; }';
		}

		$imagePadding = $this->settings->op( 'wpmega-image-width' );
		if( is_numeric( $imagePadding ) ){
			$imagePadding+= apply_filters( 'ubermenu_image_text_padding' , 7 );
			$css.= "\n/* Image Text Padding */\n";
			$css.= "#megaMenu .ss-nav-menu-with-img > a > .wpmega-link-title, #megaMenu .ss-nav-menu-with-img > a > .wpmega-link-description, #megaMenu .ss-nav-menu-with-img > a > .wpmega-item-description, #megaMenu .ss-nav-menu-with-img > span.um-anchoremulator > .wpmega-link-title, #megaMenu .ss-nav-menu-with-img > span.um-anchoremulator > .wpmega-link-description, #megaMenu .ss-nav-menu-with-img > span.um-anchoremulator > .wpmega-item-description{\n";
			$css.= "  padding-left: {$imagePadding}px;\n";
			$css.= "}\n";
		}

		/* //Using clearfix instead
		$imageHeight = $this->settings->op( 'wpmega-image-height');
		if( !empty( $imageHeight ) ){
			$css.= "\n/* Image item min-height *//*\n";
			$css.= "#megaMenu ul.megaMenu ul.sub-menu .ss-nav-menu-with-img > a, #megaMenu ul.megaMenu ul.sub-menu .ss-nav-menu-with-img > span.um-anchoremulator { min-height: {$imageHeight}px; }";
		}
		*/

		$customTweaks = $this->settings->op( 'wpmega-css-tweaks' );
		if( !empty( $customTweaks ) ) {
			$css.= "\n\n/* Custom Tweaks - UberMenu Style Configuration Settings */\n";
			$css.= stripslashes( $customTweaks );
		}
	
		//Append CSS from Generator, if using inline style & no external stylesheet
		if( $this->settings->op( 'wpmega-style' ) == 'inline'
			&& !$this->settings->op( 'use-gen-skin' ) ){
			$css = "/* Style Generator Styles */\n". $this->getGeneratorCSS() . "\n\n".$css;
		}

		$css = apply_filters( 'uberMenu_insertCSS' , $css );

		$css = trim( $css );
	
		//If we've got anything to print, print it!
		if( !empty($css) ){
			?>

<!-- UberMenu CSS - Controlled through UberMenu Options Panel 
================================================================ -->
<style type="text/css" id="ubermenu-style-generator-css">
<?php echo $css; ?>
	
</style>
<!-- end UberMenu CSS -->
		
			<?php
		}
		
	}

	function ieFix(){
		?>
		<!--[if lt IE 8]>
		<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE8.js"></script>
		<![endif]-->
		<?php 
	}


	function registerThemeLocation(){
		register_nav_menu('ubermenu' , __( 'UberMenu [Easy Integration]' ));
	}

	static function easyIntegrate($atts = array(), $data = ''){
		extract(shortcode_atts(array(
			'echo'	=>	'true',
		), $atts));
		
		$echo = $echo == 'false' ? false : true;
		
		$menu = wp_nav_menu( array( 'theme_location' => 'ubermenu' , 'megaMenu' => true , 'echo' => $echo ) );
		
		if( !$echo ) return $menu;
	}

	function getGeneratorCSS(){
		$styles = get_option( UBERMENU_STYLES );
		if( is_array( $styles ) ) $styles = $styles['wpmega-style-css-code'];	//This is how it worked in 1.x, but now it's just the CSS
		return stripslashes( $styles );
	}








	/*
	 * Add Support for Thumbnails on Menu Items
	 *
	 * This function adds support without override the theme's support for thumbnails
	 * Note we could just call add_theme_support('post-thumbnails') without specifying a post type,
	 * but this would make it look like users could set featured images on themes that don't support it
	 * so we don't want that.
	 */
	function addThumbnailSupport(){
	
		global $_wp_theme_features;
		$post_types = array( 'nav_menu_item' );
	
		$alreadySet = false;
	
		//Check to see if some features are already supported so that we don't override anything
		if( isset( $_wp_theme_features['post-thumbnails'] ) && is_array( $_wp_theme_features['post-thumbnails'][0] ) ){
			$post_types = array_merge($post_types, $_wp_theme_features['post-thumbnails'][0]);
		}
		//If they already tuned it on for EVERY type, then we don't need to do anything more
		elseif( isset( $_wp_theme_features['post-thumbnails'] ) && $_wp_theme_features['post-thumbnails'] == 1 ){
			$alreadySet = true;
		}
	
		if(!$alreadySet) add_theme_support( 'post-thumbnails' , $post_types );
	
		add_post_type_support( 'nav_menu_item' , 'thumbnail' ); //wp33
	}




	function getWalker(){
		return new UberMenuWalker();
	}

	function getMenuArgs( $args ){

		$args = parent::getMenuArgs( $args );
		
		if( $this->settings->op( 'wpmega-html5' ) )						$args['container'] 		= 'nav';
		else 															$args['container'] 		= 'div';
		
		if( $this->settings->op( 'wpmega-menubar-full' ) )				$args['container_class'].= ' megaFullWidth';
		
		if( $this->settings->op( 'wpmega-orientation' ) == 'vertical' )	$args['container_class'].= ' megaMenuVertical';
		else 															$args['container_class'].= ' megaMenuHorizontal';
		
		
	
		if( $this->settings->op( 'wpmega-autoAlign' ) )					$args['container_class'].= ' wpmega-autoAlign';
		
		
		if( $this->settings->op( 'wpmega-remove-conflicts' ) )			$args['container_class'].= ' wpmega-noconflict';
		
		if( $this->settings->op( 'center-menuitems' ) )					$args['container_class'].= ' megaCenterMenuItems';
		if( $this->settings->op( 'center-menubar' ) )					$args['container_class'].= ' megaCenterMenubar';
		if( $this->settings->op( 'enable-clearfix' ) )					$args['container_class'].= ' megaClear';
		if( $this->settings->op( 'center-inner-menu' ) )				$args['container_class'].= ' megaCenterInner';
		
		if( $this->settings->op( 'wpmega-minimizeresidual' ) ){
																		$args['menu_id'] = 'megaUber';
																		$args['container_class'].= ' megaMinimizeResiduals';
		}		
		if( $this->settings->op( 'reset-div-span-styling' ) )			$args['container_class'].= ' megaResetStyles';
		
		return $args;
	}



	/*
	 * Apply options to the Menu via the filter
	 */
	function megaMenuFilter( $args ){

		//Only print the menu once
		if( $this->count > 0 ) return $args;
		
		if( isset( $args['responsiveSelectMenu'] ) ) return $args;
		if( isset( $args['filter'] ) && $args['filter'] === false ) return $args;
		
		//Don't do anything in IE6
		if( $this->settings->op( 'no-ie6' ) && 
			strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 6') !== false ) return $args;
	
		//Check to See if this Menu Should be Megafied
		$location = $args['theme_location'];
		$activeLocs = get_option( UBERMENU_NAV_LOCS, array() ); 
		
		if( !isset( $args['preview'] ) ){	//preview automatically passes through
			//STRICT
			if( $this->settings->op( 'wpmega-strict' ) ) {
				//Strict Mode requires the location to be set and for that location to be activated
				//If location is empty or location is not activated, return
				if( ( empty( $location ) || !in_array( $location, $activeLocs ) ) && ( !isset( $args['megaMenu'] ) || $args['megaMenu'] != true ) ){
					return $args;
				} 
			
				//Check to make sure the menu exists
				$nav_menu_locations = get_nav_menu_locations();
				if( !isset( $nav_menu_locations[ $location ] ) ){
					//If this was supposed to be a Mega Menu, explain the problem to the user.
					if( isset( $args['megaMenu'] ) && $args['megaMenu'] == true ) printf( __( "Please activate UberMenu Easy Integration and set a menu in the %s theme location in Appearance > Menus" , 'ubermenu' ), $location );
					return $args;
				}
			}
			//LENIENT
			else{
				//In the Event that the LOCATION is empty, that means the theme author has not 
				//created the menu using the theme_location parameter properly, so we'll go ahead and megafy the menu
				if( isset( $args['megaMenu'] ) && $args['megaMenu'] != true && !empty( $location ) && !in_array( $location, $activeLocs ) ){
					return $args;	//megaMenu setting for manual wp_nav_menu
				}
			}
		}

		$this->count++;

		$items_wrap 	= '<ul id="%1$s" class="%2$s" data-theme-location="'.$location.'">%3$s</ul>'; //This is the default, to override any stupidity
		
		if( $this->settings->op( 'responsive-menu-toggle' ) ) 
			$items_wrap = '<div id="megaMenuToggle" class="megaMenuToggle">'.$this->settings->op( 'responsive-menu-toggle-text' ).'&nbsp; <span class="megaMenuToggle-icon"></span></div>'.$items_wrap;

		$args = $this->getMenuArgs( $args );

		return $args;
	}










	/* ADMIN */


	function adminInit(){

		parent::adminInit();


		add_action( 'admin_head', array( $this , 'addActivationMetaBox' ) );

	}

	function loadAdminNavMenuJS(){

		parent::loadAdminNavMenuJS();

		add_action( 'admin_head', array( $this  , 'insertCSS' ), 100 );
	}

	

	/*
	 * Add the Activate Uber Menu Locations Meta Box to the Appearance > Menus Control Panel
	 */
	function addActivationMetaBox(){
		if ( wp_get_nav_menus() )
			add_meta_box( 'nav-menu-theme-megamenus', __( 'Activate Uber Menu Locations' , 'ubermenu' ), array( $this , 'showActivationMetaBox' ) , 'nav-menus', 'side', 'high' );
	}

	/*
	 * Generates the Activate Uber Menu Locations Meta Box
	 */
	function showActivationMetaBox(){
	
		/* This is just in case JS is not working.  It'll only save the last checked box */
		if( isset( $_POST['megaMenu-locations'] ) && $_POST['megaMenu-locations'] == 'Save'){
			$data = $_POST['wp-mega-menu-nav-loc'];
			$data = explode(',', $data);		
			update_option( UBERMENU_NAV_LOCS, $data );
			echo 'Saved Changes';
		}
		
		$active = get_option( UBERMENU_NAV_LOCS, array());
		
		echo '<div class="megaMenu-metaBox">';	
		echo '<p class="howto">'.__( 'Select the Menu Locations to Megafy.  This must be activated for any Mega Menu Options to affect that Menu Location.', 'ubermenu' ).'</p>';
		
		echo '<form>';
		
		$locs = get_registered_nav_menus();
		
		foreach($locs as $slug => $desc){		
			echo '<label class="menu-item-title" for="megaMenuThemeLoc-'.$slug.'">'.
					'<input class="menu-item-checkbox" type="checkbox" value="'.$slug.'" id="megaMenuThemeLoc-'.$slug.'" name="wp-mega-menu-nav-loc" '.
					checked( in_array( $slug, $active ), true, false).'/>'.
					$desc.'</label>';
		}
		
		echo '<p class="button-controls">'.
				'<img class="waiting" src="'.esc_url( admin_url( 'images/wpspin_light.gif' ) ).'" alt="" style="display:none;"/>'.
				'<input id="wp-mega-menu-navlocs-submit" type="submit" class="button-primary" name="megaMenu-locations" value="'.__( 'Save' , 'ubermenu' ).'" />'.
				'</p>';
		
		echo '</form>';
		
		if( !$this->settings->op( 'wpmega-strict' ) ){
			echo '<p class="howto">'. __( 'If more than 1 menu is being megafied in your theme, turn on Strict Mode in Appearance > UberMenu > '.
					'Theme Integration.', 'ubermenu' ).'</p>';
		}

		echo '<p>'.__( 'Note you can only have 1 UberMenu per page.', 'ubermenu' ).'</p>';

		echo '</div>';
	}

	/*
	 * Update the Locations when the Activate Uber Menu Locations Meta Box is Submitted
	 */
	function updateNavLocs_callback(){
		
		$data = $_POST['data'];	
		$data = explode(',', $data);
		
		update_option( UBERMENU_NAV_LOCS, $data);
		
		echo $data;		
		die();		
	}

	
	/* Registering Sidebars */
	function registerSidebars(){
		
		if(function_exists('register_sidebars')){
			
			$numSidebars = $this->settings->op( 'wpmega-sidebars' );
			$sidebarNames = $this->settings->op( 'sidebar-names' );
			
			if(!empty($numSidebars)){

				if( $sidebarNames != '' ){

					$sidebarNames = explode( ',' , $sidebarNames );

					for( $k = 0; $k < $numSidebars; $k++ ){

						$name = __( 'UberMenu Widget Area', 'ubermenu' ) . ' '.($k+1);
						if( isset( $sidebarNames[$k] ) ){
							$name = trim( $sidebarNames[$k] ) . ' [UberMenu]';
						}
						$id = 'wpmega-sidebar';
						
						if( $k > 0 ) $id = 'wpmega-sidebar-'.($k+1);

						register_sidebar(array(
							'name'          => $name,
							'id'            => $id,
							'before_title'  => '<h2 class="widgettitle">',
							'after_title'   => '</h2>',
							'description'	=> sprintf( __( 'Widget area will span width of submenu.  Add multiple widgets to create columns.  Select "%s" in your Menu Item under Appearance > Menus to add this widget area to your menu.', 'ubermenu' ), $name )
						));	
					}
				}
				else{

					if($numSidebars == 1){
						register_sidebar(array(
							'name'          => __( 'UberMenu Widget Area 1' , 'ubermenu' ),
							'id'            => 'wpmega-sidebar',
							'before_title'  => '<h2 class="widgettitle">',
							'after_title'   => '</h2>',
							'description'	=> __( 'Widget area will span width of submenu.  Add multiple widgets to create columns.  Select "UberMeu Widget Area 1" in your Menu Item under Appearance > Menus to add this widget area to your menu.', 'ubermenu' )
						));				
					}
					else{
						register_sidebars( $numSidebars, array(
							'name'          => __( 'UberMenu Widget Area %d' , 'ubermenu' ),
							'id'            => 'wpmega-sidebar',
							'before_title'  => '<h2 class="widgettitle">',
							'after_title'   => '</h2>',
							'description'	=> __( 'Widget area will span width of submenu.  Add multiple widgets to create columns.  Select this widget area in your Menu Item\'s "Display a Widget Area" option under Appearance > Menus to add this widget area to your menu.', 'ubermenu' )
						));
					}
				}

			}
		}
	}

	/*
	 * Show a sidebar select box
	 */
	function sidebarSelect( $id , $_val ){
		
		$fid = 'edit-menu-item-sidebars-'.$id;
		$name = 'menu-item-sidebars['.$id.']';
		$selection = $_val; //get_post_meta( $id, '_menu_item_sidebars', true);
		
		$ops = $this->sidebarList();
		if( empty( $ops ) ) return '';
		
		$html = '<select id="'.$fid.'" name="'.$name.'" class="edit-menu-item-sidebars">';
		
		$html.= '<option value=""></option>';
		foreach( $ops as $opVal => $op ){
			$selected = $opVal == $selection ? 'selected="selected"' : '';
			$html.= '<option value="'.$opVal.'" '.$selected.' >'.$op.'</option>';
		}
				
		$html.= '</select>';
		
		return $html;
	}

	/*
	 * List the available sidebars
	 */
	function sidebarList(){
		
		$sb = array();
		
		$numSidebars = $this->settings->op( 'wpmega-sidebars' );
		$sidebarNames = $this->settings->op( 'sidebar-names' );
		$sidebarCustomNamed = false;
		if( $sidebarNames != '' ){
			$sidebarNames = explode( ',' , $sidebarNames );
			$sidebarCustomNamed = true;
		}
		
		for( $k = 0; $k < $numSidebars; $k++ ){
			
			$id = 'wpmega-sidebar';
			if( $k > 0 ) $id.='-'.($k+1);

			$val = __( 'UberMenu Widget Area', 'ubermenu' ) . ' '.($k+1);
			if( $sidebarCustomNamed && isset( $sidebarNames[$k] ) ){
				$val = trim( $sidebarNames[$k] );
			}
			$sb[$id] = $val;
		}
		return $sb;
	}

	/* 
	 * Show a sidebar
	 */
	function sidebar($name){
		
		if(function_exists('dynamic_sidebar')){
			ob_start();
			echo '<ul class="um-sidebar" id="wpmega-'.sanitize_title($name).'">';
			dynamic_sidebar($name);		
			echo '</ul>';
			return ob_get_clean();
		}
		return 'none';
	}

	/*
	 * Count the number of widgets in a sidebar area
	 */
	function sidebarCount($index){
		
		global $wp_registered_sidebars, $wp_registered_widgets;
	
		if ( is_int($index) ) {
			$index = "sidebar-$index";
		} else {
			$index = sanitize_title($index);
			foreach ( (array) $wp_registered_sidebars as $key => $value ) {
				if ( sanitize_title($value['name']) == $index ) {
					$index = $key;
					break;
				}
			}
		}
	
		$sidebars_widgets = wp_get_sidebars_widgets();
	
		if ( empty($wp_registered_sidebars[$index]) || !array_key_exists($index, $sidebars_widgets) || !is_array($sidebars_widgets[$index]) || empty($sidebars_widgets[$index]) )
			return false;
	
		$sidebar = $wp_registered_sidebars[$index];
		
		return count($sidebars_widgets[$index]);
	}








	function showMenuOptions( $item_id ){

		global $uberMenu;
		$settings = $uberMenu->getSettings();

		parent::showMenuOptions( $item_id );

		$this->showCustomMenuOption(
			'notext', 
			$item_id, 
			array(
				'level' => '0-plus', 
				'title' => __( 'Remove the Navigation Label text from the link.  Can be used, for example, with image-only links.', 'ubermenu' ), 
				'label' => __( 'Disable Text', 'ubermenu' ), 
				'type' 	=> 'checkbox', 
			)
		);

		

		$this->showCustomMenuOption(
			'floatRight', 
			$item_id, 
			array(
				'level' => '0', 
				'title' => __( 'Float the menu item to the right edge of the menu bar.', 'ubermenu' ), 
				'label' => __( 'Align Menu Item to Right Edge', 'ubermenu' ), 
				'type' 	=> 'checkbox', 
			)
		);







		//CONTENT OVERRIDES AND WIDGET AREAS
		
		if ( $settings->op( 'wpmega-shortcodes' ) ) {
			$this->showCustomMenuOption(
				'shortcode', 
				$item_id, 
				array(
					'level' => '0-plus', 
					'title' => __( 'Display custom content in this menu item.  This input accepts shortcodes so you can display things like contact forms, search boxes, or galleries.  Check "Disable Link" above to display only this content, instead of a link.', 'ubermenu' ), 
					'label' => __( 'Custom Content (Content Override)', 'ubermenu' ), 
					'type' 	=> 'textarea', 
				)
			);
		}


		$minSidebarLevel = 1;
		if( $settings->op( 'wpmega-top-level-widgets' ) ){
			$minSidebarLevel = 0;
		}
		
		$this->showCustomMenuOption(
			'sidebars', 
			$item_id, 
			array(
				'level' => $minSidebarLevel . '-plus', 
				'title' => __( 'Select the widget area to display', 'ubermenu' ), 
				'label' => __( 'Display a Widget Area', 'ubermenu' ), 
				'type' => 'sidebarselect', 
			)
		);


		do_action( 'ubermenu_extended_menu_item_options' , $item_id , $this );	



		global $post_ID;
		$post_ID = $item_id;

		$iframeSrc = get_upload_iframe_src('image') . '&amp;tab=type&amp;width=640&amp;height=589';
		//media-upload.php?post_id=<?php echo $item_id; &amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=589
		$wp_mega_link = "Set Thumbnail";
		$wp_mega_img = $uberMenu->getImage( $item_id );
		if (!empty($wp_mega_img)) {
			$wp_mega_link = $wp_mega_img;
			$ajax_nonce = wp_create_nonce("set_post_thumbnail-$item_id");
			$wp_mega_link .= '<div class="remove-item-thumb" id="remove-item-thumb-' . $item_id . '"><a href="#" id="remove-post-thumbnail-' . $item_id . '" onclick="wpmega_remove_thumb(\'' . $ajax_nonce . '\', ' . $item_id . ');return false;">' . esc_html__( 'Remove image' , 'ubermenu' ) . '</a></div>';
		}


		?>

		<p class="wpmega-custom-all"><a class="thickbox set-menu-item-thumb button uber_clear" id="set-post-thumbnail-<?php echo $item_id;?>" href="<?php echo $iframeSrc;?>" title="Set Thumbnail"><?php
						echo $wp_mega_link;
					?></a></p>

		<?php

	}











	/* Options Menu */

	function optionsMenu(){

		$sparkOps = parent::optionsMenu();


		/* Basic Standard */
		$basic = 'basic-config';

		$sparkOps->addRadio( $basic, 
					'wpmega-orientation',
					__( 'Orientation', 'ubermenu' ),
					__( 'Choose a vertical (top to bottom) or horizontal (left to right) menu.  Note that if your horizontal menu is forced to have a narrow width by your theme, it may appear to be vertically oriented.', 'ubermenu' ),
					array(
						'horizontal'	=>	__( 'Horizontal', 'ubermenu' ),
						'vertical'		=>	__( 'Vertical', 'ubermenu' )
					),
					'horizontal',
					'',
					2
					);
		
		$sparkOps->addCheckbox( $basic,
					'wpmega-menubar-full',
					__( 'Expand Menu Bar Full Width?', 'ubermenu' ),
					__( 'Enable to have the menu bar fill its container.  Disable to size the menu bar to its contents. ', 'ubermenu' ),
					'on',
					'',
					2
					);


		


		/* VERTICAL MENUS */
		
		$weight = 25;
		$sparkOps->addSubHeader( $basic, 
					'wpmega-vertical-header',
					__( 'Vertical Menu Sizing', 'ubermenu' ),
					'',
					'',
					$weight
					);
		
		$sparkOps->addTextInput( $basic,			
					'vertical-submenu-w',
					__( 'Vertical Mega Submenu Width', 'ubermenu' ),
					__( 'Since the width of a submenu is constrained by the container that the UberMenu is placed in, you must set the width manually if you want it to be wider.', 'ubermenu' ),
					'',
					'spark-minitext',
					'px',
					$weight
					);




		/* Advanced Content */
		$descConfig = 'description-config';
		$sparkOps->setPanelName( $descConfig, __( 'Descriptions, Shortcodes, Widgets', 'ubermenu' ) );
		$sparkOps->addSubHeader( $descConfig,
					'wpmega-othercontent-header',
					__( 'Advanced Content Capabilities: Content Overrides, Shortcodes, Widgets', 'ubermenu' )
					);
		
		$sparkOps->addCheckbox( $descConfig,
					'wpmega-shortcodes',
					__( 'Allow Content Overrides', 'ubermenu' ),
					__( 'Content Overrides allow you to include non-links in the Mega Menu.  You can use shortcodes, which will allow you to put items like contact forms, search boxes, or galleries in your Menus', 'ubermenu' ),
					'off'
					);	

		$sparkOps->addCheckbox( $descConfig,
					'title-shortcodes',
					__( 'Allow Shortcodes in Navigation Label &amp; Description', 'ubermenu' ),
					__( 'Enable this setting to use shortcodes inside menu item titles and descriptions', 'ubermenu' ),
					'off'
					);	
		
		$sparkOps->addTextInput( $descConfig,
					'wpmega-sidebars',
					__( 'Number of Widget Areas', 'ubermenu' ),
					__( 'Enter the number of widget areas/sidebars that should be generated for the UberMenu.  You can then add widgets through the normal means. <span class="ss-infobox ss-infobox-warning">This value must be 1 or greater to use widgets</span>', 'ubermenu' ),
					'0',
					'spark-minitext'
					);	

		$sparkOps->addTextArea( $descConfig,
					'sidebar-names',
					__( 'Widget Area Names', 'ubermenu' ),
					__( 'You can name your widget areas in order to better keep track of them.  Separate names with commas, e.g. <em>Contact Us Widget Area, Product List Widget Area</em>.   But keep in mind, you only need one widget area per row, which can container multiple widgets to create columns.', 'ubermenu' ),
					'',
					''
					);		
		
		$sparkOps->addCheckbox( $descConfig,
					'wpmega-top-level-widgets',
					__( 'Allow Top-Level Widgets', 'ubermenu' ),
					__( 'Turn this on to allow placing widgets in the top level, in addition to the submenu.  <span class="spark-infobox spark-infobox-warning">Remember, if you place widgets in the top level, they will always be visible.  In many cases, this will break your layout.</span>', 'ubermenu' ),
					'off'
					);	
		
		$sparkOps->addCheckbox( $descConfig,
					'load-google-maps',
					__( 'Load Google Maps', 'ubermenu' ),
					__( 'Loads the Google Maps API so you can use maps in your menu with the provided shortcode.  Note that jQuery Enhancements must be enabled in order to utilize the map shortcode.', 'ubermenu' ),
					'off'
					);	







		/*
		 * IMAGE SETTINGS PANEL
		 */
		$imageConfig = 'image-config';
		$sparkOps->registerPanel( $imageConfig, __( 'Images', 'ubermenu' ) , 30 );			
		
		
		$sparkOps->addInfobox( $imageConfig,
						'wpmega-images',
						'',
						__( 'Control how your images are sized and processed.', 'ubermenu' )
					);
		
		$sparkOps->addCheckbox( $imageConfig,			
					'wpmega-resizeimages',
					__( 'Resize Images', 'ubermenu' ),
					__( 'Should the images all be sized the same?  <span class="spark-infobox">This must be enabled if you wish to use Timthumb.</span>', 'ubermenu' ),
					'on'
					);
					
		$sparkOps->addTextInput( $imageConfig,
					'wpmega-image-width',
					__( 'Image Width', 'ubermenu' ),
					__( 'Setting this value is important for proper spacing in the menu, even if you aren\'t resizing your images.', 'ubermenu' ),
					'16',
					'spark-minitext',
					'px'
					);
		
		$sparkOps->addTextInput( $imageConfig,		
					'wpmega-image-height',
					__( 'Image Height', 'ubermenu' ),
					'',
					'16',
					'spark-minitext',
					'px'
					);
		
		$sparkOps->addCheckbox( $imageConfig,
					'wpmega-usetimthumb',
					__( 'Use Timthumb', 'ubermenu' ),
					__( 'Use TimThumb to automatically scale and crop images to the preferred width and height.  <span class="spark-infobox">You must follow the instructions in the UberMenu Support Guide to setup TimThumb for use.  Note that some hosts, especially shared hosting, will not allow you to use timthumb on their servers.</span>', 'ubermenu' ),
					'off'
					);		

		$sparkOps->addCheckbox( $imageConfig,
					'wpmega-disable-img-tooltips',
					__( 'Disable Image Title Tooltips', 'ubermenu' ),
					__( 'Enable this to prevent UberMenu from printing the <em>title</em> attribute on img tags, preventing the tooltip from displaying on hover.', 'ubermenu' ),
					'off'
					);	







		/* STYLE SETTINGS */
		$styleConfig = 'style-config';

		$sparkOps->addTextarea( $styleConfig,
					'wpmega-css-tweaks',
					__( 'Custom CSS Tweaks', 'ubermenu' ),
					__( 'Best for minor CSS tweaks.  Want to write a custom style?  Use <a href="http://sevenspark.com/docs/ubermenu-custom-skin" target="_blank">custom.css</a>.  <br/><br/><span class="spark-infobox">These styles will be applied regardless of the options above</span>', 'ubermenu' ),
					''
					);






		/* STYLE GENERATOR */
		$styleGen = 'style-gen';
		
		$sparkOps->registerPanel( $styleGen, __( 'Style Generator', 'ubermenu' ) , 50 );
		
		$sparkOps->addInfobox( $styleGen, 
					'style-gen-application-alert', 
					'',
					__( 'Please note that unless you set the <strong>Style Application</strong> to <strong>Style Generator</strong> '.
						'in <em>UberMenu > Style Configuration</em>, these settings will have no effect.  '.
						'<br/><br/><strong>Knowledgebase: <a target="_blank" href="http://sevenspark.com/docs/ubermenu-style-generator" title="Permalink to Style Generator" rel="bookmark">Style Generator</a></strong>', 'ubermenu' ),
					'spark-infobox-warning'
					);
		
		$sparkOps->addCheckbox( $styleGen,
					'save-style-gen-to-file',
					__( 'Save Custom CSS to file', 'ubermenu' ),
					__( 'Will attempt to save to a file, rather than including in site head.', 'ubermenu' )
					);
		
		$sparkOps->addHidden( $styleGen , 
					'use-gen-skin',
					'off' 
					);	
		
		
		//Menu Bar
		$sparkOps->addSubHeader( $styleGen,
					'menu-bar-header',
					__( 'Menu Bar', 'ubermenu' )
					);
					
		/*$sparkOps->addInfobox( $styleGen, 
					'colorpickers-info', 
					'',
					/*__( 'When using the color pickers, (1) click the input box, (2) select your color, (3) <strong>click '.
						'the colorwheel button in the lower right corner</strong> to set the color.  Do <strong>not</strong> '.
						'include a # before your color in the input box.', 'ubermenu' ) 
					);
					*/
		
		//@menubarColorStart, @menubarColorEnd
		$sparkOps->addColorPicker( $styleGen,
					'menu-bar-background',
					__( 'Menu Bar Background', 'ubermenu' ),
					'',
					true
					);
		
		//@menubarBorderColor
		$sparkOps->addColorPicker( $styleGen,
					'menu-bar-border-color',
					__( 'Menu Bar Border', 'ubermenu' ),
					'',
					false
					);
		
		//@menubarRadius
		$sparkOps->addTextInput( $styleGen, 
					'menu-bar-border-radius',
					__( 'Menu Bar Border Radius', 'ubermenu' ),
					__( 'CSS3: only works in modern browsers.', 'ubermenu' ),
					'0',
					'minitext',
					'px'
					);
					
		//Top Level
		
		$sparkOps->addSubHeader( $styleGen,
					'top-level-header',
					__( 'Top Level', 'ubermenu' )
					);
		
		//@topLevelFontSize
		$sparkOps->addTextInput( $styleGen, 
					'top-level-item-font-size',
					__( 'Font Size', 'ubermenu' ),
					__( 'Example: <em>12px</em> or <em>1.5em</em>', 'ubermenu' ),
					'12px',
					'minitext'
					);	
		 
		//@topLevelColor
		$sparkOps->addColorPicker( $styleGen,
					'top-level-item-font-color',
					__( 'Font Color', 'ubermenu' ),
					'',
					false
					);
		
		//@topLevelColorHover
		$sparkOps->addColorPicker( $styleGen,
					'top-level-item-font-color-hover',
					__( 'Font Color [Hover]', 'ubermenu' ),
					'',
					false
					);
		
		//@currentColor
		$sparkOps->addColorPicker( $styleGen,
					'top-level-item-font-color-current',
					__( 'Font Color [Current Menu Item]', 'ubermenu' ),
					'',
					false
					);
		
		//@topLevelTextShadow
		$sparkOps->addColorPicker( $styleGen,
					'top-level-item-text-shadow',
					__( 'Text Shadow', 'ubermenu' ),
					'',
					false
					);
	
		//@topLevelTextShadowHover
		$sparkOps->addColorPicker( $styleGen,
					'top-level-item-text-shadow-hover',
					__( 'Text Shadow [Hover]', 'ubermenu' ),
					'',
					false
					);
					
					
		//@topLevelTextTransform
		$sparkOps->addSelect( $styleGen,			
					'top-level-text-transform',
					__( 'Text Transform', 'ubermenu' ),
					'',
					array(
						'uppercase'	=>	'uppercase',
						'capitalize'=>	'capitalize',
						'lowercase'	=>	'lowercase',
						'none'		=>	'none'
					),
					'none'
					);
					
		//@topLevelTextWeight
		$sparkOps->addSelect( $styleGen,
					'top-level-text-weight',		
					__( 'Font Weight', 'ubermenu' ),
					'',
					array(
						'normal'	=>	'normal',
						'bold'		=>	'bold',
					),
					'bold'
					);
					
	
		//@topLevelDividerColor
		$sparkOps->addColorPicker( $styleGen,
					'top-level-item-border',
					__( 'Item Divider Color', 'ubermenu' ),
					'',
					false
					);
	
		//@topLevelPaddingX
		$sparkOps->addTextInput( $styleGen, 
					'top-level-item-padding-x',
					__( 'Horizontal Padding', 'ubermenu' ),
					'',
					'15',
					'minitext',
					'px'
					);	
					
		//@topLevelPaddingY
		$sparkOps->addTextInput( $styleGen, 
					'top-level-item-padding-y',
					__( 'Vertical Padding', 'ubermenu' ),
					'',
					'12',
					'minitext',
					'px'
					);	
	
		//@topLevelGlowOpacity
		$sparkOps->addTextInput( $styleGen,
					'top-level-item-glow-opacity',
					__( 'Glow Opacity', 'ubermenu' ),
					__( 'The top and left edge are given a lighter glow to add depth.  Set a decimal between 0 and 1. For lighter menus, set a value closer to 1.  For darker menus, set a number closer to 0.  '.
						'<span class="spark-infobox spark-infobox-warning">If you have a "white border" on the top and left that you can\'t remove, set this to 0.  See also the [Hover] version.</span>', 'ubermenu' ),
					'.9',
					'minitext'
					);
		
		//@topLevelBackgroundHoverStart, @topLevelBackgroundHoverEnd
		$sparkOps->addColorPicker( $styleGen,
					'top-level-item-background-hover',
					__( 'Background Color [Hover]', 'ubermenu' ),
					'',
					true
					);
		
		//@topLevelGlowOpacityHover
		$sparkOps->addTextInput( $styleGen,
					'top-level-item-glow-opacity-hover',
					__( 'Glow Opacity [Hover]', 'ubermenu' ),
					__( 'The top and left edge are given a lighter glow to add depth.  Set a decimal between 0 and 1. For lighter menus, set a value closer to 1.  For darker menus, set a number closer to 0.', 'ubermenu' ),
					'.9',
					'minitext'
					);
	
		/*$sparkOps->addColorPicker( $styleGen,
					'top-level-item-border-hover',
					'Tab and Dropdown Border Color [Hover]',
					'',
					false
					);*/
	
		/*$sparkOps->addTextInput( $styleGen,
					'top-level-item-border-radius',
					'Tab and Dropdown Border Radius [Hover]',
					'',
					'0',
					'minitext',
					'px'
					);*/
	
		
		//Sub Menus
		$sparkOps->addSubHeader( $styleGen,
					'sub-level-header',
					__( 'Sub Menu Level', 'ubermenu' )	
		);
		
		//@subMenuBorderColor
		$sparkOps->addColorPicker( $styleGen,
					'sub-menu-border',
					__( 'Submenu Border Color', 'ubermenu' ),
					'',
					false
					);
		
		//@submenuColorStart
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-background',
					__( 'Dropdown Background Color', 'ubermenu' ),
					__( 'Set the second color to create a vertical gradient.  Leave blank for a flat color.', 'ubermenu' ),
					true
					);
	
		//@subMenuColor
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-item-font-color',
					__( 'Submenu Font Color', 'ubermenu' ),
					__( 'The default font color for the submenus - overridden by header and item colors', 'ubermenu' ),
					false
					);	

		//@subMenuTextShadow
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-text-shadow',
					__( 'Submenu Text Shadow Color', 'ubermenu' ),
					'',
					false
					);	

		//@subMenuBoxShadow
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-box-shadow',
					__( 'Drop Shadow Color', 'ubermenu' ),
					'',
					false
					);
					
		//@SubMenuMinColWidth
		$sparkOps->addTextInput( $styleGen,
					'sub-level-column-width',
					__( 'Minimum Column Width', 'ubermenu' ),
					__( 'You can set the minimum width of the columns in the dropdown.  Useful to align columns in multi-row layouts.', 'ubermenu' ),
					'100',
					'minitext',
					'px'
					);
					

		
		//Submenu Headers
		$sparkOps->addSubHeader( $styleGen,
					'sub-level-header-headers',
					__( 'Sub Menu Headers', 'ubermenu' )	
		);
		
		//@subHeaderColor
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-header-font-color',
					__( 'Header Font Color', 'ubermenu' ),
					'',
					false
					);	

		//@subHeaderColorHover
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-header-font-color-hover',
					__( 'Header Link Font Color [Hover]', 'ubermenu' ),
					'',
					false
					);	
		
		//@subHeaderSize
		$sparkOps->addTextInput( $styleGen,
					'sub-level-header-font-size',
					__( 'Header Font Size', 'ubermenu' ),
					__( 'Example: 12px or 1.5em', 'ubermenu' ),
					'12px',
					'minitext',
					''
					);
		
		//@subHeaderWeight
		$sparkOps->addSelect( $styleGen,
					'sub-level-header-font-weight',		
					__( 'Header Font Weight', 'ubermenu' ),
					'',
					array(
						'normal'	=>	'normal',
						'bold'		=>	'bold',
					),
					'bold'
					);

		//@subHeaderBorderBottom (1)					
		$sparkOps->addSelect( $styleGen,			
					'sub-level-header-border-style',
					__( 'Header Underline Style', 'ubermenu' ),
					'',
					array(
						'dotted'	=>	'dotted',
						'dashed'	=>	'dashed',
						'solid'		=>	'solid',
						'none'		=>	'none',
					),
					'dotted'
					);
		
		//@subHeaderBorderBottom (2)					
		$sparkOps->addColorPicker( $styleGen,			
					'sub-level-header-border-color',
					__( 'Header Underline Color', 'ubermenu' ),
					'',
					false
					);



		//Submenu Links
		$sparkOps->addSubHeader( $styleGen,
					'sub-level-header-links',
					__( 'Sub Menu Links', 'ubermenu' )
					);
		
		//@subLinkColor
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-link-font-color',
					__( 'Submenu Link Font Color', 'ubermenu' ),
					'',
					false
					);	
		
		//@subLinkColorHover
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-link-font-color-hover',
					__( 'Submenu Link Font Color [Hover]', 'ubermenu' ),
					'',
					false
					);
		
		//@subLinkSize
		$sparkOps->addTextInput( $styleGen,
					'sub-level-link-font-size',
					__( 'Submenu Link Font Size', 'ubermenu' ),
					__( 'Example: 12px or 1.5em', 'ubermenu' ),
					'12px',
					'minitext',
					''
					);
		
		//@subLinkBackground
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-link-background',
					__( 'Item Background Color', 'ubermenu' ),
					'',
					false
					);	
		
		//@subLinkBackgroundHover
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-link-background-hover',
					__( 'Item Background Color [Hover]', 'ubermenu' ),
					'',
					false
					);	
		
		
							
		//Miscellaneous
		$sparkOps->addSubHeader( $styleGen,
					'sub-level-other-header',
					__( 'Miscellaneous'	, 'ubermenu' )
		);
		
		//@highlightColor
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-highlight-color',
					__( 'Highlight Color', 'ubermenu' ),
					__( 'Items that are highlighted will be displayed this color', 'ubermenu' ),
					false
					);
		
		//@descriptionSize
		$sparkOps->addTextInput( $styleGen,
					'menu-description-size',
					__( 'Description Font Size', 'ubermenu' ),
					__( 'Example: 12px or 1.5em', 'ubermenu' ),
					'9px',
					'minitext',
					''
					);
		
		//@descriptionColor: #bbb;
		$sparkOps->addColorPicker( $styleGen,
					'menu-description-color',
					__( 'Description Font Color', 'ubermenu' ),
					'',
					false
					);
			
		//@descriptionTransform
		$sparkOps->addSelect( $styleGen,			
					'description-transform',
					__( 'Description Transform', 'ubermenu' ),
					'',
					array(
						'uppercase'	=>	'uppercase',
						'capitalize'=>	'capitalize',
						'lowercase'	=>	'lowercase',
						'none'		=>	'none'
					),
					'none'
					);
		
		//@topLevelArrowColor
		$sparkOps->addColorPicker( $styleGen,
					'top-level-arrow-color',
					__( 'Top Level Arrow Color', 'ubermenu' ),
					'',
					false
					);
		
		//@flyoutArrowColor
		$sparkOps->addColorPicker( $styleGen,
					'sub-level-arrow-color',
					__( 'Flyout Sub Level Arrow Color', 'ubermenu' ),
					'',
					false
					);
		

		//@searchTextColor
		$sparkOps->addColorPicker( $styleGen,
					'search-text-color',
					__( 'Top Level Searchbar Text Color', 'ubermenu' ),
					'',
					false
					);

		//@searchTextBackground
		$sparkOps->addColorPicker( $styleGen,
					'search-text-background',
					__( 'Top Level Searchbar Text Background', 'ubermenu' ),
					'',
					false
					);

		//@searchSubmitTextColor
		$sparkOps->addColorPicker( $styleGen,
					'search-submit-text-color',
					__( 'Top Level Searchbar Submit Button Text Color', 'ubermenu' ),
					'',
					false
					);

		//@searchSubmitTextBackground
		$sparkOps->addColorPicker( $styleGen,
					'search-submit-background',
					__( 'Top Level Searchbar Submit Button Background', 'ubermenu' ),
					'',
					false
					);

		//@searchSubmitTextColorHover
		$sparkOps->addColorPicker( $styleGen,
					'search-submit-text-color-hover',
					__( 'Top Level Searchbar Submit Button Text Color [Hover]', 'ubermenu' ),
					'',
					false
					);

		//@searchSubmitTextBackgroundHover
		$sparkOps->addColorPicker( $styleGen,
					'search-submit-background-hover',
					__( 'Top Level Searchbar Submit Button Background [Hover]', 'ubermenu' ),
					'',
					false
					);



		//PREVIEW
		$sparkOps->addSubHeader( $styleGen,
					'stylegen-preview',
					__( 'Preview', 'ubermenu' )
					);
		
		$sparkOps->addSelect( $styleGen,
					'menu-preview',
					__( 'Menu', 'ubermenu' ),
					__( 'Select the menu you\'d like to preview', 'ubermenu' ),
					$this->getNavMenus()
					);
		
		$sparkOps->addCustomField( $styleGen,
					'ubermenu-preview-button',
					'previewButton'
					);

		







		/* THEME INTEGRATION */
		$themeIntegration = 'theme-config';
		$sparkOps->registerPanel( $themeIntegration, __( 'Theme Integration', 'ubermenu' ) , 60 );	
		
		$sparkOps->addInfobox( $themeIntegration,
					'wpmega-themeintegration',
					'',
					__( 'Help with integrating UberMenu with complex or misbehaving themes.', 'ubermenu' )
					);
					
		$sparkOps->addCheckbox( $themeIntegration,
					'wpmega-strict',
					__( 'Strict Mode', 'ubermenu' ),
					__( 'Disable Strict Mode <strong>if you have only one menu on your site</strong>, and it should be a mega menu, and your theme does not support Theme Locations.  Otherwise, leave this on or you may end up with the wrong menu megafied.', 'ubermenu' ),
					'on'
					);
		
		$sparkOps->addCheckbox( $themeIntegration,
					'wpmega-easyintegrate',
					__( 'Easy Integration', 'ubermenu' ),
					__( 'For themes that don\'t properly support WordPress 3 Menus.  Just turn this on and place '.
							'<code>&lt;?php uberMenu_easyIntegrate(); ?&gt;</code> in your header.php file, or <code>[uberMenu_easyIntegrate]</code> in a text widget to place your menu.', 'ubermenu' ),
					'off'
					);
		
		$sparkOps->addCheckbox( $themeIntegration,			
					'wpmega-remove-conflicts',
					__( 'Remove Theme Conflicts', 'ubermenu' ),
					__( 'This attempts to remove previously registered javascript acting on the menu.', 'ubermenu' ),
					'on'
					);
					
		$sparkOps->addCheckbox( $themeIntegration,
					'wpmega-minimizeresidual',
					__( 'Minimize Residual Styling', 'ubermenu' ),
					__( 'This will change the ID of the menu\'s top level UL.  If you still have residual styling, you likely need 
						to manually edit the ID or class of the elements surrounding the <code>wp_nav_menu</code> call in your header.php.', 'ubermenu' ),
					'on'
					);

		$sparkOps->addCheckbox( $themeIntegration,
					'reset-div-span-styling',
					__( 'Reset Div &amp; Span Styling', 'ubermenu' ),
					__( 'Reset margins, padding, etc, on divs &amp; spans within the menu.  This will strip styling from some widgets or shortcodes that you add to the menu.', 'ubermenu' ),
					'on'
					);
					
		$sparkOps->addCheckbox( $themeIntegration,
					'wpmega-iefix',
					__( 'Use IE Fix Script', 'ubermenu' ),
					//'Disable this only if it is causing problems or if you are already including it elsewhere.',
					__( 'Depending on how your theme is coded, some themes will require this for the menu to work properly while others will not.  IE is a fickle bitch, so try it both ways and see which works better for your theme.', 'ubermenu' ),
					'off'
					);
					
		$sparkOps->addCheckbox( $themeIntegration,
					'wpmega-jquery-noconflict',
					__( 'Run jQuery in noConflict Mode', 'ubermenu' ),
					__( 'If your theme does not load the default WordPress jQuery library, turn this on.', 'ubermenu' ),
					'off'
					);
					
		$sparkOps->addCheckbox( $themeIntegration,
					'wpmega-load-on-login',
					__( 'Load UberMenu on Login and Registration Pages', 'ubermenu' ),
					__( 'Since WordPress login and registration pages do not include a menu by default, UberMenu won\'t load there.  If you are styling your login/registration page, turn this on - otherwise UberMenu will be completely unstyled.', 'ubermenu' ),
					'off'
					);
				
		$sparkOps->addCheckbox( $themeIntegration,	
					'wpmega-include-jquery',
					__( 'Include jQuery', 'ubermenu' ),
					__( 'This is REQUIRED.  Only disable this if your theme or another plugin already is not properly enqueueing jQuery.  <span class="spark-infobox spark-infobox-warning"><strong>DO NOT DISABLE UNLESS YOU KNOW WHAT YOU ARE DOING!</strong></span>', 'ubermenu' ),
					'on'
					);
					
		$sparkOps->addCheckbox( $themeIntegration,
					'wpmega-include-hoverintent',
					__( 'Include Hover Intent', 'ubermenu' ),
					__( 'This is REQUIRED.  Only this if your theme or another plugin already includes hoverIntent.js.  <span class="spark-infobox spark-infobox-warning"><strong>DO NOT DISABLE UNLESS YOU ARE CERTAIN!</strong></span>', 'ubermenu' ),
					'on'
					);
		
		$sparkOps->addCheckbox( $themeIntegration,
					'no-ie6',
					__( 'Default to theme menu in IE6', 'ubermenu' ),
					__( 'UberMenu is not compatible with IE6, as noted in the product description.  By default, UberMenu will not load in IE6, in an effort to fall back to the default theme menu, which may or may not be IE6-compatible.  If for some reason you want to disable this feature, you can do so here.', 'ubermenu' ),
					'on'
					);

		
		
		
				
	
		/* ADVANCED */
		$advanced = 'advanced-config';
		$sparkOps->registerPanel( $advanced, __( 'Advanced Settings', 'ubermenu' ) , 70 );
		
		$sparkOps->addInfobox( $advanced , 
					'advanced-panel-warning' , 
					__( 'Warning', 'ubermenu' ),
					__( 'Do not change advanced settings unless you are certain of what you are doing.', 'ubermenu' ),
					'spark-infobox-warning' 
					);
					
		$sparkOps->addTextInput( $advanced,
					'wpmega-container-w',
					__( 'Menu Bar Width', 'ubermenu' ),
					__( 'Enter a width in pixels.  UberMenu automatically sizes to its container, so you only need to use this if you want UberMenu '.
						'to be a different size.  It will automatically be centered when possible.'.
						'<span class="spark-infobox spark-infobox-warning">Remember, your submenu\'s maximum width is restricted to the width of your menu bar by default.</span>'.
						'<span class="spark-infobox spark-infobox-warning">Be sure to disable the Expand Menu Bar Full Width option in basic settings if you intend to use this</span>', 'ubermenu' ),
					'',
					'spark-minitext',
					'px'
					);
					
		$sparkOps->addCheckbox( $advanced,
					'center-menubar',
					__( 'Center Menu Bar', 'ubermenu' ),
					__( 'Coupled with the Menu Bar Width above, this will center your menu within its container.  Be sure to enable the Clearfix option below as well', 'ubermenu' ),
					'off'
					);
					
		$sparkOps->addCheckbox( $advanced,
					'enable-clearfix',
					__( 'Enable Clearfix', 'ubermenu' ),
					__( 'Important for centering.', 'ubermenu' ),
					'off'
					);
					
		$sparkOps->addTextInput( $advanced,
					'inner-menu-width',
					__( 'Inner Menu Width', 'ubermenu' ),
					__( 'Enter a width in pixels (most common: 960).  This is useful for centering the menu items while allowing the menu bar to span the entire window.', 'ubermenu' ),
					'',
					'spark-minitext',
					'px'
					);
					
		$sparkOps->addCheckbox( $advanced,
					'center-inner-menu',
					__( 'Center Inner Menu', 'ubermenu' ),
					__( 'Coupled with the Inner Menu Width above, this will center your inner menu within the menu bar.'.
						'<span class="spark-infobox spark-infobox-warning">You must set an explicit width above to center the menu.</span>', 'ubermenu' ),
					'off'
					);

		$sparkOps->addCheckbox( $advanced,
					'center-menuitems',
					__( 'Center Menu Items', 'ubermenu' ),
					__( 'Center the menu items within the menu bar.  Note that using images, widgets or content overrides in the top level may break your layout with this option enabled.  '.
						'Works best with basic text menu items.', 'ubermenu' ),
					'off'
					);

					
		/*$sparkOps->addTextInput( $advanced,			
					'wpmega-max-submenu-w',
					'Maximum Submenu Width',
					'Normally, a submenu can only be as wide as the top level menu bar.  If you want it to be wider, set the value here.',
					'',
					'spark-minitext',
					'px'
					);*/
		
		$sparkOps->addCheckbox( $advanced,
					'wpmega-html5',
					__( 'HTML5', 'ubermenu' ),
					__( 'Use the HTML5 <code>&lt;nav&gt;</code> element as the menu container.  <span class="spark-infobox spark-infobox-warning">Only enable this if your theme supports HTML5!  Otherwise it\'ll break Internet Explorer</span>', 'ubermenu' )
					);
					
		$sparkOps->addCheckbox( $advanced,
					'include-basic-css',
					__( 'Include basic.css', 'ubermenu' ),
					__( 'UberMenu\'s basic.css contains all the structural code for the menu.  <span class="spark-infobox spark-infobox-warning">Without this code, the menu will not work.  Only disable this if you are going to manually include it elsewhere.</span>', 'ubermenu' ),
					'on'
					);

		/*
		$sparkOps->addCheckbox( $advanced,
					'custom-basic-css',
					__( 'Use custom/basic.css', 'ubermenu' ),
					__( 'If you want to use a custom version of basic.css, enable this (keep the above option enabled as well).', 'ubermenu' ),
					'off'
					);
		*/
	
		$sparkOps->addCheckbox( $advanced,
					'wpmega-debug',		//'Debug' is legacy
					__( 'Development Mode', 'ubermenu' ),
					__( 'Run in Development mode.  This will load the .dev.js ubermenu script rather than the minified version.  Easier for testing customizations and debugging.', 'ubermenu' )
					);
					
		$sparkOps->addResetButton( $advanced,
					'wpmega-reset-options',
					__( 'Reset Options', 'ubermenu' ),
					__( 'Reset UberMenu Settings to the factory defaults.', 'ubermenu' )
					);

		$sparkOps->addCheckbox( $advanced,
					'wpmega-autoAlign',
					__( 'Auto Align (Deprecated)', 'ubermenu' ),
					__( 'Automatically align the second-level menu items by setting all widths the width of the widest item in each submenu. '.
						'<div class="spark-infobox spark-infobox-warning">This feature remains for legacy use.  A better way to accomplish column alignment is to choose the "Full Width" option and set the number of columns in a menu item\'s settings</div>', 'ubermenu' ),
					'off',
					''
					);


		$sparkOps->addCheckbox( $advanced,
					'update-alerts',
					__( 'Update Alerts', 'ubermenu' ),
					__( 'Check for updates and display an alert in the admin bar when available.', 'ubermenu' ),
					'on',
					''
					);

		$sparkOps->addCheckbox( $advanced,
					'compatibility-report-panel',
					__( 'Show Compatibility Report Panel', 'ubermenu' ),
					__( 'Disable this to hide the Compatibility Report Panel.  (After saving, you\'ll need to reload a second time before the change takes effect).', 'ubermenu' ),
					'on',
					''
					);

		$sparkOps->addCheckbox( $advanced,
					'extensions-panel',
					__( 'Show Extensions &amp; Utilities Panel', 'ubermenu' ),
					__( 'Disable this to hide the Extensions &amp; Utilities Panel.  (After saving, you\'ll need to reload a second time before the change takes effect).', 'ubermenu' ),
					'on',
					''
					);


		$sparkOps->addCheckbox( $advanced,
					'custom-js',
					__( 'Include Custom Javascript', 'ubermenu' ),
					__( 'Enable this to load the custom/custom.js file.', 'ubermenu' ),
					'off',
					''
					);



		if( $sparkOps->op( 'compatibility-report-panel' ) == 'on' ){

			$compat = 'compatibility-report';
			$sparkOps->registerPanel( $compat, __( 'Compatibility Report', 'ubermenu' ) , 100 , false );
			
			$sparkOps->addInfobox( $compat , 
						'compat-alert' , 
						__( 'Please Help Improve Theme/UberMenu Compatibility' , 'ubermenu' ),
						__( 'Please let me know about compatibility with your theme so I can work towards more universal UberMenu compatibility.', 'ubermenu' ),
						'spark-infobox-notice' 
						);

			$sparkOps->addCustomField( $compat ,
						'ubermenu-compat',
						'ubermenu_compatibility_report' 
						);
		}


		if( $sparkOps->op( 'extensions-panel' ) == 'on' ){

			$extensions = 'extensions';
			$sparkOps->registerPanel( $extensions, __( 'Extensions &amp; Utilities', 'ubermenu' ) , 120 , false );
			
			$sparkOps->addInfobox( $extensions , 
						'extensions-alert' , 
						__( 'UberMenu Extensions &amp; Utilities' , 'ubermenu' ),
						__( 'The following are a selection of both free and premium extensions and utilities that can augment your UberMenu in a variety of scenarios.  <br/><br/>You can disable this panel if you wish in the Advanced Settings.', 'ubermenu' ),
						'spark-infobox-notice' 
						);

			$sparkOps->addCustomField( $extensions ,
						'ubermenu-extensions',
						'ubermenu_extensions' 
						);
		}
					
					
		
		



		if( isset( $_GET['updates'] ) ){

			/* UPDATES */
			$updates = 'updates';
			$sparkOps->registerPanel( $updates, 'Updates' , 5 , false );	
			$sparkOps->addCustomField( $updates,
					'ubermenu-updates',
					'ubermenu_update_notifier'
					);
		}


		return $sparkOps;

	}



	function getStyleApplicationOptions(){

		$ops = parent::getStyleApplicationOptions();

		//Move nothing to the end
		$nothing = $ops['none'];
		unset( $ops['none'] );

		$ops['inline']	=	__( 'Style Generator', 'ubermenu' ).
							'<span class="spark-admin-op-radio-desc">'.__( 'If custom file is not writable, adds <code>&lt;style&gt;</code> tags to <code>&lt;head&gt;</code>' , 'ubermenu' ).'</span>';
			
		$ops['custom']	=	__( 'Custom', 'ubermenu' ).
							'<span class="spark-admin-op-radio-desc">'.__( 'Load <code>ubermenu/custom/custom.css</code>.  Always use this file for customizations!', 'ubermenu' ).'</span>';

		$ops['none']	= 	$nothing;

		return $ops;
	}




	function getOptionsMenuLinks(){
		//Links
		$links = array(

			1	=>	array(
				'href'	=>	'http://goo.gl/YV7aw', //'http://j.mp/dPmJ8m',
				'class'	=>	'spark-outlink-hl',
				'title'	=>	__( 'Read the Support Manual', 'ubermenu' ),
				'text'	=>	__( 'Support Manual &rarr;', 'ubermenu' ),
			),

			2	=>	array(
				'href'	=>	'http://goo.gl/G8b3r',
				'class'	=>	'spark-outlink-hl',
				'title'	=>	__( 'Watch Video Tutorials', 'ubermenu' ),
				'text'	=>	__( 'Video Tutorials &rarr;', 'ubermenu' ),					
			),

			3	=>	array(
				'href'	=>	'http://goo.gl/0OIDQ',
				'class'	=>	'spark-outlink-hl',
				'title'	=>	__( 'Troubleshoot problems you encounter', 'ubermenu' ),
				'text'	=>	__( 'Troubleshooter &rarr;', 'ubermenu' ),	
			),

			4	=>	array(
				'href'	=>	'http://goo.gl/KdXgk',
				'class'	=>	'spark-outlink-hl',
				'title'	=>	__( 'Customization Assistant', 'ubermenu' ),
				'text'	=>	__( 'Customization Assistant &rarr;', 'ubermenu' ),
			),
			
			5	=>	array(
				'href'	=>	'http://sevenspark.com/out/support',
				'class'	=>	'spark-outlink',
				'title'	=>	__( 'Submit a support request', 'ubermenu' ),
				'text'	=>	__( 'Support Forum &rarr;', 'ubermenu' ),
			),

			6	=>	array(
				'href'	=>	'http://goo.gl/6J72a',
				'class'	=>	'spark-outlink',
				'title'	=>	__( 'Frequently Asked Questions', 'ubermenu' ),
				'text'	=>	__( 'FAQs  &rarr;', 'ubermenu' ),
			),								
			
			
			7	=>	array(
				'href'	=>	'http://j.mp/fDpVkP',
				'class'	=>	'spark-outlink',
				'title'	=>	__( 'Purchase a license for use on a second installation', 'ubermenu' ),
				'text'	=>	__( 'Additional License &rarr;', 'ubermenu' )
			)
		);

		return $links;
	}


	function showThanks(){
	
		if( isset($_GET['cleared'] ) ){
			update_option( 'ubermenu-thanks', 2 );
		}
		if( isset($_GET['reset'] ) ){
			update_option( 'ubermenu-thanks', 1 );
		}
		//Pre //Done //Kill
		$status = get_option( 'ubermenu-thanks', 1 );
		
		if($status == 2) return;
		
		?>
		<div class="ubermenu-thanks">
			<h3><?php _e( 'Thank you for installing UberMenu - WordPress Mega Menu Plugin, available exclusively from CodeCanyon!' , 'ubermenu' ); ?></h3>
			<p><?php _e( 'This license entitles you to use UberMenu on a single WordPress instance (and one private development server).' , 'ubermenu' ); ?></p>
						
			<p><?php _e( 'To get started, take a look at the <a href="http://goo.gl/R3NXGs" target="_blank">UberMenu Knowledgebase</a>, or watch the
				<a href="http://goo.gl/G8b3r" target="_blank">UberMenu Video Tutorials</a>.  If you\'d like to keep up with UberMenu updates, you can
				follow <a href="http://bit.ly/i1j6wb" target="_blank">@sevenspark</a> on Twitter or fan on <a href="http://www.facebook.com/sevensparklabs">Facebook</a>.' , 'ubermenu');?></p>
				
		
			<div class="ops">
				<a class="button button-good" id="ubermenu-thanks-cleared" href="<?php echo $_SERVER["REQUEST_URI"].'&cleared=yup';?>"><?php _e( 'I purchased UberMenu from CodeCanyon' , 'ubermenu' ); ?></a>
				<a class="button button-bad" href="http://bit.ly/grEsDs"><?php _e( 'I need a license' , 'ubermenu' );?></a>
			</div>
					
			<div class="uber_clear"></div>
		</div>
		<?php 
		
	}
	
	function showThanksCleared_callback(){
		
		if(isset($_GET['cleared'])){
			update_option('ubermenu-thanks', 2);
		}
			
		$data = array();
		
		$ajax_nonce = wp_create_nonce( "thanks-cleared" );
		
		$data['remove_nonce'] = $ajax_nonce;// $rmvBtn;
		$data['response'] = "<h3 style='display:inline-block'>".__( 'Thank you for your purchase!' , 'ubermenu' )."</h3>";
		$this->JSONresponse($data);	
	}


	function pluginActionLinks( $links, $file ) {
		if ( $file == 'ubermenu/ubermenu.php' ){
			$links[] = '<a href="' . admin_url( 'themes.php?page=uber-menu' ) . '">'.__( 'Settings' , 'ubermenu' ).'</a>';
			$links[] = '<a href="http://bit.ly/eR0cvC" target="_blank">'.__( 'Support Manual' , 'ubermenu' ).'</a>';
		}
		return $links;
	}


	/*
	 * Setup the ToolTip Tour for UberMenu
	 */
	function createTour(){
		global $pagenow;
		$this->tour = $uberTour = new TipTour( 'uberMenu' );
				
		if( $uberTour->tourOn() ){
			
			//build & load		
			
			$page_slug = '_';
			if ( isset($_GET['page']) ) $page_slug = $_GET['page'];
			
			$uberTour->addStep( new TipTourStep( 
				$pagenow,	//load anywhere
				$page_slug,
				'#menu-appearance > a.menu-top',
				__( 'Welcome to UberMenu!', 'ubermenu' ),
				'<p>'.__( 'Thank you for installing UberMenu - WordPress Mega Menu Plugin by SevenSpark!  Click "Start Tour" to view a quick introduction', 'ubermenu').'</p>',
				'top',
				'0 0',
				'Start Tour'
			));
			
			
			$uberTour->addStep( new TipTourStep( 
				'nav-menus.php',
				'',
				'#nav-menu-header',
				__( '1. Create a Menu', 'ubermenu' ),
				'<p>'.__( 'Start off by creating a menu using the WordPress 3 Menu System.  Each menu item has new options based on its level.  To create a mega menu drop down, be sure to check "Activate Mega Menu" in the UberMenu Options', 'ubermenu').'</p>',
				'top'
			));
			
			$uberTour->addStep( new TipTourStep( 
				'nav-menus.php',	//load anywhere
				'',
				'#nav-menu-theme-locations',
				__( '2. Set Theme Location', 'ubermenu' ),
				'<p>'.__( 'Next, set your menu in the appropriate theme location.  If your theme does not support theme locations, you can use UberMenu Easy Integration instead.', 'ubermenu').'</p>',
				'left'
			));
			
			
			$uberTour->addStep( new TipTourStep( 
				'nav-menus.php',	//load anywhere
				'',
				'#nav-menu-theme-megamenus',
				__( '3. Activate UberMenu Theme Locations', 'ubermenu' ),
				'<p>'.__( 'Now, activate UberMenu on the appropriate theme location.  This tells UberMenu which menus it should affect, so you can have 1 UberMenu and multiple non-UberMenus.', 'ubermenu').'</p>',
				'left'
			));
			
			
			$uberTour->addStep( new TipTourStep( 
				'themes.php',	//load anywhere
				'uber-menu',
				'#container-wpmega-orientation',
				__( '4. Pick your Orientation', 'ubermenu' ),
				'<p>'.__( 'Decide whether your menu should be vertically or horizontally aligned.', 'ubermenu').'</p>',
				'left top',
				'0 -50'
			));
			
			$uberTour->addStep( new TipTourStep( 
				'themes.php',	//load anywhere
				'uber-menu',
				'#container-wpmega-transition',
				__( '5. jQuery', 'ubermenu' ),
				'<p>'.__( 'Decide whether you want your menu to be jQuery-Enhanced or pure CSS.  Pure CSS mega submenus will all be full-width unless customized with CSS.', 'ubermenu').'</p>',
				'left top',
				'0 -50'
			));
			
			
			
			
			$uberTour->addStep( new TipTourStep( 
				'themes.php',	//load anywhere
				'uber-menu',
				'.spark-nav-footer a:first',
				__( 'Have a question?', 'ubermenu' ),
				'<p>'.__( 'You can always access the latest version of the support manual by clicking this link.', 'ubermenu').'</p><p>Thank you for your purchase.  Enjoy UberMenu!</p>',
				'left',
				'0 -75'
			));
			
			$uberTour->loadTour();
			
		}
	
		$this->settings->addTour( $this->tour );
		
	}




	/* Here is where the Presets are registered */
	function registerStylePresets(){
		
		parent::registerStylePresets();
		
		$this->registerStylePreset('shiny-black', 		__( 'Shiny Black' , 'ubermenu' ),				$this->editionURL.'styles/skins/shinyblack.css');
		$this->registerStylePreset('simple-green', 		__( 'Simple Green' , 'ubermenu' ),				$this->editionURL.'styles/skins/simplegreen.css');
		$this->registerStylePreset('earthy', 			__( 'Earthy' , 'ubermenu' ), 					$this->editionURL.'styles/skins/earthy.css');
		$this->registerStylePreset('silver-tabs', 		__( 'Silver Tabs (No Menu Bar)' , 'ubermenu' ),	$this->editionURL.'styles/skins/silvertabs.css');
		$this->registerStylePreset('black-silver', 		__( 'Black and Silver' , 'ubermenu' ), 			$this->editionURL.'styles/skins/blacksilver.css');
		$this->registerStylePreset('blue-silver', 		__( 'Blue and Silver' , 'ubermenu' ), 			$this->editionURL.'styles/skins/bluesilver.css');
		$this->registerStylePreset('red-black', 		__( 'Red and Black' , 'ubermenu' ), 			$this->editionURL.'styles/skins/redblack.css');
		$this->registerStylePreset('orange', 			__( 'Burnt Orange' , 'ubermenu' ), 				$this->editionURL.'styles/skins/orange.css');
		$this->registerStylePreset('clean-white', 		__( 'Clean White' , 'ubermenu' ), 				$this->editionURL.'styles/skins/cleanwhite.css');
		$this->registerStylePreset('trans-black', 		__( 'Transparent Black' , 'ubermenu' ),			$this->editionURL.'styles/skins/trans_black.css');
		$this->registerStylePreset('trans-black-hov',	__( 'Transparent Black Hover' , 'ubermenu' ),	$this->editionURL.'styles/skins/trans_black_hover.css');
		

		$this->registerStylePreset('blank', 			'', '' );
		$this->registerStylePreset('unsupported', 		__( '--- Legacy Skins: for Non-Responsive Sites Only: ---' , 'ubermenu' ),		'');
		$this->registerStylePreset('tt-silver', 		__( 'Two Tone Silver & Black' , 'ubermenu' ),	$this->editionURL.'styles/skins/twotone_silver_black.css');
		$this->registerStylePreset('tt-black', 			__( 'Two Tone Black & Black' , 'ubermenu' ),	$this->editionURL.'styles/skins/twotone_black_black.css');
		$this->registerStylePreset('tt-red', 			__( 'Two Tone Red & Black' , 'ubermenu' ),		$this->editionURL.'styles/skins/twotone_red_black.css');
		$this->registerStylePreset('tt-blue', 			__( 'Two Tone Blue & Black' , 'ubermenu' ),		$this->editionURL.'styles/skins/twotone_blue_black.css');
		$this->registerStylePreset('tt-green', 			__( 'Two Tone Green & Black' , 'ubermenu' ),	$this->editionURL.'styles/skins/twotone_green_black.css');
		$this->registerStylePreset('tt-purple', 		__( 'Two Tone Purple & Black' , 'ubermenu' ),	$this->editionURL.'styles/skins/twotone_purple_black.css');
		$this->registerStylePreset('tt-orange', 		__( 'Two Tone Orange & Black' , 'ubermenu' ),	$this->editionURL.'styles/skins/twotone_orange_black.css');
		$this->registerStylePreset('tt-silver-s',		__( 'Two Tone Silver & Silver' , 'ubermenu' ),	$this->editionURL.'styles/skins/twotone_silver_silver.css');
		
		$this->registerStylePreset('custom', 			__( 'Custom - (Legacy Use)' , 'ubermenu' ), 	$this->editionURL.'custom/custom.css');

	}






}