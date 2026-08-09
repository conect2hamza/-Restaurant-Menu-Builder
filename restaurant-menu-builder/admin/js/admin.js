/**
 * Restaurant Menu Builder — admin application.
 *
 * Dependency free apart from wp.i18n and wp.media. Every screen is driven by
 * the data-screen attribute on #rmb-app and talks to the REST API.
 */
( function () {
	'use strict';

	var D = window.rmbData || {};
	var i18n = ( window.wp && window.wp.i18n ) || {};
	var __ = i18n.__ || function ( text ) { return text; };
	var sprintf = i18n.sprintf || function ( format ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		var index = 0;
		return String( format ).replace( /%[sd]/g, function () {
			return args[ index++ ];
		} );
	};

	var root = document.getElementById( 'rmb-app' );

	if ( ! root ) {
		return;
	}

	/* ------------------------------------------------------------------ Utils */

	function el( tag, props, children ) {
		var node = document.createElement( tag );

		Object.keys( props || {} ).forEach( function ( key ) {
			var value = props[ key ];

			if ( key === 'class' ) {
				node.className = value;
			} else if ( key === 'text' ) {
				node.textContent = value;
			} else if ( key === 'html' ) {
				node.innerHTML = value;
			} else if ( key.indexOf( 'on' ) === 0 && typeof value === 'function' ) {
				node.addEventListener( key.slice( 2 ).toLowerCase(), value );
			} else if ( value !== null && value !== undefined && value !== false ) {
				node.setAttribute( key, value === true ? '' : value );
			}
		} );

		( children || [] ).forEach( function ( child ) {
			if ( child === null || child === undefined || child === false ) {
				return;
			}

			node.appendChild( typeof child === 'string' ? document.createTextNode( child ) : child );
		} );

		return node;
	}

	function clear( node ) {
		while ( node.firstChild ) {
			node.removeChild( node.firstChild );
		}
	}

	function debounce( fn, wait ) {
		var timer = null;

		return function () {
			var args = arguments;
			var context = this;

			window.clearTimeout( timer );
			timer = window.setTimeout( function () {
				fn.apply( context, args );
			}, wait );
		};
	}

	/* -------------------------------------------------------------- REST API */

	function api( path, options ) {
		options = options || {};

		var config = {
			method: options.method || 'GET',
			credentials: 'same-origin',
			headers: {
				'X-WP-Nonce': D.nonce,
				Accept: 'application/json'
			}
		};

		if ( options.body ) {
			config.headers[ 'Content-Type' ] = 'application/json';
			config.body = JSON.stringify( options.body );
		}

		return window.fetch( D.restUrl + path, config ).then( function ( response ) {
			return response.json().catch( function () {
				return {};
			} ).then( function ( payload ) {
				if ( ! response.ok || payload.success === false ) {
					var message = payload.message || __( 'Something went wrong. Please try again.', 'restaurant-menu-builder' );
					var error = new Error( message );
					error.code = payload.code || 'rmb_error';
					throw error;
				}

				return payload;
			} );
		} );
	}

	/* --------------------------------------------------------------- Notices */

	var noticeHost = null;

	function notify( message, type ) {
		if ( ! noticeHost ) {
			noticeHost = el( 'div', { class: 'rmb-notices rmb-ui', role: 'status', 'aria-live': 'polite' } );
			document.body.appendChild( noticeHost );
		}

		var notice = el( 'div', { class: 'rmb-toast rmb-toast-' + ( type || 'success' ) }, [
			el( 'span', { text: message } ),
			el( 'button', {
				type: 'button',
				class: 'rmb-toast-close',
				'aria-label': __( 'Dismiss', 'restaurant-menu-builder' ),
				text: '×',
				onClick: function () {
					notice.remove();
				}
			} )
		] );

		noticeHost.appendChild( notice );

		window.setTimeout( function () {
			notice.classList.add( 'is-leaving' );
			window.setTimeout( function () {
				notice.remove();
			}, 300 );
		}, type === 'error' ? 7000 : 4000 );
	}

	function fail( error ) {
		notify( error && error.message ? error.message : __( 'Something went wrong. Please try again.', 'restaurant-menu-builder' ), 'error' );
	}

	/* ----------------------------------------------------------------- Modal */

	function openModal( config ) {
		var previouslyFocused = document.activeElement;

		var form = el( 'form', { class: 'rmb-modal-form', novalidate: true } );
		form.appendChild( config.content );

		var submitButton = el( 'button', {
			type: 'submit',
			class: 'rmb-button rmb-button-primary',
			text: config.submitLabel || __( 'Save', 'restaurant-menu-builder' )
		} );

		var dialog = el( 'div', { class: 'rmb-modal', role: 'dialog', 'aria-modal': 'true', 'aria-label': config.title }, [
			el( 'div', { class: 'rmb-modal-head' }, [
				el( 'h2', { class: 'rmb-modal-title', text: config.title } ),
				el( 'button', {
					type: 'button',
					class: 'rmb-modal-close',
					'aria-label': __( 'Close', 'restaurant-menu-builder' ),
					onClick: close
				}, [ uiIcon( 'close', 18 ) ] )
			] ),
			el( 'div', { class: 'rmb-modal-body' }, [ form ] ),
			el( 'div', { class: 'rmb-modal-foot' }, [
				el( 'button', {
					type: 'button',
					class: 'rmb-button rmb-button-ghost',
					text: __( 'Cancel', 'restaurant-menu-builder' ),
					onClick: close
				} ),
				submitButton
			] )
		] );

		var backdrop = el( 'div', { class: 'rmb-modal-backdrop rmb-ui' }, [ dialog ] );

		var closed = false;

		function close() {
			if ( closed ) {
				return;
			}

			closed = true;

			document.removeEventListener( 'keydown', onKeydown, true );
			backdrop.remove();

			if ( previouslyFocused && previouslyFocused.focus ) {
				previouslyFocused.focus();
			}

			if ( typeof config.onClose === 'function' ) {
				config.onClose();
			}
		}

		function onKeydown( event ) {
			if ( event.key === 'Escape' ) {
				event.preventDefault();
				close();
				return;
			}

			if ( event.key !== 'Tab' ) {
				return;
			}

			var focusable = dialog.querySelectorAll( 'button, input, select, textarea, a[href]' );

			if ( ! focusable.length ) {
				return;
			}

			var first = focusable[ 0 ];
			var last = focusable[ focusable.length - 1 ];

			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}

		submitButton.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			form.dispatchEvent( new Event( 'submit', { cancelable: true } ) );
		} );

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();

			submitButton.disabled = true;
			submitButton.classList.add( 'is-busy' );

			Promise.resolve( config.onSubmit() )
				.then( function ( result ) {
					if ( result !== false ) {
						close();
					}
				} )
				.catch( fail )
				.then( function () {
					submitButton.disabled = false;
					submitButton.classList.remove( 'is-busy' );
				} );
		} );

		backdrop.addEventListener( 'mousedown', function ( event ) {
			if ( event.target === backdrop ) {
				close();
			}
		} );

		document.addEventListener( 'keydown', onKeydown, true );
		document.body.appendChild( backdrop );

		var firstField = dialog.querySelector( 'input, select, textarea' );

		if ( firstField ) {
			firstField.focus();
		} else {
			submitButton.focus();
		}

		return { close: close, backdrop: backdrop, dialog: dialog };
	}

	function confirmDialog( config ) {
		return new Promise( function ( resolve ) {
			var settled = false;

			var modal = openModal( {
				title: config.title,
				submitLabel: config.confirmLabel || __( 'Confirm', 'restaurant-menu-builder' ),
				content: el( 'div', { class: 'rmb-confirm' }, [
					el( 'p', { text: config.message } ),
					config.detail ? el( 'p', { class: 'rmb-field-help', text: config.detail } ) : null
				] ),
				onSubmit: function () {
					settled = true;
					resolve( true );
				},
				onClose: function () {
					if ( ! settled ) {
						resolve( false );
					}
				}
			} );

			if ( config.danger ) {
				modal.backdrop.classList.add( 'is-danger' );
			}
		} );
	}

	/* --------------------------------------------------------- Form controls */

	function field( label, control, help ) {
		var id = 'rmb-f-' + Math.random().toString( 36 ).slice( 2, 9 );

		// When a wrapper is passed, point the label at the control inside it.
		var target = control;

		if ( ! /^(INPUT|SELECT|TEXTAREA)$/.test( control.tagName ) ) {
			target = control.querySelector( 'input, select, textarea' ) || control;
		}

		target.id = id;

		return el( 'div', { class: 'rmb-field' }, [
			el( 'label', { class: 'rmb-field-label', for: id, text: label } ),
			control,
			help ? el( 'p', { class: 'rmb-field-help', text: help } ) : null
		] );
	}

	function textInput( value, placeholder ) {
		return el( 'input', {
			type: 'text',
			class: 'rmb-input',
			value: value || '',
			placeholder: placeholder || ''
		} );
	}

	function numberInput( value, placeholder ) {
		var input = el( 'input', {
			type: 'number',
			class: 'rmb-input',
			step: '0.01',
			min: '0',
			placeholder: placeholder || '0.00'
		} );

		input.value = value === null || value === undefined ? '' : value;

		return input;
	}

	function select( options, selected ) {
		var node = el( 'select', { class: 'rmb-select' } );

		options.forEach( function ( option ) {
			var choice = el( 'option', { value: option.value, text: option.label } );

			if ( String( option.value ) === String( selected ) ) {
				choice.selected = true;
			}

			node.appendChild( choice );
		} );

		return node;
	}

	/* ------------------------------------------------------------------- Icons */

	var SVG_NS = 'http://www.w3.org/2000/svg';

	// Icons are built with createElementNS rather than innerHTML so path data
	// can never be interpreted as markup, whatever a filter put in the registry.
	function svgIcon( definition, size, className ) {
		var svg = document.createElementNS( SVG_NS, 'svg' );

		svg.setAttribute( 'viewBox', '0 0 24 24' );
		svg.setAttribute( 'width', size || 20 );
		svg.setAttribute( 'height', size || 20 );
		svg.setAttribute( 'fill', 'none' );
		svg.setAttribute( 'stroke', 'currentColor' );
		svg.setAttribute( 'stroke-width', '1.5' );
		svg.setAttribute( 'stroke-linecap', 'round' );
		svg.setAttribute( 'stroke-linejoin', 'round' );
		svg.setAttribute( 'aria-hidden', 'true' );
		svg.setAttribute( 'focusable', 'false' );
		svg.setAttribute( 'class', className || 'rmb-ui-icon' );

		( ( definition && definition.paths ) || [] ).forEach( function ( d ) {
			var path = document.createElementNS( SVG_NS, 'path' );
			path.setAttribute( 'd', d );
			svg.appendChild( path );
		} );

		( ( definition && definition.circles ) || [] ).forEach( function ( circle ) {
			var node = document.createElementNS( SVG_NS, 'circle' );
			node.setAttribute( 'cx', circle[ 0 ] );
			node.setAttribute( 'cy', circle[ 1 ] );
			node.setAttribute( 'r', circle[ 2 ] );
			svg.appendChild( node );
		} );

		return svg;
	}

	function uiIcon( name, size ) {
		var definition = ( D.ui || {} )[ name ];

		return definition ? svgIcon( definition, size || 18, 'rmb-ui-icon' ) : el( 'span', {} );
	}

	function findIcon( key ) {
		return ( D.icons || [] ).filter( function ( entry ) {
			return entry.key === key;
		} )[ 0 ];
	}

	function switchControl( label, checked ) {
		var input = el( 'input', { type: 'checkbox' } );
		input.checked = !! checked;

		var wrapper = el( 'label', { class: 'rmb-switch' }, [
			input,
			el( 'span', { class: 'rmb-switch-track', 'aria-hidden': 'true' } ),
			el( 'span', { text: label } )
		] );

		wrapper.input = input;

		return wrapper;
	}

	function iconPreview( key, size ) {
		var icon = findIcon( key );
		var wrapper = el( 'span', { class: 'rmb-icon-preview' } );

		if ( icon ) {
			wrapper.appendChild( svgIcon( icon, size || 20, 'rmb-icon' ) );
			wrapper.setAttribute( 'title', icon.label );
		}

		return wrapper;
	}

	/**
	 * Searchable icon grid. Returns the wrapper with getValue().
	 */
	function iconPicker( selected ) {
		var state = { value: selected || '' };
		var options = D.icons || [];
		var buttons = [];

		var currentLabel = el( 'span', { text: '' } );
		var currentPreview = iconPreview( state.value );

		var search = el( 'input', {
			type: 'search',
			class: 'rmb-input',
			placeholder: __( 'Search icons…', 'restaurant-menu-builder' ),
			'aria-label': __( 'Search icons', 'restaurant-menu-builder' )
		} );

		// The picker lives inside the modal form, where Enter would otherwise
		// submit the whole record mid-search. Instead it picks the first icon
		// still matching, which is what typing a name is usually aiming at.
		search.addEventListener( 'keydown', function ( event ) {
			if ( event.key !== 'Enter' ) {
				return;
			}

			event.preventDefault();

			var first = buttons.filter( function ( button ) {
				return ! button.hidden;
			} )[ 0 ];

			if ( first ) {
				first.click();
			}
		} );

		var grid = el( 'div', { class: 'rmb-icon-grid', role: 'group' } );
		var emptyMessage = el( 'p', { class: 'rmb-icon-empty', text: __( 'No icon matches that search.', 'restaurant-menu-builder' ) } );

		function syncCurrent() {
			var icon = findIcon( state.value );
			var fresh = iconPreview( state.value );

			currentPreview.replaceWith( fresh );
			currentPreview = fresh;
			currentLabel.textContent = icon ? icon.label : __( 'No icon', 'restaurant-menu-builder' );

			buttons.forEach( function ( button ) {
				button.setAttribute( 'aria-pressed', button.dataset.icon === state.value ? 'true' : 'false' );
			} );
		}

		function choose( key ) {
			state.value = state.value === key ? '' : key;
			syncCurrent();
		}

		// One button per icon, grouped by the registry's own grouping.
		var groups = [];

		options.forEach( function ( icon ) {
			var group = groups.filter( function ( entry ) {
				return entry.key === icon.group;
			} )[ 0 ];

			if ( ! group ) {
				group = { key: icon.group, label: icon.groupLabel, icons: [] };
				groups.push( group );
			}

			group.icons.push( icon );
		} );

		groups.forEach( function ( group ) {
			var label = el( 'p', { class: 'rmb-icon-group-label', text: group.label } );
			var row = el( 'div', { class: 'rmb-icon-row-grid' } );

			group.icons.forEach( function ( icon ) {
				var button = el( 'button', {
					type: 'button',
					class: 'rmb-icon-option',
					'aria-pressed': 'false',
					'aria-label': icon.label,
					title: icon.label,
					onClick: function () {
						choose( icon.key );
					}
				} );

				button.dataset.icon = icon.key;
				button.dataset.search = ( icon.label + ' ' + icon.key + ' ' + group.label ).toLowerCase();
				button.appendChild( svgIcon( icon, 22, 'rmb-icon' ) );

				buttons.push( button );
				row.appendChild( button );
			} );

			grid.appendChild( label );
			grid.appendChild( row );
			group.nodes = { label: label, row: row };
		} );

		grid.appendChild( emptyMessage );
		emptyMessage.hidden = true;

		search.addEventListener( 'input', function () {
			var term = search.value.trim().toLowerCase();
			var visible = 0;

			groups.forEach( function ( group ) {
				var shown = 0;

				group.icons.forEach( function ( icon, index ) {
					var button = group.nodes.row.children[ index ];
					var match = ! term || button.dataset.search.indexOf( term ) !== -1;

					button.hidden = ! match;

					if ( match ) {
						shown++;
					}
				} );

				group.nodes.label.hidden = shown === 0;
				group.nodes.row.hidden = shown === 0;
				visible += shown;
			} );

			emptyMessage.hidden = visible > 0;
		} );

		var clearButton = el( 'button', {
			type: 'button',
			class: 'rmb-button rmb-button-ghost rmb-button-small',
			text: __( 'Clear', 'restaurant-menu-builder' ),
			onClick: function () {
				state.value = '';
				syncCurrent();
			}
		} );

		var wrapper = el( 'div', { class: 'rmb-icon-picker' }, [
			el( 'div', { class: 'rmb-icon-picker-bar' }, [
				search,
				el( 'span', { class: 'rmb-icon-current' }, [ currentPreview, currentLabel ] ),
				clearButton
			] ),
			grid
		] );

		syncCurrent();

		wrapper.getValue = function () {
			return state.value;
		};

		return wrapper;
	}

	function imagePicker( imageId, imageUrl ) {
		var state = { id: imageId || 0, url: imageUrl || '' };

		var thumb = el( 'div', { class: 'rmb-image-thumb' } );
		var removeButton = el( 'button', {
			type: 'button',
			class: 'rmb-button rmb-button-ghost rmb-button-small',
			text: __( 'Remove', 'restaurant-menu-builder' ),
			onClick: function () {
				state.id = 0;
				state.url = '';
				paint();
			}
		} );

		var chooseButton = el( 'button', {
			type: 'button',
			class: 'rmb-button rmb-button-secondary rmb-button-small',
			text: __( 'Choose image', 'restaurant-menu-builder' ),
			onClick: function () {
				if ( ! window.wp || ! window.wp.media ) {
					notify( __( 'The media library is unavailable on this screen.', 'restaurant-menu-builder' ), 'error' );
					return;
				}

				var frame = window.wp.media( {
					title: __( 'Choose an image', 'restaurant-menu-builder' ),
					button: { text: __( 'Use this image', 'restaurant-menu-builder' ) },
					library: { type: 'image' },
					multiple: false
				} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();

					state.id = attachment.id;
					state.url =
						( attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url ) || attachment.url;

					paint();
				} );

				frame.open();
			}
		} );

		function paint() {
			clear( thumb );

			if ( state.url ) {
				thumb.appendChild( el( 'img', { src: state.url, alt: '' } ) );
				chooseButton.textContent = __( 'Change image', 'restaurant-menu-builder' );
				removeButton.hidden = false;
			} else {
				thumb.appendChild( uiIcon( 'image', 22 ) );
				chooseButton.textContent = __( 'Choose image', 'restaurant-menu-builder' );
				removeButton.hidden = true;
			}
		}

		paint();

		var wrapper = el( 'div', { class: 'rmb-image-picker' }, [
			thumb,
			el( 'div', { class: 'rmb-image-actions' }, [ chooseButton, removeButton ] )
		] );

		wrapper.getValue = function () {
			return state.id;
		};

		return wrapper;
	}

	/* -------------------------------------------------------------- Sortable */

	function makeSortable( list, options ) {
		var dragging = null;
		var startY = 0;
		var moved = false;

		function rows() {
			return Array.prototype.slice.call( list.querySelectorAll( ':scope > [data-sort-id]' ) );
		}

		function commit() {
			var order = rows().map( function ( row ) {
				return parseInt( row.getAttribute( 'data-sort-id' ), 10 );
			} );

			options.onDrop( order );
		}

		list.addEventListener( 'pointerdown', function ( event ) {
			var handle = event.target.closest( '[data-drag-handle]' );

			if ( ! handle || event.button !== 0 ) {
				return;
			}

			var row = handle.closest( '[data-sort-id]' );

			if ( ! row || row.parentNode !== list ) {
				return;
			}

			dragging = row;
			startY = event.clientY;
			moved = false;

			row.classList.add( 'is-dragging' );
			handle.setPointerCapture( event.pointerId );
			event.preventDefault();
		} );

		list.addEventListener( 'pointermove', function ( event ) {
			if ( ! dragging ) {
				return;
			}

			if ( ! moved && Math.abs( event.clientY - startY ) < 4 ) {
				return;
			}

			moved = true;

			var siblings = rows().filter( function ( row ) {
				return row !== dragging;
			} );

			var placed = false;

			for ( var i = 0; i < siblings.length; i++ ) {
				var rect = siblings[ i ].getBoundingClientRect();

				if ( event.clientY < rect.top + rect.height / 2 ) {
					list.insertBefore( dragging, siblings[ i ] );
					placed = true;
					break;
				}
			}

			if ( ! placed ) {
				list.appendChild( dragging );
			}
		} );

		function stop() {
			if ( ! dragging ) {
				return;
			}

			dragging.classList.remove( 'is-dragging' );
			dragging = null;

			if ( moved ) {
				commit();
			}

			moved = false;
		}

		list.addEventListener( 'pointerup', stop );
		list.addEventListener( 'pointercancel', stop );

		// Keyboard equivalent so reordering never depends on a pointer.
		list.addEventListener( 'click', function ( event ) {
			var button = event.target.closest( '[data-move]' );

			if ( ! button ) {
				return;
			}

			var row = button.closest( '[data-sort-id]' );

			if ( ! row || row.parentNode !== list ) {
				return;
			}

			var direction = button.getAttribute( 'data-move' ) === 'up' ? -1 : 1;
			var current = rows();
			var index = current.indexOf( row );
			var target = index + direction;

			if ( target < 0 || target >= current.length ) {
				return;
			}

			if ( direction === -1 ) {
				list.insertBefore( row, current[ target ] );
			} else {
				list.insertBefore( current[ target ], row );
			}

			commit();

			var refocus = row.querySelector( '[data-move="' + ( direction === -1 ? 'up' : 'down' ) + '"]' );

			if ( refocus ) {
				refocus.focus();
			}
		} );
	}

	window.rmbAdmin = {
		el: el,
		clear: clear,
		debounce: debounce,
		api: api,
		notify: notify,
		fail: fail,
		openModal: openModal,
		confirmDialog: confirmDialog,
		field: field,
		textInput: textInput,
		numberInput: numberInput,
		select: select,
		switchControl: switchControl,
		uiIcon: uiIcon,
		svgIcon: svgIcon,
		iconPreview: iconPreview,
		iconPicker: iconPicker,
		imagePicker: imagePicker,
		makeSortable: makeSortable,
		root: root,
		data: D,
		__: __,
		sprintf: sprintf
	};
}() );
