<?php 

/*
 * This file contains the code necessary to set up a demo site.  It's not activated by
 * default.  
 */

function wpmega_demo_init(){
	if(!is_admin()){
		add_action('wp_print_styles', 'wpmega_demo_load_css');
		add_action('init', 'wpmega_demo_load_js');
	}	
}
add_action( 'plugins_loaded', 'wpmega_demo_init', 20);

function wpmega_demo_load_css(){
	$tmp = plugins_url().'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	wp_enqueue_style('wpmega-demo', 	$tmp.'demo.css', 					false, '1.0', 'all');
}

function wpmega_demo_load_js(){
	$tmp = plugins_url().'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	wp_enqueue_script('wpmegamenu-demo', $tmp.'demo.js', array('jquery'));
}


function wpmega_demo(){
	
	global $uberMenu;
	$settings = $uberMenu->getSettings();
	
	$html = '<div id="wpmega-demo"><div id="wpmega-demo-inner">';
	
	$html.= '<h4>'.__( 'Demo Controls' , 'ubermenu' ).'</h4>';
	$html.= '<div class="wpmega-demo-sub">'.__( 'Try out a few of the basic options to see what UberMenu can do.  '.
			'There are many more available via the control panel!' , 'ubermenu' ).'</div>';
	
	$descdef = isset($_GET['wpmega-demo-description-0']) ? $_GET['wpmega-demo-description-0'] : 'off';
	$styledef = isset($_GET['wpmega-demo-style']) ? $_GET['wpmega-demo-style'] : 'black-white-2';
	$orientdef = isset($_GET['wpmega-demo-orientation']) ? $_GET['wpmega-demo-orientation'] : 'horizontal';
	$transdef = isset($_GET['wpmega-demo-transition']) ? $_GET['wpmega-demo-transition'] : 'slide';
	$triggerdef = isset($_GET['wpmega-demo-trigger']) ? $_GET['wpmega-demo-trigger'] : 'hoverIntent';
	
	$html.= '<form action="'.get_bloginfo('url').'">';	
	
	$html.= $settings->showAdminOption(
			'wpmega-demo-transition',
			array(
				'title'		=>	__( 'Transition' , 'ubermenu' ),
				'type'		=>	'radio',
				'ops'		=>	array(
									'slide'		=>	__( 'Slide' , 'ubermenu' ),
									'fade'		=>	__( 'Fade' , 'ubermenu' ),
									'none'		=>	__( 'None' , 'ubermenu' )
								),
				'default'	=>	$transdef,
			)
		);
	
	
	$html.= $settings->showAdminOption(
			'wpmega-demo-trigger',
			array(
				'title'		=>	__( 'Trigger' , 'ubermenu' ),
				'type'		=>	'radio',
				'ops'		=>	array(
									'hoverIntent'	=>	'<span title="'.__( 'Submenu appears after a brief delay to enhance user experience' , 'ubermenu' ).'">'.__( 'Hover Intent' , 'ubermenu' ).'</span>',
									'hover'			=>	__( 'Hover' , 'ubermenu' ),
									'click'			=>	__( 'Click' , 'ubermenu' )
								),
				'default'	=>	$triggerdef,
			)
		);
	
	
	$html.= $settings->showAdminOption(
			'wpmega-demo-orientation',
			array(
				'title'		=>	__( 'Orientation' , 'ubermenu' ),
				'type'		=>	'radio',
				'ops'		=>	array(
									'horizontal'	=>	__( 'Horizontal' , 'ubermenu' ),
									'vertical'		=>	__( 'Vertical' , 'ubermenu' )
								),
				'default'	=>	$orientdef,
			)
		);
	
	
	$html.= $settings->showAdminOption(
			'wpmega-demo-description-0',
			array(
				'title'		=>	__( 'Display' , 'ubermenu' ),
				'type'		=>	'checkbox',
				'class'		=>	'wpmega-admin-op-box',
				'default'	=>	$descdef,
				'before'	=>	'<div class="ss-admin-op-title">'.__( 'Top-Level Descriptions' , 'ubermenu' ) . '</div>'
			)
		);
	
	
	

	$demoOps = ubermenu_getStylePresetOps( 'standard' );
	unset($demoOps['custom']);
	$html.= $settings->showAdminOption(
		'wpmega-demo-style', 
		array(
			'title'		=>	__( 'Style Preset' , 'ubermenu' ),
			'type'		=>	'select',
			'ops'		=>	$demoOps,
			'default'	=>	$styledef,
			'class'		=>	'clearfix'
		)
	);
	
	
	$html.= '<div class="ss-admin-op">';
	$html.= '<input type="hidden" value="is_submitted" name="is_submitted" />';
	$html.= '<input class="button button-red button-lighttext" type="submit" value="'.__( 'Preview Menu' , 'ubermenu' ).'"/>';
	$html.= '</div>';
	$html.= '<div class="clear"></div>';
	$html.= '</form>';
	
	$html.= '</div></div>';
	
	return $html;	
}

add_shortcode('ubermenu-demo', 'wpmega_demo');

function ubermenu_demo_settings_filter_callback( $settings ){
	
	if(isset($_GET['is_submitted'])){		
		if(isset($_GET['wpmega-demo-description-0'])) $settings['wpmega-description-0'] =  $_GET['wpmega-demo-description-0'];
		if(isset($_GET['wpmega-demo-orientation'])) $settings['wpmega-orientation'] =  $_GET['wpmega-demo-orientation'];
		if(isset($_GET['wpmega-demo-transition'])) $settings['wpmega-transition'] =  $_GET['wpmega-demo-transition'];
		if(isset($_GET['wpmega-demo-trigger'])) $settings['wpmega-trigger'] =  $_GET['wpmega-demo-trigger'];
		if(isset($_GET['wpmega-demo-style']) && $_GET['wpmega-demo-style'] != 'none') $settings['wpmega-style-preset'] = $_GET['wpmega-demo-style'];
	}
	
	return $settings;
}
add_filter( 'wp-mega-menu-settings_settings_filter', 'ubermenu_demo_settings_filter_callback' );

?>