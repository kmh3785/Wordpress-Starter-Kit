<?php

add_action( 'wp_footer' , 'ubermenu_diagnostics_loader' );
function ubermenu_diagnostics_loader(){

	//Only load for admins
	if( !current_user_can( 'manage_options' ) ) return;

	?>
	<script>
	(function($){
		var ubermenu_diagnostics_initialized = false;
		window.ubermenu_diagnostics_present = false;

		jQuery(function($) {
			ubermenu_init_diagnostics();
		});

		$( window ).load( function(){
			ubermenu_init_diagnostics();
			//setTimeout( function(){ ubermenu_init_diagnostics(); } , 200 );
		});
	
		function ubermenu_init_diagnostics(){
			
			if( ubermenu_diagnostics_initialized ) return;
			ubermenu_diagnostics_initialized = true;
			$( '.ubermenu-diagnostics-loader-button' ).on( 'click' , function(e){
				e.preventDefault();
				//Load script once
				if( !window.ubermenu_diagnostics_present ){
					ubermenu_load_diagnostics();
				}
			});
		}
		function ubermenu_load_diagnostics(){	
			$.getScript( '<?php echo UBERMENU_URL.'pro/diagnostics/diagnostics.js'; ?>' );
			$('head').append('<link rel="stylesheet" type="text/css" href="<?php echo UBERMENU_URL.'pro/diagnostics/diagnostics.css'; ?>">');
		}

		//Testing
		//setTimeout( ubermenu_load_diagnostics , 300 ); 
	})(jQuery);
	</script>
	<?php
}


function ubermenu_diagnostics_get_item_settings( $item_id ){
	$settings = get_post_meta( $item_id , UBERMENU_MENU_ITEM_META_KEY , true );
	if( $settings ){
		$settings = apply_filters( 'ubermenu_item_settings' , $settings , $item_id );
	}
	else{
		$settings = ubermenu_menu_item_setting_defaults();
		$settings['defaults'] = 1;
	}
	

	
	
	return $settings;
}
function ubermenu_diagnostics_item_info_callback(){

	if( ubermenu_op( 'diagnostics' , 'general' ) != 'on' ) die();

	if( isset( $_POST['menu_item_id'] ) ){
		$item_id = $_POST['menu_item_id'];
		$settings = ubermenu_diagnostics_get_item_settings( $item_id );
		//print_r( $settings );
		echo json_encode( $settings );
	}
	die();	
}
add_action( 'wp_ajax_ubermenu_diagnostics' , 'ubermenu_diagnostics_item_info_callback' );