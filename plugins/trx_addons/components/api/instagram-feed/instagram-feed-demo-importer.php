<?php
/**
 * Plugin support: Instagram Feed (Importer supoort)
 *
 * @package ThemeREX Addons
 * @since v1.5
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


// Check plugin in the required plugins
if ( !function_exists( 'trx_addons_instagram_feed_importer_required_plugins' ) ) {
	add_filter( 'trx_addons_filter_importer_required_plugins',	'trx_addons_instagram_feed_importer_required_plugins', 10, 2 );
	function trx_addons_instagram_feed_importer_required_plugins($not_installed='', $list='') {
		if (strpos($list, 'instagram-feed')!==false && !trx_addons_exists_instagram_feed() ) {
			$not_installed .= '<br>' . esc_html__('Instagram Feed', 'trx_addons');
		}
		return $not_installed;
	}
}

// Set plugin's specific importer options
if ( !function_exists( 'trx_addons_instagram_feed_importer_set_options' ) ) {
	add_filter( 'trx_addons_filter_importer_options',	'trx_addons_instagram_feed_importer_set_options' );
	function trx_addons_instagram_feed_importer_set_options($options=array()) {
		if (trx_addons_exists_instagram_feed() && in_array('instagram-feed', $options['required_plugins'])) {
			if (is_array($options)) {
				$options['additional_options'][] = 'sb_instagram_settings';		// Add slugs to export options for this plugin
			}
			if (is_array($options['files']) && count($options['files']) > 0) {
				foreach ($options['files'] as $k => $v) {
					$options['files'][$k]['file_with_instagram-feed'] = str_replace('name.ext', 'instagram-feed.txt', $v['file_with_']);
				}
			}
		}
		return $options;
	}
}

// Prevent import plugin's specific options if plugin is not installed
if ( !function_exists( 'trx_addons_instagram_feed_importer_check_options' ) ) {
	add_filter( 'trx_addons_filter_import_theme_options', 'trx_addons_instagram_feed_importer_check_options', 10, 4 );
	function trx_addons_instagram_feed_importer_check_options($allow, $k, $v, $options) {
		if ($allow && $k == 'sb_instagram_settings') {
			$allow = trx_addons_exists_instagram_feed() && in_array('instagram-feed', $options['required_plugins']);
		}
		return $allow;
	}
}

// Clear some plugin's specific options before export
if ( !function_exists( 'trx_addons_instagram_feed_importer_export_options' ) ) {
	add_filter( 'trx_addons_filter_export_options', 'trx_addons_instagram_feed_importer_export_options' );
	function trx_addons_instagram_feed_importer_export_options($options) {
		if ( ! empty( $options['sb_instagram_settings']['license-key'] ) ) {
			$options['sb_instagram_settings']['license-key'] = array();
		}
		if ( ! empty( $options['sb_instagram_settings']['connected_accounts'] ) ) {
			$options['sb_instagram_settings']['connected_accounts'] = array();
		}
		return $options;
	}
}

// Add checkbox to the one-click importer
if ( !function_exists( 'trx_addons_instagram_feed_importer_show_params' ) ) {
	add_action( 'trx_addons_action_importer_params',	'trx_addons_instagram_feed_importer_show_params', 10, 1 );
	function trx_addons_instagram_feed_importer_show_params($importer) {
		if ( trx_addons_exists_instagram_feed() && in_array('instagram-feed', $importer->options['required_plugins']) ) {
			$importer->show_importer_params(array(
				'slug' => 'instagram-feed',
				'title' => esc_html__('Import Instagram Feed', 'trx_addons'),
				'part' => 1
			));
		}
	}
}

// Import posts
if ( !function_exists( 'trx_addons_instagram_feed_importer_import' ) ) {
	add_action( 'trx_addons_action_importer_import',	'trx_addons_instagram_feed_importer_import', 10, 2 );
	function trx_addons_instagram_feed_importer_import($importer, $action) {
		if ( trx_addons_exists_instagram_feed() && in_array('instagram-feed', $importer->options['required_plugins']) ) {
			if ( $action == 'import_instagram-feed' ) {
				$importer->response['start_from_id'] = 0;
				$importer->import_dump('instagram-feed', esc_html__('Instagram Feed', 'trx_addons'));
			}
		}
	}
}

// Display import progress
if ( !function_exists( 'trx_addons_instagram_feed_importer_import_fields' ) ) {
	add_action( 'trx_addons_action_importer_import_fields',	'trx_addons_instagram_feed_importer_import_fields', 10, 1 );
	function trx_addons_instagram_feed_importer_import_fields($importer) {
		if ( trx_addons_exists_instagram_feed() && in_array('instagram-feed', $importer->options['required_plugins']) ) {
			$importer->show_importer_fields(array(
				'slug'	=> 'instagram-feed', 
				'title'	=> esc_html__('Instagram Feed', 'trx_addons')
				)
			);
		}
	}
}

// Export posts
if ( !function_exists( 'trx_addons_instagram_feed_importer_export' ) ) {
	add_action( 'trx_addons_action_importer_export',	'trx_addons_instagram_feed_importer_export', 10, 1 );
	function trx_addons_instagram_feed_importer_export($importer) {
		if ( trx_addons_exists_instagram_feed() && in_array('instagram-feed', $importer->options['required_plugins']) ) {
			trx_addons_fpc($importer->export_file_dir('instagram-feed.txt'), serialize( apply_filters( 'trx_addons_filter_importer_export_tables', array(
				'sbi_feeds' => $importer->export_dump('sbi_feeds'),
				'sbi_feed_caches' => $importer->export_dump('sbi_feed_caches'),
				'sbi_instagram_feeds_posts' => $importer->export_dump('sbi_instagram_feeds_posts'),
				'sbi_instagram_feed_locator' => $importer->export_dump('sbi_instagram_feed_locator'),
				'sbi_instagram_posts' => $importer->export_dump('sbi_instagram_posts'),
				'sbi_sources' => $importer->export_dump('sbi_sources'),
				), 'instagram-feed' ) )
			);
		}
	}
}

// Display exported data in the fields
if ( !function_exists( 'trx_addons_instagram_feed_importer_export_fields' ) ) {
	add_action( 'trx_addons_action_importer_export_fields',	'trx_addons_instagram_feed_importer_export_fields', 10, 1 );
	function trx_addons_instagram_feed_importer_export_fields($importer) {
		if ( trx_addons_exists_instagram_feed() && in_array('instagram-feed', $importer->options['required_plugins']) ) {
			$importer->show_exporter_fields(array(
				'slug'	=> 'instagram-feed',
				'title' => esc_html__('Instagram Feed', 'trx_addons')
				)
			);
		}
	}
}
