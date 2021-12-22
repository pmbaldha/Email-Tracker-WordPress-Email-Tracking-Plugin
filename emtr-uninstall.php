<?php
/**
 * To delete plugin
 *
 * @package email-read-tracker
 * @subpackage uninstall
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wpdb;
require_once EMTR_PLUGIN_PATH . 'defines.php';

function emtr_delete_table() {
	global $wpdb;

	/**
	 * Delete email list database table
	 */
	$tableName = \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'email' );
	$wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );

	/**
	 * Delete email track database table
	 */
	$tableName = \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'track_email_open_log' );
	$wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );

	/**
	 * Delete email track_email_link_master database table
	 */
	$tableName = \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'track_email_link_master' );
	$wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );

	/**
	 * Delete email track_email_link_master database table
	 */
	$tableName = \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'track_email_link_click_log' );
	$wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );

	/**
	 * Delete email track database table
	 */
	$tableName = \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'track_email_open_log' );
	$wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );

	/**
	 * Delete email list per page screen option value
	 */
	$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . "usermeta WHERE meta_key='emtr_emails_per_page'" );

	delete_option( \PrashantWP\Email_Tracker\Util::get_factory()->get( '\PrashantWP\Email_Tracker\Options' )->get_option_name() );
	/**
	 * Delete plugin database version
	 */
	delete_option( 'emtr_db_version' );
	delete_option( 'emtr_version' );

}

$all_roles = $GLOBALS['wp_roles']->role_names;
foreach ( $all_roles as $role_key => $role_val ) {
	$ret = wp_roles()->remove_cap( $role_key, EMTR_MANAGE_ALL_EMAILS_CAP );
}

$is_network_deactivation = fs_is_network_admin();
if ( function_exists( 'is_multisite' ) && is_multisite() && $is_network_deactivation ) {
	global $wpdb;
	$old_blog = $wpdb->blogid;
	// Get all blog ids
	$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	foreach ( $blogids as $blog_id ) {
		switch_to_blog( $blog_id );
		emtr_delete_table();
	}
	switch_to_blog( $old_blog );
} else {
	emtr_delete_table();
}
