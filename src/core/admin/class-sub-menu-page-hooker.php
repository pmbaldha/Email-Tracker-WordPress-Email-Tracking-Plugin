<?php
namespace PrashantWP\Email_Tracker\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Aliasing Menu_Page_Contract because getting error can't redeclare error Menu_Page
use PrashantWP\Email_Tracker\Core\Admin\Contract\Sub_Menu_Page as Sub_Menu_Page_Contract;
use PrashantWP\Email_Tracker\Core\Contract\Hooker;

class Sub_Menu_Page_Hooker implements Hooker {

    /**
     * admin submenu page instance
     *
     * @var object of submenu_page interface
     */
    private $sub_menu_page;

    /**
     * hook suffix
     *
     * @var string
     */
    private $hook_suffix;

    /**
     * Class constructor
     *
     * @param Admin_Menu $menu_page Object of class that implemented Admin_Menu interface
     */
    public function __construct( Sub_Menu_Page_Contract $sub_menu_page ) {
        $this->sub_menu_page = $sub_menu_page;
    }

    /**
     * invoke admin menu page
     *
     * @return void
     */
    public function hook() {
        add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
    }

    /**
     * add admin menu page
     *
     * @return void
     */
    public function add_submenu_page() {
        $this->hook_suffix = add_submenu_page(
                                $this->sub_menu_page->get_parent_slug(),
                                $this->sub_menu_page->get_page_title(),
                                $this->sub_menu_page->get_menu_title(),
                                $this->sub_menu_page->get_capability(),
                                $this->sub_menu_page->get_menu_slug(),
                                $this->sub_menu_page->get_callback_function(),
                                $this->sub_menu_page->get_pos()
                            );
    }
}