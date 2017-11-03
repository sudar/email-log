<?php namespace EmailLog\Core\UI\Page;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Settings Page.
 * This page is displayed only if any add-on has a setting enabled.
 *
 * @since 2.0.0
 */
class SettingsPage extends BasePage {

	/**
	 * Page slug.
	 */
	const PAGE_SLUG = 'email-log-settings';

	/**
	 * Specify additional hooks.
	 *
	 * @inheritdoc
	 */
	public function load() {
		parent::load();

		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings and add setting sections and fields.
	 */
	public function register_settings() {
		$sections = $this->get_setting_sections();

		foreach ( $sections as $section ) {
			register_setting(
				self::PAGE_SLUG,
				$section->option_name,
				array( 'sanitize_callback' => $section->sanitize_callback )
			);

			add_settings_section(
				$section->id,
				$section->title,
				$section->callback,
				self::PAGE_SLUG
			);

			foreach ( $section->fields as $field ) {
				add_settings_field(
					$section->id . '[' . $field->id . ']',
					$field->title,
					$field->callback,
					self::PAGE_SLUG,
					$section->id,
					$field->args
				);
			}
		}
	}

	/**
	 * Get a list of setting sections defined.
	 * An add-on can define a setting section.
	 *
	 * @return \EmailLog\Core\UI\Setting\SettingSection[] List of defined setting sections.
	 */
	protected function get_setting_sections() {
		/**
		 * Specify the list of setting sections in the settings page.
		 * An add-on can add its own setting section by adding an instance of
		 * SectionSection to the array.
		 *
		 * @since 2.0.0
		 *
		 * @param \EmailLog\Core\UI\Setting\SettingSection[] List of SettingSections.
		 */
		return apply_filters( 'el_setting_sections', array() );
	}

	/**
	 * Register page.
	 */
	public function register_page() {

		$sections = $this->get_setting_sections();

		if ( empty( $sections ) ) {
			return;
		}

		$this->page = add_submenu_page(
			LogListPage::PAGE_SLUG,
			__( 'Settings', 'email-log' ),
			__( 'Settings', 'email-log' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);

		add_action( "load-{$this->page}", array( $this, 'render_help_tab' ) );
	}

	/**
	 * Render the page.
	 * //TODO: Convert these sections into tabs.
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Email Log Settings', 'email-log' ); ?></h1>

			<form method="post" action="options.php">
				<?php
				settings_errors();
				settings_fields( self::PAGE_SLUG );
				do_settings_sections( self::PAGE_SLUG );

				submit_button( __( 'Save', 'email-log' ) );
				?>
			</form>

		</div>
		<?php

		$this->render_page_footer();
	}
}
