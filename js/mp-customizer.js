( function( api, wp, $ ) {
	'use strict';

	// stands for Customify Multi Pages
	api.CMP = api.CMP || {};

	api.CMP.addMPPanel = {};

	api.CMP.data = {
		settingTransport: 'refresh'
	};
	
	function add_root_control( id ) {
		var name = id + '_root_control';

		var control = new api.Control( name, {
			params: {
				content: '<li id="customize-control-customify_new_mp" class="customize-control customize-control-text"><label><input type="text" class="multi_page-name-field" placeholder="Root Page ID"  value=""  /></label><button type="button" class="button-secondary add-root-page add-multi-page-toggle" aria-expanded="false">Add root</button></li>',
				priority: 10,
				section: id,
				type: "text"
			},
			previewer: api.previewer
		} );

		var new_ctrl = api.control.add( id, control );

		new_ctrl.activate();

		return new_ctrl;
	}

	function add_childs_control( id ) {
		var name = id + '_childs_control';

		var control = new api.Control( name, {
			params: {
				content: '<li id="customize-control-customify_new_mp" class="customize-control customize-control-text"><label><input type="text" class="multi_page-name-field" placeholder="Set kids ids"  value=""  /></label><button type="button" class="button-secondary add-kid-page add-multi-page-toggle" aria-expanded="false">Add kids</button></li>',
				priority: 10,
				section: id,
				type: "text"
			},
			previewer: api.previewer
		} );

		var new_ctrl = api.control.add( id, control );
		new_ctrl.activate();

		return new_ctrl;
	}


	function add_new_section( id ) {
		var data = {
			id: id,
			panel: "customify_mp",
			title: id,
			priority: 1
		};

		var section = new api.Section( id, {
			params: data
		} );
		
		var news = api.section.add( id, section );
		news.activate();


		console.log( news );

		add_childs_control( id );
		add_root_control( id );

		news.focus();
	}
	
	/**
	 * Init Customizer for mp.
	 */
	api.bind( 'ready', function() {
		api.bind( 'saved', function( data ) {
			if ( data.nav_multi_page_updates || data.nav_multi_page_item_updates ) {
				//api.CMP.applySavedData( data );
			}
		} );

		$('.add-new-multi-page').on('click', function ( ev ) {

			var input = $(this).siblings('.new-multi-page-section-content').find('.multi_page-name-field');

			var name = input.val();
			if ( typeof name !== "undefined" ) {
				add_new_section( name );

				$(input).val('');
			}
		});

		$(document).on('click', '.add-root-page, .add-kid-page',function ( ev ) {

			var input = $(this).siblings('label').find('input');
console.log('click?');
			api.CMP.addMPPanel.open( input );

		});

	} );

	$('body').addClass('adding-multi_page-items');
	api.CMP.addMPPanel.open = function ( input ) {
		api.CMP.addMPPanel.loadPages();
		// add show class to body
		// set right
	};

	api.CMP.addMPPanel.loadPages = function() {

		console.log('load');
		var self = this, params, request, itemTemplate, availableMenuItemContainer;
		itemTemplate = wp.template( 'available-menu-item' );
		var object = 'page';
		var type = 'post_type';

		availableMenuItemContainer = $( '#available-multi_page-items' );
		availableMenuItemContainer.find( '.accordion-section-title' ).addClass( 'loading' );
		self.loading = true;
		params = {
			'customize-multi-page-nonce': customify_mp.nonce
			// 'page': self.pages[ type + ':' + object ]
		};

		request = wp.ajax.post( 'load-available-customify-mp-items-customizer', params );

		request.done(function( data ) {

			var items, typeInner;
			items = data.items;
			// if ( 0 === items.length ) {
			// 	if ( 0 === self.pages[ type + ':' + object ] ) {
			// 		availableMenuItemContainer
			// 			.addClass( 'cannot-expand' )
			// 			.removeClass( 'loading' )
			// 			.find( '.accordion-section-title > button' )
			// 			.prop( 'tabIndex', -1 );
			// 	}
			// 	self.pages[ type + ':' + object ] = -1;
			// 	return;
			// }
			console.log(items);
			// return;
			// items = new api.Menus.AvailableItemCollection( items ); // @todo Why is this collection created and then thrown away?
			// self.collection.add( items.models );
			typeInner = availableMenuItemContainer.find( '.accordion-section-content' );
			for (var key in items) {
				var menuItem = items[key];
				typeInner.append( itemTemplate( menuItem ) );
			}

			availableMenuItemContainer.find('.no-items').hide();

			availableMenuItemContainer.find('.accordion-section-title').click();

		});
		request.fail(function( data ) {
			if ( typeof console !== 'undefined' && console.error ) {
				console.error( data );
			}
		});
		request.always(function() {
			availableMenuItemContainer.find( '.accordion-section-title' ).removeClass( 'loading' );
			self.loading = false;
		});
	}

})( wp.customize, wp, jQuery );