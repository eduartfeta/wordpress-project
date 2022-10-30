<?php
/**
 * Smoke - a visual effect with a smoke follow the mouse
 *
 * @addon smoke
 * @version 1.0
 *
 * @package ThemeREX Addons
 * @since v2.9.0
 */


// Load required styles and scripts for the frontend
if ( ! function_exists( 'trx_addons_smoke_load_scripts_front' ) ) {
	function trx_addons_smoke_load_scripts_front( $type ) {
		static $loaded = false;
		if ( ! $loaded ) {
			$loaded = true;
			wp_enqueue_style(  'trx_addons-smoke', trx_addons_get_file_url( TRX_ADDONS_PLUGIN_ADDONS . 'smoke/smoke.css' ), array(), null );
			if ( $type == 'smoke' ) {
				wp_enqueue_script( 'trx_addons-smoke-smoke', trx_addons_get_file_url( TRX_ADDONS_PLUGIN_ADDONS . 'smoke/smoke.js' ), array('jquery'), null, true );
			} else if ( $type == 'fog' ) {
				wp_enqueue_script( 'three', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', array(), null, true );
				wp_enqueue_script( 'trx_addons-smoke-fog', trx_addons_get_file_url( TRX_ADDONS_PLUGIN_ADDONS . 'smoke/fog.js' ), array('jquery'), null, true );
			} else if ( $type == 'spots' ) {
				wp_enqueue_script( 'three', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', array(), null, true );
				wp_enqueue_script( 'trx_addons-smoke-back', trx_addons_get_file_url( TRX_ADDONS_PLUGIN_ADDONS . 'smoke/spots.js' ), array('jquery'), null, true );
			}
			do_action( 'trx_addons_action_smoke_load_scripts_front', $type );
		}
	}
}

// Return list of chart types
if ( ! function_exists( 'trx_addons_smoke_list_types' ) ) {
	function trx_addons_smoke_list_types() {
		return apply_filters( 'trx_addons_filter_smoke_types', array(
								'smoke' => esc_html__( 'Smoke', 'trx_addons' ),
								'fog'   => esc_html__( 'Fog', 'trx_addons' ),
								'spots' => esc_html__( 'Spots', 'trx_addons' )
							) );
	}
}


// trx_sc_smoke
//-------------------------------------------------------------
/*
[trx_sc_smoke id="unique_id" type="smoke"]
*/
if ( ! function_exists( 'trx_addons_sc_smoke' ) ) {
	function trx_addons_sc_smoke( $atts, $content = null ) {
		$defa = trx_addons_sc_common_atts( '', array(
			// Individual params
			"type" => 'smoke',
			"use_image" => 0,
			"image" => '',
			"image_repeat" => 5,
			"bg_color" => '#000000',
			"tint_color" => '',
			"cursor" => '',
			"smoke_curls" => 5,
			"smoke_density" => 0.97,
			"smoke_velosity" => 0.98,
			"smoke_pressure" => 0.8,
			"smoke_iterations" => 10,
			"smoke_slap" => 0.6,
			"spots" => ''
		) );

		$atts = trx_addons_sc_prepare_atts( 'trx_sc_smoke', $atts, $defa );

		$output = '';

		// Prevent multiple instances
		static $single_output = false;
		if ( ! $single_output ) {
			$single_output = true;

			// Load addon-specific scripts and styles
			trx_addons_smoke_load_scripts_front( $atts['type'] );

			// Prepare image
			$image = '';
			if ( (int)$atts['use_image'] > 0 && ! empty( $atts['image'] ) ) {
				$image = trx_addons_get_attachment_url(
							$atts['image'],
							apply_filters( 'trx_addons_filter_thumb_size',
											trx_addons_get_thumb_size( $atts['type'] == 'fog' ? 'masonry' : 'masonry' ),
											"smoke-{$atts['type']}-image}"
										)
						);
			} else if ( $atts['type'] == 'fog' ) {
				$image = trx_addons_get_file_url( TRX_ADDONS_PLUGIN_ADDONS . 'smoke/images/fog.png' );
			} else if ( (int)$atts['use_image'] > 0 ) {
				$image = trx_addons_get_file_url( TRX_ADDONS_PLUGIN_ADDONS . 'smoke/images/smoke.png' );
			}
			// Prepare bg color
			if (   empty( $atts['bg_color'] )
				|| strpos( $atts['bg_color'], 'rgb' ) !== false
				|| strpos( $atts['bg_color'], 'hsv' ) !== false
				|| strpos( $atts['bg_color'], 'var' ) !== false
			) {
				$atts['bg_color'] = '#000000';
			}
			// Prepare tint color
			if ( $atts['type'] == 'fog'
				&& (   empty(  $atts['tint_color'] )
					|| strpos( $atts['tint_color'], 'rgb' ) !== false
					|| strpos( $atts['tint_color'], 'hsv' ) !== false
					|| strpos( $atts['tint_color'], 'var' ) !== false
					)
			) {
				$atts['tint_color'] = '#000000';
			}

			// Add a smoke layout
			trx_addons_add_inline_html( apply_filters( 'trx_addons_filter_smoke_layout',
				'<canvas id="trx_addons_smoke" data-trx-addons-smoke="'
					. esc_attr( json_encode( apply_filters( 'trx_addons_filter_smoke_args', array(
								'type'       => $atts['type'],
								'bg_color'   => $atts['bg_color'],
								'tint_color' => $atts['tint_color'],
								'smoke_curls' => ! empty( $atts['smoke_curls'] ) ? max( 1, min( 20, (int)$atts['smoke_curls'] ) ) : 5,
								'smoke_density' => ! empty( $atts['smoke_density'] ) ? max( 0.1, min( 1.0, (float)$atts['smoke_density'] ) ) : 0.97,
								'smoke_velosity' => ! empty( $atts['smoke_velosity'] ) ? max( 0.1, min( 1.0, (float)$atts['smoke_velosity'] ) ) : 0.98,
								'smoke_pressure' => ! empty( $atts['smoke_pressure'] ) ? max( 0.1, min( 1.0, (float)$atts['smoke_pressure'] ) ) : 0.8,
								'smoke_iterations' => ! empty( $atts['smoke_iterations'] ) ? max( 1, min( 20, (int)$atts['smoke_iterations'] ) ) : 10,
								'smoke_slap' => ! empty( $atts['smoke_slap'] ) ? max( 0.1, min( 1.0, (float)$atts['smoke_slap'] ) ) : 0.6,
								'use_image'  => (int)$atts['use_image'] > 0,
								'image'      => esc_url( $image ),	//trx_addons_add_protocol( $image ),
								'image_repeat' => max( 1, min( 20, (int)$atts['image_repeat'] ) ),
								'spots' => ! empty( $atts['spots'] ) ? $atts['spots'] : array()
								) ) ) )
				. '"></canvas>'
			) );

			// Add styles for cursor
			if ( ! empty( $atts['cursor'] ) ) {
				trx_addons_add_inline_css(
'
@media (min-width: 1280px) {
	body.trx_addons_smoke_present {
		cursor: url(' . esc_url( trx_addons_get_attachment_url( $atts['cursor'] ) ) . '), auto;
	}
	body.trx_addons_smoke_present header,
	body.trx_addons_smoke_present footer {
		cursor: auto;
	}
}
'
				);
			}
		}

		return apply_filters('trx_addons_sc_output', $output, 'trx_sc_smoke', $atts, $content);
	}
}


// Add shortcode [trx_sc_smoke]
if ( ! function_exists( 'trx_addons_sc_smoke_add_shortcode' ) ) {
	function trx_addons_sc_smoke_add_shortcode() {
		add_shortcode( "trx_sc_smoke", "trx_addons_sc_smoke" );
	}
	add_action( 'init', 'trx_addons_sc_smoke_add_shortcode', 20 );
}


// Add shortcodes
//----------------------------------------------------------------------------

// Add shortcodes to Elementor
if ( trx_addons_exists_elementor() && function_exists( 'trx_addons_elm_init' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_ADDONS . 'smoke/smoke-sc-elementor.php';
}

// Add shortcodes to Gutenberg
if ( trx_addons_exists_gutenberg() && function_exists( 'trx_addons_gutenberg_get_param_id' ) ) {
	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_ADDONS . 'smoke/smoke-sc-gutenberg.php';
}

// Add shortcodes to VC
if ( trx_addons_exists_vc() && function_exists( 'trx_addons_vc_add_id_param' ) ) {
//	require_once TRX_ADDONS_PLUGIN_DIR . TRX_ADDONS_PLUGIN_ADDONS . 'smoke/smoke-sc-vc.php';
}
