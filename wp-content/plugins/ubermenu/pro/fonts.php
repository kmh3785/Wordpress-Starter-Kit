<?php

add_action( 'wp_enqueue_scripts' , 'ubermenu_load_fonts' );
function ubermenu_load_fonts(){

	$menus = ubermenu_get_menu_instances( true );
	foreach( $menus as $menu ){
		$google_font = ubermenu_op( 'google_font' , $menu );
		if( $google_font ){
			//echo $google_font.'<br/>';
			ubermenu_load_font( $google_font );
		}
	}

}

/** CUSTOMIZATION FUNCTIONS **/
function ubermenu_get_menu_style_google_font( $field , $menu_id , &$menu_styles ){

	$font_id = ubermenu_op( $field['name'] , $menu_id );
	if( $font_id ){
		$stack = ubermenu_get_font_stack( $font_id );
		$selector = ".ubermenu-$menu_id, .ubermenu-$menu_id .ubermenu-target, .ubermenu-$menu_id .ubermenu-nav .ubermenu-item-level-0 .ubermenu-target, .ubermenu-$menu_id div, .ubermenu-$menu_id p, .ubermenu-$menu_id input";
		$menu_styles[$selector]['font-family'] = $stack;

		$style = ubermenu_op( 'google_font_style' , $menu_id );
		$props = ubermenu_get_font_style_props( $style );
		if( is_array( $props ) ){
			foreach( $props as $prop => $val ){
				$menu_styles[$selector][$prop] = $val;
			}
		}
	}
}
function ubermenu_get_menu_style_custom_font( $field , $menu_id , &$menu_styles ){

	$font_value = ubermenu_op( $field['name'] , $menu_id );
	if( $font_value ){
		
		$selector = ".ubermenu-$menu_id, .ubermenu-$menu_id .ubermenu-target, .ubermenu-$menu_id .ubermenu-nav .ubermenu-item-level-0 .ubermenu-target";
		$menu_styles[$selector]['font'] = $font_value;
	}
}


function ubermenu_get_font_ops(){

	$fonts = ubermenu_get_registered_fonts();

	$font_select = array( '' => 'None' );
	foreach( $fonts as $font_id => $font_ops ){
		$font_select[$font_id] = $font_ops['label'];		
	}
	//up( $font_select , 2 );
	return $font_select;
}

function ubermenu_get_font_stack( $font_id ){
	$fonts = ubermenu_get_registered_fonts();
	if( isset( $fonts[$font_id] ) ){
		if( isset( $fonts[$font_id]['stack'] ) ){
			return $fonts[$font_id]['stack'];
		}
	}
}




function ubermenu_init_fonts(){
	add_action( 'ubermenu_register_fonts' , 'ubermenu_register_default_fonts' );
	do_action( 'ubermenu_register_fonts' );
}
add_action( 'init' , 'ubermenu_init_fonts' , 8 ); //Run before ubermenu init

function ubermenu_get_registered_fonts(){
	$fonts = _UBERMENU()->get_registered_fonts();
	$fonts = apply_filters( 'ubermenu_registered_fonts' , $fonts );

	return $fonts;
}

function ubermenu_register_font( $font_id , $font_ops ){
	_UBERMENU()->register_font( $font_id , $font_ops );
}



function ubermenu_load_font( $font_id , $style = '' ){

	$fonts = ubermenu_get_registered_fonts();
	if( isset( $fonts[$font_id] ) ){
		extract( $fonts[$font_id] );

		if( $style != '' ){
			$style = ':'.$style;
		}
		else{
			$styles = ubermenu_get_font_styles();

			//foreach( $styles as $style_id => $style_name ){}
			if( is_array( $styles ) ){
				$style.= ':';
				$style.= implode( ',' , array_keys( $styles ) );
			}
		}

		$src = '//fonts.googleapis.com/css?family='.$family.$style;

		wp_enqueue_style( 'ubermenu-'.$font_id , $src , false ); //, 'um-'.UBERMENU_VERSION );
	}
}

function ubermenu_get_font_style_ops(){
	$styles = ubermenu_get_font_styles();
	$style_ops = array();
	foreach( $styles as $id => $_style ){
		$style_ops[$id] = $_style['label'];
	}
	return  $style_ops;
}
function ubermenu_get_font_styles(){

	$styles = array(

		'' 		=> array(
			'label'	=> __( 'Default', 'ubermenu' ),
			'props'	=> '',
		),
		'300'	=> array(
			'label'	=> __( 'Light', 'ubermenu' ),
			'props'	=> array( 
							'font-weight' => '300'
						),
			),
		'400'	=> array(
			'label'	=> __( 'Normal', 'ubermenu' ),
			'props'	=> array( 
							'font-weight' => '400'
						),
			),
		'700'	=> array(
			'label'	=> __( 'Bold', 'ubermenu' ),
			'props'	=> array( 
							'font-weight' => '700'
						),
			),
	);

	return apply_filters( 'ubermenu_google_font_styles' , $styles );
}
function ubermenu_get_font_style_props( $style_id ){
	$styles = ubermenu_get_font_styles();
	if( isset( $styles[$style_id] ) ){
		return $styles[$style_id]['props'];
	}
	return false;
}

function ubermenu_register_default_fonts(){

	ubermenu_register_font( 'open-sans' , array(
			'label'		=> 'Open Sans',
			'family'	=> 'Open+Sans',
			'stack'		=> "'Open Sans', sans-serif",
		)
	);


	ubermenu_register_font( 'roboto' , array(
			'label'		=> 'Roboto',
			'family'	=> 'Roboto',
			'stack'		=> "'Roboto', sans-serif",
		)
	);


	ubermenu_register_font( 'oswald' , array(
			'label'		=> 'Oswald',
			'family'	=> 'Oswald',
			'stack'		=> "'Oswald', sans-serif",
		)
	);
	
	ubermenu_register_font( 'lato' , array(
			'label'		=> 'Lato',
			'family'	=> 'Lato',
			'stack'		=> "'Lato', sans-serif",
		)
	);

	ubermenu_register_font( 'droid-sans' , array(
			'label'		=> 'Droid Sans (no light)',
			'family'	=> 'Droid+Sans',
			'stack'		=> "'Droid Sans', sans-serif",
		)
	);

	ubermenu_register_font( 'open-sans-condensed' , array(
			'label'		=> 'Open Sans Condensed (Light or Bold only)',
			'family'	=> 'Open+Sans+Condensed',
			'stack'		=> "'Open Sans Condensed', sans-serif",
		)
	);

	ubermenu_register_font( 'pt-sans' , array(
			'label'		=> 'PT Sans (Normal or Bold only)',
			'family'	=> 'PT+Sans',
			'stack'		=> "'PT Sans', sans-serif",
		)
	);

	ubermenu_register_font( 'source-sans-pro' , array(
			'label'		=> 'Source Sans Pro',
			'family'	=> 'Source+Sans+Pro',
			'stack'		=> "'Source Sans Pro', sans-serif",
		)
	);

	ubermenu_register_font( 'roboto-condensed' , array(
			'label'		=> 'Roboto Condensed',
			'family'	=> 'Roboto+Condensed',
			'stack'		=> "'Roboto Condensed', sans-serif",
		)
	);

	ubermenu_register_font( 'droid-serif' , array(
			'label'		=> 'Droid Serif (Normal or Bold only)',
			'family'	=> 'Droid+Serif',
			'stack'		=> "'Droid Serif', serif",
		)
	);

	ubermenu_register_font( 'ubuntu' , array(
			'label'		=> 'Ubuntu',
			'family'	=> 'Ubuntu',
			'stack'		=> "'Ubuntu', serif",
		)
	);

	ubermenu_register_font( 'montserrat' , array(
			'label'		=> 'Montserrat (Normal or Bold only)',
			'family'	=> 'Montserrat',
			'stack'		=> "'Montserrat', sans-serif",
		)
	);

	ubermenu_register_font( 'raleway' , array(
			'label'		=> 'Raleway',
			'family'	=> 'Raleway',
			'stack'		=> "'Raleway', sans-serif",
		)
	);

	ubermenu_register_font( 'lora' , array(
			'label'		=> 'Lora',
			'family'	=> 'Lora',
			'stack'		=> "'Lora', serif",
		)
	);

	ubermenu_register_font( 'dosis' , array(
			'label'		=> 'Dosis',
			'family'	=> 'Dosis',
			'stack'		=> "'Dosis', sans-serif",
		)
	);

	ubermenu_register_font( 'abel' , array(
			'label'		=> 'Abel (Normal only)',
			'family'	=> 'Abel',
			'stack'		=> "'Abel', sans-serif",
		)
	);

	ubermenu_register_font( 'arvo' , array(
			'label'		=> 'Arvo (Normal or Bold only)',
			'family'	=> 'Arvo',
			'stack'		=> "'Arvo', serif",
		)
	);

	ubermenu_register_font( 'arimo' , array(
			'label'		=> 'Arimo (Normal or Bold only)',
			'family'	=> 'Arimo',
			'stack'		=> "'Arimo', sans-serif",
		)
	);

	ubermenu_register_font( 'bitter' , array(
			'label'		=> 'Bitter (Normal or Bold only)',
			'family'	=> 'Bitter',
			'stack'		=> "'Bitter', serif",
		)
	);

	ubermenu_register_font( 'lobster' , array(
			'label'		=> 'Lobster (Normal only)',
			'family'	=> 'Lobster',
			'stack'		=> "'Lobster', cursive",
		)
	);


	ubermenu_register_font( 'shadows_into_light' , array(
			'label'		=> 'Shadows Into Light (Normal only)',
			'family'	=> 'Shadows+Into+Light',
			'stack'		=> "'Shadows Into Light', cursive",
		)
	);

	ubermenu_register_font( 'rokkitt' , array(
			'label'		=> 'Rokkitt (Normal or Bold only)',
			'family'	=> 'Rokkitt',
			'stack'		=> "'Rokkitt', serif",
		)
	);

	ubermenu_register_font( 'roboto_slab' , array(
			'label'		=> 'Roboto Slab',
			'family'	=> 'Roboto+Slab',
			'stack'		=> "'Roboto Slab', serif",
		)
	);

	ubermenu_register_font( 'merriweather' , array(
			'label'		=> 'Merriweather',
			'family'	=> 'Merriweather',
			'stack'		=> "'Merriweather', serif",
		)
	);

	ubermenu_register_font( 'indie_flower' , array(
			'label'		=> 'Indie Flower (Normal only)',
			'family'	=> 'Indie+Flower',
			'stack'		=> "'Indie Flower', cursive",
		)
	);

	ubermenu_register_font( 'inconsolata' , array(
			'label'		=> 'Inconsolata (Normal or Bold only)',
			'family'	=> 'Inconsolata',
			'stack'		=> "'Inconsolata', monospace",
		)
	);

	ubermenu_register_font( 'play' , array(
			'label'		=> 'Play (Normal or Bold only)',
			'family'	=> 'Play',
			'stack'		=> "'Play', sans-serif",
		)
	);

	ubermenu_register_font( 'archivo_narrow' , array(
			'label'		=> 'Archivo Narrow (Normal or Bold only)',
			'family'	=> 'Archivo+Narrow',
			'stack'		=> "'Archivo Narrow', sans-serif",
		)
	);

	ubermenu_register_font( 'pacifico' , array(
			'label'		=> 'Pacifico (Normal only)',
			'family'	=> 'Pacifico',
			'stack'		=> "'Pacifico', cursive",
		)
	);

	ubermenu_register_font( 'bree_serif' , array(
			'label'		=> 'Bree Serif (Normal only)',
			'family'	=> 'Bree+Serif',
			'stack'		=> "'Bree Serif', sans-serif",
		)
	);

}

