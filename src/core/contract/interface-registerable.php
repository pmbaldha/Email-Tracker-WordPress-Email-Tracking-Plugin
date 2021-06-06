<?php
namespace PrashantWP\Email_Tracker\Core\Contract;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Registerable {

    /**
     * Register action and filter hooks
     *
     * @return void
     */
    public static function register();

}
