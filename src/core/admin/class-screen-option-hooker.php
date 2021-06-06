<?php
namespace PrashantWP\Email_Tracker\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// use PrashantWP\Email_Tracker\Core\Admin\Contract\Screen_Option as Screen_Option_Contract;
use PrashantWP\Email_Tracker\Core\Admin\Contract\Screen_Option as Screen_Option_Contract;
use PrashantWP\Email_Tracker\Core\Contract\Hooker;

class Screen_Option_Hooker implements Hooker {

    private $hook_suffix;
    private $screen_help_tabs;

    public function __construct( $hook_suffix, Screen_Option_Contract $screen_option ) {
        
        if ( ! is_string( $hook_suffix ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->hook_suffix must be string!" );
        }
    
        $this->hook_suffix = $hook_suffix;
        $this->screen_option = $screen_option;

        unset( $hook_suffix, $screen_option );
    }

    public function hook() {
        add_action( 'load-' . $this->hook_suffix,  array( $this, 'add_screen_option' ) );
        add_filter( 'set-screen-option', array( $this, 'set_screen_option' ) , 10, 3 );
    }

    /**
     * add screen option
     *
     * @return void
     */
    public function add_screen_option() {
        $screen = get_current_screen();

        $args = array(
            'label' => $this->screen_option->get_label(),
            'default' => $this->screen_option->get_default(),
            'option' => $this->screen_option->get_option(),
        );
        add_screen_option( $this->screen_option->get_id(), $args );
        
        unset( $screen );
    }

    public function set_screen_option( $status, $option, $value ) {
        if ( $this->screen_option->get_option() == $option ) {
            return intval( $value );
        } else {
            return $value;
        }
    }
}