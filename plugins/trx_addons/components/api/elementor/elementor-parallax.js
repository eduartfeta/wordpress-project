( function() {

	'use strict';

	//--- Get block sizes at each scroll event (slow) or only on resize event (fast)
	var GET_SIZES_ON_SCROLL = false;

	var $window            = jQuery( window ),
		$document          = jQuery( document ),
		$body              = jQuery( 'body' ),
		motion_step   = 0,
		motion_period = 250;

	$document.on( 'action.after_add_content', function( e, $cont ) {
		if ( $cont && $cont.length ) {
			$cont
				.find( '.trx_addons_parallax_layers_inited,.trx_addons_parallax_blocks_inited' )
				.removeClass( 'trx_addons_parallax_layers_inited trx_addons_parallax_blocks_inited' );
		}
	} );

	$document.on( 'action.got_ajax_response action.init_hidden_elements', function() {
		// Check items after timeout to allow theme add params
		setTimeout( function() {
			var items = jQuery('.trx_addons_parallax_layers:not(.trx_addons_parallax_layers_inited)');
			if ( items.length > 0 ) {
				items.each( function() {
					var parallax_layers = new trx_addons_parallax( jQuery(this).addClass('trx_addons_parallax_layers_inited'), 'layers' );
					parallax_layers.init();
				} );
			}
			items = jQuery('.trx_addons_parallax_blocks:not(.trx_addons_parallax_blocks_inited)');
			if ( items.length > 0 ) {
				items.each( function() {
					var parallax_blocks = new trx_addons_parallax( jQuery(this).addClass('trx_addons_parallax_blocks_inited'), 'blocks' );
					parallax_blocks.init();
				} );
			}
		}, 0 );
	} );

	$window.on( 'elementor/frontend/init', function() {
		function parallax_init( $target ) {
			var parallax_layers = new trx_addons_parallax( $target, 'layers' );
			parallax_layers.init();			
			var parallax_blocks = new trx_addons_parallax( $target, 'blocks' );
			parallax_blocks.init();			
		}
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/section', parallax_init );
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/column',  parallax_init );
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/element', parallax_init );
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/widget',  parallax_init );
	} );

	window.trx_addons_parallax = function( $target, init_type ) {
		var self          = this,
			settings      = false,
			parallax_type = 'none',
			edit_mode     = Boolean( window.elementorFrontend.isEditMode() ),
			scroll_list   = [],
			mouse_list    = [],
			motion_list   = [],
			$targetLayers = $target,
			$targetBlocks = $target,
			wst           = trx_addons_window_scroll_top() + trx_addons_fixed_rows_height(),
			ww            = trx_addons_window_width(),
			wh            = trx_addons_window_height() - trx_addons_fixed_rows_height(),
			tl            = 0,
			tt            = 0,
			tw            = 0,
			th            = 0,
			tx            = 0,
			ty            = 0,
			cx            = 0,
			cy            = 0,
			dx            = 0,
			dy            = 0,
			is_safari     = !!navigator.userAgent.match(/Version\/[\d\.]+.*Safari/),
			platform      = navigator.platform;

		self.init = function() {
			if ( ! edit_mode ) {
				if ( init_type == 'layers' ) {
					settings = $target.data( 'parallax-blocks' ) || false;
					if ( settings ) {
						parallax_type = 'layers';
					}
				} else {
					var params = $target.data( 'parallax-params' ) || false;					
					if ( params ) {
						settings = [];
						settings.push(params);
						parallax_type = 'blocks';
					}
				}
			} else {
				settings = self.get_editor_settings( $target, init_type );
			}
			if ( ! settings ) {
				return;
			}

			// If block must catch mouse events
			if ( settings[0].mouse == 1 ) {
				var layout_data = {
						selector: $target,
						image: false,
						size: 'auto',
						prop: settings[0].mouse_type || 'transform3d',
						type: 'mouse',
						x: 0,
						y: 0,
						z: settings[0].mouse_z || 0,
						speed: 2 * ( ( settings[0].mouse_speed ? settings[0].mouse_speed : 10 ) / 100 ),
						tilt_amount: settings[0].mouse_tilt_amount || 70,
						motion_dir: 'round',
						motion_time: 5
					};
				mouse_list.push( layout_data );
				parallax_type += '|layers';
				if ( settings[0].mouse_handler == 'window' ) {
					$targetLayers = $body;
				} else if ( settings[0].mouse_handler == 'content' ) {
					$targetLayers = jQuery( trx_addons_apply_filters( 'trx_addons_filter_page_wrap_class', TRX_ADDONS_STORAGE['page_wrap_class'] ? TRX_ADDONS_STORAGE['page_wrap_class'] : '.page_wrap', 'elementor-parallax' ) ).eq(0);
				} else if ( settings[0].mouse_handler == 'row' ) {
					$targetLayers = $target.hasClass( 'trx_addons_parallax_blocks' )
										? $target.parent()
										: $target.parents( '.elementor-section' ).eq(0);
				} else if ( settings[0].mouse_handler == 'column' ) {
					$targetLayers = $target.hasClass( 'trx_addons_parallax_blocks' )
										? $target.parent()
										: $target.parents( '.elementor-column' ).eq(0);
				} else if ( settings[0].mouse_handler == 'parent' ) {
					$targetLayers = $target.parent();
				} else if ( settings[0].mouse_handler && '.#'.indexOf( settings[0].mouse_handler.substring(0, 1) ) != -1 ) {
					$targetLayers = $target.parents( settings[0].mouse_handler );
				} else if ( settings[0].mouse_type == 'tilt' ) {
					var $tilt_trigger = $target.parents( '.trx_addons_tilt_trigger' );
					if ( $tilt_trigger.length > 0 ) {
						$targetLayers = $tilt_trigger.eq(0);
					}
				}
				$targetLayers.data('mouse-handler', settings[0].mouse_handler);
			}

			if ( parallax_type.indexOf('layers') >= 0 ) {
				if ( init_type == 'layers' ) {
					self.create_layers();
				}
				$targetLayers.on( 'mousemove.trx_addons_parallax', self.mouse_move_handler );
				$targetLayers.on( 'mouseleave.trx_addons_parallax', self.mouse_leave_handler );
				if ( motion_list.length > 0 ) {
					setInterval( self.motion_move_handler, motion_period );
				}
			}
			if ( parallax_type.indexOf('blocks') >= 0 ) {
				settings[0].selector = $targetBlocks;
				settings[0].hsection = $targetBlocks.parents( '.sc_hscroll_section' );
				settings[0].hscroller = settings[0].hsection.length ? settings[0].hsection.parents( '.sc_hscroll_scroller' ) : false;
				scroll_list.push(settings[0]);
			}
			if ( ! GET_SIZES_ON_SCROLL ) {
				self.get_blocks_sizes();
				$document.on( 'action.init_hidden_elements', self.get_blocks_sizes );
				$document.on('action.sc_layouts_row_fixed_on action.sc_layouts_row_fixed_off', self.get_blocks_sizes );
				$window.on( 'action.resize_trx_addons', self.get_blocks_sizes );
			}
			$window.on( 'action.resize_trx_addons action.scroll_trx_addons', self.scroll_handler );
			self.scroll_update();
		};

		self.get_blocks_sizes = function() {
			jQuery.each( scroll_list, function( index, block ) {
				block.sizes = {
					width: block.selector.outerWidth(),
					height: block.selector.outerHeight(),
					top: block.selector.offset().top
				};
			} );
		};

		self.get_editor_settings = function( $target, init_type ) {
			if ( ! window.elementor || ! window.elementor.hasOwnProperty( 'elements' ) ) {
				return false;
			}

			var elements = window.elementor.elements;

			if ( ! elements.models ) {
				return false;
			}

			var section_id = $target.data('id'),
				section_cid = $target.data('model-cid'),
				section_data = {};

			function get_section_data( idx, obj ) {
				if ( 0 < Object.keys( section_data ).length ) {
					return;
				} else if ( section_id == obj.id ) {
					section_data = obj.attributes.settings.attributes;
				} else if ( obj.attributes && obj.attributes.elements && obj.attributes.elements.models ) {
					jQuery.each( obj.attributes.elements.models, get_section_data );
				}
			}

			jQuery.each( elements.models, get_section_data );

			if ( 0 === Object.keys( section_data ).length ) {
				return false;
			}

			var settings = [];
			
			if ( init_type == 'layers' && section_data.hasOwnProperty( 'parallax_blocks' ) ) {
				parallax_type = 'layers';
				jQuery.each( section_data[ 'parallax_blocks' ].models, function( index, obj ) {
					settings.push( obj.attributes );
				} );
			} else if ( init_type == 'blocks' && section_data.hasOwnProperty( 'parallax' ) && section_data.parallax == 'parallax' ) {
				parallax_type = 'blocks';
				settings.push( {
					type: section_data.hasOwnProperty( 'parallax_type' ) ? section_data.parallax_type : 'object',
					x: section_data.hasOwnProperty( 'parallax_x' ) ? section_data.parallax_x.size : 0,
					x_unit: section_data.hasOwnProperty( 'parallax_x' ) ? section_data.parallax_x.unit : 0,
					y: section_data.hasOwnProperty( 'parallax_y' ) ? section_data.parallax_y.size : 0,
					y_unit: section_data.hasOwnProperty( 'parallax_y' ) ? section_data.parallax_y.unit : 0,
					scale: section_data.hasOwnProperty( 'parallax_scale' ) ? section_data.parallax_scale.size : 0,
					rotate: section_data.hasOwnProperty( 'parallax_rotate' ) ? section_data.parallax_rotate.size : 0,
					opacity: section_data.hasOwnProperty( 'parallax_opacity' ) ? section_data.parallax_opacity.size : 0,
					duration: section_data.hasOwnProperty( 'parallax_duration' ) ? section_data.parallax_duration.size : 1,
					squeeze: section_data.hasOwnProperty( 'parallax_squeeze' ) ? section_data.parallax_squeeze.size : 1,
					amplitude: section_data.hasOwnProperty( 'parallax_amplitude' ) ? section_data.parallax_amplitude.size : 40,
					mouse: section_data.hasOwnProperty( 'parallax_mouse' ) && section_data.parallax_mouse == 'mouse' ? 1 : 0,
					mouse_type: section_data.hasOwnProperty( 'parallax_mouse_type' ) ? section_data.parallax_mouse_type : 'transform3d',
					mouse_tilt_amount: section_data.hasOwnProperty( 'parallax_mouse_tilt_amount' ) ? section_data.parallax_mouse_tilt_amount.size : 70,
					mouse_speed: section_data.hasOwnProperty( 'parallax_mouse_speed' ) ? section_data.parallax_mouse_speed.size : 10,
					mouse_z: section_data.hasOwnProperty( 'parallax_mouse_z' ) ? section_data.parallax_mouse_z.size : '',
					text: section_data.hasOwnProperty( 'parallax_text' ) ? section_data.parallax_text : 'block'
				} );
			}

			return 0 !== settings.length ? settings : false;
		};

		self.create_layers = function() {

			$target.find( '> .sc_parallax_block' ).remove();
			
			var bg_parallax_present = false;

			jQuery.each( settings, function( index, block ) {
				var image       = block['image'].url,
					speed       = block['speed'].size,
					z_index     = block['z_index'].size,
					bg_size     = block['bg_size'] ? block['bg_size'] : 'auto',
					anim_prop   = block['animation_prop'] ? block['animation_prop'] : 'background',
					left        = block['left'].size,
					top         = block['top'].size,
					type        = block['type'] ? block['type'] : 'none',
					tilt_amount = block['mouse_tilt_amount'] ? block['mouse_tilt_amount'] : 70,
					motion_dir  = block['motion_dir'] ? block['motion_dir'] : 'round',
					motion_time = block['motion_time'] ? block['motion_time'].size : 5,
					// New parallax to fix Chrome scroll: used only for layers with type=='scroll' and animation type=='background'
					bg_parallax = block['bg_parallax'] && type =='scroll' && anim_prop == 'background' ? block['bg_parallax'] : false,
					$layout     = null;

				if ( bg_parallax ) {
					bg_parallax_present = true;
				}

				if ( '' !== image || 'none' !== type ) {
					var layout_init = {
						'z-index': z_index
					};
					if ( 'none' === type ) {
						layout_init['left'] = left + '%';
						layout_init['top'] = top + '%';
					}
					$layout = jQuery( '<div class="sc_parallax_block'
											+ ' sc_parallax_block_type_' + type
											+ ' sc_parallax_block_animation_' + ( bg_parallax ? 'bg_parallax' : anim_prop )
											+ (is_safari ? ' is-safari' : '')
											+ ('MacIntel' == platform ? ' is-mac' : '')
											+ (typeof block['class'] !== 'undefined' && block['class'] != '' ? ' ' + block['class'] : '')
										+ '">'
											+ '<div class="sc_parallax_block_image"'
												+ ( bg_parallax
													? ' parallax="' + ( speed / 100 ) + '"'
													: ''
													)
											+ '></div>'
										+ '</div>' )
								.prependTo( $target )
								.css( layout_init );

					layout_init = {
						'background-image': 'url(' + image + ')',
						'background-size': bg_size,
						'background-position-x': left + '%',
						'background-position-y': top + '%'
					};
					$layout.find( '> .sc_parallax_block_image' ).css(layout_init);

					var layout_data = {
						selector: $layout,
						image: image,
						size: bg_size,
						bg_parallax: bg_parallax,
						prop: anim_prop,
						type: type,
						x: left,
						y: top,
						z: z_index,
						speed: 2 * ( speed / 100 ),
						tilt_amount: tilt_amount,
						motion_dir: motion_dir,
						motion_time: motion_time
					};

					if ( 'scroll' === type ) {
						layout_data.hsection = layout_data.selector.parents( '.sc_hscroll_section' );
						layout_data.hscroller = layout_data.hsection.length ? layout_data.hsection.parents( '.sc_hscroll_scroller' ) : false;
						scroll_list.push( layout_data );
					} else if ( 'mouse' === type ) {
						mouse_list.push( layout_data );
					} else if ( 'motion' === type ) {
						motion_list.push( layout_data );
					}
				}
			});

			// Init new parallax method (to fix Google Chrome scroll)
			if ( bg_parallax_present ) {
				trx_addons_bg_parallax( $target.get(0) );
			}
		};


		// Scroll handlers
		//-------------------------------------
		self.scroll_handler = function( e ) {
			wst = trx_addons_window_scroll_top() + trx_addons_fixed_rows_height();
			ww  = trx_addons_window_width();
			wh  = trx_addons_window_height() - trx_addons_fixed_rows_height();
			self.scroll_update();
		};

		self.scroll_update = function() {
			jQuery.each( scroll_list, function( index, block ) {

				if ( ! block.selector.is(':visible') ) return;

				// Calc additional offset of the block to compatibility with blocks inside a widget 'Horizontal Scroll'
				var hscroller_offset = block.hsection.length ? block.hscroller.data( 'hscroll-offset' ) || 0 : 0;
				var hsection_offset = block.hsection.length ? block.hsection.data( 'hscroll-section-offset' ) || 0 : 0;
				hscroller_offset += hsection_offset;

				// Section (row) layers
				if ( parallax_type.indexOf('layers') >= 0 ) {
					if ( ( ! block.bg_parallax || block.prop != 'background' ) && block.speed !== undefined ) {
						var $image     = block.selector.find( '.sc_parallax_block_image' ).eq(0),
							speed      = block.speed,
							offset_top = ( GET_SIZES_ON_SCROLL ? block.selector.offset().top : block.sizes.top ) + hscroller_offset,
							h          = GET_SIZES_ON_SCROLL ? block.selector.outerHeight() : block.sizes.height,
							y          = ( wst + wh - offset_top ) / h * 100;
						if ( wst < offset_top - wh) y = 0;
						if ( wst > offset_top + h)  y = 200;
						y = parseFloat( speed * y ).toFixed(1);
						if ( 'background' === block.prop ) {
							$image.css( {
								'background-position-y': 'calc(' + block.y + '% + ' + y + 'px)'
							} );
						} else {
							$image.css( {
								'transform': 'translateY(' + y + 'px)'
							} );
						}
					}
				}

				// Widgets (blocks)
				if ( parallax_type.indexOf('blocks') >= 0 ) {
					var w_top      = wst,
						w_bottom   = w_top + wh,
						obj        = block.selector,
						obj_width  = GET_SIZES_ON_SCROLL ? obj.outerWidth() : block.sizes.width,
						obj_height = GET_SIZES_ON_SCROLL ? obj.outerHeight() : block.sizes.height,
						obj_top    = ( GET_SIZES_ON_SCROLL ? obj.offset().top : block.sizes.top ) + hscroller_offset,
						obj_bottom = obj_top + obj_height;

					var entrance = obj.hasClass('sc_parallax_entrance'),
						entrance_complete = obj.hasClass('sc_parallax_entrance_complete'),
						bottom_delta = entrance ? 100 : 0,
						start = obj.hasClass('sc_parallax_start'),
						params = block;	//obj.data('parallax-params') ? obj.data('parallax-params') : {};

					if ( typeof params.type == 'undefined' ) params.type = 'object';
					if ( typeof params.x == 'undefined' ) params.x = 0;
					if ( typeof params.x_unit == 'undefined' ) params.x_unit = 'px';
					if ( typeof params.y == 'undefined' ) params.y = 0;
					if ( typeof params.y_unit == 'undefined' ) params.y_unit = 'px';
					if ( typeof params.scale == 'undefined' ) params.scale = 0;
					if ( typeof params.rotate == 'undefined' ) params.rotate = 0;
					if ( typeof params.opacity == 'undefined' ) params.opacity = 0;
					if ( typeof params.duration == 'undefined' ) params.duration = 1;
					if ( typeof params.squeeze == 'undefined' ) params.squeeze = 1;
					if ( typeof params.amplitude == 'undefined' ) params.amplitude = 40;
					if ( typeof params.text == 'undefined' ) params.text = 'block';
					if ( typeof params.ease == 'undefined' ) params.ease = "Power2";

					if ( obj.data('inited') === undefined ) {
						if ( obj_top > w_bottom ) obj_top = w_bottom - bottom_delta;
						else if ( obj_bottom < w_top ) obj_bottom = w_top;
						obj.data('inited', 1);
					}

					if ( w_top <= obj_bottom && obj_top <= w_bottom - bottom_delta && ! entrance_complete ) {
						if ( entrance ) {
							var entrance_start = false;
							if (start && !obj.data('entrance-inited')) {
								if (obj_top < w_bottom - bottom_delta) {
									obj.addClass('sc_parallax_entrance_complete');
									return;
								}
								obj.data('entrance-inited', 1);
								entrance_start = true;
							} else {
								obj.addClass('sc_parallax_entrance_complete');
							}
						}
						var delta = entrance ? 1 : Math.max( 1, ( wh + obj_height ) * params.amplitude / 100 ),// / 2.5,
							shift = entrance ? (entrance_start ? 0 : 1) : w_bottom - obj_top,
							step_x = params.x != 0 ? params.x / delta : 0,
							step_y = params.y != 0 ? params.y / delta : 0,
							step_scale = params.scale != 0 ? params.scale / 100 / delta : 0,
							step_rotate = params.rotate != 0 ? params.rotate / delta : 0,
							step_opacity = params.opacity != 0 ? params.opacity / delta : 0;
						var scroller_init = { ease: self.get_ease(params.ease) },
							transform = '',
							val = 0;
						if (step_opacity !== 0) {
							scroller_init.opacity = trx_addons_round_number(
													start
														? Math.min(1, 1 - shift * step_opacity + params.opacity)
														: 1 + shift * step_opacity,
													2);
						}
						if (step_x !== 0) {
							val = Math.round( start
												? params.x - shift * step_x
												: shift * step_x - (params.type == 'bg' && params.x > 0 ? params.x : 0)
											);
							if ( start && ( (params.x < 0 && val > 0) || (params.x > 0 && val < 0) ) ) val = 0;
							transform += 'translateX(' + val + params.x_unit + ')';
							scroller_init.x = val + params.x_unit;
						}
						if (step_y !== 0) {
							val = Math.round( start
												? params.y - shift * step_y
												: shift * step_y - (params.type == 'bg' && params.y > 0 ? params.y : 0)
											);
							if ( start && ( (params.y < 0 && val > 0) || (params.y > 0 && val < 0) ) ) val = 0;
							transform += (transform != '' ? ' ' : '') + 'translateY(' + val + params.y_unit + ')';
							scroller_init.y = val + params.y_unit;
						}
						if (step_rotate !== 0) {
							val = trx_addons_round_number( start
															? params.rotate - shift * step_rotate
															: shift * step_rotate,
														2);
							if ( start && ( (params.rotate < 0 && val > 0) || (params.rotate > 0 && val < 0) ) ) val = 0;
							transform += (transform != '' ? ' ' : '') + 'rotate(' + val + 'deg)';
							scroller_init.rotation = val;
						}
						if (step_scale !== 0) {
							val = trx_addons_round_number( start
															? 1 + params.scale / 100 - shift * step_scale
															: 1 + shift * step_scale - (params.type == 'bg' && params.scale < 0 ? params.scale / 100 : 0),
														2);
							if ( start && ( (params.scale < 1 && val > 1) || (params.scale > 1 && val < 1) ) ) val = 1;
							transform += (transform != '' ? ' ' : '') + 'scale(' + val + ')';
							scroller_init.scale = val;
						}
						// Save current transform to data-param
						obj.data( 'trx-parallax-scroller-init', scroller_init );
						// Prepare (split) text with 'by words' and 'by chars' effects
						if ( [ 'chars', 'words'].indexOf(params.text) != -1 && obj.data('element_type') !== undefined ) {
							var sc = (obj.data('element_type') == 'widget' ? obj.data('widget_type') : obj.data('element_type')).split('.')[0],
								inner_obj = obj.find('.sc_parallax_text_block');
							if (inner_obj.length === 0) {
								inner_obj = obj.find(
											sc == 'trx_sc_title'
												? '.sc_item_title_text,.sc_item_subtitle'
												: ( sc == 'trx_sc_supertitle'
													? '.sc_supertitle_text'
													: ( sc == 'heading'
														? '.elementor-heading-title'
														: 'p'
														)
													)
											);
								if (inner_obj.length > 0) {
									inner_obj.each( function( idx ) {
										inner_obj.eq( idx )
											.html(
												params.text == 'chars'
													? self.wrap_chars( inner_obj.eq( idx ).html() )
													: self.wrap_words( inner_obj.eq( idx ).html() )
											);
									} );
									inner_obj = inner_obj.find('.sc_parallax_text_block');
								}
							}
							if (inner_obj.length > 0) {
								obj = inner_obj;
							}
						}
						obj.each( function(idx) {
							if (idx === 0) {
								TweenMax.to( obj.eq(idx), params.duration, scroller_init );
							} else {
								setTimeout( function() {
									TweenMax.to( obj.eq(idx), params.duration, scroller_init );
								}, ( params.text == 'chars' ? 50 : 250 ) * idx * params.squeeze );
							}
						} );
					}
				}
			} );
		};


		// Mouse move/leave handlers
		//-----------------------------------------
		self.mouse_move_handler = function( e ) {
			if ( tw === 0 ) {
				tl = $targetLayers.offset().left;
				tt = $targetLayers.offset().top;
				tw = $targetLayers.width();
				th = ['window', 'content'].indexOf($targetLayers.data('mouse-handler'))!=-1
						? Math.min(trx_addons_window_height(), $targetLayers.height())
						: $targetLayers.height();
			}
			wst = trx_addons_window_scroll_top() + trx_addons_fixed_rows_height();
			ww  = trx_addons_window_width();
			wh  = trx_addons_window_height() - trx_addons_fixed_rows_height();
			
			cx  = Math.ceil( tw / 2 );	// + tl,
			cy  = Math.ceil( th / 2 );	// + tt,
			dx  = e.clientX - tl - cx;
			dy  = ['window', 'content'].indexOf($targetLayers.data('mouse-handler'))!=-1
						? e.clientY - cy
						: e.clientY + wst - tt - cy;
			tx  = -1 * ( dx / cx );
			ty  = -1 * ( dy / cy );
			jQuery.each( mouse_list, self.mouse_move_update );
		};

		self.mouse_leave_handler = function( e ) {
			jQuery.each( mouse_list, function( index, block ) {
				var $image = block.selector.find( '.sc_parallax_block_image' ).eq(0);
				if ( $image.length === 0 ) {
					$image = block.selector;
				}

				var x = 0, y = 0, z = 0;

				// Add scroll parameters
				var scroller_init = block.selector.data( 'trx-parallax-scroller-init' );
				if ( scroller_init && scroller_init.css ) {
					x = x * 1 + trx_addons_units2px( scroller_init.css.x || 0, block );
					y = y * 1 + trx_addons_units2px( scroller_init.css.y || 0, block );
				}

				if ( block.prop == 'background' ) {
					TweenMax.to(
						$image,
						1.5,
						{
							backgroundPositionX: block.x + '%',
							backgroundPositionY: block.y + '%',
							ease: Power2.easeOut
						}
					);
				} else if ( block.prop == 'transform' ) {
					TweenMax.to(
						$image,
						1.5,
						{
							x: x,
							y: y,
							ease:Power2.easeOut
						}
					);
				} else if ( block.prop == 'transform3d' ) {
					TweenMax.to(
						$image,
						1.5,
						{
							x: x,
							y: y,
							z: z,
							rotationX: 0,
							rotationY: 0,
							ease:Power2.easeOut
						}
					);
				} else if ( block.prop == 'tilt' ) {
					TweenMax.to(
						$image,
						0.2,
						{
							x: x,
							y: y,
							z: z,
							rotationX: 0,
							rotationY: 0,
							scale: 1,
							transformPerspective: 1500,
							ease:Power2.easeOut
						}
					);
				}

			} );
		};

		self.mouse_move_update = function( index, block, time, ease ) {
			var	$image   = block.selector.find( '.sc_parallax_block_image' ).eq(0),
				speed    = block.speed,
				x        = parseFloat( tx * 125 * speed ).toFixed(1),
				y        = parseFloat( ty * 125 * speed ).toFixed(1),
				z        = block.z * 50,
				rotate_x = parseFloat( tx * 25 * speed ).toFixed(1),
				rotate_y = parseFloat( ty * 25 * speed ).toFixed(1);

			// Add scroll parameters
			var scroller_init = block.selector.data( 'trx-parallax-scroller-init' );
			if ( scroller_init && scroller_init.css ) {
				x = x * 1 + trx_addons_units2px( scroller_init.css.x || 0, block );
				y = y * 1 + trx_addons_units2px( scroller_init.css.y || 0, block );
			}

			if ( $image.length === 0 ) {
				$image = block.selector;
			}

			if ( block.prop == 'background' ) {
				TweenMax.to(
					$image,
					time === undefined ? 1 : time,
					{
						backgroundPositionX: 'calc(' + block.x + '% + ' + x + 'px)',
						backgroundPositionY: 'calc(' + block.y + '% + ' + y + 'px)',
						ease: ease === undefined ? Power2.easeOut : ease
					}
				);
			} else if ( block.prop == 'transform' ) {
				TweenMax.to(
					$image,
					time === undefined ? 1 : time,
					{
						x: x,
						y: y,
						ease: ease === undefined ? Power2.easeOut : ease
					}
				);
			} else if ( block.prop == 'transform3d' ) {
				TweenMax.to(
					$image,
					time === undefined ? 2 : time,
					{
						x: x,
						y: y,
						z: z,
						rotationX: rotate_y,
						rotationY: -rotate_x,
						ease: ease === undefined ? Power2.easeOut : ease
					}
				);
			} else if ( block.prop == 'tilt' ) {
				var m = block.tilt_amount > 0 ? block.tilt_amount : 70,
					k = ['window', 'content'].indexOf($targetLayers.data('mouse-handler')) != -1 ? 2 : 4;
				z = Math.max(0, block.z);
				if ( isNaN(z) ) z = 0;
				TweenMax.set( $image,
					{
						transformOrigin: ((dx + cx) * 25 / tw + 40) + "% " + ((dy + cy) * 25 / th + 40) + "%",
						transformPerspective: 1000 + 500 * z
					}
				);
				TweenMax.to(
					$image,
					time === undefined ? 0.5 : time,
					{
						rotationX:  dy / ( m - k * z ),	// ( m - 2 * z )	//( m * ( z + 2 ) / 2 )
						rotationY: -dx / ( m - k * z ),	// ( m - 2 * z )	//( m * ( z + 2 ) / 2 )
						y: ty * 2 * z,
						x: tx * 2 * z,
						z: 2 * z,
						scale: 1 + z / 100,
						ease: ease === undefined ? Power2.easeOut : ease
					}
				);
			}
		};


		// Permanent motion handlers
		//-----------------------------------------
		self.motion_move_handler = function() {
			if ( tw === 0 ) {
				tl = $targetLayers.offset().left;
				tt = $targetLayers.offset().top;
				tw = $targetLayers.width();
				th = $targetLayers.height();
			}
			cx = Math.ceil( tw / 2 );	// + tl,
			cy = Math.ceil( th / 2 );	// + tt;
			jQuery.each( motion_list, function( index, block ) {
				var fi,
					delta = ( ( motion_period * motion_step++ ) % ( block['motion_time'] * 1000 ) ) / ( block['motion_time'] * 1000 ),
					angle = 2 * Math.PI * delta;
				if ( block['motion_dir'] == 'round' ) {
					fi = Math.atan2(tw / 2 * Math.sin(angle), th / 2 * Math.cos(angle));
					dx = tw / 2 * Math.cos(fi);
					dy = th / 2 * Math.sin(fi);
				} else if ( block['motion_dir'] == 'random' ) {
					dx = -tw + tw * 2 * Math.random();
					dy = -th + th * 2 * Math.random();
				} else {
					dx = block['motion_dir'] == 'vertical' ? 0 : tw / 2 * Math.cos(angle);
					dy = block['motion_dir'] == 'horizontal' ? 0 : th / 2 * Math.sin(angle);
				}
				tx = -1 * ( dx / cx );
				ty = -1 * ( dy / cy );
				if ( block['motion_dir'] == 'random' ) {
					if ( delta === 0 ) {
						self.mouse_move_update(index, block, block['motion_time'], Power0.easeNone);
					}
				} else {
					self.mouse_move_update(index, block);
				}
			} );
		};


		// Utilities
		//-----------------------------------------

		// Return easing method from its name
		self.get_ease = function(name) {
			name = name.toLowerCase();
			if ( name == 'none' || name == 'line' || name == 'linear' || name == 'power0' )
				return Power0.easeNone;
			else if ( name == 'power1')
				return Power1.easeOut;
			else if ( name == 'power2')
				return Power2.easeOut;
			else if ( name == 'power3')
				return Power3.easeOut;
			else if ( name == 'power4')
				return Power4.easeOut;
			else if ( name == 'back')
				return Back.easeOut;
			else if ( name == 'elastic')
				return Elastic.easeOut;
			else if ( name == 'bounce')
				return Bounce.easeOut;
			else if ( name == 'rough')
				return Rough.easeOut;
			else if ( name == 'slowmo')
				return SlowMo.easeOut;
			else if ( name == 'stepped')
				return Stepped.easeOut;
			else if ( name == 'circ')
				return Circ.easeOut;
			else if ( name == 'expo')
				return Expo.easeOut;
			else if ( name == 'sine')
				return Sine.easeOut;
		};

		// Wrap each char to the <span>
		self.wrap_chars = function(txt) {
			return trx_addons_wrap_chars( txt, '<span class="sc_parallax_text_block">', '</span>' );
		};

		// Wrap each word to the <span>
		self.wrap_words = function(txt) {
			return trx_addons_wrap_words( txt, '<span class="sc_parallax_text_block">', '</span>' );
		};

	};


	// New parallax (to fix Google Chrome scroll)
	// CSS transform3D and perspective are used instead shift background position on scroll events
	// This method needs a special layout:
	// 	body (with overflow hidden)
	// 		.viewport (with overflow-y: scroll | auto and height: 100%)
	//			.parallax_wrap
	//				.parallax_image
	//				.parallax_image
	//				.parallax_image
	//				...
	//----------------------------------------------------------------------------
	window.trx_addons_bg_parallax = function(clip) {
		var parallax        = clip.querySelectorAll('.sc_parallax_block_image[parallax]'),
			parallaxDetails = [],
			sticky          = false;
		
		// Edge requires a transform on the document body and a fixed position element
		// in order for it to properly render the parallax effect as you scroll.
		// See https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/5084491/
//		if (getComputedStyle(document.body).transform == 'none') {
			// Broke page in WordPress - admin bar shift down and margin-top is appear in the body
//			document.body.style.transform = 'translateZ(0)';
//		}

		var fixedPos = document.createElement('div');
		fixedPos.style.position = 'fixed';
		fixedPos.style.top = '0';
		fixedPos.style.width = '1px';
		fixedPos.style.height = '1px';
		fixedPos.style.zIndex = 1;
		document.body.insertBefore(fixedPos, document.body.firstChild);

		for ( var i = 0; i < parallax.length; i++ ) {
			var elem = parallax[i];
			var container = elem.parentNode;
			if ( getComputedStyle(container).overflow != 'visible' ) {
				console.error('Need non-scrollable container to apply perspective for', elem);
				continue;
			}
			if ( clip && container.parentNode != clip ) {
				console.warn('Currently we only track a single overflow clip, but elements from multiple clips found.', elem);
			}
			clip = container.parentNode;
			if (getComputedStyle(clip).overflow == 'visible') {
				console.error('Parent of sticky container should be scrollable element', elem);
			}
			// TODO(flackr): optimize to not redo this for the same clip/container.
			var perspectiveElement;
			if (sticky || getComputedStyle(clip).webkitOverflowScrolling) {
				sticky = true;
				perspectiveElement = container;
			} else {
				perspectiveElement = clip;
				container.style.transformStyle = 'preserve-3d';
			}
			perspectiveElement.style.perspectiveOrigin = 'bottom right';
			perspectiveElement.style.perspective = '1px';
			if (sticky) {
				elem.style.position = '-webkit-sticky';
				elem.style.top = '0';
			}
			elem.style.transformOrigin = 'bottom right';
			// Find the previous and next elements to parallax between.
			var previousCover = parallax[i].previousElementSibling;
			while (previousCover && previousCover.hasAttribute('parallax')) {
				previousCover = previousCover.previousElementSibling;
			}
			var nextCover = parallax[i].nextElementSibling;
			while (nextCover && !nextCover.hasAttribute('parallax-cover')) {
				nextCover = nextCover.nextElementSibling;
			}
			parallaxDetails.push( {
				'node': parallax[i],
				'top': parallax[i].offsetTop,
				'sticky': !!sticky,
				'nextCover': nextCover,
				'previousCover': previousCover
			} );
		}

		for ( i = 0; i < parallax.length; i++ ) {
			parallax[i].parentNode.insertBefore(parallax[i], parallax[i].parentNode.firstChild);
		}

		// Add a scroll listener to hide perspective elements when they should no longer be visible.
		clip.addEventListener( 'scroll', function() {
			for (var i = 0; i < parallaxDetails.length; i++) {
				var container = parallaxDetails[i].node.parentNode;
				var previousCover = parallaxDetails[i].previousCover;
				var nextCover = parallaxDetails[i].nextCover;
				var parallaxStart = previousCover ? (previousCover.offsetTop + previousCover.offsetHeight) : 0;
				var parallaxEnd = nextCover ? nextCover.offsetTop : container.offsetHeight;
				var threshold = 200;
				var visible = parallaxStart - threshold - clip.clientHeight < clip.scrollTop &&
				parallaxEnd + threshold > clip.scrollTop;
				// FIXME: Repainting the images while scrolling can cause jank.
				// For now, keep them all.
				// var display = visible ? 'block' : 'none'
				var display = 'block';
				if (parallaxDetails[i].node.style.display != display) {
					parallaxDetails[i].node.style.display = display;
				}
			}
		} );

		var bg_parallax_resize = function(details) {
			for (var i = 0; i < details.length; i++) {
				var container = details[i].node.parentNode;
				var clip = container.parentNode;
				var previousCover = details[i].previousCover;
				var nextCover = details[i].nextCover;
				var rate = details[i].node.getAttribute('parallax');
				var parallaxStart = previousCover ? (previousCover.offsetTop + previousCover.offsetHeight) : 0;
				var scrollbarWidth = details[i].sticky ? 0 : clip.offsetWidth - clip.clientWidth;
				var parallaxElem = details[i].sticky ? container : clip;
				var height = details[i].node.offsetHeight;
				var depth = 0;
				if ( rate ) {
					depth = 1 - (1 / rate);
				} else {
					var parallaxEnd = nextCover ? nextCover.offsetTop : container.offsetHeight;
					depth = (height - parallaxEnd + parallaxStart) / (height - clip.clientHeight);
				}
				if ( details[i].sticky ) {
					depth = 1.0 / depth;
				}
				var scale = 1.0 / (1.0 - depth);
				// The scrollbar is included in the 'bottom right' perspective origin.
				var dx = scrollbarWidth * (scale - 1);
				// Offset for the position within the container.
				var dy = details[i].sticky
							? -(clip.scrollHeight - parallaxStart - height) * (1 - scale)
							: (parallaxStart - depth * (height - clip.clientHeight)) * scale;
				details[i].node.style.transform = 'scale(' + (1 - depth) + ') translate3d(' + dx + 'px, ' + dy + 'px, ' + depth + 'px)';
			}
		};

		window.addEventListener('resize', bg_parallax_resize.bind(null, parallaxDetails));
		bg_parallax_resize(parallaxDetails);

	};

}() );
