<?php

class UberMenuWalker extends UberMenuWalkerCore{

	function start_el( &$output, $item, $depth = 0, $args = array(), $current_object_id = 0 ){

		global $uberMenu;
		$settings = $uberMenu->getSettings();
		
		//Test override settings
		$override = $this->getUberOption( $item->ID, 'shortcode' );
		$overrideOn = $settings->op( 'wpmega-shortcodes' ) && !empty( $override ) ? true : false;
		
		//Test sidebar settings
		$sidebar = $this->getUberOption( $item->ID, 'sidebars' );
		$sidebarOn = ( $settings->op( 'wpmega-top-level-widgets' ) || $depth > 0 ) && $settings->op( 'wpmega-sidebars' ) && !empty( $sidebar ) ? true : false;
		
		//For --Divides-- with no Content
		if( ( $item->title == '' || $item->title == UBERMENU_SKIP ) && !$overrideOn  && !$sidebarOn ){ 
			if( $item->title == UBERMENU_SKIP ) $output.= '<li id="menu-item-'. $item->ID.'" class="wpmega-divider-container">'.UBERMENU_DIVIDER; //.'</li>'; 
			return; 
		}	//perhaps the filter should be called here
				  
		global $wp_query;
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
 
		//Handle class names depending on menu item settings
		$class_names = $value = '';
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		
		//The Basics
		if( $depth == 0 ) $classes[] = 'ss-nav-menu-item-'.$this->index++;
		$classes[] = 'ss-nav-menu-item-depth-'.$depth;
		   
		//Megafy (top level)
		if( $depth == 0 && $this->getUberOption( $item->ID, 'isMega' ) != 'off' ){
			$classes[] = 'ss-nav-menu-mega';
			
			//Full Width Submenus
			if( $this->getUberOption( $item->ID, 'fullWidth' ) == 'on' ){
				$classes[] = 'ss-nav-menu-mega-fullWidth';
				
				//Menu Item Columns
				$numCols = $this->getUberOption( $item->ID, 'numCols' );
				if( is_numeric( $numCols ) && $numCols <= 7 && $numCols > 0 ){
					$classes[] = 'mega-colgroup mega-colgroup-'.$numCols;
				}
			}
			
			//Submenu Alignment
			$alignment = $this->getUberOption( $item->ID, 'alignSubmenu' );	//center, right, left
			if( empty( $alignment ) ) $alignment = 'center';
			$classes[] = 'ss-nav-menu-mega-align'.ucfirst( $alignment );

		}
		//Flyouts
		else if($depth == 0){
			$classes[] = 'ss-nav-menu-reg';
			//Submenu Alignment
			$alignment = $this->getUberOption( $item->ID, 'alignSubmenu' );	//right, left
			if( empty( $alignment ) ) $alignment = 'left';
			$classes[] = 'um-flyout-align-'. $alignment;
		}

		//Right Align
		if( $depth == 0 && $this->getUberOption( $item->ID , 'floatRight' ) == 'on' ) $classes[] = 'ss-nav-menu-mega-floatRight';
				
		//Second Level - Vertical Division
		if($depth == 1){
			if( $this->getUberOption( $item->ID, 'verticaldivision' ) == 'on' ) $classes[] = 'ss-nav-menu-verticaldivision';
		}
		
		//Third Level
		if($depth >= 2){
			if( $this->getUberOption( $item->ID, 'isheader' ) == 'on' ) $classes[] = 'ss-nav-menu-header';			//Headers
			if( $this->getUberOption( $item->ID, 'newcol' ) == 'on' ){												//New Columns
				$output.= '</ul></li>';
				$output.= '<li class="menu-item ss-nav-menu-item-depth-'.($depth-1).' sub-menu-newcol">'.
							'<span class="um-anchoremulator">&nbsp;</span><ul class="sub-menu sub-menu-'.$depth.'">';
			}
		}
		
		//Highlight
		if( $this->getUberOption( $item->ID, 'highlight' ) == 'on' ) $classes[] = 'ss-nav-menu-highlight';		//Highlights
		
		//Thumbnail
		$thumb = $uberMenu->getImage( $item->ID, $settings->op( 'wpmega-image-width' ), $settings->op( 'wpmega-image-height' ) );
		if( !empty( $thumb ) ) $classes[] = 'ss-nav-menu-with-img';
		
		//NoText, NoLink		
		$notext = $this->getUberOption( $item->ID, 'notext' ) == 'on' || $item->title == UBERMENU_NOTEXT ? true : false;
		$nolink = $this->getUberOption( $item->ID, 'nolink' ) == 'on' ? true : false;
		
		if( $notext ) $classes[] = 'ss-nav-menu-notext';
		if( $nolink ) $classes[] = 'ss-nav-menu-nolink';
		
		if( $sidebarOn  ) $classes[] = 'ss-sidebar';
		if( $overrideOn ) $classes[] = 'ss-override';
		
		$prepend = '<span class="wpmega-link-title">';
		$append = '</span>';

		//Icon
		$icon = $this->getUberOption( $item->ID, 'icon' );
		if( $icon ){
			$icon_markup = '<i class="'.apply_filters( 'ubermenu-icon-class' , $icon ).'"></i> ';
			//if( $notext ) $thumb.= $icon_markup; //If no text, use it as a thumbnail
			//else $prepend .= $icon_markup; //If text, append to title
			$prepend .= $icon_markup; //If text, append to title
			$classes[] = 'ss-nav-menu-with-icon';
		}

		//Description
		$description  = ! empty( $item->description ) ? '<span class="wpmega-item-description">'.esc_attr( $item->description ).'</span>' : '';
		
		if(	(	$depth == 0		&& 	!$settings->op( 'wpmega-description-0' ) )	||
			(	$depth == 1		&& 	!$settings->op( 'wpmega-description-1' ) )	||
			(	$depth >= 2		&& 	!$settings->op( 'wpmega-description-2' ) )  ){
			$description = '';
		}
		
		if( !empty( $description ) ) $classes[] = 'ss-nav-menu-with-desc';
		
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
		$class_names = ' class="'. esc_attr( $class_names ) . '"';

		$output .= /*$indent . */'<li id="menu-item-'. $item->ID . '"' . $value . $class_names .'>';

		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		//$attributes .= ! empty( $item->class )      ? ' class="'  . esc_attr( $item->class      ) .'"' : '';

		
		
		$item_output = '';
		
		/* Add title and normal link content - skip altogether if nolink and notext are both checked */
		if( !empty( $item->title ) && trim( $item->title ) != '' && !( $nolink && $notext ) ){

			//Determine the title
			$title = apply_filters( 'the_title', $item->title, $item->ID );
			if( $item->title == UBERMENU_NOTEXT || ( $notext && !$icon ) ) $title = $prepend = $append = '';

			//Horizontal Divider automatically skips the link
			if( $item->title == UBERMENU_SKIP ){
				$item_output.= UBERMENU_DIVIDER;
			}
			//A normal link or link emulator
			else{
				$item_output = $args->before;

				//Allow shortcodes in Title/Description?
				if( $settings->op( 'title-shortcodes' ) ){
					$title = do_shortcode( $title );
					$description = do_shortcode( $description );
				}

				//To link or not to link?
				if( $nolink )  $item_output.= '<span class="um-anchoremulator" >';
				else $item_output.= '<a'. $attributes .'>';
								
					//Prepend Thumbnail
					$item_output.= $thumb;

					//Link Before (not added by UberMenu)
					if( !$nolink ) $item_output.= $args->link_before;
				
						//Text - Title
						if( !$notext ) $item_output.= $prepend . $title . $append;
						elseif( $icon ) $item_output.= $prepend . $append;
				
						//Description
						$item_output.= $description;
				
					//Link After (not added by UberMenu)
					if( !$nolink ) $item_output.= $args->link_after;
				
				//Close Link or emulator
				if( $nolink ) $item_output.= '</span>'; 
				else $item_output.= '</a>';
				
				//Append after Link (not added by UberMenu)
				$item_output .= $args->after;
			}
		}
		
		/* Add overrides and widget areas */
		if( $overrideOn || $sidebarOn ){
			$class = 'wpmega-nonlink';
			
			//Get the widget area or shortcode
			$gooeyCenter = '';
			//Content Overrides
			if( $overrideOn ){
				$gooeyCenter = do_shortcode( $override );
			}
			//Widget Areas
			if( $sidebarOn ){
				$class.= ' wpmega-widgetarea ss-colgroup-'.$uberMenu->sidebarCount( $sidebar );	
				$gooeyCenter = $uberMenu->sidebar( $sidebar );
			}
			
			$item_output.= '<div class="'.$class.' uberClearfix">';
			$item_output.= $gooeyCenter;
			//$item_output.= '<div class="clear"></div>';
			$item_output.= '</div>';
		}
		
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		
	}




}