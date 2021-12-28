<?php
namespace PrashantWP\Email_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Base {

	protected $factory;

	public function __construct() {
		$this->factory = Util::get_factory();
	}
}
