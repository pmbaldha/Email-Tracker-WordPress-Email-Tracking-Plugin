<?php
namespace PrashantWP\Email_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages site options using the WordPress options API.
 */
final class Options extends \PrashantWP\Email_Tracker\Core\Options {

	private $option_name      = 'email-tracker-settings';
	private $option_container = array();

	public function __construct() {
		$this->option_container = get_option( $this->option_name, array() );
	}

	public function get_option_name() {
		return $this->option_name;
	}

	/**
	 * Gets the site option for the given name. Returns the default value if the value does not exist.
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get( $name, $default = null ) {
		if ( isset( $this->option_container[ $name ] ) ) {
			return $this->option_container[ $name ];
		}

		return $default;
	}

	/**
	 * Removes the site option with the given name.
	 *
	 * @param string $name
	 */
	public function remove( $name ) {
		unset( $this->option_container[ $name ] );
		$this->save_option();
	}

	/**
	 * Sets a site option. Overwrites the existing site option if the name is already in use.
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	public function set( $name, $value ) {
		$this->option_container[ $name ] = $value;
		$this->save_option();
	}

	private function save_option() {
		update_option( $this->option_name, $this->option_container );
	}
}
