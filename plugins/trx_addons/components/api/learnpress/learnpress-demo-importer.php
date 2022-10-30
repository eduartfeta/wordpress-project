<?php
/**
 * Plugin support: LearnPress (Importer support)
 *
 * @package ThemeREX Addons
 * @since v1.6.62
 */

// Don't load directly
if ( ! defined( 'TRX_ADDONS_VERSION' ) ) {
	exit;
}


// Check plugin in the required plugins
if ( !function_exists( 'trx_addons_learnpress_importer_required_plugins' ) ) {
	if (is_admin()) add_filter( 'trx_addons_filter_importer_required_plugins',	'trx_addons_learnpress_importer_required_plugins', 10, 2 );
	function trx_addons_learnpress_importer_required_plugins($not_installed='', $list='') {
		if (strpos($list, 'learnpress')!==false && !trx_addons_exists_learnpress() ) {
			$not_installed .= '<br>' . esc_html__('learnpress', 'trx_addons');
		}
		return $not_installed;
	}
}

// Set plugin's specific importer options
if ( !function_exists( 'trx_addons_learnpress_importer_set_options' ) ) {
	if (is_admin()) add_filter( 'trx_addons_filter_importer_options',	'trx_addons_learnpress_importer_set_options' );
	function trx_addons_learnpress_importer_set_options($options=array()) {
		if ( trx_addons_exists_learnpress() && in_array('learnpress', $options['required_plugins']) ) {
			$options['additional_options'][]	= 'learn_press_%';					// Add slugs to export options for this plugin

			if (is_array($options['files']) && count($options['files']) > 0) {
				foreach ($options['files'] as $k => $v) {
					$options['files'][$k]['file_with_learnpress'] = str_replace('name.ext', 'learnpress.txt', $v['file_with_']);
				}
			}
		}
		return $options;
	}
}

// Prevent import plugin's specific options if plugin is not installed
if ( !function_exists( 'trx_addons_learnpress_importer_check_options' ) ) {
	add_filter( 'trx_addons_filter_import_theme_options', 'trx_addons_learnpress_importer_check_options', 10, 4 );
	function trx_addons_learnpress_importer_check_options($allow, $k, $v, $options) {
		if ($allow && strpos($k, 'learn_press_')===0) {
			$allow = trx_addons_exists_learnpress() && in_array('learnpress', $options['required_plugins']);
		}
		return $allow;
	}
}

// Add checkbox to the one-click importer
if ( !function_exists( 'trx_addons_learnpress_importer_show_params' ) ) {
	if (is_admin()) add_action( 'trx_addons_action_importer_params',	'trx_addons_learnpress_importer_show_params', 10, 1 );
	function trx_addons_learnpress_importer_show_params($importer) {
		if ( trx_addons_exists_learnpress() && in_array('learnpress', $importer->options['required_plugins']) ) {
			$importer->show_importer_params(array(
				'slug' => 'learnpress',
				'title' => esc_html__('Import LearnPress', 'trx_addons'),
				'part' => 0
			));
		}
	}
}

// Import posts
if ( !function_exists( 'trx_addons_learnpress_importer_import' ) ) {
	if (is_admin()) add_action( 'trx_addons_action_importer_import',	'trx_addons_learnpress_importer_import', 10, 2 );
	function trx_addons_learnpress_importer_import($importer, $action) {
		if ( trx_addons_exists_learnpress() && in_array('learnpress', $importer->options['required_plugins']) ) {
			if ( $action == 'import_learnpress' ) {
				$importer->response['start_from_id'] = 0;
				$importer->import_dump('learnpress', esc_html__('LearPress meta', 'trx_addons'));
			}
		}
	}
}

// Check if the row will be imported
if ( !function_exists( 'trx_addons_learnpress_importer_check_row' ) ) {
	if (is_admin()) add_filter('trx_addons_filter_importer_import_row', 'trx_addons_learnpress_importer_check_row', 9, 4);
	function trx_addons_learnpress_importer_check_row($flag, $table, $row, $list) {
		if ($flag || strpos($list, 'learnpress')===false) return $flag;
		if ( trx_addons_exists_learnpress() ) {
			if ($table == 'posts') {
				$flag = in_array($row['post_type'], array(LP_COURSE_CPT, LP_LESSON_CPT, LP_QUESTION_CPT, LP_QUIZ_CPT, LP_ORDER_CPT));
			}
		}
		return $flag;
	}
}

// Display import progress
if ( !function_exists( 'trx_addons_learnpress_importer_import_fields' ) ) {
	if (is_admin()) add_action( 'trx_addons_action_importer_import_fields',	'trx_addons_learnpress_importer_import_fields', 10, 1 );
	function trx_addons_learnpress_importer_import_fields($importer) {
		if ( trx_addons_exists_learnpress() && in_array('learnpress', $importer->options['required_plugins']) ) {
			$importer->show_importer_fields(array(
				'slug'=>'learnpress', 
				'title' => esc_html__('LearnPress meta', 'trx_addons')
				)
			);
		}
	}
}

// Export posts
if ( !function_exists( 'trx_addons_learnpress_importer_export' ) ) {
	if (is_admin()) add_action( 'trx_addons_action_importer_export',	'trx_addons_learnpress_importer_export', 10, 1 );
	function trx_addons_learnpress_importer_export($importer) {
		if ( trx_addons_exists_learnpress() && in_array('learnpress', $importer->options['required_plugins']) ) {
			trx_addons_fpc($importer->export_file_dir('learnpress.txt'), serialize( apply_filters( 'trx_addons_filter_importer_export_tables', array(
				"learnpress_order_items"			=> $importer->export_dump("learnpress_order_items"),
				"learnpress_order_itemmeta"			=> $importer->export_dump("learnpress_order_itemmeta"),
				"learnpress_question_answers"		=> $importer->export_dump("learnpress_question_answers"),
				"learnpress_question_answermeta"	=> $importer->export_dump("learnpress_question_answers"),
				"learnpress_quiz_questions"			=> $importer->export_dump("learnpress_quiz_questions"),
				"learnpress_review_logs"			=> $importer->export_dump("learnpress_review_logs"),
				"learnpress_sections"				=> $importer->export_dump("learnpress_sections"),
				"learnpress_section_items"			=> $importer->export_dump("learnpress_section_items"),
				"learnpress_sessions"				=> $importer->export_dump("learnpress_sessions"),
				"learnpress_user_items"				=> $importer->export_dump("learnpress_user_items"),
				"learnpress_user_itemmeta"			=> $importer->export_dump("learnpress_user_itemmeta"),
				), 'learnpress' ) )
			);
		}
	}
}

// Display exported data in the fields
if ( !function_exists( 'trx_addons_learnpress_importer_export_fields' ) ) {
	if (is_admin()) add_action( 'trx_addons_action_importer_export_fields',	'trx_addons_learnpress_importer_export_fields', 10, 1 );
	function trx_addons_learnpress_importer_export_fields($importer) {
		if ( trx_addons_exists_learnpress() && in_array('learnpress', $importer->options['required_plugins']) ) {
			$importer->show_exporter_fields(array(
				'slug'	=> 'learnpress',
				'title' => esc_html__('LearnPress', 'trx_addons')
				)
			);
		}
	}
}
