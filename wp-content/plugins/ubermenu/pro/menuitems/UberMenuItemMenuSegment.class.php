<?php

/*
 This item does not produce any output, it just rearranges this children
 so that their output is set up appropriately
 */
class UberMenuItemMenuSegment extends UberMenuItem{

	protected $type = 'menu_segment';
	//protected $auto_child = 'toggle';
	//protected $alter_structure = true;

	function getSetting( $key ){

		//Keys that should be grabbed from the grandparent item instead
		$kickup = array( 'submenu_column_default' );

		$val = '';
		if( in_array( $key , $kickup ) && $this->walker->grandparent_item() ){	//$this->depth > 1 && 
			$val = $this->walker->grandparent_item()->getSetting($key);
		}
		else $val = isset( $this->settings[$key] ) ? $this->settings[$key] : $this->walker->setting_defaults[$key];

		return $val;
	}

	function get_start_el(){
		//up( $this->settings );
		$menu_segment = $this->getSetting( 'menu_segment' );

		$html = "<!-- begin Segment: $menu_segment -->";


		//prevent infinite looping
		if( isset( $this->args->menu ) && $this->args->menu ){
			if( $this->args->menu == $menu_segment ){
				$html.= '<!-- Prevented infinite loop with segment nesting -->';
				return $html;
			}
		}



		if( $menu_segment == '_none' || !$menu_segment ){
			$html.= '<li>'.ubermenu_admin_notice( 'Please set a segment for <strong>'.$this->item->title .' ('.$this->ID.')</strong>', false ).'</li>';
			return $html.='<!-- no menu set -->';
		}

		$menu_object = wp_get_nav_menu_object( $menu_segment );
		if( !$menu_object ){
			$html.= '<li>'.ubermenu_admin_notice( 'No menu with ID '.$menu_segment.' for menu item: <strong>'.$this->item->title .' ('.$this->ID.')</strong>', false ).'</li>';
			return $html.'<!-- no menu with ID "'.$menu_segment.'" -->';
		}


		//Submenus of this item should defer to parent
		if( $this->depth > 0 ){
			//If this is top level, we don't need to set
			$this->settings['submenu_type_calc'] = $this->walker->parent_item()->getSetting('submenu_type_calc');
		}

		//Set Depth offset for segment
		$current_depth_offset = $this->walker->get_offset_depth();
		$this->walker->set_offset_depth( $this->depth );

		$html.= wp_nav_menu( array( 
			'menu' 			=> $menu_segment , 
			'echo' 			=> false ,
			'container' 	=> false,
			'items_wrap'	=> '%3$s',
			'walker'		=> $this->walker, 
			'uber_instance'	=> $this->args->uber_instance,
			'uber_segment'	=> $this->ID,
			// new UberMenuWalker( $this->depth ),
		) );

		//Reset depth offset
		$this->walker->set_offset_depth( $current_depth_offset );

		return $html;
	}
	function get_end_el(){
		//$this->resetAutoChild();
		$menu_segment = $this->getSetting( 'menu_segment' );
		return "<!-- end Segment: $menu_segment -->";
	}


	/* No submenus for the Segment Item */
	function get_submenu_wrap_start(){
		return '';
	}
	function get_submenu_wrap_end(){
		return '';
	}
}