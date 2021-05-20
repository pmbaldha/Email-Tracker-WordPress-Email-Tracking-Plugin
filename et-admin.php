<?php
/**
 * To display admin side pages
 *
 * @package email-read-tracker
 * @subpackage srt-admin
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
/*
 * Email list table class
 */
require_once( __DIR__.DIRECTORY_SEPARATOR.'class-et-email-list-table.php' );
/** ************************ REGISTER ADMIN PAGES ****************************
 *******************************************************************************
 * Now we just need to define an admin page. For this example, we'll add a top-level
 * menu item to the bottom of the admin menus.
 */
function emtr_add_menu_items(){
	global $emtr_email_list_page, $emtr_compose_email;
    $emtr_email_list_page = add_menu_page(__('Email Tracker', EMTR_TEXT_DOMAIN), esc_html__('Email Tracker', EMTR_TEXT_DOMAIN), 'administrator', 'emtr_email_list', 'emtr_render_email_list_page', 'dashicons-email-alt' );
	add_action("load-$emtr_email_list_page", "emtr_email_list_screen_options");
	$emtr_compose_email = add_submenu_page( 'emtr_email_list', 'Compose Email', 'Compose Email', 'administrator', 'emtr_compose_email', 'emtr_render_compose_email' );
	
	add_action("load-$emtr_compose_email", "emtr_compose_email_screen_options");
} 
add_action('admin_menu', 'emtr_add_menu_items');

function emtr_email_list_screen_options() {
	global $emtr_email_list_page;
	$screen = get_current_screen();
 
	// get out of here if we are not on our settings page
	if(!is_object($screen) || $screen->id != $emtr_email_list_page)
		return;
 
	$args = array(
		'label' => esc_html__('Emails per page', EMTR_TEXT_DOMAIN),
		'default' => 50,
		'option' => 'emtr_emails_per_page'
	);
	add_screen_option( 'per_page', $args );
	
    // Add overview help tab
    $screen->add_help_tab( array(
        'id'	=> 'emtr_email_list_help_overview',
        'title'	=> esc_html__('Overview', EMTR_TEXT_DOMAIN),
        'content'	=> '<p>' . esc_html__( 'This screen provides access to all of sent emails. You can see emails read log in Read Log column.', EMTR_TEXT_DOMAIN ) . '</p>',
    ) );
	// Add Available actions help tab
    $screen->add_help_tab( array(
        'id'	=> 'emtr_email_list_help_available_actions',
        'title'	=> esc_html__('Available Actions', EMTR_TEXT_DOMAIN),
        'content'	=> '<p>' . esc_html__( 'Hovering over a row in the posts list will display action links that allow you to manage your post. You can perform the following actions:', EMTR_TEXT_DOMAIN ) . '</p>'
					   .'<ul>'
							.'<li><strong>'.esc_html__('View', EMTR_TEXT_DOMAIN).'</strong> '.esc_html__('will show you all email details.', EMTR_TEXT_DOMAIN).'</li>'
							.'<li><strong>'.esc_html__('Delete', EMTR_TEXT_DOMAIN).'</strong> '.esc_html__('will permanently delete email.', EMTR_TEXT_DOMAIN).'</li>'							
						.'<ul>',
    ) );
	// Add Bulk actions help tab
    $screen->add_help_tab( array(
        'id'	=> 'emtr_email_list_help_bulk_actions',
        'title'	=> esc_html__('Bulk Actions'),
        'content'	=> '<p>' . esc_html__( 'You can also delete multiple emails at once. Select the emails you want to act on using the checkboxes, then select the action you want to take from the Bulk Actions menu and click Apply.', EMTR_TEXT_DOMAIN) . '</p>',
    ) );
}

function emtr_set_screen_option($status, $option, $value) {
	if ( 'emtr_emails_per_page' == $option ) return $value;
}
add_filter('set-screen-option', 'emtr_set_screen_option', 10, 3);

function emtr_compose_email_screen_options() {
	global $emtr_compose_email;
	$screen = get_current_screen();
 
	// get out of here if we are not on our settings page
	if(!is_object($screen) || $screen->id != $emtr_compose_email)
		return;
	
	// Add overview help tab
    $screen->add_help_tab( array(
        'id'	=> 'emtr_compose_email_help_overview',
        'title'	=> esc_html__('Overview', EMTR_TEXT_DOMAIN),
        'content'	=> '<p>' . esc_html__( 'This screen alows you send email with attachments. This sent mail will be tracked for view', EMTR_TEXT_DOMAIN) . '</p>',
    ) ); 
}


/** *************************** RENDER Email List PAGE ********************************
 *******************************************************************************
 * This function renders the admin page and the example list table. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */
function emtr_render_email_list_page(){    
	add_thickbox(); 	
    //Create an instance of our package class...
    $obj_list_table = new EMTR_Email_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $obj_list_table->prepare_items();    
    ?>
    <div class="wrap">
        <h1>
        	Emails           
			<a href="<?php menu_page_url( 'emtr_compose_email', 1 );?>" class="page-title-action">Compose Email</a>
        </h1>
       	<?php
        emtr_display_success_msg();
		emtr_display_error_msg();
		?>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="et-email-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php 
			$obj_list_table->search_box( esc_html__('Search Email', EMTR_TEXT_DOMAIN), 'email_search' );
			$obj_list_table->display();
			?>
        </form>        
    </div>	
	<?php
}

function emtr_render_compose_email() {
	if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "emtr_send_mail" && wp_verify_nonce( $_POST['_wpnonce'], 'emtr_compose_email' ) ) {
	
		if( isset($_POST['to']) ) 
			$to = sanitize_text_field($_POST['to']);
		if( isset($_POST['subject']) ) 
			$subject = sanitize_text_field($_POST['subject']);
		if( isset($_POST['message']) ) {
			//message may be content of html tags
			$message = wp_kses_post( $_POST['message'] );	
		}
		$arr_attachments = array();
		if( isset($_POST['attachments']) ) {
			$arr_attachments_url = explode(',',$_POST['attachments']);	
			$arr_attachments = array();
			foreach( $arr_attachments_url as $attach_url ) {
				$arr_attachments[] = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $attach_url );
			}
		}
		
		$header = '';
		if( !empty( $_POST['from'] ) && filter_var($_POST['from'], FILTER_VALIDATE_EMAIL) ) {	
			$header = 'From:' . sanitize_text_field( $_POST['from'] ) .  "\r\n";
		}
		$ret_mail = wp_mail($to, $subject, $message, $header, $arr_attachments);
		if( $ret_mail ) {
			$success = esc_html__(' Mail Sent!', EMTR_TEXT_DOMAIN); 
		} else {
			$error = esc_html__('Mail can\'t be send.  Possible reason: your host may have disabled the mail() function', EMTR_TEXT_DOMAIN);
		}
	}	?>
	<div class="wrap">
		<h1><?php esc_html_e('Compose Email', EMTR_TEXT_DOMAIN);?></h1>
		<?php
			if( isset($success) && !empty($success) ) {
				echo '<div class="notice notice-success is-dismissible"><p>'.$success.'</p></div>';
			}
			if( isset($error) && !empty($error) ) {
				echo '<div class="notice notice-error is-dismissible"><p>'.$error.'</p></div>';
			}
		?>
		<form method="post">
			<table  class="form-table">
				<tr> 
					<th scope="row">  
						<label for="from"><?php esc_html_e('From (Optional)', EMTR_TEXT_DOMAIN);?></label> 
					</th>
					<td> 
						<input type="text" id="from" name="from" value="" placeholder="<?php esc_attr_e('name@yourdomain.com (Optional)', EMTR_TEXT_DOMAIN);?>" tabindex="1" class="regular-text">
						<p class="description"><strong><?php esc_html_e( 'Make sure you are setting a from address is hosted in your domain; otherwise, Your Composed email may be considered spam. For example, You should write the from email address like john@yourdomain.com.
', EMTR_TEXT_DOMAIN);?></strong></p>
					</td>  
				</tr>
                <tr> 
					<th scope="row">  
						<label for="to"><?php esc_html_e('To', EMTR_TEXT_DOMAIN);?></label> 
					</th>
					<td> 
						<input type="text" id="to" name="to" value="" placeholder="<?php esc_attr_e('To', EMTR_TEXT_DOMAIN);?>" tabindex="2" class="regular-text" required>
					</td>  
				</tr>
				<tr> 
					<th scope="row">  
						<label for="subject"><?php esc_html_e('Subject', EMTR_TEXT_DOMAIN);?></label> 
					</th>
					<td>
						<input type="text" id="subject" name="subject" value="" placeholder="<?php esc_attr_e('Subject', EMTR_TEXT_DOMAIN);?>" tabindex="3"  class="regular-text"  required>
					</td>
				</tr>     
				<tr> 
					<th scope="row">  
						<label for="upload-button"><?php esc_html_e('Attachments', EMTR_TEXT_DOMAIN);?></label> 
					</th>
					<td>
					
						<input type="hidden" name="attachments" id="attachments" value="" class="regular-text" >
						<input id="upload-button" type="button" class="button" value="<?php esc_attr_e('Attach Files', EMTR_TEXT_DOMAIN)?>" tabindex="4" />
						<div id="attachment-container">
						</div>
					</td>
				</tr>     
				<tr> 
					<th scope="row">  
						<label for="message"><?php esc_html_e('Message', EMTR_TEXT_DOMAIN);?></label> 
					</th>
					<td>
						<?php 
						$args = array('textarea_name' => 'message', 'wpautop' => false, /*'textarea_rows' => '22',*/  'media_buttons' => true, 'tabindex' => 4,);
						wp_editor( '', 'message', $args ); 
						?>
					</td>
				</tr>
				<tr>
                	<td>
                    </td>
                    <td>
						<?php wp_nonce_field( 'emtr_compose_email' ); ?>
                    	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Send Email', EMTR_TEXT_DOMAIN);?>">
                    </td>
			</table>
			<input type="hidden" name="action" value="emtr_send_mail" />
		 </form>
	</div>
<?php
}

 /* Add the media uploader script */
function emtr_media_lib_uploader_enqueue( $hook ) {
	if( $hook == 'toplevel_page_emtr_email_list' || $hook == 'email-read-tracker_page_emtr_compose_email' ) {
		wp_enqueue_style ( 'emtr_custom_css', plugins_url( 'css/custom.css' , EMTR_BASE_FILE_PATH ), array(), '1.0', 'all' );
	}
	
	
	if( $hook == 'email-tracker_page_emtr_compose_email' ) {
		wp_enqueue_style ( 'emtr_compose_email_css', plugins_url( 'css/compose_email.css' , EMTR_BASE_FILE_PATH ), array(), '0.1', 'all' );
		wp_enqueue_media();
		wp_enqueue_script( 'media_lib_uploader_js', plugins_url( 'js/media-lib-uploader.js' , EMTR_BASE_FILE_PATH ), array('jquery'), true );
	}
}
add_action('admin_enqueue_scripts', 'emtr_media_lib_uploader_enqueue');
/* To resolve header already sent error */
function emtr_output_buffer_start() {
	if(  isset($_GET['page']) && $_GET['page'] == 'emtr_email_list'
		&& 
		( 
			( isset($_GET['action']) && $_GET['action'] == 'delete' )
			||
			( isset($_GET['action2']) && $_GET['action2'] == 'delete' )
		)
     ) {
		ob_start();
	 }
}
add_action('init', 'emtr_output_buffer_start', 1);