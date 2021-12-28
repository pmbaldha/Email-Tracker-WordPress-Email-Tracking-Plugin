<?php
namespace PrashantWP\Email_Tracker\Core\Admin\Contract;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Sub_Menu_Page {
    public function get_parent_slug();
    public function get_page_title();
    public function get_menu_title();
    public function get_capability();
    public function get_menu_slug();
    public function get_callback_function();
}