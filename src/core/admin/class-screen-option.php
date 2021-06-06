<?php
namespace PrashantWP\Email_Tracker\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Aliasing Screen_Help_Tab because getting error can't redeclare error Screen_Option
use PrashantWP\Email_Tracker\Core\Admin\Contract\Screen_Option as Screen_Option_Contract;

final class Screen_Option implements Screen_Option_Contract
{
    private $id;
    private $label;
    private $default;
    private $option;

    /**
     * set id
     *
     * @param string $id
     * @return void
     */
    public function set_id( $id ) {
        if ( ! is_string( $id ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->id must be string type!" );
        }
        $this->id = $id;
    }

    public function get_id() {
        return $this->id;
    }

    /**
     * set label
     *
     * @param string $label
     * @return void
     */
    public function set_label( $label ) {
        if ( ! is_string( $label ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->label must be string type!" );
        }
        $this->label = $label;
    }

    public function get_label() {
        return $this->label;
    }

     /**
     * set default
     *
     * @param string $default
     * @return void
     */
    public function set_default( $default ) {
        $this->default = $default;
    }

    public function get_default() {
        return $this->default;
    }

    /**
     * set label
     *
     * @param string $label
     * @return void
     */
    public function set_option( $option ) {
        if ( ! is_string( $option ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->option must be string type!" );
        }
        $this->option = $option;
    }

    public function get_option() {
        return $this->option;
    }
   
}