<?php

class Customify_Multi_Pages {

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	protected function __construct() {
		add_action( 'customify_create_custom_control', array( $this, 'create_custom_control' ), 10, 1 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'available_items_template' ) );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_templates' ) );

		add_action( 'wp_ajax_load-available-customify-mp-items-customizer', array( $this, 'ajax_load_available_items' ) );
	}

	public function enqueue_scripts() {
		$dir = plugin_dir_url( __FILE__ );
		$dir = rtrim( $dir, 'features/' );

		wp_enqueue_style( 'customify_mp', $dir . '/css/mp-customizer.css', array() );
		wp_enqueue_script( 'customify_mp', $dir . '/js/mp-customizer.js', array(), false, true );

		// prepare the script data

		$mp = get_option( 'customify_mp' );

		$data = array(
			'nonce' => wp_create_nonce( 'customize-multi-page' ),
			'data' => $mp,
			'customizingMultiPages'  => sprintf( __( 'Customizing &#9656; %s' ), 'Multi-Page' ),
		);

		wp_localize_script( 'customify_mp', 'customify_mp', $data );
	}

	/**
	 * Return an instance of this class.
	 * @since     1.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	function create_custom_control( $wp_customize ) {

		include_once 'customizer/class-Add-Section-Button.php';

		$wp_customize->add_panel( 'customify_mp', array(
			'priority'   => 12,
			'capability' => 'edit_theme_options',
			'title'      => __( 'Multi-Pages', 'customify_txtd' ),
		) );


		// Add the add-new-multi_page section and controls.
		$wp_customize->add_section( new WP_Customize_Add_Section_Button( $wp_customize, 'customify_add_mp', array(
			'title'    => __( 'Add Multi Page' ),
			'panel'    => 'customify_mp',
			'priority' => 999,
		) ) );


		$wp_customize->add_control( 'customify_new_mp', array(
			'label'       => '',
			'section'     => 'customify_add_mp',
			'type'        => 'text',
			'settings'    => array(),
			'input_attrs' => array(
				'class'       => 'multi_page-name-field',
				'placeholder' => __( 'New Multi Page name' ),
			),
		) );
	}

	public function print_templates() {?>
		<script type="text/html" id="tmpl-available-menu-item">
			<li id="customify-multi-page-item-tpl-{{ data.id }}" class="customify-multi-page-item-tpl" data-page-item-id="{{ data.id }}">
				<div class="menu-item-bar">
					<div class="menu-item-handle">
						<span class="item-type" aria-hidden="true">{{ data.type_label }}</span>
						<span class="item-title" aria-hidden="true">
							<span class="menu-item-title<# if ( ! data.title ) { #> no-title<# } #>">{{ data.title || wp.customize.Menus.data.l10n.untitled }}</span>
						</span>
						<button type="button" class="button-link item-add">
							<span class="screen-reader-text"><?php
								/* translators: 1: Title of a menu item, 2: Type of a menu item */
								printf( __( 'Add to menu: %1$s (%2$s)' ), '{{ data.title || wp.customize.Menus.data.l10n.untitled }}', '{{ data.type_label }}' );
								?></span>
						</button>
					</div>
				</div>
			</li>
		</script>
	<?php }

	function available_items_template() { ?>
		<div id="available-multi_page-items" class="accordion-container">
			<div id="available-multi_page-items-search" class="accordion-section cannot-expand">
				<div class="accordion-section-title">
					<label class="screen-reader-text"
					       for="multi_page-items-search"><?php _e( 'Search multi_page Items' ); ?></label>
					<input type="text" id="multi_page-items-search"
					       placeholder="<?php esc_attr_e( 'Search multi_page items&hellip;' ) ?>"
					       aria-describedby="multi_page-items-search-desc"/>
					<p class="screen-reader-text"
					   id="multi_page-items-search-desc"><?php _e( 'The search results will be updated as you type.' ); ?></p>
					<span class="spinner"></span>
				</div>
				<button type="button" class="clear-results"><span
						class="screen-reader-text"><?php _e( 'Clear Results' ); ?></span></button>
				<ul class="accordion-section-content" data-type="search"></ul>
			</div>
			<div id="available-menu-items-post_type-page" class="accordion-section">
				<h3 class="accordion-section-title" role="presentation">
					<strong>Pages List</strong>
					<span class="spinner"></span>
					<span class="no-items"><?php _e( 'No items' ); ?></span>
					<button type="button" class="button-link" aria-expanded="false">
					</button>
				</h3>
				<ul class="accordion-section-content" data-type="page" data-object="page"></ul>
			</div>
		</div><!-- #available-multi_page-items -->
		<?php
	}

	/**
	 * Ajax handler for loading available menu items.
	 *
	 * @since 4.3.0
	 * @access public
	 */
	public function ajax_load_available_items() {
		check_ajax_referer( 'customize-multi-page', 'customize-multi-page-nonce' );

//		if ( ! current_user_can( 'edit_theme_options' ) ) {
//			wp_die( -1 );
//		}
//
//		if ( empty( $_POST['type'] ) || empty( $_POST['object'] ) ) {
//			wp_send_json_error( 'nav_menus_missing_type_or_object_parameter' );
//		}

		$page = empty( $_POST['page'] ) ? 0 : absint( $_POST['page'] );
		$items = $this->load_available_items_query( 'post_type', 'page', $page );

		if ( is_wp_error( $items ) ) {
			wp_send_json_error( $items->get_error_code() );
		} else {
			wp_send_json_success( array( 'items' => $items ) );
		}
	}
	/**
	 * Performs the post_type and taxonomy queries for loading available menu items.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @param string $type   Optional. Accepts any custom object type and has built-in support for
	 *                         'post_type' and 'taxonomy'. Default is 'post_type'.
	 * @param string $object Optional. Accepts any registered taxonomy or post type name. Default is 'page'.
	 * @param int    $page   Optional. The page number used to generate the query offset. Default is '0'.
	 * @return WP_Error|array Returns either a WP_Error object or an array of menu items.
	 */
	public function load_available_items_query( $type = 'post_type', $object = 'page', $page = 0 ) {
		$items = array();

		if ( 'post_type' === $type ) {
			$post_type = get_post_type_object( $object );
			if ( ! $post_type ) {
				return new WP_Error( 'nav_menus_invalid_post_type' );
			}

			if ( 0 === $page && 'page' === $object ) {
				// Add "Home" link. Treat as a page, but switch to custom on add.
				$items[] = array(
					'id'         => 'home',
					'title'      => _x( 'Home', 'nav menu home label' ),
					'type'       => 'custom',
					'type_label' => __( 'Custom Link' ),
					'object'     => '',
					'url'        => home_url(),
				);
			} elseif ( 'post' !== $object && 0 === $page && $post_type->has_archive ) {
				// Add a post type archive link.
				$items[] = array(
					'id'         => $object . '-archive',
					'title'      => $post_type->labels->archives,
					'type'       => 'post_type_archive',
					'type_label' => __( 'Post Type Archive' ),
					'object'     => $object,
					'url'        => get_post_type_archive_link( $object ),
				);
			}

			$posts = get_posts( array(
				'numberposts' => 10,
				'offset'      => 10 * $page,
				'orderby'     => 'date',
				'order'       => 'DESC',
				'post_type'   => $object,
			) );
			foreach ( $posts as $post ) {
				$post_title = $post->post_title;
				if ( '' === $post_title ) {
					/* translators: %d: ID of a post */
					$post_title = sprintf( __( '#%d (no title)' ), $post->ID );
				}
				$items[] = array(
					'id'         => "post-{$post->ID}",
					'title'      => html_entity_decode( $post_title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
					'type'       => 'post_type',
					'type_label' => get_post_type_object( $post->post_type )->labels->singular_name,
					'object'     => $post->post_type,
					'object_id'  => intval( $post->ID ),
					'url'        => get_permalink( intval( $post->ID ) ),
				);
			}
		} elseif ( 'taxonomy' === $type ) {
			$terms = get_terms( $object, array(
				'child_of'     => 0,
				'exclude'      => '',
				'hide_empty'   => false,
				'hierarchical' => 1,
				'include'      => '',
				'number'       => 10,
				'offset'       => 10 * $page,
				'order'        => 'DESC',
				'orderby'      => 'count',
				'pad_counts'   => false,
			) );
			if ( is_wp_error( $terms ) ) {
				return $terms;
			}

			foreach ( $terms as $term ) {
				$items[] = array(
					'id'         => "term-{$term->term_id}",
					'title'      => html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
					'type'       => 'taxonomy',
					'type_label' => get_taxonomy( $term->taxonomy )->labels->singular_name,
					'object'     => $term->taxonomy,
					'object_id'  => intval( $term->term_id ),
					'url'        => get_term_link( intval( $term->term_id ), $term->taxonomy ),
				);
			}
		}

		/**
		 * Filters the available menu items.
		 *
		 * @since 4.3.0
		 *
		 * @param array  $items  The array of menu items.
		 * @param string $type   The object type.
		 * @param string $object The object name.
		 * @param int    $page   The current page number.
		 */
		$items = apply_filters( 'customize_mult-page_available_items', $items, $type, $object, $page );

		return $items;
	}

}