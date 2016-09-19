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
				content: '<li id="customize-control-customify_new_mp" class="customize-control customize-control-text"><label><input type="hidden" class="multi_page-name-field" placeholder="Root Page ID"  value=""  /></label><button type="button" class="button-secondary add-page-button add-multi-page-toggle" aria-expanded="false">Add Page</button></li>',
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
			priority: 1,
			customizeAction: customify_mp.customizingMultiPages
		};

		var section = new api.Section( id, {
			params: data
		} );
		
		var news = api.section.add( id, section );
		news.activate();

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

		$(document).on('click', '.add-page-button',function ( ev ) {

			var input = $(this).siblings('label').find('input');

			api.CMP.addMPPanel.open( input );

			$('body').addClass('adding-multi_page-items');
		});

	} );

	api.CMP.addMPPanel.open = function ( input ) {
		api.CMP.addMPPanel.loadPages();
		// add show class to body
		// set right
	};

	api.CMP.addMPPanel.loadPages = function() {

		var self = this, params, request, itemTemplate, availableMenuItemContainer,
			object = 'page',
			type = 'post_type';

		itemTemplate = wp.template( 'available-menu-item' );

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