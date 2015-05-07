<?php

require_once 'UberMenuItemMenuSegment.class.php';
require_once 'UberMenuItemTabs.class.php';
require_once 'UberMenuItemCustom.class.php';
require_once 'UberMenuItemWidgetArea.class.php';
require_once 'UberMenuItemDynamic.class.php';
require_once 'UberMenuItemDynamicTerms.class.php';
require_once 'UberMenuItemDynamicPosts.class.php';

add_filter( 'ubermenu_item_object_class' , 'ubermenu_pro_item_object_class' , 20 , 4 );

function ubermenu_pro_item_object_class( $class , $item , $id , $auto_child = ''  ){

	$ubermenu_custom_type = '';

	if( $item->custom_type ){
		$ubermenu_custom_type = $item->custom_type;
	}
	else{
		$ubermenu_custom_type = get_post_meta( $item->db_id , '_ubermenu_custom_item_type' , true );
	}

	switch( $ubermenu_custom_type ){

		case 'menu_segment':
			$class = 'UberMenuItemMenuSegment';
			break;

		case 'widget_area':
			$class = 'UberMenuItemWidgetArea';
			break;

		case 'custom_content':
			$class = 'UberMenuItemCustom';
			break;

		case 'tabs':
			$class = 'UberMenuItemTabs';
			break;

		case 'toggle_group':
			$class = 'UberMenuItemToggleGroup';
			break;

		case 'toggle_content_panel':
			$class = 'UberMenuItemToggleContentPanel';
			break;

		case 'toggle_content_panels_group':
			$class = 'UberMenuItemToggleContentPanelsGroup';
			break;

		case 'dynamic_terms':
			$class = 'UberMenuItemDynamicTerms';
			break;

		case 'dynamic_term_item':
			$class = 'UberMenuItemDynamicTerm';
			break;

		case 'dynamic_posts':
			$class = 'UberMenuItemDynamicPosts';
			break;

		case 'dynamic_post_item':
			$class = 'UberMenuItemDynamicPost';
			break;

		default:

			switch( $auto_child ){

				case 'tab':
					//$class = 'UberMenuItemToggle';
					$class = 'UberMenuItemTab';
					break;

				default:
					//don't touch, leave as is
			}
			break;
	}

	return $class;
}