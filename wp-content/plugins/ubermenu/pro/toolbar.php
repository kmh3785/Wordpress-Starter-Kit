<?php

add_action( 'admin_bar_menu', 'ubermenu_add_toolbar_items', 100 );

function ubermenu_toolbar_icon( $icon_name ){
	if( is_admin() ) return '';
	return '<i class="fa fa-'.$icon_name.'"></i> ';
}

function ubermenu_add_toolbar_items( $admin_bar ){

	if( !current_user_can( 'manage_options' ) ) return;

	if( ubermenu_op( 'ubermenu_toolbar' , 'general' ) != 'on' ) return;

	$admin = is_admin();

	$admin_bar->add_node( array(
		'id'    => 'ubermenu',
		'title' => ubermenu_toolbar_icon( 'gears' ). 'UberMenu',
		'href'  => admin_url( 'themes.php?page=ubermenu-settings' ),
		'meta'  => array(
			'title' => __( 'UberMenu' , 'UberMenu' ),            
		),
	));

	$admin_bar->add_node( array(
		'id'    => 'ubermenu_customize',
		'parent' => 'ubermenu',
		'title' => ubermenu_toolbar_icon('eye') .__( 'Customize' , 'ubermenu' ),
		'href'  => admin_url( 'customize.php' ),
		'meta'  => array(
			'title' => __( 'Configure the UberMenu Settings' , 'ubermenu' ),
			'target' => '_blank',
			'class' => ''
		),
	));

	$admin_bar->add_node( array(
		'id'    => 'ubermenu_control_panel',
		'parent' => 'ubermenu',
		'title' => ubermenu_toolbar_icon( 'sliders' ).__( 'UberMenu Control Panel' , 'ubermenu' ),
		'href'  => admin_url( 'themes.php?page=ubermenu-settings' ),
		'meta'  => array(
			'title' => '<i class="fa fa-sliders"></i> '.__( 'Configure the UberMenu Settings' , 'ubermenu' ),
			'target' => '_blank',
			'class' => ''
		),
	));

	$admin_bar->add_node( array(
		'id'    	=> 'ubermenu_edit_menus',
		'parent' 	=> 'ubermenu',
		'title' 	=> ubermenu_toolbar_icon( 'pencil' ).__( 'Edit Menus', 'ubermenu' ),
		'href'  	=> admin_url( 'nav-menus.php' ),
		'meta'  	=> array(
			'title' => __('Add, remove, and configure menu items' , 'ubermenu' ),
			'target' => '_blank',
			'class' => ''
		),
	));


	$menus = wp_get_nav_menus( array('orderby' => 'name') );
	foreach( $menus as $menu ){
		$admin_bar->add_node( array(
			'id'    	=> 'ubermenu_edit_menus_'.$menu->slug,
			'parent' 	=> 'ubermenu_edit_menus',
			'title' 	=> /*__( 'Edit' , 'ubermenu' ) . ' ' .*/ $menu->name,
			'href'  	=> admin_url( 'nav-menus.php?action=edit&menu='.$menu->term_id ),
			'meta'  	=> array(
				'title' => __('Configure' , 'ubermenu' ) . ' '. $menu->name,
				'target' => '_blank',
				'class' => ''
			),
		));
	}


	$admin_bar->add_node( array(
		'id'		=> 'ubermenu_assign_menus',
		'parent'	=> 'ubermenu',
		'title'		=> ubermenu_toolbar_icon( 'bars' ).__( 'Assign Menus', 'ubermenu' ),
		'href'		=> admin_url( 'nav-menus.php?action=locations' ),
		'meta'		=> array(
			'title'	=> __( 'Theme Location Manager' , 'ubermenu' ),
			'target'=> '_blank',
			'class'	=> ''
		),
	));


	$admin_bar->add_node( array(
		'id'		=> 'ubermenu_knowledgebase',
		'parent'	=> 'ubermenu',
		'title'		=> ubermenu_toolbar_icon( 'book' ).__( 'Knowledgebase', 'ubermenu' ),
		'href'		=> UBERMENU_KB_URL,
		'meta'		=> array(
			'title'	=> __('UberMenu Knowledgebase / Support Guide' , 'ubermenu' ),
			'target'=> '_blank',
			'class'	=> ''
		),
	));

	$admin_bar->add_node( array(
		'id'		=> 'ubermenu_docs',
		'parent'	=> 'ubermenu_knowledgebase',
		'title'		=> ubermenu_toolbar_icon( 'book' ).__( 'Documentation', 'ubermenu' ),
		'href'		=> UBERMENU_KB_URL,
		'meta'		=> array(
			'title'	=> __('UberMenu Knowledgebase / Support Guide' , 'ubermenu' ),
			'target'=> '_blank',
			'class'	=> ''
		),
	));

	$admin_bar->add_node( array(
		'id'		=> 'ubermenu_video_tutorials',
		'parent'	=> 'ubermenu_knowledgebase',
		'title'		=> ubermenu_toolbar_icon( 'video-camera' ).__( 'Video Tutorials', 'ubermenu' ),
		'href'		=> UBERMENU_VIDEOS_URL,
		'meta'		=> array(
			'title'	=> __( 'UberMenu Video Tutorials' , 'ubermenu' ),
			'target'=> '_blank',
			'class'	=> ''
		),
	));

	$admin_bar->add_node( array(
		'id'    	=> 'ubermenu_troubleshooter',
		'parent'	=> 'ubermenu',
		'title' 	=> ubermenu_toolbar_icon( 'wrench' ).__( 'Troubleshooter', 'ubermenu' ),
		'href'  	=> UBERMENU_TROUBLESHOOTER_URL,
		'meta'  	=> array(
			'title' => __( 'UberMenu Troubleshooter' , 'ubermenu' ),
			'target' => '_blank',
			'class' => ''
		),
	));

	$admin_bar->add_node( array(
		'id'    	=> 'ubermenu_support',
		'parent'	=> 'ubermenu',
		'title' 	=> ubermenu_toolbar_icon( 'life-ring' ).__( 'Support / Help', 'ubermenu' ),
		'href'  	=> UBERMENU_SUPPORT_URL,
		'meta'  	=> array(
			'title' => __( 'UberMenu Support Center' , 'ubermenu' ),
			'target' => '_blank',
			'class' => ''
		),
	));

	



	if( !is_admin() ){

		//Diagnostics
		if( ubermenu_op( 'diagnostics' , 'general' ) == 'on' ){
			$admin_bar->add_node( array(
				'id'    	=> 'ubermenu_diagnostics',
				'parent'	=> 'ubermenu',
				'title' 	=> ubermenu_toolbar_icon( 'stethoscope' ).__( 'Diagnostics (Alpha)', 'ubermenu' ),
				'href'		=> '#',
				'meta'  	=> array(
					'title' => __( 'Load diagnostics script (experimental feature in Alpha development)' , 'ubermenu' ),
					'class' => 'ubermenu-diagnostics-loader-button'
				),
			));
		}


		//Loading Message
		$admin_bar->add_node( array(
			'id'    => 'ubermenu_loading',
			'title' => ubermenu_toolbar_icon( 'exclamation-triangle' ). ' UberMenu Loading...',
			'href'  => '#',
		));

		$loading_msg = __( 'If this message does not disappear, it means that UberMenu\'s javascript has not been able to load.  This most commonly indicates that you have a javascript error on this page, which will need to be resolved in order to allow UberMenu to run.' , 'ubermenu' );
		//$loading_msg.= ' <a target="_blank" href="http://goo.gl/oS6L6C">How to check for javascript errors.</a>';

		$admin_bar->add_node( array(
			'id'    => 'ubermenu_loading_msg',
			'parent'	=> 'ubermenu_loading',
			'title' => $loading_msg,
			'href'  => 'http://goo.gl/oS6L6C',
			'meta'	=> array(
				'target'	=> '_blank',
			),
		));
	}
}

