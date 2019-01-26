<?php

/**
 * Class Compat_Checker
 */
class Compat_Checker {

	/**
	 * Plugin settings.
	 *
	 * @var $settings
	 */
	protected $settings;

	/**
	 * Plugin name.
	 *
	 * @var $plugin_name
	 */
	protected $plugin_name;

	/**
	 * Plugin slug.
	 *
	 * @var $plugin_slug
	 */
	protected $plugin_slug;

	/**
	 * PHP version.
	 *
	 * @var $min_php_version
	 */
	protected $min_php_version;

	/**
	 * WP version.
	 *
	 * @var $wp_version
	 */
	protected $min_wp_version;

	/**
	 * Genesis version.
	 *
	 * @var $min_genesis_version
	 */
	protected $min_genesis_version;

	/**
	 * Genesis requirement.
	 *
	 * @var $require_genesis
	 */
	protected $require_genesis;

	/**
	 * Child theme requirement.
	 *
	 * @var $require_child_theme
	 */
	protected $require_child_theme;

	/**
	 * Theme information.
	 *
	 * @var $theme
	 */
	protected $active_theme;

	/**
	 * Parent theme.
	 *
	 * @var $parent_theme
	 */
	protected $parent_theme;

	/**
	 * Compat_Checker constructor.
	 *
	 * @param bool $config
	 *
	 * @since 1.0.1
	 */
	public function __construct( $config = false ) {
		$this->settings            = $this->get_settings( $config );
		$this->active_theme        = wp_get_theme();
		$this->parent_theme        = $this->active_theme->parent();
		$this->plugin_name         = $this->settings['plugin_name'];
		$this->plugin_slug         = $this->settings['plugin_slug'];
		$this->min_php_version     = $this->settings['min_php_version'];
		$this->min_wp_version      = $this->settings['min_wp_version'];
		$this->min_genesis_version = $this->settings['min_genesis_version'];
		$this->require_genesis     = $this->is_truthy( $this->settings['require_genesis'] );
		$this->require_child_theme = $this->is_truthy( $this->settings['require_child_theme'] );
	}

	/**
	 * Assign properties and run hook.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run() {
		if ( ! $this->is_compatible() ) {
			add_action( 'plugins_loaded', array( $this, 'deactivate' ) );
		}
	}

	/**
	 * Helper to check if plugin meets requirements.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_compatible() {
		return $this->get_message() === false ? true : false;
	}

	/**
	 * Runs deactivation hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function deactivate() {
		add_action( 'admin_init', array( $this, 'deactivate_plugin' ) );
		add_action( 'admin_notices', array( $this, 'display_notice' ) );
	}

	/**
	 * Deactivates current plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function deactivate_plugin() {
		deactivate_plugins( $this->plugin_slug );
	}

	/**
	 * Displays deactivation notice.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function display_notice() {
		echo '<div class="notice notice-warning"><p>' . wp_kses_post( $this->get_message() ) . '</p></div>';
	}

	/**
	 * Returns the correct deactivation notice.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function get_message() {
		if ( version_compare( PHP_VERSION, $this->min_php_version, '<' ) ) {
			return sprintf(
				__( 'The <strong>%1$s</strong> plugin requires at least PHP version %2$s to run. This site uses %3$s, so the plugin has been <strong>deactivated</strong>.', 'compat-checker' ),
				$this->plugin_name,
				$this->min_php_version,
				PHP_VERSION
			);

		} elseif ( version_compare( $GLOBALS['wp_version'], $this->min_wp_version, '<' ) ) {
			return sprintf(
				__( 'The <strong>%1$s</strong> plugin requires at least WordPress version %2$s. You are running version %3$s, so the plugin has been <strong>deactivated</strong>.', 'compat-checker' ),
				$this->plugin_name,
				$this->min_wp_version,
				$GLOBALS['wp_version']
			);

		} elseif ( $this->require_genesis && 'Genesis' !== $this->parent_theme->Name ) {
			return sprintf(
				__( 'Sorry, you cannot run the <strong>%1$s</strong> plugin without an active Genesis child theme. Please install and activate a Genesis child theme and try again.', 'compat-checker' ),
				$this->plugin_name
			);

		} elseif ( $this->require_genesis && version_compare( $this->parent_theme->Version, $this->min_genesis_version, '<' ) ) {
			return sprintf(
				__( 'The <strong>%1$s</strong> plugin requires at least Genesis version %2$s. You are running version %3$s so the plugin has been <strong>deactivated</strong>.', 'compat-checker' ),
				$this->plugin_name,
				$this->min_genesis_version,
				$this->parent_theme->Version
			);

		} elseif ( $this->require_child_theme && 'Genesis' !== $this->parent_theme->Name && 'Genesis' !== $this->active_theme->Name ) {
			return sprintf(
				__( 'Sorry, you cannot run the <strong>%1$s</strong> plugin without an active Genesis child theme. Please install and activate a Genesis child theme and try again.', 'compat-checker' ),
				$this->plugin_name
			);
		}

		return false;
	}

	/**
	 * Returns the plugin settings.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $settings PHP array or path to composer.json.
	 *
	 * @return array
	 */
	protected function get_settings( $settings ) {
		if ( is_array( $settings ) ) {
			return array_merge( $this->get_defaults(), $settings );

		} elseif ( file_exists( $settings ) ) {
			return $this->json_decode( $settings );

		} else {
			return $this->get_defaults();
		}
	}

	/**
	 * Returns array of default settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_defaults() {
		return apply_filters( 'compat_checker_defaults', array(
			'plugin_slug'         => 'compat-checker/example-plugin.php',
			'plugin_name'         => 'Genesis Compat',
			'min_php_version'     => '5.3.0',
			'min_wp_version'      => '5.0.0',
			'min_genesis_version' => '2.8.0',
			'require_genesis'     => true,
			'require_child_theme' => true,
		) );
	}

	/**
	 * Retrieves settings from composer.json.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Path to composer.json.
	 *
	 * @return array
	 */
	protected function json_decode( $path ) {
		$json             = file_get_contents( $path );
		$json             = json_decode( $json, true );
		$settings         = $json['extra']['compat-checker'];
		$min_wp_php       = '5.2.4';
		$min_composer_php = $json['require']['php'] ? $json['require']['php'] : $min_wp_php;
		$min_plugin_php   = $settings['min_php_version'] ? $settings['min_php_version'] : $min_composer_php;

		$settings['min_php_version'] = str_replace( array( '<', '=', '>'), '', $min_plugin_php );

		return $settings;
	}

	/**
	 * Check for truthy value of variable.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Value to check.
	 *
	 * @return bool
	 */
	protected function is_truthy( $value ) {
		$values = array(
			1,
			true,
			'1',
			'on',
			'yes',
			'true',
		);

		return in_array( $value, $values, true ) ? true : false;
	}
}
