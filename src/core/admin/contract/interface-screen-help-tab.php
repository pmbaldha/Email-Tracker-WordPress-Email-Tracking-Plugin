<?php
namespace PrashantWP\Email_Tracker\Core\Admin\Contract;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Screen_Help_Tab {

    public function get_id();
    public function get_title();
    public function get_content();

}