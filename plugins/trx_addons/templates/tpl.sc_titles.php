<?php
/**
 * The template to display shortcode's title, subtitle and description
 *
 * @package ThemeREX Addons
 * @since v1.6.08
 */

extract(get_query_var('trx_addons_args_sc_show_titles'));

// If called not from Elementor - add border and background image styles
$add_class = '';
if ( ! isset( $args['gradient_direction_extra'] ) ) {
	$border_width = ! empty( $args['title_border_width'] ) ? (int)$args['title_border_width'] : '';
	$border_color = ! empty( $args['title_border_color'] ) ? trim( $args['title_border_color'] ) : '';
	$bg_image     = ! empty( $args['title_bg_image'] ) ? trx_addons_get_attachment_url( $args['title_bg_image'] ) : '';
	if ( ! empty( $border_width ) && ! empty( $border_color ) ) {
		$add_class = 'sc_item_title_text_with_border '
					. trx_addons_add_inline_css_class(
						sprintf( '-webkit-text-stroke-color:%1$s; -webkit-text-stroke-width:%2$s;',
							$border_color,
							trx_addons_prepare_css_value( $border_width )
						)
					);
	}
	if ( ! empty( $bg_image ) ) {
		$add_class .= ( ! empty( $add_class ) ? ' ' : '' )
					. 'sc_item_title_text_with_bg_image '
					. trx_addons_add_inline_css_class(
						  '-webkit-text-fill-color: transparent;'
						. '-webkit-background-clip:text;'
						. 'background-clip:text;'
						. 'background-position:center center;'
						. 'background-image:url(' . esc_url( $bg_image ) . ');'
						);
	}
}

if (empty($args['title_align']))		$args['title_align'] = 'none';
if (empty($args['subtitle_align']))		$args['subtitle_align'] = 'none';
if (empty($args['subtitle_position']))	$args['subtitle_position'] = 'above';

$align = !trx_addons_is_off($args['title_align']) ? ' sc_align_'.trim($args['title_align']) : '';
$style = !empty($args['title_style']) ? ' sc_item_title_style_'.trim($args['title_style']) : '';

if (empty($args['title2'])) $args['title2'] = '';

$subtitle = '';
if (!empty($args['subtitle'])) {
	$subtitle_align = trx_addons_is_off($args['subtitle_align']) ? $align : ' sc_align_'.trim($args['subtitle_align']);
	$subtitle .= '<span class="' . esc_attr(apply_filters(
											'trx_addons_filter_sc_item_subtitle_class',
											'sc_item_subtitle'
											. ' ' . $sc . '_subtitle'
											. $subtitle_align
											. ' sc_item_subtitle_' . esc_attr($args['subtitle_position'])
											. $style,
											 $sc)
								)
				. '">'
					. trx_addons_prepare_macros($args['subtitle'])
				. '</span>';
}
if ($args['subtitle_position'] == 'above' && (empty($args['title']) || trx_addons_is_off($args['subtitle_align']) || $args['subtitle_align'] == $args['title_align'])) {
	trx_addons_show_layout($subtitle);
}
if (!empty($args['title'])) {
	if (!empty($args['typed']) && !empty($args['typed_strings'])) {
		// Don't process strings with 'trim' to enable one type behaviour
		$use_trim = false;
		$typed_strings = $use_trim
							? array_map( "trim", explode( "\n", strip_tags( trim( $args['typed_strings'] ) ) ) )
							: explode( "\n", strip_tags( $args['typed_strings'] ) );
		$title_key = strpos( $args['title'], $typed_strings[0] ) !== false
						? 'title'
						: ( strpos( $args['title2'], $typed_strings[0] ) !== false
							? 'title2'
							: ''
						);
		if ( ! empty( $title_key ) ) {
			wp_enqueue_script( 'typed', trx_addons_get_file_url('js/typed/typed.min.js'), array('jquery'), null, true );
			$args[ $title_key ] = str_replace(
								$typed_strings[0],
								sprintf('<span class="sc_typed_entry'
										. ( !empty($args['typed_color']) ? ' ' . trx_addons_add_inline_css_class('color: ' . esc_attr($args['typed_color']) . ' !important') : '')
										. '"'
										. ' data-strings="' . esc_attr( json_encode($typed_strings) ) . '"'
										. ' data-loop="' . esc_attr( !empty($args['typed_loop']) ? 1 : 0 ) . '"'
										. ' data-cursor="' . esc_attr( !isset($args['typed_cursor']) || !empty($args['typed_cursor']) ? 1 : 0 ) . '"'
										. ' data-cursor-char="' . esc_attr( !empty($args['typed_cursor_char']) ? $args['typed_cursor_char'] : '|' ) . '"'
										. ' data-speed="' . esc_attr( !empty($args['typed_speed']) ? $args['typed_speed'] : 6 ) . '"'
										. ' data-delay="' . esc_attr( !empty($args['typed_delay']) ? $args['typed_delay'] : 1 ) . '"'
										. '>%s</span>',
										$typed_strings[0]
								),
								$args[ $title_key ]
							);
		}
	}
	if ( empty($size) ) $size = 'large';	//is_page() ? 'large' : 'normal';
	$title_tag = ! empty($args['title_tag']) && !trx_addons_is_off($args['title_tag'])
					? $args['title_tag']
					: apply_filters('trx_addons_filter_sc_item_title_tag', 'large' == $size ? 'h2' : ('tiny' == $size ? 'h4' : 'h3'));
	$title_tag_class = ( ! empty($args['title_tag']) && ! trx_addons_is_off($args['title_tag'])
							? ' sc_item_title_tag'
							: ''
							)
						. ( ! empty($args['title_color']) && $args['title_style'] != 'gradient'
								? ' ' . trx_addons_add_inline_css_class('color:' . esc_attr($args['title_color']) . ' !important')
							: ''
							)
						. ( ! empty($args['typed'])
							? ' sc_typed'
							: ''
							);
	?><<?php echo esc_attr($title_tag); ?> class="<?php 
		echo esc_attr( apply_filters('trx_addons_filter_sc_item_title_class', 'sc_item_title '.$sc.'_title'.$align.$style.$title_tag_class, $sc, $args ) );
		?>"
		<?php do_action('trx_addons_action_sc_item_title_data', $sc, $args ); ?>
	><?php
		if ( !trx_addons_is_off($args['subtitle_align']) && $args['subtitle_align'] != $args['title_align']) {
			echo '<span class="sc_item_title_inner">';
			if ($args['subtitle_position'] == 'above') {
				trx_addons_show_layout($subtitle);
			}
		}
		if ($args['title_style'] == 'gradient') {
			echo '<span class="trx_addons_text_gradient sc_item_title_text' . ( ! empty( $add_class ) ? ' ' . esc_attr( $add_class ) : '' ) . '"'
					. ( ! empty( $args['title_color'] )
						? ' style="'
							. 'color:' . esc_attr($args['title_color']) . ';'
							. 'background:' . esc_attr($args['title_color']) . ';'
							. 'background:linear-gradient('
							. max(0, min(360, (int) $args['gradient_direction'])) . 'deg,'
							. esc_attr(!empty($args['title_color2']) ? $args['title_color2'] : 'transparent') . ','
							. esc_attr($args['title_color']) . ');'
							. '"'
						: '')
					. '>'
					. trx_addons_prepare_macros($args['title'])
					. '</span>';
		} else {
			trx_addons_show_layout(trx_addons_prepare_macros($args['title']), '<span class="sc_item_title_text' . ( ! empty( $add_class ) ? ' ' . esc_attr( $add_class ) : '' ) . '">', '</span>');
		}
		// Dual title
		if ( ! empty( $args['title2'] ) ) {
			// If called not from Elementor - add border and background image styles
			$add_class2 = '';
			if ( ! isset( $args['gradient_direction_extra'] ) ) {
				$border_width = ! empty( $args['title2_border_width'] ) ? (int)$args['title2_border_width'] : '';
				$border_color = ! empty( $args['title2_border_color'] ) ? trim( $args['title2_border_color'] ) : '';
				$bg_image     = ! empty( $args['title2_bg_image'] ) ? trx_addons_get_attachment_url( $args['title2_bg_image'] ) : '';
				if ( ! empty( $args['title2_color'] ) ) {
					$add_class2 .= ( ! empty( $add_class2 ) ? ' ' : '' )
								. trx_addons_add_inline_css_class( 'color:' . esc_attr( $args['title2_color'] ) );
				}
				if ( ! empty( $border_width ) && ! empty( $border_color ) ) {
					$add_class2 .= ( ! empty( $add_class2 ) ? ' ' : '' )
								. 'sc_item_title_text_with_border '
								. trx_addons_add_inline_css_class(
									sprintf( '-webkit-text-stroke-color:%1$s; -webkit-text-stroke-width:%2$s;',
										$border_color,
										trx_addons_prepare_css_value( $border_width )
									)
								);
				}
				if ( ! empty( $bg_image ) ) {
					$add_class2 .= ( ! empty( $add_class2 ) ? ' ' : '' )
								. 'sc_item_title_text_with_bg_image '
								. trx_addons_add_inline_css_class(
									'-webkit-text-fill-color: transparent;'
									. '-webkit-background-clip:text;'
									. 'background-position:center center;'
									. 'background-clip:text;background-image:url(' . esc_url( $bg_image ) . ');'
									);
				}
			}
			// Output a part 2
			trx_addons_show_layout( trx_addons_prepare_macros($args['title2']), '<span class="sc_item_title_text2' . ( ! empty( $add_class2 ) ? ' ' . esc_attr( $add_class2 ) : '' ) . '">', '</span>');
		}
		if ( !trx_addons_is_off($args['subtitle_align']) && $args['subtitle_align'] != $args['title_align']) {
			if ($args['subtitle_position'] != 'above') {
				trx_addons_show_layout($subtitle);
			}
			echo '</span>';
		}
	?></<?php echo esc_attr($title_tag); ?>><?php
}
if ($args['subtitle_position'] !== 'above' && (trx_addons_is_off($args['subtitle_align']) || $args['subtitle_align'] == $args['title_align'])) {
	trx_addons_show_layout($subtitle);
}
if (!empty($args['description'])) {
	?><div class="<?php echo esc_attr(apply_filters('trx_addons_filter_sc_item_description_class', 'sc_item_descr '.$sc.'_descr'.$align, $sc)); ?>"><?php trx_addons_show_layout( wpautop( do_shortcode( trx_addons_prepare_macros( $args['description'] ) ) ) ); ?></div><?php
}