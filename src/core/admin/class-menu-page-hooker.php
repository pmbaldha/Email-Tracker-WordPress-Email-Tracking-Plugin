<?php
namespace PrashantWP\Email_Tracker\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Aliasing Menu_Page_Contract because getting error can't redeclare error Menu_Page
use PrashantWP\Email_Tracker\Core\Admin\Contract\Menu_Page as Menu_Page_Contract;
use PrashantWP\Email_Tracker\Core\Contract\Hooker;


class Menu_Page_Hooker implements Hooker {

    /**
     * admin menu page instance
     *
     * @var object of menu_page interface
     */
    private $menu_page;

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
    public function __construct( Menu_Page_Contract $menu_page ) {
        $this->menu_page = $menu_page;
    }

    /**
     * invoke admin menu page
     *
     * @return void
     */
    public function hook() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
    }

    /**
     * add admin menu page
     *
     * @return void
     */
    public function add_menu_page() {
        $this->hook_suffix = add_menu_page(
                                $this->menu_page->get_page_title(),
                                $this->menu_page->get_menu_title(),
                                $this->menu_page->get_capability(),
                                $this->menu_page->get_menu_slug(),
                                $this->menu_page->get_callback_function(),
                                $this->menu_page->get_icon_url()
                            );
    }
}