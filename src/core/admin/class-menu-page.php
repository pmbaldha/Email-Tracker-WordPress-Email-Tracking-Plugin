<?php
namespace PrashantWP\Email_Tracker\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PrashantWP\Email_Tracker\Core\Contract\Viewer;
// Aliasing Menu_Page_Contract because getting error can't redeclare error Menu_Page
use PrashantWP\Email_Tracker\Core\Admin\Contract\Menu_Page as Menu_Page_Contract;

class Menu_Page implements Menu_Page_Contract
{
    protected $page_title = '';
    protected $menu_title = '';
    protected $capability;
    protected $menu_slug;
    protected $viewer;
    protected $icon_url = '';

    /**
     * class constructor
     */
    public function __construct( Viewer $viewer ) {
        $this->viewer = $viewer;
    }

    /**
     * Set page title
     *
     * @param string $page_title
     * @return void
     */
    public function set_page_title( $page_title) {
        if ( ! is_string( $page_title ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->page_title must be string type!" );
        }
        $this->page_title = $page_title;
    }

    public function get_page_title() {
        return $this->page_title;
    }

    /**
     * Set menu title
     *
     * @param string $menu_title
     * @return void
     */
    public function set_menu_title( $menu_title ) {
        if ( ! is_string( $menu_title ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->menu_title must be string type!" );
        }
        $this->menu_title = $menu_title;
    }

    public function get_menu_title() {
        return $this->menu_title;
    }

    /**
     * Set menu page capability
     *
     * @param string $capability
     * @return void
     */
    public function set_capability( $capability ) {
        $this->capability = $capability;
    }

    public function get_capability() {
        return $this->capability;
    }

    /**
     * Set menu slug
     *
     * @param string $menu_slug
     * @return void
     */
    public function set_menu_slug( $menu_slug ) {
        if ( ! is_string( $menu_slug ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->menu_slug must be string type!" );
        }
        $this->menu_slug = $menu_slug;
    }

    public function get_menu_slug() {
        return $this->menu_slug;
    }

    public function get_callback_function() {
        return array( $this->viewer, 'view' );
    }

    /**
     * set icon url
     *
     * @param string $icon_url
     * @return void
     */
    public function set_icon_url( $icon_url ) {
        if ( ! is_string( $icon_url ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->icon_url must be string type!" );
        }
        $this->icon_url = $icon_url;
    }

    public function get_icon_url() {
        return $this->icon_url;
    }
}