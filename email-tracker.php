<?php

/*
Plugin Name: Email Tracker Pro
Plugin URI:  https://wordpress.org/plugins/email-tracker/
Description: Email Tracker is a WordPress plugin that lets you know if the emails you have sent have been read or not.
Version:     5.2.0
Author:      Prashant Baldha
Requires at least: 3.8
Tested up to: 5.7.2
Text Domain: email-tracker
Domain Path: /languages
Author URI:  http://prashantwp.com/
License:     GPL2
Email Tracker is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Email Tracker is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Email Tracker. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'emtr' ) ) {
    emtr()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'emtr' ) ) {
        // Create a helper function for easy SDK access.
        function emtr()
        {
            global  $emtr ;
            
            if ( !isset( $emtr ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $emtr = fs_dynamic_init( array(
                    'id'             => '1811',
                    'slug'           => 'email-tracker',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_1ed59e732da6955b547b2f0daa319',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                    'menu'           => array(
                    'slug'       => 'emtr_email_list',
                    'first-path' => 'admin.php?page=emtr_email_list',
                ),
                    'is_live'        => true,
                ) );
            }
            
            return $emtr;
        }
        
        // Init Freemius.
        emtr();
        // Signal that SDK was initiated.
        do_action( 'emtr_loaded' );
    }
    
    // ... Your plugin's main file logic ...
    define( 'EMTR_BASE_FILE_PATH', __FILE__ );
    define( 'EMTR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
    define( 'EMTR_TEXT_DOMAIN', 'email-tracker' );
    require_once EMTR_PLUGIN_PATH . 'main.php';
}
