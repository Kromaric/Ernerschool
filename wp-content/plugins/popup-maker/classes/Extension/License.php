<?php
/**
 * Extension License Handler
 *
 * @package   PopupMaker
 * @copyright Copyright (c) 2024, Code Atlantic LLC
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * License handler for Popup Maker
 *
 * This class should simplify the process of adding license information to new Popup Maker extensions.
 *
 * Note for wordpress.org admins. This is not called in the free hosted version and is simply used for hooking in addons to one update system rather than including it in each plugin.
 *
 * @version 1.1
 */
class PUM_Extension_License {

	/**
	 * Plugin file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * License key.
	 *
	 * @var string
	 */
	private $license;

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	private $item_name;

	/**
	 * Plugin EDD item ID.
	 *
	 * @var int
	 */
	private $item_id;

	/**
	 * Plugin shortname.
	 *
	 * @var string
	 */
	private $item_shortname;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Plugin author.
	 *
	 * @var string
	 */
	private $author;

	/**
	 * API URL.
	 *
	 * @var string
	 */
	private $api_url = 'https://wppopupmaker.com/edd-sl-api/';

	/**
	 * Class constructor
	 *
	 * @param string $_file
	 * @param string $_item_name
	 * @param string $_version
	 * @param string $_author
	 * @param string $_optname
	 * @param string $_api_url
	 * @param int    $_item_id
	 */
	public function __construct( $_file, $_item_name, $_version, $_author, $_optname = null, $_api_url = null, $_item_id = null ) {
		$this->file      = $_file;
		$this->item_name = $_item_name;

		if ( is_numeric( $_item_id ) ) {
			$this->item_id = absint( $_item_id );
		}

		$this->item_shortname = 'popmake_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
		$this->version        = $_version;
		$this->license        = trim( PUM_Utils_Options::get( $this->item_shortname . '_license_key', '' ) );
		$this->author         = $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

		/**
		 * Allows for backwards compatibility with old license options,
		 * i.e. if the plugins had license key fields previously, the license
		 * handler will automatically pick these up and use those in lieu of the
		 * user having to reactive their license.
		 */
		if ( ! empty( $_optname ) ) {
			$opt = PUM_Utils_Options::get( $_optname );

			if ( isset( $opt ) && empty( $this->license ) ) {
				$this->license = trim( $opt );
			}
		}

		// Setup hooks
		$this->includes();
		$this->hooks();
	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Register settings
		add_filter( 'pum_settings_fields', [ $this, 'settings' ], 1 );

		// Activate license key on settings save
		add_action( 'admin_init', [ $this, 'activate_license' ] );

		// Deactivate license key
		add_action( 'admin_init', [ $this, 'deactivate_license' ] );

		// Check that license is valid once per week
		add_action( 'popmake_weekly_scheduled_events', [ $this, 'weekly_license_check' ] );

		// For testing license notices, uncomment this line to force checks on every page load
		// add_action( 'admin_init', array( $this, 'weekly_license_check' ) );

		// Updater
		add_action( 'admin_init', [ $this, 'auto_updater' ], 0 );

		// Display notices to admins
		// add_action( 'admin_notices', array( $this, 'notices' ) );

		// Display notices to admins
		add_filter( 'pum_alert_list', [ $this, 'alerts' ] );

		add_action( 'in_plugin_update_message-' . plugin_basename( $this->file ), [ $this, 'plugin_row_license_missing' ], 10, 2 );

		// Register plugins for beta support
		add_filter( 'pum_beta_enabled_extensions', [ $this, 'register_beta_support' ] );
	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @return  void
	 */
	public function auto_updater() {
		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}

		$args = [
			'version' => $this->version,
			'license' => $this->license,
			'item_id' => $this->item_id,
			'author'  => $this->author,
			'beta'    => PUM_Admin_Tools::extension_has_beta_support( $this->item_shortname ),
		];

		if ( ! empty( $this->item_id ) ) {
			$args['item_id'] = $this->item_id;
		} else {
			$args['item_name'] = $this->item_name;
		}

		// Setup the updater
		$popmake_updater = new PUM_Extension_Updater( $this->api_url, $this->file, $args );
	}


	/**
	 * Add license field to settings
	 *
	 * @access  public
	 *
	 * @param array $tabs
	 *
	 * @return  array
	 */
	public function settings( $tabs = [] ) {
		static $license_help_text = false;

		if ( ! $license_help_text && ! isset( $tabs['licenses']['main']['license_help_text'] ) ) {
			$license_help_text = true;

			$tabs['licenses']['main']['license_help_text'] = [
				'type'     => 'html',
				'content'  => '<p><strong>' . sprintf(
					/* translators: 1. opening link text, 2. closing link text */
					esc_html__( 'Enter your extension license keys here to receive updates for purchased extensions. If your license key has expired, please %1$srenew your license%2$s.', 'popup-maker' ),
					'<a href="https://wppopupmaker.com/docs/policies/license-renewal/?utm_medium=license-help-text&utm_campaign=Licensing&utm_source=plugin-settings-page-licenses-tab" target="_blank">',
					'</a>'
				) . '</strong></p>',
				'priority' => 0,
			];
		}

		$tabs['licenses']['main'][ $this->item_shortname . '_license_key' ] = [
			'type'    => 'license_key',
			'label'   => esc_attr( $this->item_name ),
			'options' => [
				'is_valid_license_option' => $this->item_shortname . '_license_active',
				'activation_callback'     => [ $this, 'activate_license' ],
			],
		];

		return $tabs;
	}

	/**
	 * Activate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function activate_license() {
		if ( ! isset( $_POST['pum_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['pum_settings_nonce'] ) ), 'pum_settings_nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['pum_settings'] ) ) {
			return;
		}

		if ( ! isset( $_POST['pum_settings'][ $this->item_shortname . '_license_key' ] ) ) {
			return;
		}

		// Don't activate a key when deactivating a different key
		if ( ! empty( $_POST['pum_license_deactivate'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$details = get_option( $this->item_shortname . '_license_active' );

		if ( is_object( $details ) && 'valid' === $details->license ) {
			return;
		}

		$license = sanitize_text_field( wp_unslash( $_POST['pum_settings'][ $this->item_shortname . '_license_key' ] ) );

		if ( empty( $license ) && empty( $_POST['pum_license_activate'][ $this->item_shortname . '_license_key' ] ) ) {
			return;
		}

		// Data to send to the API
		$api_params = [
			'edd_action'  => 'activate_license',
			'license'     => $license,
			'item_id'     => $this->item_id,
			'item_name'   => rawurlencode( $this->item_name ),
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		];

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			[
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			]
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			return;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );

		// Decode license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( $this->item_shortname . '_license_active', $license_data );
	}


	/**
	 * Deactivate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function deactivate_license() {
		if ( ! isset( $_POST['pum_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['pum_settings_nonce'] ) ), 'pum_settings_nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['pum_settings'] ) ) {
			return;
		}

		if ( ! isset( $_POST['pum_settings'][ $this->item_shortname . '_license_key' ] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Run on deactivate button press
		if ( isset( $_POST['pum_license_deactivate'][ $this->item_shortname . '_license_key' ] ) ) {

			// Data to send to the API
			$api_params = [
				'edd_action'  => 'deactivate_license',
				'license'     => $this->license,
				'item_id'     => $this->item_id,
				'item_name'   => rawurlencode( $this->item_name ),
				'url'         => home_url(),
				'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
			];

			// Call the API
			$response = wp_remote_post(
				$this->api_url,
				[
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				]
			);

			// Make sure there are no errors
			if ( is_wp_error( $response ) ) {
				return;
			}

			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			delete_option( $this->item_shortname . '_license_active' );
		}
	}


	/**
	 * Check if license key is valid once per week
	 *
	 * @access  public
	 * @since   2.5
	 * @return  void
	 */
	public function weekly_license_check() {

		// Simply checking existence.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['popmake_settings'] ) ) {
			return; // Don't fire when saving settings
		}

		if ( empty( $this->license ) ) {
			return;
		}

		// data to send in our API request
		$api_params = [
			'edd_action'  => 'check_license',
			'license'     => $this->license,
			'item_id'     => $this->item_id,
			'item_name'   => rawurlencode( $this->item_name ),
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		];

		// Call the API
		$response = wp_remote_get(
			$this->api_url,
			[
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			]
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( $this->item_shortname . '_license_active', $license_data );
	}

	/**
	 * Adds an alert to the Popup Maker notification area when the license is invalid, expired, or empty
	 *
	 * @param array $alerts The existing alerts from the pum_alert_list filter
	 * @return array Our modified array of alerts
	 */
	public function alerts( $alerts = [] ) {

		static $showed_invalid_message;

		// If user can't manage it, or we already showed this alert abort.
		if ( ! current_user_can( 'manage_options' ) || $showed_invalid_message ) {
			return $alerts;
		}

		// If this alert is already in the list of alerts, abort.
		foreach ( $alerts as $alert ) {
			if ( 'license_not_valid' === $alert['code'] ) {
				return $alerts;
			}
		}

		// If this license key is not empty, check if it's valid.
		if ( ! empty( $this->license ) ) {
			$license = get_option( $this->item_shortname . '_license_active' );

			if ( ! is_object( $license ) || 'valid' === $license->license ) {
				return $alerts;
			}
		}

		$showed_invalid_message = true;

		if ( empty( $this->license ) ) {
			$alerts[] = [
				'code'        => 'license_not_valid',
				'message'     => sprintf(
					/* translators: 1. opening link text, 2. closing link text */
					__( 'One or more of your extensions are missing license keys. You will not be able to receive updates until the extension has a valid license key entered. Please go to the %1$sLicenses page%2$s to add your license keys.', 'popup-maker' ),
					'<a href="' . admin_url( 'edit.php?post_type=popup&page=pum-settings&tab=licenses' ) . '">',
					'</a>'
				),
				'type'        => 'error',
				'dismissible' => '4 weeks',
				'priority'    => 0,
			];
		} else {
			$alerts[] = [
				'code'        => 'license_not_valid',
				'message'     => sprintf(
					/* translators: 1. opening link text, 2. closing link text */
					__( 'You have invalid or expired license keys for Popup Maker. Please go to the %1$sLicenses page%2$s to correct this issue.', 'popup-maker' ),
					'<a href="' . admin_url( 'edit.php?post_type=popup&page=pum-settings&tab=licenses' ) . '">',
					'</a>'
				),
				'type'        => 'error',
				'dismissible' => '4 weeks',
				'priority'    => 0,
			];
		}

		return $alerts;
	}

	/**
	 * Admin notices for errors
	 *
	 * @access  public
	 * @return  void
	 */
	public function notices() {

		static $showed_invalid_message;

		if ( empty( $this->license ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) || $showed_invalid_message ) {
			return;
		}

		$messages = [];

		$license = get_option( $this->item_shortname . '_license_active' );

		if ( is_object( $license ) && 'valid' !== $license->license ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( empty( $_GET['tab'] ) || 'licenses' !== $_GET['tab'] ) {
				$messages[] = sprintf(
					/* translators: 1. opening link text, 2. closing link text */
					esc_html__( 'You have invalid or expired license keys for Popup Maker. Please go to the %1$sLicenses page%2$s to correct this issue.', 'popup-maker' ),
					'<a href="' . admin_url( 'edit.php?post_type=popup&page=pum-settings&tab=licenses' ) . '">',
					'</a>'
				);

				$showed_invalid_message = true;
			}
		}

		if ( ! empty( $messages ) ) {
			foreach ( $messages as $message ) {
				echo '<div class="error">';
				echo '<p>' . wp_kses( $message, wp_kses_allowed_html( 'data' ) ) . '</p>';
				echo '</div>';
			}
		}
	}

	/**
	 * Displays message inline on plugin row that the license key is missing
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {

		static $showed_imissing_key_message;

		$license = get_option( $this->item_shortname . '_license_active' );

		if ( ( ! is_object( $license ) || 'valid' !== $license->license ) && empty( $showed_imissing_key_message[ $this->item_shortname ] ) ) {
			echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'edit.php?post_type=popup&page=pum-settings&tab=licenses' ) ) . '">' . esc_html__( 'Enter valid license key for automatic updates.', 'popup-maker' ) . '</a></strong>';
			$showed_imissing_key_message[ $this->item_shortname ] = true;
		}
	}

	/**
	 * Adds this plugin to the beta page
	 *
	 * @access  public
	 *
	 * @param   array $products
	 *
	 * @return array
	 */
	public function register_beta_support( $products ) {
		$products[ $this->item_shortname ] = $this->item_name;

		return $products;
	}
}
