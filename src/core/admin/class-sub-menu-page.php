<?php
namespace PrashantWP\Email_Tracker\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PrashantWP\Email_Tracker\Core\Contract\Viewer;
// Aliasing Menu_Page_Contract because getting error can't redeclare error Menu_Page
use PrashantWP\Email_Tracker\Core\Admin\Contract\Sub_Menu_Page as Sub_Menu_Page_Contract;

class Sub_Menu_Page implements Sub_Menu_Page_Contract
{
    protected $parent_slug = '';
    protected $page_title = '';
    protected $menu_title = '';
    protected $capability;
    protected $menu_slug;
    protected $viewer;
    protected $icon_url = '';
    protected $pos = null;

    /**
     * class constructor
     */
    public function __construct( Viewer $viewer ) {
        $this->viewer = $viewer;
    }

    /**
     * Set parent slug
     *
     * @param string $parent_slug
     * @return void
     */
    public function set_parent_slug( $parent_slug ) {
        if ( ! is_string( $parent_slug ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->parent_slug must be string type!" );
        }
        $this->parent_slug = $parent_slug;
    }

    /**
     * Get parent slug
     *
     * @return string
     */
    public function get_parent_slug() {
        return $this->parent_slug;
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

    /**
     * Set position of sub menu
     *
     * @param int|null $pos
     * @return void
     */
    public function set_pos( $pos = null ) {
        if ( ! is_int( $pos ) && ! is_null( $pos ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->pos must be integer or null type!" );
        }
        $this->pos = $pos;
    }

    public function get_pos() {
        return $this->pos;
    }

    public function get_callback_function() {
        return array( $this->viewer, 'view' );
    }
}