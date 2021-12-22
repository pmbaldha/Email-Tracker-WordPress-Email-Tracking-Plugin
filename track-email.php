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
    // Exit if accessed directly
}

$Action = sanitize_text_field( get_query_var( 'action', '' ) );
$PK = intval( get_query_var( 'pk', '' ) );
if ( $PK == 0 ) {
    die;
}
/*
 * Display email track image
 */
// If email has been opened

if ( $Action == 'o' ) {
    // Add Email Open Log
    $POST['trkemail_email_id'] = $PK;
    \PrashantWP\Email_Tracker\Factory::get( '\\PrashantWP\\Email_Tracker\\Model\\TrackEmail' )->insert_email_open_log( $POST );
    /*
    $filename = __DIR__ . '/images/track-log.png';
    $s_code = file_get_contents( $filename );
    */
    header( 'Content-Type: image/png' );
    header( 'Pragma-directive: no-cache' );
    header( 'Cache-directive: no-cache' );
    header( 'Cache-control: no-cache' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );
    header( 'Content-Length: ' . 921 );
    // track-log.png base64 encoded value
    echo  base64_decode( "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAFoEvQfAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NUQ4RTg0RkQxQjZBMTFFM0EyMjZEMEI1RDQxQTNEODgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NUQ4RTg0RkUxQjZBMTFFM0EyMjZEMEI1RDQxQTNEODgiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1RDhFODRGQjFCNkExMUUzQTIyNkQwQjVENDFBM0Q4OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1RDhFODRGQzFCNkExMUUzQTIyNkQwQjVENDFBM0Q4OCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmpagdgAAAANSURBVHjaY/7//z8DAAkLAwFJ9B4LAAAAAElFTkSuQmCC" ) ;
    die;
} elseif ( $Action == 'l' ) {
    $model_trackemail = \PrashantWP\Email_Tracker\Factory::get( '\\PrashantWP\\Email_Tracker\\Model\\TrackEmail' );
    $link = $model_trackemail->get_link( $PK );
    
    if ( $link != false ) {
        $link = html_entity_decode( $link );
        // Redirect to main link
        header( "location: {$link}" );
        die;
    }

}
