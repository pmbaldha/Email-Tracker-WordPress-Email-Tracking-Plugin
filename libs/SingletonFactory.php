<?php
/**
 * To make sure model object intiated only once
 *
 * @package email-read-tracker
 * @subpackage lib
 */
 
 /**
 * base class which keep records of all model object
 *
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
if( !class_exists('SingletonFactory') ) {
	class SingletonFactory { 
		public $container = array();
		protected $json_to_var_replace_arr = array(
												"'" => "_s_sq_s_",
												'"' => "_s_dq_s_",
												'{' => "_s_sb_s_",
												'}' => "_s_eb_s_",
												':' => "_s_cl_s_",
												',' => "_s_cm_s_",
												' ' => "_s_sp_s_",
											);
		protected static $factory_inst = array();										
											
		public static function getInstance()
		{
			$cls = get_called_class(); // late-static-bound class name
			if ( !isset( self::$factory_inst[$cls] ) ) {
				self::$factory_inst[$cls] = new static;
			}
			return self::$factory_inst[$cls];
		}	
											
		protected function arr_to_key($class , $arg_arr)
		{
			$arg_json = json_encode($arg_arr);
			self::getInstance()->container_key = $class."_".str_replace(array_keys(self::getInstance()->json_to_var_replace_arr), array_values(self::getInstance()->json_to_var_replace_arr), $arg_json);
		}
		
		public static function __callStatic($class, $arg_arr) 
		{
			return self::getInstance()->getClassObj($class, $arg_arr);
		}
		
		public function __call($class, $arg_arr = array()) 
		{
			return self::getInstance()->getClassObj($class, $arg_arr);
		}
		
		public function __get($class) 
		{
			return self::getInstance()->getClassObj($class);
		}
		
		protected static function getClassObj($class, $arg_arr = array())
		{
			self::getInstance()->arr_to_key($class, $arg_arr);
			
			if (isset(self::getInstance()->container[self::getInstance()->container_key])) 
			{
				return self::getInstance()->container[self::getInstance()->container_key];
			} 
			else 
			{
				try 
				{
					if (!file_exists(self::getInstance()->base_path . DIRECTORY_SEPARATOR . $class . ".php")) {
						throw new Exception('Given Class "' . $class . '" not found!');
					}
					require_once(self::getInstance()->base_path . DIRECTORY_SEPARATOR . $class . ".php");
					$reflected_class = new ReflectionClass($class);
					return self::getInstance()->container[self::getInstance()->container_key] = $reflected_class->newInstanceArgs($arg_arr);
				} 
				catch (Exception $e) 
				{
					echo $e->getMessage();
				}
			}
			
		}
	}
}
 /**
 * base which initiates model object based on singleton pattern
 *
 */
define('EMTR_MODEL_PATH', __DIR__.'/../model');
if( !class_exists('EMTR_Model') ) {
	class EMTR_Model extends SingletonFactory {
		public $base_path = EMTR_MODEL_PATH;
		
	}
}