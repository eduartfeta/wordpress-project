/* global jQuery:false */

(function() {

	"use strict";

	var requestAnimationFrame = window.requestAnimationFrame
		|| window.webkitRequestAnimationFrame
		|| window.mozRequestAnimationFrame
		|| window.oRequestAnimationFrame
		|| window.msRequestAnimationFrame
		|| null;

	window.trx_addons_smoke_selector = function() {
		return '#trx_addons_smoke';
	};

	var $window   = jQuery( window ),
		$document = jQuery( document ),
		$smoke    = jQuery( trx_addons_smoke_selector() );

	if ( $smoke.length ) {
		jQuery( 'body' ).addClass( 'trx_addons_smoke_present' );
	}


	// Init effects
	//-------------------------------------------------
	$document.on('action.init_trx_addons', function() {

		if ( $smoke.length === 0 || ! requestAnimationFrame ) return;

		init_fog( $smoke.data('trx-addons-smoke') );

	} );


	// Effect: Fog
	//-------------------------------------------

	// Timing utility: Linear interpolation
	function lerp( start, end, amt ){
		return ( 1 - amt ) * start + amt * end;
	}

	// Timing utility: Change value in specified time
	function tween_value( args ) {
		// Defaults
		if ( ! args.time ) args.time = 1;	// 1s

		// Use TweenMax ( if loaded )
		if ( typeof TweenMax != 'undefined' ) {
			var t = {
				value: args.start
			};
			return TweenMax.to( t, args.time, {
				value: args.end,
				ease: args.ease ? args.ease : Power2.easeOut,
				onUpdate: function() {
					args.callbacks.onUpdate( t.value );
				},
				onComplete: function() {
					if ( args.callbacks.onComplete ) {
						args.callbacks.onComplete();
					}
				}
			} );

			// Internal tween manager
		} else {
			var amount = 0.1;
			return setInterval( function() {
				t.value = lerp(t.value, args.end, amount);
				args.callbacks.onUpdate( t.value );
				if (  Math.abs( t.value - args.end ) < 0.0001 ) {
					t.value = args.end;
					if ( args.callbacks.onComplete ) {
						args.callbacks.onComplete();
					}
				}
			}, Math.max(1, Math.round( args.time * 1000 / ( Math.abs( args.end - args.start ) / amount ) ) ) );
		}
	}

	// Timing utility: Stop changing value
	function tween_stop( handler ) {
		// Use TweenMax (if loaded)
		if ( typeof TweenMax != 'undefined' ) {
			if ( handler ) handler.kill();

			// Internal tween manager
		} else {
			if ( handler ) clearTimeout( handler );

		}
	}

	// Timing utility: Debounce
	function debounce( callback, duration ) {
		var timer;
		return function(event) {
			clearTimeout(timer);
			timer = setTimeout( function() {
				callback(event);
			}, duration);
		};
	}

	// Class Fog
	//----------------------
	function Fog() {

		this.uniforms = {
			time: {
				type: 'f',
				value: 0.0
			},
			mouseX: {
				type: 'f',
				value: 0.0
			},
			mouseY: {
				type: 'f',
				value: 0.0
			},
			zoom: {
				type: 'f',
				value: 0.0
			},
			opacity: {
				type: 'f',
				value: 1.0
			},
			vTintColor: {
				type: 'vec3',
				value: new THREE.Color()
			},
			tex: {
				type: 't',
				value: null
			}
		};
		this.num = 5;
		this.obj = null;

	}

	Fog.prototype.createObj = function( tex, point ) {
		// Define Geometries
		var geometry = new THREE.InstancedBufferGeometry();
		var baseGeometry = new THREE.PlaneBufferGeometry(1100, 1100, 20, 20);

		// Copy attributes of the base Geometry to the instancing Geometry
		geometry.addAttribute('position', baseGeometry.attributes.position);
		geometry.addAttribute('normal', baseGeometry.attributes.normal);
		geometry.addAttribute('uv', baseGeometry.attributes.uv);
		geometry.setIndex(baseGeometry.index);

		// Define attributes of the instancing geometry
		var instancePositions = new THREE.InstancedBufferAttribute( new Float32Array(this.num * 3), 3, 1 );
		var delays = new THREE.InstancedBufferAttribute( new Float32Array(this.num), 1, 1 );
		var rotates = new THREE.InstancedBufferAttribute( new Float32Array(this.num), 1, 1 );
		for ( var i = 0, ul = this.num; i < ul; i++ ) {
			instancePositions.setXYZ(
				i,
				( Math.random() - 0.5 ) * 300 + point.x,	//850,
				0,
				( Math.random() - 0.5 ) * 300 + point.y 	//300
			);
			delays.setXYZ(i, Math.random());
			rotates.setXYZ(i, Math.random() * 2 + 1);
		}
		geometry.addAttribute( 'instancePosition', instancePositions );
		geometry.addAttribute( 'delay', delays );
		geometry.addAttribute( 'rotate', rotates );

		// Define Material
		var material = new THREE.RawShaderMaterial( {
			uniforms: this.uniforms,
			vertexShader: `
				attribute vec3 position;
				attribute vec2 uv;
				attribute vec3 instancePosition;
				attribute float delay;
				attribute float rotate;

				uniform mat4 projectionMatrix;
				uniform mat4 modelViewMatrix;
				uniform float time;
				uniform float mouseX;
				uniform float mouseY;
				uniform float zoom;

				varying vec3 vPosition;
				varying vec2 vUv;
				varying vec3 vColor;
				varying float vBlink;

				const float duration = 100.0;

				mat4 calcRotateMat4Z(float radian) {
					return mat4(
							cos(radian), -sin(radian), 0.0, 0.0,
							sin(radian), cos(radian), 0.0, 0.0,
							0.0, 0.0, 1.0, 0.0,
							0.0, 0.0, 0.0, 1.0
							);
				}
				vec3 convertHsvToRgb(vec3 c) {
					vec4 K = vec4(1.0, 2.0 / 3.0, 1.0 / 3.0, 3.0);
					vec3 p = abs(fract(c.xxx + K.xyz) * 6.0 - K.www);
					return c.z * mix(K.xxx, clamp(p - K.xxx, 0.0, 1.0), c.y);
				}

				void main(void) {
					float now  = mod(time + delay * duration, duration) / duration;
					float newX = mouseX;
					float newY = mouseY;
					float newZ = sin(radians(time * 50.0 + delay + length(position))) * 30.0;
					vec3  mousePosition = vec3(newX, newY, newZ);

					mat4 rotateMat = calcRotateMat4Z(radians(rotate * 360.0) + time * 0.1);
					vec3 rotatePosition = (rotateMat * vec4(position, 1.0)).xyz;

					vec3 updatePosition = mousePosition + ( instancePosition + rotatePosition ) * zoom;

					vec3 hsv = vec3(time * 0.1 + delay * 0.2 + length(instancePosition) * 100.0, 0.5, 0.8);
					vec3 rgb = convertHsvToRgb(hsv);
					float blink = (sin(radians(now * 360.0 * 20.0)) + 1.0) * 0.88;

					vec4 mvPosition = modelViewMatrix * vec4(updatePosition, 1.0);

					vPosition = position;
					vUv = uv;
					vColor = rgb;
					vBlink = blink;

					gl_Position = projectionMatrix * mvPosition;
				}
   			`,
			fragmentShader: `
				precision highp float;

				uniform sampler2D tex;
				uniform float opacity;
				uniform vec3 vTintColor;

				varying vec3 vPosition;
				varying vec2 vUv;
				varying vec3 vColor;
				varying float vBlink;

				void main() {
					vec2 p = vUv * 2.0 - 1.0;

					vec4 texColor = texture2D(tex, vUv);
					vec3 color = (texColor.rgb - vBlink * length(p) * 0.8) * vColor + vTintColor;
					float opacity = texColor.a * opacity;

					gl_FragColor = vec4(color, opacity);
				}
			`,
			transparent: true,
			depthWrite: false,
			blending: THREE.AdditiveBlending,
		} );

		this.uniforms.tex.value = tex;

		// Create Object3D
		this.obj = new THREE.Mesh(geometry, material);
	};

	Fog.prototype.render = function( time ) {
		this.uniforms.time.value += time;
	};

	Fog.prototype.setMouse = function( x, y ) {
		this.uniforms.mouseX.value = x;
		this.uniforms.mouseY.value = y;
	};

	Fog.prototype.setZoom = function( z ) {
		this.uniforms.zoom.value = z;
	};

	Fog.prototype.setOpacity = function( o ) {
		this.uniforms.opacity.value = o;
	};

	Fog.prototype.setTintColor = function( c ) {
		var rgb = trx_addons_hex2rgb( c );
		this.uniforms.vTintColor = new THREE.Uniform( new THREE.Color( rgb.r / 255, rgb.g / 255, rgb.b / 255 ) );
	};

	Fog.prototype.setTextureTotal = function( n ) {
		this.num = Math.max( 1, Math.min( 20, n ) );
	};

	// Global scene
	var scene = null,
		fog = null,
		camera = null;

	window.trx_addons_smoke_set_bg_color = function( bg_color ) {
		if ( bg_color ) {
			var bg = trx_addons_hex2rgb( bg_color );
			scene.background = new THREE.Color( bg.r / 255, bg.g / 255, bg.b / 255 );
		}
	};
	window.trx_addons_smoke_set_tint_color = function( tint_color ) {
		if ( tint_color ) {
			fog.setTintColor( tint_color );
		}
	};

	// Init effect
	function init_fog( args ) {

		var resolution = new THREE.Vector2();
		var mouse = new THREE.Vector2();
		var opacityStart = 0.7;
		var opacityEnd = 0;
		var opacity = 0;
		var zoomStart = 0.03;
		var zoomEnd = 0.8;
		var zoom = zoomStart;
		var bg_color = args['bg_color'] || '#000000';
		var bg_rgb = trx_addons_hex2rgb( bg_color );
		var tint_color = args['tint_color'] || '#000000';
		var image_repeat = args['image_repeat'] || 5;

		var canvas = $smoke.get(0);	//document.getElementById('canvas-webgl');

		var renderer = new THREE.WebGLRenderer({
			alpha: true,
			antialias: true,
			canvas: canvas,
		});

		var clock = new THREE.Clock();

		scene = new THREE.Scene();
		scene.background = new THREE.Color( bg_rgb.r / 255, bg_rgb.g / 255, bg_rgb.b / 255 );

		resolution.set(window.innerWidth, window.innerHeight);

		camera = new THREE.OrthographicCamera( resolution.x / -2, resolution.x / 2, resolution.y / 2, resolution.y / -2, 1, 2000 );

		fog = new Fog();
		fog.setTintColor( tint_color );
		fog.setTextureTotal( image_repeat );

		var lastX = -1,
			lastY = 0,
			curX = 0,
			curY = 0,
			realX = 0,
			realY = 0,
			dx = 0,
			dy = 0,
			tween = null;

		function render() {
			var time = clock.getDelta();
			fog.setMouse(mouse.x, mouse.y);
			fog.setOpacity(opacity);
			fog.setZoom(zoom);
			fog.render(time);
			renderer.render(scene, camera);
		}

		function renderLoop() {
			render();
			requestAnimationFrame(renderLoop);
		}

		function resizeCamera() {
			camera.left = resolution.x / -2;
			camera.right = resolution.x / 2;
			camera.top = resolution.y / 2;
			camera.bottom = resolution.y / -2;
			camera.updateProjectionMatrix();
		}

		function resizeWindow() {
			resolution.set(window.innerWidth, window.innerHeight);
			canvas.width = resolution.x;
			canvas.height = resolution.y;
			resizeCamera();
			renderer.setSize(resolution.x, resolution.y);
		}

		var tween_start = trx_addons_debounce( function() {
			tween = tween_value( {
				start: 0,
				end: 1,
				time: 2,
				callbacks: {
					onUpdate: function(value) {
						curX = lastX + dx * value;
						curY = lastY + dy * value;
						mouse.set( curX - resolution.x / 2, resolution.y / 2 - curY );
						opacity = opacityStart + value * ( opacityEnd - opacityStart );
						zoom = zoomStart + value * ( zoomEnd - zoomStart );
					},
					onComplete: function() {
						tween = null;
						lastX = -1;
						opacity = 0;
						zoom = 0;
					}
				}
			} );
		}, 50, false );

		function mouseUpdate(coords) {
			if ( lastX == -1 ) {
				lastX = coords.x;
				lastY = coords.y;
			}
			if ( tween === null ) {

				var size_max = Math.min( resolution.x, resolution.y ) / 2;

				dx = coords.x - lastX;
				dy = coords.y - lastY;

				if ( dx !== 0 || dy !== 0 ) {
					var size = Math.abs( dx ) + Math.abs( dy ),
						dx_rat = dx / size,
						dy_rat = dy / size;
					dx = size_max * dx_rat;
					dy = size_max * dy_rat;
					lastX = coords.x;
					lastY = coords.y;
					mouse.set( lastX - resolution.x / 2, resolution.y / 2 - lastY );
					opacity = opacityStart;
					zoom = zoomStart;
					tween_start();
					render();
				}
			}
		}

		function mouseMove( event ) {
			if ( ! resolution.x ) {
				resolution.set( window.innerWidth, window.innerHeight );
			}
			if ( tween ) {
				tween_stop( tween );
				tween = null;
				lastX = realX;
				lastY = realY;
				opacity = opacityStart;
				zoom = zoomStart;
			}
			var coords = {
				x: event.targetTouches && event.targetTouches[0] ? event.targetTouches[0].clientX : event.clientX,
				y: event.targetTouches && event.targetTouches[0] ? event.targetTouches[0].clientY : event.clientY
			};
			if ( tween == null ) {
				mouseUpdate( coords );
			}
			realX = coords.x;
			realY = coords.y;
		}

		function on() {

			window.addEventListener('resize', trx_addons_debounce( resizeWindow, 300 ) );

			document.addEventListener( 'mousemove', mouseMove );
			document.addEventListener( 'touchmove', mouseMove );
		}

		function loadTexs( imgs, callback ) {
			var texLoader = new THREE.TextureLoader();
			var length = Object.keys(imgs).length;
			var loadedTexs = {};
			var count = 0;

			texLoader.crossOrigin = 'anonymous';

			for ( var key in imgs ) {
				var k = key;
				if ( imgs.hasOwnProperty( k ) ) {
					texLoader.load( imgs[k], function( tex ) {
						tex.repeat = THREE.RepeatWrapping;
						loadedTexs[k] = tex;
						count++;
						if ( count >= length ) {
							callback( loadedTexs );
						}
					} );
				}
			}
		}

		( function() {

			loadTexs( { fog: args['image'] }, function( loadedTexs ) {

				fog.createObj( loadedTexs.fog, mouse );

				scene.add( fog.obj );

				renderer.setClearColor( 0x111111, 1.0 );
				camera.position.set( 0, 0, 1000 );
				camera.lookAt( new THREE.Vector3() );
				clock.start();

				on();
				resizeWindow();
				renderLoop();

			} );

		} )();

	}

})();