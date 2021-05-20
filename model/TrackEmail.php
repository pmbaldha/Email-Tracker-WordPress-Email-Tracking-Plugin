<?php

/**
 * To make entry for email track
 *
 * @package email-read-tracker
 * @subpackage model
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class TrackEmail
{
    public function __construct()
    {
        global  $wpdb ;
        // Table Name
        $this->eo_table_name = emtr_get_table_name( 'track_email_open_log' );
        $this->email_table_name = emtr_get_table_name( 'email' );
        $this->eo_pk = 'trkemail_id';
        $this->eo_fk = 'trkemail_email_id';
        $this->rw_url_email_open = '/track/e/o/';
        /*
         * To set time difference between two read tracked
         * @package email-read-tracker
         * @subpackage model
         */
        $this->track_interval = 0;
        // in hour
    }
    
    /**
     * insert_email_open_log()
     *
     * @param array $POST = posted data
     * @return true
     *
     * add log data for email opened by track image access request or by click on link in email
     * check for last log during given track interval
     * if last entry is less than given time than, will add new open log entry
     */
    public function insert_email_open_log( $POST )
    {
        global  $wpdb ;
        $rs_email_cnt = $wpdb->get_var( $wpdb->prepare( 'SELECT count(*) FROM ' . $this->email_table_name . ' WHERE 
								email_id=%d', intval( $POST['trkemail_email_id'] ) ) );
        /**
         * If email is deleted, no need to track it
         */
        if ( $rs_email_cnt == 0 ) {
            return false;
        }
        $POST['trkemail_tacked_by'] = 'Image';
        // Get date time for given interval
        $interval_date_time = date( 'Y-m-d H:i:s', strtotime( sprintf( '-%d hours', $this->track_interval ) ) );
        // Set sql query parameters
        $param = 'AND ' . $this->eo_fk . ' = %s AND trkemail_date_time > %s';
        // Get record
        $rs_cnt = $wpdb->get_var( $wpdb->prepare( 'SELECT count(*) FROM ' . $this->eo_table_name . ' WHERE 1 ' . $param, $POST[$this->eo_fk], $interval_date_time ) );
        
        if ( $rs_cnt == 0 ) {
            $arr_insert = array(
                $this->eo_fk               => $POST[$this->eo_fk],
                'trkemail_tacked_by'       => $POST['trkemail_tacked_by'],
                'trkemail_http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'trkemail_ip_address'      => $this->get_client_ip(),
                'trkemail_date_time'       => gmdate( 'Y-m-d H:i:s' ),
            );
            $wpdb->insert( $this->eo_table_name, $arr_insert, array(
                '%s',
                '%s',
                '%s',
                '%s'
            ) );
            unset( $arr_insert );
        }
        
        return true;
    }
    
    /**
     * GetLink()
     *
     * @author Dinesh
     *
     * @param int $ldemail_id = Lead email id
     * @return string image tag with track url
     *
     * @ check for link for given id and return if found and return false if not found
     */
    public function get_track_code( $email_id )
    {
        return "<img src='" . get_home_url() . $this->rw_url_email_open . $email_id . "/track-log' alt='track' />";
    }
    
    /**
     * TrackEmail::Insert_Link_Click_Log()
     *
     * @param array $POST = posted value
     * @return true
     *
     * add log for link clicked
     */
    public function insert_link_click_log( $link_id )
    {
    }
    
    public function insert_link_open_log( $email_id )
    {
        $interval_date_time = date( 'Y-m-d H:i:s', strtotime( gmdate( 'Y-m-d H:i:s' ) ) - 3600 * 60 );
        $client_ip = self::get_client_ip();
        $sql = $GLOBALS['wpdb']->prepare(
            'SELECT count(*) as count FROM ' . emtr_get_table_name( 'track_email_open_log' ) . ' WHERE trkemail_email_id=%d AND trkemail_ip_address = %s AND trkemail_date_time > %s',
            $email_id,
            $client_ip,
            $interval_date_time
        );
        $already_track = $GLOBALS['wpdb']->get_var( $sql );
        
        if ( !$already_track ) {
            $arr_insert = array(
                'trkemail_email_id'        => $email_id,
                'trkemail_tacked_by'       => 'link',
                'trkemail_http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'trkemail_ip_address'      => $this->get_client_ip(),
                'trkemail_date_time'       => gmdate( 'Y-m-d H:i:s' ),
            );
            $GLOBALS['wpdb']->insert( $this->eo_table_name, $arr_insert, array(
                '%s',
                '%s',
                '%s',
                '%s'
            ) );
            unset( $arr_insert );
        }
    
    }
    
    /**
     * TrackEmail::GetLink()
     *
     * @param int $pk = ID of link
     * @return href link | false
     *
     * @ check for link for given id and return if found and return false if not found
     */
    public function get_link( $pk )
    {
        $link = $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( 'SELECT trklink_link FROM ' . $GLOBALS['wpdb']->prefix . 'emtr_track_email_link_master WHERE trklink_id=%d', $pk ) );
        
        if ( !empty($link) ) {
            return $link;
        } else {
            return false;
        }
    
    }
    
    /**
     * TrackEmail::EmailLinkReplace()
     *
     * @param array $arrLeadEmail = array of lead email
     * @return new email contain
     *
     * @ find all links in html contain and replace link with traking logic
     * Also insert link information in database
     */
    public function email_link_replace( $email_content, $email_id, $user_id )
    {
        /*$regex = "/<a.*?href=[\"']?([^>'\"]*)[\"']?[^>]*?>(.*?)<\/a>/i";*/
        $regex = "/<a.*?href=[\"']?(?!javascript:|#)([^>'\"]+)[\"']?[^>]*?>(.+?)<\\/a>/i";
        preg_match_all( $regex, $email_content, $matches );
        $all_links = $matches[1];
        // Set populateSchema for table link master
        foreach ( $all_links as $key => $link ) {
            $GLOBALS['wpdb']->insert( emtr_get_table_name( 'track_email_link_master' ), array(
                'trklink_link'     => $link,
                'trklink_email_id' => $email_id,
                'trklink_user_id'  => $user_id,
            ), array( '%s', '%d', '%d' ) );
            // generate new link
            $link_id = $GLOBALS['wpdb']->insert_id;
            $new_link = home_url( '/track/e/l/' . $link_id );
            // Exact match required and these are twice required
            $email_content = str_replace( "'" . $link . "'", "'" . $new_link . "'", $email_content );
            $email_content = str_replace( '"' . $link . '"', '"' . $new_link . '"', $email_content );
        }
        return stripslashes( $email_content );
    }
    
    public function get_link_log( $email_id )
    {
        $sql = $GLOBALS['wpdb']->prepare( 'SELECT TEM.*, 
                (
                    SELECT COUNT(*) FROM  ' . emtr_get_table_name( 'track_email_link_click_log' ) . ' TEC 
                    WHERE TEC.trklinkclick_trklink_id = TEM.trklink_id
                ) AS total_clicked,				
                (
                    SELECT MAX(trklinkclick_date_time) FROM  ' . emtr_get_table_name( 'track_email_link_click_log' ) . ' TEC 
                    WHERE TEC.trklinkclick_trklink_id = TEM.trklink_id
                ) AS last_clicked
                FROM ' . emtr_get_table_name( 'track_email_link_master' ) . ' TEM WHERE TEM.trklink_email_id = %d', $email_id );
        // Execute query
        $rs = $GLOBALS['wpdb']->get_results( $sql, ARRAY_A );
        return $rs;
    }
    
    public function get_email_view_data( $email_id )
    {
        $item = $GLOBALS['wpdb']->get_row( $GLOBALS['wpdb']->prepare( 'SELECT E.*,
							(SELECT count(*) FROM ' . emtr_get_table_name( 'track_email_open_log' ) . ' EOC WHERE EOC.trkemail_email_id = E.email_id) AS view_count,
							(SELECT GROUP_CONCAT(trkemail_date_time) FROM ' . emtr_get_table_name( 'track_email_open_log' ) . ' EOD WHERE EOD.trkemail_email_id = E.email_id ORDER BY EOD.trkemail_date_time DESC) AS view_date_time 
						 FROM ' . emtr_get_table_name( 'email' ) . ' E WHERE 1 AND E.email_id=%d', $email_id ), ARRAY_A );
        return $item;
    }
    
    /**
     * get_client_ip()
     *
     * @param nonr
     * @return ip address
     *
     * to get ip address of tracked email when it will be read
     */
    function get_client_ip()
    {
        $ipaddress = '';
        
        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        
        $ipaddress_exploded = explode( ',', $ipaddress );
        $ipaddress_exploded = array_map( 'trim', $ipaddress_exploded );
        $ipaddress_exploded = array_filter( $ipaddress_exploded );
        return $ipaddress[0];
    }

}