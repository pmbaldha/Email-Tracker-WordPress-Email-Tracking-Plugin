<?php
namespace PrashantWP\Email_Tracker\Core\Contract;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Admin_Menu_Page {

    public function get_page_title();
    public function get_menu_title();
    public function get_capability();
    public function get_menu_slug();
    public function get_render_function();
    public function get_icon_url();

}