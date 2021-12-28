<?php
namespace PrashantWP\Email_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Factory {
	private static $object_container = array();

	public static function get( $namespace ) {
		if ( ! isset( self::$object_container[ $namespace ] ) ) {
			self::$object_container[ $namespace ] = new $namespace();
		}
		return self::$object_container[ $namespace ];
	}
}
