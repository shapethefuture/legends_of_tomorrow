<?php

if( !class_exists( 'Themify_Builder_Access_Role' ) ){
	class Themify_Builder_Access_Role extends Themify_Builder_Options{

		function __construct(){
			add_filter( 'admin_init', array( $this, 'role_access_config' ), 99 );
			add_filter( 'themify_builder_is_frontend_editor', array( $this, 'tf_themify_hide_builder_frontend' ), 99 );
			add_filter( 'themify_do_metaboxes', array( $this, 'tf_themify_hide_builder_backend' ), 99 );
		}

		// Renders the options for backend role access control
		function themify_builder_backend_role_access(){

			global $wp_roles;
		    $roles = $wp_roles->get_names();

		    // Remove the adminitrator and subscriber user role from the array
		    unset( $roles['administrator']);

		    // Remove all the user roles with no "edit_posts" capability
		    foreach( $roles as $role => $slug ) {
		    	$userCapabilities = $wp_roles->roles[$role]['capabilities'];
		    	if( !isset( $userCapabilities['edit_posts'] ) ){
		    		unset( $roles[$role] );
		    	} elseif ( false == $userCapabilities['edit_posts'] ) {
		    		unset( $roles[$role] );
		    	}
		    }
		    
		    // Get the unique setting name
		    $setting = 'backend';

		    // Generate prefix with the setting name
		    $prefix = 'setting-'.$setting.'-';
		    ?>
		    <ul>
			<?php foreach( $roles as $role => $slug ) {

				$option_name = $this->option_name . '['.$prefix.$role.']';
				$this->option_value = get_option( $this->option_name );

				// Get value from the database
				$value = isset( $this->option_value[ $prefix.$role ] ) ? $this->option_value[ $prefix.$role ] : 'default';				?>
			   	<li class="role-access-controller">
				   	<!-- Set the column title -->
				   	<div class="role-title">
				   		<?php echo esc_attr( $slug ); ?>
				   	</div>

				   	<!-- Set option to default -->
				   	<div class="role-option role-default">
					   	<input type="radio" id="default-<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" value="default" <?php echo checked( $value, 'default', false ); ?>/>
					   	<label for="default-<?php echo esc_attr( $option_name ); ?>"><?php _e( 'Default', 'themify' ); ?></label>
				   	</div>

					<!-- Set option to enable -->
				   	<div class="role-option role-enable">
					   	<input type="radio" id="enable-<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" value="enable" <?php echo checked( $value, 'enable', false ); ?>/>
					   	<label for="enable-<?php echo esc_attr( $option_name ); ?>"><?php _e( 'Enable', 'themify' ); ?></label>
				   	</div>

				   	<!-- Set option to disable -->
				   	<div class="role-option role-disable">
					   	<input type="radio" id="disable-<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" value="disable" <?php echo checked( $value, 'disable', false ); ?>/>
					   	<label for="disable-<?php echo esc_attr( $option_name ); ?>"><?php _e( 'Disable', 'themify' ); ?></label>
				   	</div>
			   </li>
			<?php }//end foreach ?>
			</ul>
			<?php
		}

		// Renders the options for backend role access control
		function themify_builder_frontend_role_access(){

			global $wp_roles;
		    $roles = $wp_roles->get_names();

		    // Remove the adminitrator and subscriber user role from the array
		    unset( $roles['administrator']);

		    // Remove all the user roles with no "edit_posts" capability
		    foreach( $roles as $role => $slug ) {
		    	$userCapabilities = $wp_roles->roles[$role]['capabilities'];
		    	if( !isset( $userCapabilities['edit_posts'] ) ){
		    		unset( $roles[$role] );
		    	} elseif ( false == $userCapabilities['edit_posts'] ) {
		    		unset( $roles[$role] );
		    	}
		    }
		    
		    // Get the unique setting name
		    $setting = 'frontend';

		    // Generate prefix with the setting name
		    $prefix = 'setting-'.$setting.'-';

		    ?>
		    <ul>
			<?php foreach( $roles as $role => $slug ) {

				$option_name = $this->option_name . '['.$prefix.$role.']';
				$this->option_value = get_option( $this->option_name );

				// Get value from the database
				$value = isset( $this->option_value[ $prefix.$role ] ) ? $this->option_value[ $prefix.$role ] : 'default';
				?>
			   	<li class="role-access-controller">
				   	<!-- Set the column title -->
				   	<div class="role-title">
				   		<?php echo esc_attr( $slug ); ?>
				   	</div>

				   	<!-- Set option to default -->
				   	<div class="role-option role-default">
					   	<input type="radio" id="default-<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" value="default" <?php echo checked( $value, 'default', false ); ?>/>
					   	<label for="default-<?php echo esc_attr( $option_name ); ?>"><?php _e( 'Default', 'themify' ); ?></label>
				   	</div>

					<!-- Set option to enable -->
				   	<div class="role-option role-enable">
					   	<input type="radio" id="enable-<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" value="enable" <?php echo checked( $value, 'enable', false ); ?>/>
					   	<label for="enable-<?php echo esc_attr( $option_name ); ?>"><?php _e( 'Enable', 'themify' ); ?></label>
				   	</div>

				   	<!-- Set option to disable -->
				   	<div class="role-option role-disable">
					   	<input type="radio" id="disable-<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>" value="disable" <?php echo checked( $value, 'disable', false ); ?>/>
					   	<label for="disable-<?php echo esc_attr( $option_name ); ?>"><?php _e( 'Disable', 'themify' ); ?></label>
				   	</div>
			   </li>
			<?php }//end foreach ?>
			</ul>
			<?php
		}
                
               //check if user has access to builder in backend
                public static function check_access_backend(){
                    if( is_user_logged_in() ){
                        $user = wp_get_current_user();
                        $userRole = isset( $user->roles[0] ) ? $user->roles[0] : '';
                        $prefix = 'setting-backend-';
                        $options = get_option('themify_builder_setting');
                        $backend_builder = isset( $options[ $prefix.$userRole ] ) ? $options[ $prefix.$userRole ] : '';
                        return "disable" != $backend_builder;
                    }
                    return false;
                }
                
		/**
		 * Role Access Control
		 * @param array $themify_theme_config
		 * @return array
		 */
		function role_access_config( $themify_theme_config ) {
			register_setting( 'themify_builder_option_group', $this->option_name );

			$current_tab = ( empty( $_GET['tab'] ) ) ? 'builder' : sanitize_text_field( urldecode( $_GET['tab'] ) );
			$this->current_tab = $current_tab;

			switch ( $current_tab ) {
				default:
					if ( Themify_Builder_Model::builder_check() ) {
						add_settings_field(
							'builder_backend', 
							__( 'Builder Backend Role Access', 'themify' ), 
							array( $this, 'themify_builder_backend_role_access' ), 
							self::$slug,
							'setting_builder_section'			
						);
						add_settings_field(
							'builder_frontend', 
							__( 'Builder Frontend Role Access', 'themify' ), 
							array( $this, 'themify_builder_frontend_role_access' ), 
							self::$slug,
							'setting_builder_section'			
						);
					}
				break;
			}
		}

		// Hide Themify Builder Backend
		function tf_themify_hide_builder_backend( $meta ) {
			if( is_user_logged_in() ){
				$return = array();
				$user = wp_get_current_user();
			    $userRole = isset( $user->roles[0] ) ? $user->roles[0] : '';
			    
			    // Generate prefix with the setting name
			    $prefix = 'setting-backend-';
			    $option_name = $this->option_name . '['.$prefix.$userRole.']';
				$this->option_value = get_option( $this->option_name );
				$value = isset( $this->option_value[ $prefix.$userRole ] ) ? $this->option_value[ $prefix.$userRole ] : 'default';
			    //$value = themify_get( $prefix.$userRole );
				if ( "enable" == $value ) {
					$return = $meta;
			    } elseif( "disable" == $value ) {
			        $return = array();
			    } else {
			    	$return = $meta;
			    }
			   	return $return;
			} else {
				return $meta;
			}
		}

		// Hide Themify Builder Frontend
		function tf_themify_hide_builder_frontend( $return ) {
			if( is_user_logged_in() ){
				$user = wp_get_current_user();
			    $userRole = isset( $user->roles[0] ) ? $user->roles[0] : '';
			    
			    // Generate prefix with the setting name
			    $prefix = 'setting-frontend-';
			    $option_name = $this->option_name . '['.$prefix.$userRole.']';
				$this->option_value = get_option( $this->option_name );
				$value = isset( $this->option_value[ $prefix.$userRole ] ) ? $this->option_value[ $prefix.$userRole ] : 'default';
			    //$value = themify_get( $prefix.$userRole );
				if ( "enable" == $value ) {
					return true;
			    } elseif( "disable" == $value ) {
			        return false;
			    } elseif( current_user_can( 'edit_posts', get_the_ID() ) ){
			    	return $return;
			    }
		   	}
		}

	}

	$GLOBALS['themify_builder_access_role'] = new Themify_Builder_Access_Role();
}