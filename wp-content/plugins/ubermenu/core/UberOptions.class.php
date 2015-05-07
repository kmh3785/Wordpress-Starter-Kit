<?php

class UberOptions extends UberSparkOptions{
	
	
	function __construct( $id, $config = array() , $links = array() , $baseURL ){
		
		parent::__construct( $id, $config , $links , $baseURL );
		
		$this->options_key = self::generateOptionsKey( $this->id );
	}
	
	
	public static function generateOptionsKey( $id ){
		return $id;
	}
	
	public function previewButton(){
		global $uberMenu;
		return '<input type="submit" value="Preview" name="ubermenu-preview-button" id="ubermenu-preview-button" class="button reset-button" />'.
				'<div id="ubermenu-style-preview"></div>'.
				'<input type="submit" value="'.__( 'Show/Hide CSS' , 'ubermenu' ).'" name="ubermenu-style-viewer-button" id="ubermenu-style-viewer-button" class="button reset-button" />'.
				'<div id="ubermenu-style-viewer"><textarea disabled>'.$uberMenu->getGeneratorCSS().'</textarea></div>';
		
	}

	public function ubermenu_pro_upgrade(){
		
		?>

		<div class="spark">
			<?php _e( 'Your theme includes the lite version of UberMenu, which allows you to create awesome basic mega menus.  Upgrade to the full UberMenu MegaMenu Plugin to get even more advanced features, like images, widgets, shortcodes, and more!' , 'ubermenu' ); ?>
		</div>

		<div class="spark-action-button">
			<a href="http://wpmegamenu.com" target="_blank" class=""><?php _e( 'Learn More' , 'ubermenu' ); ?> <i class="icon-chevron-right"></i></a>
		</div>
		<br/>

		<table class="ss-table-compare">
			<tr>
				<th></th>
				<th><?php _e( 'UberMenu Lite' , 'ubermenu' ); ?>
					<span class="desc"><?php _e( 'Included with theme' , 'ubermenu' ); ?></span>
				</th>
				<th><?php _e( 'UberMenu' , 'ubermenu' ); ?>
					<span class="desc"><?php _e( 'Full plugin upgrade' , 'ubermenu' ); ?></span>
				</th>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Click or Hover Trigger' , 'ubermenu' ); ?></td>
				<td><i class="icon-ok"></i></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Slide or Fade Effects' , 'ubermenu' ); ?></td>
				<td><i class="icon-ok"></i></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Responsive' , 'ubermenu' ); ?></td>
				<td><i class="icon-ok"></i></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php __( 'Flyout Menus' , 'ubermenu' ); ?></td>
				<td><i class="icon-ok"></i></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Mega Menus' , 'ubermenu' ); ?></td>
				<td><i class="icon-ok"></i></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Descriptions' , 'ubermenu' ); ?></td>
				<td><i class="icon-ok"></i></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Images' , 'ubermenu' ); ?></td>
				<td></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Widgets' , 'ubermenu' ); ?>
					<span class="desc"><?php _e( 'Add widgets to your menu' , 'ubermenu' ); ?></span></td>
				<td></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Custom Content' , 'ubermenu' ); ?>
					<span class="desc"><?php _e( 'Add any custom HTML or text content to your menu' , 'ubermenu' ); ?></span></td>
				<td></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Google Maps' , 'ubermenu' ); ?>
					<span class="desc"><?php _e( 'Easily add Google Maps to your menu with a shortcode' , 'ubermenu' ); ?></span></td>
				<td></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Recent Posts' , 'ubermenu' ); ?>
					<span class="desc"><?php _e( 'Display Recent Posts in your menu with a shortcode' , 'ubermenu' ); ?></span></td>
				<td></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Contact Forms &amp; Shortcodes' , 'ubermenu' ); ?>
					<span class="desc"><?php _e( 'Display a Contact Form 7 form or any shortcode in your menu' , 'ubermenu' ); ?></span></td>
				<td></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( '20+ Preset Styles' , 'ubermenu' ); ?>
					<span class="desc"><?php _e( 'Choose from over 20 preset styles' , 'ubermenu' ); ?></span></td>
				<td></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Style Generator' , 'ubermenu' ); ?>
					<span class="desc"><?php _e( 'Create your own skins with color pickers' , 'ubermenu' ); ?></span></td>
				<td></td>
				<td><i class="icon-ok"></i></td>
			</tr>
			<tr>
				<td class="ss-feature"><?php _e( 'Compatible with UberMenu Extensions' , 'ubermenu' ); ?>
					<span class="desc"><?php _e( 'Extend the functionality of your menu with great extensions like <a href="http://goo.gl/0LrTj">Conditionals</a> and <a href="http://goo.gl/VOnRK">Sticky</a>' , 'ubermenu' ); ?></span></td>
				<td></td>
				<td><i class="icon-ok"></i></td>
			</tr>
		</table>


		<div class="spark-action-button">
			<a href="http://wpmegamenu.com" target="_blank" class="">Get UberMenu <i class="icon-chevron-right"></i></a>
		</div>

		
		<div style="font-size:11px; color:#999; border-top:1px dotted #ccc; margin-top:80px;">
			<p><?php _e( 'If you would like to hide this panel, you can do so by adding the following code to your functions.php file: ', 'ubermenu' ); ?></p>

			<pre>define( 'UBERMENU_UPGRADE' , false );</pre>
		</small>
		<?php
	}

	function ubermenu_update_notifier() { 
		$xml = ubermenu_get_latest_plugin_version( UBERMENU_UPDATE_DELAY );
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/ubermenu/ubermenu.php' ); // Get plugin data (current version is what we want)
		?>

		<div class="wrap">
		
			<div id="icon-tools" class="icon32"></div>
			<div id="message" class="updated below-h2"><p><strong><?php _e( 'There is a new version of UberMenu available.', 'ubermenu' ); ?></strong> You have version <?php echo $plugin_data['Version']; ?> installed. Update to version <?php echo $xml->latest; ?>.</p></div>
			<br/>
			<a href="http://sevenspark.com/out/ubermenu-update-instructions" class="button" target="_blank">Update Instructions</a>
			<a href="http://codecanyon.net/downloads" class="button" target="_blank">Download update from CodeCanyon</a>
			<br/>
			<div class="uber_clear umclear"></div>
			
			<h3 class="title"><?php _e( 'Changelog', 'ubermenu' ); ?></h3>
			<?php echo $xml->changelog; ?>

		</div>
		
	<?php } 


	function ubermenu_compatibility_report(){
		$theme_data = wp_get_theme();

		$data_array = array();

		$data_array['theme_name'] = $theme_data->Name;
		$data_array['theme_version'] = $theme_data->Version;
		$data_array['theme_author'] = $theme_data->get( 'Author' );
		$data_array['theme_author_uri'] = $theme_data->get( 'AuthorURI' );
		$data_array['theme_parent'] = $theme_data->get( 'Template' );

		$data_array['ubermenu_version'] = UBERMENU_VERSION;
		$data_array['wordpress_version'] = get_bloginfo( 'version' );

		$data_array['site_url'] = get_bloginfo( 'url' );
		$data_array['site_contact'] = get_bloginfo( 'admin_email' );


		$data = http_build_query( $data_array );

		$url = 'http://sevenspark.com/ubermenu-compatibility?'.$data;

		//umssd( $data_array );

		
		/*Theme: <?php echo $data_array['theme_name']; ?><br/>
		Version: <?php echo $data_array['theme_version']; ?><br/>
		Author: <?php echo $data_array['theme_author']; ?><br/>
		Author URI: <?php echo $data_array['theme_author_uri']; ?><br/>
		*/
	
		?>

		<p><?php _e( 'Clicking the button below will bring you to sevenspark.com and automatically complete the basic form fields for the compatibility report.  However, you can edit this information and only what you submit will be recorded.', 'ubermenu' ); ?></p>

		<a href="<?php echo $url; ?>" target="_blank" class="button save-button"><?php _e( 'Answer Compatibility Questions', 'ubermenu' ); ?></a>

		<br class="umclear"/><br class="umclear"/>
		
		<p class="umclear spark-infobox spark-infobox-warning"><strong>Please note that this report is for statistical purposes.  If you need assistance with a specific issue, please be sure to
			post your question in the <a href="http://sevenspark.com/support">Support Forum</a> in order to receive support, <em>prior to filling out the form</em>.</strong><br/>
			<br/>Don't forget to check out the Knowledgebase, FAQs, Troubleshooter, Video Tutorials, and Customization Assistant (resource links on the left)</p>

		<?php
	}

	function ubermenu_extensions(){
		?>
			<div class="social_media">
				<a target="_blank" href="https://twitter.com/#!/sevenspark" class="ss-twitter"></a> 
				<a target="_blank" href="http://www.facebook.com/sevensparklabs" class="ss-facebook"></a> 
				<a target="_blank" href="http://dribbble.com/sevenspark" class="ss-dribbble"></a>
			</div>



			<div class="um-half umcf">
				<h4>UberMenu - Icons Extension</h4>

				<a href="http://goo.gl/n3ZuMI" target="_blank"><img src="http://i.imgur.com/R6jF2N3.png" /></a>

				<p>The Icons Extension allows you to add Font Awesome Icons to your menu items</p>

				<a href="http://goo.gl/n3ZuMI" target="_blank" class="button save-button">Get 
				UberMenu Icons &rarr;</a>
			</div>
			

			

			<div class="um-half umcf">
				<h4>UberMenu - Sticky Menu Extension</h4>

				<a target="_blank" href="http://goo.gl/iwvg3a"><img src="http://i.imgur.com/VX3uX5U.png" alt="UberMenu Sticky Menu" /></a>

				<p>The Sticky Extension automatically turns your UberMenu into a Sticky Menu as your users scroll down the page.</p>

				<a href="http://goo.gl/iwvg3a" class="button save-button" target="_blank">Get UberMenu Sticky &rarr;</a>

			</div>

			<div class="um-half umcf">
				<h4>Menu Swapper</h4>

				<a href="http://wordpress.org/plugins/menu-swapper/" target="_blank"><img src="http://i.imgur.com/HtzdwfQ.png" /></a>

				<p>The Menu Swapper allows you to register custom theme locations and easily swap menus on individual Pages or Posts</p>

				<a href="http://wordpress.org/plugins/menu-swapper/" target="_blank" class="button save-button">Get 
				Menu Swapper &rarr;</a>
			</div>

			

			

			<div class="um-half umcf">
				<h4>UberMenu - Flat Skin Pack</h4>

				<a target="_blank" href="http://goo.gl/mwGFrO"><img src="http://2.s3.envato.com/files/56692616/FlatSkinPack_CodeCanyonPackaging.png" alt="UberMenu" /></a>

				<p>The Flat Skin Pack Extension gives you 30 gorgeous, out-of-the-box, flat-style skins for UberMenu. </p>

				<a href="http://goo.gl/mwGFrO" class="button save-button" target="_blank">Get the Flat Skin Pack &rarr;</a>

			</div>

			<div class="um-half umcf">
				<h4>Menu Management Enhancer</h4>

				<a href="http://goo.gl/ulA8J2" target="_blank"><img src="http://i.imgur.com/DNcqlYh.png" alt="Agility" /></a>
				<p>The WordPress Menu Management Enhancer makes managing large numbers of menu items in the Appearance > Menus screen a breeze</p>
				<a href="http://goo.gl/ulA8J2" class="button save-button" target="_blank">Get the Enhancer &rarr;</a>
			</div>

			<div class="um-half umcf">
				<h4>UberMenu - Conditionals Extension</h4>

				<a href="http://goo.gl/lbPmmE" target="_blank"><img src="http://i.imgur.com/y2X234g.png" alt="UberMenu Conditionals" /></a>

				<p>The Conditionals Extension allows you to display or hide individual menu items based on preset conditions.</p>

				<a href="http://goo.gl/lbPmmE" class="button save-button" target="_blank">Get UberMenu Conditionals &rarr;</a>

			</div>


			<div class="um-half umcf">
				<h4>Responsive Select Menu</h4>

				<a href="http://goo.gl/RFUAaa" target="_blank"><img src="http://i.imgur.com/ZNeNhrl.png" /></a>

				<p>The Responsive Select Menu plugin automatically turns any WordPress 3 Menu into a select box / dropdown at small sizes</p>

				<a href="http://wordpress.org/plugins/responsive-select-menu/" target="_blank" class="button save-button">Get 
				Responsive Select Menu &rarr;</a>
			</div>


			
		<?php
	}

	
}