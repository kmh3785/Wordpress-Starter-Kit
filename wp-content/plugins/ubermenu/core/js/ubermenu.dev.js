/**
 * 
 * UberMenu JavaScript
 * 
 * @author Chris Mavricos, SevenSpark http://sevenspark.com
 * @version 2.3.2.2
 * Last Modified 2013-08-15
 * 
 */


var $ujq = jQuery;
var uberMenuWarning = false;

if( typeof uberMenuSettings != 'undefined' && uberMenuSettings.noconflict == 'on' ){
	//Settings may not be defined if using a caching program.
	$ujq = jQuery.noConflict();
}
else uberMenuWarning = true;

jQuery(document).ready(function($){

	//boolean-ify settings
	uberMenuSettings['removeConflicts'] = uberMenuSettings['removeConflicts'] == 'on' ? true : false;
	uberMenuSettings['noconflict'] = uberMenuSettings['noconflict'] == 'on' ? true : false;
	uberMenuSettings['autoAlign'] = uberMenuSettings['autoAlign'] == 'on' ? true : false;
	uberMenuSettings['fullWidthSubs'] = uberMenuSettings['fullWidthSubs'] == 'on' ? true : false;
	uberMenuSettings['androidClick'] = uberMenuSettings['androidClick'] == 'on' ? true : false;
	uberMenuSettings['iOScloseButton'] = uberMenuSettings['iOScloseButton'] == 'on' ? true : false;
	uberMenuSettings['loadGoogleMaps'] = uberMenuSettings['loadGoogleMaps'] == 'on' ? true : false;
	uberMenuSettings['repositionOnLoad'] = uberMenuSettings['repositionOnLoad'] == 'on' ? true : false;
	uberMenuSettings['hoverInterval'] = parseInt( uberMenuSettings['hoverInterval'] );
	uberMenuSettings['hoverTimeout'] = parseInt( uberMenuSettings['hoverTimeout'] );
	uberMenuSettings['speed'] = parseInt( uberMenuSettings['speed'] );

	//If we were supposed to run in noConflict mode, but didn't because the variable wasn't set to begin with, alert the user
	if( uberMenuWarning && uberMenuSettings['noconflict'] && typeof console != 'undefined' ){
		console.log('[UberMenu Notice] Not running in noConflict mode.  Are you using a caching plugin?  If so, you need to load the UberMenu scripts in the footer.');
	}
	
	var deviceAgent = navigator.userAgent.toLowerCase();

	//If this is Android, and we're using click for android, swap the trigger
	if( uberMenuSettings['androidClick'] ){
		if( deviceAgent.match(/(android)/) ){
			uberMenuSettings['trigger'] = 'click';
		}
	}

	//Windows 8 Temporary Measures
	if( window.navigator.msMaxTouchPoints ){
		uberMenuSettings['trigger'] = 'click';
	}

	//For iOS, set to hover for consistency
	var is_iOS = deviceAgent.match(/(iphone|ipod|ipad)/);
	if( is_iOS ){
		uberMenuSettings['trigger'] = 'hover';
	}
	
	//Client Side	
	var $menu = $( '#megaMenu' );
	if( $menu.size() == 0 ) return;
	
	$menu.uberMenu( uberMenuSettings );
	var $um = $menu.data( 'uberMenu' );
	
	//Google Maps
	if( /* uberMenuSettings['loadGoogleMaps'] && //don't check this so it can run even when API is loaded elsewhere */
	   typeof google !== 'undefined' &&
	   typeof google.maps !== 'undefined' &&
	   typeof google.maps.LatLng !== 'undefined') {
		$('.spark-map-canvas').each(function(){
			
			var $canvas = $(this);
			var dataZoom = $canvas.attr('data-zoom') ? parseInt($canvas.attr('data-zoom')) : 8;
			
			var latlng = $canvas.attr('data-lat') ? 
							new google.maps.LatLng($canvas.attr('data-lat'), $canvas.attr('data-lng')) :
							new google.maps.LatLng(40.7143528, -74.0059731);
					
			var myOptions = {
				zoom: dataZoom,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				center: latlng
			};
					
			var map = new google.maps.Map(this, myOptions);
			
			if($canvas.attr('data-address')){
				var geocoder = new google.maps.Geocoder();
				geocoder.geocode({ 
						'address' : $canvas.attr('data-address') 
					},
					function(results, status) {					
						if (status == google.maps.GeocoderStatus.OK) {
							map.setCenter(results[0].geometry.location);
							latlng = results[0].geometry.location;
							var marker = new google.maps.Marker({
								map: map,
								position: results[0].geometry.location,
								title: $canvas.attr('data-mapTitle')
							});
						}
				});
			}
			else{
				//place marker for regular lat/long
				var marker = new google.maps.Marker({
					map: map,
					position: latlng,
					title: $canvas.attr('data-mapTitle')
				});
			}
			
			var $li = $(this).parents( 'li.ss-nav-menu-item-depth-0' );
			var mapHandler = function(){
				google.maps.event.trigger(map, "resize");
				map.setCenter(latlng);
				//Only resize the first time we open
				$li.unbind( 'ubermenuopen', mapHandler );
			}			
			$li.bind( 'ubermenuopen', mapHandler );
		});
	}

	//Redraw submenus after fonts have loaded
	if( uberMenuSettings['repositionOnLoad'] ){
		jQuery( window ).load( function(){
			uberMenu_redrawSubmenus();
		});
	}

	var $menu_ul = $menu.find( 'ul.megaMenu' );
	$( '#megaMenuToggle' ).click( function(){
		$menu_ul.slideToggle( function(){
			$menu_ul.css( 'overflow' , 'visible' );
			$menu_ul.toggleClass( 'megaMenuToggleOpen' );
		});
	});
});


;(function($) {

	$.uberMenu = function(el, options) {

		var defaults = {
			
			 speed			: 300
			,trigger		: 'hover'		//hover, hoverInterval, click
			,orientation 	: 'horizontal'	//horizontal, vertical
			,transition		: 'slide'		//slide, fade, none
			
			,hoverInterval	: 100
			,hoverTimeout	: 400
			,removeConflicts: true
			,autoAlign		: false
			
			//,maxSubmenuWidth: false
			,fullWidthSubs	: false
			
			,onOpen			: function(){}
		}
		
		var plugin = this;
		plugin.settings = {}

		var init = function() {
						
			plugin.settings = $.extend({}, defaults, options);
			//console.log(plugin.settings);
			plugin.el = el;
			plugin.$megaMenu = $(el);
			
			
			//Remove Conflicts - remove events and styles that might be added by the theme, as long as "Remove Conflicts" is not deactivated
			if( plugin.$megaMenu.hasClass( 'wpmega-noconflict' ) ){
				//$('#megaMenu.wpmega-noconflict ul, #megaMenu.wpmega-noconflict ul li, #megaMenu.wpmega-noconflict ul li a')
				plugin.$megaMenu.find( 'ul, ul li.menu-item, ul li.menu-item > a' ).removeAttr('style').unbind().off();
			}
					
			
			//Remove 'nojs'
			plugin.$megaMenu.removeClass('megaMenu-nojs').addClass('megaMenu-withjs');
			
			//Setup menus w/ subs - no longer needed, done in PHP 
			//$('#megaMenu > ul > li:has(ul)').addClass('mega-with-sub');			
			
			//Setup flyout menus w/ subs
			$('#megaMenu li.ss-nav-menu-reg li:has(> ul)').addClass('megaReg-with-sub');
			
						
			//Mega Menus
			var $megaItems = plugin.$megaMenu.find( 'ul.megaMenu > li.ss-nav-menu-mega.mega-with-sub' );
			
			//Setup Positioning
			if( !plugin.settings.fullWidthSubs ){
				positionMegaMenus( $megaItems , true );
				$( window ).resize( function(){
					positionMegaMenus( $megaItems , false );	//reposition but don't re-align
				});
			}
			else{
				$megaItems.find( '> ul.sub-menu-1' ).hide();
			}
			
			switch( plugin.settings.trigger ){
				
				//Setup click items
				case 'click':
					$megaItems.find( '> a, > span.um-anchoremulator' )
						.click( 
							function(e){
								
								var $li = $(this).parent('li');
							
								//Normal Links
								//if( $li.has('ul.sub-menu').size() == 0 ){ return true; };

								//Mega Drops
								e.preventDefault();	//No clicking allowed
								if( $li.hasClass( 'wpmega-expanded' ) ){
									$li.removeClass( 'wpmega-expanded' );
									closeSubMenu( $li.get(0) , false );
								}
								else{
									$li.addClass( 'wpmega-expanded' );
									showMega( $li.get(0) );
								}
								
							});

					//Close when body is clicked
					$(document).click( function(e){
						closeAllSubmenus();				
					});
					//But not when the menu is clicked
					plugin.$megaMenu.click( function(e){
						e.stopPropagation();
					});
							
					break;
			
				//Setup hoverIntent items
				case 'hoverIntent':
					$megaItems
						.hoverIntent({
							
							over: function(){				
								showMega( this );
							}, 			
							out: function(e){
								if(typeof e === 'object' && $(e.fromElement).is('#megaMenu form, #megaMenu input, #megaMenu select, #megaMenu textarea, #megaMenu label')){
									return; //Chrome has difficulty with Form element hovers
								}
								closeSubMenu( this , false);
							},				
							timeout: plugin.settings.hoverTimeout,
							interval: plugin.settings.hoverInterval,
							sensitivity: 2
							
						});
				
					break;
			
				//Setup Hover items
				case 'hover':
					$megaItems
						.hover( 
							function(){
								showMega( this );							
							},
							function(e){
								if(typeof e === 'object' && $(e.fromElement).is('#megaMenu form, #megaMenu input, #megaMenu select, #megaMenu textarea, #megaMenu label')){
									return; //Chrome has difficulty with Form element hovers
								}
								closeSubMenu( this );
							});
							
					break;
			
			}
			
			//Flyout Menus
			var $flyItems = plugin.$megaMenu.find( 'ul.megaMenu > li.ss-nav-menu-reg.mega-with-sub, li.ss-nav-menu-reg li.megaReg-with-sub' );
			$flyItems.find( 'ul.sub-menu' ).hide();
			switch( plugin.settings.trigger ){
				
				//Setup click items
				case 'click':
					$flyItems.find( '> a, > span.um-anchoremulator' )
						.click( 
							function(e){
								
								var $li = $(this).parent('li');
																	
								//Flyouts
								e.preventDefault();	//No clicking allowed
								e.stopPropagation();
								if( $li.hasClass( 'wpmega-expanded' ) ){
									$li.removeClass( 'wpmega-expanded' );
									closeSubMenu( $li.get(0) );
								}
								else{
									$li.addClass( 'wpmega-expanded' );
									showFlyout( $li.get(0) );
								}
								
							});
					break;
						
				//Setup HoverIntent items
				case 'hoverIntent':
					$flyItems
						.hoverIntent({
							
							over: function(){				
								showFlyout( this );
							}, 			
							out: function(e){
								if(typeof e === 'object' && $(e.fromElement).is('#megaMenu form, #megaMenu input, #megaMenu select, #megaMenu textarea, #megaMenu label')){
									return; //Chrome has difficulty with Form element hovers
								}
								closeSubMenu( this , false);
							},				
							timeout: plugin.settings.hoverTimeout,
							interval: plugin.settings.hoverInterval,
							sensitivity: 2
							
						});
				
					break;
				
				//Setup hover items
				case 'hover':
				
					$flyItems.hover(
						function(){
							showFlyout( this );
						},
						function(){
							closeSubMenu( this );
						}
					);
					break;
				
			}
						
			//Mobile - iOS
			var deviceAgent = navigator.userAgent.toLowerCase();
			var is_iOS = deviceAgent.match(/(iphone|ipod|ipad)/);
	
			if( uberMenuSettings['iOScloseButton'] && ( jQuery.uber_mobile || is_iOS ) ){
			
				var $navClose = $( '<span class="uber-close">&times;</span>' );
				$navClose.appendTo( '#megaMenu li.mega-with-sub > a, #megaMenu li.mega-with-sub > span.um-anchoremulator' );
				plugin.$megaMenu.on( 'click' , '.uber-close' , function(e){
					e.preventDefault();
					if( $( this ).attr( 'data-uber-status' ) == 'open' ){
						closeSubMenu( $(this).parents( 'li.mega-with-sub' )[0] , true );
						$( this ).html( '&darr;' ).attr( 'data-uber-status' , 'closed' );
					}
					else{
						showMega( $(this).parents( 'li.mega-with-sub' )[0] );
						$( this ).html( '&times;' ).attr( 'data-uber-status' , 'open' );
					}
					$(this).show(); //make sure we keep showing
					return false;
				});

				plugin.$megaMenu.find('ul.megaMenu > li.mega-with-sub').hover(function(e){
					e.preventDefault();
					$( this ).find( '.uber-close' ).html( '&times;' ).attr( 'data-uber-status' , 'open' ).show();					
				}, function(e){
					e.preventDefault();
					$( this ).find( '.uber-close' ).hide();
				});
					  
			}
			
		}
		
		var positionMegaMenus = function( $megaItems , runAlignment ){
			
			plugin.menuEdge = plugin.settings.orientation == 'vertical' 
								? plugin.$megaMenu.find('> ul.megaMenu').offset().top 
								: plugin.$megaMenu.find('> ul.megaMenu').offset().left;
			var menuBarWidth = plugin.$megaMenu.find('> ul.megaMenu').outerWidth();
			var menuBarHeight = plugin.$megaMenu.find('> ul.megaMenu').outerHeight();
			
			$megaItems.each( function() {
				
				var $li = $(this);
				var isOpen = $li.hasClass('megaHover');
				
				//Find submenu
				var $sub = $li.find( '> ul.sub-menu-1' );
								
				//AutoAlign
				if( runAlignment && plugin.settings.autoAlign ){
					var $subItems = $sub.find('li.ss-nav-menu-item-depth-1:not(.ss-sidebar)');	//subitems that aren't widget areas
					var maxColW = 0;
					$sub.css('left', '-999em').show();	//remove from view to inspect size
					$subItems.each(function(){
						if( $(this).width() > maxColW ) maxColW = $(this).width();
						//console.log( 'maxColW = ' + $(this).width() );
					});	
					$subItems.width( maxColW );
					$sub.css( 'left', '' );
				}
				
				//Position centered submenus that are non-full-width
				switch( plugin.settings.orientation ){
					
					case 'horizontal':
					
						if( $(this).hasClass( 'ss-nav-menu-mega-alignCenter' ) &&
							!$(this).hasClass( 'ss-nav-menu-mega-fullWidth' ) ){
								
							var topWidth = $(this).outerWidth();
							var subWidth = $sub.outerWidth();
							
							var centerLeft = ( $(this).offset().left + ( topWidth / 2 ) )
										- ( plugin.menuEdge + ( subWidth / 2 ) );
							
							
							//If submenu is left of menuEdge
							var left = centerLeft > 0 ? centerLeft : 0;
							
							//If submenu is right of menuEdge
							if( left + subWidth > menuBarWidth ){
								//console.log( menuBarWidth + ' - ' + subWidth );
								left = menuBarWidth - subWidth;
							} 
							
							
							$sub.css({						
								left	: left
							});
						}
						break;
						
					case 'vertical':
					
						if( $(this).hasClass( 'ss-nav-menu-mega-alignCenter' ) ){
							
							var topHeight = $(this).outerHeight();
							var subHeight = $sub.outerHeight();
							
							var centerTop = ( $(this).offset().top + ( topHeight / 2 ) )
										- ( plugin.menuEdge + ( subHeight / 2 ) );
							
							
							//If submenu is above menuEdge
							var top = centerTop > 0 ? centerTop : 0;
							
							//If submenu is below of menuEdge
							if( top + subHeight > menuBarHeight ){
								left = menuBarHeight - subHeight;
							} 
														
							$sub.css({						
								top	: top
							});
							
						}
					
						break;
						
				}
								
				//Hide the submenu
				if( !isOpen ) $sub.hide();
			});
			
		}
		
		//Private Methods
		var showMega = function( li ){
			
			var $li = $(li);
			
			closeAllSubmenus( $li );
			
			$li.addClass('megaHover wpmega-expanded');	//2.3

			var $subMenu = $li.find('ul.sub-menu-1');
						
			switch( plugin.settings.transition ){
				
				case 'slide':
					$subMenu.stop( true, true ).slideDown( plugin.settings.speed , 'swing' , function(){
						$li.trigger('ubermenuopen');
					} );
					//$subMenu.animate( { width: 'toggle' });
					break;
				
				case 'fade':
					$subMenu.stop( true, true ).fadeIn( plugin.settings.speed , 'swing' , function(){
						$li.trigger('ubermenuopen');
					} );
					break;
					
				case 'none':
					$subMenu.show();
					$li.trigger('ubermenuopen');
					break;
					
			}
			
		}
		
		var showFlyout = function( li ){
			
			var $li = $(li);
			if( !$li.has('ul.sub-menu') ) return;
			
			//Top Level
			if( $li.hasClass( 'ss-nav-menu-reg' ) ) closeAllSubmenus( $li );
			//Sub Level
			else $li.siblings().each( function(){ closeSubMenu( this , true) } );	//auto close all siblings' sub-menus
			
			
			$li.addClass( 'megaHover' );

			var $subMenu = $li.find( '> ul.sub-menu' );
			
			
			switch( plugin.settings.transition ){
				
				case 'slide':
					$subMenu.stop( true, true ).slideDown( plugin.settings.speed , 'swing' , function(){
						$li.trigger('ubermenuopen');
						$subMenu.css( 'overflow' , 'visible' );
					} );
					break;
				
				case 'fade':
					$subMenu.stop( true, true ).fadeIn( plugin.settings.speed , 'swing' , function(){
						$li.trigger('ubermenuopen');
					} );
					break;
					
				case 'none':
					$subMenu.show();
					$li.trigger('ubermenuopen');
					break;
					
			}

			//$subMenu.css( 'overflow' , 'visible' );
			
		}
		
		var closeSubMenu = function( li , immediate ){
			
			var $li = $(li);
			
			var $subMenu = $li.find('> ul.sub-menu');
	
			if( immediate ){
				$subMenu.hide();
				$li.removeClass('megaHover').removeClass('wpmega-expanded');
				return;
			}
			
			if($subMenu.size() > 0){
								
				switch( plugin.settings.transition ){
				
					case 'slide':					
						$subMenu.stop( true, true ).slideUp( plugin.settings.speed , function(){
							$li.removeClass('megaHover').removeClass('wpmega-expanded');
							$li.trigger('ubermenuclose');
						});
						break;
						
					case 'fade':
					
						$subMenu.stop( true, true ).fadeOut( plugin.settings.speed , function(){
							$li.removeClass('megaHover').removeClass('wpmega-expanded');
							$li.trigger('ubermenuclose');
						});
						break;
						
					case 'none':
						$subMenu.hide();
						$li.removeClass('megaHover').removeClass('wpmega-expanded');
						$li.trigger('ubermenuclose');
						break;
						
					
				}
				
			}
			else $li.removeClass('megaHover').removeClass('wpmega-expanded');
			
		}
		
		var closeAllSubmenus = function( $not ){
			
			var $topItems = plugin.$megaMenu.find( '> ul.megaMenu > li' );
			
			if( $not != null ){
				$topItems = $topItems.not( $not );
			}
			
			$topItems
				.removeClass('megaHover').removeClass('wpmega-expanded')
				.find( '> ul.sub-menu' ).hide();
			
		}
		
		
		//Public Methods
		plugin.openMega = function( id ){
			showMega( id );
		}
		
		plugin.openFlyout = function( id ){
			showFlyout( id );
		}
		
		plugin.close = function( id , immediate ){
			if( !immediate ) immediate = false;
			closeSubMenu( id , immediate );
		}

		plugin.redrawSubmenus = function(){
			//Mega Menus
			var $megaItems = plugin.$megaMenu.find( 'ul.megaMenu > li.ss-nav-menu-mega.mega-with-sub' );
			
			//Setup Positioning
			if( !plugin.settings.fullWidthSubs ){
				positionMegaMenus( $megaItems , true );
			}
		}
		
		
		//Initialize
		init();
		
	}
	
	$.fn.uberMenu = function(options) {

		return this.each(function() {
			if ( undefined == $(this).data( 'uberMenu' ) ){
				var uberMenu = new $.uberMenu( this, options );
				$( this ).data( 'uberMenu', uberMenu );
			}
		});

	}


})( jQuery );


/* 
 * API Functions
 * Pass the top level menu item ID to control the submenu 
 */
function uberMenu_openMega( id ){
	var $uber = $ujq('#megaMenu').data( 'uberMenu' );
	$uber.openMega( id );
}

function uberMenu_openFlyout( id ){
	var $uber = $ujq('#megaMenu').data( 'uberMenu' );
	$uber.openFlyout( id );
}

function uberMenu_close( id ){
	var $uber = $ujq('#megaMenu').data( 'uberMenu' );
	$uber.close( id );
}

function uberMenu_redrawSubmenus(){
	var $uber = $ujq('#megaMenu').data( 'uberMenu' );
	$uber.redrawSubmenus();
}


/**
 * jQuery.browser.uber_mobile (http://detectmobilebrowser.com/)
 *
 * jQuery.browser.uber_mobile will be true if the browser is a mobile device
 *
 **/
(function(a){jQuery.uber_mobile=/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))})(navigator.userAgent||navigator.vendor||window.opera);
