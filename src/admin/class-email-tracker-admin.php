<?php
namespace PrashantWP\Email_Tracker\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Exception;
use PrashantWP\Email_Tracker\Util;

class Email_Tracker_Admin {

    /**
	 * Self class instance
	 *
	 * @var object
	 */
	private static $instance;

    public function __construct() {
        
    }

    public static function register() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();

            self::$instance->set_up_email_list();
            self::$instance->set_up_rest();
        }
    }

    public function set_up_email_list() {
        ( new Email_List\Setup() )->init();
    }

    public function set_up_rest() {
        add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
    }

    public function rest_api_init() {
        // email-tracker/v1/email/5
        register_rest_route( '/email-tracker/v1/', '/email/(?P<id>[\d]+)', array(
            'methods' => 'GET',
            'callback' =>  array( $this, 'get_email_for_view' ),
            'args'         => array(
                'id' => array(
                    'validate_callback' => array( $this, 'rest_validate_email_id_request' ),
                ),
            ),
            'permission_callback' => array( $this, 'rest_permission_check' ),
        ) );
    }

    public function rest_validate_email_id_request( $param, $request, $key ) {
        return is_numeric( $param );
    }

    public function rest_permission_check() {
        return current_user_can( 'manage_options' );
    }

    public function get_email_for_view( $request ) {
        global $wpdb;

        $ret_data = array();
        $email_id = $request['id'];

        $sql = $wpdb->prepare( 'SELECT `to`, subject, message, message_plain, headers, attachments, date_time, '.
                                '(SELECT count( trkemail_id	) FROM ' . Util::emtr_get_table_name( 'track_email_open_log' ) . ' WHERE trkemail_email_id = em.email_id) AS total_read_count, '.
                                '(SELECT count( trklinkclick_id	) FROM ' . Util::emtr_get_table_name( 'track_email_link_click_log' ) . ' WHERE trklinkclick_email_id = em.email_id ) AS total_link_click_count '.
                                'FROM ' . Util::emtr_get_table_name( 'email' ) . ' AS em WHERE email_id=%d', $email_id );
        $email_row = $wpdb->get_row( $sql, ARRAY_A );

        if ( is_null( $email_row ) ) {
            throw new Exception( 'Email can\'t be found!' );
        }
        $pass_key_arr = array(
            'to',
            'subject',
            'message',
            'message_plain',
            'headers',
            'attachments',
            'date_time',
            'total_read_count',
            'total_link_click_count',
        );
        foreach ($pass_key_arr as $field ) {
            $ret_data[$field] = $email_row[$field];    
        }

        $ret_data['read_log'] = array();
        $sql = $wpdb->prepare( 'SELECT trkemail_id as read_id, trkemail_date_time as date_time, trkemail_ip_address as ip_address FROM ' . Util::emtr_get_table_name( 'track_email_open_log' ) . ' EOD WHERE EOD.trkemail_email_id = %d ORDER BY EOD.trkemail_date_time DESC', $email_id );
        $email_open_res = $wpdb->get_results( $sql, ARRAY_A );
        foreach ( $email_open_res as $row ) {
            $ret_data['read_log'][] = array(
                'read_id' => $row['read_id'],
                'date_time' => $row['date_time'],
                'ip_address' => $row['ip_address'],
            );
        }

        $ret_data['link_click_log'] = array();
        $email_link_res = 'SELECT trkemail_date_time as date_time, trkemail_ip_address as ip_address FROM ' . Util::emtr_get_table_name( 'track_email_open_log' ) . ' EOD WHERE EOD.trkemail_email_id = E.email_id ORDER BY EOD.trkemail_date_time DESC';

        $sql = $GLOBALS['wpdb']->prepare(
            'SELECT lm.trklink_id as link_id, lm.trklink_link as link, lc.trklinkclick_date_time as date_time, lc.trklinkclick_ip_address as ip_address '.
                'FROM ' . Util::emtr_get_table_name( 'track_email_link_master' ) . ' lm LEFT JOIN ' . Util::emtr_get_table_name( 'track_email_link_click_log' ) . ' lc '.
                    ' ON lm.trklink_id = lc.trklinkclick_trklink_id '.
                'WHERE lm.trklink_email_id = %d '.
                'ORDER BY lm.trklink_id ASC, lc.trklinkclick_date_time DESC;',
            $email_id
        );
        $email_link_res = $GLOBALS['wpdb']->get_results( $sql, ARRAY_A );

        foreach ( $email_link_res as $row ) {
            $ret_data['link_click_log'][] = array(
                'link_id' => $row['link_id'],
                'link' => $row['link'],
                'date_time' => $row['date_time'],
                'ip_address' => $row['ip_address'],
            );
        }

        return $ret_data;
    }
}