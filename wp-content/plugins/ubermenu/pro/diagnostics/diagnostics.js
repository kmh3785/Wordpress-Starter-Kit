(function($){

	window.ubermenu_diagnostics_present = true;

	if( console ) console.log( 'Loaded UberMenu Diagnostics' );

	$( '.ubermenu-item-level-0' ).each( function(){

		var menu_item_id;
		var attr_id = $( this ).attr( 'id' );
		if( attr_id ){
			menu_item_id = attr_id.substring( 10 );
		}
		else{
			menu_item_id = '(repeat)';
		}

		var $item = $(this);

		var data = {
			action: 'ubermenu_diagnostics',
			menu_item_id: menu_item_id
		};

		$.post( ubermenu_data.ajax_url, data, function( r ) {
			//console.log( r );
			build_diagnostics_box( $item , menu_item_id , r );
		}, 'json' );
		
	});

	//console.log( 'setup clickers ' + $( '.ubermenu' ).size() );

	$( '.ubermenu' ).on( 'click' , '.um-db-button-pin' , function(e){
		e.preventDefault();
		$(this).toggleClass( 'um-db-button-active' );
		$(this).closest( '.ubermenu-item' ).toggleClass( 'ubermenu-force' );
	});

	$( '.ubermenu' ).on( 'click' , '.um-db-button-highlight' , function(e){
		e.preventDefault();
		$(this).toggleClass( 'um-db-button-active' );
		$(this).closest( '.ubermenu-item' ).toggleClass( 'ubermenu-highlight-layout' );
		$( '.ubermenu' ).ubermenu( 'sizeTabs' );
	});

	$( '.ubermenu' ).on( 'click' , '.um-db-button[data-umdb-target]' , function(e){
		e.preventDefault();

		$(this).toggleClass( 'um-db-button-active' );
		var target = '.' + $( this ).data( 'umdb-target');
		$(this).siblings( '.um-db-button-tab' ).removeClass( 'um-db-button-active' );
		$(this).parent().find( '.um-db-content:not('+target+')' ).hide();
		$(this).parent().find( target ).fadeToggle();
	});



	//Run General Diagnostics

	var umd = '<div id="ubermenu-diagnostics">';

		umd+= '<h3 class="ubermenu-diagnostics-toggle"><i class="fa fa-stethoscope"></i> UberMenu Diagnostics (Alpha)</h3>';

		umd+= '<div class="umd-inner">';

			//Version
			umd+= umd_notice( 'UberMenu v' + ubermenu_data.v );

			//Check for .ubermenu-nav and then parent item ?
			var $ubermenus = $( '.ubermenu' );
			if( $ubermenus.size() === 0 ){
				umd+= umd_error( 'No UberMenu instances detected' , 'Be sure to follow the integration instructions in the Knowledgebase to add UberMenu to your site.' );
			}
			else{
				umd+= umd_notice( $ubermenus.size() + ' UberMenu instance(s) found' );
			}

			//jQuery Version
			var jqversion = jQuery.fn.jquery;

			if( versionCompare( '1.11' , jqversion ) === -1 ){
				umd+= umd_notice( 'jQuery ' + jqversion );
			}
			else{
				umd+= umd_error( 'jQuery: ' + jqversion , 'It looks like you are loading an old version of jQuery, which may not be compatible with UberMenu' );
			}

			//Theme
			$( 'link[rel="stylesheet"][href*="wp-content/themes"][href*="style.css"]' ).each( function(){
				var url = $(this).attr( 'href' );

				var parser = document.createElement('a');
				parser.href = url;

				if( parser.hostname == window.location.hostname ){

					jQuery.get( url , function(data) {

						data = data.substr( data.indexOf( '/*' ) + 2 , data.indexOf( '*/' ) );
						var lines = data.split("\n");
						var theme, theme_url, author, author_url, theme_version;

						for( var i = 0, len = lines.length; i < len; i++ ){
							var line = lines[i];

							var k;
							if( line.indexOf( 'Theme Name:' ) >= 0 ){
								theme = line.substr( 11 ).trim();
							}
							if( line.indexOf( 'Theme URI:' ) >= 0 ){
								theme_url = line.substr( 10 ).trim();
							}
							if( line.indexOf( 'Author:' ) >= 0 ){
								author = line.substr( 7 ).trim();
							}
							if( line.indexOf( 'Author URI:' ) >= 0 ){
								author_url = line.substr( 11 ).trim();
							}
							if( line.indexOf( 'Version:' ) >= 0 ){
								theme_version = line.substr( 8 ).trim();
							}
						}
						var theme_info = '<a href="'+theme_url+'" target="_blank">'+theme+' v.'+theme_version+'</a>  by <a href="'+author_url+'" target="_blank">'+author+'</a>';
						//umd+= umd_notice( theme_info );
						$( '.umd-inner' ).append( umd_notice( theme_info ) );

					});
				}
			});

			//Check for CSS files (may be cached)
			var $ubermenu_css = $( 'link[rel="stylesheet"][href*="/ubermenu.min.css"]' );
			if( $ubermenu_css.size() === 0 ){
				umd+= umd_warning( 'UberMenu core stylesheet missing' , 'Could not find ubermenu.min.css.  This may simply mean that your site has concatenated & minified this file with others.  Without this file present, UberMenu cannot operate.' );
			}
			else{
				umd+= umd_success( 'UberMenu core stylesheet loaded' , 'The ubermenu.min.css stylesheet was linked in the page source' );
			}

			

			//Skins
			var $skins = $( 'link[rel="stylesheet"][href*="ubermenu/assets/css/skins/"], link[rel="stylesheet"][href*="ubermenu/pro/assets/css/skins/"]' );
			if( $skins.size() === 0 ){
				umd+= umd_warning( 'No skins loaded' , 'No UberMenu skin found.  It may be concatenated with other files if you are using a minifier.  Otherwise, be sure to provide a complete skin for your menu via custom.css or the Customizer.' );
			}
			else{
				$skins.each( function(){
					var href = $(this).attr( 'href' );
					var skin_name = href.substr( href.indexOf( '/skins/' ) + 7 );
					var qmark = skin_name.indexOf( '?' );
					if( qmark > 0 ) skin_name = skin_name.substr( 0 , qmark );
					umd+= umd_success( 'Skin: ' + skin_name );
				});
			}

			//Check for javascript
			if( $('script[src*="ubermenu.min.js"], script[src*="ubermenu.js"]').size() > 0 ){
				umd+= umd_success( 'UberMenu javascript included' );
			}
			else umd+= umd_warning( 'UberMenu javascript not found' , 'This could mean that it is concatenated with other file via a minifier, disabled in the Control Panel Assets screen, or that your theme is missing the wp_footer() hook.  This script is required for UberMenu to run.' );

			//Javascript Ran?
			if( $ubermenus.size() ){
				if( $( '.ubermenu' ).hasClass( 'ubermenu-nojs' ) ){
					umd+= umd_error( 'UberMenu javascript did not run' , 'Your ubermenu.min.js file may not be loaded (check that your theme includes the wp_footer() hook), or you may have an unrelated javascript error preventing the UberMenu script from being able to run.' );
				}
				else{
					umd+= umd_success( 'UberMenu javascript initialized' );
				}
			}

			//Custom.css
			if( $( 'link[rel="stylesheet"][href*="ubermenu/custom/custom.css"]' ).size() > 0 ){
				umd+= umd_notice( 'Loaded custom.css' , 'This means you have enabled the custom.css asset in the Control Panel.  Make sure you have created the file in ubermenu/custom/custom.css.' );
			}

			//Google Maps
			var num_maps = $( 'script[src*="maps.googleapis.com/maps/api/js?"]' ).size();
			//console.log( 'maps = ' + num_maps );
			switch( num_maps ){
				case 0 :
					umd+= umd_warning( 'No Google Maps API found' , 'Enable the Google Maps API in the Control Panel > General > Assets to use the Google Maps Shortcode' );
					break;
				case 1 :
					umd+= umd_notice( 'Google Maps API loaded' , 'If you don\'t want to use a map in your menu, you can disable this in the UberMenu Control Panel > General > Assets' );
					break;
				default:
					umd+= umd_error( 'Multiple Google Maps APIs Loaded' , 'You should only load the Google Maps API once.  To disable UberMenu\'s, visit the Control Panel > General > Assets' );
			}

			//Check for Cache
			if( $( 'link[href*="wp-content/cache/minify"]' ).size() > 0 ){
				umd+= umd_warning( 'Page is cached' , 'If your changes are not appearing on the front end, or if something seems to be malfunctioning, try disabling caching and minification. ');
			}


			//Script Config

			umd+= umd_notice( 'Responsive Breakpoint: ' + ubermenu_data.responsive_breakpoint );
			umd+= umd_notice( 'Remove Javascript Conflicts: ' + ubermenu_data.remove_conflicts , 'You may need to disable this setting in the Control Panel if you want external javascript to work within UberMenu' );
			umd+= umd_notice( 'Reposition On Load: ' + ubermenu_data.reposition_on_load );
			umd+= umd_notice( 'Intent Delay: ' + ubermenu_data.intent_delay );
			umd+= umd_notice( 'Intent Threshold: ' + ubermenu_data.intent_threshold );
			umd+= umd_notice( 'ScrollTo Offset: ' + ubermenu_data.scrollto_offset );
			umd+= umd_notice( 'ScrollTo Duration: ' + ubermenu_data.scrollto_duration );
			umd+= umd_notice( 'Accessibility: ' + ubermenu_data.accessible );
			umd+= umd_notice( 'Retractor Display Strategy: ' + ubermenu_data.retractor_display_strategy );
			umd+= umd_notice( 'Touch off Close: ' + ubermenu_data.touch_off_close );
			


			// umd+= umd_notice( 'ubermenu.css loaded' );
			// umd+= umd_warning( 'ubermenu.css loaded' );
			// umd+= umd_error( 'ubermenu.css loaded' );


			
			
		
		umd+= '</div>';


	umd+= '</div>';


	$( 'body' ).append( umd );

	$( '.ubermenu-diagnostics-toggle' ).on( 'click' , function(){
		$( '#ubermenu-diagnostics' ).toggleClass( 'ubermenu-diagnostics-collapse' );
	});


	function umd_notice( msg , explanation ){
		return umd_status( msg , 'notice' , explanation );
	}

	function umd_success( msg , explanation ){
		return umd_status( msg , 'success' , explanation );
	}

	function umd_warning( msg , explanation ){
		return umd_status( msg , 'warning' , explanation );
	}

	function umd_error( msg , explanation ){
		return umd_status( msg , 'error' , explanation );
	}

	function umd_status( msg , status , explanation ){
		var html = '<div class="umd-status umd-status-'+status+'">';

		var icon = '';
		switch( status ){
			case 'success':
				icon = 'fa fa-check-circle';
				break;
			case 'warning':
				icon = 'fa fa-warning';
				break;
			case 'error':
				icon = 'fa fa-minus-circle';
				break;
			case 'notice':
				icon = 'fa fa-info-circle';
				break;
		}

		html+= '<span class="umd-msg"><i class="'+icon+'"></i> '+ msg + '</span>';

		if( typeof explanation !== 'undefined' ){
			html+= '<span class="umd-status-explanation">'+ explanation + '</span>';
		}

		html+= '</div>';

		return html;
	}











	function build_diagnostics_box( $item , menu_item_id , settings ){

		//console.log( menu_item_id );
		//console.log( settings );

		var has_submenu = false,
			dbox = '',
			info = {

				'item_id'	: {
					'title'	: 'Menu Item ID',
					'val'	: '',
					'src'	: false
				},
				'submenu_type'	: {
					'title' : 'Submenu Type',
					'val'	: 'No submenu',
					'src'	: false,
				},
				'columns'	: {
					'title'	: 'Column Width',
					'val'	: '',
				},
				'current'	: {
					'title'	: 'Current Item',
					'val'	: 'No',
					'src'	: false
				},
				'mini_item' : {
					'title'	: 'Mini Item',
					'val'	: '',
				},
				'defaults'	: {
					'title'	: 'Settings',
					'src'	: false
				}

			},
			submenu = {

				'submenu_type_calc'	: {
					'title' : 'Submenu Type (Determined)',
					'val'	: '',
					'src'	: false
				},

				'submenu_type'	: {
					'title' : 'Submenu Type (Selected)',
					'val'	: ''
				},
				'submenu_column_default' : {
					'title' : 'Submenu Columns Default',
					'status': ( settings && settings.submenu_column_default != 'auto' ) ? 'notice' : '',
				},
				'submenu_column_autoclear' : {
					'title' : 'Auto Row',
					'status': ( settings && settings.submenu_column_autoclear == 'on' ) ? 'notice' : '',
					'msg'	: 'Auto Row means that if your submenu column default is 1/3, your 4th item will be forced to a new row.  If you intend to have your submenu columns be various sizes, disable this.'
				},

				'submenu_position' : {
					'title'	: 'Mega Submenu Position'
				},
				'flyout_submenu_position' : {
					'title'	: 'Flyout Submenu Position'
				},
				'submenu_width' : {
					'title'	: 'Submenu Width'
				},
				'submenu_min_width' : {
					'title' : 'Submenu Min Width'
				}


			},
			image = {
				'item_image': {
					'title'	: 'Selected Image',
				},
				'inherit_featured_image' : {
					'title'	: 'Inherit Featured Image'
				},
				'image_size': {
					'title'	: 'Image Size',
				},
				'image_dimensions' : {
					'title'	: 'Image Dimensions',
				},
				'image_width_custom' : {
					'title' : 'Image Width (Custom)'
				},
				'image_height_custom' : {
					'title'	: 'Image Height (Custom)',
				},
			};

		//Defaults
		if( settings && settings.hasOwnProperty( 'defaults' ) && settings.defaults === 1 ){
			info.defaults.val = 'Defaults';
			info.defaults.msg = 'No settings have been saved for this item.';
		}
		else{
			info.defaults.val = 'User defined';
		}

		//ID
		info.item_id.val = '#' + $item.attr( 'id' );

		//Submenu?
		if( $item.hasClass( 'ubermenu-has-submenu-drop' ) ){
			has_submenu = true;

			if( $item.hasClass( 'ubermenu-has-submenu-flyout' ) ){
				info.submenu_type.val = 'Flyout submenu';
				submenu.submenu_type_calc.val = 'Flyout submenu';
			}
			else if( $item.hasClass( 'ubermenu-has-submenu-mega' ) ){
				info.submenu_type.val = 'Mega submenu';
				submenu.submenu_type_calc.val = 'Mega submenu';
			}
		}

		//Current?
		if( $item.hasClass( 'ubermenu-current-menu-item' ) ){
			info.current.val = 'Current';
			info.current.status = 'notice';
		}

		//Mini item
		if( settings && settings.mini_item == 'on' ){
			if( has_submenu ){
				if( settings.disable_submenu_indicator != 'on' ){
					info.mini_item.status = 'warning';
					info.mini_item.msg = 'Using the Mini Item setting with an item with a submenu, without disabling the Submenu Indicator, is not recommended.';
				}
			}
		}

		dbox = '<div class="ubermenu-item-diagnostics-box">';

			//Tabs

				//Pin
				if( has_submenu ){
					dbox+= '<a title="Pin Submenu Open" class="um-db-button um-db-button-pin"><i class="fa fa-thumb-tack"></i></a>';
					dbox+= '<a title="Highlight Submenu Layout" class="um-db-button um-db-button-highlight"><i class="fa fa-columns"></i></a>';
				}

				//Overview - At a glance
				dbox+= '<a title="Overview" class="um-db-button um-db-button-tab" data-umdb-target="um-db-content-overview"><i class="fa fa-sliders"></i></a>';

				//Submenu
				if( has_submenu ){
					dbox+= '<a title="Submenu" class="um-db-button um-db-button-tab" data-umdb-target="um-db-content-submenu"><i class="fa fa-chevron-down"></i></a>';
				}

				//Image
				//dbox+= '<a class="um-db-button um-db-button-tab" data-umdb-target="um-db-content-image"><i class="fa fa-picture-o"></i></a>';

			//Content
			
				//Overview
				dbox+= build_info_panel( 'overview' , "Overview" , info , settings );

				//Submenu
				if( has_submenu ){
					dbox+= build_info_panel( 'submenu' , "Submenu" , submenu , settings );
				}

				//dbox+= build_info_panel( 'image' , "Image" , image , settings );
				

		dbox+= '</div>';

		$item.append( dbox );
	}


	function build_info_panel( id , panel_title , data , settings ){
		var dbox = '';
		dbox+= '<div class="um-db-content um-db-content-'+id+'">';
		dbox+= '<h4 class="um-db-panel-title">' + panel_title + '</h4>';

		var val, status, msg, title;
		for( var key in data ){
			status = 'normal';
			val = '';
			msg = '';
			title = data[key]['title'];
			if( data.hasOwnProperty( key ) ){

				if( data[key].hasOwnProperty( 'src' ) && data[key]['src'] === false ){
					//Determined dynamically
					val = data[key]['val'];
				}
				else{
					//Get from settings data
					//console.log( key + ' :: ' + settings[key] );
					if( settings ){
						val = settings[key];
					}
					else val = 'Diagnostics Disabled';
				}

				if( data[key].hasOwnProperty( 'status' ) ){
					status = data[key]['status'];
				}

				if( data[key].hasOwnProperty( 'msg' ) ){
					msg = data[key]['msg'];
				}

				if( status == 'warning' ){
					title = '<i class="fa fa-warning"></i> ' + title;
				}

				dbox+= '<div class="um-db-status um-db-status-'+status+'">';
					dbox+= '<span class="um-db-status-title">'+title+'</span>';
					dbox+= '<span class="um-db-status-value">'+val+'</span>';
					if( msg ){
						dbox+= '<span class="um-db-status-msg">'+msg+'</span>';
					}
				dbox+= '</div>';
				//console.log( data[key]['title'] + ' ' + data[key]['val'] );
			}
		}
		dbox+= '</div>';

		return dbox;
	}






	function versionCompare(left, right) {
		if (typeof left + typeof right != 'stringstring')
			return false;
		
		var a = left.split('.'),
			b = right.split('.'),
			i = 0,
			len = Math.max(a.length, b.length);
		
		for (; i < len; i++) {
			if ((a[i] && !b[i] && parseInt(a[i]) > 0) || (parseInt(a[i]) > parseInt(b[i]))) {
				return 1;
			} else if ((b[i] && !a[i] && parseInt(b[i]) > 0) || (parseInt(a[i]) < parseInt(b[i]))) {
				return -1;
			}
		}
	
		return 0;
	}


})(jQuery);