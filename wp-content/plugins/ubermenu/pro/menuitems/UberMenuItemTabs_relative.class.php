<?php


	//ID is the numeric ID of the [Tabs] Menu Item  ex "269"
		//This item does not actually produce any output apart from the 
		//HTML comments denoting start and end.  What it does is create two new items:
		//Tabs (Group) and Panels (Group)

	//Tabs ID is the Tabs Group child for this menu item ex "tabs-269"
	//Panels Group ID is the Panels child for this Tab Group ex "panels-group-269"
	
	//Each Tab has an ID ($tab->ID) based on the ID of the child item of [Tabs]
		//which comprises the individual tabs.  e.g. "242"
	//Each individual PANEL (which is a submenu of the Tab) has an ID based on the parent tab
		//e.g. "panel-242"




/*
 This item does not produce any output, it just rearranges this children
 so that their output is set up appropriately
 */
class UberMenuItemTabs extends UberMenuItem{

	protected $type = 'tabs';
	protected $auto_child = 'toggle';
	protected $alter_structure = true;

	function init(){

		if( $this->depth == 0 ){
			$this->walker->set_ignore( $this->ID );

			$this->branch_prefix = $this->walker->get_branch_prefix();
		}

	}


	function alter( &$children ){

		if( !isset( $children[$this->source_id] ) ) return;


		//Keep track of the original children
		//$my_children = $children[$this->ID];
		$my_children = $children[$this->source_id];	//in case parent is dynamic

		


		//The original children become the children of the new Tabs Group
		$tabs_id = 'tabs-'.$this->ID;
		$children[$tabs_id] = $my_children;

		
		//The children of the original Tabs become the children of the new Panels Group
		//TODO: test for tabs, or keep traversing if the child items are anything but normal menu items (skip columns, for example?)
		$panels_group_id = 'panels-group-'.$this->ID;
		$children[$panels_group_id] = array();

//echo 'created '.$panels_group_id.'<br/>';



		///////////////////////////////////////
		/// CREATE TABS FOR THE TOGGLE GROUP
		///////////////////////////////////////


		//For each child item (these are the Tabs/Toggles), create a new Content Panel
		//to hold its children.  Then copy over the children into that panel
		foreach( $my_children as $k => $tab ){
			//$tab is WP_Post object
			
			//Only move the children if this tab actually has children
			if( isset( $children[$tab->ID] ) ){
				$panel_id = 'panel-'.$tab->ID;
//echo '<em>Panel ID: '.$panel_id.'</em><br/>';

//echo 'add to ' . $panels_group_id;
				//Add Content Panel to Panels Group
				$children[$panels_group_id][] 
					= new UberMenu_dummy_item( 
							$panel_id , 
							'toggle_content_panel' , 
							'Toggle Content Panel' ,
							$tab->ID,
							array( 'menu_item_parent' => $panels_group_id , 'original_parent_id' => $this->ID ),
							array()

				);

				//Move the children of the menu item that became the tab
				//into the Content Panel for that tab
				
//echo 'add kids for ' .$tab->ID;
				
				
				//$panel_reference_index = $this->create_reference( $tab->ID , $children );
				//$children[$panel_id] = $children[$panel_reference_index];
//echo '[['.$tab->ID. ']]<br/>';
				$children[$panel_id] = $children[$tab->ID];

				//??
				//$reference_index = $this->create_reference( $tab->ID , $children );
				//$children[$panel_id] = $children[$reference_index];

				
/*				//This part is for Dynamic child content
				$_kids = array();

				foreach( $children[$tab->ID] as $kid ){
					$clone_id = $kid->ID . '-panel-' . $tab->ID;

					$clone = clone $kid;
					$clone->ID = $clone_id;
					//$clone->db_id = $clone_id;
					$clone->menu_item_parent = $panels_group_id;

					$_kids[] = $clone;

					//$this->walker->set_ignore( $kid->ID );
				}
				
				$children[$panel_id] = $_kids;
*/



				//These children are no longer children of this tab,
				//they are now only children of the panel
				

				//$this->walker->set_ignore_children( $tab->ID );
				//$this->create_reference( $tab->ID , $children );

				unset( $children[$tab->ID] );
				//$this->walker->set_ignore_children( $tab->ID );
				//$this->walker->feed_trash_collector( $tab->ID );


				//$this->walker->set_ignore( $tab->ID );
				//foreach( $children[$tab->ID] as $kid ){
				// 	$this->walker->set_ignore( $kid->ID );
				//}

//echo 'Create Dummy Panel ' . $panel_id . '<br/>';
//up( $children , 2 );
			}

			//Make a reference 
			$panels_group_reference_index = $this->create_reference( $panels_group_id , $children );

		}

		$tab_layout = $this->getSetting( 'tab_layout' );
		$tab_layout_class = 'ubermenu-tab-layout-'.$tab_layout;

		$tabs_group_layout = $this->getSetting( 'tabs_group_layout' );
		$panels_group_layout = $this->getSetting( 'panels_group_layout' );

		if( $tab_layout == 'right' || $tab_layout == 'left' ){
			if( $tabs_group_layout == 'auto' ) $tabs_group_layout = '1-4';
			if( $panels_group_layout == 'auto' ) $panels_group_layout = '3-4';
		}
		
		$tabs_column_class = 'ubermenu-column ubermenu-column-'.$tabs_group_layout; //' ubermenu-column-1-3';
		$panels_column_class = 'ubermenu-column ubermenu-column-'.$panels_group_layout; //ubermenu-column-2-3';
		
		
		$tabs_default_panel_class = $panels_default_panel_class = '';
		if( $this->getSetting( 'show_default_panel' ) == 'on' ){
			$tabs_default_panel_class = $panels_default_panel_class = 'ubermenu-toggle-show-default';
		}


		
		////////////////////////////////////////////////////
		/// Setup the Tabs and Panels wrappers for this item
		////////////////////////////////////////////////////
		
		$children[$this->ID] = array();

		//Tab Group
		$this->settings['menu_item_parent'] = $this->source_id; //$this->ID;
		$tab_group = new UberMenu_dummy_item( 
						$tabs_id , 
						'toggle_group' , 
						'Toggle Group' , 
						$this->ID ,
						$this->settings,
						array( $tab_layout_class , $tabs_column_class , $tabs_default_panel_class )
					);


		//Panels Group
		$this->settings['menu_item_parent'] = $this->source_id; //$this->ID;
		$panel_group = new UberMenu_dummy_item( 
						$panels_group_id , 
						'toggle_content_panels_group' , 
						'Toggle Content Panels Group' , 
						$this->ID ,
						$this->settings,
						array( $tab_layout_class , $panels_column_class , $panels_default_panel_class )
					);

		//Reorder to move before/after
		if( $tab_layout == 'right' || $tab_layout == 'bottom' ){
			$children[$this->ID][] = $panel_group;
			$children[$this->ID][] = $tab_group;
		}
		else{
			$children[$this->ID][] = $tab_group;
			$children[$this->ID][] = $panel_group;
		}



		//Find Tabs - only menu items, not custom UberMenu types
		//Find Tabs children (Content Panels)
		//Create new wrapper element
	}

	function get_start_el(){
		$this->setupAutoChild();
		//$this->settings['submenu_type_calc'] = 'toggles-group';
		return "<!-- begin Tabs: ".$this->item->title." $this->ID -->";
	}
	function get_end_el(){
		$this->resetAutoChild();
		return "<!-- end Tabs: ".$this->item->title." $this->ID -->";
	}


	/* No submenus for the Tabs Item */
	function get_submenu_wrap_start(){
		return '';
	}
	function get_submenu_wrap_end(){
		return '';
	}
}

/* An individual toggle item (regular menu item) */
class UberMenuItemToggle extends UberMenuItem{

	protected $type = 'toggle';
	protected $auto_child = 'toggle-content-panel';	

	function get_start_el(){

		$this->setupAutoChild();

		$this->settings['submenu_type_calc'] = 'toggles-content-panel';

		//Variable Initialization
		$item_output = '';
		$class_names = $value = '';

		//Setup Classes
		$this->add_class_item_defaults();
		$this->add_class_id();
		$this->prefix_classes();
		$this->add_class_item_display();
		$this->add_class_level();
		$this->add_class_layout_columns();
		//$this->add_class_submenu();	//Tabs won't have submenus, add manually
		$this->item_classes[] = 'ubermenu-has-submenu-drop';
//echo '>> ' . $this->item->title . ' : ' . implode(',' , $this->item_classes ) .'<br/>';

		$this->item_classes[] = 'ubermenu-toggle';

		$class_names = $this->filter_item_classes();

		//Setup ID
		$id = $this->filter_item_id();

		//Data target
		$data_target = ' data-ubermenu-toggle-target="#ubermenu-panel-'.$this->ID.'"';

		//Item LI
		$item_output .= '<li' . $id . $value . $class_names . $data_target .'>';

		//Anchor
		$atts = $this->anchor_atts(); //Attributes
		//$atts['data-ubermenu-toggle-target'] = '#ubermenu-panel-'.$this->ID;
		$item_output .= $this->get_anchor( $atts );

		//Custom Content
		$item_output .= $this->get_custom_content();

		return $item_output;

	}

	function get_end_el(){
		$this->resetAutoChild();
		$item_output = "</li>"; //<!-- end tabs group wrap ".$this->item->ID."-->\n";
		return $item_output;
	}

	function get_submenu_wrap_start(){

		$id = 'id="ubermenu-panel-'.$this->ID.'"';
		$class = 'class="ubermenu-submenu ubermenu-toggle-content-panel"';
		$out = "<ul $id $class>";
		return $out;
	}
}

/* A wrapper for a group of toggles - Dummy */
class UberMenuItemToggleGroup extends UberMenuItem{

	protected $type = 'toggle_group';

	function get_settings(){
		//up( $this->item );
		return $this->item->settings;
	}

	function get_start_el(){

		$this->settings['submenu_type_calc'] = 'toggles-group';
		$classes = array();
		$classes[] = 'ubermenu-tabs';
		$classes[] = 'ubermenu-toggles-group-wrap';
		$classes = array_merge( $classes , $this->item->classes );

		$class= 'class="'.implode( ' ' , $classes ).'"';

		$id = 'id="ubermenu-'.$this->ID.'"';

		return "<li $id $class>";
	}
	function get_submenu_wrap_start(){
		$trigger = $this->getSetting( 'tabs_trigger' );
		$data = 'data-ubermenu-toggle-trigger="'.$trigger.'"';
		
		$class = 'class="ubermenu-submenu ubermenu-toggles-group"';

		return "<ul $class $data>";
	}
	
}

/* An individual Panel that a Toggle is linked to */
class UberMenuItemToggleContentPanel extends UberMenuItem{

	//ref_id is the parent item (the tab)

	protected $type = 'toggle_content_panel';
	protected $auto_child = '';

	function get_term_id(){

		if( isset( $this->item->settings ) && isset( $this->item->settings['term_id'] ) ){
			return $this->item->settings['term_id'];
		}
		return false;
	}

	function get_start_el(){

//echo '<br/><br/>UberMenuItemToggleContentPanel  ** '. $this->ID;

		if( isset( $this->item->settings['ignore'] ) && $this->item->settings['ignore'] ){
			//echo 'ignore';
			//return;
		}

		$this->setupAutoChild();

		//Get the settings of the parent menu item for this panel
		$ref_settings = get_post_meta( $this->item->ref_id, UBERMENU_MENU_ITEM_META_KEY , true );
//up( $ref_settings );
		$this->settings['submenu_type_calc'] = 'stack';
		
		$col_def = isset( $ref_settings['submenu_column_default'] ) ? $ref_settings['submenu_column_default'] : 'auto';
		if( $col_def == 'auto' ) $col_def = '1-3';	//Default to 1/3 for 
		$this->settings['submenu_column_default'] = $col_def;

			//$this->walker->get_item_setting( $this->item->ref_id , 'submenu_column_default' );//$this->walker->parent_item()->getSetting( 'submenu_column_default' );

		$classes = array();
		$classes[] = 'ubermenu-submenu ubermenu-toggle-content-panel';
		$classes[] = 'ubermenu-submenu-id-'.$this->item->ref_id;

		//Background Image
		if( isset( $ref_settings['submenu_background_image'] ) && $ref_settings['submenu_background_image'] ){
			$classes[] = 'ubermenu-submenu-bkg-img';
		}



		$class = 'class="'.implode( ' ' , $classes ).'"';
		$id = 'id="ubermenu-'.$this->ID.'"';
		return "<ul $id $class >";
	}
	function get_end_el(){

		if( isset( $this->item->settings['ignore'] ) && $this->item->settings['ignore'] ){
			//return;
		}

		$this->resetAutoChild();

		$html = '';

		$ref_settings = get_post_meta( $this->item->ref_id, UBERMENU_MENU_ITEM_META_KEY , true );

		//Submenu Footer Content
		if( isset( $ref_settings['submenu_footer_content'] ) && $ref_settings['submenu_footer_content'] ){ 
			$html.= '<li class="ubermenu-submenu-footer ubermenu-submenu-footer-id-'.$this->item->ref_id.'">'.$ref_settings['submenu_footer_content'].'</li>';
		}

		$html.= '</ul>';

		return $html;
	}

	/* No submenus for the Content Panel */
	function get_submenu_wrap_start(){
		return '';
	}
	function get_submenu_wrap_end(){
		return '';
	}
}

/* A wrapper for a group of Toggle Panels - Dummy */
class UberMenuItemToggleContentPanelsGroup extends UberMenuItem{

	//protected $alter_structure = true;
	protected $type = 'toggle_content_panels_group';

/*
	function alter( &$children ){

		$panels_group_id = $this->ID;

		echo 'before '. $panels_group_id;
up( $children[$panels_group_id] );

			$panels_group_reference_index = $this->create_reference( $panels_group_id , $children );

			//Restore
			$children[$panels_group_id] = $children[$panels_group_reference_index];

echo 'after ' . $panels_group_id. ' :: from :: ' . $panels_group_reference_index ;
up( $children[$panels_group_id] );

	}
*/

	function get_settings(){
		//up( $this->item );
		return $this->item->settings;
	}

	function get_start_el(){

		$this->settings['submenu_type_calc'] = 'stack';

		$classes = array();
		$classes[] = 'ubermenu-tabs-panels';
		$classes[] = 'ubermenu-toggle-contents-group';
		$classes = array_merge( $classes , $this->item->classes );

		$class = 'class="'.implode( ' ' , $classes ).'"';

		$id = 'id="ubermenu-'.$this->ID.'"';

		return "<li $id $class>";
		//return "<!-- Begin Toggle Content Panels Group ".$this->ID."--><li $id $class>";
	}
	// function get_end_el(){
	// 	return "</li><!-- End Toggle Content Panels Group ".$this->ID."-->";
	// }

	/* No submenus for the content panels group */
	function get_submenu_wrap_start(){
		return '';
	}
	function get_submenu_wrap_end(){
		return '';
	}
}