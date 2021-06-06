<?php
namespace PrashantWP\Email_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Email Tracker class responsible to load everything
 */
class Email_Tracker_Invoker {

    /**
	 * Self class instance
	 *
	 * @var object
	 */
	private static $instance;

    /**
     * class constructor
     */
    public function __construct() {
        
    }

    public static function register() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();

			require_once plugin_dir_path( __FILE__ ) . '/core/class-name-space-auto-loader.php';
            // PrashantWP\Email_Tracker
            $name_space_prefix = 'PrashantWP\\Email_Tracker\\';
            $name_space_path = plugin_dir_path( __FILE__ );
            Core\Name_Space_Auto_Loader::register( $name_space_prefix, $name_space_path );

            Admin\Email_Tracker_Admin::register();
        }
    }


}

