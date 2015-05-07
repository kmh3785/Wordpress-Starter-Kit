<?php

/**
 * CREATE INSTANCE MANAGER
 */

add_action( 'ubermenu_settings_before' , 'ubermenu_instance_manager');

function ubermenu_instance_manager(){
	
	?>

	<div class="ubermenu_instance_manager">

		<a class="ubermenu_instance_toggle ubermenu_instance_button">+ Add UberMenu Configuration</a>

		<div class="ubermenu_instance_wrap ubermenu_instance_container_wrap">

			<div class="ubermenu_instance_container">

				<h3>Add UberMenu Configuration</h3>

				<form class="ubermenu_instance_form">
					<input class="ubermenu_instance_input" type="text" name="ubermenu_instance_id" placeholder="configuration_id" />
					<?php wp_nonce_field( 'ubermenu-add-instance' ); ?>
					<a class="ubermenu_instance_button ubermenu_instance_create_button">Create Configuration</a>
				</form>

				<p class="ubermenu_instance_form_desc">Enter an ID for your new menu configuration.  This ID will be used when printing the menu, 
					and must contain only letters, hyphens, and underscores.  <a class="ubermenu_instance_notice_close" href="#">close</a></p>

				<span class="ubermenu_instance_close">&times;</span>

			</div>

		</div>


		<div class="ubermenu_instance_wrap ubermenu_instance_notice_wrap ubermenu_instance_notice_success">
			<div class="ubermenu_instance_notice">
				New menu created. <a href="<?php echo admin_url('themes.php?page=ubermenu-settings'); ?>" class="ubermenu_instance_button">Refresh Page</a> 
				<p>Note: Any setting changes you've made have not been saved yet.  <a class="ubermenu_instance_notice_close" href="#">close</a></p>
			</div>
		</div>

		<div class="ubermenu_instance_wrap ubermenu_instance_notice_wrap ubermenu_instance_notice_error">
			<div class="ubermenu_instance_notice">
				New menu configuration creation failed.  <span class="ubermenu-error-message">You may have a PHP error on your site which prevents AJAX requests from completing.</span>  <a class="ubermenu_instance_notice_close" href="#">close</a>
			</div>
		</div>

		<div class="ubermenu_instance_wrap ubermenu_instance_notice_wrap ubermenu_instance_delete_notice_success">
			<div class="ubermenu_instance_notice">
				Configuration Deleted.  <a class="ubermenu_instance_notice_close" href="#">close</a></p>
			</div>
		</div>

		<div class="ubermenu_instance_wrap ubermenu_instance_notice_wrap ubermenu_instance_delete_notice_error">
			<div class="ubermenu_instance_notice">
				Menu Configuration deletion failed.  <span class="ubermenu-delete-error-message">You may have a PHP error on your site which prevents AJAX requests from completing.</span>  <a class="ubermenu_instance_notice_close" href="#">close</a>
			</div>
		</div>

		
	</div>

	<?php
}

function ubermenu_add_instance_callback(){

	check_ajax_referer( 'ubermenu-add-instance' , 'ubermenu_nonce' );

	$response = array();

	$serialized_settings = $_POST['ubermenu_data'];
	$dirty_settings = array();
	parse_str( $serialized_settings, $dirty_settings );
	
	//ONLY ALLOW SETTINGS WE'VE DEFINED	
	$data = wp_parse_args( $dirty_settings, array( 'ubermenu_instance_id' ) );

	$new_id = $data['ubermenu_instance_id'];

	if( $new_id == '' ){
		$response['error'] = 'Please enter an ID. ';
	}
	else{
		//$new_id = sanitize_title( $new_id );
		$new_id = sanitize_key( $new_id );

		//update 
		$menus = get_option( UBERMENU_MENU_INSTANCES , array() );

		if( in_array( $new_id , $menus ) ){
			$response['error'] = 'That ID is already taken. ';
		}
		else{
			$menus[] = $new_id;
			update_option( UBERMENU_MENU_INSTANCES , $menus );
		}

		$response['id'] = $new_id;
	}

	$response['data'] = $data;

	echo json_encode( $response );

	die();
}
add_action( 'wp_ajax_ubermenu_add_instance', 'ubermenu_add_instance_callback' );


function ubermenu_delete_instance_callback(){

	check_ajax_referer( 'ubermenu-delete-instance' , 'ubermenu_nonce' );

	$response = array();
//echo json_encode( $_POST['ubermenu_data'] );
//die();
	//$serialized_settings = $_POST['ubermenu_data'];
	//$dirty_settings = array();
	//parse_str( $serialized_settings, $dirty_settings );
	
	$dirty_settings = $_POST['ubermenu_data'];

	//ONLY ALLOW SETTINGS WE'VE DEFINED	
	$data = wp_parse_args( $dirty_settings, array( 'ubermenu_instance_id' ) );

	$id = $data['ubermenu_instance_id'];

	if( $id == '' ){
		$response['error'] = 'Missing ID';
	}
	else{
		
		$menus = get_option( UBERMENU_MENU_INSTANCES , array() );

		if( !in_array( $id , $menus ) ){
			$response['error'] = 'ID not in $menus ['.$id.']';
		}
		else{
			//unset( $menus[$id] );
			$i = array_search( $id , $menus );
			if( $i !== false ) unset( $menus[$i] );

			update_option( UBERMENU_MENU_INSTANCES , $menus );
			$response['menus'] = $menus;
		}

		$response['id'] = $id;
	}

	$response['data'] = $data;

	echo json_encode( $response );

	die();
}
add_action( 'wp_ajax_ubermenu_delete_instance', 'ubermenu_delete_instance_callback' );

