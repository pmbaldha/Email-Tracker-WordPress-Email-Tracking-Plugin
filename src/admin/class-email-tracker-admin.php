<?php
namespace PrashantWP\Email_Tracker\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Exception;
final class Email_Tracker_Admin extends \PrashantWP\Email_Tracker\Base {

    /**
	 * Self class instance
	 *
	 * @var object
	 */
	private static $instance;

    public function __construct() {
        parent::__construct();
    }

    public static function register() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();

            self::$instance->set_up_email_list();
	        if ( emtr()->is__premium_only() ) {
		        self::$instance->set_up_settings__premium_only();
	        }
        }
    }

    public function set_up_email_list() {
        $this->factory
                ->get( '\PrashantWP\Email_Tracker\Admin\Email_List\Setup' )
                ->init();
    }

    public function set_up_settings__premium_only() {
        ( new Settings\Setup() )->init();
    }
}