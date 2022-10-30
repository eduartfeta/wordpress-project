<?php
/**
 * Plugin support: LearnPress
 *
 * @package ThemeREX Addons
 * @since v1.6.62
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}

// Check if plugin installed and activated
if ( !function_exists( 'trx_addons_exists_learnpress' ) ) {
	function trx_addons_exists_learnpress() {
		return class_exists('LearnPress');
	}
}

// Return true, if current page is Give plugin's page
if ( !function_exists( 'trx_addons_is_learnpress_page' ) ) {
	function trx_addons_is_learnpress_page() {
		$rez = false;
		if ( trx_addons_exists_learnpress() && ! is_search() ) {
			$rez = is_learnpress();
		}
		return $rez;
	}
}


// Change rewrite slug of internal courses to avoid conflicts with Learn Press
//----------------------------------------------------------------------------
if ( !function_exists( 'trx_addons_learnpress_change_courses_slug' ) ) {
	add_filter('trx_addons_cpt_list', 'trx_addons_learnpress_change_courses_slug');
	function trx_addons_learnpress_change_courses_slug($list) {
		if ( ! empty($list['courses']['post_type_slug']) && $list['courses']['post_type_slug'] == 'courses' ) {
			$list['courses']['post_type_slug'] = 'cpt_courses';
		}
		return $list;
	}
}


// Additional meta fields to the course
//----------------------------------------------------------------------------

// Add video and additional info to the course meta box
if ( ! function_exists( 'trx_addons_learnpress_add_fields' ) ) {
	add_filter( 'learn_press_course_settings_meta_box_args', 'trx_addons_learnpress_add_fields' );
	function trx_addons_learnpress_add_fields( $meta_box ) {
		$meta_box['fields'][] = array(
			'name' => __( 'Intro video (local)', 'trx_addons' ),
			'desc' => __( 'Video-presentation of the course uploaded to your site.', 'trx_addons' ),
			'id'   => '_lp_intro_video',
			'type' => 'video',
			'std'  => ''
		);
		$meta_box['fields'][] = array(
			'name' => __( 'Intro video (external)', 'trx_addons' ),
			'desc' => __( 'or specify url of the video-presentation from popular video hosting (like Youtube, Vimeo, etc.)', 'trx_addons' ),
			'id'   => '_lp_intro_video_external',
			'type' => 'text',
			'std'  => ''
		);
		$meta_box['fields'][] = array(
			'name' => __( 'Includes', 'trx_addons' ),
			'desc' => __( 'List of includes of the course.', 'trx_addons' ),
			'id'   => '_lp_course_includes',
			'type' => 'wysiwyg',
			'std'  => ''
		);
		return $meta_box;
	}
}

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_learnpress_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_learnpress_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_learnpress_load_scripts_front', 10, 1 );
	function trx_addons_learnpress_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_learnpress() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_is_learnpress_page()
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'learnpress',
									'entries' => array(
										array( 'type' => 'sc',  'sc' => 'confirm_order' ),
										array( 'type' => 'sc',  'sc' => 'profile' ),
										array( 'type' => 'sc',  'sc' => 'become_teacher_form' ),
										array( 'type' => 'sc',  'sc' => 'login_form' ),
										array( 'type' => 'sc',  'sc' => 'register_form' ),
										array( 'type' => 'sc',  'sc' => 'checkout' ),
										array( 'type' => 'sc',  'sc' => 'recent_courses' ),
										array( 'type' => 'sc',  'sc' => 'featured_courses' ),
										array( 'type' => 'sc',  'sc' => 'popular_courses' ),
										array( 'type' => 'sc',  'sc' => 'button_enroll' ),
										array( 'type' => 'sc',  'sc' => 'button_purchase' ),
										array( 'type' => 'sc',  'sc' => 'button_course' ),
										array( 'type' => 'sc',  'sc' => 'course_curriculum' ),
										array( 'type' => 'sc',  'sc' => 'learn_press_archive_course' ),
										//array( 'type' => 'gb',  'sc' => 'wp:trx-addons/events' ),	// This sc is not exists for GB
										array( 'type' => 'elm', 'sc' => '"widgetType":"wp-widget-learnpress_widget_' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[confirm_order' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[profile' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[become_teacher_form' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[login_form' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[register_form' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[checkout' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[recent_courses' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[featured_courses' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[popular_courses' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[button_enroll' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[button_purchase' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[button_course' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[course_curriculum' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[learn_press_archive_course' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			do_action( 'trx_addons_action_load_scripts_front', $force, 'learnpress' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'learnpress', 2 );
		}
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_learnpress_check_in_html_output' ) ) {
	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_learnpress_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_learnpress_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_learnpress_check_in_html_output', 10, 1 );
	function trx_addons_learnpress_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_learnpress()
			&& ! trx_addons_need_frontend_scripts( 'learnpress' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							'class=[\'"][^\'"]*learnpress',
							'id=[\'"][^\'"]*learnpress',
							'class=[\'"][^\'"]*type\\-(lp_course|lp_lesson|lp_question|lp_quiz|lp_order)',
							'class=[\'"][^\'"]*(course_category|course_tag|question_tag)\\-',
							),
							'learnpress'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_learnpress_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'learnpress/learnpress-demo-importer.php';
}
