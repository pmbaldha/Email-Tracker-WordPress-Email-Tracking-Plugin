<?php

/**
 * To track email when email will be read
 *
 * @package email-read-tracker
 * @subpackage track-email
 */
/*
 * To access EMTR_Model function to call model object
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
require_once __DIR__ . '/libs/SingletonFactory.php';
$Action = get_query_var( 'action', '' );
$PK = get_query_var( 'pk', '' );
/*
 * Display email track image
 */
# If email has been opened

if ( $Action == 'o' ) {
    # Add Email Open Log
    $POST['trkemail_email_id'] = $PK;
    EMTR_Model::TrackEmail()->insert_email_open_log( $POST );
    # Send image to browser
    $filename = __DIR__ . '/images/tack-log.png';
    $s_code = file_get_contents( $filename );
    header( "Content-Type: image/png" );
    header( "Pragma-directive: no-cache" );
    header( "Cache-directive: no-cache" );
    header( "Cache-control: no-cache" );
    header( "Pragma: no-cache" );
    header( "Expires: 0" );
    header( 'Content-Length: ' . strlen( $s_code ) );
    echo  $s_code ;
    die;
} else {
    
    if ( $Action == 'l' ) {
        $link = EMTR_Model::TrackEmail()->get_link( $PK );
        
        if ( $link != false ) {
            $link = html_entity_decode( $link );
            # Redirect to main link
            header( "location: {$link}" );
            die;
        }
    
    }

}
