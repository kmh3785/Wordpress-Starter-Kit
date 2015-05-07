<?php
/**
 * Add the Pro Sub Sections for all Instances
 */
add_filter( 'ubermenu_settings_subsections' , 'ubermenu_pro_instance_subsections' , 20 , 2 );
function ubermenu_pro_instance_subsections( $subsections , $config_id ){
	return array(
		'integration'	=> array(
			'title'	=> __( 'Integration' , 'ubermenu' ),
		),
		'basic' => array(
			'title' => __( 'Basic Configuration' , 'ubermenu' ),
		),
		'position'	=> array(
			'title'	=> __( 'Position & Layout' , 'ubermenu' ),
		),
		'submenus'	=> array(
			'title'	=> __( 'Submenus' , 'ubermenu' ),
		),
		'descriptions'	=> array(
			'title'	=> __( 'Descriptions' , 'ubermenu' ),
		),
		// 'custom_content'	=> array(
		// 	'title'	=> __( 'Custom Content' , 'ubermenu' ),
		// ),
		'images'	=> array(
			'title'	=> __( 'Images' , 'ubermenu' ),
		),
		'responsive'	=> array(
			'title'	=> __( 'Responsive & Mobile' , 'ubermenu' ),
		),
		'style_customizations' => array(
			'title'	=> __( 'Style Customizations' , 'ubermenu' ),
		),
		'icons'	=> array(
			'title'	=> __( 'Icons' , 'ubermenu' ),
		),
		'fonts'	=> array(
			'title'	=> __( 'Fonts' ),
		),
		'misc'	=> array(
			'title'	=> __( 'Miscellaneous' , 'ubermenu' ),
		),
		'advanced'	=> array(
			'title'	=> __( 'Advanced' , 'ubermenu' ),
		),
		'export'	=> array(
			'title'	=> __( 'Import/Export' , 'ubermenu' ),
		),
	);
}

/**
 * Add the Pro settings for all Instances
 */
function ubermenu_menu_integration_code( $args , $config_id ){

	//$shortcode = '';
	//$api = '';

	// if( $menu_id == '_default' ){
	// 	$shortcode = '<code class="ubermenu-highlight-code">[ubermenu config="'.$config_id.'"]</code>'; //Toggle Content[/ubermenu_toggle]</code>'
	// 	$api = '<code class="ubermenu-highlight-code">&lt;?php ubermenu( \''.$config_id.'\' ); ?&gt;</code>';
	// }
	//else{
		// $shortcode = '<code class="ubermenu-highlight-code">[ubermenu config="'.$config_id.'" menu="'.$menu_id.'"]</code>';//Toggle Content[/ubermenu_toggle]</code>'
		// $api = '<code class="ubermenu-highlight-code">&lt;?php ubermenu( \''.$config_id.'\' , array( \'menu\' => '.$menu_id.' ) ); ?&gt;</code>';
	//}

	$shortcode = '<code class="ubermenu-highlight-code">[ubermenu config_id="'.$config_id.'"';
	$api = '<code class="ubermenu-highlight-code">&lt;?php ubermenu( \''.$config_id.'\' ';
	if( is_array( $args ) && !empty( $args ) ){
		$api.= ', array( ';
		$k = 0;
		foreach( $args as $key => $val ){
			$shortcode.= ' '.$key.'="'.$val.'"';

			if( $k>0 ) $api.= ",";

			if( !is_numeric( $val ) ) $val = "'$val'";
			$api.= "'$key' => $val ";

			$k++;
		}
		$api.= ') ';
	}
	$shortcode.= ']</code>';
	$api.= '); ?&gt;</code>';

	$code_id = '_default';
	if( isset( $args['theme_location'] ) ) $code_id = $args['theme_location'];
	else if( isset( $args['menu'] ) ) $code_id = $args['menu'];

	$code = 
		'<div class="ubermenu-integration-code ubermenu-integration-code-'.$code_id.'">'.
			'<div class="ubermenu-desc-row">
				<span class="ubermenu-code-snippet-type">PHP</span> '.$api.'
			</div>
			<div class="ubermenu-desc-row">
				<span class="ubermenu-code-snippet-type">Shortcode</span> '.$shortcode.'				
			</div>
			<p class="ubermenu-sub-desc ubermenu-desc-mini" >Click to select, then <strong><em>&#8984;+c</em></strong> or <strong><em>ctrl+c</em></strong> to copy to clipboard</p>
			<p class="ubermenu-sub-desc ubermenu-desc-understated">Pick the appropriate code and add to your template or content where you want the menu to appear.</p>'.
		'</div>';

	return $code;
}
function ubermenu_integration_code_ui( $config_id ){
	$integration_code = '<div class="ubermenu-integration-code-wrap">'.ubermenu_menu_integration_code( array() , $config_id );

	$menu_select = '<h4>Integrate Specific Menu</h4>';
	$loc_select = '<h4>Integrate Specific Theme Location</h4>';

	$menus = wp_get_nav_menus( array('orderby' => 'name') );
	
	if( is_array( $menus ) ){
		foreach( $menus as $menu ){
			$integration_code.= ubermenu_menu_integration_code( array( 'menu' => $menu->term_id ) , $config_id );
		}

		$menu_select.= '<select class="ubermenu-manual-code-menu-selection">';
		$menu_select.= '<option value="_default">Default</option>';
		foreach( $menus as $menu ){
			$menu_select.= '<option value="'.$menu->term_id.'">'.$menu->name.'</option>';
		}
		$menu_select.= '</select>';

		$menu_select.= '<p class="ubermenu-sub-desc ubermenu-desc-understated">To display a specific menu, select the menu above to generate that code</p>';
	}

	$locs = get_registered_nav_menus();

	if( is_array( $locs ) ){

		foreach( $locs as $loc_id => $loc_name ){
			$integration_code.= ubermenu_menu_integration_code( array( 'theme_location' => $loc_id ) , $config_id );
		}

		$loc_select.= '<select class="ubermenu-manual-code-menu-selection">';
		$loc_select.= '<option value="_default">None</option>';
		foreach( $locs as $loc_id => $loc_name ){
			$loc_select.= '<option value="'.$loc_id.'">'.$loc_name.'</option>';
		}
		$loc_select.= '</select>';

		$loc_select.= '<p class="ubermenu-sub-desc ubermenu-desc-understated">To display a specific theme locaton, select the theme location above to generate that code</p>';
	}

	$integration_code.= $menu_select . $loc_select;

	$integration_code.='</div>';

	return $integration_code;
}

add_filter( 'ubermenu_instance_settings' , 'ubermenu_pro_instance_settings' , 20 , 2 );
function ubermenu_pro_instance_settings( $settings , $config_id ){

	//$integration_code = ubermenu_integration_code_ui( $config_id );

	//Integration
	$settings[10] = array(
		'name'	=> 'header_integration',
		'label'	=> __( 'Integration' , 'ubermenu' ),
		'desc'	=> __( 'To integrate this menu, either (1) select the Theme Location(s) to automatically replace, or (2) use the provided integration code to insert wherever you like.' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'integration',
	);

	$settings[20] = array(
		'name'	=> 'auto_theme_location',
		'label'	=> __( 'Automatic Integration Theme Location' , 'ubermenu' ),
		'type'	=> 'multicheck',
		'desc'	=> __( 'Select the theme locations to activate automatically.  Works with most modularly coded themes.  The above theme locations are specific to your site.' , 'ubermenu' ) . 
					'<div class="ubermenu-alert">Please note that if your menu doesn\'t seem to be working properly after using Automatic Integration, the most common scenario is that you have <a href="http://sevenspark.com/docs/ubermenu-3/integration/residual-styling" target="_blank" title="Knowledgebase: Residual Styling">residual styling <i class="fa fa-book"></i></a> from your theme and would need to use <a href="http://sevenspark.com/docs/ubermenu-3/integration/manual" target="_blank" title="Knowledgebase: Manual Integration">Manual Integration <i class="fa fa-book"></i></a> instead.  Try the Direct Injection Testing setting below to see if your menu works better outside of your theme\'s menu.  If it does, the issue is likely residual styling and resolvable through manual integration.  If the issues occur in the Direct Injection menu as well, please see <a href="http://sevenspark.com/docs/ubermenu-3/integration/theme-interference" title="Knowledgebase: Other ways in which themes can interfere with the menu" target="_blank">Theme Interference <i class="fa fa-book"></i></a></div>',
		'options' => 'ubermenu_get_theme_location_ops',
		'default' => '',
		'group'	=> 'integration',
	);

	$settings[25] = array(
		'name'	=> 'direct_inject',
		'label'	=> __( 'Direct Injection Testing (Experimental)' , 'ubermenu' ),
		'type'	=> 'checkbox',
		'desc'	=> __( 'This setting is intended for testing - enable and compare this menu to your main menu.  It will add UberMenu to your site via the wp_footer() hook and fix it to the top of your site.  This allows you to test the menu without interference from the theme\'s menu (in the majority of cases).  It is a good way to test if you need to use Manual Integration.  You will need to assign a menu to the UberMenu [Direct Injection] theme location' , 'ubermenu' ),
		'default' => 'off',
		'group'	=> 'integration',
	);

	

	$settings[30] = array(
		'name'	=> 'header_manual_integration',
		'label'	=> __( 'Manual Integration' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'integration',
	);

	$settings[40] = array(
		'name'	=> 'php',
		'label'	=> __( 'Manual Integration Code' , 'ubermenu' ),
		'desc'	=> array( 'func' => 'ubermenu_integration_code_ui' , 'args' => $config_id ), //$integration_code,
		'type'	=> 'func_html',
		'group'	=> 'integration'
	);

	$settings[50] = array(
		'name'	=> 'nav_menu_id',
		'label'	=> __( 'Default Manual Integration Menu', 'ubermenu' ),
		'desc'	=> __( 'This is the default menu that will appear when you use the manual integration code.  It can be overridden by the <code>menu</code> parameter within the nav menu args array.  Please note that this setting will override any theme_location parameter passed to the ubermenu() function.', 'ubermenu' ),
		'type'	=> 'select',
		'default'	=> '_none',
		'options' => 'ubermenu_get_nav_menu_ops',
		'group'	=> 'integration',
	);




	/* Position & Layout */
			
	$settings[150] = array(
		'name'	=> 'header_position_menu_bar',
		'label'	=> __( 'Menu Bar' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'position',
	);
	
	$settings[160] = array(
		'name'		=> 'bar_align',
		'label'		=> __( 'Menu Bar Alignment' , 'ubermenu' ),
		'desc'		=> __( 'Alignment relative to the theme container.  If you choose "Center", you must set a Menu Bar Width below.' , 'ubermenu' ),
		'type'		=> 'radio',
		'options' 	=> array(
			'full'	=> 'Full Width',
			'left' 	=> __( 'Left', 'ubermenu' ),
			'right'	=> __( 'Right', 'ubermenu' ),
			'center'=> __( 'Center (requires Menu Bar Width)', 'ubermenu' ),
		),
		'default' 	=> 'full',
		'group'	=> 'position',
	);


	$settings[170] = array(
		'name'		=> 'bar_width',
		'label'		=> __( 'Menu Bar Width' , 'ubermenu' ),
		'desc'		=> __( 'Set an explicit width for the menu bar.  Required for centering.  Generally not needed.', 'ubermenu' ),
		'type'		=> 'text',
		'default' 	=> '',
		'group'	=> 'position',
		'custom_style' => 'bar_width',
	);

	$settings[172] = array(
		'name'		=> 'bar_margin_top',
		'label'		=> __( 'Menu Bar Margin Top' , 'ubermenu' ),
		'desc'		=> __( 'Useful for tweaking position', 'ubermenu' ),
		'type'		=> 'text',
		'default' 	=> '',
		'group'	=> 'position',
		'custom_style' => 'bar_margin_top',
		'customizer'=> true,
		'customizer_section' => 'menu_bar',
	);
	$settings[173] = array(
		'name'		=> 'bar_margin_bottom',
		'label'		=> __( 'Menu Bar Margin Bottom' , 'ubermenu' ),
		'desc'		=> __( 'Useful for spacing out elements', 'ubermenu' ),
		'type'		=> 'text',
		'default' 	=> '',
		'group'	=> 'position',
		'custom_style' => 'bar_margin_bottom',
		'customizer'=> true,
		'customizer_section' => 'menu_bar',
	);



	/* Menu Items Alignment */
	$settings[180] = array(
		'name'	=> 'header_position_menu_items',
		'label'	=> __( 'Menu Items' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'position',
	);

	$settings[190] = array(
		'name'		=> 'items_align',
		'label'		=> __( 'Horizontal Item Alignment' , 'ubermenu' ),
		'type'		=> 'radio',
		'options' 	=> array(			
			'left' 	=> __( 'Left', 'ubermenu' ),
			'center'=> __( 'Center', 'ubermenu' ),
			'right'	=> __( 'Right', 'ubermenu' ),
		),
		'default' 	=> 'left',
		'group'	=> 'position',
	);

	/* Won't do anything with floated items.
	$settings[200] = array(
		'name'		=> 'items_align_vertical',
		'label'		=> __( 'Vertical Item Alignment' , 'ubermenu' ),
		'desc'		=> __( 'Align the menu items to the top or bottom of the menu bar.  Makes no difference if all items are the same height.  Most useful for scenarios like top level stacks.', 'ubermenu' ),
		'type'		=> 'radio',
		'options' 	=> array(			
			'bottom'=> __( 'Bottom', 'ubermenu' ),
			'top' 	=> __( 'Top', 'ubermenu' ),
		),
		'default' 	=> 'bottom',
		'group'	=> 'position',
	);
	*/

	/* Inner Bar Position & Layout */
	
	$settings[210] = array(
		'name'	=> 'header_position_bar_inner',
		'label'	=> __( 'Inner Menu Bar' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'position',
	);
	
	$settings[220] = array(
		'name'		=> 'bar_inner_center',
		'label'		=> __( 'Center Inner Menu Bar' , 'ubermenu' ),
		'desc'		=> __( 'Requires an Inner Menu Bar Width below.' , 'ubermenu' ),
		'type'		=> 'checkbox',
		'default' 	=> 'off',
		'group'	=> 'position',
	);

	$settings[230] = array(
		'name'		=> 'bar_inner_width',
		'label'		=> __( 'Inner Menu Bar Width' , 'ubermenu' ),
		'desc'		=> __( 'Set an explicit width for the inner menu bar.  Generally not needed except for inner menu bar centering.  You may also wish to set the "Bound Submenu" option to Inner', 'ubermenu' ),
		'type'		=> 'text',
		'default' 	=> '',
		'group'	=> 'position',
		'custom_style' => 'bar_inner_width',
	);






	/* SUBMENUS  */

	$settings[290] = array(
		'name'	=> 'header_position_submenus',
		'label'	=> __( 'Submenus' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> array( 'position' , 'submenus' ),
	);


	$settings[300] = array(
		'name'		=> 'bound_submenus',
		'label'		=> __( 'Bound Submenu To' , 'ubermenu' ),
		'desc'		=> __( 'Set to "Unbounded" if you want a submenu wider than the menu bar.  The submenu will be bound by the next relatively positioned ancestor element in your theme.  Only relevant for horizontally oriented menus.', 'ubermenu' ),
		'type'		=> 'radio',
		'default' 	=> 'on',
		'options'	=> array(
			'on'	=> __( 'Menu Bar' , 'ubermenu' ),
			'inner'	=> __( 'Inner menu bar width' , 'ubermenu' ),
			'off'	=> __( 'Unbounded' , 'ubermenu' ),
		),
		'group'		=> array( 'position' , 'submenus' ),
		//'custom_style' => 'bound_submenus',
	);

	$settings[310] = array(
		'name'		=> 'submenu_inner_width',
		'label'		=> __( 'Submenu Row Width' , 'ubermenu' ),
		'desc'		=> __( 'If you are using Rows within your submenu, you can center the contents at this width.' , 'ubermenu' ),
		'type'		=> 'text',
		'default' 	=> '',
		'group'		=> array( 'position' , 'submenus' ),
		'custom_style' => 'submenu_inner_width',
	);

	$settings[315] = array(
		'name'		=> 'submenu_max_height',
		'label'		=> __( 'Mega Submenu Max Height' , 'ubermenu' ),
		'desc'		=> __( 'The maximum height of the submenu.  Submenus taller than this will get a vertical scrollbar.  Defaults to 600px.' , 'ubermenu' ),
		'type'		=> 'text',
		'default' 	=> '',
		'group'		=> array( 'position' , 'submenus' ),
		'custom_style' => 'submenu_max_height',
	);

	$settings[317] = array(
		'name'		=> 'dropdown_within_mega',
		'label'		=> __( 'Allow Dropdown within Mega Submenu' , 'ubermenu' ),
		'desc'		=> __( '<strong>Experimental</strong>.  Will allow dropdown submenus to appear within mega submenus.  May have side effects.  Not compatible with Slide submenu transition.' , 'ubermenu' ),
		'type'		=> 'checkbox',
		'default' 	=> 'off',
		'group'		=> array( 'position' , 'submenus' ),
		'custom_style' => 'dropdown_within_mega',
	);

	

	

	






	/** IMAGES **/

	$settings[320] = array(
		'name'	=> 'header_images',
		'label'	=> __( 'Images' , 'ubermenu' ),
		'type'	=> 'header',
		'desc'	=> __( 'Default image settings' , 'ubermenu' ),
		'group'	=> 'images',
	);

	$settings[330] = array(
		'name'		=> 'image_size',
		'label'		=> __( 'Image Size' , 'ubermenu' ),
		'type'		=> 'radio_advanced',
		'options' 	=> 'ubermenu_get_image_size_ops_inherit',
		'default' 	=> 'full',
		'desc'		=> __( 'Image sizes can be overridden on individual menu items' , 'ubermenu' ),
		'group'		=> 'images',
	);

	$settings[340] = array(
		'name'		=> 'image_width',
		'type'		=> 'text',
		'label'		=> __( 'Image Width' , 'ubermenu' ),
		'desc'		=> __( 'The width attribute value for menu item images in pixels.  Do not include units.  Leave blank to use actual dimensions.' , 'ubermenu' ),
		'group'		=> 'images',
		'custom_style' => 'image_width',
	);

	$settings[345] = array(
		'name'		=> 'image_height',
		'type'		=> 'text',
		'label'		=> __( 'Image Height' , 'ubermenu' ),
		'desc'		=> __( 'The height attribute value for menu item images in pixels.  Do not include units.  Leave blank to use actual dimensions.' , 'ubermenu' ),
		'group'		=> 'images',
	);

	$settings[350] = array(
		'name' 		=> 'image_set_dimensions',
		'label' 	=> __( 'Set Image Dimensions', 'ubermenu' ),
		'desc' 		=> __( 'Set the actual width and height attributes on an image if none are set manually.', 'ubermenu' ),
		'type' 		=> 'checkbox',
		'default' 	=> 'on',
		'group'		=> 'images',
	);

	$settings[360] = array(
		'name' 		=> 'image_title_attribute',
		'label' 	=> __( 'Use Image Title Attribute', 'ubermenu' ),
		'desc' 		=> __( '', 'ubermenu' ),
		'type' 		=> 'checkbox',
		'default' 	=> 'off',
		'group'		=> 'images',
	);

	$settings[365] = array(
		'name' 		=> 'disable_images_mobile',
		'label' 	=> __( 'Disable Images on Mobile', 'ubermenu' ),
		'desc' 		=> __( 'Detected via wp_is_mobile() - be aware if you set up caching, it would need to handle mobile device detection for this to work.', 'ubermenu' ),
		'type' 		=> 'checkbox',
		'default' 	=> 'off',
		'group'		=> 'images',
	);

	$settings[370] = array(
		'name' 		=> 'lazy_load_images',
		'label' 	=> __( 'Lazy Load Images (Experimental)', 'ubermenu' ),
		'desc' 		=> __( 'Only load the images when the submenu is opened.  More efficient bandwidth usage, but a slight delay for users depending on your server speed.', 'ubermenu' ),
		'type' 		=> 'checkbox',
		'default' 	=> 'off',
		'group'		=> 'images',
	);


	/* Background Images */
	$settings[380] = array(
		'name'	=> 'header_background_images',
		'label'	=> __( 'Submenu Background Images' , 'ubermenu' ),
		'type'	=> 'header',
		'desc'	=> __( '' , 'ubermenu' ),
		'group'	=> 'images',
	);


	$settings[390] = array(
		'name' 		=> 'submenu_background_image_reponsive_hide',
		'label' 	=> __( 'Hide Background Images on Mobile', 'ubermenu' ),
		'desc' 		=> __( '', 'ubermenu' ),
		'type' 		=> 'checkbox',
		'default' 	=> 'off',
		'group'		=> 'images',
	);










	/** STYLE CUSTOMIZATIONS **/

	$settings[480] = array(
		'name'	=> 'header_style_customizations',
		'label'	=> __( 'Style Customizations' , 'ubermenu' ),
		'type'	=> 'header',
		'desc'	=> __( 'Visit the Theme Customizer to edit most of these settings with a Live Preview.' , 'ubermenu' ) . ' <a class="button button-tertiary" href="'.admin_url('customize.php').'"><i class="fa fa-pencil"></i> Customizer</a>',
		'group'	=> 'style_customizations',
	);

	$settings[485] = array(
		'name'	=> 'force_styles',
		'label'	=> __( 'Force Styles' , 'ubermenu' ),
		'type'	=> 'checkbox',
		'default' => 'on',
		'desc'	=> __( 'Forces override of Skin styles.  For styles like border colors, also adds a border width and style, which may override skin settings.' , 'ubermenu' ),
		'group'	=> 'style_customizations',
	);
	
	$settings[490] = array(
		'name'	=> 'style_menu_bar_background',
		'label'	=> __( 'Menu Bar Background' , 'ubermenu' ),
		'type'	=> 'color_gradient',
		'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'menu_bar_background',
		'customizer'	=> true,
		'customizer_section' => 'menu_bar',
	);

	$settings[495] = array(
		'name'	=> 'style_menu_bar_transparent',
		'label'	=> __( 'Transparent menu bar' , 'ubermenu' ),
		//'type'	=> 'checkbox',
		'type'		=> 'radio',
		'default'	=> 'off',
		'options'	=> array(
			'on'	=> 'On',
			'off'	=> 'Off',
		),
		//'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'menu_bar_transparent',
		'customizer'	=> true,
		'customizer_section' => 'menu_bar',
	);

	$settings[500] = array(
		'name'	=> 'style_menu_bar_border',
		'label'	=> __( 'Menu Bar Border' , 'ubermenu' ),
		'type'	=> 'color',
		'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'menu_bar_border',
		'customizer'	=> true,
		'customizer_section' => 'menu_bar',
	);

	$settings[510] = array(
		'name'	=> 'style_menu_bar_radius',
		'label'	=> __( 'Menu Bar Border Radius' , 'ubermenu' ),
		'type'	=> 'text',
		'desc'	=> __( 'Pixel value (do not include px)' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'menu_bar_radius',
		'customizer'	=> true,
		'customizer_section' => 'menu_bar',
	);

	$settings[520] = array(
		'name'	=> 'style_top_level_font_size',
		'label'	=> __( 'Top Level Font Size' , 'ubermenu' ),
		'type'	=> 'text',
		'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_font_size',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);


	$settings[530] = array(
		'name'	=> 'style_top_level_text_transform',
		'label'	=> __( 'Top Level Text Transform' , 'ubermenu' ),
		'type'	=> 'select',
		'desc'	=> __( '' ),
		'options'	=> array(
			''			=> '&mdash;',
			'none'		=> 'None',
			'uppercase'	=> 'Uppercase',
			'capitalize'=> 'Capitalize',
		),
		'default'	=> '',
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_text_transform',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[540] = array(
		'name'	=> 'style_top_level_font_weight',
		'label'	=> __( 'Top Level Font Weight' , 'ubermenu' ),
		'type'	=> 'select',
		'desc'	=> __( '' ),
		'options'	=> array(
			''			=> '&mdash;',
			'normal'	=> 'Normal',
			'bold'		=> 'Bold',
		),
		'default'	=> '',
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_font_weight',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);



	$settings[550] = array(
		'name'	=> 'style_top_level_font_color',
		'label'	=> __( 'Top Level Font Color' , 'ubermenu' ),
		'type'	=> 'color',
		'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_font_color',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[560] = array(
		'name'	=> 'style_top_level_font_color_hover',
		'label'	=> __( 'Top Level Font Color [Activated]' , 'ubermenu' ),
		'type'	=> 'color',
		'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_font_color_hover',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[570] = array(
		'name'	=> 'style_top_level_font_color_current',
		'label'	=> __( 'Top Level Font Color [Current]' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_font_color_current',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[580] = array(
		'name'	=> 'style_top_level_font_color_highlight',
		'label'	=> __( 'Top Level Font Color [Highlight]' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_font_color_highlight',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);


	$settings[590] = array(
		'name'	=> 'style_top_level_background_hover',
		'label'	=> __( 'Top Level Background [Activated]' , 'ubermenu' ),
		'type'	=> 'color_gradient',
		//'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_background_hover',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);


	$settings[600] = array(
		'name'	=> 'style_top_level_background_current',
		'label'	=> __( 'Top Level Background [Current]' , 'ubermenu' ),
		'type'	=> 'color_gradient',
		//'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_background_current',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[610] = array(
		'name'	=> 'style_top_level_background_highlight',
		'label'	=> __( 'Top Level Background [Highlight]' , 'ubermenu' ),
		'type'	=> 'color_gradient',
		//'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_background_highlight',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[620] = array(
		'name'	=> 'style_top_level_item_divider_color',
		'label'	=> __( 'Top Level Item Divider Color' , 'ubermenu' ),
		'type'	=> 'color',
		'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_item_divider_color',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[630] = array(
		'name'	=> 'style_top_level_item_glow_opacity',
		'label'	=> __( 'Top Level Item Divider Glow Opacity' , 'ubermenu' ),
		'type'	=> 'text',
		'desc'	=> __( 'A number between 0 and 1 representing the opacity of the inner box shadow on the item\'s left edge.', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_item_glow_opacity',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[640] = array(
		'name'	=> 'style_top_level_item_glow_opacity_hover',
		'label'	=> __( 'Top Level Item Divider Glow Opacity [Active]' , 'ubermenu' ),
		'type'	=> 'text',
		'desc'	=> __( 'A number between 0 and 1 representing the opacity of the inner box shadow on the item\'s left edge.', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_item_glow_opacity_hover',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[650] = array(
		'name'	=> 'style_top_level_padding',
		'label'	=> __( 'Top Level Vertical Padding' , 'ubermenu' ),
		'type'	=> 'text',
		//'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_padding',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[660] = array(
		'name'	=> 'style_top_level_horiz_padding',
		'label'	=> __( 'Top Level Horizontal Padding' , 'ubermenu' ),
		'type'	=> 'text',
		//'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_horiz_padding',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[670] = array(
		'name'	=> 'style_extra_submenu_indicator_padding',
		'label'	=> __( 'Leave space for submenu indicator' , 'ubermenu' ),
		//'type'	=> 'checkbox',
		'type'		=> 'radio',
		'default'	=> 'on',
		'options'	=> array(
			'on'	=> 'On',
			'off'	=> 'Off',
		),
		//'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	$settings[680] = array(
		'name'	=> 'style_align_submenu_indicator',
		'label'	=> __( 'Align submenu indicator' , 'ubermenu' ),
		//'type'	=> 'checkbox',
		'type'		=> 'radio',
		'default'	=> 'edge',
		'options'	=> array( 'edge' => 'Edge' , 'text' => 'Text', ),
		//'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'customizer'	=> true,
		'customizer_section' => 'top_level_items',
	);

	

	$settings[690] = array(
		'name'	=> 'style_top_level_item_height',
		'label'	=> __( 'Top Level Menu Item Height' , 'ubermenu' ),
		'type'	=> 'text',
		'desc'	=> __( 'Generally best to leave blank and use the Top Level Vertical Padding setting to adjust menu bar height', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_item_height',
	);




	//Submenu
	
	$settings[700] = array(
		'name'	=> 'style_submenu_background_color',
		'label'	=> __( 'Submenu Background Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'submenu_background_color',
		'customizer'	=> true,
		'customizer_section' => 'submenu',
	);
	
	$settings[710] = array(
		'name'	=> 'style_submenu_border_color',
		'label'	=> __( 'Submenu Border Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'submenu_border_color',
		'customizer'	=> true,
		'customizer_section' => 'submenu',
	);

	$settings[712] = array(
		'name'	=> 'style_submenu_dropshadow_opacity',
		'label'	=> __( 'Submenu Dropshadow Opacity' , 'ubermenu' ),
		'type'	=> 'text',
		'desc'	=> __( 'A number between 0 and 1 that determines the opacity of the submenu drop shadow.  Set to 0 to remove.', 'ubermenu' ),
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'submenu_dropshadow_opacity',
		'customizer'	=> true,
		'customizer_section' => 'submenu',
	);

	$settings[720] = array(
		'name'	=> 'style_submenu_fallback_font_color',
		'label'	=> __( 'Submenu Fallback Font Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'submenu_fallback_font_color',
		'customizer'	=> true,
		'customizer_section' => 'submenu',
	);

	$settings[730] = array(
		'name'	=> 'style_submenu_minimum_column_width',
		'label'	=> __( 'Submenu Minimum Column Width' , 'ubermenu' ),
		'type'	=> 'text',
		'desc'	=> __( 'Use with caution', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'submenu_minimum_column_width',
	);

	$settings[740] = array(
		'name'	=> 'style_submenu_highlight_font_color',
		'label'	=> __( 'Submenu Highlight Font Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'submenu_highlight_font_color',
		'customizer'	=> true,
		'customizer_section' => 'submenu',
	);


	$settings[750] = array(
		'name'	=> 'style_submenu_item_padding',
		'label'	=> __( 'Submenu Item Padding' , 'ubermenu' ),
		'type'	=> 'text',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'submenu_item_padding',
		'customizer'	=> true,
		'customizer_section' => 'submenu',
	);




	//Headers

	$settings[760] = array(
		'name'	=> 'style_header_font_size',
		'label'	=> __( 'Column Header Font Size' , 'ubermenu' ),
		'type'	=> 'text',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'header_font_size',
		'customizer'	=> true,
		'customizer_section' => 'headers',
	);

	$settings[770] = array(
		'name'	=> 'style_header_font_color',
		'label'	=> __( 'Column Header Font Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'header_font_color',
		'customizer'	=> true,
		'customizer_section' => 'headers',
	);

	$settings[780] = array(
		'name'	=> 'style_header_font_color_hover',
		'label'	=> __( 'Column Header Font Color [Hover]' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'header_font_color_hover',
		'customizer'	=> true,
		'customizer_section' => 'headers',
	);

	$settings[790] = array(
		'name'	=> 'style_header_font_color_current',
		'label'	=> __( 'Column Header Font Color [Current]' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'header_font_color_current',
		'customizer'	=> true,
		'customizer_section' => 'headers',
	);

	$settings[800] = array(
		'name'	=> 'style_header_font_weight',
		'label'	=> __( 'Column Header Font Weight' , 'ubermenu' ),
		'type'	=> 'select',
		'desc'	=> __( '' ),
		'options'	=> array(
			''			=> '&mdash;',
			'normal'	=> 'Normal',
			'bold'		=> 'Bold',
		),
		'default'	=> '',
		'group'	=> 'style_customizations',
		'custom_style'	=> 'header_font_weight',
		'customizer'	=> true,
		'customizer_section' => 'headers',
	);


	$settings[810] = array(
		'name'	=> 'style_header_border_color',
		'label'	=> __( 'Column Header Border Color' , 'ubermenu' ),
		'type'	=> 'color',
		'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'header_border_color',
		'customizer'	=> true,
		'customizer_section' => 'headers',
	);

	$settings[820] = array(
		'name'	=> 'display_header_border_color',
		'label'	=> __( 'Display Header Border Color' , 'ubermenu' ),
		//'type'	=> 'checkbox',
		'type'	=> 'radio',
		'default'	=> 'on',
		'options'	=> array( 'on' => 'On' , 'off' => 'Off' ),
		'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
		'customizer'	=> true,
		'customizer_section' => 'headers',
	);








	//Normal Items
	$settings[830] = array(
		'name'	=> 'style_normal_font_color',
		'label'	=> __( 'Normal Items Font Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'normal_font_color',
		'customizer'	=> true,
		'customizer_section' => 'normal',
	);

	$settings[840] = array(
		'name'	=> 'style_normal_font_color_hover',
		'label'	=> __( 'Normal Items Font Color [Hover]' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'normal_font_color_hover',
		'customizer'	=> true,
		'customizer_section' => 'normal',
	);

	$settings[850] = array(
		'name'	=> 'style_normal_font_color_current',
		'label'	=> __( 'Normal Items Font Color [Current]' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'normal_font_color_current',
		'customizer'	=> true,
		'customizer_section' => 'normal',
	);

	$settings[860] = array(
		'name'	=> 'style_normal_font_size',
		'label'	=> __( 'Normal Items Font Size' , 'ubermenu' ),
		'type'	=> 'text',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'normal_font_size',
		'customizer'	=> true,
		'customizer_section' => 'normal',
	);

	$settings[870] = array(
		'name'	=> 'style_normal_background_hover',
		'label'	=> __( 'Normal Items Background Hover' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'normal_background_hover',
		'customizer'	=> true,
		'customizer_section' => 'normal',
	);

	//Flyout
	$settings[875] = array(
		'name'	=> 'style_flyout_vertical_padding',
		'label'	=> __( 'Flyout Items Vertical Padding' , 'ubermenu' ),
		'type'	=> 'text',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'flyout_vertical_padding',
		'customizer'	=> true,
		'customizer_section' => 'normal',
	);


	//Descriptions
	$settings[880] = array(
		'name'	=> 'style_description_font_size',
		'label'	=> __( 'Description Font Size' , 'ubermenu' ),
		'type'	=> 'text',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'description_font_size',
		'customizer'	=> true,
		'customizer_section' => 'descriptions',
	);

	$settings[890] = array(
		'name'	=> 'style_description_font_color',
		'label'	=> __( 'Description Font Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'description_font_color',
		'customizer'	=> true,
		'customizer_section' => 'descriptions',
	);

	$settings[900] = array(
		'name'	=> 'style_description_text_transform',
		'label'	=> __( 'Description Text Transform' , 'ubermenu' ),
		'type'	=> 'select',
		'options'	=> array(
			''		=> '&mdash;',
			'none'	=> 'None',
			'uppercase'	=> 'Uppercase',
			'lowercase'	=> 'Lowercase',
		),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'description_text_transform',
		'customizer'	=> true,
		'customizer_section' => 'descriptions',
	);



	//Arrows
	$settings[910] = array(
		'name'	=> 'style_top_level_arrow_color',
		'label'	=> __( 'Top Level Arrow Color' , 'ubermenu' ),
		'type'	=> 'color',
		'group'	=> 'style_customizations',
		'custom_style'	=> 'top_level_arrow_color',
		'customizer'	=> true,
		'customizer_section' => 'arrows',
	);

	$settings[920] = array(
		'name'	=> 'style_submenu_arrow_color',
		'label'	=> __( 'Submenu Arrow Color' , 'ubermenu' ),
		'type'	=> 'color',
		'group'	=> 'style_customizations',
		'custom_style'	=> 'submenu_arrow_color',
		'customizer'	=> true,
		'customizer_section' => 'arrows',
	);


	//HR
	$settings[930] = array(
		'name'	=> 'style_hr',
		'label'	=> __( 'Horizontal Rule Color' , 'ubermenu' ),
		'type'	=> 'color',
		'group'	=> 'style_customizations',
		'custom_style'	=> 'hr',
		'customizer'	=> true
	);



	//Toggle Bar

	$settings[940] = array(
		'name'	=> 'style_toggle_background',
		'label'	=> __( 'Responsive Toggle Background' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'toggle_background',
		'customizer'	=> true,
		'customizer_section' => 'toggle',
	);

	$settings[950] = array(
		'name'	=> 'style_toggle_color',
		'label'	=> __( 'Responsive Toggle Font Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'toggle_color',
		'customizer'	=> true,
		'customizer_section' => 'toggle',
	);

	$settings[960] = array(
		'name'	=> 'style_toggle_background_hover',
		'label'	=> __( 'Responsive Toggle Background [Hover]' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'toggle_background_hover',
		'customizer'	=> true,
		'customizer_section' => 'toggle',
	);

	$settings[970] = array(
		'name'	=> 'style_toggle_color_hover',
		'label'	=> __( 'Responsive Toggle Font Color [Hover]' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'toggle_color_hover',
		'customizer'	=> true,
		'customizer_section' => 'toggle',
	);


	//Search Bar
	$settings[990] = array(
		'name'	=> 'style_search_background',
		'label'	=> __( 'Search Bar Background' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'search_background',
		'customizer'	=> true,
		'customizer_section' => 'search',
	);

	$settings[990] = array(
		'name'	=> 'style_search_color',
		'label'	=> __( 'Search Bar Text Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'search_color',
		'customizer'	=> true,
		'customizer_section' => 'search',
	);

	$settings[1000] = array(
		'name'	=> 'style_search_placeholder_color',
		'label'	=> __( 'Search Bar Placeholder Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'search_placeholder_color',
		'customizer'	=> true,
		'customizer_section' => 'search',
	);

	$settings[1010] = array(
		'name'	=> 'style_search_icon_color',
		'label'	=> __( 'Search Bar Icon Color' , 'ubermenu' ),
		'type'	=> 'color',
		//'desc'	=> __( '', 'ubermenu' ),
		'group'	=> 'style_customizations',
		'custom_style'	=> 'search_icon_color',
		'customizer'	=> true,
		'customizer_section' => 'search',
	);








	$settings[1020] = array(
		'name'	=> 'header_style_customizations_row',
		'label'	=> __( 'Rows' , 'ubermenu' ),
		'type'	=> 'header',
		'desc'	=> __( '' ),
		'group'	=> 'style_customizations',
	);

	$settings[1025] = array(
		'name'		=> 'row_spacing',
		'label'		=> __( 'Row Spacing' , 'ubermenu' ),
		'desc'		=> __( 'The bottom margin to apply to rows.' , 'ubermenu' ),
		'type'		=> 'text',
		'default' 	=> '',
		'group'	=> 'style_customizations',
		'custom_style' => 'row_spacing',
	);






	/* Icons */
	$settings[1029] = array(
		'name'		=> 'icons_header',
		'label'		=> __( 'Icons' , 'ubermenu' ),
		'type'		=> 'header',
		'group'		=> 'icons',
	);


	$settings[1030] = array(
		'name'		=> 'icon_width',
		'label'		=> __( 'Icon Width' , 'ubermenu' ),
		'desc'		=> __( 'The width to allot for the icon.  Icon will be centered within this width.  1.3em by default.' , 'ubermenu' ),
		'type'		=> 'text',
		'group'		=> 'icons',
		'custom_style' => 'icon_width',
	);

	$settings[1031] = array(
		'name'		=> 'icon_display',
		'label'		=> __( 'Display Icons' , 'ubermenu' ),
		'desc'		=> __( 'If your text is too long to fit on one line along with the icon, set the icon display to Inline so that the icon and text will appear on the same line.' , 'ubermenu' ),
		'type'		=> 'radio',
		'default'	=> 'block',
		'options'	=> array(
			'block'		=> __( 'Block' , 'ubermenu' ),
			'inline'	=> __( 'Inline' , 'ubermenu' ),
		),
		'group'		=> 'icons',
	);

	//1032 is in icons extension



	/* Fonts */
	$settings[1055] = array(
		'name'		=> 'font_header',
		'label'		=> __( 'Font' , 'ubermenu' ),
		'desc'		=> __( 'Set a font for the menu.  Note that this may be overridden by the CSS of some themes.' , 'ubermenu' ),
		'type'		=> 'header',
		'group'		=> 'fonts'
	);

	$settings[1056] = array(
		'name'		=> 'google_font',
		'label'		=> __( 'Google Font' , 'ubermenu' ),
		'desc'		=> __( 'Using this property will (1) load the Google Font asset and (2) set this font as the menu font.' , 'ubermenu' ),
		'type'		=> 'select',
		'options'	=> ubermenu_get_font_ops(),
		'group'		=> 'fonts',
		'custom_style'	=> 'google_font',
		'customizer'=> true,
		'customizer_section' => 'fonts',

	);

	$settings[1057] = array(
		'name'		=> 'google_font_style',
		'label'		=> __( 'Google Font Style' , 'ubermenu' ),
		'desc'		=> __( 'Select the style to use for the menu.  Note that not all Google Fonts support all styles.' , 'ubermenu' ),
		'type'		=> 'select',
		'default'	=> '',
		'options'	=> ubermenu_get_font_style_ops(),
		'group'		=> 'fonts',
		'customizer'=> true,
		'customizer_section' => 'fonts',
	);

	$settings[1058] = array(
		'name'		=> 'custom_font_property',
		'label'		=> __( 'Custom Font Property' , 'ubermenu' ),
		'desc'		=> __( 'Set a custom <strong>font</strong> CSS property for the menu.  Example: <strong><code>bold 12px/24px Helvetica, Arial, sans-serif</code></strong>  Not necessary in conjuction with Google Font setting above.' , 'ubermenu' ),
		'type'		=> 'text',
		'group'		=> 'fonts',
		'custom_style'	=> 'custom_font',
		'customizer'=> true,
		'customizer_section' => 'fonts',
	);



	/* Misc */
	$settings[1065] = array(
		'name'		=> 'misc_header',
		'label'		=> __( 'Miscellaneous' , 'ubermenu' ),
		'type'		=> 'header',
		'group'		=> 'misc',
	);

	$settings[1070] = array(
		'name'		=> 'container_tag',
		'label'		=> __( 'Container Tag' , 'ubermenu' ),
		'desc'		=> __( 'The tag that wraps the entire menu.  Switch to div for non-HTML5 sites.', 'ubermenu' ),
		'type'		=> 'radio',
		'default'	=> 'nav',
		'options'	=> array(
			'nav'	=> '&lt;nav&gt;',
			'div'	=> '&lt;div&gt;',
		),
		'group'		=> 'misc',
	);

	$settings[1080] = array(
		'name'		=> 'allow_shortcodes_in_labels',
		'label'		=> __( 'Allow Shortcodes in Navigation Label & Description' , 'ubermenu' ),
		'desc'		=> __( 'Enable to process shortcodes in the menu item Navigation Label and Description settings.' , 'ubermenu' ),
		'type'		=> 'checkbox',
		'default'	=> 'off',
		'group'		=> 'misc',
	);

	$settings[1090] = array(
		'name'		=> 'submenu_settings_header',
		'label'		=> __( 'Submenu Settings' , 'ubermenu' ),
		'type'		=> 'header',
		'group'		=> 'misc',
	);


	$settings[1100] = array(
		'name'		=> 'display_submenu_indicators',
		'label'		=> __( 'Display Submenu Indicators' , 'ubermenu' ),
		'desc'		=> __( 'Display an arrow indicator when a drop submenu exists.' , 'ubermenu' ),
		'type'		=> 'checkbox',
		'default'	=> 'on',
		'group'		=> 'misc',
	);

	$settings[1110] = array(
		'name'		=> 'display_submenu_close_button',
		'label'		=> __( 'Display Submenu Close Button' , 'ubermenu' ),
		'desc'		=> __( 'Display an x to close the submenu (at all sizes).  Useful for click trigger.' , 'ubermenu' ),
		'type'		=> 'checkbox',
		'default'	=> 'off',
		'group'		=> 'misc',
	);



	/** ADVANCED **/
	$settings[1120] = array(
		'name'		=> 'header_advanced',
		'label'		=> __( 'Advanced' , 'ubermenu' ),
		'desc'		=> '<i class="fa fa-warning"></i> '.__( 'You should only adjust settings in this section if you are certain of what you are doing.', 'ubermenu' ),
		'type'		=> 'header',
		'group'		=> 'advanced',
	);
	$settings[1130] = array(
		'name'	=> 'theme_location_instance',
		'label'	=> __( 'Theme Location Instance' , 'ubermenu' ),
		'type'	=> 'text',
		'default'	=> 0,
		'desc'	=> __( 'Determines which instance of the theme location UberMenu should apply to.  0 means apply to all; set to 1 to apply to only the first, 2 to the second, etc.  Useful if your theme is reusing theme locations for mobile menu, sticky menu, etc.', 'ubermenu' ),
		'group'	=> 'advanced',
	);



	$settings[1150] = array(
		'name'		=> 'header_export',
		'type'		=> 'header',
		'label'		=> __( 'Import/Export' , 'ubermenu' ),
		'group'		=> 'export',
	);

	$settings[1151] = array(
		'name'		=> 'export',
		'label' 	=> __( 'Export' , 'ubermenu' ),
		'group'		=> 'export',
		'type'		=> 'func_html',		
		'desc'		=> array(
			'func'		=> 'ubermenu_export_data',
			'args'		=> array( 'config_id' => $config_id ),
		),

	);

	$settings[1152] = array(
		'name'		=> 'import',
		'label' 	=> __( 'Import' , 'ubermenu' ),
		'group'		=> 'export',
		'type'		=> 'func_html',		
		'desc'		=> array(
			'func'		=> 'ubermenu_import_data',
			'args'		=> array( 'config_id' => $config_id ),
		),

	);


	return $settings;
}


//Customizer Panels

add_action( 'ubermenu_customizer_register_subsections' , 'ubermenu_pro_customizer_sections' , 10 , 2 );
function ubermenu_pro_customizer_sections( $wp_customize , $panel_id ){
	$wp_customize->add_section( $panel_id.'_fonts', array(
		'title'		=> __( 'Fonts', 'ubermenu' ),
		'priority'	=> 100,
		'panel'		=> $panel_id,
	) );
}



function ubermenu_export_data( $data ){
	$config_id = $data['config_id'];
	$settings = ubermenu_get_instance_options( $config_id );
	unset( $settings['auto_theme_location'] );
	$html = '<textarea style="height:150px;">'.json_encode( $settings ).'</textarea>';
	$html.= '<br/><span class="description">Copy and paste this text into an Import box to copy these settings to a different Configuration.</span>';

	return $html;
}

function ubermenu_import_data( $data ){
	$config_id = $data['config_id'];
	// $name = 'ubermenu_'.$config_id.'[import_settings]';
	// $html = '<textarea id="'.$name.'" name="'.$name.'"></textarea>';
	// $html.= '<br/><span class="description">Paste export data above to import settings to this configuration</span>';
	// $html.= '<br/><div class="ubermenu-alert">WARNING: This will override all settings for this configuration!</div>';
	$html= '<a class="button button-primary" href="'.admin_url( 'themes.php?page=ubermenu-settings&amp;do=settings-import&amp;config_id='.$config_id ).'">Import Settings</a>';
	return $html;
}

/**
 * Add the Pro settings for General
 */
add_filter( 'ubermenu_settings_panel_fields' , 'ubermenu_settings_panel_fields_pro' , 20 );
function ubermenu_settings_panel_fields_pro( $all_fields = array() ){

	////////////////////////////////////
	///GENERAL
	////////////////////////////////////

	$fields = $all_fields[UBERMENU_PREFIX.'general'];

	/* ASSETS */

	$fields[50] = array(
		'name'	=> 'header_assets',
		'label'	=> __( 'Assets' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'assets',
	);

	$fields[60] = array(
		'name' 		=> 'load_custom_css',
		'label' 	=> __( 'Load Custom Stylesheet', 'ubermenu' ),
		'desc' 		=> __( 'Create a custom.css in the <code>custom/</code> directory.  You may wish to disable the skin preset.', 'ubermenu' ),
		'type' 		=> 'checkbox',
		'default' 	=> 'off',
		'group'		=> 'assets',
	);

	$fields[70] = array(
		'name' 		=> 'load_custom_js',
		'label' 	=> __( 'Load Custom Javascript', 'ubermenu' ),
		'desc' 		=> __( 'Create a custom.js in the <code>custom/</code> directory.', 'ubermenu' ),
		'type' 		=> 'checkbox',
		'default' 	=> 'off',
		'group'		=> 'assets',
	);

	$fields[80] = array(
		'name' 		=> 'load_ubermenu_css',
		'label' 	=> __( 'Load UberMenu Core Layout', 'ubermenu' ),
		'desc' 		=> __( 'Don\'t disable this unless you include it elsewhere', 'ubermenu' ),
		'type' 		=> 'checkbox',
		'default' 	=> 'on',
		'group'		=> 'assets',
	);

	$fields[90] = array(
		'name' 		=> 'load_fontawesome',
		'label' 	=> __( 'Load Font Awesome', 'ubermenu' ),
		'desc' 		=> __( 'If you are already loading Font Awesome 4 elsewhere in your setup, you can disable this.', 'ubermenu' ),
		'type' 		=> 'checkbox',
		'default' 	=> 'on',
		'group'		=> 'assets',
	);

	$fields[100] = array(
		'name' 		=> 'load_google_maps',
		'label' 	=> __( 'Load Google Maps API', 'ubermenu' ),
		'desc' 		=> __( 'If you are already loading the Google Maps API, or if you do not need Google Maps in your menu, you can disable this.', 'ubermenu' ),
		'type' 		=> 'checkbox',
		'default' 	=> 'on',
		'group'		=> 'assets',
	);

	/* Responsive & Mobile */
	$fields[110] = array(
		'name'	=> 'header_responsive',
		'label'	=> __( 'Responsive & Mobile' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'responsive',
	);
	$fields[120] = array(
		'name'	=> 'responsive_breakpoint',
		'label'	=> __( 'Responsive Breakpoint' , 'ubermenu' ),
		'type'	=> 'text',
		'desc'	=> __( 'The viewport width at which the menu will collapse to mobile menu.  959 by default', 'ubermenu' ),
		'group'	=> 'responsive',
	);

	$fields[125] = array(
		'name'		=> 'retractor_display_strategy',
		'label'		=> __( 'Determine Retractor Display By' , 'ubermenu' ),
		'desc'		=> __( 'Choose when the retractors should be shown.  Note that if you are using a caching plugin, you\'ll need to configure it properly to allow the Mobile option to work; Using Touch Detection may result in the Close button appearing on desktop browsers that support touch events.' , 'ubermenu' ),
		'type'		=> 'radio',
		'default'	=> 'responsive',
		'options'	=> array(
			'responsive'	=> __( 'Responsive - Display below responsive breakpoint' , 'ubermenu' ),
			'mobile'		=> __( 'Mobile - Use wp_is_mobile() mobile device detection' , 'ubermenu' ),
			'touch'			=> __( 'Touch Detection - Display when browser supports touch events' , 'ubermenu' ),
		),
		'group'		=> 'responsive',
	);

	/*
		array(
			'name'	=> 'responsive_breakpoint_secondary',
			'label'	=> __( 'Secondary Responsive Breakpoint' , 'ubermenu' ),
			'type'	=> 'text',
			'desc'	=> __( 'The point at which the menu will collapse to a single-column mobile menu. 480 by default', 'ubermenu' ),
			'group'	=> 'responsive',
		),
		*/


	/* Widgets */
	$fields[130] = array(
		'name'	=> 'header_widgets',
		'label'	=> __( 'Widgets' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'widgets',
	);


	$fields[140] = array(
		'name'	=> 'num_widget_areas',
		'label'	=> __( 'Number of Widget Areas' , 'ubermenu' ),
		'type'	=> 'text',
		'desc'	=> __( 'Enter the number of widget areas to auto-generate', 'ubermenu' ),
		'group'	=> 'widgets',
	);

	$fields[150] = array(
		'name'	=> 'widget_area_names',
		'label'	=> __( 'Widget Area Names' , 'ubermenu' ),
		'type'	=> 'textarea',
		'desc'	=> __( 'Comma delimited list of widget area names to assign' , 'ubermenu' ),
		'group'	=> 'widgets',
	);

	$fields[160] = array(
		'name'	=> 'allow_top_level_widgets',
		'label'	=> __( 'Allow Top Level Widgets' , 'ubermenu' ),
		'type'	=> 'checkbox',
		'default'	=> 'off',
		'desc'	=> __( 'Normally, widgets are only placed in a submenu.  Enable this to allow widgets to be placed in the top level of the menu.', 'ubermenu' ),
		'group'	=> 'widgets',
	);






	/* Advanced Menu Items */
	$fields[240] = array(
		'name'		=> 'adv_menu_items_header',
		'label'		=> __( 'Advanced Menu Items' , 'ubermenu' ),
		'type'		=> 'header',
		'group'		=> 'advanced_menu_items',
	);
	$fields[250] = array(
		'name'		=> 'autocomplete_max_term_results',
		'label'		=> __( 'Maximum Autocomplete Term Results' , 'ubermenu' ),
		'desc'		=> __( 'The maximum number of results that can appear in a Dynamic Posts or Dynamic Terms term autocomplete setting.  Limited for performance reasons for sites with huge numbers of terms.' , 'ubermenu' ),
		'type'		=> 'text',
		'default'	=> 100,
		'group'		=> 'advanced_menu_items',
	);

	$fields[260] = array(
		'name'		=> 'autocomplete_max_post_results',
		'label'		=> __( 'Maximum Autocomplete Post Results' , 'ubermenu' ),
		'desc'		=> __( 'The maximum number of results that can appear in a Dynamic Posts post autocomplete setting.  Limited for performance reasons for sites with huge numbers of posts.' , 'ubermenu' ),
		'type'		=> 'text',
		'default'	=> 100,
		'group'		=> 'advanced_menu_items',
	);



	/** Misc **/

	$fields[290] = array(
		'name'	=> 'ubermenu_toolbar',
		'label'	=> __( 'Display UberMenu Toolbar' , 'ubermenu' ),
		'type'	=> 'checkbox',
		'default'	=> 'on',
		'desc'	=> __( 'Display the UberMenu menu in the WordPress Toolbar.  Will ony be displayed to admins.', 'ubermenu' ),
		'group'	=> 'misc',
	);

	/** Theme Integration **/

	$fields[294] = array(
		'name'	=> 'ubermenu_theme_integration',
		'label' => __( 'Theme Integration / Interference' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> array( 'misc', 'theme_integration' ),
		'desc'	=> __( 'Settings to help tame misbehaving themes.  See also: ' , 'ubermenu' ) . '<a href="http://sevenspark.com/docs/ubermenu-3/integration/theme-interference" target="_blank">Theme Interference</a>',
	);

	$fields[295]	= array(
		'name'	=> 'force_filter',
		'label'	=> __( 'Force Filter UberMenu Settings' , 'ubermenu' ),
		'type'	=> 'checkbox',
		'default'	=> 'off',
		'desc'	=> __( 'Sometimes theme filters will override UberMenu\'s filters, preventing UberMenu from properly integrating.  Enable this to try to force UberMenu\'s filters', 'ubermenu' ),
		'group'	=> array( 'misc' , 'theme_integration' ),
	);

	$fields[297]	= array(
		'name'	=> 'disable_class_filtering',
		'label'	=> __( 'Disable Menu Item Class Filtering' , 'ubermenu' ),
		'type'	=> 'checkbox',
		'default'	=> 'off',
		'desc'	=> __( 'Some themes or plugins filter the core menu item classes, which can break things.  Enable this setting to attempt to remove them.  Please note this is a core hook, and disabling all filters on this hook could also disable functionality you want.', 'ubermenu' ),
		'group'	=> array( 'misc' , 'theme_integration' ),
	);






	/** ADVANCED **/
	$fields[300] = array(
		'name'	=> 'header_advanced',
		'label'	=> __( 'Advanced' , 'ubermenu' ),
		'desc'	=> '<i class="fa fa-warning"></i> '. __( 'You should only adjust settings in this section if you are certain of what you are doing.'  , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'advanced',
	);


	$fields[310] = array(
		'name'	=> 'strict_mode',
		'label'	=> __( 'Strict Mode' , 'ubermenu' ),
		'type'	=> 'checkbox',
		'default'	=> 'on',
		'desc'	=> __( 'Only auto-apply UberMenu to activated theme locations.  You should not deactivate this unless your theme is improperly using theme locations, as this will apply UberMenu to ALL menus.', 'ubermenu' ),
		'group'	=> 'advanced',
	);

	$fields[320] = array(
		'name'	=> 'ubermenu_theme_location',
		'label'	=> __( 'Register Easy Integration UberMenu Theme Location' , 'ubermenu' ),
		'type'	=> 'checkbox',
		'default'	=> 'off',
		'desc'	=> __( 'When enabled, creates a new theme location called "ubermenu" which you can use to insert into your theme.', 'ubermenu' ),
		'group'	=> 'advanced',
	);




	/** MAINTENANCE **/

	$fields[360] = array(
		'name'	=> 'reset_styles',
		'label'	=> __( 'Reset Style Customization Settings' , 'ubermenu' ),
		'desc'	=> '<a class="button button-primary" href="'.admin_url('themes.php?page=ubermenu-settings&do=reset-styles-check').'">'.__( 'Reset Style Customizations' , 'ubermenu' ).'</a><br/><p>'.__( 'Reset Style Customization Settings to the factory defaults.', 'ubermenu' ).'</p>',
		'type'	=> 'html',
		'group'	=> 'maintenance',
	);

	$fields[362] = array(
		'name'	=> 'manage_widget_areas',
		'label'	=> __( 'Widget Area Manager' , 'ubermenu' ),
		'desc'	=> '<a class="button button-primary" href="'.admin_url('themes.php?page=ubermenu-settings&do=widget-manager').'">'.__( 'Manage Widget Areas' , 'ubermenu' ).'</a><br/><p>'.__( 'Choose which Custom Widget Areas to delete.  Useful for orphaned widget areas from deleted menu items.', 'ubermenu' ).'</p>',
		'type'	=> 'html',
		'group'	=> array( 'maintenance' , 'widgets' ),
	);


	/** DIAGNOSTICS **/

	$fields[370] = array(
		'name'	=> 'header_diagnostics',
		'label'	=> __( 'Diagnostics' , 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'diagnostics',
	);
	$fields[371] = array(
		'name'	=> 'diagnostics',
		'label' => __( 'Enable Diagnostics (Experimental, Alpha status)' , 'ubermenu' ),
		'desc'	=> __( 'Enable front end diagnostics system.', 'ubermenu' ),
		'type'	=> 'checkbox', 
		'default' => 'on',
		'group'	=> 'diagnostics',
	);



	$all_fields[UBERMENU_PREFIX.'general'] = $fields;

	return $all_fields;
}

/**
 * Add the Pro Sub Sections for Instances
 */
add_filter( 'ubermenu_settings_panel_sections' , 'ubermenu_settings_panel_sections_pro' );
function ubermenu_settings_panel_sections_pro( $sections = array() ){

	$menus = ubermenu_get_menu_instances();

	//Add a Tab for each additional Instance
	foreach( $menus as $menu ){

		$sections[] = array(
			'id'	=> UBERMENU_PREFIX.$menu,
			'title' => '+'.$menu,
			'sub_sections'	=> ubermenu_get_settings_subsections( $menu ),
		);
	}

	return $sections;
}

/**
 * Add pro Sub Sections to General Panel
 */
add_filter( 'ubermenu_general_settings_sections' , 'ubermenu_general_settings_sections_pro' );
function ubermenu_general_settings_sections_pro( $section ){

	$section['sub_sections'] = array(

			// 'basic' => array(
			// 	'title' => __( 'Basic' , 'ubermenu' ),
			// ),
			'custom_css'=> array(
				'title'	=> __( 'Custom CSS' , 'ubermenu' ),
			),
			'assets'	=> array(
				'title'	=> __( 'Assets' , 'ubermenu' ),
			),
			'responsive'=> array(
				'title'	=> __( 'Responsive &amp; Mobile' , 'ubermenu' ),
			),			
			'widgets'=> array(
				'title'	=> __( 'Widgets' , 'ubermenu' ),
			),
			'script_config'=> array(
				'title'	=> __( 'Script Configuration' , 'ubermenu' ),
			),
			'advanced_menu_items' => array(
				'title'	=> __( 'Advanced Menu Items' , 'ubermenu' ),
			),
			'theme_integration'=> array(
				'title'	=> __( 'Theme Integration' , 'ubermenu' ),
			),
			'misc'=> array(
				'title'	=> __( 'Miscellaneous' , 'ubermenu' ),
			),
			'advanced'	=> array(
				'title'	=> __( 'Advanced' , 'ubermenu' ),
			),
			'maintenance'=> array(
				'title'	=> __( 'Maintenance', 'ubermenu' ),
			),
			'diagnostics'=> array(
				'title'	=> __( 'Diagnostics', 'ubermenu' ),
			),

			//$prefix.'main-'
		);

	return $section;
}

/**
 * Add Pro Settings Fields for each Instance
 */
add_filter( 'ubermenu_settings_panel_fields' , 'ubermenu_settings_panel_fields_instances' , 50 );
function ubermenu_settings_panel_fields_instances( $fields = array() ){

	//Add options for each additional Instance
	$menus = ubermenu_get_menu_instances();	
	foreach( $menus as $menu ){
		$fields[UBERMENU_PREFIX.$menu] = ubermenu_get_settings_fields_instance( $menu );		
	}

	return $fields;
}



add_action( 'init' , 'ubermenu_register_skins_pro' , 20 );
function ubermenu_register_skins_pro(){

	$main = UBERMENU_URL . 'pro/assets/css/skins/';

	ubermenu_register_skin( 'white' , 'White' , $main.'white.css' );
	ubermenu_register_skin( 'deepsky' , 'Deep Sky' , $main.'deepsky.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'berry' , 'Berry' , $main.'berry.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'aqua' , 'Sea Green' , $main.'aqua.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'fire' , 'Fire' , $main.'fire.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'eggplant' , 'Eggplant' , $main.'eggplant.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'robinsegg' , 'Robin\'s Egg' , $main.'robinsegg.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'tangerine' , 'Tangerine' , $main.'tangerine.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'nightsky' , 'Night Sky' , $main.'nightsky.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'charcoal' , 'Charcoal' , $main.'charcoal.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'shinyblack' , 'Shiny Black' , $main.'shinyblack.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'simple-green' , 'Simple Green' , $main.'simplegreen.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'earthy' , 'Earthy' , $main.'earthy.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'black-silver' , 'Black & Silver' , $main.'blacksilver.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'blue-silver' , 'Blue & Silver' , $main.'bluesilver.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'red-black' , 'Red & Black' , $main.'redblack.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'orange' , 'Burnt Orange' , $main.'orange.css' , 'ubermenu-has-border' );
	ubermenu_register_skin( 'clean-white' , 'Clean White' , $main.'cleanwhite.css' );
	ubermenu_register_skin( 'trans-black' , 'Transparent Black' , $main.'trans_black.css' );
	ubermenu_register_skin( 'trans-black-hov' , 'Transparent Black - Hover' , $main.'trans_black_hover.css' );
	ubermenu_register_skin( 'silver-tabs' , 'Silver Tabs' , $main.'silvertabs.css' );

	ubermenu_register_skin( 'tt-silver' , 'Two Tone Silver & Black (Deprecated)' , $main.'twotone_silver_black.css' );
	ubermenu_register_skin( 'tt-black' , 'Two Tone Black & Black (Deprecated)' , $main.'twotone_black_black.css' );
	ubermenu_register_skin( 'tt-red' , 'Two Tone Red & Black (Deprecated)' , $main.'twotone_red_black.css' );
	ubermenu_register_skin( 'tt-blue' , 'Two Tone Blue & Black (Deprecated)' , $main.'twotone_blue_black.css' );
	ubermenu_register_skin( 'tt-green' , 'Two Tone Green & Black (Deprecated)' , $main.'twotone_green_black.css' );
	ubermenu_register_skin( 'tt-purple' , 'Two Tone Purple & Black (Deprecated)' , $main.'twotone_purple_black.css' );
	ubermenu_register_skin( 'tt-orange' , 'Two Tone Orange & Black (Deprecated)' , $main.'twotone_orange_black.css' );
	ubermenu_register_skin( 'tt-silver-s' , 'Two Tone Silver & Silver (Deprecated)' , $main.'twotone_silver_silver.css' );

}





function ubermenu_kb_search(){

	ob_start();

	?>
	<div class="ubermenu-kb-search">
		<div class="search-topper"><a target="_blank" href="<?php echo UBERMENU_KB_URL; ?>"><i class="fa fa-search"></i> Search the Knowledgebase</a></div>
		<gcse:search></gcse:search>
	</div>
	<?php

	$html = ob_get_clean();

	return $html;

}

function ubermenu_support_forum_help(){
	$html = '<div class="ubermenu-help-wrap">';
	$html.= '<h3><i class="fa fa-life-ring"></i> '.__( 'Support Forum' , 'ubermenu' ).'</h3>';
	$html.= '<p>'.__( 'Didn\'t find the answer you needed in the Knowledgebase or Video Tutorials?  Visit the ' , 'ubermenu' ).
				'<a target="_blank" class="button" href="'.UBERMENU_SUPPORT_URL.'"><i class="fa fa-life-ring"></i> Support Forum</a></p>';
	$html.= '</div>';
	return $html;
}

function ubermenu_video_tutorials_help(){
	$html = '<div class="ubermenu-help-wrap">';
	$html.= '<h3><i class="fa fa-video-camera"></i> '.__( 'Video Tutorials' , 'ubermenu' ).'</h3>';
	$html.= '<a target="_blank" href="'.UBERMENU_VIDEOS_URL.'" class="ubermenu-help-video-tuts-link"><img src="'.UBERMENU_URL . 'admin/assets/images/video_tutorials.jpg"/><i class="fa fa-play"></i></a>';
	$html.= '</div>';

	return $html;
}


/** 
 * HELP
 */
add_filter( 'ubermenu_settings_panel_sections' , 'ubermenu_help_section' , 100 );
add_filter( 'ubermenu_settings_panel_fields' , 'ubermenu_help_fields' , 100 );
function ubermenu_help_section( $sections ){
	$prefix = UBERMENU_PREFIX;
	$sections[] = array(
		'id' => $prefix.'help',
		'title' => __( 'Help', 'ubermenu' ),
		'sub_sections'	=> array(
			'knowledgebase'	=> array(
				'title' 	=> __( 'Knowledgebase' , 'ubermenu' ),
			),
			'video_tutorials'	=> array(
				'title' 	=> __( 'Video Tutorials' , 'ubermenu' ),
			),
			'support'	=> array(
				'title' 	=> __( 'Support' , 'ubermenu' ),
			),
		),
	);

	return $sections;	
}
function ubermenu_help_fields( $fields = array() ){
	$section = UBERMENU_PREFIX.'help';
	$f = array();



	$f[] = array(
			'name'	=> 'search_knowledgebase',
			'label'	=> __( 'Search the Knowledgebase' , 'ubermenu' ),
			'desc'	=> ubermenu_kb_search(),
			'type'	=> 'html',
			'group'	=> 'knowledgebase',
		);

	$f[] = array(
			'name'	=> 'video_tutorials',
			'label' => __( 'Video Tutorials' , 'ubermenu' ),
			'desc'	=> ubermenu_video_tutorials_help(),
			'type'	=> 'html',
			'group'	=> 'video_tutorials',
		);

	$f[] = array(
			'name'	=> 'support_forum',
			'label' => __( 'Support Forum' , 'ubermenu' ),
			'desc'	=> ubermenu_support_forum_help(),
			'type'	=> 'html',
			'group'	=> 'support',
		);


	$fields[$section] = $f;
	return $fields;
}










/**
 * DELETE SETTINGS
 */

add_filter( 'ubermenu_settings_subsections' , 'ubermenu_settings_subsection_delete' , 1000 , 2 );
add_filter( 'ubermenu_settings_panel_fields' , 'ubermenu_settings_panel_fields_delete' , 1000 );

function ubermenu_settings_subsection_delete( $subsections , $config_id ){
	if( $config_id != 'main' ){
		$subsections['delete'] = array(
			'title'	=> __( 'Delete' ),
		);
	}
	return $subsections;
}

function ubermenu_settings_panel_fields_delete( $fields = array() ){

	$delete_header = array(
		'name'	=> 'header_delete',
		'label'	=> __( 'Delete', 'ubermenu' ),
		'type'	=> 'header',
		'group'	=> 'delete',
	);

	$menus = ubermenu_get_menu_instances( false );

	foreach( $menus as $menu ){

		//Requres $menu var
		$delete_instance = array(
			'name'	=> 'delete',
			'label'	=> __( 'Delete Configuration' , 'shiftnav' ),
			'desc'	=> '<a class="ubermenu_instance_button ubermenu_instance_button_delete" href="#" data-ubermenu-instance-id="'.$menu.'" data-ubermenu-nonce="'.wp_create_nonce( 'ubermenu-delete-instance' ).'">'.__( 'Permanently Delete Configuration' , 'ubermenu' ).'</a>',
			'type'	=> 'html',
			'group'	=> 'delete',
		);

		$fields[UBERMENU_PREFIX.$menu][2000] = $delete_header;
		$fields[UBERMENU_PREFIX.$menu][2000] = $delete_instance;
	}

	return $fields;
}







/**
 * WELCOME
 */

add_action( 'ubermenu_settings_after' , 'ubermenu_settings_welcome' );
function ubermenu_settings_welcome(){
	$show_welcome = get_option( UBERMENU_WELCOME_MSG , 1 );
	//echo $show_welcome ? 'true' : 'false';
	//$show_welcome = false;
	?>
	<div class="ubermenu-welcome <?php if( !$show_welcome ) echo 'ubermenu-welcome-hide'; ?>">
		<div class="ubermenu-welcome-inner">
			<h2>Welcome to UberMenu <?php echo UBERMENU_VERSION; ?></h2>
			<a class="ubermenu-welcome-dismiss" href="#" data-ubermenu-nonce="<?php echo wp_create_nonce( 'ubermenu-dismiss-welcome' ); ?>">
				&times;
				<span class="ubermenu-welcome-dismiss-alert">If you are unable to close this window, please check for javascript errors from other plugins or your theme</span>
			</a>
			
			<div class="ubermenu-welcome-buttons">
				<a target="_blank" class="button button-primary" href="<?php echo UBERMENU_KB_URL; ?>"><i class="fa fa-book"></i> Knowledgebase</a>
				<a target="_blank" class="button button-tertiary" href="<?php echo UBERMENU_VIDEOS_URL; ?>"><i class="fa fa-video-camera"></i> Video Tutorials</a>
				<a target="_blank" class="button button-secondary" href="<?php echo UBERMENU_SUPPORT_URL; ?>"><i class="fa fa-life-ring"></i> Support Forum</a>

				<span class="ubermenu-welcome-licenses">Your purchase entitles you to use UberMenu on one (1) site. <a target="_blank" class="ubermenu-license-info-link" href="http://goo.gl/lTLDhv"><i class="fa fa-question-circle"></i></a></span>
				<a target="_blank" class="button button-secondary button-new-license" href="http://wpmegamenu.com"><i class="fa fa-shopping-cart"></i> Need another license?</a>
			</div>

			<p>Links to the Knowledgebase, Video Tutorials, and Support Forum will appear in the upper right 
				of your Control Panel for easy access.  The QuickStart video below will help you get up and running quickly.</p>

			<?php if( $show_welcome ): ?>
				<iframe class="ubermenu-welcome-video" width="1000" height="563" src="<?php echo UBERMENU_QUICKSTART_URL; ?>" data-src="<?php echo UBERMENU_QUICKSTART_URL; ?>" frameborder="0" allowfullscreen></iframe>
			<?php else: ?>
				<iframe class="ubermenu-welcome-video" width="1000" height="563" data-src="<?php echo UBERMENU_QUICKSTART_URL; ?>" frameborder="0" allowfullscreen></iframe>
			<?php endif; ?>
		</div>
	</div>
	<?php
}


function ubermenu_dismiss_welcome_callback(){

	check_ajax_referer( 'ubermenu-dismiss-welcome' , 'ubermenu_nonce' );

	$response = array();

	update_option( UBERMENU_WELCOME_MSG , 0 );

	$response['welcome_msg'] = 0;

	echo json_encode( $response );

	die();
}
add_action( 'wp_ajax_ubermenu_dismiss_welcome', 'ubermenu_dismiss_welcome_callback' );





add_filter( 'plugin_action_links_'.UBERMENU_BASENAME , 'ubermenu_action_links' );
function ubermenu_action_links( $links ) {
	$links[] = '<a href="'. admin_url( 'themes.php?page=ubermenu-settings' ) .'">Control Panel</a>';
	$links[] = '<a href="'. admin_url( 'themes.php?page=ubermenu-settings#ubermenu_updates' ) .'">Updates</a>';
	$links[] = '<a target="_blank" href="'.UBERMENU_KB_URL.'">Knowledgebase</a>';
	return $links;
}