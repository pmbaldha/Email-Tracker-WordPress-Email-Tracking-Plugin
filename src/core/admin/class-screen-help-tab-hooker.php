<?php
namespace PrashantWP\Email_Tracker\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PrashantWP\Email_Tracker\Core\Admin\Contract\Screen_Help_Tab as Screen_Help_Tab_Contract;
use PrashantWP\Email_Tracker\Core\Contract\Hooker;

class Screen_Help_Tab_Hooker implements Hooker {

    private $hook_suffix;
    private $screen_help_tabs;

    public function __construct( $hook_suffix, $screen_help_tabs ) {
        
        if ( ! is_string( $hook_suffix ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->hook_suffix must be string!" );
        }
    
        $this->hook_suffix = $hook_suffix;

        if ( is_array( $screen_help_tabs ) ) {
            foreach( $screen_help_tabs as $screen_help_tab ) {
                if ( ! $this->is_screen_help_tab_valid_instance( $screen_help_tab ) ) {
                    throw new \InvalidArgumentException( __CLASS__ . "->screen_help_tabs array instances must be instance of Screen_Help_Tab_Contract!" );
                }
            }
            $this->screen_help_tabs = $screen_help_tabs;
        } else {
            if ( ! $this->is_screen_help_tab_valid_instance( $screen_help_tabs ) ) {
                throw new \InvalidArgumentException( __CLASS__ . "->screen_help_tab instance must be instance of Screen_Help_Tab_Contract!" );
            }
            $this->screen_help_tabs = (array) $screen_help_tabs;
        }

        unset( $hook_suffix, $screen_help_tabs );
    }

    private function is_screen_help_tab_valid_instance( $screen_help_tab ) {
        return ( $screen_help_tab instanceof Screen_Help_Tab_Contract );
    }

    public function hook() {
        add_action( 'load-' . $this->hook_suffix,  array( $this, 'add_screen_help_tab' ) );
    }

    /**
     * add help tab
     *
     * @return void
     */
    public function add_screen_help_tab() {
        $screen = get_current_screen();

        foreach ( $this->screen_help_tabs as $screen_help_tab ) {
            $screen->add_help_tab( array(
                    'id'      => $screen_help_tab->get_id(),
                    'title'   => $screen_help_tab->get_title(),
                    'content' => $screen_help_tab->get_content(),
                )
            );
        }
        
        unset( $screen, $screen_help_tab );
    }
}