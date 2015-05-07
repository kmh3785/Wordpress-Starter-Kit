<?php

function ubermenu_register_theme_customizers( $wp_customize ){

	ubermenu_define_custom_customizer_controls();

	$instances = ubermenu_get_menu_instances( true );
	foreach( $instances as $instance ){
		ubermenu_register_theme_customizer( $instance , $wp_customize );
	}

}

function ubermenu_register_theme_customizer( $config_id , $wp_customize ) {

	

	//$config_id = 'main';
	$prefixed_menu_id = UBERMENU_PREFIX.$config_id;


	//Add Section for Instance
	$section_id = $panel_id = 'ubermenu_instance_'.$config_id;

	// $wp_customize->add_panel( $section_id.'_panel', array(
	// 	'title'          => __( 'UberMenu Panel', 'ubermenu' ) . ' ['.$config_id.']',
	// 	'priority'       => 34,
	// ) );

	$use_panels = false;
	if( method_exists( $wp_customize , 'add_panel' ) ){
		$use_panels = true;
	}

	if( $use_panels ){

		//UberMenu Panel Wrap for instance
		$wp_customize->add_panel( $panel_id, array(
			'title'          => __( 'UberMenu', 'ubermenu' ) . ' ['.$config_id.']',
			'priority'       => 35,
		) );

		//Sub sections

		//General
		$section_id = $panel_id .'_general';
		$wp_customize->add_section( $section_id, array(
			'title'		=> __( 'General / Miscellaneous', 'ubermenu' ),
			'priority'	=> 5,
			'panel'		=> $panel_id,
		) );

		//Menu Bar
		$wp_customize->add_section( $panel_id.'_menu_bar', array(
			'title'		=> __( 'Menu Bar', 'ubermenu' ),
			'priority'	=> 10,
			'panel'		=> $panel_id,
		) );

		//Top Level Items
		$wp_customize->add_section( $panel_id.'_top_level_items', array(
			'title'		=> __( 'Top Level Items', 'ubermenu' ),
			'priority'	=> 20,
			'panel'		=> $panel_id,
		) );

		//Submenu
		$wp_customize->add_section( $panel_id.'_submenu', array(
			'title'		=> __( 'Submenu', 'ubermenu' ),
			'priority'	=> 30,
			'panel'		=> $panel_id,
		) );

		//Submenu Headers
		$wp_customize->add_section( $panel_id.'_headers', array(
			'title'		=> __( 'Submenu Headers', 'ubermenu' ),
			'priority'	=> 40,
			'panel'		=> $panel_id,
		) );

		//Normal Submenu Items
		$wp_customize->add_section( $panel_id.'_normal', array(
			'title'		=> __( 'Normal Items', 'ubermenu' ),
			'priority'	=> 50,
			'panel'		=> $panel_id,
		) );

		//Descriptions
		$wp_customize->add_section( $panel_id.'_descriptions', array(
			'title'		=> __( 'Descriptions', 'ubermenu' ),
			'priority'	=> 60,
			'panel'		=> $panel_id,
		) );

		//Arrows
		$wp_customize->add_section( $panel_id.'_arrows', array(
			'title'		=> __( 'Arrows', 'ubermenu' ),
			'priority'	=> 70,
			'panel'		=> $panel_id,
		) );

		//Responsive Toggle
		$wp_customize->add_section( $panel_id.'_toggle', array(
			'title'		=> __( 'Responsive Toggle', 'ubermenu' ),
			'priority'	=> 80,
			'panel'		=> $panel_id,
		) );

		//Responsive Toggle
		$wp_customize->add_section( $panel_id.'_search', array(
			'title'		=> __( 'Search Bar', 'ubermenu' ),
			'priority'	=> 90,
			'panel'		=> $panel_id,
		) );

		do_action( 'ubermenu_customizer_register_subsections' , $wp_customize , $panel_id );
		
	}
	else{
		$wp_customize->add_section( $section_id, array(
			'title'          => __( 'UberMenu', 'ubermenu' ) . ' ['.$config_id.']',
			'priority'       => 35,
		) );
	}



	//Add Settings
	$setting_op = $prefixed_menu_id;
	$all_fields = ubermenu_get_settings_fields();
	$fields = $all_fields[$prefixed_menu_id];
	$priority = 0;

	foreach( $fields as $field ){

		$priority+= 10;

		if( isset( $field['customizer'] ) && $field['customizer'] ){

			$setting_id = $setting_op.'['.$field['name'].']';

			$default = isset( $field['default'] ) ? $field['default'] : '';
			if( $field['type'] == 'checkbox' ){
				$default = $default == 'on' ? true : false;
			}

			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'     	=> $default,
					'type'			=> 'option',
				)
			);

			//If we're using panels, sort into subsections, otherwise we'll use the default
			$field_section_id = $section_id;
			if( $use_panels ){
				if( isset( $field['customizer_section'] ) ){
					$field_section_id = $panel_id.'_'.$field['customizer_section'];	//ubermenu_instance_{instance_id}_{section}
				}
			}

			$args = array(
				'label'		=> $field['label'],
				'section'	=> $field_section_id,
				'settings'	=> $setting_id,
				'priority'	=> $priority,
			);

			switch( $field['type'] ){

				case 'text':

					$args['type'] = 'text';
					$wp_customize->add_control(
						$setting_id,
						$args
					);
					break;

				case 'checkbox':

					$args['type'] = 'checkbox';
					//$args['std'] = $default == 'on' ? 1 : 0;
					//$args['default'] = $default == 'on' ? true : false;

					$wp_customize->add_control(
						$setting_id,
						$args
					);

					/*$wp_customize->add_control(
						new UberMenu_Customize_Better_Checkbox_Control(
							$wp_customize,
							$setting_id,
							$args
						)
					);*/
					break;

				case 'select':

					$args['type'] = 'select';
					$ops = $field['options'];
					if( !is_array( $ops ) && function_exists( $ops ) ){
						$ops = $ops();
					}
					$args['choices'] = $ops;
					//$args['choices'] = $field['options'];
					$wp_customize->add_control(
						$setting_id,
						$args
					);
					break;

				case 'radio':

					$args['type'] = 'radio';
					$args['choices'] = $field['options'];
					$wp_customize->add_control(
						$setting_id,
						$args
					);
					break;



				case 'color':
					
					$wp_customize->add_control(
						new WP_Customize_Color_Control(
							$wp_customize,
							$setting_id,
							$args
						)
					);
					break;


				case 'color_gradient':

					$wp_customize->add_control(
						new UberMenu_Customize_Color_Gradient_Control(
							$wp_customize,
							$setting_id,
							$args
						)
					);
					break;

					/*

					

					/*
					$wp_customize->add_control(
						new WP_Customize_Color_Control(
							$wp_customize,
							$setting_id,
							array(
								'label'		=> 'Deux',
								'section'	=> $section_id,
								'settings'	=> $setting_id,
							)
						)
					);
					*/

					break;


			}

		}

	}

/*
	$setting_id = $setting_op.'[style_menu_bar_background]';

	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'     	=> '#000000',
			'type'			=> 'option',
		)
	);
 
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			$setting_id,
			array(
				'label'      => __( 'Menu Bar Background', 'tcx' ),
				'section'    => $section_id,
				'settings'   => $setting_id,
			)
		)
	);
*/
}
add_action( 'customize_register', 'ubermenu_register_theme_customizers' );



function ubermenu_customizer_css() {

	//echo ubermenu_generate_custom_styles();

	//return;
	//$ops = get_option( UBERMENU_PREFIX.'main' );
	//$color = $ops['ubermenu_link_color'];
	//$color = ubermenu_op( 'style_menu_bar_background' , 'main' );
	//$ops = get_option( UBERMENU_PREFIX.'main' );
	//up( $ops );
	//$color = $ops[ 'style_menu_bar_background' ];
	//echo '['.$color.']'

	global $wp_customize;
	if ( isset( $wp_customize ) ):
	?>
	<style type="text/css">
		<?php 
			/*.ubermenu{ background: <?php echo $color; ?> !important; }*/
			//echo ubermenu_generate_custom_styles();
			echo ubermenu_generate_all_menu_preview_styles();
		?>
	</style>
	<?php endif;
}
add_action( 'wp_head', 'ubermenu_customizer_css' );


function ubermenu_generate_all_menu_preview_styles(){

	$all_styles = array();

	//$all_styles['main'] = ubermenu_generate_menu_preview_styles( 'main' );

	$instances = ubermenu_get_menu_instances( true );
	foreach( $instances as $config_id ){
		$all_styles[$config_id] = ubermenu_generate_menu_preview_styles( $config_id );
	}

	return ubermenu_generate_all_menu_styles( $all_styles );

}

function ubermenu_generate_menu_preview_styles( $config_id , $fields = false ){

	$menu_key = UBERMENU_PREFIX . $config_id;

	if( !$fields ){
		$all_fields = ubermenu_get_settings_fields();
		$fields = $all_fields[$menu_key];
	}

	$menu_styles = array();

	/*
	if( !isset( $menu_styles[$config_id] ) ){
		$menu_styles[$config_id] = array();
	}
	*/

	foreach( $fields as $field ){

		if( isset( $field['custom_style'] ) ){
			$callback = 'ubermenu_get_menu_style_'. $field['custom_style'];

			if( function_exists( $callback ) ){
				$callback( $field , $config_id , $menu_styles );
			}
		}

	}

	return $menu_styles;

}


function ubermenu_define_custom_customizer_controls(){

	/**
	 * Customize Checkbox Better Class
	 *
	 * @package WordPress
	 * @subpackage Customize
	 * @since 3.4.0
	 */
	class UberMenu_Customize_Better_Checkbox_Control extends WP_Customize_Control {
		/**
		 * @access public
		 * @var string
		 */
		public $type = 'better_checkbox';

		/**
		 * @access public
		 * @var array
		 */
		public $statuses;

		/**
		 * Constructor.
		 *
		 * @since 3.4.0
		 * @uses WP_Customize_Control::__construct()
		 *
		 * @param WP_Customize_Manager $manager
		 * @param string $id
		 * @param array $args
		 */
		public function __construct( $manager, $id, $args = array() ) {
			$this->statuses = array( '' => __('Default') );
			parent::__construct( $manager, $id, $args );
		}

		/**
		 * Enqueue scripts/styles for the color picker.
		 *
		 * @since 3.4.0
		 */
		public function enqueue() {
			
		}

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @since 3.4.0
		 * @uses WP_Customize_Control::to_json()
		 */
		public function to_json() {
			parent::to_json();
			$this->json['statuses'] = $this->statuses;
		}


		/**
		 * Render the control's content.
		 *
		 * @since 3.4.0
		 */
		public function render_content() {
			//$this_default = $this->setting->default;
			//up( $this->value() );
			//value="on" 
			?>
			<label>
				<input type="checkbox" <?php $this->link(); checked( 'on' , $this->value() ); ?> />
				<?php echo esc_html( $this->label ); ?>
			</label>
			
			<?php
		}
	}


	/**
	 * Customize Color Control Class
	 *
	 * @package WordPress
	 * @subpackage Customize
	 * @since 3.4.0
	 */
	class UberMenu_Customize_Color_Gradient_Control extends WP_Customize_Control {
		/**
		 * @access public
		 * @var string
		 */
		public $type = 'color_gradient';

		/**
		 * @access public
		 * @var array
		 */
		public $statuses;

		/**
		 * Constructor.
		 *
		 * @since 3.4.0
		 * @uses WP_Customize_Control::__construct()
		 *
		 * @param WP_Customize_Manager $manager
		 * @param string $id
		 * @param array $args
		 */
		public function __construct( $manager, $id, $args = array() ) {
			$this->statuses = array( '' => __('Default') );
			parent::__construct( $manager, $id, $args );
		}

		/**
		 * Enqueue scripts/styles for the color picker.
		 *
		 * @since 3.4.0
		 */
		public function enqueue() {
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'ubermenu-customizer' , UBERMENU_URL . 'admin/assets/customizer.js' , array( 'jquery' ) , UBERMENU_VERSION , true );
		}

		/**
		 * Refresh the parameters passed to the JavaScript via JSON.
		 *
		 * @since 3.4.0
		 * @uses WP_Customize_Control::to_json()
		 */
		public function to_json() {
			parent::to_json();
			$this->json['statuses'] = $this->statuses;
		}

		/**
		 * Render the control's content.
		 *
		 * @since 3.4.0
		 */
		public function render_content() {
			$this_default = $this->setting->default;
			$default_attr = '';
			if ( $this_default ) {
				if ( false === strpos( $this_default, '#' ) )
					$this_default = '#' . $this_default;
				$default_attr = ' data-default-color="' . esc_attr( $this_default ) . '"';
			}
			// The input's value gets set by JS. Don't fill it.
			
			//Val could be single val or gradient string
			$val = $this->value(); 
			$colors = explode( ',' , $val );
			$c1 = isset( $colors[0] ) ? $colors[0] : '';
			$c2 = isset( $colors[1] ) ? $colors[1] : '';
			
			?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			</label>

			<div class="customize-control-content">
				<input class="ubermenu-color-stop ubermenu-color-stop-1" type="text" data-uber-gradient-color="<?php echo $c1; ?>" maxlength="7" placeholder="<?php esc_attr_e( 'Hex Value' ); ?>"<?php echo $default_attr; ?> />
				<input class="ubermenu-color-stop ubermenu-color-stop-2" type="text" data-uber-gradient-color="<?php echo $c2; ?>"maxlength="7" placeholder="<?php esc_attr_e( 'Hex Value' ); ?>"<?php echo $default_attr; ?> />
				<input type="hidden" id="<?php echo $this->id; ?>" class="ubermenu-gradient-list" <?php $this->link(); ?> value="<?php echo sanitize_text_field( $this->value() ); ?>">
				<small>Select 1 color for flat, 2 for gradient.</small>
			</div>
			
			<?php
		}
	}

}

