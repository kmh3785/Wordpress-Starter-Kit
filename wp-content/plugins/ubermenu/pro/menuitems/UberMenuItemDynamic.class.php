<?php

class UberMenuItemDynamic extends UberMenuItem{
	protected $type = 'dynamic';

	function get_start_el(){}

	/* No submenus for the Dynamic Items var var ??]] */
	function get_submenu_wrap_start(){
		return '';
	}
	function get_submenu_wrap_end(){
		return '';
	}


	//Get the setting, but kick certain settings up to the parent
	//(Could also do this by setting val in child instead perhaps?)
	function getSetting( $key ){

		$val = '';

		//Keys that should be grabbed from the grandparent item instead, in the 
		//event that we are dealing with a tab content panel, so that the submenu columns
		//default can be inherited
		$kickup = array( 'submenu_column_default' );
		
		if( in_array( $key , $kickup ) ){

			//If the grandparent item is a tab
			$gp = $this->walker->grandparent_item();
			if( $gp && $gp->is_tab() ){
				$val = $this->walker->find_inherited_setting( $key , 'auto' , 1 );
			}
		}

		//If this wasn't the submenu column default, or grandparent wasn't a tab, just
		//do the usual
		if( !$val ){
			$val = isset( $this->settings[$key] ) ? $this->settings[$key] : $this->walker->setting_defaults[$key];
		}

		return $val;
	}

}



class UberMenu_dynamic_item{

	var $ID;	

	//Inherited Item Settings
	var $post_title;	
	var $post_name;	
	var $post_parent;
	var $guid;
	var $menu_order;
	var $post_type;
	var $db_id;
	var $menu_item_parent;
	var $object_id;
	var $object;
	var $type;
	var $type_label;
	var $title;
	var $url;
	var $target;
	var $attr_title;
	var $description;
	var $classes;
	var $xfn;
	
	var $current;
	var $current_item_ancestor;
	var $current_item_parent;


	//Ignored item settings
	// var $post_author;
	// var $post_date;
	// var $post_date_gmt;
	// var $post_content;
	// var $post_excerpt;
	// var $post_status;
	// var $comment_status;
	// var $ping_status;
	// var $post_password;
	// var $to_ping;
	// var $pinged;
	// var $post_modified;
	// var $post_modified_gmt;
	// var $post_content_filtered;
	// var $post_mime_type;
	// var $comment_count;
	// var $filter;


	//Custom Settings
	var $custom_type; // = 'dynamic_term_item';
	

	var $parent_settings;
	var $ref_id;
	//var $type_label = 'Dynamic';
	//var $classes;

	function __construct( $id , &$item , $args , $extra_classes = array(), $parent_settings = array() ){
		$this->ID = $id;
		//$this->db_id = $id;
		//$this->

		$props = array(
			'post_title',	
			'post_name',	
			'post_parent',
			'guid',
			'menu_order',
			'post_type',
			//'db_id',			//causes major issue with infinite loop
			'menu_item_parent',
			'object_id',
			'object',
			'type',
			'type_label',
			'title',
			'url',
			'target',
			'attr_title',
			'description',
			'classes',
			'xfn',
			'current',
			'current_item_ancestor',
			'current_item_parent',
			
		);
		foreach( $props as $prop ){
			//echo '<br/><br/>'.$prop;
			$this->$prop = $item->$prop;
		}
		//$this->ref_id = $item->db_id;
		$this->db_id = $id;
		$this->ref_id = $item->db_id;

		foreach( $args as $prop => $val ){
			//echo $prop .' :: ' .$val .' <br/>';
			$this->$prop = $val;
		}

		$this->parent_settings = $parent_settings;

		//$this->settings = $args;

		//$this->classes[] = 'dynamic-term';
		$this->classes = array_merge( $this->classes , $extra_classes );

	}
}
class UberMenu_dynamic_term_item extends UberMenu_dynamic_item{
	var $term_id;
	var $taxonomy_slug;
	var $custom_type = 'dynamic_term_item';
}

class UberMenu_dynamic_post_item extends UberMenu_dynamic_item{
	var $dynamic_post_id;
	var $custom_type = 'dynamic_post_item';
}

