/* global jQuery:false, elementorFrontend:false */

(function() {

	"use strict";

	var trx_addons_once_resize = false;

	var $window          = jQuery( window ),
		$document        = jQuery( document ),
		$body            = jQuery( 'body' ),
		$scheme_watchers = jQuery('.watch_scheme');

	var $animated_items,
		$scheme_sections,
		$stack_sections;

	// Update links and values
	$document.on( 'action.got_ajax_response', update_jquery_links );
	$document.on( 'action.init_hidden_elements', update_jquery_links );
	var first_run = true;
	function update_jquery_links(e) {
		if ( first_run && e && e.namespace == 'init_hidden_elements' ) {
			first_run = false;
			return; 
		}
		$animated_items  = jQuery('[class*="animated-item"]');
		$scheme_sections = jQuery('[class*="scheme_"]:visible');
		$stack_sections  = jQuery('.sc_stack_section_effect_slide:not(.elementor-element-edit-mode)');
	}
	update_jquery_links();

	// Disable Elementor's lightbox if our lightbox is used
	if ( typeof TRX_ADDONS_STORAGE != 'undefined' && TRX_ADDONS_STORAGE['popup_engine'] != 'none' ) {
		$document.on( 'action.init_hidden_elements', function(e, cont) {
			// Disable Elementor's lightbox on the .esgbox links
			cont.find('a.esgbox').attr('data-elementor-open-lightbox', 'no');

			// Disable Elementor's lightbox on every link to the large image
			cont.find('a[href$=".jpg"],a[href$=".jpeg"],a[href$=".png"],a[href$=".gif"]').attr('data-elementor-open-lightbox', 'no');

			// Disable Elementor's lightbox only on the standard gallery
			//cont.find('.wp-block-gallery a[href$=".jpg"],.wp-block-gallery a[href$=".jpeg"],.wp-block-gallery a[href$=".png"],.wp-block-gallery a[href$=".gif"]').attr('data-elementor-open-lightbox', 'no');
		} );
	}

	$window.on( 'elementor/frontend/init', function() {
		if ( typeof window.elementorFrontend !== 'undefined' && typeof window.elementorFrontend.hooks !== 'undefined' ) {

			// If Elementor is in the Editor's Preview mode
			if ( elementorFrontend.isEditMode() ) {

				// Prevent many calls on first init elements - trottle with 2.5s is used
				var init_hidden_elements_immediately = false,

					// Run this function after 2.5s after last call
					init_hidden_elements_immediately_start = trx_addons_throttle( function() {
						init_hidden_elements_immediately = true;
						init_hidden_elements( $body );
					}, 2500, true ),

					// Run after any element is added
					init_hidden_elements = function($cont) {
						// Add 'sc_layouts_item'
						if ( $body.hasClass('cpt_layouts-template')
							|| $body.hasClass('cpt_layouts-template-default')
							|| $body.hasClass('single-cpt_layouts')
							|| $body.hasClass('single-elementor_library')
						) {
							$body.find('.elementor-element.elementor-widget').addClass('sc_layouts_item');
						}
						
						// Remove TOC if exists (rebuild on init_hidden_elements)
						jQuery('#toc_menu').remove();

						// Init hidden elements (widgets, shortcodes) when its added to the preview area
						$document.trigger( 'action.init_hidden_elements', [$cont] );

						// Trigger 'resize' actions after the element is added (inited)
						if ( $cont.parents('.elementor-section-stretched').length > 0 && ! trx_addons_once_resize ) {
							trx_addons_once_resize = true;
							$document.trigger( 'action.resize_trx_addons', [$cont.parents('.elementor-section-stretched')] );
						} else {
							$document.trigger( 'action.resize_trx_addons', [$cont] );
						}

						// Prepare animations
						trx_addons_elementor_animate_items();
					};

				// Init elements after creation
				elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function( $cont ) {
					if ( init_hidden_elements_immediately ) {
						init_hidden_elements( $cont );
					} else {
						init_hidden_elements_immediately_start();
					}
				} );

				// First init - add wrap 'sc_layouts_item'
				if ( $body.hasClass('cpt_layouts-template') || $body.hasClass('cpt_layouts-template-default') || $body.hasClass('single-cpt_layouts') ) {
					jQuery('.elementor-element.elementor-widget').addClass('sc_layouts_item');
				}

				// Make parent of the 'trx_addons_layout_editor_mask' relative (if need)
				jQuery('.trx_addons_layout_editor_mask').each( function() {
					var $cont = jQuery( this ).parent();
					if ( $cont.css( 'position' ) == 'static' ) {
						$cont.css( 'position', 'relative' );
					}
				} );


				// Shift elements down under fixed rows
				elementorFrontend.hooks.addFilter( 'frontend/handlers/menu_anchor/scroll_top_distance', function( scrollTop ) {
					return scrollTop - trx_addons_fixed_rows_height();
				} );

				// Link 'Edit layout'
				//-----------------------------------------------------
				jQuery( '.trx_addons_layout_editor_link:not(.inited)' )
					.addClass('inited')
					.on( 'click', function(e) {
						e.stopImmediatePropagation();
						return true;
					} );

				// Open/Close list with layouts
				jQuery( '.trx_addons_layout_editor_selector_trigger:not(.inited)' )
					.addClass('inited')
					.on( 'click', function(e) {
						jQuery(this).next().slideToggle();
						jQuery(this).parent().toggleClass('trx_addons_layout_editor_selector_opened');
						e.preventDefault();
						return false;
					} );

				// Change layout
				jQuery( '.trx_addons_layout_editor_selector_list_item:not(.inited)' )
					.addClass('inited')
					.on( 'click', function(e) {
						var $self        = jQuery(this),
							layout_id    = $self.data('layout-id'),
							layout_type  = $self.data('layout-type'),
							layout_url   = $self.data('layout-url'),
							layout_title = $self.text(),
							post_id      = $self.data('post-id');

						$self.parent().prevAll('.trx_addons_layout_editor_selector_trigger').eq(0).trigger('click');
						var link = $self.parent().prevAll('.trx_addons_layout_editor_link').eq(0),
							text = link.text();
						link
							.attr('href', layout_url)
							.text( text.replace( /"[^"]*"/, '"' + layout_title + '"' ) );

						if ( layout_id && layout_type && post_id && elementor ) {
							var settings = elementor.settings,
								model = settings.page.model,
								theme_slug = TRX_ADDONS_STORAGE['theme_slug'],
								override_name = theme_slug + '_options_override_' + layout_type + '_type',
								field_name = theme_slug + '_options_field_' + layout_type + '_type';
							if ( model.attributes.hasOwnProperty(override_name) ) {
								model.set(override_name, '1');
							}
							if ( model.attributes.hasOwnProperty(field_name) ) {
								model.set(field_name, 'custom');
							}
							override_name = theme_slug + '_options_override_' + layout_type + '_style';
							field_name = theme_slug + '_options_field_' + layout_type + '_style';
							if ( model.attributes.hasOwnProperty(override_name) ) {
								model.set(override_name, '1');
							}
							if ( model.attributes.hasOwnProperty(field_name) ) {
								model.set(field_name, layout_type + '-custom-' + layout_id);
							}
							// Open panel - tab - section with changed settings
							if ( elementor.panel.$el.find('input[data-setting="'+override_name+'"]').length === 0 ) {
								elementor.panel.$el.find('#elementor-panel-footer-settings').trigger('click');
								setTimeout( function() {
									elementor.panel.$el.find('.elementor-tab-control-advanced a').trigger('click');
									setTimeout( function() {
										var sec_number = layout_type == 'sidebar' ? 1 : ( layout_type == 'header' ? 3 : 4 );
										if ( sec_number > 1 ) {
											elementor.panel.$el.find('.elementor-control-section_theme_options_' + sec_number ).trigger('click');
										}
										setTimeout( function() {
											elementor.panel.$el.find('input[data-setting="'+override_name+'"]').trigger('change');
										}, 10 );
									}, 10 );
								}, 10 );

							// If panel already opened - recreate it via close and open
							} else {
								var field = elementor.panel.$el.find('select[data-setting="'+field_name+'"]'),
									layout_value = layout_type + '-custom-' + layout_id;
								if ( field.length > 0 ) {
									field.val(layout_value);
									field.trigger('change');
								}
							}
						}
						e.preventDefault();
						return false;
					} );


			// If Elementor is in Frontend
			} else {
				// Add page settings to the elementorFrontend object
				// in the frontend for non-Elementor pages (blog pages, categories, tags, etc.)
				if (typeof elementorFrontend.config !== 'undefined'
					&& typeof elementorFrontend.config.settings !== 'undefined'
					&& typeof elementorFrontend.config.settings.general === 'undefined'
				) {
					elementorFrontend.config.settings.general = {
						'elementor_stretched_section_container': TRX_ADDONS_STORAGE['elementor_stretched_section_container']
					};
				}
				// Call 'resize' handlers after Elementor inited
				// Use setTimeout to run our code after Elementor's stretch row code!
				setTimeout(function() {
					trx_addons_once_resize = true;
					$document.trigger('action.resize_trx_addons');
				}, 0);
				// Prepare separate animations before Elementor inited
				trx_addons_elementor_animate_items();
			}

			// Trigger action 'action.stretch_section_elementor' on stretched section was resized (only in Frontend)
			// !!! Temporary commented (switched off) - testing performance
			/*
			elementorFrontend.hooks.addAction( 'frontend/element_ready/section', function( $element ) {
				if ( typeof window.MutationObserver !== 'undefined' ) {
					if ( $element.hasClass( 'elementor-section-stretched' ) && ! $element.hasClass( 'stretch_observer_inited' ) ) {
						$element.addClass( 'stretch_observer_inited' ).data( 'old-width', $element.is( ':visible' ) ? $element.width() : 0 );
						var id = $element.data( 'id' );
						trx_addons_create_observer(
							'check_stretch_row-' + id,
							$element,
							function( mutationsList ) {
								if ( ! $element.is( ':visible' ) || $element.css( 'display' ) == 'none' ) return;
								var new_width = $element.width();
								if ( new_width && new_width != $element.data( 'old-width' ) ) {
									$document.trigger( 'action.stretch_section_elementor', [ $element ] );
									$element.data( 'old-width', new_width );
								}
							},
							{ childList: false, subtree: false, attributes : true, attributeFilter : ['style'] }
						);
					}
				}
			} );
			*/
		}

	});


	// Scheme watchers
	//---------------------------------------------------
	if ( $scheme_watchers.length > 0 ) {
		$document.on('action.scroll_trx_addons', function() {
			$scheme_watchers.each( function(idx) {
				var item = $scheme_watchers.eq(idx),
					item_dom = item.get(0),
					scheme_present = false,
					item_offset, item_cx, item_cy;
				if ( typeof item_dom.getBoundingClientRect == 'function' ) {	// Returns the boundaries of the object, considering the rotation
					item_offset = item_dom.getBoundingClientRect();
					item_cx = $window.scrollLeft() + item_offset.left + item_offset.width / 2;
					item_cy = $window.scrollTop() + item_offset.top + item_offset.height / 2;
				} else {														// Returns the boundaries of the object, without taking into account the rotation
					item_offset = item.offset();
					item_cx = item_offset.left + item.width() / 2;
					item_cy = item_offset.top + item.height() / 2;
				}	
				$scheme_sections.each( function(idx2) {
					var section = $scheme_sections.eq(idx2),
						section_offset = section.offset(),
						section_left = section_offset.left,
						section_top = section_offset.top;
					if (
						section_left < item_cx && section_left + section.outerWidth() > item_cx
						&& section_top < item_cy && section_top + section.outerHeight() > item_cy
					) {
						var scheme = trx_addons_get_class_by_prefix( section.attr('class'), 'scheme_' );
						if ( ! item.hasClass(scheme) ) {
							item.attr( 'class', trx_addons_chg_class_by_prefix( item.attr( 'class'), 'scheme_', scheme ) );
						}
						scheme_present = true;
					}
				} );
				if ( ! scheme_present ) {
					item.attr( 'class', trx_addons_chg_class_by_prefix( item.attr( 'class'), 'scheme_', '' ) );
				}
			} );
		} );
	}



	// Fix left position of sticky sections
	//---------------------------------------------------
	if ( $body.hasClass( 'fixed_blocks_sticky' ) ) {
		var $sections_wrap = false;
		$document.on('action.resize_trx_addons', function() {
			if ( $stack_sections.length === 0 ) return;
			if ( $sections_wrap === false ) {
				$sections_wrap = $stack_sections.eq(0).parents('.elementor-section-wrap').eq(0);
			}
			if ( $sections_wrap.length ) {
				var left = $sections_wrap.offset().left;
				if ( typeof TRX_ADDONS_STORAGE['elementor_stretched_section_container'] != 'undefined' ) {
					var $page_wrap = jQuery( TRX_ADDONS_STORAGE['elementor_stretched_section_container'] ).eq(0);
					if ( $page_wrap.length > 0 ) {
						left -= $page_wrap.offset().left;
					}
				}
				$stack_sections.each(function() {
					var $self = jQuery(this);
					if ( $self.hasClass( 'elementor-section-stretched' ) ) {
						$self.css( {
							'left': 'unset',
							'transform': 'translateX(' + (-1) * left + 'px)'
						} );
					}
				} );
			}
		} );
	}


	// Tabs
	//---------------------------------------------------

	// Open tabs on hover
	$document.on( 'action.init_hidden_elements', function(e, cont) {
		var tabs = cont.hasClass( 'elementor-widget-tabs' ) ? cont : jQuery( '.elementor-widget-tabs' );
		if ( tabs.hasClass('sc_tabs_open_on_hover_on') && ! tabs.hasClass('sc_tabs_open_on_hover_inited') ) {
			tabs
				.addClass( 'sc_tabs_open_on_hover_inited' )
				.on( 'mouseenter', '.elementor-tab-title:not(.elementor-active)', function(e) {
					jQuery(this).trigger('click');
				} );
		}
	} );

	// Init hidden elements on tab open
	$document.on( 'action.init_hidden_elements', function(e, cont) {
		var tabs = cont.hasClass( 'elementor-widget-tabs' ) ? cont : jQuery( '.elementor-widget-tabs' );
		if ( ! tabs.hasClass('sc_tabs_hidden_inited') ) {
			tabs
				.addClass( 'sc_tabs_hidden_inited' )
				.on( 'touchstart mousedown', '.elementor-tab-title:not(.elementor-active)', function(e) {
					var $active = jQuery(this).siblings('.elementor-active');
					if ( $active.length ) {
						var $tab_content = tabs.find( '.elementor-tab-content[data-tab="' + $active.data('tab') + '"]' );
						if ( $tab_content.length ) {
							$document.trigger( 'action.deactivate_tab', [$tab_content] );
						}
					}
				} )
				.on( 'click', '.elementor-tab-title', function(e) {
					var $self = jQuery(this);
					var $tab_content = tabs.find( '.elementor-tab-content[data-tab="' + $self.data('tab') + '"]' );
					setTimeout( function() {
						$document.trigger( 'action.activate_tab', [$tab_content] );
						$document.trigger( 'action.init_hidden_elements', [$tab_content] );
						$document.trigger( 'action.resize_trx_addons' );
					}, $tab_content.height() > 50 ? 0 : 600 );	// Use delay if tab_content is animated now
				} );
		}
	} );

/*
	// Disable squize height of tabs while tab_content is animated
	// Fixed: in Elementor 3.0.13 tab_content animation is removed
	$document.on( 'action.init_hidden_elements', function(e, cont) {
		var tabs = cont && cont.hasClass( 'elementor-widget-tabs' ) ? cont : jQuery( '.elementor-widget-tabs' );
		if ( ! tabs.hasClass('sc_tabs_height_inited') ) {
			tabs
				.addClass( 'sc_tabs_height_inited' )
				.on( 'mousedown', '.elementor-tab-title', function(e) {
					var wrap = tabs.find('.elementor-tabs-content-wrapper'),
						height = wrap.find('.elementor-tab-content:visible').outerHeight();
					wrap.css( 'min-height', height + 'px' );
					setTimeout( function() {
						wrap.css( 'min-height', 0 );
					}, 500 );
				} );
		}
	} );
*/


	// Animations
	//--------------------------------------------------------------------------------

	// Do entrance animation inside popup
	$document.on( 'action.prepare_popup_elements', function(e, cont, mfp) {
		var max_delay = 0,
			items = cont.find('.animated-item,.animated[class*="animation_type_"],[data-settings*="_animation"][class*="animation_type_"]');
		items.each( function(idx) {
			var $self  = jQuery(this),
				block  = $self.hasClass('animation_type_block') || $self.hasClass( 'animated-separate' ),
				sc     = block ? $self : $self.parents( '.animated-separate' ),
				params = sc.data( 'animation-settings' ) || sc.data( 'settings' ),
				delay  = trx_addons_elementor_animate_items_delay(params, sc, $self, idx, items.length),
				animation = trx_addons_elementor_animate_items_animation(params);
			if ( delay > max_delay ) max_delay = delay;
			$self
				.removeClass('animated ' + animation + ' ' + trx_addons_elementor_animate_items_animation_revert_name( animation))
				.addClass('elementor-invisible');
			if ( block ) {
				trx_addons_elementor_animate_item($self, idx, items.length, false);
			}
		} );
		cont.data('animation-delay', max_delay);
		if ( mfp && mfp.st ) {
			mfp.st.removalDelay = max_delay + 300;	// Add out effect duration
		}
	} );

	// Revert entrance animation on close popup
	$document.on( 'action.close_popup_elements', function(e, cont) {
		var max_delay = cont.data('animation-delay') || 0,
			items = cont.find('.animated-item,.animated[class*="animation_type_"]');
		items.each( function(idx) {
			var $self  = jQuery(this),
				block  = $self.hasClass('animation_type_block'),
				sc     = block ? $self : $self.parents( '.animated-separate' ),
				params = sc.data( block ? 'settings' : 'animation-settings' );
			if ( ! params ) {
				return;
			}
			var reverted = sc.data( 'animation-reverted' ),
				animation = reverted ? reverted : trx_addons_elementor_animate_items_animation(params);
			$self.removeClass('animated ' + animation);
			// Set back animation
			if ( ! reverted ) {
				var revert_params = trx_addons_elementor_animate_items_animation_revert_params( sc, params, max_delay );
				sc.data( block ? 'settings' : 'animation-settings', revert_params ).data( 'animation-reverted', animation );
				setTimeout( function() {
					sc.data( block ? 'settings' : 'animation-settings', params ).data( 'animation-reverted', '' );
				}, max_delay );
			}
			// Revert animation
			trx_addons_elementor_animate_item($self, idx, items.length, false);
		} );
	} );

	// Move entrance animation parameters from the shortcode's wrapper to the items
	// to make sequental or random animation item by item
	$document.on( 'action.init_hidden_elements', function(e, cont) {
		trx_addons_elementor_animate_items();
	} );

	window.trx_addons_elementor_animate_items = function( force ) {
		jQuery('[class*="animation_type_"]:not(.animation_type_block)' + ( ! force ? ':not(.animated-separate)' : '' )).each( function() {
			var sc = jQuery(this),
				sc_name = sc.data('widget_type');
			if ( sc_name ) {
				sc_name = sc_name.split('.');
				sc_name = '.' + sc_name[0].replace('trx_', '') + '_item';
				if ( sc.find( sc_name ).length === 0 ) {
					sc_name = '.post_item';
					if ( sc.find( sc_name ).length === 0 ) {
						sc_name = '[class*="column-"]';
					}
				}
			} else {
				sc_name = '[class*="column-"]';
			}
			sc_name += sc_name && TRX_ADDONS_STORAGE['elementor_animate_items'] ? ',' + TRX_ADDONS_STORAGE['elementor_animate_items'] : '';
			var items = sc.find( sc_name );
			if ( items.length === 0 ) {
				sc.addClass( 'animation_type_block' );
				return;
			}
			var cid         = sc.data('model-cid'),
				params      = cid ? trx_addons_elementor_get_settings_by_cid( cid, ['_animation'] ) : sc.data('settings'),
				item_params = {},
				item_speed  = sc.hasClass( 'animated-slow' )
								? 'animated-slow'
								: ( sc.hasClass( 'animated-fast' )
									? 'animated-fast'
									: ''
									);
			if ( ! params ) {
				return;
			}
			for (var i in params) {
				if (i.substr(0, 10) == '_animation' || i.substr(0, 9) == 'animation') {
					item_params[i] = params[i];
					delete params[i];
				}
			}
			sc.removeClass('elementor-invisible animated '
					+ trx_addons_elementor_animate_items_animation( item_params ) 
					+ ( sc.data('last-animation') ? ' ' + sc.data('last-animation') : '' )
					+ ( item_speed ? ' ' + item_speed : '' )
					)
				.addClass('animated-separate')
				.data( 'last-animation', trx_addons_elementor_animate_items_animation( item_params ) );
			if ( ! cid ) {
				sc
//					.attr('data-settings', JSON.stringify(params))
					.data('settings', params);
			}
			sc.data( 'animation-settings', item_params );
			items.each( function(idx) {
				var item = jQuery(this);
				// Split titles to separate words
				if ( item.hasClass( 'sc_item_title' )
//					|| item.hasClass( 'sc_item_subtitle' )
//					|| item.hasClass( 'sc_item_descr' )
					|| item.hasClass( 'elementor-heading-title' )
				) {
					item.html( trx_addons_wrap_words( item.html(), '<span class="sc_item_animated_block elementor-invisible animated-item' + ( item_speed ? ' ' + item_speed : '' ) + '">', '</span>' ) );
				} else {
					if ( item_speed ) {
						item.addClass( item_speed );
					}
					item.addClass( 'elementor-invisible animated-item' );
				}
				// Remove default animation
				if ( item.data( 'animation' ) !== undefined ) {
					item.removeAttr( 'data-animation' );
				}
			} );

		} );

		$animated_items = jQuery('[class*="animated-item"]');
		if ( force ) {
			trx_addons_elementor_animate_items_scroll( force );
		}
	};

	// Get object's settings by cid from the Elementor's Editor
	window.trx_addons_elementor_get_settings_by_cid = function( cid, keys ) {
		if ( typeof elementorFrontend != 'undefined' ) {
			var settings = elementorFrontend.config.elements.data[cid].attributes;
			if ( keys ) {
				var params = {};
				for ( var s in settings ) {
					for ( var i = 0; i < keys.length; i++ ) {
						if ( s.indexOf( keys[i] ) === 0 ) {
							// If current field is repeater
							if ( typeof settings[s] == 'object' && settings[s].hasOwnProperty('models') ) {
								var tmp = [];
								for ( var m = 0; m < settings[s]['models'].length; m++ ) {
									tmp.push( settings[s]['models'][m]['attributes'] );
								}
								params[s] = tmp;

							// Else it a plain field
							} else {
								params[s] = settings[s];
							}
							break;
						}
					}
				}
				return params;
			}
			return settings;
		}
		return false;
	};

	// Add entrance animation for items (Elementor is not init its)
	$document.on('action.scroll_trx_addons', function() {
		trx_addons_elementor_animate_items_scroll();
	} );

	function trx_addons_elementor_animate_items_scroll( force ) {
		var cnt = 0;
		$animated_items.each(function(idx) {
			var item = jQuery(this);	//$animated_items.eq(idx);
			if ( ! force && ( item.hasClass('animated') || item.hasClass('wait-for-animation') ) ) return;
			var item_top = item.offset().top,
				window_top = $window.scrollTop(),
				window_height = $window.height();
			if ( item_top + 50 < window_top + window_height ) {
				var item_height = item.outerHeight(),
					need_animation = item_top + item_height > window_top;
				trx_addons_elementor_animate_item( item, cnt, $animated_items.length - idx + cnt, force || ! need_animation );
				if ( need_animation ) {
					cnt++;
				}
			}
		});
	}

	function trx_addons_elementor_animate_item( item, idx, total, force ) {
		var block = item.hasClass('animation_type_block'),
			sc    = block ? item : item.parents( '.animated-separate' );
		var item_params = sc.data(block ? 'settings' : 'animation-settings'),
			item_delay = trx_addons_elementor_animate_items_delay(item_params, sc, item, idx, total),
			item_animation = trx_addons_elementor_animate_items_animation(item_params);
		if ( force ) {
			if ( item.data('last-animation') && item.data('last-animation') != item_animation ) {
				item.removeClass( item.data('last-animation') );
				item.data('last-animation', '');
			}
			if ( item.hasClass('elementor-invisible') ) {
				item.removeClass('elementor-invisible');
			}
			if ( ! item.hasClass('animated') ) {
				item.addClass('animated');
			}
			if ( false && ! item.hasClass(item_animation) ) {	// Don't add animation to elements above current viewport - just leave its visible (.animated was added in the previous step)
				item.addClass(item_animation);
				item.data('last-animation', item_animation);
			}
		} else {
			item.addClass('wait-for-animation');
			setTimeout( function() {
				item.removeClass('wait-for-animation').addClass('animated').addClass(item_animation).removeClass('elementor-invisible');
			}, item_delay );
		}
	}

	function trx_addons_elementor_animate_items_delay( params, sc, item, idx, total ) {
		var delay = sc.hasClass( 'animation_type_block' )
						? ( params && params._animation_delay ? params._animation_delay : 0 )
						: ( sc.hasClass( 'animation_type_sequental' )
							? ( params && params._animation_delay ? params._animation_delay : 150 )
								* ( sc.data( 'animation-reverted' )
									? ( item.hasClass('menu-item') ? item.siblings('.menu-item').length + 1 - item.index() : Math.min( 8, total - idx ) )
									: ( item.hasClass('menu-item') ? item.index() : Math.min( 8, idx ) )
									)
							: trx_addons_random( 0, params && params._animation_delay ? params._animation_delay : 1500 )
							);
		return delay;
	}

	function trx_addons_elementor_animate_items_animation( params ) {
		var device = $body.data( 'elementor-device-mode' );
		if ( ! device || device == 'desktop' ) {
			device = '';
		} else {
			device = '_' + device;
		}
		return params
				? ( typeof params["_animation" + device] != 'undefined' ? params["_animation" + device] : params["_animation"] )
				: '';
	}

	function trx_addons_elementor_animate_items_animation_revert_name( animation ) {
		return animation.replace('In', 'Out').replace('Up', 'Down');
	}

	function trx_addons_elementor_animate_items_animation_revert_params( sc, params, max_delay ) {
		var device = $body.data( 'elementor-device-mode' );
		if ( ! device || device == 'desktop' ) {
			device = '';
		} else {
			device = '_' + device;
		}
		if ( params ) {
			var revert_params = trx_addons_object_clone( params );
			if ( typeof revert_params["_animation" + device] != 'undefined' ) {
				revert_params["_animation" + device] = trx_addons_elementor_animate_items_animation_revert_name( revert_params["_animation" + device] );
			} else {
				revert_params["_animation"] = trx_addons_elementor_animate_items_animation_revert_name( revert_params["_animation"] );
			}
			if ( sc.hasClass( 'animation_type_block' ) ) {
				revert_params._animation_delay = revert_params._animation_delay
													? Math.max(0, max_delay - revert_params._animation_delay)
													: max_delay;
			}
			return revert_params;
		}
		return params;
	}


	// Background text
	//----------------------------------------------------
	$document.on( 'action.init_hidden_elements', function(e, cont) {
		trx_addons_elementor_add_bg_text( cont );
	} );

	// Add background text to the sections
	function trx_addons_elementor_add_bg_text( cont ) {
		if ( cont.hasClass('elementor-section') ) {
			cont.find('.trx_addons_bg_text').remove();
			trx_addons_elementor_add_bg_text_in_row( cont );
		} else {
			jQuery( ( typeof window.elementorFrontend !== 'undefined' && elementorFrontend.isEditMode()
						? '.elementor-section.elementor-element-edit-mode'
						: '.trx_addons_has_bg_text'
						)
					+ ':not(.trx_addons_has_bg_text_inited)' ).each( function() {
				trx_addons_elementor_add_bg_text_in_row( jQuery( this ) );
			} );
		}
	}

	// Add background text to the single section
	function trx_addons_elementor_add_bg_text_in_row( row ) {
		var data = row.data('bg-text'),
			cid = '',
			rtl = $body.hasClass( 'rtl' );
		if ( ! data ) {
			cid  = row.data('model-cid');
			if ( cid ) {
				data = trx_addons_elementor_get_settings_by_cid( cid, ['bg_text'] );
			}
		}
		if ( ! data ) {
			return;
		}
		if ( data['bg_text'] ) {
			if ( ! row.hasClass( 'trx_addons_has_bg_text' ) ) {
				row.addClass( 'trx_addons_has_bg_text' );
			}
			var row_cont = row.addClass('trx_addons_has_bg_text_inited');//.find('.elementor-container').eq(0);
			var chars = '', in_tag=false, ch;
			if ( data['bg_text_effect'] != 'none' ) {
				for ( var i = 0; i < data['bg_text'].length; i++ ) {
					ch = data['bg_text'].substr(i,1);
					if ( ! in_tag ) {
						if ( ch == '<' ) {
							in_tag = true;
						} else {
							chars += '<span class="trx_addons_bg_text_char">' + ( ch == ' ' ? '&nbsp;' : ch ) + '</span>';
						}
					}
					if ( in_tag ) {
						chars += ch;
						if ( ch == '>' ) {
							in_tag = false;
						}
					}
				}
			} else {
				chars = '<span class="trx_addons_bg_text_char">' + data['bg_text'] + '</span>';
			}
			var marquee_speed = typeof data['bg_text_marquee'] == 'object'
						? ( data['bg_text_marquee']['size']
							? data['bg_text_marquee']['size']
							: 0
							)
						: data['bg_text_marquee'],
				marquee_dir = typeof data['bg_text_reverse'] != 'undefined'
						? ( data['bg_text_reverse'] > 0
							? ( rtl ? -1 : 1 )
							: ( rtl ? 1 : -1 )
							)
						: ( rtl ? 1 : -1 ),
				overlay = typeof data['bg_text_overlay'] == 'object'
						? data['bg_text_overlay']['url']
						: data['bg_text_overlay'];
			row_cont.prepend(
				'<div class="trx_addons_bg_text'
					+ ( marquee_speed > 0 ? ' trx_addons_marquee_wrap' : '')
					+ ( ( ! rtl && marquee_dir > 0 ) || ( rtl && marquee_dir < 0 ) ? ' trx_addons_marquee_reverse' : '' )
				+ '">'
					+ '<div class="trx_addons_bg_text_inner'
									+ ' trx_addons_bg_text_effect_' + data['bg_text_effect']
									+ ( marquee_speed > 0 ? ' trx_addons_marquee_element' : '')
									+ ( cid == '' ? ' trx_addons_show_on_scroll' : ' trx_addons_showed_on_scroll trx_addons_in_preview_mode' )
									+ '"'
					+ '>'
						+ chars
					+ '</div>'
					+ ( overlay
						? '<div class="trx_addons_bg_text_overlay trx_addons_show_on_scroll"></div>'
						: ''
						)
				+ '</div>'
			);
			$document.trigger( 'action.got_ajax_response', [''] );
			if ( marquee_speed > 0 && cid === '' ) {
				var marquee_wrap = row_cont.find('.trx_addons_marquee_wrap').eq(0),
					marquee_chars = data['bg_text_effect'] == 'none' ? data['bg_text'].length :  marquee_wrap.find( '.trx_addons_bg_text_char' ).length,
					marquee_time = 20 * marquee_chars / 15;
				setTimeout( function() {
					trx_addons_elementor_marquee_bg_text( marquee_wrap, marquee_dir, marquee_speed, marquee_time, true, data['bg_text_effect'] );
				}, data['bg_text_effect'] != 'none' ? marquee_chars * 100 + 800 : 0 );
			}
		}
	}

	// Marqueue bg text
	function trx_addons_elementor_marquee_bg_text( marquee_wrap, marquee_dir, marquee_speed, marquee_time, start, effect ) {
		var elements = marquee_wrap.find('.trx_addons_marquee_element'),
			mw = elements.eq(0).outerWidth(),
			mx = 0,
			mpw = marquee_wrap.width();
		if ( effect == 'none' && elements.eq(0).hasClass( 'trx_addons_show_on_scroll' ) && ! elements.eq(0).hasClass( 'trx_addons_showed_on_scroll' ) ) {
			elements.eq(0).removeClass( 'trx_addons_show_on_scroll' ).addClass( 'trx_addons_showed_on_scroll' );
		}
		if ( elements.eq(0).hasClass( 'trx_addons_showed_on_scroll' ) ) {
			if ( start ) {
				for (var i=1; i < Math.ceil((mpw + mw) / mw); i++ ) {
					var element_clone = elements.eq(0).clone();
					elements.eq(0).after( element_clone );
				}
				elements = marquee_wrap.find('.trx_addons_marquee_element');
			}
			elements.each( function(idx) {
				TweenMax.to(
					elements.eq(idx),
					marquee_time - marquee_time * 0.8 * marquee_speed / 10,
					{
						x: mw * marquee_dir,
						y: 0,
						ease: Power0.easeNone,
						onComplete: function() {
							if ( idx == elements.length - 1 ) {
								elements.each( function(idx2) {
									TweenMax.to(
										elements.eq(idx2),
										0,
										{
											x: 0,
											y: 0,
											ease: Power0.easeNone
										}
									);
								});
								setTimeout( function() {
									trx_addons_elementor_marquee_bg_text( marquee_wrap, marquee_dir, marquee_speed, marquee_time, false, effect );
								}, 1);
							}
						}
					}
				);
			} );
		} else {
			setTimeout( function() {
				trx_addons_elementor_marquee_bg_text( marquee_wrap, marquee_dir, marquee_speed, marquee_time, start, effect );
			}, effect != 'none' ? elements.eq(0).find( '.trx_addons_bg_text_char' ).length * 100 + 800 : 0 );
		}
	}

})();