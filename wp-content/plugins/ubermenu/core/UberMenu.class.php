<?php

/* The Meat */
class UberMenu{
	
	protected $settings;
	protected $baseURL;
	protected $coreURL;
	protected $editionURL;


	protected $tour;
	protected $stylePresets;
	
	protected $menuItemOptions;
	protected $optionDefaults;

	protected $count = 0;

	function __construct( $base_url = '' ){
		
		if( $base_url ){
			//Integrated theme version
			$this->baseURL = $base_url;
			$this->coreURL = $base_url.'core/';
		}
		else{
			//Plugin Version
			$this->baseURL = plugins_url().'/'.dirname( str_replace(basename( __FILE__ ),"",plugin_basename( __FILE__ ) ) ) . '/';
			$this->coreURL = plugins_url().'/'.str_replace(basename( __FILE__ ),"",plugin_basename( __FILE__ ) );
		}
		if( !$this->editionURL ) $this->editionURL = $this->baseURL;
		
		$this->registerStylePresets();
		$this->settings = $this->optionsMenu();
		$this->menuItemOptions = array();
		
		//ADMIN
		if( is_admin() ){
			add_action( 'admin_menu' , array( $this , 'adminInit' ) );
			add_action( 'wp_ajax_megaMenu_updateNavLocs', array( $this , 'updateNavLocs_callback' ) );			//For logged in users
			add_action( 'wp_ajax_wpmega-add-menu-item', array( $this , 'addMenuItem_callback' ) );
			add_action( 'wp_ajax_ubermenu_getPreview', array( $this ,  'getPreview_callback' ) );
			
			add_action( 'ubermenu_menu_item_options', array( $this , 'menuItemCustomOptions' ), 10, 1);		//Must go here for AJAX purposes
			
			
			
			//AJAX Load Image
			add_action( 'wp_ajax_ubermenu_getMenuImage', array( $this, 'getMenuImage_callback' ) );
			
			//Appearance > UberMenu Preview
			add_filter( 'wp_nav_menu_args' , array( $this , 'megaMenuFilter' ), 2000 );  	//filters arguments passed to wp_nav_menu
			
			//sparkoptions_before_settings_panel_wp-mega-menu-settings
			//add_action( 'sparkoptions_before_settings_panel_'.UBERMENU_SETTINGS,  array( $this, 'checkPostLimits' ) );
			add_action( 'admin_notices',  array( $this, 'checkPostLimits' ) );

			$this->optionDefaults = array(

				'menu-item-shortcode'			=> '',
				'menu-item-sidebars'			=> '',
				'menu-item-highlight'			=> 'off',
				'menu-item-notext'				=> 'off',
				'menu-item-nolink'				=> 'off',
				'menu-item-isheader'			=> 'off',
				'menu-item-verticaldivision'	=> 'off',
				'menu-item-newcol'				=> 'off',
				'menu-item-isMega'				=> 'off',
				'menu-item-alignSubmenu'		=> 'center',
				'menu-item-floatRight'			=> 'off',
				'menu-item-fullWidth'			=> 'off',
				'menu-item-numCols'				=> 'auto',

			);

			$this->optionDefaults = apply_filters( 'uberMenu_menu_item_options_value_defaults' , $this->optionDefaults );
			
		}
		//FRONT END
		else{
			add_action( 'plugins_loaded' , array( $this , 'init' ) );
			add_action( 'after_setup_theme' , array( $this , 'fire_custom_preset_hook' ) );
		}

		add_shortcode( 'uberMenu_direct' , array( $this , 'directIntegrationShortcode' ) );		

	}
	
	
	function init(){
			
		$this->loadAssets();
		
		load_plugin_textdomain( 'ubermenu' , false , basename( dirname( dirname( __FILE__ ) ) ) . '/languages' );
		
		//Filters
		//add_filter( 'wp_nav_menu_args' , array( $this , 'megaMenuFilter' ), 2000 );  	//filters arguments passed to wp_nav_menu
		
		//if( $this->settings->op( 'responsive-menu-toggle' ) ) add_filter( 'wp_nav_menu' , array( $this , 'responsiveMenuToggle' ), 2001 , 2 ); 
		
	}
	
	function loadAssets(){
		
		//Load on front end, as well as on login and registration pages if setting is enabled
		if( !is_admin() && 
		  ( $this->settings->op( 'wpmega-load-on-login' ) || !in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) ) {
			
			//Actions
			//add_action( 'wp_print_styles' , array( $this , 'loadCSS' ) );
			add_action( 'wp_enqueue_scripts' , array( $this , 'loadCSS' ) );
			
			//Load Javascript unless disabled
			if( $this->settings->op( 'wpmega-jquery' ) ) add_action( 'init', array( $this , 'loadJS' ), 500);
			
		}

	}
	
	
	
	function loadCSS(){
		
		//Load Structural CSS - if this option does not exist (invalid), show basic.css
		if( $this->settings->op( 'include-basic-css' ) || $this->settings->op( 'include-basic-css' , 'invalid' ) == 'invalid' ) {
			wp_enqueue_style( 'ubermenu-basic', $this->editionURL.'styles/basic.css', false, UBERMENU_VERSION, 'all' );
			//wp_enqueue_style( 'ubermenu-basic', $this->baseURL.'custom/basic.css', false, UBERMENU_VERSION, 'all');
		}
		
		//Load Preset
		if( $this->settings->op( 'wpmega-style' ) == 'preset' || !$this->settings->op( 'wpmega-style' ) ) {				
		
			$id = 
				!$this->settings->op( 'wpmega-style-preset' )  
				? $this->getDefaultPreset()
				: $this->settings->op( 'wpmega-style-preset' );

			if( !empty( $id ) ) {
				$href = $this->stylePresets[$id]['path'];
				wp_enqueue_style( 'ubermenu-'.$id, $href, false, UBERMENU_VERSION, 'all'); 
			}

		}		
		
	}

	function getDefaultPreset(){
		return apply_filters( 'uberMenu_default_preset' , 'grey-white' );
	}

	/*function loadLESS(){
		?>
		<!-- HERE I AM -->
		<link rel="stylesheet/less" type="text/css" href="<?php echo $this->editionURL; ?>styles/basic.less" />
		<script src="<?php echo $this->coreURL; ?>styles/less/less.min.js" type="text/javascript"></script>
		<?php
	}
	*/
	
	
	
	function loadJS(){
		
		// Load jQuery - optionally disable for when dumb themes don't include jquery properly
		if( $this->settings->op( 'wpmega-include-jquery' ) ) wp_enqueue_script( 'jquery' );

		// Load Hover Intent
		if( $this->settings->op( 'wpmega-include-hoverintent' ) &&  $this->settings->op( 'wpmega-trigger' ) == 'hoverIntent' )
			wp_enqueue_script( 'hoverintent' , $this->coreURL.'js/hoverIntent.js', array( 'jquery' ), false, true );

		
		if($this->settings->op( 'wpmega-debug' ) == 'on') 	wp_enqueue_script( 'ubermenu', $this->coreURL.'js/ubermenu.dev.js', array(), false, true );		
		else 												wp_enqueue_script( 'ubermenu', $this->coreURL.'js/ubermenu.min.js', array(), false, true );
	
	
		$this->loadJSsettings();
	
	}
	
	function loadJSsettings(){
	
		wp_localize_script( 'ubermenu', 'uberMenuSettings', array(
			'speed'				=>	$this->settings->op( 'wpmega-animation-time' ),
			'trigger'			=>	$this->settings->op( 'wpmega-trigger' ),
			'orientation'		=>	$this->settings->op( 'wpmega-orientation' , 'horizontal' ),
			'transition'		=>	$this->settings->op( 'wpmega-transition' ),
			'hoverInterval'		=>	$this->settings->op( 'wpmega-hover-interval' ),
			'hoverTimeout'		=>	$this->settings->op( 'wpmega-hover-timeout' ),
			
			//turn booleans to strings, since wp_localize script can handle booleans - converted back in JS
			'removeConflicts'	=>	$this->settings->op( 'wpmega-remove-conflicts' ) ? 'on' : 'off',
			'autoAlign'			=>	$this->settings->op( 'wpmega-autoAlign' ) ? 'on' : 'off',			
			'noconflict'		=>	$this->settings->op( 'wpmega-jquery-noconflict' ) ? 'on' : 'off',
			'fullWidthSubs'		=>	$this->settings->op( 'wpmega-submenu-full' ) ? 'on' : 'off',
			'androidClick'		=>	$this->settings->op( 'android-click' ) ? 'on' : 'off',
			'iOScloseButton'	=> 	$this->settings->op( 'iOS-close-button' ) ? 'on' : 'off',
			
			
			'loadGoogleMaps'	=>	$this->settings->op( 'load-google-maps' ) ? 'on' : 'off',

			'repositionOnLoad'	=>	$this->settings->op( 'reposition-on-load' ) ? 'on' : 'off',
		));
		
	}
	
	
	function fire_custom_preset_hook(){

		do_action( 'uberMenu_register_styles' );

	}
	

	

	function getSettings(){
		return $this->settings;
	}
	

	/*
	 * Default walker, but this can be overridden
	 */
	function getWalker(){
		return new UberMenuWalkerCore();
	}
	
	


	function directIntegration( $theme_location = 'ubermenu' , $filter = false , $echo = true ){
		$args = array();
		$args['theme_location'] = $theme_location;
		$args['filter'] = $filter;
		$args['echo'] = $echo;

		$args = $this->getMenuArgs( $args );

		if( $echo ) wp_nav_menu( $args );
		else return wp_nav_menu( $args );
	}

	function directIntegrationShortcode( $atts ){
		extract(shortcode_atts(array(
			'theme_location' => 'ubermenu'
		), $atts));

		return $this->directIntegration( $theme_location , false , false );
	}

	function getMenuArgs( $args ){

		$items_wrap 	= '<ul id="%1$s" class="%2$s">%3$s</ul>'; 

		if( $this->settings->op( 'responsive-menu-toggle' ) ) 
			$items_wrap = '<div id="megaMenuToggle" class="megaMenuToggle">'.$this->settings->op( 'responsive-menu-toggle-text' ).'&nbsp; <span class="megaMenuToggle-icon"></span></div>'.$items_wrap;


		$args['walker'] 			= $this->getWalker();
		$args['container_id'] 		= 'megaMenu';
		$args['container_class'] 	= 'megaMenuContainer megaMenu-nojs';
		$args['menu_class']			= 'megaMenu';
		$args['depth']				= 0;
		$args['items_wrap']			= $items_wrap;
		$args['link_before']		= '';
		$args['link_after']			= '';


		if( $this->settings->op( 'wpmega-style' ) == 'preset' )			$args['container_class'].= ' wpmega-preset-'.$this->settings->op( 'wpmega-style-preset' );

		if( $this->settings->op( 'responsive-menu' ) )					$args['container_class'].= ' megaResponsive';
		if( $this->settings->op( 'responsive-menu-toggle' ) )			$args['container_class'].= ' megaResponsiveToggle';

		if( $this->settings->op( 'wpmega-jquery' )	)					$args['container_class'].= ' wpmega-withjs';
		else 															$args['container_class'].= ' wpmega-nojs';

		if( $this->settings->op( 'wpmega-transition' ) == 'fade' )		$args['container_class'].= ' megaMenuFade';
		
		if( $this->settings->op( 'wpmega-trigger' ) == 'click' )		$args['container_class'].= ' megaMenuOnClick';
		else															$args['container_class'].= ' megaMenuOnHover';

		if( $this->settings->op( 'wpmega-submenu-full' ) )				$args['container_class'].= ' megaFullWidthSubs';  
		

		return $args;
	}
	
	

	/*
	 * Apply options to the Menu via the filter
	 */
	function megaMenuFilter( $args ){

		//Only print the menu once
		if( $this->count > 0 ) return $args;
		
		if( isset( $args['responsiveSelectMenu'] ) ) return $args;

		$this->count++; //increment count
		
		$args = $this->getMenuArgs( $args );

		return $args;
	}


	
	
	
	function getImage( $id, $w = 16, $h = 16 ){
				
		if( empty( $w ) ) $w = 16; 
		if( empty( $h ) ) $h = 16;
	
		if( has_post_thumbnail( $id ) ){
			$img_id = get_post_thumbnail_id( $id );
			$attachment = get_post( $img_id );
			
			$image = wp_get_attachment_image_src( $img_id, 'single-post-thumbnail' );
			$src = $image[0];
			
			if( is_ssl() ) $src = str_replace('http://', 'https://', $src);
			
			$alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
			$title = trim( strip_tags( $attachment->post_title ) );
			if( empty( $alt ) ) $alt = $title;
			if( $this->settings->op( 'wpmega-disable-img-tooltips' ) ) $title = '';

			if( $this->settings->op( 'wpmega-resizeimages' ) ){
				if( $this->settings->op( 'wpmega-usetimthumb' ) ){	
					return $this->timthumb($src, $w, $h, $title, $alt);
				}
				else return '<img class="um-img um-img-resize" height="'.$h.'" width="'.$w.'" src="'.$src.'" alt="'.$alt.'" title="'.$title.'" />';
			}
			else return '<img class="um-img um-img-noresize" src="'.$src.'" alt="'.$alt.'" title="'.$title.'" />';
			
		}
		return '';
	}
	
	/*
	 * TimThumb function
	 */
	function timthumb( $src, $w, $h, $title = '', $alt = '', $zc = 1 ){  //, $rel=''){
		
		if( stristr( trim( $src ), 'http://' ) != 0){
			$src = get_bloginfo('url') . trim($src);
		}
		
		//$ttsrc = UBERMENU_TT;
		$ttsrc = $this->editionURL.'timthumb/tt.php';
		
		if( is_ssl() ) $ttsrc = str_replace( 'http://', 'https://', $ttsrc );
	
		$img = '<img src="'.$ttsrc.
					'?src='.$src.
					'&amp;w='.$w.
					'&amp;h='.$h.
					'&amp;zc='.$zc.
					'" alt="'.$alt.'" title="'.$title.'" class="um-img um-img-timthumb"';
		$img.= '/>';

		return $img;
	}

	/*
	 * Get the Post Thumbnail Image
	 */
	function getPostImage( $id, $w=30, $h=30, $default_img = false ){

		if( empty( $w ) ) $w = 30; if( empty( $h ) ) $h = 30;
		
		if ( has_post_thumbnail( $id ) ){
			$img_id = get_post_thumbnail_id( $id );
			$attachment = get_post( $img_id );
			//$image = wp_get_attachment_image_src( $img_id , 'single-post-thumbnail' );
			$image = wp_get_attachment_image_src( $img_id , array( $w, $h ) );
			$src = $image[0];
			
			$alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
			$title = trim( strip_tags( $attachment->post_title ) );
			if( empty( $alt ) ) $alt = $title;
			if( $this->settings->op( 'wpmega-disable-img-tooltips' ) ) $title = '';
					
			return $this->buildImg( $src, $w, $h , $title, $alt );
		}
		else if( $default_img ){
			//Use Default Image if Post does not have featured image
			return $this->buildImg( $default_img, $w, $h , '', '' );
		}
		return '';
	}
	
	function buildImg( $src, $w, $h, $title, $alt ){

		if( is_ssl() ) $src = str_replace('http://', 'https://', $src);
		
		if( $this->settings->op( 'wpmega-usetimthumb' ) ){
			return $this->timthumb( $src, $w, $h , $title, $alt );
		}
		else return '<img height="'.$h.'" width="'.$w.'" src="'.$src.'" alt="'.$alt.'" title="'.$title.'" />';
	}
	
	
	
	
	
	
	
	
	
	
	
	/* ADMIN */
	
	function adminInit(){

	
		//Appearance > Menus : load additional styles and scripts
		add_action( 'admin_print_styles-nav-menus.php', array( $this , 'loadAdminNavMenuJS' ) ); 
		add_action( 'admin_print_styles-nav-menus.php', array( $this , 'loadAdminNavMenuCSS' )); 
		
		//Appearance > Menus : modify menu item options
		add_filter( 'wp_edit_nav_menu_walker', array( $this , 'editWalker' ) , 2000);
		
		//Appearance > Menus : save custom menu options
		add_action( 'wp_update_nav_menu_item', array( $this , 'updateNavMenuItem' ), 10, 3); //, $menu_id, $menu_item_db_id, $args;
		
		
		//Appearance > UberMenu load additional styles and scripts
		add_action( 'sparkoptions_load_js_'.UBERMENU_SETTINGS , array( $this , 'loadAdminNavMenuJS' ) );
		add_action( 'sparkoptions_load_css_'.UBERMENU_SETTINGS , array( $this , 'loadAdminNavMenuCSS' ) );
		
		do_action( 'uberMenu_register_styles' );
				
		//For extensibility
		do_action( 'uberMenu_after_init' );
		
	}
	
	function loadAdminNavMenuJS(){
		
		wp_enqueue_script('jquery');	// Load jQuery
	
		wp_enqueue_script('ubermenu', $this->coreURL.'js/ubermenu.dev.js', array(), UBERMENU_VERSION, true);
		$this->loadJSsettings();
	
		wp_enqueue_script('hoverintent', $this->coreURL.'js/hoverIntent.js', array( 'jquery' ), UBERMENU_VERSION, true);
		wp_enqueue_script('thickbox');
	
		//Admin Extras	
		wp_enqueue_script('ubermenu-admin-js', $this->coreURL.'js/ubermenu.admin.js', array(), UBERMENU_VERSION, true);	
		
		//Colorpicker
		wp_enqueue_script( 'wp-color-picker' );
		//wp_enqueue_script('colorpicker-js', $this->coreURL.'sparkoptions/js/colorpicker/js/colorpicker.js', array(), UBERMENU_VERSION, true);

	}
	
	function loadAdminNavMenuCSS(){
		wp_enqueue_style('ubermenu-admin-css', 	$this->coreURL.'styles/admin.css', 	false, UBERMENU_VERSION, 'all');
		wp_enqueue_style('thickbox');
	
		//wp_enqueue_style('wpmega-basic', 	$this->baseURL.'styles/basic.css', 					false, UBERMENU_VERSION, 'all');
		
		//Colorpicker
		wp_enqueue_style( 'wp-color-picker' );
		//wp_enqueue_style('colorpicker',		$this->coreURL.'sparkoptions/js/colorpicker/css/colorpicker.css', false, UBERMENU_VERSION, 'all');

		//Really for UberMenu settings page (Preview)
		wp_enqueue_style( 'ubermenu-basic', $this->editionURL.'styles/basic.css', false, UBERMENU_VERSION, 'all' );
	}
	
	
	
	/*
	 * Custom Walker Name - to be overridden by Standard
	 */
	function editWalker( $className ){
		return 'UberMenuWalkerEdit';
	}
	
	
	/*
	 * Get the Image for a Menu Item via AJAX
	 */
	function getMenuImage_callback(){
		
		$id = $_POST['id'];
		
		$id = substr($id, (strrpos($id, '-')+1));
		
		$data = array();
		
		$ajax_nonce = wp_create_nonce( "set_post_thumbnail-$id" );
		$rmvBtn = '<div class="remove-item-thumb" id="remove-item-thumb-'.$id.
					'"><a href="#" id="remove-post-thumbnail-'.$id.
					'" onclick="wpmega_remove_thumb(\'' . $ajax_nonce . '\', '.
					$id.');return false;">' . esc_html__( 'Remove image' , 'ubermenu' ) . '</a></div>';
		
		$data['remove_nonce'] = $ajax_nonce;// $rmvBtn;
		$data['id'] = $id;
		
		$data['image'] = $this->getImage( $id );
		$this->JSONresponse( $data );
	}
	
	
	
	

	
	




	

	
	
	
	
	static function convertToOldParameter( $param ){
		$params = array(
			'menu-item-shortcode'			=> '_menu_item_shortcode',
			'menu-item-sidebars'			=> '_menu_item_sidebars',
			'menu-item-highlight'			=> '_menu_item_highlight',
			'menu-item-notext'				=> '_menu_item_notext',
			'menu-item-nolink'				=> '_menu_item_nolink',
			'menu-item-isheader'			=> '_menu_item_isheader',
			'menu-item-verticaldivision'	=> '_menu_item_verticaldivision',
			'menu-item-newcol'				=> '_menu_item_newcol',
			'menu-item-isMega'				=> '_menu_item_isMega',
			'menu-item-alignSubmenu'		=> '_menu_item_alignSubmenu',
			'menu-item-floatRight'			=> '_menu_item_floatRight',
			'menu-item-fullWidth'			=> '_menu_item_fullWidth',
			'menu-item-numCols'				=> '_menu_item_numCols',
		);
		return $params[ $param ];
	}
	
	
	
	/*
	 * Save the Menu Item Options for UberMenu
	 */
	function updateNavMenuItem( $menu_id, $menu_item_db_id, $args ){

		//Parse the serialized string of UberMenu Options into an array
		$uber_options_string = isset( $_POST['uber_options'][$menu_item_db_id] ) ? $_POST['uber_options'][$menu_item_db_id] : '';
		$uber_options = array();
		parse_str( $uber_options_string, $uber_options );
		
		$uber_options = wp_parse_args( $uber_options, $this->optionDefaults ); //only allow registered options

		update_post_meta( $menu_item_db_id, '_uber_options', $uber_options );
	
	}



	/**
	 * This function is paired with a JavaScript Override Function so that we can use our custom Walker rather
	 * than the built-in version.  This allows us to include the UberMenu Options as soon as an item is added to the menu,
	 * 
	 * This is a slightly edited version of case 'add-menu-item' : located in wp-admin/admin-ajax.php
	 * 
	 * In the future, if WordPress provides a hook or filter, this should be updated to use that instead.
	 * 
	 */
	function addMenuItem_callback(){
		
		check_ajax_referer( 'add-menu_item', 'menu-settings-column-nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) )
			wp_die('-1');

		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
	
		// For performance reasons, we omit some object properties from the checklist.
		// The following is a hacky way to restore them when adding non-custom items.
	
		$menu_items_data = array();
		foreach ( (array) $_POST['menu-item'] as $menu_item_data ) {
			if (
				! empty( $menu_item_data['menu-item-type'] ) &&
				'custom' != $menu_item_data['menu-item-type'] &&
				! empty( $menu_item_data['menu-item-object-id'] )
			) {
				switch( $menu_item_data['menu-item-type'] ) {
					case 'post_type' :
						$_object = get_post( $menu_item_data['menu-item-object-id'] );
					break;
	
					case 'taxonomy' :
						$_object = get_term( $menu_item_data['menu-item-object-id'], $menu_item_data['menu-item-object'] );
					break;
				}
	
				$_menu_items = array_map( 'wp_setup_nav_menu_item', array( $_object ) );
				$_menu_item = array_shift( $_menu_items );
	
				// Restore the missing menu item properties
				$menu_item_data['menu-item-description'] = $_menu_item->description;
			}
	
			$menu_items_data[] = $menu_item_data;
		}
	
		$item_ids = wp_save_nav_menu_items( 0, $menu_items_data );
		if ( is_wp_error( $item_ids ) )
			wp_die( 0 );
	
		$menu_items = array();

		foreach ( (array) $item_ids as $menu_item_id ) {
			$menu_obj = get_post( $menu_item_id );
			if ( ! empty( $menu_obj->ID ) ) {
				$menu_obj = wp_setup_nav_menu_item( $menu_obj );
				$menu_obj->label = $menu_obj->title; // don't show "(pending)" in ajax-added items
				$menu_items[] = $menu_obj;
			}
		}

		//$walker_class_name = apply_filters( 'wp_edit_nav_menu_walker', 'Walker_Nav_Menu_Edit', $_POST['menu'] );

		//if ( ! class_exists( $walker_class_name ) )
		//	wp_die( 0 );
	
		if ( ! empty( $menu_items ) ) {
			$args = array(
				'after' => '',
				'before' => '',
				'link_after' => '',
				'link_before' => '',
				//'walker' => new $walker_class_name,
				'walker' =>	new UberMenuWalkerEdit,			//EDIT FOR UBERMENU
			);
			echo trim( walk_nav_menu_tree( $menu_items, 0, (object) $args ) );
		}
		wp_die();
	}
	
	
	/*
	 * Create the UberMenu SparkOptions Panel and Settings object
	 */
	function optionsMenu(){
	
		$sparkOps = new UberOptions( 
							UBERMENU_SETTINGS, 
							
							//Menu Page
							array(
								'parent_slug' 	=> 'themes.php',
								'page_title'	=> __( 'UberMenu Options' , 'ubermenu' ),
								'menu_title'	=> __( 'UberMenu' , 'ubermenu' ),
								'menu_slug'		=> 'uber-menu',
								'version'		=> UBERMENU_VERSION,
							),
							
							$this->getOptionsMenuLinks(),

							$this->coreURL.'sparkoptions/'
							
						);
		
		
		
		/*
		 * Basic Config Panel
		 */
		$basic = 'basic-config';
		$sparkOps->registerPanel( $basic, __( 'Basic Configuration' , 'ubermenu' ), 10 );
		
		$sparkOps->addHidden( $basic , 'current-panel-id' , $basic );
		
		
		$weight = 15;

		/* Responsiveness */
		$sparkOps->addSubHeader( $basic, 
					'megaMenu-responsiveness',
					__( 'Responsive Settings' , 'ubermenu' ),
					__( 'Knowledgebase: <a target="_blank" href="http://sevenspark.com/docs/ubermenu-responsive" >UberMenu Responsiveness</a>' , 'ubermenu' ),
					'',
					$weight
					);
					
		$sparkOps->addCheckbox( $basic,
					'responsive-menu',
					__( 'Responsive Mega Menu' , 'ubermenu' ),
					__( 'Enable UberMenu\'s responsive properties. Requires a responsive theme, otherwise you may have '.
						'strange results if your theme is fixed-width.' , 'ubermenu' ),
					'on',
					'',
					$weight
					);

		$sparkOps->addCheckbox( $basic,
					'responsive-menu-toggle',
					__( 'Responsive Mega Menu - Toggle' , 'ubermenu' ),
					__( 'Display a toggle button to show/hide menu on mobile devices.' , 'ubermenu' ),
					'on',
					'',
					$weight
					);

		$sparkOps->addTextInput( $basic,
					'responsive-menu-toggle-text',
					__( 'Responsive Mega Menu - Toggle Text' , 'ubermenu' ),
					__( 'Text or HTML to display on Toggle Button.  Set to a single space to disable text.' , 'ubermenu' ),
					'Menu',
					'',
					'',
					$weight
					);

		$sparkOps->addCheckbox( $basic,
					'iOS-close-button',
					__( 'Display Close Button on iOS' , 'ubermenu' ),
					__( 'You can disable this if you do not wish to display the close button on iPads, iPhones, and iPods.' , 'ubermenu' ),
					'on',
					'',
					$weight
					);
			

		/* ANIMATIONS */
			
		$weight = 20;

		$sparkOps->addSubHeader( $basic, 
					'wpmega-animation-header',
					__( 'jQuery Animations &amp; Auto-positioning' , 'ubermenu' ),
					'',
					'',
					$weight
					);
		
		$sparkOps->addCheckbox( $basic,
					'wpmega-jquery',
					__( 'jQuery Enhanced' , 'ubermenu' ),
					__( 'Disable to use UberMenu without jQuery enhancement.  All submenus will be full-width by default.' , 'ubermenu' ),
					'on',
					'',
					$weight
					);
		
		$sparkOps->addRadio( $basic,
					'wpmega-transition',
					__( 'Animation' , 'ubermenu' ),
					'',
					array(
						'slide'	=>	__( 'Slide' , 'ubermenu' ),
						'fade'	=>	__( 'Fade' , 'ubermenu' ),
						'none'	=>	__( 'None' , 'ubermenu' ),
					),
					'slide',
					'',
					$weight
					);
					
		$sparkOps->addTextInput( $basic,
					'wpmega-animation-time',
					__( 'Animation Time' , 'ubermenu' ),
					__( 'Set how long the animation should take in milliseconds' , 'ubermenu' ),
					300,
					'spark-minitext',
					'ms',
					$weight
					);
					
		$sparkOps->addRadio( $basic,
					'wpmega-trigger',
					__( 'Trigger' , 'ubermenu' ),
					'',
					array(
							'hover'			=>	'Hover',
							'hoverIntent'	=>	'Hover Intent',
							'click'			=>	'Click'
						),
					'hoverIntent',
					'',
					$weight
					);
		
		
		$sparkOps->addTextInput( $basic,
					'wpmega-hover-interval',
					__( 'Hover Intent Interval' , 'ubermenu' ),
					__( 'The number of milliseconds before the hover event is triggered.  Defaults to 100. [Applies to trigger: Hover Intent]' , 'ubermenu' ),
					20,
					'spark-minitext',
					'ms',
					$weight
					);
					
		$sparkOps->addTextInput( $basic,
					'wpmega-hover-timeout',
					__( 'Hover Intent Timeout' , 'ubermenu' ),
					__( 'The number of milliseconds before the out event is triggered (milliseconds until the submenu closes after hover-off).  Defaults to 400. [Applies to trigger: Hover Intent]' , 'ubermenu' ),
					400,
					'spark-minitext',
					'ms',
					$weight	
					);			
					
		$sparkOps->addCheckbox( $basic,
					'wpmega-submenu-full',
					__( 'Full Width Submenus' , 'ubermenu' ),
					__( 'Enable to force all submenus to be full-width, regardless of their content.  Disable '.
						'to size submenus to their content and position with Javascript.' , 'ubermenu' ),
					'off',
					'',
					$weight
					);			
					
		
		
		
					
		/* Mobile Settings */

		$weight = 30;
		$sparkOps->addSubHeader( $basic, 
					'mobile-settings-header',
					__( 'Mobile Settings' , 'ubermenu' ),
					'',
					'',
					$weight
					);
					
		$sparkOps->addCheckbox( $basic,
					'android-click',
					__( 'Force Click Trigger on Android' , 'ubermenu' ),
					__( '<em>Makes Android interface act more like iOS</em>.  By default, Android will redirect to the top level menu item link URL as soon as it is tapped.  To display a submenu, users must '.
					'tap, and without lifting their finger slide off of the menu item.  By enabling Force-Click, the menu touch interface on Android will '.
					'work similarly to iOS: a tap will open the sub menu.  Tap + hold will follow the link.' , 'ubermenu' ),
					'off',
					'',
					$weight
					);	

		/* Special */
		$weight = 35;
		$sparkOps->addSubHeader( $basic, 
					'basic-other-settings',
					__( 'Submenu Positioning' , 'ubermenu' ),
					'',
					'',
					$weight
					);
		$sparkOps->addCheckbox( $basic,
					'reposition-on-load',
					__( 'Reposition submenus on window.load' , 'ubermenu' ),
					__( 'If your submenus aren\'t positioned properly, it may be because you have a font (like @font-face or Cufon) that needs to load before '.
						'the menu items are positioned.  Turn this on to position your submenus once your font finishes loading.' , 'ubermenu' ),
					'off',
					'',
					$weight
					);
		


		
		/* 
		 * DESCRIPTION, SHORTCODE, WIDGET SETTINGS 
		 */
		$descConfig = 'description-config';
		$sparkOps->registerPanel( $descConfig, __( 'Descriptions' , 'ubermenu' ) , 20 );
		
		$sparkOps->addSubHeader( $descConfig, 
					'wpmega-desc-header',
					__( 'Descriptions' , 'ubermenu' )
					);
		
		$sparkOps->addInfobox( $descConfig,
					'wpmega-descriptions',
					'',
					__( 'You can turn on and off menu item descriptions on a per-level basis.' , 'ubermenu' )
					);
		
		$sparkOps->addCheckbox( $descConfig,
					'wpmega-description-0',
					__( 'Display Top-Level Descriptions' , 'ubermenu' )
					);
					
		$sparkOps->addCheckbox( $descConfig,
					'wpmega-description-1',
					__( 'Display Sub-Header Descriptions' , 'ubermenu' )
					);
					
		$sparkOps->addCheckbox( $descConfig,
					'wpmega-description-2',
					__( 'Display Sub-Menu Item Descriptions' , 'ubermenu' )
					);	
					
		
		
							
							
							
		
		
	
	
	
	
	
		
					
					
		
		/* STYLE SETTINGS */
		$styleConfig = 'style-config';
		$sparkOps->registerPanel( $styleConfig, __( 'Style Configuration' , 'ubermenu' ) , 40 );
		
		$sparkOps->addInfobox( $styleConfig, 
					'wpmega-style-settings', 
					'',
					__( 'Configure how you want to apply styles to the menu' , 'ubermenu' )
					);
		
		$sparkOps->addRadio( $styleConfig,
					'wpmega-style',
					__( 'Style Application' , 'ubermenu' ),
					'',
					$this->getStyleApplicationOptions(),
					'preset'
					);
					
		$sparkOps->addSelect( $styleConfig,
					'wpmega-style-preset',	
					__( 'Style Preset' , 'ubermenu' ),
					__( 'Only applicable if <strong>Use a Preset</strong> is selected above.  <strong><a target="_blank" href="http://goo.gl/4lnno">Get More Skins</a></strong>' , 'ubermenu' ),
					'ubermenu_getStylePresetOps',
					array( 'execute', &$this , 'getDefaultPreset' )
					);
					

		
		return $sparkOps;
		
	}

	

	function getStyleApplicationOptions(){
		return array(
			'preset'	=>	__( 'Use a Preset' , 'ubermenu' ) .
							'<span class="spark-admin-op-radio-desc">'. __( 'Select from the Preset Styles below'  , 'ubermenu' ) .'</span>',
			
			'none'		=>	__( 'Do Nothing ' , 'ubermenu' ) .
							'<span class="spark-admin-op-radio-desc">'.__( 'I will manually include the skin styles elsewhere, like in my <code>style.css</code>.' , 'ubermenu' ).'</span>',
		);
	}



	function getNavMenus(){
		$menus = array();
		foreach( wp_get_nav_menus() as $m ){
			$menus[$m->slug] = $m->name;
		}
		return $menus;
	}


	function menuItemCustomOptions( $item_id ){
		
		?>
	
			<!--  START MEGAWALKER ATTS -->
			<div class="uber_clear">
				<a href="#" class="wpmega-showhide-menu-ops"><?php _e( 'Show/Hide UberMenu Options' , 'ubermenu' ); ?></span></a>
				<span class="ss-info-container">?<span class="ss-info"><?php _e( 'UberMenu options set here only apply to menus that have been activated in the "Activate UberMenu Locations" meta box.' , 'ubermenu' ); ?></span></span> 
					
				<div class="wpmega-atts wpmega-unprocessed">
					<input id="uber_options-<?php echo $item_id;?>" class="uber_options_input" name="uber_options[<?php echo $item_id;?>]" 
						type="hidden" value="" />

					<?php $this->showMenuOptions( $item_id ); ?>
									
				</div>
				<!--  END MEGAWALKER ATTS -->
			</div>
	<?php
	}

	function showMenuOptions( $item_id ){

		global $uberMenu;
		$settings = $uberMenu->getSettings();

		$this->showCustomMenuOption(
			'isMega', 
			$item_id, 
			array(
				'level'	=> '0', 
				'title' => __( 'Make this item\'s submenu a mega menu.  Leave unchecked to use a flyout menu, or for menu items without submenus (this will remove the submenu indicator).' , 'ubermenu' ), 
				'label' => __( 'Activate Mega Menu' , 'ubermenu' ), 
				'type' 	=> 'checkbox',
				'default' => 'on'
			)
		);
		
		
		
		$this->showCustomMenuOption(
			'fullWidth', 
			$item_id, 
			array(
				'level' => '0', 
				'title' => __( 'Make this item\'s submenu the full width of the menu bar.  Note that with javascript disabled, submenus are full-width by default.  This is horizontal-orientation specific.  To make a vertical menu full-width, set the width appropriately in the Basic Configuration Options.' , 'ubermenu' ), 
				'label' => __( 'Full Width Submenu' , 'ubermenu' ), 
				'type' 	=> 'checkbox', 
			)
		);

		$this->showCustomMenuOption(
			'alignSubmenu', 
			$item_id, 
			array(
				'level' => '0', 
				'title' => __( 'Select where to align the submenu.  Note that only mega submenus can be centered, and only if jQuery Enhancements are enabled.  Horizontal-orientation specific.' , 'ubermenu' ), 
				'label' => __( 'Align Submenu' , 'ubermenu' ), 
				'type' 	=> 'select', // 'checkbox',
				'ops'	=>	array(
					'left'	=>	__( 'Left' , 'ubermenu' ),
					'center'=>	__( 'Center (Mega submenus only - Left for flyout)' , 'ubermenu' ),
					'right'	=>	__( 'Right' , 'ubermenu' ),
				),
				'default'	=>	'center'
			)
		);

		
		$this->showCustomMenuOption(
			'numCols', 
			$item_id, 
			array(
				'level' => '0', 
				'title' => __( '<strong>Only valid for full-width submenus</strong>.  Set how many columns should be in each row in the submenu.  Columns will be sized evenly.' , 'ubermenu' ), 
				'label' => __( 'Submenu Columns [FullWidth]' , 'ubermenu' ), 
				'type' 	=> 'select', // 'checkbox',
				'ops'	=>	array(
					'auto'	=> __( 'Automatic' , 'ubermenu' ),
					'1'		=>	'1',
					'2'		=>	'2',
					'3'		=>	'3',
					'4'		=>	'4',
					'5'		=>	'5',
					'6'		=>	'6',
					'7'		=>	'7',
				),
				'default'	=>	'auto'
			)
		);
		
		
		//here
		
		$this->showCustomMenuOption(
			'isheader', 
			$item_id, 
			array(
				'level' => '2-plus', 
				'title' => __( 'Display this item as a header, like second-level menu items.  Good for splitting columns vertically without starting a new row' , 'ubermenu' ), 
				'label' => __( 'Header Display' , 'ubermenu' ), 
				'type' => 'checkbox', 
			)
		);

		$this->showCustomMenuOption(
			'highlight', 
			$item_id, 
			array(
				'level' => '0-plus', 
				'title' => __( 'Make this item stand out.' , 'ubermenu' ), 
				'label' => __( 'Highlight this item' , 'ubermenu' ), 
				'type' => 'checkbox', 
			)
		);

		$this->showCustomMenuOption(
			'newcol', 
			$item_id, 
			array(
				'level' => '2-plus', 
				'title' => __( 'Use this on the item that should start the second column under the same header - for example, have two columns under "Sports"' , 'ubermenu' ), 
				'label' => __( 'Start a new column (under same header)?' , 'ubermenu' ), 
				'type' => 'checkbox', 
			)
		);

		$this->showCustomMenuOption(
			'verticaldivision', 
			$item_id, 
			array(
				'level' => '1', 
				'title' => __( 'Begin a new row with this item.  You should always check this on the first item in the second row of your submenu.' , 'ubermenu' ), 
				'label' => __( 'New Row (Vertical Division)' , 'ubermenu' ), 
				'type' => 'checkbox', 
			)
		);

		$this->showCustomMenuOption(
			'nolink', 
			$item_id, 
			array(
				'level' => '0-plus', 
				'title' => __( 'Remove the link altogether.  Can be used, for example, with content overrides or widgets.' , 'ubermenu' ), 
				'label' => __( 'Disable Link' , 'ubermenu' ), 
				'type' 	=> 'checkbox', 
			)
		);
		
			

	}

	function getMenuItemOption( $item_id , $id ){		

		$option_id = 'menu-item-'.$id;

		//We haven't investigated this item yet
		if( !isset( $this->menuItemOptions[ $item_id ] ) ){
			
			$uber_options = get_post_meta( $item_id , '_uber_options', true );
			//If $uber_options are set, use them
			if( $uber_options ){
				//echo '<pre>'; print_r( $uber_options ); echo '</pre>';
				$this->menuItemOptions[ $item_id ] = $uber_options;
			} 
			//Otherwise get the old meta
			else{
				return get_post_meta( $item_id, '_menu_item_'.$id , true );
			}
		}
//echo '<pre>'; print_r( $this->menuItemOptions[ $item_id ] ); echo '</pre>';
//		echo '['.(isset( $this->menuItemOptions[ $item_id ][ $option_id ] ) ? $this->menuItemOptions[ $item_id ][ $option_id ] : '').']';
		return isset( $this->menuItemOptions[ $item_id ][ $option_id ] ) ? stripslashes( $this->menuItemOptions[ $item_id ][ $option_id ] ) : '';

	}

	function showCustomMenuOption( $id, $item_id, $args ){
		extract( wp_parse_args(
			$args, array(
				'level'	=> '0-plus',
				'title' => '',
				'label' => '',
				'type'	=> 'text',
				'ops'	=>	array(),
				'default'=> '',
			) )
		);

		$_val = $this->getMenuItemOption( $item_id , $id );
	
		global $uberMenu;
		$settings = $uberMenu->getSettings();
		
		$desc = '<span class="ss-desc">'.$label.'<span class="ss-info-container">?<span class="ss-info">'.$title.'</span></span></span>';
		?>
				<p class="field-description description description-wide wpmega-custom wpmega-l<?php echo $level;?> wpmega-<?php echo $id;?>">
					<label for="edit-menu-item-<?php echo $id;?>-<?php echo $item_id;?>">
						
						<?php
						
						switch($type) {
							
							case 'text': 
								echo $desc;
								?>						
								<input type="text" id="edit-menu-item-<?php echo $id;?>-<?php echo $item_id;?>" 
									class="edit-menu-item-<?php echo $id;?>" 
									name="menu-item-<?php echo $id;?>[<?php echo $item_id;?>]" 
									size="30" 
									value="<?php echo htmlspecialchars( $_val );?>" />
								<?php
								
								break;

							case 'textarea':
								echo $desc;
								?>
								<textarea id="edit-menu-item-<?php echo $id;?>-<?php echo $item_id;?>"
									 class="edit-menu-item-<?php echo $id;?>"
									 name="menu-item-<?php echo $id;?>[<?php echo $item_id;?>]" ><?php
										echo htmlspecialchars( $_val );
									 ?></textarea>
								<?php
								break;

							case 'checkbox':
								?>
								<input type="checkbox" 
									id="edit-menu-item-<?php echo $id;?>-<?php echo $item_id;?>" 
									class="edit-menu-item-<?php echo $id;?>" 
									name="menu-item-<?php echo $id;?>[<?php echo $item_id;?>]" 
									<?php
										if ( ( $_val == '' && $default == 'on' ) || 
												$_val == 'on')
											echo 'checked="checked"';
									?> />
								<?php
								echo $desc;
								break;
								
							case 'select':
								echo $desc;
								//$_val = get_post_meta($item_id, '_menu_item_' . $id, true);
								if( empty($_val) ) $_val = $default;
								?>
								<select 
									id="edit-menu-item-<?php echo $id; ?>-<?php echo $item_id; ?>"
									class="edit-menu-item-<?php echo $id; ?>"
									name="menu-item-<?php echo $id;?>[<?php echo $item_id;?>]">
									<?php foreach( $ops as $opval => $optitle ): ?>
										<option value="<?php echo $opval; ?>" <?php if( $_val == $opval ) echo 'selected="selected"'; ?> ><?php echo $optitle; ?></option>
									<?php endforeach; ?>
								</select>
								<?php
								break;
								
							case 'sidebarselect':
								echo $desc;
								if( $settings->op( 'wpmega-sidebars' ) > 0){
									echo $uberMenu->sidebarSelect( $item_id , $_val );
								}
								else echo '<div><small class="uber_clear">'.__( 'You currently have 0 widget areas set in your UberMenu options.' , 'ubermenu' ).'</small></div>';
								break;
	
						}
 						?>
						
					</label>
				</p>
	<?php
	}


	function getOptionsMenuLinks(){
		//Links
		$links = array(

			1	=>	array(
				'href'	=>	'http://j.mp/dPmJ8m',
				'class'	=>	'spark-outlink-hl',
				'title'	=>	__( 'Read the Support Manual' , 'ubermenu' ),
				'text'	=>	__( 'Support Manual &rarr;' , 'ubermenu' ),							
			),

			2	=>	array(
				'href'	=>	'http://goo.gl/G8b3r',
				'class'	=>	'spark-outlink-hl',
				'title'	=>	__( 'Watch Video Tutorials' , 'ubermenu' ),
				'text'	=>	__( 'Video Tutorials &rarr;' , 'ubermenu' ),			
			),
			
			3	=>	array(
				'href'	=>	'http://j.mp/fDpVkP',
				'class'	=>	'spark-outlink',
				'title'	=>	__( 'Get images, widgets, shortcodes, and more with a full UberMenu license.' , 'ubermenu' ),
				'text'	=>	__( 'Upgrade to UberMenu Plugin &rarr;' , 'ubermenu' )
			)
		);

		return $links;
	}




	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	 * Generates a Preview Menu for display in the control panel
	 */
	function getPreview_callback(){
				
		$d = $_POST['data'];
		wp_parse_str($d, $data);
		
		$style_source = $data['wpmega-style'];
		
		$style = '';
		$link = '';

		
		// Generate CSS
		$settings = $this->getStyleSettings( $data );
		$gen = new StyleGenerator( UBERMENU_LESS );
		$style = $gen->generateCSS( $settings );
			
		$html = '<h3>'.__( 'Menu Preview' , 'ubermenu' ).' <span class="spark-preview-close"></span></h3>';
		$html.= '<div class="ss-preview-note spark-infobox spark-infobox-warning">'.__( 'Note: The menu preview gives a general impression of colors and styles, but will not give an exact representation of '.
				'the menu in all cases - especially when using advanced methods like widgets and shortcodes.' , 'ubermenu' ).'</div>';
		
		$html.= wp_nav_menu( array( 'menu' => $data['menu-preview'], 'megaMenu' => TRUE, 'echo' => false , 'preview' => true ) );
	
		$json 			= array();
		$json['content']= $this->escapeNewlines( $html );
		$json['style']	= $this->escapeNewlines( $style );
		$json['link'] 	= $link;
		
		$this->JSONresponse($json);
	}
	
	
	/*
	 * Style Generator - LESS PHP
	 */
	function getStyleSettings( $data ){
		
		$settings = array();
		
		
		//Menu Bar
		
		//Background Gradient
		$settings['menubarColorStart'] 		= 	$this->colorOrTransparent( $data['menu-bar-background'] );
		$settings['menubarColorEnd']		=	$this->colorOrTransparent( $data['menu-bar-background-color2'], $settings['menubarColorStart'] );
		$settings['menubarBorder']			=	empty( $data['menu-bar-border-color'] ) ? 'none' : '1px solid '.$this->colorOrTransparent( $data['menu-bar-border-color'] );
		$settings['menubarRadius']			=	$this->orDefault( $data['menu-bar-border-radius'], 0 ).'px';
				
		//Top Level
		$settings['topLevelFontSize'] 		= 	$this->orDefault( $data['top-level-item-font-size'] , '12px' );
		$settings['topLevelColor']			=	$this->colorOrTransparent( $data['top-level-item-font-color'] , '#333' );
		$settings['topLevelColorHover']		=	$this->colorOrTransparent( $data['top-level-item-font-color-hover'] , $settings['topLevelColor'] );
		$settings['topLevelTextShadow']		=	'0 -1px 1px '.$this->colorOrTransparent( $data['top-level-item-text-shadow'] );
		$settings['topLevelTextShadowHover']=	'0 -1px 1px '.$this->colorOrTransparent( $data['top-level-item-text-shadow-hover'] );
		$settings['topLevelTextTransform']	=	$this->orDefault( $data['top-level-text-transform'], 'none' );
		$settings['topLevelTextWeight']		=	$this->orDefault( $data['top-level-text-weight'] , 'normal' );
		
		$settings['currentColor']			=	$this->colorOrTransparent( $data['top-level-item-font-color-current'] , '#000' );
		$settings['topLevelPaddingX']		=	$this->orDefault( $data['top-level-item-padding-x'].'px' , '15px' );
		$settings['topLevelPaddingY']		=	$this->orDefault( $data['top-level-item-padding-y'].'px' , '12px' );
		$settings['topLevelDividerColor'] 	=	$this->colorOrTransparent( $data['top-level-item-border'] ); //, '#e0e0e0'
		$settings['topLevelGlowOpacity'] 	=	$this->orDefault( $data['top-level-item-glow-opacity'], '.9' , true );

		$settings['topLevelBackgroundHoverStart'] 	=  $this->colorOrTransparent( $data['top-level-item-background-hover'] );
		$settings['topLevelBackgroundHoverEnd'] 	= $this->colorOrTransparent( $data['top-level-item-background-hover-color2'], $settings['topLevelBackgroundHoverStart'] );
		$settings['topLevelGlowOpacityHover'] 		=	$this->orDefault( $data['top-level-item-glow-opacity-hover'], '.9' , true );

		
		//Submenu
		$settings['subMenuBorderColor']		=	$this->colorOrTransparent( $data['sub-menu-border'] );
		//$settings['subMenuMarginTop']		=	'0px'; //$settings['menubarBorder'] == 'none' ? '0' : '1px';
		$settings['submenuColorStart']		=	$this->colorOrTransparent( $data['sub-level-background'] );
		$settings['submenuColorEnd']		=	$this->colorOrTransparent( $data['sub-level-background-color2'] , $settings['submenuColorStart'] );
		
		$settings['subMenuColor']			=	$this->colorOrTransparent( $data['sub-level-item-font-color'] , '#000' );
		$settings['subMenuTextShadow']		=	'0px 1px 1px '.$this->colorOrTransparent( $data['sub-level-text-shadow'] );
		$settings['subMenuBoxShadow']		=	'1px 1px 1px '.$this->colorOrTransparent( $data['sub-level-box-shadow'] );
		$settings['subMenuMinColWidth']		=	$this->orDefault( $data['sub-level-column-width'].'px', '120px' );
		

		//Submenu Headers
		$settings['subHeaderColor']			=	$this->colorOrTransparent( $data['sub-level-header-font-color'] , '#777' );
		$settings['subHeaderColorHover']	=	$this->colorOrTransparent( $data['sub-level-header-font-color-hover'] , $settings['subHeaderColor']	);
		$settings['subHeaderSize']			=	$this->orDefault( $data['sub-level-header-font-size'] , '12px' );
		$settings['subHeaderWeight']		=	$this->orDefault( $data['sub-level-header-font-weight'] , 'normal' );
		$settings['subHeaderBorderBottom']	=	$data['sub-level-header-border-style'] == 'none' 
													? 'none' 
													: '1px '.$this->orDefault( $data['sub-level-header-border-style'], 'none' ).' '.$this->colorOrTransparent( $data['sub-level-header-border-color'] );
		$settings['subHeaderMarginBottom']	=	$settings['subHeaderBorderBottom'] == 'none' ? '.4em' : '.6em';
		
		//Submenu Links
		$settings['subLinkColor']			=	$this->colorOrTransparent( $data['sub-level-link-font-color'] , '#888' );
		$settings['subLinkColorHover']		=	$this->colorOrTransparent( $data['sub-level-link-font-color-hover'] , '#000' );
		$settings['subLinkSize']			=	$this->orDefault( $data['sub-level-link-font-size'] , '12px' );
		$settings['subLinkBackground']		=	$this->colorOrTransparent( $data['sub-level-link-background'] );
		$settings['subLinkBackgroundHover']	=	$this->colorOrTransparent( $data['sub-level-link-background-hover'] );
 		
		//Misc
		$settings['highlightColor']			=	$this->colorOrTransparent( $data['sub-level-highlight-color'] , $settings['subLinkColor'] );
		$settings['descriptionSize']		=	$this->orDefault( $data['menu-description-size'], '9px' );
		$settings['descriptionColor']		=	$this->colorOrTransparent( $data['menu-description-color'] , '#bbb' );
		$settings['descriptionTransform']	=	$this->orDefault( $data['description-transform'], 'none' );
 		
		//Arrows
		$settings['topLevelArrowColor']		= 	$this->colorOrTransparent( $data['top-level-arrow-color'] );
		$settings['flyoutArrowColor'] 		=	$this->colorOrTransparent( $data['sub-level-arrow-color'] );
		
		//Images
		$settings['imageWidth']				=	$this->orDefault( $data['wpmega-image-width'].'px' , '16px' );
		$settings['imageHeight']			=	$this->orDefault( $data['wpmega-image-height'].'px' , '16px' );
		
		// @imageWidth: 15px;
		// @imageHeight: 15px;

		//Search Bar
		$settings['searchTextColor']		=	$this->colorOrTransparent( $data['search-text-color'] , '#fff' );
		$settings['searchTextBackground']	=	$this->colorOrTransparent( $data['search-text-background'] , '#666' );
		$settings['searchSubmitTextColor']	=	$this->colorOrTransparent( $data['search-submit-text-color'] , '#fff' );
		$settings['searchSubmitBackground']	=	$this->colorOrTransparent( $data['search-submit-background'] , '#666' );
		$settings['searchSubmitTextColorHover']	=	$this->colorOrTransparent( $data['search-submit-text-color-hover'] , '#fff' );
		$settings['searchSubmitBackgroundHover']	=	$this->colorOrTransparent( $data['search-submit-background-hover'] , '#222' );
		
		return $settings;		
	}
	
	//utility helper
	function orDefault( $val , $default , $zeroValid = false ){	
		if( $zeroValid ){
			if( $val === 0 || $val === '0' ) return $val;
		}
		return empty( $val ) ? $default : $val;
	}
	//utility helper
	function colorOrTransparent( $val, $default = 'transparent' ){
		if( empty( $val ) ){
			$val = $default;
		}
		//Only add '#' if it doesn't already exist
		else if( strpos( $val , '#' ) === false ) $val = '#'.$val;
		return $val;
	}
	
	function saveStyleGenerator( $saveOps ){
		
		$sheetWritten = 'off';
		
		//If we're using Style Generator Styles ('inline' is legacy)
		if( $saveOps['wpmega-style'] == 'inline' ){
		
			$styleSettings = $this->getStyleSettings( $saveOps );
			$gen = new StyleGenerator( UBERMENU_LESS );
			$style = $gen->generateCSS( $styleSettings );
			
			//Save the CSS to the DB
			update_option( UBERMENU_STYLES , $style );
			
			//Write File (if option set)
			if( $saveOps['save-style-gen-to-file'] == 'on' ){
				
				//Append a Comment to the beginning of the style /* Generated for ___ on ___ date - if you want to customize, copy to custom.css */
				//Set a message if file could not be written
				
				$site = get_bloginfo('wpurl');
				$date = date('F j, Y H:i:s');
				$prepend = "/*\n * Generated for $site on $date by UberMenu Style Generator \n * To customize this file, copy it to custom.css and have at it! \n */ \n\n";
				
				$sheetWritten = 'on';
				if( $gen->writeStylesheet( UBERMENU_GEN_SKIN , $prepend ) === false ){
					//write failed
					$sheetWritten = 'off';
					$this->settings->warning = 'The stylesheet '.UBERMENU_GEN_SKIN.' could not be written.  Styles will be loaded in the site &lt;head&gt; instead.';
				}	
			}
		}
		$this->settings->settings['use-gen-skin'] = $sheetWritten;
	}
	
	/*
	 * Escape newlines, tabs, and carriage returns
	 */
	function escapeNewlines($html){
		
		$html = str_replace("\n", '\\n', $html);
		$html = str_replace("\t", '\\t', $html);
		$html = str_replace("\r", '\\r', $html);
		
		return $html;
		
	}
	
	
	/*
	 * Prints a json response
	 */
	function JSONresponse($data){
			
		header('Content-Type: application/json; charset=UTF-8');	//Set the JSON header so that JSON will validate Client-Side
		
		echo '{ '.$this->buildJSON($data).' }';					//Send the response
			
		die();
	}

	/*
	 * Builds a json object from an array
	 */
	function buildJSON($ar){
		if($ar == null) return '';
		$txt = '';
		$count = count($ar);
		$k = 1;
		foreach($ar as $key=>$val){	
			$comma = ',';
			if($count == 1 || $count == $k) $comma = '';
			
			if(is_array($val)){
				$txt.= '"'.$key.'" : { ';
				$txt.= $this->buildJSON($val);	//recurse
				$txt.= ' }'.$comma."\n ";
			}
			else{
				$quotes = is_numeric($val) ? FALSE : TRUE;	
				$txt.= '"' . str_replace('-', '_', $key).'" : ';
				if($quotes) $txt.= '"';
				$txt.= str_replace('"','\'', $val);
				if($quotes) $txt.= '"';
				$txt.= $comma."\n";			
			}
			$k++;
		}
		return $txt;
	}
	
	
	
	
	
	/*
	 * Register a Style Preset Option
	 */
	function registerStylePreset($id, $name, $path, $top = false, $src = 'standard' ){
		$this->stylePresets[$id] = array(
			'name'	=>	$name,
			'path'	=>	$path,
			'top'	=>	$top,
			'src'	=>	$src
		);
	}
	
	/*
	 * Get the registered Style Presets as an array of options
	 */
	function getStylePresetOps( $src = '' ){		

		$ops = array( 'none' => '&nbsp;');
		$tops = array();
		
		foreach( $this->stylePresets as $id => $s ){

			if( is_array( $src ) && !in_array( $s['src'] , $src ) ) continue;

			if( $s['top'] ){
				$tops[$id] = $s['name'];
			}
			else $ops[$id] = $s['name'];
		}
		
		if( !empty( $tops ) ){
			$ops = array_merge( $tops, $ops );
		}
		
		return $ops;	
	}
	
	/* Here is where the Presets are registered */
	function registerStylePresets(){
		
		$this->stylePresets = array();
		
		$this->registerStylePreset( 'grey-white', 		__( 'Black and White' , 'ubermenu' ), 				$this->coreURL.'styles/skins/blackwhite.css');
		$this->registerStylePreset( 'black-white-2',	__( 'Black and White 2.0' , 'ubermenu' ),			$this->coreURL.'styles/skins/blackwhite2.css');
		$this->registerStylePreset( 'vanilla', 			__( 'Vanilla' , 'ubermenu' ), 						$this->coreURL.'styles/skins/vanilla.css');
		$this->registerStylePreset( 'vanilla-bar', 		__( 'Vanilla Bar' , 'ubermenu' ), 					$this->coreURL.'styles/skins/vanilla_bar.css');
		
	}

	function checkPostLimits(){
		$screen = get_current_screen();
		if( $screen->id != 'nav-menus' ) return;

		$currentPostVars_count = $this->countPostVars();
		/*
		$count = 0;
		foreach( $_POST as $key => $arr ){
			$c = count( $arr );
			echo $key . ' :: ' . $c .  "<br/>";
			$count+= $c;
		}
		echo $count.'<br/>';
		umssd( $_POST );
		*/
		

		$r = array(); //restrictors

		$r['suhosin_post_maxvars'] = ini_get( 'suhosin.post.max_vars' );
		$r['suhosin_request_maxvars'] = ini_get( 'suhosin.request.max_vars' );
		$r['max_input_vars'] = ini_get( 'max_input_vars' );


		//$r['max_input_vars'] = 1355;

		if( $r['suhosin_post_maxvars'] != '' ||
			$r['suhosin_request_maxvars'] != '' ||
			$r['max_input_vars'] != '' ){


			$message = array();

			if( ( $r['suhosin_post_maxvars'] != '' && $r['suhosin_post_maxvars'] < 1000 ) || 
				( $r['suhosin_request_maxvars']!= '' && $r['suhosin_request_maxvars'] < 1000 ) ){
				$message[] = __( "Your server is running Suhosin, and your current maxvars settings may limit the number of menu items you can save." , 'ubermenu' );
			}


			//150 ~ 10 left
			foreach( $r as $key => $val ){
				if( $val > 0 ){
					if( $val - $currentPostVars_count < 150 ){
						$message[] = __( "You are approaching the post variable limit imposed by your server configuration.  Exceeding this limit may automatically delete menu items when you save.  Please increase your <strong>$key</strong> directive in php.ini.  <a href='http://sevenspark.com/out/ubermenu-item-limit'>More information</a>" , 'ubermenu' );
					}
				}
			}

			if( !empty( $message ) ): ?>
			<div class="spark-infobox spark-infobox-warning error">
				<h4><?php _e( 'Menu Item Limit Warning' , 'ubermenu' ); ?></h4>
				<ul>
				<?php foreach( $message as $m ): ?>
					<li><?php echo $m; ?></li>
				<?php endforeach; ?>
				</ul>

				<?php
				if( $r['max_input_vars'] != '' ) echo "<strong>max_input_vars</strong> :: ". 
					$r['max_input_vars']. " <br/>";
				if( $r['suhosin_post_maxvars'] != '' ) echo "<strong>suhosin.post.max_vars</strong> :: ".$r['suhosin_post_maxvars']. " <br/>";
				if( $r['suhosin_request_maxvars'] != '' ) echo "<strong>suhosin.request.max_vars</strong> :: ". $r['suhosin_request_maxvars'] ." <br/>";
				
				echo "<br/><strong>".__( 'Menu Item Post variable count on last save' )."</strong> :: ". $currentPostVars_count."<br/>";
				if( $r['max_input_vars'] != '' ){
					$estimate = ( $r['max_input_vars'] - $currentPostVars_count ) / 15;
					if( $estimate < 0 ) $estimate = 0;
					echo "<strong>".__( 'Approximate remaining menu items' , 'ubermenu' )."</strong> :: " . floor( $estimate );
				};

				echo "<br/><br/>Loaded configuration file: ". php_ini_loaded_file();

				?>


			</div>
			<?php endif; 

		}

	}

	function countPostVars() {

		if( isset( $_POST['save_menu'] ) ){

			$count = 0;
	    	foreach( $_POST as $key => $arr ){
				$count+= count( $arr );
			}

			update_option( 'ubermenu-post-var-count' , $count );
		}
		else{
			$count = get_option( 'ubermenu-post-var-count' , 0 );
		}

		return $count;
	}
	
}