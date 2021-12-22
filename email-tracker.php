<?php
/**
 * Plugin Name: Email Tracker
 * Plugin URI:  https://wordpress.org/plugins/email-tracker/
 * Description: Email Tracker is a WordPress plugin that lets you know if the emails you have sent have been read or not.
 * Version:     5.2.8
 * Author:      Prashant Baldha
 * Requires at least: 4.0
 * Tested up to: 5.7.2
 * Requires PHP: 5.6.1
 * Text Domain: email-tracker
 * Domain Path: /languages
 * Author URI:  https://www.prashantwp.com/
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Email Tracker is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Email Tracker is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Email Tracker. If not, see <http://www.gnu.org/licenses/>.
 */

namespace PrashantWP\Email_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'emtr' ) ) {
	emtr()->set_basename( false, __FILE__ );
} else {
	// DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
	if ( ! function_exists( 'emtr' ) ) {
		// Create a helper function for easy SDK access.
		require_once plugin_dir_path( __FILE__ ) . 'src/integrations/emtr.php';

		// Init Freemius.
		emtr();
		// Signal that SDK was initiated.
		do_action( 'emtr_loaded' );
	}

	if ( ! defined( 'EMTR_FILE' ) ) {
		define( 'EMTR_FILE', __FILE__ );
	}

	define( 'EMTR_BASE_FILE_PATH', __FILE__ );
	define( 'EMTR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

	require_once plugin_dir_path( __FILE__ ) . 'email-tracker-main.php';
}
