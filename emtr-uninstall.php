<?php
/**
 * To delete plugin
 *
 * @package email-read-tracker
 * @subpackage uninstall
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb;
/*
 * For emtr_get_table_name function
 */
require_once( EMTR_PLUGIN_PATH . 'et-functions.php' );

function emtr_delete_table()
{
	global $wpdb;
	/**
	 * Delete email list database table
	 */
	$tableName = emtr_get_table_name('email');
	$wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );
	
	/**
	 * Delete email track database table
	 */
	$tableName =  emtr_get_table_name('track_email_open_log');
	$wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );
	
	
	/**
	 * Delete email track_email_link_master database table
	 */
	$tableName =  emtr_get_table_name('track_email_link_master');
	$wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );
	
	/**
	 * Delete email track_email_link_master database table
	 */
	$tableName =  emtr_get_table_name('track_email_link_click_log');
	$wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );
	
	/**
	 * Delete email track database table
	 */
	$tableName =  emtr_get_table_name('track_email_open_log');
	$wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );
	
	
	
	/**
	 * Delete email list per page screen option value
	 */
	$wpdb->query( "DELETE FROM ".$wpdb->prefix."usermeta WHERE meta_key='emtr_emails_per_page'" );
	/**
	 * Delete plugin databse vwrsion
	 */
	delete_option( 'emtr_db_version' );
	delete_option( 'emtr_version' );
}

if (function_exists( 'is_multisite' ) && is_multisite() ) {
   global $wpdb;
   $old_blog =  $wpdb->blogid;
   //Get all blog ids
   $blogids =  $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
   foreach ( $blogids as $blog_id ) {
      switch_to_blog($blog_id);
      emtr_delete_table();
   }
   switch_to_blog( $old_blog );
} else {
   emtr_delete_table();
}

