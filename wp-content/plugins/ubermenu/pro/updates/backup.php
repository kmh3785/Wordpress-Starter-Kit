<?php

if ( !defined( 'ABSPATH' ) ) exit;


/** 
 * BACKUP ON ADMIN ACCESS
 */

/*
 * Run a backup when users with update plugin capabilities access the admin panel.
 * This ensures that a backup of the latest custom files will be made prior to
 * any updates being run.
 */
add_action( 'admin_init' , 'ubermenu_run_backups' );
function ubermenu_run_backups(){
	if( !defined( 'DOING_AJAX' ) && current_user_can( 'update_plugins' ) ){
		if( ubermenu_op( 'backup_custom_assets' , 'updates' ) != 'off' ){
			ubermenu_backup_custom_assets();
			//ubermenu_restore_custom_assets();	//Just for testing
		}
	}
}

/*
 * Run backups for the custom assets, custom.css and custom.js - if they exist
 */
function ubermenu_backup_custom_assets(){

	$custom_dir = trailingslashit( UBERMENU_DIR ).'custom/';

	$custom_css = $custom_dir.'custom.css';
	if( file_exists( $custom_css ) ){
		ubermenu_backup_file( $custom_css , 'custom.css' , 'css' );
	}

	$custom_js = $custom_dir.'custom.js';
	if( file_exists( $custom_js ) ){
		ubermenu_backup_file( $custom_js , 'custom.js' , 'js' );
	}

}

/*
 * Copies the source file to uploads/ubermenu_backups directory
 * Also creates a date-stamped backup in the daily folder (optional)
 */
function ubermenu_backup_file( $source_file , $dest_filename , $daily_folder = false ){

	//If the destination filename is empty, bail
	if( !$dest_filename ){
		error_log( 'UberMenu: Cannot backup file (destination not set) :: '. $source_file . ' :: '.$dest_filename );
		return;
	}

	//If the source file doesn't exist, bail
	if( !file_exists( $source_file ) ){
		error_log( 'UberMenu: Cannot backup file (source file does not exist) :: ' . $source_file );
		return;
	}

	//Find the path to the backups directory
	$uploads = wp_upload_dir();
	$uploads_dir = trailingslashit( $uploads['basedir'] );
	$dest_dir = $uploads_dir . 'ubermenu_backups/';
	
	//Create ubermenu_backups dir if it doesn't already exist
	if( !ubermenu_make_backup_dir( $dest_dir ) ){
		//Bail if making the backup directory fails
		return;
	}

	//Destination File Name
	$dest_file = $dest_dir . $dest_filename;

	//Make the latest copy
	if( is_writable( $dest_dir ) ){
		if( !copy( $source_file, $dest_file ) ){
			//Warning should be printed automatically in this case
			//return; //copy failed
		}
	}
	//If the destination directory isn't writable, log the error
	else{
		error_log( "UberMenu: could not back up $source_file , $dest_dir not writable" );
	}

	//Make a daily backup
	$daily_dir = $dest_dir.$daily_folder.'/';		//Daily backup folder inside /ubermenu_backups
	ubermenu_make_backup_dir( $daily_dir );		//Create the daily directory if it doesn't exist

	if( is_writable( $daily_dir ) ){

		$daily = $daily_dir . $dest_filename . '_' . current_time( 'Y-m-d' );	//Date-stamp the file

		copy( $source_file , $daily );				//Make the backup

		//Clear old backups - if there are more than 10 files, purge the oldest
		$max_files = 10;
		$files = glob( $daily_dir.'*.*' );
		if( count( $files ) > $max_files ){
			asort( $files );	//Make sure they are sorted alphabetically (which is chronologically, due to the date stamp)
			//uberp( $files );
			while( count( $files ) > $max_files ){
				unlink( $files[0] );	//Delete the file from the server
				unset( $files[0] );		//This is critical, otherwise we loop infinitely
			}
		}
	}

}

/*
 * Creates a directory if it doesn't already exist
 */
function ubermenu_make_backup_dir( $dir ){
	if( !file_exists( $dir ) ){
		if( !wp_mkdir_p( $dir ) ){
			return false; //Couldn't create directory
		}
	}
	return true;
}



/** 
 * RESTORE ON PLUGIN ACTIVATION
 */

/*
 * When the plugin is activated, restore the custom assets
 * (Plugins are re-activated after update)
 */
register_activation_hook( UBERMENU_FILE , 'ubermenu_restore_backups' );
function ubermenu_restore_backups(){
	ubermenu_restore_custom_assets();
}

/*
 * Restores custom.css and custom.css - if they exist
 */
function ubermenu_restore_custom_assets(){

	$custom_dir = trailingslashit( UBERMENU_DIR ).'custom/';	//UberMenu's /custom directory

	//Find the Backups directory
	$uploads = wp_upload_dir();
	$uploads_dir = trailingslashit( $uploads['basedir'] );
	$backups_dir = $uploads_dir . 'ubermenu_backups/';

	//Restore CSS backup - if one exists and the custom.css does not exist in the plugin
	$custom_css = $custom_dir.'custom.css';
	$backup_css = $backups_dir.'custom.css';
	if( !file_exists( $custom_css ) && file_exists( $backup_css ) ){
		$result = ubermenu_restore_file( $backup_css , $custom_css , $custom_dir );

		if( is_wp_error( $result ) ){
			add_action( 'admin_notices', 'ubermenu_restore_admin_notice_fail_css' );
		}
	}

	//Restore JS backup - if one exists and the custom.js does not exist in the plugin
	$custom_js = $custom_dir.'custom.js';
	$backup_js = $backups_dir.'custom.js';
	if( !file_exists( $custom_js ) && file_exists( $backup_js ) ){
		ubermenu_restore_file( $backup_js , $custom_js , $custom_dir );
	}
}

/*
 * Copies the file from the source to the destination
 * $dest_file is a full path
 */
function ubermenu_restore_file( $source_file , $dest_file , $dest_dir ){
	//echo 'restore '.$source_file .' to ' .$dest_file;
	
	//If the directory is writable
	if( is_writable( $dest_dir ) ){
		copy( $source_file , $dest_file );
	}
	else{
		error_log( 'UberMenu: could not restore (not writable) ' . $source_file . ' to ' . $dest_file );
		//Not really important, since the directory had to be writable in order to run the update
		//and delete the file in the first place
		return new WP_Error( 'ubermenu_restore_failed' , 'File not writable' );
	}
}



function ubermenu_restore_admin_notice_fail_css(){
	ubermenu_restore_admin_notice( __( 'Could not restore custom.css file, as the directory is not writable.  You can manually restore the file from the wp-content/uploads/ubermenu_backups directory' , 'ubermenu' ) , 'error' );
}

function ubermenu_restore_admin_notice( $notice , $type = 'udpated' ) {
    ?>
    <div class="<?php echo $type; ?>">
        <p><?php echo $notice; ?></p>
    </div>
    <?php
}




