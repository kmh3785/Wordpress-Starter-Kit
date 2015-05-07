<?php

add_filter( 'ubermenu_custom_menu_item_types' , 'ubermenu_pro_custom_menu_item_types' );
function ubermenu_pro_custom_menu_item_types( $items ){

	$items['custom_content'] = array(
		'label'	=> __( 'Custom Content' , 'ubermenu' ), 
		'title' => '['.__( 'Custom' , 'ubermenu' ) .']',
		'desc'	=> __( 'A block of custom content, which can include HTML and shortcodes' , 'ubermenu' ),
		'panels'=> array( 'custom_content' , 'custom_content_layout' , 'responsive' ),
	);

	$items['tabs'] = array(
		'label'	=>	__( 'Tabs Block' , 'ubermenu' ),
		'title' =>	'['.__( 'Tabs' , 'ubermenu' ) . ']',
		'panels'=> array( 'tabs' , 'responsive' ),
		'desc'	=> __( 'A group of tabs' , 'ubermenu' ),
		//'url'	=>	'#ubermenu-tabs',
	);

	$items['dynamic_posts'] = array(
		'label'	=>	__( 'Dynamic Posts' , 'ubermenu' ),
		'title' =>	'['.__( 'Dynamic Posts' , 'ubermenu' ) . ']',
		'desc'	=>	__( 'Dynamically generated post content.', 'ubermenu' ),
		'panels'=> array( 'dynamic_posts' , 'general', 'image' , 'icon', 'layout', 'responsive',
		'submenu', 'customize' ),
	);

	$items['dynamic_terms'] = array(
		'label'	=>	__( 'Dynamic Terms' , 'ubermenu' ),
		'title' =>	'['.__( 'Dynamic Terms' , 'ubermenu' ) . ']',
		'desc'	=>	__( 'Dynamically generated taxonomy/category content.', 'ubermenu' ),
		'panels'=> array( 'dynamic_terms' , 'general' , 'icon' , 'layout' , 'responsive' ),
	);

	$items['menu_segment']	= array(
		'label'	=>	__( 'Menu Segment' , 'ubermenu' ),
		'title'	=>	'['.__( 'Menu Segment' , 'ubermenu' ).']',
		'desc'	=>	__( 'Insert a separate menu into the menu tree.' , 'ubermenu' ),
		'panels'=>	array( 'menu_segment' ), //, 'layout'
	);
	

	$items['widget_area']	= array(
		'label'	=> __( 'Widget Area' , 'ubermenu' ),
		'title'	=> '['.__( 'Widget Area' , 'ubermenu' ).']',
		'desc'	=> __( 'Insert a Widget Area into the menu' , 'ubermenu' ),
		'panels'=> array( 'widgets' , 'widget_layout' , 'responsive' ),
	);


	//Image
	
	//Search bar
	
	//Shopping cart?



	return $items;	
}