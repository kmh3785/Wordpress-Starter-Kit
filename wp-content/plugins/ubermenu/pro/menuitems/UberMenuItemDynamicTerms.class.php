<?php

/** TERMS **/

//// The way this works on multiple levels is that when we expand the terms
//// for dynamic item #55, the new item will have an ID #{item_id}-term-{term_id}, e.g.
//// #55-term-21.  Each item is cloned with a new ID.  However, the clone retains 
//// a reference to the original ID (55) in the `object_id` property.  This ID is 
//// used to retrieve the proper children to clone for the newly expand dynamic terms


class UberMenuItemDynamicTerms extends UberMenuItemDynamic{
	protected $type = 'dynamic_terms';
	protected $alter_structure = true;
	protected $notice;
	//protected $term_map = array();
	//protected $term_map_index = 0;



	//Because this item isn't actually taking up and space, its children
	//are effectively one level up 
	function getVirtualDepth(){
		//return $this->walker->parent_item()->depth;
		return $this->depth-1;
	}


	function init(){
		$this->source_id = $this->item->db_id;
	}


	function alter( &$children ){

//echo '<h3>dynamic_terms ' . $this->ID. ' || '. $this->source_id . ' || ' .  $this->item->object_id . '</h3><br/>';

		//Dynamic Items are only good on submenus
		if( $this->depth > 0 ){

			//Set the reference index during the first pass
			$reference_index = $this->create_reference( $this->source_id , $children );

			
			
			//Get the Terms Settings			
			$term_args = array();
			$settings_map = array(
				//'taxonomy'	=> 'dt_taxonomy',
				'number'	=> 'dt_number',
				'parent'	=> 'dt_parent',
				'child_of'	=> 'dt_child_of',
				'orderby'	=> 'dt_orderby',
				'order'		=> 'dt_order',
				'hide_empty'=> 'dt_hide_empty',
				'hierarchical' => 'dt_hierarchical',
				'exclude'	=> 'dt_exclude',
				//'exclude_tree' => 'dt_exclude_tree',
			);
			//Setup terms args based on settings
			foreach( $settings_map as $t_arg => $s_key ){
				$v = $this->getSetting( $s_key ); //isset( $settings[$s_key] ) ? $settings[$s_key] : $defaults[$s_key];
				
				if( $v === 'on' ) $v = true;
				else if( $v === 'off' ) $v = false;

				$term_args[$t_arg] = $v;
			}

			//Inherit parent term ID
			if( $term_args['parent'] == -1 ){
				$term_args['parent'] = $this->walker->find_parent_term();
			}

			//Inherit ancestor term ID
			if( $term_args['child_of'] == -1 ){
				$term_args['child_of'] = $this->walker->find_parent_term();
			}
			
			//Get the taxonomies to search
			$taxonomies = $this->getSetting( 'dt_taxonomy' ); //isset( $settings['dt_taxonomy'] ) ? $settings['dt_taxonomy'] : $defaults['dt_taxonomy'];
			if( empty( $taxonomies ) ){
				$this->notice = '<strong>'._( 'Please select at least one taxonomy in the Dynamic Terms settings' , 'ubermenu' ).'</strong>';
			}


			//Allow filtering
			$term_args = apply_filters( 'ubermenu_dynamic_terms_args' , $term_args , $this->ID );

			///////////////////////////////
			//Retrieve the Terms
			///////////////////////////////

			$terms = get_terms( $taxonomies , $term_args );

			if( empty( $terms ) ){
				$this->notice = '<strong>'.$this->item->title.' ('.$this->ID.')</strong>: '.__( 'No results found' , 'ubermenu' );

				if( empty( $taxonomies ) ){
					$this->notice.= '<br/><strong>'._( 'Please select at least one taxonomy in the Dynamic Terms settings' , 'ubermenu' ).'</strong>';
				}
				else if( count( $taxonomies ) > 1 ){
					$this->notice.= '<br/><strong>'._( 'Make sure you only select the taxonomies that you want to display terms from' , 'ubermenu' ).'</strong>';
				}

				$this->notice.= '<br/><em>'.__( 'Taxonomies' , 'ubermenu' ).':</em>';
				$this->notice.= '<pre>';
				$this->notice.= print_r( $taxonomies , true );
				$this->notice.= '</pre>';
				$this->notice.= '<br/><em>'.__( 'Query Arguments' , 'ubermenu' ).':</em>';
				$this->notice.= '<pre>';
				$this->notice.= print_r( $term_args , true );
				$this->notice.= '</pre>';
			}
			
			$term_children = array();

			//Autocolumns setup
			$autocolumns = $this->getSetting( 'dt_autocolumns' );
			$term_count = count( $terms );
			$items_per_column;
			$column_id;
			$column_children = array();

			$column_map = array();
			
			if( $autocolumns && $autocolumns != 'disabled' ){
				//echo "$term_count / $autocolumns = " . ( $term_count / $autocolumns );

				$remainder = $term_count % $autocolumns;
				
				if( $remainder ){

					$items_per_column = floor( $term_count / $autocolumns );
					$items_per_column++;
					for( $_k = 0; $_k < $autocolumns; $_k++ ){
						$column_map[$_k] = $items_per_column;
						if( $_k+1 == $remainder ){
							$items_per_column--;
						}
					}
				}
				else{
					$items_per_column = ceil( $term_count / $autocolumns );
					for( $_k = 0; $_k < $autocolumns; $_k++ ){
						$column_map[$_k] = $items_per_column;
					}
				}
			} 

			//uberp( $column_map );
			
			

			//Loop through each term, get its info and create a Dummy Item to
			//stash in the children array.  The $_i keeps track of the index as
			//this is how child Dynamic Terms can map back
			$_i = 0;
			$_col = 0;
			foreach( $terms as $term ){

				//Find the URL for this term
				$url = get_term_link( $term );
				if( is_wp_error( $url ) ) $url = '#_term';

				$term_item_id = $this->ID . '-term-' . $term->term_id;
				
				$term_item = new UberMenu_dynamic_term_item( 
							$term_item_id, 
							$this->item,
							array(
								//'title'	=> '['.$term->name.']',
								'term_id' => $term->term_id,
								'taxonomy_slug' => $term->taxonomy,
								//'attr_title' => $term->name,
								'url'		=> $url,
							),
							array( 'dynamic-term' ),	//classes
							$this->get_settings()
						);


				if( $autocolumns > 0 ){

					if( $_i == 0 || ( isset( $column_map[$_col] ) && $column_map[$_col] == 0 ) ){

						//move on to the next column
						if( $column_map[$_col] == 0 ){
							$_col++;
							//Record items in previous column
							$children[$column_id] = $column_children;
						}

						$column_children = array();
						$column_id = $this->ID . '-col-' . $_col;
						$term_children[] = new UberMenu_dummy_item( 
							$column_id , 
							'column' , 
							'Auto Column' , 
							$this->ID,
							array( 'columns' => '1-'.$autocolumns ),
							array( 'ubermenu-autocolumn' )
						);
					}

					//Decrement the number remaining in this column
					$column_map[$_col]--;
					//uberp( $column_map );

					/*
					if( $_i % $items_per_column == 0 ){
						//echo 'column at '.$_i . '<br/>';
						$column_children = array();
						$column_id = $this->ID . '-col-' . $_i;
						$term_children[] = new UberMenu_dummy_item( 
							$column_id , 
							'column' , 
							'Auto Column' , 
							$this->ID,
							array( 'columns' => '1-'.$autocolumns ),
							array( 'ubermenu-autocolumn' )
						);
					}
					*/

					$column_children[] = $term_item;

					// if( ( $_i + 1 ) % $items_per_column == 0 ){
					// 	$children[$column_id] = $column_children;
					// }
				}
				else{
					$term_children[] = $term_item;
				}
				

				//Find the children of this item and remove them, but keep a 
				//reference.  They will later be appended to the generated terms instead

				$mykids = false;
				if( isset( $children[$reference_index] ) ){
				 	
				 	$mykids = $children[$reference_index];
				 	$children[$term_item_id] = $mykids;
					
				}

				$_i++;
			}

			//If we had an incomplete row (uneven division), tack on the remainder
			if( is_array( $column_children ) && !empty( $column_children ) ){
				$children[$column_id] = $column_children;
			}

			$children[$this->ID] = $term_children;

		}

	}

	function get_start_el(){
		//$this->setupAutoChild();
		//$this->settings['submenu_type_calc'] = 'dynamic-terms';

		//Setup the submenu type
		$submenu_type = 'mega';
		if( $this->depth > 0 ){
			$submenu_type = $this->walker->parent_item()->getSetting( 'submenu_type_calc' ); 
		}
		$this->settings['submenu_type_calc'] = $submenu_type; // 'dynamic-terms';


		$html = "<!-- begin Dynamic Terms: ".$this->item->title." $this->ID -->";

		if( $this->notice ){
			$html.= '<li class="ubermenu-item">'.ubermenu_admin_notice( $this->notice , false ).'</li>';
		}

		return $html;
	}
	function get_end_el(){
		//$this->resetAutoChild();
		return "<!-- end Dynamic Terms: ".$this->item->title." $this->ID -->";
	}
}







class UberMenuItemDynamicTerm extends UberMenuItemDefault{

	protected $type = 'dynamic_term';
	protected $is_tab = false;

	var $term;

	function get_term_id(){
		return $this->term->term_id;
	}

	function init(){

		//Set Source ID to the original Dynamic Terms Item
		//$this->source_id = $this->item->object_id;


		//Act like one level up, since we've been pushed down 1
		//by the Dynamic Terms Item
		$this->depth--;


		//Term
		$this->term = get_term( $this->item->term_id , $this->item->taxonomy_slug );

		//Branch Prefix 
		//$this->branch_prefix = 'term-'.$this->term->term_id.'_';

		//If this Dynamic Item is a child of a "Tabs" item, it becomes a toggle
		if( $this->walker->parent_item() ){ 

			if( $this->walker->parent_item()->getType() == 'tabs' ){

				$this->is_tab = true;

				//Ask the tab to set it up
				$this->walker->parent_item()->setup_tab( $this );


				$this->item_classes[] = 'ubermenu-tab';
				$this->item_classes[] = 'ubermenu-has-submenu-drop';


				$cols = $this->getSetting( 'columns' );
				// if( $this->depth > 0 && $cols == 'auto' ){
				//  	$cols = $this->walker->parent_item()->getSetting( 'submenu_column_default' );
				// }
				
				//Setup tab content panel columns 

				if( $this->depth > 0 && ( !isset( $this->settings['submenu_column_default'] ) || $this->settings['submenu_column_default'] == 'auto' ) ){
					$this->settings['submenu_column_default'] = $this->walker->parent_item()->getSetting( 'submenu_column_default' );
					//if( $this->term->name == 'China' ) uberp( $this->settings );
				}

				//Change specific for Left/Right Tab layouts, so that by default we're full width
				if( $cols == 'auto' ){
					$tab_layout = $this->walker->parent_item()->getSetting( 'tab_layout' );
					if( $tab_layout == 'right' || $tab_layout == 'left' ){
						//$cols = 'full';
						$this->settings['columns'] = 'full'; 
					}
				}
			}
		}


		//If item is current
		global $wp_query;
		$queried_object_id = (int) $wp_query->queried_object_id;
		$queried_object = $wp_query->get_queried_object();
		if( $this->item->term_id == $queried_object_id && ( $wp_query->is_category || $wp_query->is_tag || $wp_query->is_tax ) && $queried_object->taxonomy == $this->item->taxonomy_slug ){
			$this->item_classes[] = 'ubermenu-current-menu-item';	//hasn't been prefixed yet
		}
	}


	/**
	 * Get the Anchor and its contents
	 * @param  array $atts An array of attributes to add to the anchor
	 * @return string       The HTML for the anchor
	 */
	function get_anchor( $atts ){

		$term = $this->term;
		//up( $term );

		$a = '';
		$tag = 'a';

		//Image
		$image = $this->get_image();
		if( $image ) $atts['class'] .= ' ubermenu-target-with-image';


		//Icon
		$icon = $this->getSetting( 'icon' );
		if( $icon ){
			$atts['class'] .= ' ubermenu-target-with-icon';
			$icon = '<i class="ubermenu-icon '.$icon.'"></i>';
		}


		//Layout
		$layout = $this->getSetting( 'item_layout' );
		$atts['class'].= ' ubermenu-item-layout-'.$layout;

		//Content Align
		$content_align = $this->getSetting( 'content_alignment' );
		if( $content_align != 'default' ){
			$atts['class'].= ' ubermenu-content-align-'.$content_align;
		}

		
		if( $layout == 'default' ){

			if( $image ){
				$layout = 'image_left';
			}
			else if( $icon ){
				if( function_exists( 'ubermenu_icon_layout_default' ) ){
					$layout = ubermenu_icon_layout_default( $this );
				}
				else $layout = 'icon_left';
			}
			else{
				$layout = 'text_only';
			}

			$atts['class'].= ' ubermenu-item-layout-'.$layout;
		}

		$layout_order = ubermenu_get_item_layouts( $layout );
		if( !$layout_order ){
			ubermenu_admin_notice( __( 'Unknown layout order:', 'ubermenu' ).' '.$layout.' ['.$this->item->title.'] ('.$this->ID.')' );
		}		

		//No wrap
		if( $this->getSetting( 'no_wrap' ) == 'on' ){
			$atts['class'].= ' ubermenu-target-nowrap';
		}



		//Disabled Link (change tag)
		$disable_link = false;
		if( $this->getSetting( 'disable_link' ) == 'on' ){
			$tag = 'span';
			$disable_link = true;
			unset( $atts['href'] );
		}


		//Anchor Attributes
		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}




		//Title
		$title = '';
		if( $this->getSetting( 'disable_text' ) == 'off' ){
			$title .= '<span class="ubermenu-target-title ubermenu-target-text">';
			$title .= $term->name; //apply_filters( 'the_title', $term->name, $this->item->ID );

			if( $this->getSetting( 'dt_display_term_counts' ) == 'on' ){
				$title .= ' <span class="ubermenu-term-count">'. UBERMENU_TERM_COUNT_WRAP_START .$term->count. UBERMENU_TERM_COUNT_WRAP_END.'</span>';
			}
			//$title .= ' ['. $term->term_id .'] ['.$this->ID.']';
			$title .= '</span>';
		}
		else{
			//Flag items with disabled text
			$atts['class'].= ' ubermenu-item-notext';
		}


		//Description
		$description = '';
		//if( $this->getSetting( 'disable_text' ) == 'off' ){
		if( $this->item->description ){
			$description.= '<span class="ubermenu-target-description ubermenu-target-text">';
			$description.= $this->item->description;
			$description.= '</span>';
		}


		//Check if we still have something to print
		if( !$title && !$description && !$image && !$icon ){
			return '';
		}


		//Build the Layout
		
		//Get custom pieces 
		$custom_pieces = array();
		extract( apply_filters( 'ubermenu_custom_item_layout_data' , $custom_pieces , $layout , $this->ID , $term->term_id ) );

		//Gather all the pieces in the layout order into an array
		$layout_pieces = compact( $layout_order );

		//Output the anchor
		$a .= $this->args->before;
		$a .= '<'.$tag. $attributes .'>';
		$a .= $this->args->link_before;

		//Add pieces based on layout order		
		foreach( $layout_pieces as $piece ){
			$a.= $piece;
		}
		
		$a .= $this->args->link_after;
		$a .= '</'.$tag.'>';
		$a .= $this->args->after;

		return $a;
	}

	function setup_trigger(){

		$trigger = $this->getSetting( 'item_trigger' );

		if( $this->is_tab ){
			//If auto, get trigger from Tabs Group
			if( !$trigger || $trigger == 'auto' ){
				$trigger = $this->walker->grandparent_item()->getSetting( 'tabs_trigger' );
			}
		}

		if( $trigger && $trigger != 'auto' ){
			$this->item_atts['data-ubermenu-trigger'] = $trigger;
		}
	}

	function get_settings(){
		if( isset( $this->settings ) ) return $this->settings;
		
		$settings = $this->item->parent_settings;
		$settings = apply_filters( 'ubermenu_item_settings' , $settings , $this->ID );
		
		return $settings;
	}

}

