<?php
/**
 * To list of all emails
 */
namespace PrashantWP\Email_Tracker\Admin\Email_List;
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PrashantWP\Email_Tracker\Util;

if ( ! class_exists('\WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 * Our theme for this list table is going to be movies.
 */
class Table extends \WP_List_Table {
    

	protected $table_name;
	protected $open_log_table_name;
	protected $key = 'email_id';
	protected $key_field = 'to';
	
	protected $content_path;

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
		global $wpdb;
                
		$this->table_name = Util::emtr_get_table_name( 'email' );
		$this->open_log_table_name = Util::emtr_get_table_name( 'track_email_open_log' );

		$this->content_path = WP_CONTENT_URL;
		
        //Set parent defaults
        parent::__construct( array(
            'singular'  => __('email', 'email-tracker'),     //singular name of the listed records
            'plural'    => __('emails', 'email-tracker'),    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

    private function get_view_link( $item, $link_label ) {
        return '<a href="#" onClick="return EMTRLoadView( ' . esc_js( $item[$this->key] ) . ', \'' . esc_js( $item['subject'] ) . '\', \'' . esc_js( $item[$this->key_field] ) . '\')">'. esc_html( $link_label ) . '</a>';
    }

    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'subject':
				return self::get_view_link( $item, $item['subject'] );
			case 'view_count':
				$str = '<b>'.sprintf( _n( '%s time', '%s times', $item[$column_name] ), $item[$column_name] ) . __( ' read', 'email-tracker' ) . '</b>';
				if( $item[$column_name] == 0 ) {
					$str .= '<div alt="f147" class="dashicons dashicons-no-alt"></div>';
				}
				else {
					$str .= '<div alt="f147" class="dashicons dashicons-yes"></div>';
				}
				$str .= '<br/>';
				if( !empty($item['view_date_time']) ) {
					$arr_view_date_time = explode(',', $item['view_date_time']);
					rsort( $arr_view_date_time );
					$str .= __( 'Last read on ', 'email-tracker' ). get_date_from_gmt( $arr_view_date_time[0], 'F j, Y g:i A' ).'&nbsp;' . Util::emtr_relative_time(get_date_from_gmt( $arr_view_date_time[0] ) );
				
				}
				return $str;	
				
				
			case 'click_count':
				if ( emtr()->is__premium_only() ) {
        			if ( emtr()->is_plan( 'pro', true ) ||  emtr()->is_trial() ) {
						$str = '<b>'.sprintf( _n( '%s time', '%s times', $item[$column_name] ), $item[$column_name] ).__(' clicked', 'email-tracker').'</b>';
						if( $item[$column_name] == 0 ) {
							$str .= '<div alt="f147" class="dashicons dashicons-no-alt"></div>';
						}
						else {
							$str .= '<div alt="f147" class="dashicons dashicons-yes"></div>';
						}
						$str .= '<br/>';
						if( !empty($item['click_date_time']) ) {
							$arr_click_date_time = explode(',',$item['click_date_time']);
							rsort( $arr_click_date_time );
							$str .= __('Last click on ', 'email-tracker'). get_date_from_gmt($arr_click_date_time[0], 'F j, Y g:i A').'&nbsp;'.Util::emtr_relative_time(get_date_from_gmt( $arr_click_date_time[0] ));
						
						}
						return $str;
					}
				}
				return '<strong>' . sprintf( __( 'To Track Email Links, Please %sUpgrade Now!%s', 'email-tracker'), '<a href="' . emtr()->get_upgrade_url() . '">', '</a>' ) . '<strong>';
            case 'date_time':
				return get_date_from_gmt( $item[$column_name] ,'F j, Y g:i A' ) . Util::emtr_relative_time( get_date_from_gmt( $item[$column_name] ) );
				
			case 'headers':
				return nl2br( $item[$column_name] );
			case 'attachments':
				if( empty($item[$column_name]) )
					return $item[$column_name];
				$arr_attachments = explode( ',\n', $item[$column_name] );
				$str_attach = '';
				foreach( $arr_attachments as $key=>$attach ) {
					$str_attach .= '<a href="'. esc_url( $this->content_path.$attach ).'" title="'. esc_attr( $this->content_path . $attach ) .'" alt="'. esc_attr( $this->content_path . $attach ) .'" class="dashicons dashicons-paperclip" target="_blank">'.'</a>';
					if( $key != count($arr_attachments) - 1 ) {
						//$str_attach .= ',<br/>';
					}
				}
				return $str_attach;
				
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_to($item){
        $delete_url = wp_nonce_url( sprintf( '?page=emtr_email_list&action=%s&email=%s', 'delete', intval( $item[$this->key] ) ), 'emtr-email-list-delete-'.$item[$this->key], '_wpnonce' );
        $actions = array(
            'edit'        => self::get_view_link( $item, __( 'View', 'email-tracker' ) ),
            'delete'    => '<a href="' . $delete_url . '" class="delete">Delete</a>',
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item[$this->key_field],
            /*$2%s*/ $item[$this->key],
            /*$3%s*/ $this->row_actions($actions)
        );
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurposed the table's singular label ("movie")
            /*$2%s*/ $item[$this->key]                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'          => '<input type="checkbox" />', //Render a checkbox instead of text
            'to'          => esc_html__( 'To', 'email-tracker' ),
            'subject'     => esc_html__( 'Subject', 'email-tracker' ),
			'date_time'   => esc_html__( 'Date', 'email-tracker' ),
			'view_count'  => esc_html__( 'Read Log', 'email-tracker' ),
			'click_count' => esc_html__( 'Click Log', 'email-tracker' ),
			
            //'headers'  => 'Headers',
			//'attachments'  => 'Attachments'
        );
        return $columns;
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'to'     => array('to',false),     //true means it's already sorted
            'subject'    => array('subject',false),
            'date_time'  => array('date_time',true),
			'view_count'  => array('view_count',false),
			'click_count'  => array('click_count',false)
        );
        return $sortable_columns;
    }


    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => esc_html__( 'Delete' , 'email-tracker' ),
        );
        return $actions;
    }


    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
			// Bulk Delete
            if ( isset( $_GET['action2'] ) && 'delete' == $_GET['action2'] ) {
                if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'],  'emtr-email-list-filter') ) {
                    die( 'Security issue1' );
                }
            } else { // Single Email Delete action
				$email_id = intval( $_GET['email'] );
                if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'emtr-email-list-delete-' . $email_id ) ) {
                    die( 'Security issue2' );
                }
            }

			global $wpdb;

            if ( is_array( $_GET['email'] ) ) {
                $arr_email_id = array_map( 'sanitize_text_field', $_GET['email'] );
            } else {
			    $arr_email_id = (array) sanitize_text_field( $_GET['email'] );
            }

            $arr_email_id = array_map( 'intval', $arr_email_id );
            $arr_email_id = array_filter( $arr_email_id  );
			$arr_email_id = array_map( 'absint', $arr_email_id );
			if ( count( $arr_email_id ) > 0 ) {
				Util::delete_emails( $arr_email_id );

				if ( count($arr_email_id) == 1 ) {
					Util::emtr_set_success_msg( __( 'Email has been deleted successfully.', 'email-tracker' ) );
				} else {
					Util::emtr_set_success_msg( __( 'Emails have been deleted successfully.', 'email-tracker' ) );
				}
			} else {
				Util::emtr_set_error_msg( __( 'Please select at least one email to delete.', 'email-tracker' ) );
			}

			if ( wp_get_referer() ) {
				wp_safe_redirect( wp_get_referer() );
				exit;
			}
			
            //wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
    }

    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
		 // get the current user ID
		$user = get_current_user_id();
		// get the current admin screen
		$screen = get_current_screen();

		// retrieve the "per_page" option
		$screen_option = $screen->get_option( 'per_page', 'option' );

		// retrieve the value of the option stored for the current user
		$per_page = get_user_meta($user, $screen_option, true);
		if ( empty ( $per_page) || $per_page < 1 || is_array( $per_page ) ) {
			// get the default value if none is set
			 $per_page = 50;
		}

        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
		
        
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'date_time'; //If no sort, default to title
        if( $orderby == 'view_count'  || $orderby == 'click_count')  {
            $orderby = $orderby ;
        }
        else {
            $orderby = 'E.'.$orderby ;
        }

        $order = 'DESC';
        if ( ! empty( $_REQUEST['order'] ) ) {
            $order_input = sanitize_text_field( $_REQUEST['order'] );
            if ( ! in_array( $order_input, array( 'ASC', 'DESC' ) ) ) {
                $order = $order_input;
            }
        }

        /***********************************************************************
         * ---------------------------------------------------------------------
         * 
         * In a real-world situation, this is where you would place your query.
         *
         * For information on making queries in WordPress, see this Codex entry:
         * http://codex.wordpress.org/Class_Reference/wpdb
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/

        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
		$wh = ' ';
		
		//For search
		if( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'emtr-email-list-filter' ) ) {
                die( 'Security issue' );
            }
            
            $search_text = sanitize_text_field( $_REQUEST['s'] );
			
			$wh_arr = array();
         	$arr_search_field = array( 'to', 'subject', 'message_plain', 'headers', 'attachments' );
			foreach ( $arr_search_field as $field ) {
				$wh_arr[] = $wpdb->prepare( ' E.' . $field . ' LIKE %s', '%' . $wpdb->esc_like( $search_text ) . '%' );
			}
			$wh = ' AND (' . implode( ' OR ' , $wh_arr). ')';
		}
		 
		 
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
		//$total_items = count($data);
		$total_items = $wpdb->get_var( 'SELECT count(*) FROM '.$this->table_name.' E WHERE 1 ' . $wh );
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        //$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $sql = 'SELECT E.*,
							( SELECT count(*) FROM '.$this->open_log_table_name.
                                ' EOC WHERE EOC.trkemail_email_id = E.email_id) AS view_count,
							( SELECT GROUP_CONCAT( trkemail_date_time ) FROM '.$this->open_log_table_name .
                                ' EOD WHERE EOD.trkemail_email_id = E.email_id ORDER BY EOD.trkemail_date_time DESC) AS view_date_time,
							( SELECT count(*) FROM ' . Util::emtr_get_table_name( 'track_email_link_click_log' ) .
                                ' ECL WHERE ECL.trklinkclick_email_id = E.email_id) AS click_count,	
							( SELECT GROUP_CONCAT( trklinkclick_date_time ) FROM ' . Util::emtr_get_table_name( 'track_email_link_click_log' ) . 					  							' ECLT WHERE ECLT.trklinkclick_email_id = E.email_id ORDER BY ECLT.trklinkclick_date_time DESC) AS click_date_time
						 FROM '.$this->table_name.' E WHERE 1 '.$wh.'ORDER BY '.$orderby.' '.$order.' LIMIT '.$per_page.' OFFSET '.(($current_page-1)*$per_page);
        
        $this->items = $wpdb->get_results( $sql, ARRAY_A);
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil( $total_items / $per_page )   //WE have to calculate the total number of pages
        ) );
    }
}