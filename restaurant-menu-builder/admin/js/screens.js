/**
 * Restaurant Menu Builder — admin screen controllers.
 *
 * Depends on admin.js, which exposes the shared helpers on window.rmbAdmin.
 */
( function () {
	'use strict';

	var A = window.rmbAdmin;

	if ( ! A ) {
		return;
	}

	var el = A.el;
	var clear = A.clear;
	var api = A.api;
	var notify = A.notify;
	var fail = A.fail;
	var __ = A.__;
	var sprintf = A.sprintf;
	var D = A.data;
	var root = A.root;

	var screen = root.getAttribute( 'data-screen' );
	var menuId = parseInt( root.getAttribute( 'data-menu' ), 10 ) || 0;

	function editorUrl( id ) {
		return D.adminUrl + '?page=' + ( ( D.pages && D.pages.menus ) || 'rmb-menus-list' ) + '&view=edit&menu=' + id;
	}

	function statusLabel( status ) {
		return status === 'active' ? __( 'Active', 'restaurant-menu-builder' ) : __( 'Draft', 'restaurant-menu-builder' );
	}

	function dragHandle() {
		return el(
			'span',
			{
				class: 'rmb-drag',
				'data-drag-handle': '',
				'aria-hidden': 'true',
				title: __( 'Drag to reorder', 'restaurant-menu-builder' )
			},
			[ A.uiIcon( 'grip', 16 ) ]
		);
	}

	function moveButtons( label ) {
		return el( 'span', { class: 'rmb-move' }, [
			el(
				'button',
				{
					type: 'button',
					class: 'rmb-icon-button',
					'data-move': 'up',
					'aria-label': sprintf( __( 'Move %s up', 'restaurant-menu-builder' ), label )
				},
				[ A.uiIcon( 'chevron_up', 14 ) ]
			),
			el(
				'button',
				{
					type: 'button',
					class: 'rmb-icon-button',
					'data-move': 'down',
					'aria-label': sprintf( __( 'Move %s down', 'restaurant-menu-builder' ), label )
				},
				[ A.uiIcon( 'chevron_down', 14 ) ]
			)
		] );
	}

	/**
	 * Icon-only row action. The label stays available to screen readers and as a
	 * tooltip, so the icon is never the only thing carrying the meaning.
	 */
	function actionButton( icon, label, onClick, danger ) {
		return el(
			'button',
			{
				type: 'button',
				class: 'rmb-icon-button' + ( danger ? ' rmb-danger' : '' ),
				'aria-label': label,
				title: label,
				onClick: onClick
			},
			[ A.uiIcon( icon, 16 ) ]
		);
	}

	function emptyState( title, message, actionLabel, onAction, icon ) {
		return el( 'div', { class: 'rmb-empty' }, [
			el( 'span', { class: 'rmb-empty-icon' }, [ A.uiIcon( icon || 'menus', 24 ) ] ),
			el( 'h2', { text: title } ),
			el( 'p', { text: message } ),
			actionLabel
				? el(
						'button',
						{
							type: 'button',
							class: 'rmb-button rmb-button-primary',
							onClick: onAction
						},
						[ A.uiIcon( 'plus', 16 ), el( 'span', { text: actionLabel } ) ]
				  )
				: null
		] );
	}

	/* ----------------------------------------------------------- Category form */

	function categoryForm( category, targetMenuId, onSaved ) {
		var isNew = ! category;
		var data = category || { name: '', description: '', icon: '', image_id: 0, image: '', status: 'active' };

		var nameInput = A.textInput( data.name, __( 'Starters', 'restaurant-menu-builder' ) );
		var descriptionInput = el( 'textarea', {
			class: 'rmb-textarea',
			rows: '2',
			placeholder: __( 'Shown under the category title', 'restaurant-menu-builder' )
		} );
		descriptionInput.value = data.description || '';

		var iconPicker = A.iconPicker( data.icon );
		var picker = A.imagePicker( data.image_id, data.image );
		var statusSelect = A.select(
			[
				{ value: 'active', label: __( 'Active', 'restaurant-menu-builder' ) },
				{ value: 'draft', label: __( 'Draft — hidden from visitors', 'restaurant-menu-builder' ) }
			],
			data.status
		);

		var content = el( 'div', {}, [
			A.field( __( 'Name', 'restaurant-menu-builder' ), nameInput ),
			A.field( __( 'Description', 'restaurant-menu-builder' ), descriptionInput ),
			el( 'div', { class: 'rmb-field' }, [
				el( 'span', { class: 'rmb-field-label', text: __( 'Icon', 'restaurant-menu-builder' ) } ),
				iconPicker,
				el( 'p', {
					class: 'rmb-field-help',
					text: __( 'Shown beside the category in the menu navigation. Search by name, or clear it to use no icon.', 'restaurant-menu-builder' )
				} )
			] ),
			el( 'div', { class: 'rmb-field' }, [
				el( 'span', { class: 'rmb-field-label', text: __( 'Image', 'restaurant-menu-builder' ) } ),
				picker
			] ),
			A.field( __( 'Status', 'restaurant-menu-builder' ), statusSelect )
		] );

		A.openModal( {
			title: isNew ? __( 'Add category', 'restaurant-menu-builder' ) : __( 'Edit category', 'restaurant-menu-builder' ),
			submitLabel: isNew ? __( 'Add category', 'restaurant-menu-builder' ) : __( 'Save category', 'restaurant-menu-builder' ),
			content: content,
			onSubmit: function () {
				var name = nameInput.value.trim();

				if ( ! name ) {
					notify( __( 'Enter a category name.', 'restaurant-menu-builder' ), 'error' );
					nameInput.focus();
					return false;
				}

				var body = {
					name: name,
					description: descriptionInput.value,
					icon: iconPicker.getValue(),
					image_id: picker.getValue(),
					status: statusSelect.value
				};

				if ( isNew ) {
					body.menu_id = targetMenuId;
				}

				return api( isNew ? '/categories' : '/categories/' + data.id, {
					method: 'POST',
					body: body
				} ).then( function ( response ) {
					notify( response.message );
					onSaved();
				} );
			}
		} );
	}

	/* --------------------------------------------------------------- Item form */

	function variationRow( variation, onRemove ) {
		var label = A.textInput( variation.label, __( 'Small', 'restaurant-menu-builder' ) );
		var price = A.numberInput( variation.price, '0.00' );
		var sale = A.numberInput( variation.sale_price, __( 'Sale', 'restaurant-menu-builder' ) );

		var row = el( 'div', { class: 'rmb-variation' }, [
			label,
			price,
			sale,
			el( 'button', {
				type: 'button',
				class: 'rmb-icon-button',
				'aria-label': __( 'Remove this size', 'restaurant-menu-builder' ),
				text: '×',
				onClick: function () {
					row.remove();
					onRemove();
				}
			} )
		] );

		row.getValue = function () {
			return {
				label: label.value,
				price: price.value,
				sale_price: sale.value
			};
		};

		return row;
	}

	function itemForm( item, categories, defaultCategoryId, onSaved ) {
		var isNew = ! item;
		var data = item || {
			name: '',
			description: '',
			image_id: 0,
			image: '',
			price: null,
			sale_price: null,
			price_type: 'single',
			variations: [],
			badge: '',
			status: 'active'
		};

		var nameInput = A.textInput( data.name, __( 'Margherita', 'restaurant-menu-builder' ) );

		var descriptionInput = el( 'textarea', {
			class: 'rmb-textarea',
			rows: '3',
			placeholder: __( 'Tomato, mozzarella, basil', 'restaurant-menu-builder' )
		} );
		descriptionInput.value = data.description || '';

		var categorySelect = A.select(
			categories.map( function ( category ) {
				return { value: category.id, label: category.name };
			} ),
			isNew ? defaultCategoryId : data.category_id
		);

		var picker = A.imagePicker( data.image_id, data.image );
		var badgeInput = A.textInput( data.badge, __( '2 pcs', 'restaurant-menu-builder' ) );

		var priceTypeSelect = A.select(
			[
				{ value: 'single', label: __( 'One price', 'restaurant-menu-builder' ) },
				{ value: 'multiple', label: __( 'Several sizes', 'restaurant-menu-builder' ) }
			],
			data.price_type
		);

		var priceInput = A.numberInput( data.price, '0.00' );
		var salePriceInput = A.numberInput( data.sale_price, '0.00' );

		var singleBlock = el( 'div', { class: 'rmb-price-grid' }, [
			A.field( __( 'Price', 'restaurant-menu-builder' ), priceInput ),
			A.field(
				__( 'Sale price', 'restaurant-menu-builder' ),
				salePriceInput,
				__( 'Leave empty unless the dish is discounted. It must be lower than the price.', 'restaurant-menu-builder' )
			)
		] );

		var variationList = el( 'div', { class: 'rmb-variations' } );
		var addVariation = el( 'button', {
			type: 'button',
			class: 'rmb-button rmb-button-secondary rmb-button-small',
			text: __( 'Add a size', 'restaurant-menu-builder' ),
			onClick: function () {
				appendVariation( { label: '', price: '', sale_price: '' } );
			}
		} );

		function refreshAddState() {
			addVariation.disabled = variationList.children.length >= ( D.maxVariations || 8 );
		}

		function appendVariation( variation ) {
			variationList.appendChild( variationRow( variation, refreshAddState ) );
			refreshAddState();
		}

		( data.variations || [] ).forEach( appendVariation );

		if ( ! variationList.children.length ) {
			appendVariation( { label: __( 'Small', 'restaurant-menu-builder' ), price: '', sale_price: '' } );
		}

		var multiBlock = el( 'div', {}, [
			el( 'div', { class: 'rmb-variation rmb-variation-head' }, [
				el( 'span', { text: __( 'Size', 'restaurant-menu-builder' ) } ),
				el( 'span', { text: __( 'Price', 'restaurant-menu-builder' ) } ),
				el( 'span', { text: __( 'Sale price', 'restaurant-menu-builder' ) } ),
				el( 'span', {} )
			] ),
			variationList,
			addVariation
		] );

		function syncPriceBlocks() {
			var multiple = priceTypeSelect.value === 'multiple';
			singleBlock.hidden = multiple;
			multiBlock.hidden = ! multiple;
		}

		priceTypeSelect.addEventListener( 'change', syncPriceBlocks );

		var statusSelect = A.select(
			[
				{ value: 'active', label: __( 'Active', 'restaurant-menu-builder' ) },
				{ value: 'draft', label: __( 'Draft — hidden from visitors', 'restaurant-menu-builder' ) }
			],
			data.status
		);

		var content = el( 'div', {}, [
			A.field( __( 'Name', 'restaurant-menu-builder' ), nameInput ),
			A.field( __( 'Description', 'restaurant-menu-builder' ), descriptionInput ),
			A.field( __( 'Category', 'restaurant-menu-builder' ), categorySelect ),
			el( 'div', { class: 'rmb-field' }, [
				el( 'span', { class: 'rmb-field-label', text: __( 'Image', 'restaurant-menu-builder' ) } ),
				picker
			] ),
			A.field( __( 'Pricing', 'restaurant-menu-builder' ), priceTypeSelect ),
			singleBlock,
			multiBlock,
			A.field( __( 'Badge', 'restaurant-menu-builder' ), badgeInput, __( 'A short note beside the name, such as a portion size.', 'restaurant-menu-builder' ) ),
			A.field( __( 'Status', 'restaurant-menu-builder' ), statusSelect )
		] );

		syncPriceBlocks();

		A.openModal( {
			title: isNew ? __( 'Add item', 'restaurant-menu-builder' ) : __( 'Edit item', 'restaurant-menu-builder' ),
			submitLabel: isNew ? __( 'Add item', 'restaurant-menu-builder' ) : __( 'Save item', 'restaurant-menu-builder' ),
			content: content,
			onSubmit: function () {
				var name = nameInput.value.trim();

				if ( ! name ) {
					notify( __( 'Enter an item name.', 'restaurant-menu-builder' ), 'error' );
					nameInput.focus();
					return false;
				}

				var variations = Array.prototype.slice.call( variationList.children ).map( function ( row ) {
					return row.getValue();
				} );

				var body = {
					name: name,
					description: descriptionInput.value,
					category_id: parseInt( categorySelect.value, 10 ),
					image_id: picker.getValue(),
					price_type: priceTypeSelect.value,
					price: priceInput.value,
					sale_price: salePriceInput.value,
					variations: variations,
					badge: badgeInput.value,
					status: statusSelect.value
				};

				return api( isNew ? '/items' : '/items/' + data.id, {
					method: 'POST',
					body: body
				} ).then( function ( response ) {
					notify( response.message );
					onSaved();
				} );
			}
		} );
	}

	/* ------------------------------------------------------- Category & items */

	function CategoryList( container, options ) {
		var state = { categories: [] };

		function load() {
			return api( '/menus/' + options.menuId + '/tree' ).then( function ( response ) {
				state.categories = response.categories;
				render();

				if ( options.onLoad ) {
					options.onLoad( state.categories );
				}
			} );
		}

		function itemRow( item, category ) {
			// Formatted server side in the site currency.
			var priceText = item.price_display || '';

			return el( 'li', { class: 'rmb-row rmb-row-item', 'data-sort-id': item.id }, [
				dragHandle(),
				item.image
					? el( 'img', { class: 'rmb-row-thumb', src: item.image, alt: '' } )
					: el( 'span', { class: 'rmb-row-thumb is-empty', 'aria-hidden': 'true' } ),
				el( 'span', { class: 'rmb-row-main' }, [
					el( 'span', { class: 'rmb-row-name', text: item.name } ),
					item.description ? el( 'span', { class: 'rmb-row-sub', text: item.description } ) : null
				] ),
				el( 'span', { class: 'rmb-row-meta' }, [
					priceText ? el( 'span', { class: 'rmb-row-price', text: priceText } ) : null,
					item.status !== 'active'
						? el( 'span', { class: 'rmb-status-chip', 'data-status': 'draft', text: statusLabel( item.status ) } )
						: null,
					moveButtons( item.name ),
					el( 'span', { class: 'rmb-row-actions' }, [
					actionButton( 'edit', __( 'Edit item', 'restaurant-menu-builder' ), function () {
						itemForm( item, state.categories, category.id, refresh );
					} ),
					actionButton( 'copy', __( 'Duplicate item', 'restaurant-menu-builder' ), function () {
						api( '/items/' + item.id + '/duplicate', { method: 'POST' } )
							.then( function ( response ) {
								notify( response.message );
								refresh();
							} )
							.catch( fail );
					} ),
					actionButton(
						'trash',
						__( 'Delete item', 'restaurant-menu-builder' ),
						function () {
							A.confirmDialog( {
								title: __( 'Delete this item?', 'restaurant-menu-builder' ),
								message: sprintf( __( '“%s” will be removed from the menu.', 'restaurant-menu-builder' ), item.name ),
								confirmLabel: __( 'Delete item', 'restaurant-menu-builder' ),
								danger: true
							} ).then( function ( confirmed ) {
								if ( ! confirmed ) {
									return;
								}

								api( '/items/' + item.id, { method: 'DELETE' } )
									.then( function ( response ) {
										notify( response.message );
										refresh();
									} )
									.catch( fail );
							} );
						},
						true
					)
					] )
				] )
			] );
		}

		function categoryCard( category ) {
			var itemList = el( 'ul', { class: 'rmb-item-list' } );

			if ( options.withItems ) {
				if ( ! category.items.length ) {
					itemList.appendChild(
						el( 'li', { class: 'rmb-row-empty', text: __( 'No items in this category yet.', 'restaurant-menu-builder' ) } )
					);
				} else {
					category.items.forEach( function ( item ) {
						itemList.appendChild( itemRow( item, category ) );
					} );

					A.makeSortable( itemList, {
						onDrop: function ( order ) {
							api( '/items/reorder', {
								method: 'POST',
								body: { category_id: category.id, order: order }
							} )
								.then( function () {
									if ( options.onChange ) {
										options.onChange();
									}
								} )
								.catch( function ( error ) {
									fail( error );
									refresh();
								} );
						}
					} );
				}
			}

			var head = el( 'div', { class: 'rmb-row rmb-row-category' }, [
				dragHandle(),
				A.iconPreview( category.icon ),
				el( 'span', { class: 'rmb-row-main' }, [
					el( 'span', { class: 'rmb-row-name', text: category.name } ),
					el( 'span', {
						class: 'rmb-row-sub',
						text: sprintf(
							/* translators: %d: number of items. */
							__( '%d items', 'restaurant-menu-builder' ),
							category.items.length
						)
					} )
				] ),
				el( 'span', { class: 'rmb-row-meta' }, [
					category.status !== 'active'
						? el( 'span', { class: 'rmb-status-chip', 'data-status': 'draft', text: statusLabel( category.status ) } )
						: null,
					moveButtons( category.name ),
					el( 'span', { class: 'rmb-row-actions' }, [
					options.withItems
						? el(
								'button',
								{
									type: 'button',
									class: 'rmb-button rmb-button-secondary rmb-button-small',
									onClick: function () {
										itemForm( null, state.categories, category.id, refresh );
									}
								},
								[ A.uiIcon( 'plus', 14 ), el( 'span', { text: __( 'Add item', 'restaurant-menu-builder' ) } ) ]
						  )
						: null,
					actionButton( 'edit', __( 'Edit category', 'restaurant-menu-builder' ), function () {
						categoryForm( category, options.menuId, refresh );
					} ),
					actionButton( 'copy', __( 'Duplicate category', 'restaurant-menu-builder' ), function () {
						api( '/categories/' + category.id + '/duplicate', { method: 'POST' } )
							.then( function ( response ) {
								notify( response.message );
								refresh();
							} )
							.catch( fail );
					} ),
					actionButton(
						'trash',
						__( 'Delete category', 'restaurant-menu-builder' ),
						function () {
							A.confirmDialog( {
								title: __( 'Delete this category?', 'restaurant-menu-builder' ),
								message: sprintf(
									/* translators: %s: category name. */
									__( '“%s” and everything inside it will be removed.', 'restaurant-menu-builder' ),
									category.name
								),
								detail: sprintf(
									/* translators: %d: number of items. */
									__( '%d items will be deleted with it.', 'restaurant-menu-builder' ),
									category.items.length
								),
								confirmLabel: __( 'Delete category', 'restaurant-menu-builder' ),
								danger: true
							} ).then( function ( confirmed ) {
								if ( ! confirmed ) {
									return;
								}

								api( '/categories/' + category.id, { method: 'DELETE' } )
									.then( function ( response ) {
										notify( response.message );
										refresh();
									} )
									.catch( fail );
							} );
						},
						true
					)
					] )
				] )
			] );

			return el( 'li', { class: 'rmb-category-card', 'data-sort-id': category.id }, [
				head,
				options.withItems ? itemList : null
			] );
		}

		function render() {
			clear( container );

			if ( ! state.categories.length ) {
				container.appendChild(
					emptyState(
						__( 'No categories yet', 'restaurant-menu-builder' ),
						__( 'Categories are the sections of your menu, such as Starters or Drinks.', 'restaurant-menu-builder' ),
						__( 'Add your first category', 'restaurant-menu-builder' ),
						function () {
							categoryForm( null, options.menuId, refresh );
						},
						'category'
					)
				);

				return;
			}

			var list = el( 'ul', { class: 'rmb-category-list' } );

			state.categories.forEach( function ( category ) {
				list.appendChild( categoryCard( category ) );
			} );

			A.makeSortable( list, {
				onDrop: function ( order ) {
					api( '/categories/reorder', {
						method: 'POST',
						body: { menu_id: options.menuId, order: order }
					} )
						.then( function () {
							if ( options.onChange ) {
								options.onChange();
							}
						} )
						.catch( function ( error ) {
							fail( error );
							refresh();
						} );
				}
			} );

			container.appendChild( list );
		}

		function refresh() {
			load()
				.then( function () {
					if ( options.onChange ) {
						options.onChange();
					}
				} )
				.catch( fail );
		}

		return {
			load: function () {
				return load().catch( function ( error ) {
					clear( container );
					container.appendChild( el( 'p', { class: 'rmb-error', text: error.message } ) );
				} );
			},
			refresh: refresh,
			categories: function () {
				return state.categories;
			},
			addCategory: function () {
				categoryForm( null, options.menuId, refresh );
			},
			addItem: function () {
				if ( ! state.categories.length ) {
					notify( __( 'Add a category before adding items.', 'restaurant-menu-builder' ), 'error' );
					return;
				}

				itemForm( null, state.categories, state.categories[ 0 ].id, refresh );
			}
		};
	}

	/* ----------------------------------------------------------- Style editor */

	/**
	 * Grouped, typed style editor.
	 *
	 * Every control is built from the schema the server sends, so a setting
	 * added in PHP appears here without touching this file.
	 */
	function StyleEditor( container, initial, onInput ) {
		var values = Object.assign( {}, D.defaults.style, initial );
		var groups = D.styleGroups || {};
		var panels = {};
		var tabButtons = [];

		function commit() {
			onInput( values );
		}

		/* -------------------------------------------------------- Presets */

		function presetButton( preset ) {
			var swatches = el( 'span', { class: 'rmb-preset-swatches', 'aria-hidden': 'true' } );

			( preset.swatches || [] ).forEach( function ( color ) {
				var dot = el( 'span', { class: 'rmb-preset-swatch' } );
				dot.style.background = color;
				swatches.appendChild( dot );
			} );

			return el(
				'button',
				{
					type: 'button',
					class: 'rmb-preset',
					'data-preset': preset.key,
					onClick: function () {
						// A preset fills every field; the editor is rebuilt so the
						// controls show the new values, and the change is not saved
						// until the user presses Save.
						values = Object.assign( {}, D.defaults.style, preset.style );
						rebuild();
						commit();
						notify(
							sprintf(
								/* translators: %s: preset name. */
								__( '%s applied. Save to keep it.', 'restaurant-menu-builder' ),
								preset.label
							)
						);
					}
				},
				[ swatches, el( 'span', { class: 'rmb-preset-label', text: preset.label } ) ]
			);
		}

		function presetsBlock() {
			if ( ! ( D.presets || [] ).length ) {
				return null;
			}

			var grid = el( 'div', { class: 'rmb-preset-grid' } );

			D.presets.forEach( function ( preset ) {
				grid.appendChild( presetButton( preset ) );
			} );

			return el( 'div', { class: 'rmb-style-presets' }, [
				el( 'p', { class: 'rmb-field-label', text: __( 'Start from a look', 'restaurant-menu-builder' ) } ),
				grid,
				el( 'p', {
					class: 'rmb-field-help',
					text: __( 'A preset fills every control below. Adjust anything afterwards.', 'restaurant-menu-builder' )
				} )
			] );
		}

		/* -------------------------------------------------------- Controls */

		function colorControl( definition ) {
			var colorInput = el( 'input', { type: 'color', class: 'rmb-color', value: values[ definition.key ] } );
			var hexInput = el( 'input', { type: 'text', class: 'rmb-input rmb-input-hex', value: values[ definition.key ] } );

			colorInput.addEventListener( 'input', function () {
				values[ definition.key ] = colorInput.value;
				hexInput.value = colorInput.value;
				commit();
			} );

			hexInput.addEventListener( 'change', function () {
				if ( ! /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( hexInput.value.trim() ) ) {
					hexInput.value = values[ definition.key ];
					notify( __( 'Use a hex colour such as #1f2933.', 'restaurant-menu-builder' ), 'error' );
					return;
				}

				values[ definition.key ] = hexInput.value.trim().toLowerCase();
				colorInput.value = values[ definition.key ];
				commit();
			} );

			return el( 'div', { class: 'rmb-color-row' }, [ colorInput, hexInput ] );
		}

		function selectControl( definition ) {
			var choices = definition.choices || {};
			var options = Object.keys( choices ).map( function ( key ) {
				return { value: key, label: choices[ key ] };
			} );

			var select = A.select( options, values[ definition.key ] );

			select.addEventListener( 'change', function () {
				values[ definition.key ] = select.value;
				commit();
			} );

			return select;
		}

		function toggleControl( definition ) {
			var toggle = A.switchControl( definition.label, values[ definition.key ] );

			toggle.input.addEventListener( 'change', function () {
				values[ definition.key ] = toggle.input.checked;
				commit();
			} );

			return toggle;
		}

		function sizeControl( definition ) {
			var input = A.textInput( values[ definition.key ], '16px' );

			input.addEventListener( 'change', function () {
				values[ definition.key ] = input.value.trim();
				commit();
			} );

			return input;
		}

		function controlFor( definition ) {
			if ( definition.type === 'color' ) {
				return colorControl( definition );
			}

			if ( definition.type === 'select' ) {
				return selectControl( definition );
			}

			if ( definition.type === 'toggle' ) {
				return toggleControl( definition );
			}

			return sizeControl( definition );
		}

		/* ---------------------------------------------------------- Build */

		function rebuild() {
			clear( container );
			panels = {};
			tabButtons = [];

			var presets = presetsBlock();

			if ( presets ) {
				container.appendChild( presets );
			}

			var groupKeys = Object.keys( groups ).filter( function ( key ) {
				return ( D.styleFields || [] ).some( function ( field ) {
					return field.group === key;
				} );
			} );

			var tabs = el( 'nav', { class: 'rmb-subtabs', role: 'tablist' } );

			groupKeys.forEach( function ( key, index ) {
				var button = el( 'button', {
					type: 'button',
					class: 'rmb-subtab' + ( index === 0 ? ' is-active' : '' ),
					role: 'tab',
					'aria-selected': index === 0 ? 'true' : 'false',
					text: groups[ key ],
					onClick: function () {
						tabButtons.forEach( function ( other ) {
							var active = other === button;
							other.classList.toggle( 'is-active', active );
							other.setAttribute( 'aria-selected', active ? 'true' : 'false' );
						} );

						groupKeys.forEach( function ( panelKey ) {
							panels[ panelKey ].classList.toggle( 'is-active', panelKey === key );
						} );
					}
				} );

				tabButtons.push( button );
				tabs.appendChild( button );

				panels[ key ] = el( 'div', {
					class: 'rmb-style-panel' + ( index === 0 ? ' is-active' : '' ),
					role: 'tabpanel'
				} );
			} );

			if ( groupKeys.length > 1 ) {
				container.appendChild( tabs );
			}

			( D.styleFields || [] ).forEach( function ( definition ) {
				var panel = panels[ definition.group ];

				if ( ! panel ) {
					return;
				}

				var control = controlFor( definition );

				// A toggle already carries its own label text.
				if ( definition.type === 'toggle' ) {
					panel.appendChild(
						el( 'div', { class: 'rmb-field' }, [
							control,
							definition.help ? el( 'p', { class: 'rmb-field-help', text: definition.help } ) : null
						] )
					);

					return;
				}

				panel.appendChild( A.field( definition.label, control, definition.help ) );
			} );

			groupKeys.forEach( function ( key ) {
				container.appendChild( panels[ key ] );
			} );
		}

		rebuild();

		return {
			values: function () {
				return values;
			},
			reset: function ( defaults ) {
				values = Object.assign( {}, defaults );
				rebuild();
				commit();

				// Rebuilding in place keeps the same instance valid.
				return this;
			}
		};
	}

	/* --------------------------------------------------------------- Preview */

	function Preview( frame ) {
		var lastHeight = 0;

		function paint( payload ) {
			var doc =
				'<!doctype html><html><head><meta charset="utf-8">' +
				'<meta name="viewport" content="width=device-width, initial-scale=1">' +
				'<link rel="stylesheet" href="' + payload.stylesheet + '">' +
				'<style>html,body{margin:0;padding:24px;background:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}</style>' +
				( payload.global_css ? '<style>' + payload.global_css + '</style>' : '' ) +
				'</head><body>' +
				payload.html +
				'<script src="' + payload.script + '"><\/script></body></html>';

			frame.srcdoc = doc;
		}

		frame.addEventListener( 'load', function () {
			try {
				var body = frame.contentDocument && frame.contentDocument.body;

				if ( ! body ) {
					return;
				}

				var height = Math.max( body.scrollHeight, 320 );

				if ( Math.abs( height - lastHeight ) > 4 ) {
					lastHeight = height;
					frame.style.height = height + 'px';
				}
			} catch ( error ) {
				frame.style.height = '640px';
			}
		} );

		return { paint: paint };
	}

	function bindPreviewSizes( pane, frame ) {
		var buttons = pane.querySelectorAll( '.rmb-size' );

		Array.prototype.forEach.call( buttons, function ( button ) {
			button.addEventListener( 'click', function () {
				Array.prototype.forEach.call( buttons, function ( other ) {
					other.classList.toggle( 'is-active', other === button );
				} );

				var width = button.getAttribute( 'data-width' );
				frame.style.width = width === 'full' ? '100%' : width + 'px';
			} );
		} );
	}

	function createMenu() {
		var nameInput = A.textInput( '', __( 'Main menu', 'restaurant-menu-builder' ) );
		var statusSelect = A.select(
			[
				{ value: 'draft', label: __( 'Draft — build it privately first', 'restaurant-menu-builder' ) },
				{ value: 'active', label: __( 'Active — visible wherever the shortcode is placed', 'restaurant-menu-builder' ) }
			],
			'draft'
		);

		A.openModal( {
			title: __( 'Add a menu', 'restaurant-menu-builder' ),
			submitLabel: __( 'Create menu', 'restaurant-menu-builder' ),
			content: el( 'div', {}, [
				A.field( __( 'Menu name', 'restaurant-menu-builder' ), nameInput ),
				A.field( __( 'Status', 'restaurant-menu-builder' ), statusSelect )
			] ),
			onSubmit: function () {
				var name = nameInput.value.trim();

				if ( ! name ) {
					notify( __( 'Enter a menu name.', 'restaurant-menu-builder' ), 'error' );
					nameInput.focus();
					return false;
				}

				return api( '/menus', {
					method: 'POST',
					body: { name: name, status: statusSelect.value }
				} ).then( function ( response ) {
					window.location.href = editorUrl( response.menu.id );
				} );
			}
		} );
	}

	/* ------------------------------------------------------- Dashboard screen */

	function dashboardScreen() {
		root.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '[data-action="create-menu"]' ) ) {
				createMenu();
				return;
			}

			var copy = event.target.closest( '[data-action="copy-shortcode"]' );

			if ( copy ) {
				copyText( copy.getAttribute( 'data-shortcode' ) );
			}
		} );
	}

	/* ---------------------------------------------------------- Menus screen */

	function menusScreen() {
		var listHost = root.querySelector( '[data-role="menu-list"]' );
		var search = root.querySelector( '[data-role="search"]' );
		var statusFilter = root.querySelector( '[data-role="status-filter"]' );

		function row( menu ) {
			var shortcodeButton = el( 'button', {
				type: 'button',
				class: 'rmb-shortcode',
				title: __( 'Copy shortcode', 'restaurant-menu-builder' ),
				text: menu.shortcode,
				onClick: function () {
					copyText( menu.shortcode );
				}
			} );

			var statusSelect = A.select(
				[
					{ value: 'active', label: __( 'Active', 'restaurant-menu-builder' ) },
					{ value: 'draft', label: __( 'Draft', 'restaurant-menu-builder' ) }
				],
				menu.status
			);

			statusSelect.classList.add( 'rmb-select-small' );
			statusSelect.setAttribute( 'aria-label', sprintf( __( 'Status of %s', 'restaurant-menu-builder' ), menu.name ) );

			statusSelect.addEventListener( 'change', function () {
				api( '/menus/' + menu.id, { method: 'POST', body: { status: statusSelect.value } } )
					.then( function ( response ) {
						notify( response.message );
					} )
					.catch( function ( error ) {
						statusSelect.value = menu.status;
						fail( error );
					} );
			} );

			return el( 'tr', {}, [
				el( 'td', {}, [
					el( 'a', {
						class: 'rmb-link-strong',
						href: editorUrl( menu.id ),
						text: menu.name
					} ),
					el( 'div', { class: 'rmb-row-sub', text: '/' + menu.slug } )
				] ),
				// data-label lets the stacked mobile layout name each cell, since the
				// header row is hidden there.
				el( 'td', { 'data-label': __( 'Shortcode', 'restaurant-menu-builder' ) }, [ shortcodeButton ] ),
				el( 'td', {
					'data-label': __( 'Items', 'restaurant-menu-builder' ),
					text: String( menu.counts.items )
				} ),
				el( 'td', {
					'data-label': __( 'Categories', 'restaurant-menu-builder' ),
					text: String( menu.counts.categories )
				} ),
				el( 'td', { 'data-label': __( 'Status', 'restaurant-menu-builder' ) }, [ statusSelect ] ),
				el( 'td', { class: 'rmb-cell-actions' }, [
					el(
						'a',
						{
							class: 'rmb-icon-button',
							href: editorUrl( menu.id ),
							'aria-label': sprintf( __( 'Edit %s', 'restaurant-menu-builder' ), menu.name ),
							title: __( 'Edit menu', 'restaurant-menu-builder' )
						},
						[ A.uiIcon( 'edit', 16 ) ]
					),
					actionButton( 'copy', __( 'Duplicate menu', 'restaurant-menu-builder' ), function () {
						api( '/menus/' + menu.id + '/duplicate', { method: 'POST' } )
							.then( function ( response ) {
								notify( response.message );
								load();
							} )
							.catch( fail );
					} ),
					actionButton(
						'trash',
						__( 'Delete menu', 'restaurant-menu-builder' ),
						function () {
							A.confirmDialog( {
								title: __( 'Delete this menu?', 'restaurant-menu-builder' ),
								message: sprintf(
									/* translators: %s: menu name. */
									__( '“%s” will be removed permanently.', 'restaurant-menu-builder' ),
									menu.name
								),
								detail: sprintf(
									/* translators: 1: category count, 2: item count. */
									__( '%1$d categories and %2$d items will be deleted with it.', 'restaurant-menu-builder' ),
									menu.counts.categories,
									menu.counts.items
								),
								confirmLabel: __( 'Delete menu', 'restaurant-menu-builder' ),
								danger: true
							} ).then( function ( confirmed ) {
								if ( ! confirmed ) {
									return;
								}

								api( '/menus/' + menu.id, { method: 'DELETE' } )
									.then( function ( response ) {
										notify( response.message );
										load();
									} )
									.catch( fail );
							} );
						},
						true
					)
				] )
			] );
		}

		function load() {
			var query = [];

			if ( search && search.value.trim() ) {
				query.push( 'search=' + encodeURIComponent( search.value.trim() ) );
			}

			if ( statusFilter && statusFilter.value ) {
				query.push( 'status=' + encodeURIComponent( statusFilter.value ) );
			}

			api( '/menus' + ( query.length ? '?' + query.join( '&' ) : '' ) )
				.then( function ( response ) {
					clear( listHost );

					if ( ! response.items.length ) {
						listHost.appendChild(
							emptyState(
								__( 'No menus found', 'restaurant-menu-builder' ),
								__( 'Create a menu to start adding categories and dishes.', 'restaurant-menu-builder' ),
								__( 'Add new menu', 'restaurant-menu-builder' ),
								createMenu,
								'menus'
							)
						);

						return;
					}

					var table = el( 'table', { class: 'rmb-table' }, [
						el( 'thead', {}, [
							el( 'tr', {}, [
								el( 'th', { text: __( 'Menu', 'restaurant-menu-builder' ) } ),
								el( 'th', { text: __( 'Shortcode', 'restaurant-menu-builder' ) } ),
								el( 'th', { text: __( 'Items', 'restaurant-menu-builder' ) } ),
								el( 'th', { text: __( 'Categories', 'restaurant-menu-builder' ) } ),
								el( 'th', { text: __( 'Status', 'restaurant-menu-builder' ) } ),
								el( 'th', { class: 'rmb-cell-actions', text: __( 'Actions', 'restaurant-menu-builder' ) } )
							] )
						] )
					] );

					var body = el( 'tbody', {} );

					response.items.forEach( function ( menu ) {
						body.appendChild( row( menu ) );
					} );

					table.appendChild( body );
					listHost.appendChild( table );
				} )
				.catch( function ( error ) {
					clear( listHost );
					listHost.appendChild( el( 'p', { class: 'rmb-error', text: error.message } ) );
				} );
		}

		root.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '[data-action="create-menu"]' ) ) {
				createMenu();
			}
		} );

		if ( search ) {
			search.addEventListener( 'input', A.debounce( load, 300 ) );
		}

		if ( statusFilter ) {
			statusFilter.addEventListener( 'change', load );
		}

		load();

		if ( root.getAttribute( 'data-open-create' ) === '1' ) {
			createMenu();
		}
	}

	function copyText( text ) {
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard
				.writeText( text )
				.then( function () {
					notify( __( 'Shortcode copied.', 'restaurant-menu-builder' ) );
				} )
				.catch( function () {
					notify( __( 'Copy the shortcode manually: ', 'restaurant-menu-builder' ) + text, 'error' );
				} );

			return;
		}

		notify( __( 'Copy the shortcode manually: ', 'restaurant-menu-builder' ) + text, 'error' );
	}

	/* --------------------------------------------------------- Editor screen */

	function editorScreen() {
		var previewFrame = root.querySelector( '[data-role="preview-frame"]' );
		var previewPane = root.querySelector( '[data-role="preview-pane"]' );
		var preview = Preview( previewFrame );
		var styleHost = root.querySelector( '[data-role="style-editor"]' );
		var settingsHost = root.querySelector( '[data-role="menu-settings"]' );
		var categoryHost = root.querySelector( '[data-role="category-list"]' );

		var menu = null;
		var styleEditor = null;
		var dirty = false;

		bindPreviewSizes( previewPane, previewFrame );

		var refreshPreview = A.debounce( function () {
			if ( ! menu ) {
				return;
			}

			api( '/menus/' + menuId + '/preview', {
				method: 'POST',
				body: { settings: collectSettings() }
			} )
				.then( function ( response ) {
					preview.paint( response );
				} )
				.catch( fail );
		}, 350 );

		function markDirty() {
			dirty = true;
			var save = root.querySelector( '[data-action="save-menu"]' );

			if ( save ) {
				save.classList.add( 'is-dirty' );
			}

			refreshPreview();
		}

		var controls = {};

		function collectSettings() {
			return {
				layout: controls.layout.value,
				show_navigation: controls.show_navigation.input.checked,
				show_images: controls.show_images.input.checked,
				show_descriptions: controls.show_descriptions.input.checked,
				show_prices: controls.show_prices.input.checked,
				use_global_style: controls.use_global_style.input.checked,
				style: styleEditor.values(),
				custom_css: controls.custom_css.value
			};
		}

		function buildSettingsPanel() {
			clear( settingsHost );

			controls.name = A.textInput( menu.name );
			controls.slug = A.textInput( menu.slug );
			controls.status = A.select(
				[
					{ value: 'active', label: __( 'Active', 'restaurant-menu-builder' ) },
					{ value: 'draft', label: __( 'Draft — hidden from visitors', 'restaurant-menu-builder' ) }
				],
				menu.status
			);

			controls.layout = A.select(
				Object.keys( D.layouts ).map( function ( key ) {
					return { value: key, label: D.layouts[ key ] };
				} ),
				menu.settings.layout
			);

			controls.show_navigation = A.switchControl( __( 'Show category navigation', 'restaurant-menu-builder' ), menu.settings.show_navigation );
			controls.show_images = A.switchControl( __( 'Show images', 'restaurant-menu-builder' ), menu.settings.show_images );
			controls.show_descriptions = A.switchControl( __( 'Show descriptions', 'restaurant-menu-builder' ), menu.settings.show_descriptions );
			controls.show_prices = A.switchControl( __( 'Show prices', 'restaurant-menu-builder' ), menu.settings.show_prices );

			controls.custom_css = el( 'textarea', { class: 'rmb-textarea rmb-code', rows: '6', spellcheck: 'false' } );
			controls.custom_css.value = menu.settings.custom_css || '';

			[ controls.name, controls.slug, controls.status, controls.layout, controls.custom_css ].forEach( function ( control ) {
				control.addEventListener( 'change', markDirty );
			} );

			[ 'show_navigation', 'show_images', 'show_descriptions', 'show_prices' ].forEach( function ( key ) {
				controls[ key ].input.addEventListener( 'change', markDirty );
			} );

			settingsHost.appendChild( A.field( __( 'Menu name', 'restaurant-menu-builder' ), controls.name ) );
			settingsHost.appendChild(
				A.field( __( 'Slug', 'restaurant-menu-builder' ), controls.slug, __( 'Used by [restaurant_menu slug="…"].', 'restaurant-menu-builder' ) )
			);
			settingsHost.appendChild( A.field( __( 'Status', 'restaurant-menu-builder' ), controls.status ) );
			settingsHost.appendChild( A.field( __( 'Layout', 'restaurant-menu-builder' ), controls.layout ) );
			settingsHost.appendChild( controls.show_navigation );
			settingsHost.appendChild( controls.show_images );
			settingsHost.appendChild( controls.show_descriptions );
			settingsHost.appendChild( controls.show_prices );
			settingsHost.appendChild(
				A.field(
					__( 'Custom CSS for this menu', 'restaurant-menu-builder' ),
					controls.custom_css,
					__( 'Applies to this menu only.', 'restaurant-menu-builder' )
				)
			);
		}

		function buildStylePanel() {
			clear( styleHost );

			controls.use_global_style = A.switchControl(
				__( 'Use the global style from Restaurant Menu → Style', 'restaurant-menu-builder' ),
				menu.settings.use_global_style
			);

			var fields = el( 'div', { class: 'rmb-style-fields' } );

			function syncLock() {
				var locked = controls.use_global_style.input.checked;

				fields.classList.toggle( 'is-locked', locked );
				fields
					.querySelectorAll( 'input, select, button' )
					.forEach( function ( control ) {
						control.disabled = locked;
					} );
			}

			controls.use_global_style.input.addEventListener( 'change', function () {
				syncLock();
				markDirty();
			} );

			styleHost.appendChild( controls.use_global_style );
			styleHost.appendChild( fields );

			styleEditor = StyleEditor( fields, menu.settings.style, function () {
				syncLock();
				markDirty();
			} );

			syncLock();
		}

		function load() {
			api( '/menus/' + menuId )
				.then( function ( response ) {
					menu = response.menu;
					buildStylePanel();
					buildSettingsPanel();
					refreshPreview();
				} )
				.catch( fail );
		}

		var categoryList = CategoryList( categoryHost, {
			menuId: menuId,
			withItems: true,
			onChange: refreshPreview
		} );

		categoryList.load();
		load();

		root.addEventListener( 'click', function ( event ) {
			var tab = event.target.closest( '.rmb-tab[data-tab]' );

			if ( tab ) {
				var name = tab.getAttribute( 'data-tab' );

				root.querySelectorAll( '.rmb-tab[data-tab]' ).forEach( function ( other ) {
					var active = other === tab;
					other.classList.toggle( 'is-active', active );
					other.setAttribute( 'aria-selected', active ? 'true' : 'false' );
				} );

				root.querySelectorAll( '.rmb-tab-panel' ).forEach( function ( panel ) {
					panel.classList.toggle( 'is-active', panel.getAttribute( 'data-panel' ) === name );
				} );

				return;
			}

			if ( event.target.closest( '[data-action="add-category"]' ) ) {
				categoryList.addCategory();
				return;
			}

			if ( event.target.closest( '[data-action="copy-shortcode"]' ) ) {
				copyText( event.target.closest( '[data-action="copy-shortcode"]' ).getAttribute( 'data-shortcode' ) );
				return;
			}

			if ( event.target.closest( '[data-action="toggle-preview"]' ) ) {
				var button = event.target.closest( '[data-action="toggle-preview"]' );
				var hidden = root.classList.toggle( 'is-preview-hidden' );

				button.setAttribute( 'aria-pressed', hidden ? 'false' : 'true' );
				button.textContent = hidden
					? __( 'Show preview', 'restaurant-menu-builder' )
					: __( 'Hide preview', 'restaurant-menu-builder' );

				return;
			}

			if ( event.target.closest( '[data-action="reset-menu-style"]' ) ) {
				styleEditor = styleEditor.reset( D.defaults.style );
				markDirty();
				return;
			}

			if ( event.target.closest( '[data-action="save-menu"]' ) ) {
				var save = event.target.closest( '[data-action="save-menu"]' );

				save.disabled = true;

				api( '/menus/' + menuId, {
					method: 'POST',
					body: {
						name: controls.name.value,
						slug: controls.slug.value,
						status: controls.status.value,
						settings: collectSettings()
					}
				} )
					.then( function ( response ) {
						menu = response.menu;
						dirty = false;
						save.classList.remove( 'is-dirty' );

						var title = root.querySelector( '[data-role="menu-title"]' );
						var chip = root.querySelector( '[data-role="menu-status"]' );

						if ( title ) {
							title.textContent = menu.name;
						}

						if ( chip ) {
							chip.setAttribute( 'data-status', menu.status );
							chip.textContent = statusLabel( menu.status );
						}

						controls.slug.value = menu.slug;

						notify( response.message );
						refreshPreview();
					} )
					.catch( fail )
					.then( function () {
						save.disabled = false;
					} );
			}
		} );

		window.addEventListener( 'beforeunload', function ( event ) {
			if ( ! dirty ) {
				return;
			}

			event.preventDefault();
			event.returnValue = '';
		} );
	}

	/* ------------------------------------------- Categories and items screens */

	function listScreen( withItems ) {
		var host = root.querySelector( '[data-role="category-list"]' );

		if ( ! host || ! menuId ) {
			return;
		}

		var list = CategoryList( host, { menuId: menuId, withItems: withItems } );

		list.load();

		root.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '[data-action="add-category"]' ) ) {
				list.addCategory();
			}

			if ( event.target.closest( '[data-action="add-item"]' ) ) {
				list.addItem();
			}
		} );

		var search = root.querySelector( '[data-role="item-search"]' );

		if ( search ) {
			search.addEventListener(
				'input',
				A.debounce( function () {
					var term = search.value.trim().toLowerCase();

					host.querySelectorAll( '.rmb-row-item' ).forEach( function ( row ) {
						var name = row.querySelector( '.rmb-row-name' );
						var match = ! term || ( name && name.textContent.toLowerCase().indexOf( term ) !== -1 );
						row.hidden = ! match;
					} );
				}, 200 )
			);
		}
	}

	/* ---------------------------------------------------------- Style screen */

	function styleScreen() {
		var styleHost = root.querySelector( '[data-role="style-editor"]' );
		var previewFrame = root.querySelector( '[data-role="preview-frame"]' );
		var previewPane = root.querySelector( '[data-role="preview-pane"]' );
		var preview = Preview( previewFrame );

		bindPreviewSizes( previewPane, previewFrame );

		var refreshPreview = A.debounce( function () {
			if ( ! menuId ) {
				return;
			}

			api( '/menus/' + menuId + '/preview', {
				method: 'POST',
				body: { settings: { use_global_style: false, style: styleEditor.values() } }
			} )
				.then( function ( response ) {
					preview.paint( response );
				} )
				.catch( fail );
		}, 350 );

		var styleEditor = StyleEditor( styleHost, D.style, refreshPreview );

		refreshPreview();

		root.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '[data-action="save-global-style"]' ) ) {
				api( '/settings', { method: 'POST', body: { style: styleEditor.values() } } )
					.then( function ( response ) {
						notify( response.message );
					} )
					.catch( fail );

				return;
			}

			if ( event.target.closest( '[data-action="reset-global-style"]' ) ) {
				A.confirmDialog( {
					title: __( 'Restore the default style?', 'restaurant-menu-builder' ),
					message: __( 'Colours, sizes and the font go back to the plugin defaults.', 'restaurant-menu-builder' ),
					confirmLabel: __( 'Restore defaults', 'restaurant-menu-builder' )
				} ).then( function ( confirmed ) {
					if ( ! confirmed ) {
						return;
					}

					styleEditor = styleEditor.reset( D.defaults.style );
				} );
			}
		} );
	}

	/* ------------------------------------------------------- Settings screen */

	function settingsScreen() {
		function collect() {
			var payload = {};

			root.querySelectorAll( '[data-setting]' ).forEach( function ( control ) {
				var key = control.getAttribute( 'data-setting' );

				if ( control.type === 'checkbox' ) {
					payload[ key ] = control.checked;
				} else if ( control.type === 'number' ) {
					payload[ key ] = parseInt( control.value, 10 ) || 1;
				} else {
					payload[ key ] = control.value;
				}
			} );

			return payload;
		}

		root.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '[data-action="save-settings"]' ) ) {
				var button = event.target.closest( '[data-action="save-settings"]' );

				button.disabled = true;

				api( '/settings', { method: 'POST', body: { general: collect() } } )
					.then( function ( response ) {
						notify( response.message );
					} )
					.catch( fail )
					.then( function () {
						button.disabled = false;
					} );

				return;
			}

			if ( event.target.closest( '[data-action="flush-cache"]' ) ) {
				api( '/cache/flush', { method: 'POST' } )
					.then( function ( response ) {
						notify( response.message );
					} )
					.catch( fail );

				return;
			}

			if ( event.target.closest( '[data-action="reset-settings"]' ) ) {
				A.confirmDialog( {
					title: __( 'Restore default settings?', 'restaurant-menu-builder' ),
					message: __( 'Currency, display options and custom CSS go back to their defaults.', 'restaurant-menu-builder' ),
					detail: __( 'Your menus, categories and items are not touched.', 'restaurant-menu-builder' ),
					confirmLabel: __( 'Restore defaults', 'restaurant-menu-builder' )
				} ).then( function ( confirmed ) {
					if ( ! confirmed ) {
						return;
					}

					api( '/settings/reset', { method: 'POST' } )
						.then( function ( response ) {
							notify( response.message );
							window.location.reload();
						} )
						.catch( fail );
				} );
			}
		} );
	}

	/* ------------------------------------------------------------------ Boot */

	switch ( screen ) {
		case 'dashboard':
			dashboardScreen();
			break;
		case 'menus':
			menusScreen();
			break;
		case 'editor':
			editorScreen();
			break;
		case 'categories':
			listScreen( false );
			break;
		case 'items':
			listScreen( true );
			break;
		case 'style':
			styleScreen();
			break;
		case 'settings':
			settingsScreen();
			break;
		default:
			break;
	}
}() );
