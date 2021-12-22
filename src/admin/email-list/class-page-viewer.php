<?php
namespace PrashantWP\Email_Tracker\Admin\Email_List;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PrashantWP\Email_Tracker\Util;
use PrashantWP\Email_Tracker\Core\Contract\Viewer;


class Page_Viewer implements Viewer {

    /** *************************** RENDER Email List PAGE ********************************
     *******************************************************************************
    * This function renders the admin page and the example list table. Although it's
    * possible to call prepare_items() and display() from the constructor, there
    * are often times where you may need to include logic here between those steps,
    * so we've instead called those methods explicitly. It keeps things flexible, and
    * it's the way the list tables are used in the WordPress core.
    */
    public function view() {
        //Create an instance of our package class...
        $obj_list_table = new Table();
        //Fetch, prepare, sort, and filter our data...
        $obj_list_table->prepare_items();    
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e( 'Emails', 'email-tracker' ); ?>
                <a href="<?php esc_url( menu_page_url( 'emtr_compose_email', 1 ) );?>" class="page-title-action"><?php esc_html_e( 'Compose Email', 'email-tracker' );?></a>
            </h1>
            <?php
            Util::emtr_display_success_msg();
            Util::emtr_display_error_msg();
            ?>
            <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
            <form id="et-email-filter" method="get">
                <?php 
                $obj_list_table->search_box( esc_html__( 'Search Email', 'email-tracker' ), 'email_search' );
                $obj_list_table->display();
                wp_nonce_field( 'emtr-email-list-filter', '_wpnonce' );
                ?>
                <input type="hidden" name="page" value="emtr_email_list" />
            </form>
            <div id="emtr-email-view-modal-container">
            </div>
        </div>	
        <?php
    }

}