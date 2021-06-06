<?php
namespace PrashantWP\Email_Tracker\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Exception;

/**
 * autoloader to include src/* files
 */
class Name_Space_Auto_Loader {

	/**
	 * Self class instance
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * auto Loader name space prefix
	 *
	 * @var string
	 */
	private $namepace_prefix;

    /**
	 * auto loader name space directory
	 *
	 * @var string
	 */
	private $namepace_dir;

	/**
	 * Autoloader constructor
	 *
	 * @param string $namepace_prefix 
     * @param string $namepace_dir autoloading directory path
	 */
	public function __construct( $namepace_prefix, $namepace_dir ) {
        if ( ! is_string( $namepace_prefix ) ||  empty( $namepace_prefix ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "->namepace_prefix should not be empty." );
        }
		if ( ! is_string( $namepace_dir ) || empty( $namepace_dir ) ) {
            throw new \InvalidArgumentException( __CLASS__ . "namepace_dir should not be empty." );
        }
		$this->namepace_prefix = $namepace_prefix;
        $this->namepace_dir = $namepace_dir;
	}

	/**
	 * register class methods
	 *
	 * @param string $namepace_prefix 
     * @param string $namepace_dir autoloading directory path
	 * @return void
	 */
	public static function register( $namepace_prefix, $namepace_dir ) {
		if ( ! isset( self::$instance ) ) {
            self::$instance = new self( $namepace_prefix, $namepace_dir );

			spl_autoload_register( array( self::$instance, 'auto_load' ), true );
			register_shutdown_function( array( self::$instance, 'shutdown' ) );
        }
	}

	/**
	 * auto load namespaced class
	 *
	 * @param string $namespace_class
	 * @return void
	 */
	public function auto_load( $namespace_class ) {
		// bail if not desired namespace
		if ( 0 !== strpos( $namespace_class, $this->namepace_prefix ) ) {
			return;
		}

		$namespace_class_relative = preg_replace( '/^'. addslashes( $this->namepace_prefix ) . '/', '', $namespace_class );
		
		$is_interface = preg_match( '/\\\\Contract\\\\[^\\\\]+$/', $namespace_class_relative );
		
		$namespace_class_simple = str_replace( array( '_', '\\' ), array( '-', '/' ), strtolower( $namespace_class_relative ) );
		
		$temp_last_forward_slash_pos = strrpos( $namespace_class_simple, '/' );
		if (false === $temp_last_forward_slash_pos ) {
			$file_name_post_fix = $namespace_class_simple;
			$path_postfix = '';
		} else {
			$file_name_post_fix = substr( $namespace_class_simple, ( $temp_last_forward_slash_pos + 1 ) );	
			$path_postfix = substr( $namespace_class_simple, 0, $temp_last_forward_slash_pos );
		}

		$file_name = ( $is_interface ? 'interface-' : 'class-' ) . ltrim( $file_name_post_fix, '/' ) . '.php';
		$full_path = trailingslashit( $this->namepace_dir )  . ( empty( $path_postfix ) ? '' : trailingslashit( $path_postfix ) ) . $file_name;

		if ( file_exists( $full_path ) ) {
			require $full_path;
			return;
		}


		throw new Exception( 'namespace ' . $namespace_class . ' can\'t be found! Path parsed: ' . $full_path );
	}

	public function shutdown() {
		spl_autoload_unregister(  array( self::$instance, 'auto_load' ) );
	}
}