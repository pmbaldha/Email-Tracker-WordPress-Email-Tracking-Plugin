<?php
/**
 * Provides almost all public static function which required by plugin
 *
 * @package email-read-tracker
 */
namespace PrashantWP\Email_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Util {

	public static function emtr_get_table_name( $var ) {
		global $wpdb;
		return $wpdb->prefix . 'emtr_' . $var;
	}

	public static function emtr_extract_attachments( $attachments ) {
		$attachments     = is_array( $attachments ) ? $attachments : array( $attachments );
		$attachment_urls = array();
		$uploads         = wp_upload_dir();
		$basename        = basename( $uploads['baseurl'] );
		$basename_needle = '/' . $basename . '/';
		foreach ( $attachments as $attachment ) {
			$append_url        = substr( $attachment, strrpos( $attachment, $basename_needle ) );
			$attachment_urls[] = $append_url;
		}
		return implode( ',\n', $attachment_urls );
	}

	public static function emtr_relative_time( $timestamp ) {
		if ( $timestamp != '' && ! is_int( $timestamp ) ) {
			$timestamp = strtotime( $timestamp );
		}
		if ( ! is_int( $timestamp ) ) {
			return 'never';
		}
		$difference = strtotime( get_date_from_gmt( gmdate( 'Y-m-d H:i:s' ) ) ) - $timestamp;
		$periods    = array( 'moment', 'min', 'hour', 'day', 'week', 'month', 'year', 'decade' );
		$lengths    = array( '60', '60', '24', '7', '4.35', '12', '10', '10' );
		if ( $difference >= 0 ) {
			// This was in the past
			$ending = 'ago';
		} else {
			// This is in the future
			$difference = -$difference;
			$ending     = 'to go';
		}
		for ( $j = 0; $difference >= $lengths[ $j ]; $j++ ) {
			$difference /= $lengths[ $j ];
		}
		$difference = round( $difference );
		if ( $difference != 1 ) {
			$periods[ $j ] .= 's';
		}
		if ( $difference < 60 && $j == 0 ) {
			return " ({$periods[$j]} {$ending})";
		}
		return " ({$difference} {$periods[$j]} {$ending})";
	}

	public static function emtr_set_success_msg( $msg ) {
		// $_SESSION["success_msg"][] = $msg;
		set_transient( 'emtr_success_msg', $msg, 60 );
	}

	public static function emtr_set_error_msg( $msg ) {
		// $_SESSION["error_msg"][] = $msg;
		set_transient( 'emtr_error_msg', $msg, 60 );
	}

	public static function emtr_display_error_msg() {
		$emtr_error_msg = get_transient( 'emtr_error_msg' );
		if ( $emtr_error_msg !== false ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $emtr_error_msg ) . '</p></div>';
			delete_transient( 'emtr_error_msg' );
		}
	}

	public static function emtr_display_success_msg() {
		$emtr_success_msg = get_transient( 'emtr_success_msg' );
		if ( $emtr_success_msg !== false ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $emtr_success_msg ) . '</p></div>';
			delete_transient( 'emtr_success_msg' );
		}
	}

	public static function get_factory() {
		static $factory_obj;
		if ( ! isset( self::$obj ) ) {
			$factory_obj = new \PrashantWP\Email_Tracker\Factory();
		}
		return $factory_obj;
	}

	public static function delete_emails( $email_ids = array()  ) {
		global $wpdb;

		$wpdb->query(
			'DELETE FROM ' . self::emtr_get_table_name( 'email' ) . ' WHERE email_id IN (' . implode(',', $email_ids ) . ')'
		);

		$wpdb->query(
			'DELETE FROM ' . self::emtr_get_table_name( 'track_email_open_log' ) . ' WHERE trkemail_email_id IN ('. implode(',', $email_ids ) . ')'
		);

		$wpdb->query(
			'DELETE FROM ' .
					self::emtr_get_table_name( 'track_email_link_click_log' ) . ' WHERE trklinkclick_trklink_id IN (SELECT trklink_id FROM ' . self::emtr_get_table_name( 'track_email_link_master' ).' WHERE trklink_email_id IN ('.implode(',',$email_ids ).') 
					)'
		);

		$wpdb->query(
			'DELETE FROM ' . self::emtr_get_table_name( 'track_email_link_master' ) . ' WHERE trklink_email_id IN (' . implode(',', $email_ids ). ')'
		);

		return true;
	}
}
