<?php
/**
 * Provides almost all function which required by plugin
 *
 * @package email-read-tracker
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function emtr_get_table_name($var) {
	global $wpdb;
	return $wpdb->prefix.'emtr_'.$var; 
}

function emtr_get_view_link( $email_id ) {
	return add_query_arg( array(
					'emtr_email_id' => 	$email_id ,
					'emtr_action'   => 	'view',
					'TB_iframe'     => 	'true',
					'width'         => 	600,
					'height' 		=> 	550,
					
				), home_url() );
	
}

function emtr_url_to_link($text) {
	 // The Regular Expression filter
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        // The Text you want to filter for urls

        // Check if there is a url in the text
        if(preg_match_all($reg_exUrl, $text, $url)) {
               // make the urls hyper links
               $matches = array_unique($url[0]);
               foreach($matches as $match) {
                    $replacement = "<a href=".$match.">{$match}</a>";
                    $text = str_replace($match,$replacement,$text);
               }
               return ($text);
        } else {

               // if no urls in the text just return the text
               return ($text);

        } 
}

function emtr_extract_attachments( $attachments ) {
	$attachments = is_array( $attachments ) ? $attachments : array( $attachments );
	$attachment_urls = array();
	$uploads = wp_upload_dir();
	$basename = basename( $uploads['baseurl'] );
	$basename_needle = '/'.$basename.'/';
	foreach( $attachments as $attachment ) {
		$append_url = substr( $attachment, strrpos( $attachment, $basename_needle ) );
		$attachment_urls[] = $append_url;
	}
	return implode( ',\n', $attachment_urls );
}
function emtr_relative_time($timestamp) {
	if ($timestamp != '' && ! is_int($timestamp)) {
		$timestamp = strtotime($timestamp);
	}
	if (! is_int($timestamp)) {
		return "never";
	}
	$difference = strtotime(get_date_from_gmt( gmdate('Y-m-d H:i:s') ))- $timestamp;
	$periods = array('moment', 'min', 'hour', 'day', 'week', 'month', 'year', 'decade');
	$lengths = array('60', '60', '24', '7', '4.35', '12', '10', '10');
	if ($difference >= 0) {
		// This was in the past
		$ending = "ago";
	} else {
		// This is in the future
		$difference = -$difference;
		$ending = "to go";
	}
	for ($j = 0; $difference >= $lengths[$j]; $j++) {
		$difference /= $lengths[$j];
	}
	$difference = round($difference);
	if ($difference != 1) {
		$periods[$j] .= "s";
	}
	if ($difference < 60 && $j == 0) {
		return " ({$periods[$j]} {$ending})";
	}
	return " ({$difference} {$periods[$j]} {$ending})";
}
function emtr_set_success_msg( $msg ) {
	//$_SESSION["success_msg"][] = $msg;
	set_transient( 'emtr_success_msg', $msg, 60 );	
}
function emtr_set_error_msg( $msg ) {
	//$_SESSION["error_msg"][] = $msg;
	set_transient( 'emtr_error_msg', $msg, 60 );
}
	
function emtr_display_error_msg() {
	$emtr_error_msg =  get_transient( 'emtr_error_msg' );
	if( $emtr_error_msg !== false ) {
		echo '<div class="notice notice-error"><p>'.$emtr_error_msg.'</p></div>';
		delete_transient( 'emtr_error_msg' );
	}
}
function emtr_display_success_msg() {
	$emtr_success_msg =  get_transient( 'emtr_success_msg' );
	if( $emtr_success_msg !== false ) {
		echo '<div class="notice notice-success is-dismissible"><p>'.$emtr_success_msg.'</p></div>';
		delete_transient( 'emtr_success_msg' );
	}	
}
/**
 * Tries to convet the given HTML into a plain text format - best suited for
 * e-mail display, etc.
 *
 * <p>In particular, it tries to maintain the following features:
 * <ul>
 *   <li>Links are maintained, with the 'href' copied over
 *   <li>Information in the &lt;head&gt; is lost
 * </ul>
 *
 * @param string html the input HTML
 * @return string the HTML conveted, as best as possible, to text
 * @throws Html2TextException if the HTML could not be loaded as a {@link DOMDocument}
 */
function emtr_convet_html_to_text($html) {
	$html = emtr_fix_newlines($html);

	$doc = new DOMDocument();
	if (! @$doc->loadHTML($html) )
		return '';
		//throw new Html2TextException("Could not load HTML - badly formed?", $html);

	$output = iterate_over_node($doc);

	// remove leading and trailing spaces on each line
	$output = preg_replace("/[ \t]*\n[ \t]*/im", "\n", $output);

	// remove leading and trailing whitespace
	$output = trim($output);

	return $output;
}

/**
 * Unify newlines; in particular, \r\n becomes \n, and
 * then \r becomes \n. This means that all newlines (Unix, Windows, Mac)
 * all become \ns.
 *
 * @param string text text with any number of \r, \r\n and \n combinations
 * @return string the fixed text
 */
function emtr_fix_newlines($text) {
	// replace \r\n to \n
	$text = str_replace("\r\n", "\n", $text);
	// remove \rs
	$text = str_replace("\r", "\n", $text);

	return $text;
}

function next_child_name($node) {
	// get the next child
	$nextNode = $node->nextSibling;
	while ($nextNode != null) {
		if ($nextNode instanceof DOMElement) {
			break;
		}
		$nextNode = $nextNode->nextSibling;
	}
	$nextName = null;
	if ($nextNode instanceof DOMElement && $nextNode != null) {
		$nextName = strtolower($nextNode->nodeName);
	}

	return $nextName;
}
function prev_child_name($node) {
	// get the previous child
	$nextNode = $node->previousSibling;
	while ($nextNode != null) {
		if ($nextNode instanceof DOMElement) {
			break;
		}
		$nextNode = $nextNode->previousSibling;
	}
	$nextName = null;
	if ($nextNode instanceof DOMElement && $nextNode != null) {
		$nextName = strtolower($nextNode->nodeName);
	}

	return $nextName;
}

function iterate_over_node($node) {
	if ($node instanceof DOMText) {
		return preg_replace("/[\\t\\n\\v\\f\\r ]+/im", " ", $node->wholeText);
	}
	if ($node instanceof DOMDocumentType) {
		// ignore
		return "";
	}

	$nextName = next_child_name($node);
	$prevName = prev_child_name($node);

	$name = strtolower($node->nodeName);

	// start whitespace
	switch ($name) {
		case "hr":
			return "------\n";

		case "style":
		case "head":
		case "title":
		case "meta":
		case "script":
			// ignore these tags
			return "";

		case "h1":
		case "h2":
		case "h3":
		case "h4":
		case "h5":
		case "h6":
			// add two newlines
			$output = "\n";
			break;

		#Prashant Monday, September 22, 2014
		//because tr should replace with \n
		case "tr":
		case "p":
		case "div":
			// add one line
			$output = "\n";
			break;

		default:
			// print out contents of unknown tags
			$output = "";
			break;
	}

	// debug
	//$output .= "[$name,$nextName]";

	if (isset($node->childNodes)) {
		for ($i = 0; $i < $node->childNodes->length; $i++) {
			$n = $node->childNodes->item($i);

			$text = iterate_over_node($n);

			$output .= $text;
		}
	}

	// end whitespace
	switch ($name) {
		case "style":
		case "head":
		case "title":
		case "meta":
		case "script":
			// ignore these tags
			return "";

		case "h1":
		case "h2":
		case "h3":
		case "h4":
		case "h5":
		case "h6":
			$output .= "\n";
			break;

		case "p":
		case "br":
			// add one line
			if ($nextName != "div")
				$output .= "\n";
			break;

		case "div":
			// add one line only if the next child isn't a div
			if ($nextName != "div" && $nextName != null)
				$output .= "\n";
			break;

		case "a":
			// links are returned in [text](link) format
			$href = $node->getAttribute("href");
			if ($href == null) {
				// it doesn't link anywhere
				if ($node->getAttribute("name") != null) {
					$output = "[$output]";
				}
			} else {
				if ($href == $output || $href == "mailto:$output" || $href == "http://$output" || $href == "https://$output") {
					// link to the same address: just use link
					$output;
				} else {
					// replace it
					$output = "[$output]($href)";
				}
			}

			// does the next node require additional whitespace?
			switch ($nextName) {
				case "h1": case "h2": case "h3": case "h4": case "h5": case "h6":
					$output .= "\n";
					break;
			}

		default:
			// do nothing
	}
	return $output;
}

class Html2TextException extends Exception {
	var $more_info;

	public function __construct($message = "", $more_info = "") {
		parent::__construct($message);
		$this->more_info = $more_info;
	}
}