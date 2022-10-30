<?php
/**
 * Plugin support: BBPress and BuddyPress
 *
 * @package ThemeREX Addons
 * @since v1.5
 */

// Check if BBPress and BuddyPress is installed and activated
if ( !function_exists( 'trx_addons_exists_bbpress' ) ) {
	function trx_addons_exists_bbpress() {
		return class_exists( 'BuddyPress' ) || class_exists( 'bbPress' );
	}
}

// Return true, if current page is any bbpress page
if ( !function_exists( 'trx_addons_is_bbpress_page' ) ) {
	function trx_addons_is_bbpress_page() {
		$rez = false;
		if (trx_addons_exists_bbpress()) {
			if (!is_search()) {
				$rez = ( function_exists('is_buddypress') && is_buddypress() ) 
					|| ( function_exists('is_bbpress') && is_bbpress() )
					|| ( ! is_user_logged_in() && in_array( get_query_var('post_type'), array('forum', 'topic', 'reply') ) );
			}
		}
		return $rez;
	}
}

// Return link to the main bbpress page for the breadcrumbs
if ( !function_exists( 'trx_addons_bbpress_get_blog_all_posts_link' ) ) {
	add_filter('trx_addons_filter_get_blog_all_posts_link', 'trx_addons_bbpress_get_blog_all_posts_link', 10, 2);
	function trx_addons_bbpress_get_blog_all_posts_link($link='', $args=array()) {
		if ($link=='' && trx_addons_is_bbpress_page() && function_exists('bbp_get_forum_post_type')) {
			// Page exists at root slug path, so use its permalink
			$page = bbp_get_page_by_path( bbp_get_root_slug() );
			$pt = bbp_get_forum_post_type();
			$obj = get_post_type_object($pt);
			if (($url = !empty( $page ) ? get_permalink( $page->ID ) : get_post_type_archive_link($pt)) !='')
				$link = '<a href="'.esc_url($url).'">' . esc_html($obj->labels->all_items) . '</a>';
		}
		return $link;
	}
}


// Remove taxonomy 'topic_tag' from breadcrumbs
if ( !function_exists( 'trx_addons_bbpress_post_type_taxonomy' ) ) {
	add_filter( 'trx_addons_filter_post_type_taxonomy',	'trx_addons_bbpress_post_type_taxonomy', 10, 2 );
	function trx_addons_bbpress_post_type_taxonomy($tax='', $post_type='') {
		if (trx_addons_exists_bbpress() 
			&& function_exists('bbp_get_topic_post_type')
			&& $post_type == bbp_get_topic_post_type()
			&& $tax == bbp_get_topic_tag_tax_id())
			$tax = '';
		return $tax;
	}
}

// Load required styles and scripts for the frontend
if ( !function_exists( 'trx_addons_bbpress_load_scripts_front' ) ) {
	add_action("wp_enqueue_scripts", 'trx_addons_bbpress_load_scripts_front', TRX_ADDONS_ENQUEUE_SCRIPTS_PRIORITY);
	add_action( 'trx_addons_action_pagebuilder_preview_scripts', 'trx_addons_bbpress_load_scripts_front', 10, 1 );
	function trx_addons_bbpress_load_scripts_front( $force = false ) {
		static $loaded = false;
		if ( ! trx_addons_exists_bbpress() ) return;
		$debug    = trx_addons_is_on( trx_addons_get_option( 'debug_mode' ) );
		$optimize = ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) );
		$preview_elm = trx_addons_is_preview( 'elementor' );
		$preview_gb  = trx_addons_is_preview( 'gutenberg' );
		$theme_full  = current_theme_supports( 'styles-and-scripts-full-merged' );
		$need        = ! $loaded && ( ! $preview_elm || $debug ) && ! $preview_gb && $optimize && (
						$force === true
							|| ( $preview_elm && $debug )
							|| trx_addons_is_bbpress_page()
							|| trx_addons_sc_check_in_content( array(
									'sc' => 'bbpress',
									'entries' => array(
										// Forums
										array( 'type' => 'sc',  'sc' => 'bbp-forum-index' ),
										array( 'type' => 'sc',  'sc' => 'bbp-forum-form' ),
										array( 'type' => 'sc',  'sc' => 'bbp-single-forum' ),
										// Topics
										array( 'type' => 'sc',  'sc' => 'bbp-topic-index' ),
										array( 'type' => 'sc',  'sc' => 'bbp-topic-form' ),
										array( 'type' => 'sc',  'sc' => 'bbp-single-topic' ),
										// Topic tags
										array( 'type' => 'sc',  'sc' => 'bbp-topic-tags' ),
										array( 'type' => 'sc',  'sc' => 'bbp-single-tag' ),
										// Replies
										array( 'type' => 'sc',  'sc' => 'bbp-reply-form' ),
										array( 'type' => 'sc',  'sc' => 'bbp-single-reply' ),
										// Views
										array( 'type' => 'sc',  'sc' => 'bbp-single-view' ),
										// Search
										array( 'type' => 'sc',  'sc' => 'bbp-search-form' ),
										array( 'type' => 'sc',  'sc' => 'bbp-search' ),
										// Account
										array( 'type' => 'sc',  'sc' => 'bbp-login' ),
										array( 'type' => 'sc',  'sc' => 'bbp-register' ),
										array( 'type' => 'sc',  'sc' => 'bbp-lost-pass' ),
										// Others
										array( 'type' => 'sc',  'sc' => 'bbp-stats' ),
										array( 'type' => 'sc',  'sc' => 'bbp-' ),
										// Gutenberg blocks
										array( 'type' => 'gb',  'sc' => 'wp:bp/' ),
										array( 'type' => 'gb',  'sc' => 'wp:bbp/' ),
										// Elementor widgets
										array( 'type' => 'elm', 'sc' => '"widgetType":"wp-widget-bp' ),
										array( 'type' => 'elm', 'sc' => '"widgetType":"wp-widget-bbp' ),
										array( 'type' => 'elm', 'sc' => '"shortcode":"[bbp-' ),
									)
								) ) );
		if ( ! $loaded && ! $preview_gb && ( ( ! $optimize && $debug ) || ( $optimize && $need ) ) ) {
			$loaded = true;
			do_action( 'trx_addons_action_load_scripts_front', $force, 'bbpress' );
		}
		if ( ! $loaded && $preview_elm && $optimize && ! $debug && ! $theme_full ) {
			do_action( 'trx_addons_action_load_scripts_front', false, 'bbpress', 2 );
		}
	}
}

// Load styles and scripts if present in the cache of the menu or layouts or finally in the whole page output
if ( !function_exists( 'trx_addons_bbpress_check_in_html_output' ) ) {
//	add_filter( 'trx_addons_filter_get_menu_cache_html', 'trx_addons_bbpress_check_in_html_output', 10, 1 );
//	add_action( 'trx_addons_action_show_layout_from_cache', 'trx_addons_bbpress_check_in_html_output', 10, 1 );
	add_action( 'trx_addons_action_check_page_content', 'trx_addons_bbpress_check_in_html_output', 10, 1 );
	function trx_addons_bbpress_check_in_html_output( $content = '' ) {
		if ( trx_addons_exists_bbpress() && function_exists( 'bbp_get_forum_post_type' ) && function_exists( 'bbp_get_topic_post_type' )
			&& ! trx_addons_need_frontend_scripts( 'bbpress' )
			&& ! trx_addons_is_off( trx_addons_get_option( 'optimize_css_and_js_loading' ) )
		) {
			$checklist = apply_filters( 'trx_addons_filter_check_in_html', array(
							// BBPress
							'class=[\'"][^\'"]*(bbpress\\-|bbp_widget_|widget_display_)',
							'id=[\'"][^\'"]*bbpress',
							//BuddyPress
							'class=[\'"][^\'"]*(buddypress|widget\\-bp\\-core\\-)',
							'id=[\'"][^\'"]*buddypress',
							//Blogger with BP or BBP posts
							'class=[\'"][^\'"]*type\\-(' . bbp_get_forum_post_type() . '|' . bbp_get_topic_post_type() . ')',
							),
							'bbpress'
						);
			foreach ( $checklist as $item ) {
				if ( preg_match( "#{$item}#", $content, $matches ) ) {
					trx_addons_bbpress_load_scripts_front( true );
					break;
				}
			}
		}
		return $content;
	}
}

// Remove plugin-specific styles if present in the page head output
if ( !function_exists( 'trx_addons_bbpress_filter_head_output' ) ) {
	add_filter( 'trx_addons_filter_page_head', 'trx_addons_bbpress_filter_head_output', 10, 1 );
	function trx_addons_bbpress_filter_head_output( $content = '' ) {
		if ( trx_addons_exists_bbpress()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'bbpress' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'bbpress' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/bbpress/[^>]*>#', '', $content );
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/buddypress/[^>]*>#', '', $content );
		}
		return $content;
	}
}

// Remove plugin-specific styles and scripts if present in the page body output
if ( !function_exists( 'trx_addons_bbpress_filter_body_output' ) ) {
	add_filter( 'trx_addons_filter_page_content', 'trx_addons_bbpress_filter_body_output', 10, 1 );
	function trx_addons_bbpress_filter_body_output( $content = '' ) {
		if ( trx_addons_exists_bbpress()
			&& trx_addons_get_option( 'optimize_css_and_js_loading' ) == 'full'
			&& ! trx_addons_is_preview()
			&& ! trx_addons_need_frontend_scripts( 'bbpress' )
			&& apply_filters( 'trx_addons_filter_remove_3rd_party_styles', true, 'bbpress' )
		) {
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/bbpress/[^>]*>#', '', $content );
			$content = preg_replace( '#<script[^>]*src=[\'"][^\'"]*/bbpress/[^>]*>[\\s\\S]*</script>#U', '', $content );
			$content = preg_replace( '#<link[^>]*href=[\'"][^\'"]*/buddypress/[^>]*>#', '', $content );
			$content = preg_replace( '#<script[^>]*src=[\'"][^\'"]*/buddypress/[^>]*>[\\s\\S]*</script>#U', '', $content );
		}
		return $content;
	}
}


// Demo data install
//----------------------------------------------------------------------------

// One-click import support
if ( is_admin() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'bbpress/bbpress-demo-importer.php';
}

// OCDI support
if ( is_admin() && trx_addons_exists_bbpress() && function_exists( 'trx_addons_exists_ocdi' ) && trx_addons_exists_ocdi() ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_API . 'bbpress/bbpress-demo-ocdi.php';
}
