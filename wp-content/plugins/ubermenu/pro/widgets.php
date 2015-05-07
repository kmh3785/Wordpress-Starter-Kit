<?php

function ubermenu_item_save_create_auto_widget_area( $item_id , $setting , $val , &$saved_settings ){

	$menu_item_widget_areas = get_option( UBERMENU_MENU_ITEM_WIDGET_AREAS , array() );

	//Widget Area ID
	//$widget_area_id = 'umitem_'.$item_id;
	$widget_area_id = $item_id;

	//If Widget Area Name is set, set it
	if( $val ){
		$menu_item_widget_areas[$widget_area_id] = $val;
	}
	//Remove if Widget Area name is blank
	else{
		unset( $menu_item_widget_areas[$widget_area_id] );
	}

	update_option( UBERMENU_MENU_ITEM_WIDGET_AREAS , $menu_item_widget_areas );

}


add_action( 'init' , 'ubermenu_register_menu_item_auto_widget_areas' , 500 );
function ubermenu_register_menu_item_auto_widget_areas(){
	$menu_item_widget_areas = get_option( UBERMENU_MENU_ITEM_WIDGET_AREAS , array() );

	foreach( $menu_item_widget_areas as $id => $name ){
		register_sidebar( array(
			'name'			=> '[UberMenu] '.$name,
			'id'			=> 'umitem_'.$id,
			'description'	=> __( 'UberMenu Custom Widget Area for Menu Item ', 'ubermenu' ).$id, // . '. <a href="'.admin_url('themes.php?page=ubermenu-settings&do=widget-manager').'">Manage</a>',
			'before_title'	=> '<h3 class="ubermenu-widgettitle ubermenu-target">',
			'after_title'	=> '</h3>',
			'before_widget'	=> '<li id="%1$s" class="widget %2$s ubermenu-widget ubermenu-column ubermenu-item-header">',
			'after_widget'	=> '</li>',
			//'class'			=> 'ubermenu-widget',
		));
	}

	$widget_areas = ubermenu_get_widget_areas();

	foreach( $widget_areas as $id => $name ){

		//$name = isset( $names[$k] ) ? trim( $names[$k] ) : 'UberMenu Widget Area ' . $k;

		register_sidebar( array(
			'name'			=> '[UberMenu] '.$name,
			'id'			=> $id,
			'description'	=> __( 'You can assign this widget area to a menu item in Appearance > Menus', 'ubermenu' ),
			'before_title'	=> '<h3 class="ubermenu-widgettitle ubermenu-target">',
			'after_title'	=> '</h3>',
			'before_widget'	=> '<li id="%1$s" class="widget %2$s ubermenu-widget ubermenu-column ubermenu-item-header">',
			'after_widget'	=> '</li>',
			//'class'			=> 'ubermenu-widget',
		));
	}

	
}

function ubermenu_get_widget_areas(){

	$widget_areas = array();

	$num_widget_areas = ubermenu_op( 'num_widget_areas' , 'general' , 0 );
	$widget_area_names = ubermenu_op( 'widget_area_names' , 'general' , '' );

	$names = explode( ',' , $widget_area_names );

	if( $num_widget_areas ){
		for( $k = 0; $k < $num_widget_areas; $k++ ){

			$id = 'ubermenu-sidebar-'.($k+1);
			//echo $id;
			$name = ( isset( $names[$k] ) && trim( $names[$k] ) ) ? trim( $names[$k] ) : 'UberMenu Widget Area ' . ($k+1);
			
			$widget_areas[$id] = $name;
		}
	}

	return $widget_areas;
}


function ubermenu_get_widget_area_ops(){

	$widget_areas = array();
	$widget_areas[''] = __( 'None' , 'ubermenu' );

	$widget_areas = array_merge( $widget_areas , ubermenu_get_widget_areas() );

	return $widget_areas;
}