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
class Setup
{
    private  $menu_slug ;
    private  $hook_suffix ;
    public function __construct()
    {
        $this->menu_slug = 'emtr_email_list';
    }
    
    public function init()
    {
        $this->menu_page();
        $this->help_tabs();
        $this->help_options();
        add_action(
            'admin_enqueue_scripts',
            array( $this, 'admin_enqueue_scripts' ),
            999,
            1
        );
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
        $email_list_menu_page->set_page_title( __( 'Email List', 'email-tracker' ) );
        $email_list_menu_page->set_menu_title( __( 'Email Tracker', 'email-tracker' ) );
        $email_list_menu_page->set_capability( 'administrator' );
        // Make change here cause to change in freemius lib call function too.
        $email_list_menu_page->set_menu_slug( $this->get_menu_slug() );
        $email_list_menu_page->set_icon_url( 'dashicons-email-alt' );
        $menu_page_hooker = new Menu_Page_Hooker( $email_list_menu_page );
        $menu_page_hooker->hook();
        // $this->hook_suffix = $menu_page_hooker->get_hook_suffix();
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
        $available_actions->set_content( '<p>' . esc_html__( 'Hovering over a row in the posts list will display action links that allow you to manage your post. You can perform the following actions:', 'email-tracker' ) . '</p>' . '<ul>' . '<li><strong>' . __( 'View', 'email-tracker' ) . '</strong> ' . __( 'will show you all email details.', 'email-tracker' ) . '</li>' . '<li><strong>' . __( 'Delete', 'email-tracker' ) . '</strong> ' . __( 'will permanently delete email.', 'email-tracker' ) . '</li>' . '<ul>' );
        $bulk_actions = new Screen_Help_Tab();
        $bulk_actions->set_id( 'emtr_email_list_help_bulk_actions' );
        $bulk_actions->set_title( __( 'Bulk Actions', 'email-tracker' ) );
        $bulk_actions->set_content( '<p>' . esc_html__( 'You can also delete multiple emails at once. Select the emails you want to act on using the checkboxes, then select the action you want to take from the Bulk Actions menu and click Apply.', 'email-tracker' ) . '</p>' );
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
        // bail if easy-notes admin page doesn't
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

}