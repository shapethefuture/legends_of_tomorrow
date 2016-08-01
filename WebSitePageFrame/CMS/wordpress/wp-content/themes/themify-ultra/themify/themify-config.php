<?php
/***************************************************************************
 *
 * 	----------------------------------------------------------------------
 * 							DO NOT EDIT THIS FILE
 *	----------------------------------------------------------------------
 *
 * 						Copyright (C) Themify 
 *
 *	----------------------------------------------------------------------
 *
 ***************************************************************************/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function themify_config_init() {

	/* 	Global Vars
 	***************************************************************************/
	global $themify_config, $pagenow, $ThemifyConfig, $themify_gfonts, $content_width;

	if ( ! isset( $content_width ) ) {
		$content_width = 1165;
	}

	/*	Activate Theme
 	***************************************************************************/
	if ( isset( $_GET['activated'] ) && 'themes.php' == $pagenow ) {
		themify_maybe_clear_legacy();
		add_action( 'init', 'themify_theme_first_run', 20 );
	}


	/* 	Theme Config
 	***************************************************************************/
	define( 'THEMIFY_VERSION', '2.8.3' ); 

	/*	Load Config from theme-config.php or custom-config.php
 	***************************************************************************/
	$themify_config = $ThemifyConfig->get_config();

	/* 	Google Fonts
 	***************************************************************************/
	$themify_gfonts = themify_get_google_font_lists();

	/* 	Run after update
 	***************************************************************************/
	if ( 'update_ok' === get_option( 'themify_update_ok_flag' ) ) {
		/**
		 * Fires after the updater finished the updating process.
		 *
		 * @since 1.8.3
		 */
		do_action( 'themify_updater_post_install' );
	}

	/* 	Woocommerce
	 ***************************************************************************/
	if( themify_is_woocommerce_active() ) {
		add_theme_support('woocommerce');
	}

	/**
	 * Editor Style
	 * @since 2.0.2
	 */
	add_editor_style();
	add_theme_support( 'title-tag' );

}
add_action( 'after_setup_theme', 'themify_config_init' );

function themify_theme_first_run() {
	flush_rewrite_rules();
	header( 'Location: ' . admin_url() . 'admin.php?page=themify&firsttime=true' );
}

///////////////////////////////////////
// Load theme languages
///////////////////////////////////////

load_theme_textdomain( 'themify', THEME_DIR.'/languages' );

/**
 * Load Filesystem Class
 * @since 2.5.8
 */
require_once( THEME_DIR . '/themify/class-themify-filesystem.php' );

/**
 * Load Cache
 */
require_once(THEME_DIR . '/themify/class-themify-cache.php');

/**
 * Load Shortcodes
 * @since 1.1.3
 */
require_once(THEME_DIR . '/themify/themify-shortcodes.php');

/**
 * Load Page Builder
 * @since 1.1.3
 */
require_once( THEMIFY_DIR . '/themify-builder/themify-builder.php' );

/**
 * Load Customizer
 * @since 1.8.2
 */
require_once THEMIFY_DIR . '/customizer/class-themify-customizer.php';

/**
 * Load Schema.org Microdata
 * @since 2.6.5
 */
require_once THEMIFY_DIR . '/themify-microdata.php';

require_once THEMIFY_DIR . '/themify-wp-filters.php';
require_once THEMIFY_DIR . '/themify-plugin-compatibility.php';
require_once THEMIFY_DIR . '/themify-template-tags.php';

/**
 * Enqueue framework CSS Stylesheets:
 * 1. themify-skin
 * 2. custom-style
 * 3. fontawesome - added 1.7.8
 *
 * @since 1.7.4
 */
add_action( 'wp_enqueue_scripts', 'themify_enqueue_framework_assets', 12 );

/**
 * Output module styling and Custom CSS:
 * 1. module styling
 * 2. Custom CSS
 */
add_action( 'wp_head', 'themify_output_framework_styling' );

/**
 * Themify - Insert settings page link in WP Admin Bar
 * @since 1.1.2
 */
add_action( 'wp_before_admin_bar_render', 'themify_admin_bar' );

/**
 * Protected meta fields
 */
add_action( 'admin_init', 'themify_compile_protected_meta_list' );
add_filter( 'is_protected_meta', 'themify_protected_meta', 10, 3 );

/**
 * Menu Icons
 */
add_action( 'init', 'themify_setup_menu_icons' );

/**
 * Sets the WP Featured Image size selected for Query Category pages
 */
add_action( 'template_redirect', 'themify_feature_size_page' );

/**
 * Outputs html to display alert messages in post edit/new screens. Excludes pages.
 */
add_action( 'admin_notices', 'themify_prompt_message' );

/**
 * Load Google fonts library
 */
add_action( 'wp_enqueue_scripts', 'themify_enqueue_gfonts' );

/**
 * Add "js" classname to html element when JavaScript is enabled
 */
add_action( 'wp_head', 'themify_html_js_class', 0 );

/**
 * Allows to query by category slug or id
 */
add_filter( 'themify_query_posts_page_args', 'themify_framework_query_posts_page_args' );

/**
 * Add different CSS classes to body tag.
 */
add_filter( 'body_class', 'themify_body_classes' );

/**
 * Adds classes to .post based on elements enabled for the currenty entry.
 */
add_filter( 'post_class', 'themify_post_class' );

/**
 * Disable responsive design based on user choice.
 */
if ( 'on' == themify_get( 'setting-disable_responsive_design' ) ) {
	add_action( 'init', 'themify_disable_responsive_design' );
}

/**
 * Enable pinch to zoom on mobile.
 */
if ( themify_get( 'setting-enable_mobile_zoom' ) == 'on' ) {
	add_action( 'init', 'themify_enable_mobile_zoom' );
}

add_filter( 'themify_builder_fullwidth_layout_support', 'themify_theme_fullwidth_layout_support' );

/**
 * Add support for feeds on the site
 */
add_theme_support( 'automatic-feed-links' );

/**
 * Load Themify Hooks
 * @since 1.2.2
 */
require_once(THEMIFY_DIR . '/themify-hooks.php' );
require_once(THEMIFY_DIR . '/class-hook-contents.php' );

/**
 * Load Themify Role Access Control
 * @since 2.6.2
 */
require_once(THEMIFY_DIR . '/class-themify-access-role.php' );

/**
* Add buttons to TinyMCE
*******************************************************/
themify_wpeditor_add_shortcodes_button();

/**
 * Admin Only code follows
 ******************************************************/
if( is_admin() ){

	/**
	 * Initialize settings page and update permissions.
	 * @since 2.1.8
	 */
	add_action( 'init', 'themify_after_user_is_authenticated' );

	/**
 	* Enqueue jQuery and other scripts
 	*******************************************************/
	add_action( 'admin_enqueue_scripts', 'themify_enqueue_scripts' );

	/**
 	* Ajaxify admin
 	*******************************************************/
	require_once(THEMIFY_DIR . '/themify-wpajax.php');
}

/**
 * In this hook current user is authenticated so we can check for capabilities.
 *
 * @since 2.1.8
 */
function themify_after_user_is_authenticated() {
	if ( current_user_can( 'manage_options' ) ) {
		require_once THEMIFY_DIR . '/themify-admin.php';

		/**
	 	 * Themify - Admin Menu
	 	 *******************************************************/
		add_action( 'admin_menu', 'themify_admin_nav' );

		/**
		 * Themify Updater - In multisite, it's only available to super admins.
		 **********************************************************************/
		if ( themify_allow_update() ) {
			require_once THEMIFY_DIR . '/themify-updater.php';
		}
	}
}

/**
 * Add Themify Settings link to admin bar
 * @since 1.1.2
 */
function themify_admin_bar() {
	global $wp_admin_bar;
	if ( !is_super_admin() || !is_admin_bar_showing() )
		return;
	$wp_admin_bar->add_menu( array(
		'id' => 'themify-settings',
		'parent' => 'appearance',
		'title' => __( 'Themify Settings', 'themify' ),
		'href' => admin_url( 'admin.php?page=themify' )
	));
}

/**
 * Clear legacy themify-ajax.php and strange files that might have been uploaded to or directories created in the uploads folder within the theme.
 * @since 1.6.3
 */
function themify_maybe_clear_legacy() {
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	WP_Filesystem();
	global $wp_filesystem;

	$flag = 'themify_clear_legacy';
	$clear = get_option( $flag );
	if ( ! isset( $clear ) || ! $clear ) {
		$legacy = THEMIFY_DIR . '/themify-ajax.php';
		if ( $exists = $wp_filesystem->exists( $legacy ) ) {
			$wp_filesystem->delete( $legacy );
		}
		$list = $wp_filesystem->dirlist( THEME_DIR . '/uploads/', true, true );
		if ( is_array( $list ) ) {
			foreach ( $list as $item ) {
				if ( 'd' == $item['type'] ) {
					foreach ( $item['files'] as $subitem ) {
						if ( 'd' == $subitem['type'] ) {
							// There shouldn't be a directory here, let's delete it
							$del_dir = THEME_DIR . '/uploads/' . $item['name'] . '/' . $subitem['name'];
							$wp_filesystem->delete( $del_dir, true );
						} else {
							$extension = pathinfo( $subitem['name'], PATHINFO_EXTENSION );
							if ( ! in_array( $extension, array( 'jpg', 'gif', 'png', 'jpeg', 'bmp' ) ) ) {
								$del_file = THEME_DIR . '/uploads/' . $item['name'] . '/' . $subitem['name'];
								$wp_filesystem->delete( $del_file );
							}
						}
					}
				} else {
					$extension = pathinfo( $item['name'], PATHINFO_EXTENSION );
					if ( ! in_array( $extension, array( 'jpg', 'gif', 'png', 'jpeg', 'bmp' ) ) ) {
						$del_file = THEME_DIR . '/uploads/' . $item['name'];
						$wp_filesystem->delete( $del_file );
					}
				}
			}
		}
		update_option( $flag, true );
	}
}
add_action( 'init', 'themify_maybe_clear_legacy', 9 );

/**
 * Change setting name where theme settings are stored.
 * Runs after updater succeeded.
 * @since 1.7.6
 */
function themify_migrate_settings_name() {
	$flag = 'themify_migrate_settings_name';
	$change = get_option( $flag );
	if ( ! isset( $change ) || ! $change ) {
		if ( $themify_data = get_option( wp_get_theme()->display('Name') . '_themify_data' ) ) {
			themify_set_data( $themify_data );
		}
		update_option( $flag, true );
	}
}
add_action( 'after_setup_theme', 'themify_migrate_settings_name', 1 );

/**
 * Function called after a successful update through WP Admin.
 * Code to run ONLY ONCE after update must be added here.
 *
 * @since 1.8.3
 */
function themify_theme_updater_post_install() {
	// Delete option to reset styling behaviour
	delete_option( 'themify_has_styling_data' );

	// Once all tasks have been executed, delete the flag.
	delete_option( 'themify_update_ok_flag' );
}
add_action( 'themify_updater_post_install', 'themify_theme_updater_post_install' );

/**
 * Load files to add the shortcode button to WP Editor
 *
 * @since 1.8.9
 */
function themify_wpeditor_add_shortcodes_button() {
	require_once THEMIFY_DIR . '/tinymce/class-themify-tinymce.php';
}

/**
 * Refresh permalinks to avoid 404 on custom post type fetching.
 * @since 1.9.3
 */
function themify_flush_rewrite_rules_after_manual_update() {
	$flag = 'themify_flush_rewrite_rules_after_manual_update';
	$change = get_option( $flag );
	if ( ! isset( $change ) || ! $change ) {
		flush_rewrite_rules();
		update_option( $flag, true );
	}
}
add_action( 'init', 'themify_flush_rewrite_rules_after_manual_update', 99 );
