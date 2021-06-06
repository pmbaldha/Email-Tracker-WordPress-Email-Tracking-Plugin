<?php
namespace PrashantWP\Email_Tracker\Core\Admin\Contract;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Screen_Help_Option {

    public function get_id();

    public function get_label();
    public function get_default();
    public function get_option();

}