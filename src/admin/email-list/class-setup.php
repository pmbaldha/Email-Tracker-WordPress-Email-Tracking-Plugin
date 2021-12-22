<?php

namespace PrashantWP\Email_Tracker\Admin\Email_List;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
use  PrashantWP\Email_Tracker\Core\Admin\Menu_Page ;
use  PrashantWP\Email_Tracker\Core\Admin\Menu_Page_Hooker ;
use  PrashantWP\Email_Tracker\Core\Admin\Screen_Help_Tab ;
use  PrashantWP\Email_Tracker\Core\Admin\Screen_Help_Tab_Hooker ;
use  PrashantWP\Email_Tracker\Core\Admin\Screen_Option ;
use  PrashantWP\Email_Tracker\Core\Admin\Screen_Option_Hooker ;
class Setup extends \PrashantWP\Email_Tracker\Base
{
    private  $menu_slug ;
    private  $hook_suffix ;
    public function __construct()
    {
        $this->menu_slug = 'emtr_email_list';
        parent::__construct();
    }
    
    public function init()
    {
        $this->hook();
        $this->menu_page();
        $this->help_tabs();
        $this->help_options();
    }
    
    public function hook()
    {
        add_action( 'init', array( $this, 'init_hook' ), 10 );
        add_action(
            'admin_enqueue_scripts',
            array( $this, 'admin_enqueue_scripts' ),
            999,
            1
        );
        $option_name = $this->factory->get( 'PrashantWP\\Email_Tracker\\Options' )->get_option_name();
        add_action(
            'add_option_' . $option_name,
            array( $this, 'set_up_capability_to_roles_add_hook' ),
            10,
            2
        );
        add_action(
            'update_option_' . $option_name,
            array( $this, 'set_up_capability_to_roles_update_hook' ),
            10,
            3
        );
        add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
    }
    
    public function get_menu_slug()
    {
        return $this->menu_slug;
    }
    
    public function get_hook_suffix()
    {
        return 'toplevel_page_' . $this->get_menu_slug();
    }
    
    public function menu_page()
    {
        $email_list_menu_page = new Menu_Page( new Page_Viewer() );
        $email_list_menu_page->set_page_title( esc_html__( 'Email List', 'email-tracker' ) );
        $email_list_menu_page->set_menu_title( esc_html__( 'Email Tracker', 'email-tracker' ) );
        $email_list_menu_page->set_capability( $this->get_cap_to_manage_all_emails() );
        // Make change here cause to change in freemius lib call function too.
        $email_list_menu_page->set_menu_slug( $this->get_menu_slug() );
        $email_list_menu_page->set_icon_url( 'dashicons-email-alt' );
        $menu_page_hooker = new Menu_Page_Hooker( $email_list_menu_page );
        $menu_page_hooker->hook();
    }
    
    private function get_cap_to_manage_all_emails()
    {
        return EMTR_MANAGE_ALL_EMAILS_CAP;
    }
    
    public function init_hook()
    {
        if ( current_user_can( 'administrator' ) && !current_user_can( $this->get_cap_to_manage_all_emails() ) ) {
            $this->set_up_capability_to_administrator();
        }
    }
    
    public function set_up_capability_to_administrator()
    {
        wp_roles()->add_cap( 'administrator', $this->get_cap_to_manage_all_emails(), true );
    }
    
    public function set_up_capability_to_roles_add_hook( $option, $value )
    {
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        $option_name = $this->factory->get( 'PrashantWP\\Email_Tracker\\Options' )->get_option_name();
        if ( $option_name != $option ) {
            return;
        }
        error_log( 'add_option' );
        $new_roles = ( isset( $value['roles_to_access_all_emails'] ) ? $value['roles_to_access_all_emails'] : array() );
        if ( is_array( $new_roles ) && !empty($new_roles) ) {
            foreach ( $new_roles as $new_role ) {
                wp_roles()->add_cap( $new_role, $this->get_cap_to_manage_all_emails(), true );
            }
        }
    }
    
    public function set_up_capability_to_roles_update_hook( $old_option_values, $new_option_values, $option )
    {
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        $option_name = $this->factory->get( 'PrashantWP\\Email_Tracker\\Options' )->get_option_name();
        if ( $option_name != $option ) {
            return;
        }
        error_log( 'update_option' );
        $old_roles = ( isset( $old_option_values['roles_to_access_all_emails'] ) ? $old_option_values['roles_to_access_all_emails'] : array() );
        if ( is_array( $old_roles ) && !empty($old_roles) ) {
            foreach ( $old_roles as $old_role ) {
                wp_roles()->remove_cap( $old_role, $this->get_cap_to_manage_all_emails() );
            }
        }
        $new_roles = ( isset( $new_option_values['roles_to_access_all_emails'] ) ? $new_option_values['roles_to_access_all_emails'] : array() );
        if ( is_array( $new_roles ) && !empty($new_roles) ) {
            foreach ( $new_roles as $new_role ) {
                wp_roles()->add_cap( $new_role, $this->get_cap_to_manage_all_emails(), true );
            }
        }
    }
    
    public function help_tabs()
    {
        $overview = new Screen_Help_Tab();
        $overview->set_id( 'emtr_email_list_help_overview' );
        $overview->set_title( __( 'Overview', 'email-tracker' ) );
        $overview->set_content( '<p>' . esc_html__( 'This screen provides access to all of sent emails. You can see emails read log in Read Log column.', 'email-tracker' ) . '</p>' );
        $available_actions = new Screen_Help_Tab();
        $available_actions->set_id( 'emtr_email_list_help_available_actions' );
        $available_actions->set_title( __( 'Available Actions', 'email-tracker' ) );
        $available_actions->set_content( '<p>' . __( 'Hovering over a row in the posts list will display action links that allow you to manage your post. You can perform the following actions:', 'email-tracker' ) . '</p>' . '<ul>' . '<li><strong>' . __( 'View', 'email-tracker' ) . '</strong> ' . __( 'will show you all email details.', 'email-tracker' ) . '</li>' . '<li><strong>' . __( 'Delete', 'email-tracker' ) . '</strong> ' . __( 'will permanently delete email.', 'email-tracker' ) . '</li>' . '<ul>' );
        $bulk_actions = new Screen_Help_Tab();
        $bulk_actions->set_id( 'emtr_email_list_help_bulk_actions' );
        $bulk_actions->set_title( __( 'Bulk Actions', 'email-tracker' ) );
        $bulk_actions->set_content( '<p>' . __( 'You can also delete multiple emails at once. Select the emails you want to act on using the checkboxes, then select the action you want to take from the Bulk Actions menu and click Apply.', 'email-tracker' ) . '</p>' );
        $screen_help_tab_hooker = new Screen_Help_Tab_Hooker( $this->get_hook_suffix(), array( $overview, $available_actions, $bulk_actions ) );
        $screen_help_tab_hooker->hook();
    }
    
    public function help_options()
    {
        $per_page_screen_option = new Screen_Option();
        $per_page_screen_option->set_id( 'per_page' );
        $per_page_screen_option->set_label( __( 'Emails per page', 'email-tracker' ) );
        $per_page_screen_option->set_default( 50 );
        $per_page_screen_option->set_option( 'emtr_emails_per_page' );
        $per_page_screen_option_hooker = new Screen_Option_Hooker( $this->get_hook_suffix(), $per_page_screen_option );
        $per_page_screen_option_hooker->hook();
    }
    
    /**
     * Enqueue notes scripts
     */
    public function admin_enqueue_scripts( $hook_suffix )
    {
        // bail if email tracker admin page doesn't
        if ( $this->get_hook_suffix() !== $hook_suffix ) {
            return;
        }
        $asset_path = plugin_dir_path( EMTR_FILE ) . 'assets/build/';
        $asset_url = plugin_dir_url( EMTR_FILE ) . 'assets/build/';
        $asset_file = (include $asset_path . 'index.asset.php');
        wp_enqueue_script(
            'emtr-email-list',
            $asset_url . 'index.js',
            $asset_file['dependencies'],
            $asset_file['version'],
            true
        );
        wp_localize_script( 'emtr-email-list', 'email_tracker', array(
            'content_url' => content_url(),
        ) );
        wp_enqueue_style( 'wp-components' );
        if ( file_exists( $asset_path . 'index.css' ) ) {
            wp_enqueue_style(
                'emtr-email-list',
                $asset_url . 'index.css',
                array(),
                ( defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : '1.0.1' ),
                'all'
            );
        }
        do_action( 'email-tracker/admin_enqueue_scripts' );
    }
    
    public function rest_api_init()
    {
        // email-tracker/v1/email/5
        register_rest_route( 'email-tracker/v1', 'email/(?P<id>[\\d]+)', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_email_for_view' ),
            'args'                => array(
            'id' => array(
            'validate_callback' => array( $this, 'rest_validate_email_id_request' ),
        ),
        ),
            'permission_callback' => array( $this, 'rest_permission_check' ),
        ) );
    }
    
    public function rest_validate_email_id_request( $param, $request, $key )
    {
        return is_numeric( $param );
    }
    
    public function rest_permission_check()
    {
        return current_user_can( $this->get_cap_to_manage_all_emails() );
    }
    
    public function get_email_for_view( $request )
    {
        global  $wpdb ;
        $ret_data = array();
        $email_id = $request['id'];
        $sql = $wpdb->prepare( 'SELECT `to`, subject, message, message_plain, headers, attachments, date_time, ' . '(SELECT count( trkemail_id	) FROM ' . \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'track_email_open_log' ) . ' WHERE trkemail_email_id = em.email_id) AS total_read_count, ' . '(SELECT count( trklinkclick_id	) FROM ' . \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'track_email_link_click_log' ) . ' WHERE trklinkclick_email_id = em.email_id ) AS total_link_click_count ' . 'FROM ' . \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'email' ) . ' AS em WHERE email_id=%d', $email_id );
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
            'total_link_click_count'
        );
        foreach ( $pass_key_arr as $field ) {
            $ret_data[$field] = $email_row[$field];
        }
        $ret_data['read_log'] = array();
        $sql = $wpdb->prepare( 'SELECT trkemail_id as read_id, trkemail_date_time as date_time, trkemail_ip_address as ip_address FROM ' . \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'track_email_open_log' ) . ' EOD WHERE EOD.trkemail_email_id = %d ORDER BY EOD.trkemail_date_time DESC', $email_id );
        $email_open_res = $wpdb->get_results( $sql, ARRAY_A );
        foreach ( $email_open_res as $row ) {
            $ret_data['read_log'][] = array(
                'read_id'    => $row['read_id'],
                'date_time'  => $row['date_time'],
                'ip_address' => $row['ip_address'],
            );
        }
        $ret_data['link_click_log'] = array();
        $email_link_res = 'SELECT trkemail_date_time as date_time, trkemail_ip_address as ip_address FROM ' . \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'track_email_open_log' ) . ' EOD WHERE EOD.trkemail_email_id = E.email_id ORDER BY EOD.trkemail_date_time DESC';
        $sql = $GLOBALS['wpdb']->prepare( 'SELECT lm.trklink_id as link_id, lm.trklink_link as link, lc.trklinkclick_date_time as date_time, lc.trklinkclick_ip_address as ip_address ' . 'FROM ' . \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'track_email_link_master' ) . ' lm LEFT JOIN ' . \PrashantWP\Email_Tracker\Util::emtr_get_table_name( 'track_email_link_click_log' ) . ' lc ' . ' ON lm.trklink_id = lc.trklinkclick_trklink_id ' . 'WHERE lm.trklink_email_id = %d ' . 'ORDER BY lm.trklink_id ASC, lc.trklinkclick_date_time DESC;', $email_id );
        $email_link_res = $GLOBALS['wpdb']->get_results( $sql, ARRAY_A );
        foreach ( $email_link_res as $row ) {
            $ret_data['link_click_log'][] = array(
                'link_id'    => $row['link_id'],
                'link'       => $row['link'],
                'date_time'  => $row['date_time'],
                'ip_address' => $row['ip_address'],
            );
        }
        return $ret_data;
    }

}