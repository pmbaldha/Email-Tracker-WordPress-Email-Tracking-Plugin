<?php
/**
 * To display admin side pages
 *
 * @package email-read-tracker
 * @subpackage srt-admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/** ************************ REGISTER ADMIN PAGES ****************************
 * ******************************************************************************
 * Now we just need to define an admin page. For this example, we'll add a top-level
 * menu item to the bottom of the admin menus.
 */
function emtr_add_menu_items() {
	global $emtr_compose_email;

	$emtr_compose_email = add_submenu_page( 'emtr_email_list', __( 'Compose Email', 'email-tracker' ), __( 'Compose Email', 'email-tracker' ), 'administrator', 'emtr_compose_email', 'emtr_render_compose_email', 1 );

	add_action( "load-$emtr_compose_email", 'emtr_compose_email_screen_options' );
}
add_action( 'admin_menu', 'emtr_add_menu_items' );

function emtr_compose_email_screen_options() {
	global $emtr_compose_email;
	$screen = get_current_screen();

	// get out of here if we are not on our settings page
	if ( ! is_object( $screen ) || $screen->id != $emtr_compose_email ) {
		return;
	}

	// Add overview help tab
	$screen->add_help_tab(
		array(
			'id'      => 'emtr_compose_email_help_overview',
			'title'   => esc_html__( 'Overview', 'email-tracker' ),
			'content' => '<p>' . esc_html__( 'This screen alows you send email with attachments. This sent mail will be tracked for view', 'email-tracker' ) . '</p>',
		)
	);
}

function emtr_render_compose_email() {
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['action'] ) && $_POST['action'] == 'emtr_send_mail' && wp_verify_nonce( $_POST['_wpnonce'], 'emtr_compose_email' ) ) {

		if ( isset( $_POST['to'] ) ) {
			$to = sanitize_text_field( $_POST['to'] );
		}
		if ( isset( $_POST['subject'] ) ) {
			$subject = sanitize_text_field( $_POST['subject'] );
		}
		if ( isset( $_POST['message'] ) ) {
			// message may be content of html tags
			$message = wp_kses_post( $_POST['message'] );
		}
		$arr_attachments = array();
		if ( isset( $_POST['attachments'] ) ) {
			$arr_attachments_url = explode( ',', $_POST['attachments'] );
			$arr_attachments_url = array_map( 'sanitize_text_field', $arr_attachments_url );
			$arr_attachments     = array();
			foreach ( $arr_attachments_url as $attach_url ) {
				$arr_attachments[] = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $attach_url );
			}
		}

		$header = '';
		if ( ! empty( $_POST['from'] ) && filter_var( $_POST['from'], FILTER_VALIDATE_EMAIL ) ) {
			$header = 'From:' . sanitize_text_field( $_POST['from'] ) . "\r\n";
		}
		$ret_mail = wp_mail( $to, $subject, $message, $header, $arr_attachments );
		if ( $ret_mail ) {
			$success = esc_html__( ' Mail Sent!', 'email-tracker' );
		} else {
			$error = esc_html__( 'Mail can\'t be send.  Possible reason: your host may have disabled the mail() function', 'email-tracker' );
		}
	}   ?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Compose Email', 'email-tracker' ); ?></h1>
		<?php
		if ( isset( $success ) && ! empty( $success ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $success ) . '</p></div>';
		}
		if ( isset( $error ) && ! empty( $error ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $error ) . '</p></div>';
		}
		?>
		<form method="post">
			<table  class="form-table">
				<tr> 
					<th scope="row">  
						<label for="from"><?php esc_html_e( 'From (Optional)', 'email-tracker' ); ?></label> 
					</th>
					<td> 
						<input type="text" id="from" name="from" value="" placeholder="<?php esc_attr_e( 'name@yourdomain.com (Optional)', 'email-tracker' ); ?>" tabindex="1" class="regular-text">
						<p class="description"><strong>
						<?php
						esc_html_e(
							'Make sure you are setting a from address is hosted in your domain; otherwise, Your Composed email may be considered spam. For example, You should write the from email address like john@yourdomain.com.
',
							'email-tracker'
						);
						?>
														</strong></p>
					</td>  
				</tr>
				<tr> 
					<th scope="row">  
						<label for="to"><?php esc_html_e( 'To', 'email-tracker' ); ?></label> 
					</th>
					<td> 
						<input type="text" id="to" name="to" value="" placeholder="<?php esc_attr_e( 'To', 'email-tracker' ); ?>" tabindex="2" class="regular-text" required>
					</td>  
				</tr>
				<tr> 
					<th scope="row">  
						<label for="subject"><?php esc_html_e( 'Subject', 'email-tracker' ); ?></label> 
					</th>
					<td>
						<input type="text" id="subject" name="subject" value="" placeholder="<?php esc_attr_e( 'Subject', 'email-tracker' ); ?>" tabindex="3"  class="regular-text"  required>
					</td>
				</tr>     
				<tr> 
					<th scope="row">  
						<label for="upload-button"><?php esc_html_e( 'Attachments', 'email-tracker' ); ?></label> 
					</th>
					<td>
					
						<input type="hidden" name="attachments" id="attachments" value="" class="regular-text" >
						<input id="upload-button" type="button" class="button" value="<?php esc_attr_e( 'Attach Files', 'email-tracker' ); ?>" tabindex="4" />
						<div id="attachment-container">
						</div>
					</td>
				</tr>     
				<tr> 
					<th scope="row">  
						<label for="message"><?php esc_html_e( 'Message', 'email-tracker' ); ?></label> 
					</th>
					<td>
						<?php
						$args = array(
							'textarea_name' => 'message',
							'wpautop'       => false, /*'textarea_rows' => '22',*/
							'media_buttons' => true,
							'tabindex'      => 4,
						);
						wp_editor( '', 'message', $args );
						?>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<?php wp_nonce_field( 'emtr_compose_email' ); ?>
						<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Send Email', 'email-tracker' ); ?>">
					</td>
			</table>
			<input type="hidden" name="action" value="emtr_send_mail" />
		 </form>
	</div>
	<?php
}

 /* Add the media uploader script */
function emtr_media_lib_uploader_enqueue( $hook ) {
	if ( $hook == 'email-tracker_page_emtr_compose_email' ) {
		wp_enqueue_style( 'emtr_compose_email_css', plugins_url( 'css/compose_email.css', EMTR_BASE_FILE_PATH ), array(), '0.1', 'all' );
		wp_enqueue_media();
		wp_enqueue_script( 'media_lib_uploader_js', plugins_url( 'js/media-lib-uploader.js', EMTR_BASE_FILE_PATH ), array( 'jquery' ), true );
	}
}
add_action( 'admin_enqueue_scripts', 'emtr_media_lib_uploader_enqueue' );

/* To resolve header already sent error */
function emtr_output_buffer_start() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'emtr_email_list'
		&&
		(
			( isset( $_GET['action'] ) && $_GET['action'] == 'delete' )
			||
			( isset( $_GET['action2'] ) && $_GET['action2'] == 'delete' )
		)
	 ) {
		ob_start();
	}
}
add_action( 'init', 'emtr_output_buffer_start', 1 );
