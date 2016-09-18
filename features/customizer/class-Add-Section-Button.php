<?php
/**
 * Customize API: WP_Customize_New_Menu_Section class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customize Menu Section Class
 *
 * Implements the new-menu-ui toggle button instead of a regular section.
 *
 * @since 4.3.0
 *
 * @see WP_Customize_Section
 */
class WP_Customize_Add_Section_Button extends WP_Customize_Section {

	/**
	 * Control type.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var string
	 */
	public $type = 'add_mp';

	/**
	 * Render the section, and the controls that have been added to it.
	 *
	 * @since 4.3.0
	 * @access protected
	 */
	protected function render() {
		?>
		<li id="accordion-section-<?php echo esc_attr( $this->id ); ?>" class="accordion-section-new-multi-page">
			<ul class="new-multi-page-section-content"></ul>
			<button type="button" class="button-secondary add-new-multi-page add-multi-page-toggle" aria-expanded="false">
				<?php echo esc_html( $this->title ); ?>
			</button>
		</li>
		<?php
	}
}


/**
 * Customize API: WP_Customize_New_Menu_Control class
 *
 * @package WordPress
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customize control class for new menus.
 *
 * @since 4.3.0
 *
 * @see WP_Customize_Control
 */
class WP_Customize_Add_Section_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var string
	 */
	public $type = 'add_mp';

	/**
	 * Render the control's content.
	 *
	 * @since 4.3.0
	 * @access public
	 */
	public function render_content() {
		?>
		<button type="button" class="button button-primary" id="create-new-multi-page-submit"><?php _e( 'Create Multi-Page' ); ?></button>
		<span class="spinner"></span>
		<?php
	}
}
