<?php
namespace PrashantWP\Email_Tracker\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PrashantWP\Email_Tracker\Core\Contract\Viewer;
// Aliasing Screen_Help_Tab because getting error can't redeclare error Screen_Help_Tab
use PrashantWP\Email_Tracker\Core\Admin\Contract\Screen_Help_Tab as Screen_Help_Tab_Contract;

final class Screen_Help_Tab implements Screen_Help_Tab_Contract
{
    private $id;
    private $title;
    private $content;

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
     * set title
     *
     * @param string $title
     * @return void
     */
    public function set_title( $title ) {
        if ( ! is_string( $title ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->title must be string type!" );
        }
        $this->title = $title;
    }

    public function get_title() {
        return $this->title;
    }

    /**
     * set title
     *
     * @param string $title
     * @return void
     */
    public function set_content( $content ) {
        if ( ! is_string( $content ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->content must be string type!" );
        }
        $this->content = $content;
    }

    public function get_content() {
        return $this->content;
    }
}