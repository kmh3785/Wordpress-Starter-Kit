<?php
require_once( 'backup.php' );
require_once( 'plugin-update-checker.php' );

define( 'UBERMENU_UPDATES_URL' , 'http://updates.sevenspark.com/ubermenu' );	//TODO
define( 'UBERMENU_UPDATES_CHECK_PERIOD' , 48 );


function ubermenu_update_checker(){

	//$username = ubermenu_op( 'envato_username' , 'updates' );
	//Too early to call ubermenu_op
	$settings = ubermenu_updates_get_account_settings();

	if( is_array( $settings ) && isset( $settings['envato_username'] ) ){

		//TODO: REMOVE
		//define( 'UBERMENU_UPDATES_URL' , 'http://mavair.local/~chris/updates/ubermenu' );
		
		$uber_update_checker = new PluginUpdateChecker_1_6 (
			//$url,
			UBERMENU_UPDATES_URL,
		    UBERMENU_FILE,
		    'ubermenu',
		    UBERMENU_UPDATES_CHECK_PERIOD
		);

		//Just make sure we don't dump errors to the end user if the URL fails
		$uber_update_checker->debugMode = false;

		$uber_update_checker->addQueryArgFilter( 'ubermenu_filter_update_checks' );
		$uber_update_checker->addResultFilter( 'ubermenu_filter_update_results' );

		//$uber_update_checker->checkForUpdates();	//Testing
	}
}
ubermenu_update_checker();

//Add the license key to query arguments.
function ubermenu_filter_update_checks( $queryArgs ) {

    $settings = ubermenu_updates_get_account_settings();
    //unset?

	foreach( $settings as $key => $val ){
		$queryArgs[$key] = urlencode( $val );
	}

	$queryArgs['site_url'] = get_site_url( null , '' , 'http' );
	$queryArgs['ubermenu_version'] = UBERMENU_VERSION;
    
    return $queryArgs;
}

function ubermenu_filter_update_results( $pluginInfo, $result ){

	$notices = array();

	if( isset( $pluginInfo->error ) ){
		//$notices = get_option( UBERMENU_UPDATE_NOTICES_KEY , array() );
		if( !isset( $notices['errors'] ) ) $notices['errors'] = array();
		$notices['errors'][] = $pluginInfo->error;
	}

	update_option( UBERMENU_UPDATE_NOTICES_KEY , $notices );

	return $pluginInfo;

}

$file = basename( dirname( UBERMENU_FILE ) ) . '/' . basename( UBERMENU_FILE );
add_action( "after_plugin_row_$file" , 'ubermenu_plugin_display_notice' , 10 , 2 );

function ubermenu_plugin_display_notice( $file , $plguin_data ){
	$notices = get_option( UBERMENU_UPDATE_NOTICES_KEY , array() );
	if( isset( $notices['errors'] ) ){
		echo '<tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange">';
		foreach ( $notices['errors'] as $e ){
			if( is_string( $e ) ) echo "<div class='update-message'>UberMenu update check error: $e</div>";
			else uberp( $e,4);
		}
		echo '</td></tr>';
	}
}



function ubermenu_updates_get_account_settings(){
	return get_option( UBERMENU_PREFIX.'updates' , array() );
}

//updates.sevenspark.com/ubermenu




//ubermenu_op( '')

add_filter( 'ubermenu_settings_panel_sections' , 'ubermenu_updates_section' , 100 );
add_filter( 'ubermenu_settings_panel_fields' , 'ubermenu_updates_fields' , 100 );
function ubermenu_updates_section( $sections ){
	$prefix = UBERMENU_PREFIX;
	$sections[] = array(
		'id' => $prefix.'updates',
		'title' => __( 'Updates', 'ubermenu' ),
		'sub_sections'	=> array(
			'backups'	=> array(
				'title' 	=> __( 'Backups' , 'ubermenu' ),
			),
			'updates'	=> array(
				'title' 	=> __( 'Account Info' , 'ubermenu' ),
			),
		),
	);

	return $sections;	
}
function ubermenu_updates_fields( $fields = array() ){
	$section = UBERMENU_PREFIX.'updates';
	$f = array();

	$f[] = array(
			'name'	=> 'backups_header',
			'label' => __( 'Custom Asset Backups' , 'ubermenu' ),
			'desc'	=> __( 'UberMenu will attempt to automatically backup and restore your custom.css and custom.js files when you update', 'ubermenu' ),
			'type'	=> 'header',
			'group'	=> 'backups',
		);

	$f[] = array(
			'name'	=> 'backup_custom_assets',
			'label'	=> __( 'Backup custom assets' , 'ubermenu' ),
			'desc'	=> __( 'Automatically backup custom.css and custom.js so that they can be restored after updating the plugin', 'ubermenu' ),
			'type'	=> 'checkbox',
			'default'	=> 'on',
			'group'	=> 'backups',
		);

	$f[] = array(
			'name'	=> 'backup_notice',
			'label' => __( 'Automatic backups status' , 'ubermenu' ),
			'desc'	=> ubermenu_field_backup_notice(),
			'type'	=> 'html',
			'group'	=> 'backups',
		);


	$f[] = array(
			'name'	=> 'update_settings',
			'label' => __( 'Update Notifications' , 'ubermenu' ), //__( 'Automatic Updates' , 'ubermenu' ),
			'desc'	=> __( 'Enter your Envato info to receive update notifications', 'ubermenu' ),
			'type'	=> 'header',
			'group'	=> 'updates',
		);

	$f[] = array(
			'name'	=> 'envato_username',
			'label'	=> __( 'CodeCanyon Username' , 'ubermenu' ),
			'desc'	=> 'Enter your Envato Username.  This must match the username on the account you used to purchase the UberMenu license.',
			'type'	=> 'text',
			'group'	=> 'updates',
		);


	$f[] = array(
			'name'	=> 'api_key',
			'label'	=> __( 'API Key' , 'ubermenu' ),
			'desc'	=> __( 'Enter your Envato API Key.  Create an API key at codecanyon.net by visiting your Settings Page and clicking "API Keys" in the left menu.', 'ubermenu' ),
			'type'	=> 'text',
			'group'	=> 'updates',
		);

	$f[] = array(
			'name'	=> 'purchase_code',
			'label'	=> __( 'Purchase Code' , 'ubermenu' ),
			'desc'	=> __( 'Enter your Envato Purchase Code', 'ubermenu' ) . '. <a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code-">Find your purchase code</a>',
			'type'	=> 'text',
			'group'	=> 'updates',
		);

	// $f[] = array(
			// 'name'	=> 'video_tutorials',
			// 'label' => __( 'Video Tutorials' , 'ubermenu' ),
			// 'desc'	=> ubermenu_video_tutorials_help(),
			// 'type'	=> 'html',
			// 'group'	=> 'video_tutorials',
		// );


	$fields[$section] = $f;
	return $fields;
}

function ubermenu_field_backup_notice(){

	$note = $msg = '';

	$custom_dir = trailingslashit( UBERMENU_DIR ).'custom/';

	//Find the Backups directory
	$uploads = wp_upload_dir();
	
	$uploads_dir = trailingslashit( $uploads['basedir'] );
	$backups_dir = $uploads_dir . 'ubermenu_backups/';

	$uploads_url = trailingslashit( $uploads['baseurl'] );
	$backups_url = $uploads_url . 'ubermenu_backups/';

	if( !is_writable( $uploads_dir ) ){
		//TODO - readd this: <strong>These files will be lost when updating if not backed up first</strong></p>  
		$note = '<p>The uploads directory is not writable by the server ( <code>'.$uploads_dir.'</code> ).  </p><p>UberMenu will not automatically be able to back up your <strong><code>custom.css</code></strong> and <strong><code>custom.js</code></strong> if you create them.  Please make this directory writable if you wish to automatically back up these files, otherwise you can back them up and restore manually after plugin update. <p>(If you are not using <code>custom.css</code> or <code>custom.js</code>, you can safely ignore this message)</p>';

		$msg.= '<div id="setting-error-update-write" class="ubermenu-settings-notice ubermenu-settings-notice-large ubermenu-settings-error">' . 
				'<i class="ubermenu-settings-notice-icon fa fa-warning"></i>'.
				'<strong>Automatic Backups Not Available</strong>'.
				'<p>'.$note.'</p></div>';
	}
	else{

		$backups_exist = false;

		$custom_css = $backups_dir . 'custom.css';
		$custom_css_url = $backups_url . 'custom.css';
		if( file_exists( $custom_css ) ){

			$backups_exist = true;

			$msg.= '<div class="ubermenu-settings-notice ubermenu-settings-success">' . 
				'<i class="ubermenu-settings-notice-icon fa fa-check"></i>'.
				'<strong>custom.css backup available</strong>'.
				' <a href="'.$custom_css_url .'" target="_blank" download="custom.css"><i class="fa fa-download"></i></a>'.
				'</div>';
		}

		$custom_js = $backups_dir . 'custom.js';
		$custom_js_url = $backups_url . 'custom.js';
		if( file_exists( $custom_js ) ){

			$backups_exist = true;

			$msg.= '<div class="ubermenu-settings-notice ubermenu-settings-success">' . 
				'<i class="ubermenu-settings-notice-icon fa fa-check"></i>'.
				'<strong>custom.js backup available</strong>'.
				' <a href="'.$custom_js_url .'" download="custom.js" target="_blank"><i class="fa fa-download"></i></a>'.
				'</div>';
		}



		if( file_exists( $backups_dir ) ){

			if( file_exists( $custom_dir . 'custom.css' ) && !is_writable( $backups_dir . 'css' ) ){
				$msg.= '<div class="ubermenu-settings-notice ubermenu-settings-error">' . 
					'<i class="ubermenu-settings-notice-icon fa fa-warning"></i>'.
					'<strong>Daily CSS backups not writable</strong>'.
					' <p>UberMenu attempts to save daily backups, but this directory is not writable. <code>'.$backups_dir.'css/</code></p>'.
					'</div>';
			}

			if( file_exists( $custom_dir . 'custom.js' ) && !is_writable( $backups_dir . 'js' ) ){
				$msg.= '<div class="ubermenu-settings-notice ubermenu-settings-error">' . 
					'<i class="ubermenu-settings-notice-icon fa fa-warning"></i>'.
					'<strong>Daily JS backups not writable</strong>'.
					' <p>UberMenu attempts to save daily backups, but this directory is not writable. <code>'.$backups_dir.'js/</code></p>'.
					'</div>';
			}

		}



		if( !$backups_exist ){

			if( file_exists( $custom_dir.'custom.css' ) ||
				file_exists( $custom_dir.'custom.js' )){
				$msg.= '<div class="ubermenu-settings-notice ubermenu-settings-success"><i class="fa fa-info-circle"></i> No backups found.  If this message is present after refreshing, please check that your /uploads directory is writable.</div>';
			}
			else{
				$msg.= '<div class="ubermenu-settings-notice ubermenu-settings-success"><i class="fa fa-info-circle"></i> No custom assets in use.</div>';
			}
		}

	}

	

	

	
	
	

	return $msg;
}



