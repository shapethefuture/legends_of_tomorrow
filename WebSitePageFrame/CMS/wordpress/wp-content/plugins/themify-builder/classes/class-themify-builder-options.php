<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Themify_Builder_Options' ) ) {
	
	/**
	 * Class Builder Options
	 */
	class Themify_Builder_Options {
		
		protected $option_name = 'themify_builder_setting';
		protected $option_value = array();
		protected $current_tab = '';
		public static $slug = 'themify-builder';

		/**
		 * Constructor
		 */
		public function __construct() {
			if ( is_admin() ){
				add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
				add_action( 'admin_init', array( $this, 'page_init' ) );
			}
			if ( ! is_admin() ){
				add_action( 'wp_head', array( $this, 'show_custom_css' ) );
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'load_enqueue_scripts' ) );
			add_filter( 'themify_top_pages', array( $this, 'queue_top_pages' ) );
 			add_filter( 'themify_pagenow', array( $this, 'queue_pagenow' ) );
		}
		
		public function add_plugin_page(){
			// This page will be under "Settings"
			add_menu_page( __( 'Themify Builder', 'themify' ), __( 'Themify Builder', 'themify' ), 'manage_options', self::$slug, array( $this, 'create_admin_page'), plugins_url( 'themify-builder/themify/img/favicon.png' ) );
			add_submenu_page( self::$slug, __( 'Settings', 'themify' ), __( 'Settings', 'themify' ), 'manage_options', self::$slug );
			
			if ( Themify_Builder_Model::builder_check() ) {
				add_submenu_page( self::$slug, __( 'Builder Layouts', 'themify' ), __( 'Builder Layouts', 'themify' ), 'edit_posts', 'edit.php?post_type=tbuilder_layout' );
				add_submenu_page( self::$slug, __( 'Builder Layout Parts', 'themify' ), __( 'Builder Layout Parts', 'themify' ), 'edit_posts', 'edit.php?post_type=tbuilder_layout_part' );
			}
		}

		public function create_admin_page() {
			$this->option_value = get_option( $this->option_name );

			if ( isset( $_GET['action'] ) ) {
				$action = 'upgrade';
				themify_builder_updater();
			}

			?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e('Themify Builder', 'themify') ?></h2>			
			<form method="post" action="options.php">
				<div class="icon32" id="icon-options-general"><br /></div><h2 class="nav-tab-wrapper themify-nav-tab-wrapper">
					<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[tabs_active]" value="<?php echo esc_attr( $this->current_tab ); ?>">
					<?php
						$tabs = array(
							'builder' => __( 'Settings', 'themify' ),
							'image_setting' => __( 'Image Script', 'themify' ),
							'custom_css' => __('Custom CSS', 'themify'),
                                                        'builder_settings'=>__('Themify Builder settings page','themify')
						);

						$tabs = apply_filters('themify_builder_settings_tab_array', $tabs);

						foreach ( $tabs as $name => $label ) {
							echo '<a href="' . admin_url( 'admin.php?page=' . self::$slug. '&tab=' . $name ) . '" class="nav-tab ';
							if( $this->current_tab == $name ) echo 'nav-tab-active';
							echo '">' . $label . '</a>';
						}
					?>
				</h2>
				<?php
					// This prints out all hidden setting fields
					settings_fields( 'themify_builder_option_group' );	
					do_settings_sections( self::$slug );
				?>
				<?php submit_button(); ?>
			</form>

			<!-- alerts -->
			<div class="alert"></div> 
			<!-- /alerts -->
			
			<!-- prompts -->
			<div class="prompt-box">
				<div class="show-login">
					<form id="themify_update_form" method="post" action="<?php echo admin_url( 'admin.php?page=' . self::$slug . '&action=upgrade&login=true' ); ?>">
					<p class="prompt-msg"><?php _e('Enter your Themify login info to upgrade', 'themify'); ?></p>
					<p><label><?php _e('Username', 'themify'); ?></label> <input type="text" name="username" class="username" value=""/></p>
					<p><label><?php _e('Password', 'themify'); ?></label> <input type="password" name="password" class="password" value=""/></p>
					<input type="hidden" value="true" name="login" />
					<p class="pushlabel"><input name="login" type="submit" value="Login" class="button themify-builder-upgrade-login" /></p>
					</form>
				</div>
				<div class="show-error">
					<p class="error-msg"><?php _e('There were some errors updating the theme', 'themify'); ?></p>
				</div>
			</div>
			<!-- /prompts -->

			<script type="text/javascript">
				function switch_image_field() {
					if(!jQuery('.disable_img_php').is(':checked')) {
						jQuery('.image_global_size_field').closest('tr').hide();
						jQuery('.img_field').closest('tr').show();
					} else {
						jQuery('.img_field').closest('tr').hide();
						jQuery('.image_global_size_field').closest('tr').show();
					}	
				}
				
				switch_image_field();
				jQuery('.disable_img_php').on('click', function(e){
					switch_image_field();
				});
			</script>
		</div>
		<?php
		}
		
		public function page_init() {		
			register_setting( 'themify_builder_option_group', $this->option_name, array( $this, 'before_save' ) );
			$current_tab = ( empty( $_GET['tab'] ) ) ? 'builder' : sanitize_text_field( urldecode( $_GET['tab'] ) );
			$this->current_tab = $current_tab;

			switch ( $current_tab ) {
				case 'image_setting':
					// image script settings
					add_settings_section(
						'setting_builder_image_section',
						__( 'Image Script Settings', 'themify' ),
						'',
						self::$slug
					);

					add_settings_field(
						'image_script', 
						__('Disable', 'themify'),
						array( $this, 'image_script_field' ), 
						self::$slug,
						'setting_builder_image_section'			
					);

					add_settings_field(
						'image_global_size', 
						__('Default Featured Image Size', 'themify'), 
						array( $this, 'image_global_field' ), 
						self::$slug,
						'setting_builder_image_section'			
					);

				break;

				case 'custom_css':
					// image script settings
					add_settings_section(
						'setting_builder_custom_css_section',
						__( 'Custom CSS', 'themify' ),
						'',
						self::$slug
					);

					add_settings_field(
						'custom_css', 
						false, 
						array( $this, 'custom_css_field' ), 
						self::$slug,
						'setting_builder_custom_css_section'			
					);
				break;
				case 'builder_settings':
					// image script settings
					add_settings_section(
						'setting_builder_settings_section',
						__( 'Themify Builder settings page', 'themify' ),
						'',
						self::$slug
					);

					add_settings_field(
						'google_map', 
						__('Google Map Api Key','themify'), 
						array( $this, 'google_map_api_key_field' ), 
						self::$slug,
						'setting_builder_settings_section'			
					);
				break;
				default:
					add_settings_section(
						'setting_builder_section',
						__( 'Builder Settings', 'themify' ),
						'',
						self::$slug
					);
						
					add_settings_field(
						'builder_active', 
						__( 'Themify Builder', 'themify' ), 
						array( $this, 'builder_active_field' ), 
						self::$slug,
						'setting_builder_section'			
					);

					if ( Themify_Builder_Model::builder_check() ) {
						
						add_settings_field(
							'builder_cache', 
							__( 'Disable Builder Cache', 'themify' ), 
							array( $this, 'builder_disable_cache' ), 
							self::$slug,
							'setting_builder_section'			
						);

						if (themify_builder_get('builder_disable_cache')==='enable' && TFCache::check_version()) {
							add_settings_field(
								'builder_clear_cache', 
								__( 'Clear Builder Cache', 'themify' ), 
								array( $this, 'builder_clear_cache' ), 
								self::$slug,
								'setting_builder_section'			
							);
						}

						add_settings_field(
							'builder_shortcuts', 
							__( 'Disable Shortcuts', 'themify' ), 
							array( $this, 'builder_disable_shortcuts' ), 
							self::$slug,
							'setting_builder_section'			
						);

						add_settings_field(
							'builder_animation', 
							__( 'Animation Effects', 'themify' ), 
							array( $this, 'builder_animation_field' ), 
							self::$slug,
							'setting_builder_section'			
						);

						add_settings_field(
							'builder_parallax', 
							__( 'Parallax Effects', 'themify' ), 
							array( $this, 'builder_parallax_field' ), 
							self::$slug,
							'setting_builder_section'			
						);

						add_settings_field(
							'builder_responsive', 
							esc_html__( 'Responsive Design', 'themify' ), 
							array( $this, 'builder_responsive_field' ), 
							self::$slug,
							'setting_builder_section'			
						);

						add_settings_field(
							'builder_excludes', 
							__( 'Exclude Builder Modules', 'themify' ), 
							array( $this, 'builder_exclude_field' ), 
							self::$slug,
							'setting_builder_section'			
						);
					}
				break;
			}

		}

		public function show_custom_css(){
			$settings = get_option( $this->option_name );
			$custom_css = isset( $settings['custom_css-custom_css'] ) ? $settings['custom_css-custom_css'] : false;
			if ( $custom_css ){
				echo PHP_EOL . '<!-- Builder Custom Style -->' . PHP_EOL;
				echo '<style type="text/css">' . PHP_EOL;
				echo $custom_css . PHP_EOL;
				echo '</style>' . PHP_EOL . '<!-- / end builder custom style -->' . PHP_EOL;
			}
		}

		function before_save($input) {
			$active_tabs = $input['tabs_active'];
			$exist_data = get_option( $this->option_name );
			$exist_data = is_array($exist_data) ? $exist_data : array();
			
			foreach($exist_data as $k => $v) {
				if ( strpos( $k, $active_tabs ) !== false ) {
					unset($exist_data[$k]);
				}
			}

			$all = array_merge($exist_data, $input);
                        if(class_exists('TFCache')){
                            $remove = $all['builder_is_active']!='enable' || isset($all['builder_disable_cache']);
                            TFCache::rewrite_htaccess($remove);
                        }
			return $all;
		}
		
		function print_section_builder_info(){
			_e('Enable/Disable Themify Builder', 'themify');
		}
		
		function builder_active_field(){
			$selected = isset( $this->option_value[$this->current_tab.'_is_active'] ) ? $this->option_value[$this->current_tab.'_is_active'] : 'enable';
			?>
			<select name="<?php echo esc_attr( $this->option_name . '['.$this->current_tab.'_is_active]' ); ?>">
				<option value="enable" <?php selected( $selected, 'enable'); ?>><?php _e('Enable', 'themify') ?></option>
				<option value="disable" <?php selected( $selected, 'disable'); ?>><?php _e('Disable', 'themify') ?></option>
			</select>
			<?php
		}

		function builder_exclude_field() {
			global $ThemifyBuilder;
			$modules = $ThemifyBuilder->get_modules( 'all' );
			?>

			<?php foreach( $modules as $k => $v ): ?>
			<?php
			$name = $this->current_tab . '_exclude_module_' . $v['id'];
			$field_name = $this->option_name . '['.$name.']';
			$mod_checked = isset($this->option_value[$name] ) ? $this->option_value[$name] : 0; ?>
			<p>
			<input id="exclude_module_<?php echo $v['id']; ?>" type="checkbox" value="1" name="<?php echo $field_name; ?>" <?php checked( $mod_checked, 1 ); ?>>
			<label for="exclude_module_<?php echo $v['id']; ?>"><?php echo sprintf(__('Exclude %s module', 'themify'), $v['name']); ?></label>
			</p>
			<?php endforeach; ?>
			<?php
		}

		/**
		 * Disable builder cache field.
		 * 
		 * @since 1.5.4
		 * @access public
		 */
		public function builder_disable_cache() {
			if(TFCache::check_version()){
				global $ThemifyBuilder;
				$pre = $this->current_tab . '_';
				$disable_cache_status = isset( $this->option_value[$pre.'disable_cache'] ) ? $this->option_value[$pre.'disable_cache'] : '';
				$out = sprintf( '<p><label for="%s"><input type="radio" id="%s" name="%s" value="%s" %s> %s %s</label><br/><label for="%s"><input type="radio" id="%s" name="%s" value="%s" %s> %s %s</label></p>',
						esc_attr( $pre . 'disable_cache' ),
						esc_attr( $pre . 'disable_cache' ),
						esc_attr( $this->option_name . '[' . $pre . 'disable_cache' . ']' ),
						esc_attr( 'disable' ),
						checked( 'disable', $disable_cache_status, false ) . checked( 'on', $disable_cache_status, false ) . checked( '', $disable_cache_status, false ),
						esc_attr( 'Disable' ),
						wp_kses_post( __('(disable it if you experience Builder issues and conflicts)', 'themify') ),
						esc_attr( $pre . 'enable_cache' ),
						esc_attr( $pre . 'enable_cache' ),
						esc_attr( $this->option_name . '[' . $pre . 'disable_cache' . ']' ),
						esc_attr( 'enable' ),
						checked( 'enable', $disable_cache_status, false ),
						esc_attr( 'Enable' ),
						wp_kses_post( __('(enable it for faster page load)', 'themify') )
				);
				echo $out;
			}
			else{
				_e('Your server does not support Builder cache, thus it is disabled. It requires PHP 5.4+','themify');
			}
		}

		/**
		 * Clear builder cache.
		 * 
		 * @since 1.5.4
		 * @access public
		 */
		public function builder_clear_cache() {
			global $ThemifyBuilder;
			$pre = $this->current_tab . '_';
                        $out='<div>';
                        $expire =  isset( $this->option_value[$pre.'cache_expiry'] )?$this->option_value[$pre.'cache_expiry']:'';
                        $expire = $expire>0?intval($expire):2;
                        $out.=sprintf('<input type="text" class="width2" value="%s" name="%s" />  &nbsp;&nbsp;<span>%s</span>',
                                    $expire,
                                    esc_attr( $this->option_name . '[' . $pre . 'cache_expiry' . ']' ),
                                     __( 'Expire Cache (days)', 'themify' )
                                );
                        $out.='<br/><br/>';
                        $out.= sprintf( '<a href="#" data-clearing-text="%s" data-done-text="%s" data-default-text="%s" data-default-icon="ti-eraser" class="button button-secondary js-clear-builder-cache"> <i class="ti-eraser"></i> <span>%s</span></a><br/><span class="description">%s</span>',
				esc_html__( 'Clearing cache...', 'themify' ),
				esc_html__( 'Done', 'themify' ),
				esc_html__( 'Clear cache', 'themify' ),
				esc_html__( 'Clear cache', 'themify' ),
				esc_html__( 'Clear all Builder cache', 'themify' )
			);
                        $out.='</div>';
                        echo $out;
		}

		/**
		 * Render checkbox to disable shortcuts.
		 *
		 * @since 1.4.7
		 * @access public
		 */
		function builder_disable_shortcuts() {
			global $ThemifyBuilder;
			$pre = $this->current_tab . '_';
			$out = sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
				esc_attr( $pre . 'disable_shortcuts' ),
				esc_attr( $pre . 'disable_shortcuts' ),
				esc_attr( $this->option_name . '[' . $pre . 'disable_shortcuts' . ']' ),
				checked( true, isset( $this->option_value[$pre.'disable_shortcuts'] ), false ),
				wp_kses_post( __( 'Disable Builder shortcuts (eg. disable shortcut like Cmd+S = save)', 'themify') )
			);
			echo $out;
		}

		function builder_animation_field() {
			global $ThemifyBuilder;
			$pre = $this->current_tab . '_animation_';
			$mobile_checked = '';
			$disabled_checked = '';

			if ( isset( $this->option_value[ $pre.'mobile_exclude' ] ) && $this->option_value[ $pre.'mobile_exclude' ] ) 
				$mobile_checked = " checked='checked'";
			
			if ( isset( $this->option_value[ $pre.'disabled' ] ) && $this->option_value[ $pre . 'disabled' ] ) 
				$disabled_checked = " checked='checked'";

			$out = '';
			$out .= sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
				$pre . 'mobile_exclude',
				$pre . 'mobile_exclude',
				$this->option_name . '[' . $pre . 'mobile_exclude' . ']',
				$mobile_checked,
				__( 'Disable Builder animation on mobile and tablet only', 'themify')
			);
			$out .= sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
				$pre . 'disabled',
				$pre . 'disabled',
				$this->option_name . '[' . $pre . 'disabled' . ']',
				$disabled_checked,
				__( 'Disable Builder animation on all devices (all row and module animation will not have any effect)', 'themify')
			);
			echo $out;
		}

		function builder_parallax_field() {
			global $ThemifyBuilder;
			$pre = $this->current_tab . '_parallax_';
			$mobile_checked = '';
			$disabled_checked = '';

			if ( isset( $this->option_value[ $pre.'mobile_exclude' ] ) && $this->option_value[ $pre.'mobile_exclude' ] ) 
				$mobile_checked = " checked='checked'";
			
			if ( isset( $this->option_value[ $pre.'disabled' ] ) && $this->option_value[ $pre . 'disabled' ] ) 
				$disabled_checked = " checked='checked'";

			$out = '';
			$out .= sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
				$pre . 'mobile_exclude',
				$pre . 'mobile_exclude',
				$this->option_name . '[' . $pre . 'mobile_exclude' . ']',
				$mobile_checked,
				__( 'Disable Builder parallax on mobile and tablet only', 'themify')
			);
			$out .= sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
				$pre . 'disabled',
				$pre . 'disabled',
				$this->option_name . '[' . $pre . 'disabled' . ']',
				$disabled_checked,
				__( 'Disable Builder parallax on all devices (all row parallax will not have any effect)', 'themify')
			);
			echo $out;
		}

		/**
		 * Responsive Design Fields.
		 * 
		 * @access public
		 */
		public function builder_responsive_field() {
			$pre = $this->current_tab . '_responsive_design_';
			$bp_tablet = ( isset( $this->option_value[ $pre. 'tablet'] ) && ! empty( $this->option_value[ $pre . 'tablet'] ) ) ? $this->option_value[ $pre . 'tablet'] : 768;
			$bp_tablet_landscape = ( isset( $this->option_value[ $pre. 'tablet_landscape'] ) && ! empty( $this->option_value[ $pre . 'tablet_landscape'] ) ) ? $this->option_value[ $pre . 'tablet_landscape'] : 1024;
			$bp_mobile = ( isset( $this->option_value[ $pre. 'mobile'] ) && ! empty( $this->option_value[ $pre . 'mobile'] ) ) ? $this->option_value[ $pre . 'mobile'] : 680;

			$out = '';
			$out .= sprintf( '<p class="clearfix"><span class="label">%s</span></p>', esc_html__( 'Responsive Breakpoints:', 'themify' ) );
			$out .= sprintf( '<div class="clearfix"><div class="label">%s</div><div class="label input-range width10"><div class="range-slider width8"></div><input type="text" name="%s" value="%s" data-min="%d" data-max="%d" class="width4" readonly> px</div></div>',
				esc_html__( 'Tablet Landscape', 'themify' ),
				$this->option_name . '[' . $pre . 'tablet_landscape' . ']',
				$bp_tablet_landscape,
				769,
				1200,
				$bp_tablet_landscape
			);
			$out .= sprintf( '<div class="clearfix"><div class="label">%s</div><div class="label input-range width10"><div class="range-slider width8"></div><input type="text" name="%s" value="%s" data-min="%d" data-max="%d" class="width4" readonly> px</div></div>',
				esc_html__( 'Tablet Portrait', 'themify' ),
				$this->option_name . '[' . $pre . 'tablet' . ']',
				$bp_tablet,
				681,
				768,
				$bp_tablet
			);
			$out .= sprintf( '<div class="clearfix"><div class="label">%s</div><div class="label input-range width10"><div class="range-slider width8"></div><input type="text" name="%s" value="%s" data-min="%d" data-max="%d" class="width4" readonly> px</div></div>',
				esc_html__( 'Mobile', 'themify' ),
				$this->option_name . '[' . $pre . 'mobile' . ']',
				$bp_mobile,
				320,
				680,
				$bp_mobile
			);
			echo $out;
		}

		function image_script_field() {
			$name = $this->current_tab . '-img_settings_use';
			$field_name = $this->option_name . '['.$name.']';
			$checked = isset($this->option_value[$name]) ? $this->option_value[$name] : '';
			echo '<input id="themify_setting-img_settings_use" type="checkbox" name="'.$field_name.'" class="disable_img_php" value="1" '.checked( $checked, 1, false ).'/>';
			echo '<label for="themify_setting-img_settings_use">&nbsp; '.__('Disable image script globally',
					'themify').'</label>';
			echo '<br /><span><small>'.__('(WordPress Featured Image or original images will be used)', 'themify').'</small></span>';
		}

		function image_global_field() {
			$feature_sizes = themify_get_image_sizes_list();

			$name = $this->current_tab.'-global_feature_size';
			$field_name = $this->option_name . '['.$name.']';
			$selected = isset($this->option_value[$name]) ? $this->option_value[$name] : '';
			echo '<select name="'.$field_name.'" class="image_global_size_field">';
			foreach($feature_sizes as $option){
				echo '<option value="' . esc_attr( $option['value'] ) . '"'.selected( $selected, $option['value'] ).'>' . esc_html( $option['name'] ) . '</option>';
			}
			echo '</select>';
		}

		function image_quality_field() {
			$name = $this->current_tab.'-img_settings_quality';
			$field_name = $this->option_name . '['.$name.']';
			$value = isset($this->option_value[$name]) ? $this->option_value[$name] : '';
			echo '<input type="text" name="'.$field_name.'" value="'.$value.'" class="img_field">';
			echo '&nbsp; <small>'. __('max 100 (higher = better quality, but bigger file size)', 'themify') .'</small>';
		}

		function image_crop_align_field() {
			$options = array(
				array("value" => "c", "name" => __('Center', 'themify')),
				array("value" => "t", "name" => __('Top', 'themify')),
				array("value" => "tr",	"name" => __('Top Right', 'themify')),
				array("value" => "tl",	"name" => __('Top Left', 'themify')),
				array("value" => "b",	"name" => __('Bottom', 'themify')),
				array("value" => "br",	"name" => __('Bottom Right', 'themify')),
				array("value" => "bl",	"name" => __('Bottom Left', 'themify')),
				array("value" => "l",	"name" => __('Left', 'themify')),
				array("value" => "r",	"name" => __('Right', 'themify'))
			);
			$name = $this->current_tab .'-img_settings_crop_option';
			$field_name = $this->option_name . '['.$name.']';
			echo '<select name="'.$field_name.'" class="img_field"><option></option>';
			foreach($options as $option){
				$selected = isset( $this->option_value[$name] ) ? $this->option_value[$name] : '';
					echo '<option value="' . esc_attr( $option['value'] ) . '" '.selected( $selected, $option['value']).'>' . esc_html( $option['name'] ) . '</option>';
				}
			echo '</select>';
		}

		function image_vertical_crop_field() {
			$options_vertical = array(
				array('name'=> '',					 'value' => ''),
				array('name'=> __('Yes', 'themify'), 'value' => 'yes'),
				array('name'=> __('No', 'themify'),	 'value' => 'no')
			);
			$name = $this->current_tab .'-img_settings_vertical_crop_option';
			$field_name = $this->option_name . '['.$name.']';
			$selected = isset( $this->option_value[$name] ) ? $this->option_value[$name] : '';
			echo '<select name="' . esc_attr( $field_name ) . '" class="img_field">';
			foreach($options_vertical as $option_vertical){
				echo '<option value="' . esc_attr( $option_vertical['value'] ) . '"'.selected( $selected, $option_vertical['value'] ).'>' . esc_html( $option_vertical['name'] ) . '</option>';
			}
			echo '</select>&nbsp; <small>' . __('(Select \'no\' to disable vertical cropping globally)', 'themify') . '</small>';
		}

		function custom_css_field(){
			$name = $this->current_tab . '-custom_css';
			$field_name = $this->option_name . '['.$name.']';
			echo '<textarea name="'.$field_name.'" style="width:100%;height:600px;">';
			echo isset( $this->option_value[$name] ) ? $this->option_value[$name] : '';
			echo '</textarea>';
		}
                
                function google_map_api_key_field(){
			$pre = $this->current_tab . '_';
                        $out='<div>';
                        $google_map_key =  isset( $this->option_value[$pre.'google_map_key'] )?$this->option_value[$pre.'google_map_key']:'';
                        $out.=sprintf('<input type="text" style="min-width:300px;" value="%s" name="%s" />',
                                    $google_map_key,
                                    esc_attr( $this->option_name . '[' . $pre . 'google_map_key' . ']' )
                                );
                        $out.='<br/><br/>';
                        $out.= __('Google API key is required to use Builder Map module and Map shortcode.','themify').' <a href="//developers.google.com/maps/documentation/javascript/get-api-key#key" target="_blank">'.__( 'Generate Api key', 'themify' ).'</a>';
                        $out.='</div>';
                        echo $out;
                    
                }

		function queue_top_pages( $pages ) {
	 		array_push( $pages, 'toplevel_page_themify-builder' );
	 		return $pages;
	 	}

	 	function queue_pagenow( $pagenows ) {
	 		array_push( $pagenows, 'themify-builder' );
	 		return $pagenows;
	 	}

	 	function load_enqueue_scripts( $page ) {
	 		if ( 'toplevel_page_themify-builder' == $page ) {
	 			wp_enqueue_script( 'jquery-ui-slider' );
	 			wp_enqueue_script( 'jquery-ui-sortable' );
	 			wp_enqueue_script( 'themify-builder-plugin-upgrade', THEMIFY_BUILDER_URI . '/js/themify.builder.upgrader.js', array('jquery'), false, true );
	 		}
	 	}
	}
}
?>