<?php
namespace PrashantWP\Email_Tracker\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PrashantWP\Email_Tracker\Core\Admin\Sub_Menu_Page;
use PrashantWP\Email_Tracker\Core\Admin\Sub_Menu_Page_Hooker;

final class Setup extends \PrashantWP\Email_Tracker\Base {

    private $menu_slug;
    private $hook_suffix;

    public function __construct() {
        $this->menu_slug = 'email-tracker-settings';
        parent::__construct();
    }

    public function init() {
        $this->menu_page();
        add_action('admin_init', array($this, 'admin_init') );
    }

    public function get_menu_slug() {
        return $this->menu_slug;
    }

    public function get_hook_suffix() {
        return $this->get_menu_slug();
    }

    public function menu_page() {
        $settings_sub_menu_page = new Sub_Menu_Page( new Page_Viewer( $this->get_menu_slug() ) );

        $settings_sub_menu_page->set_parent_slug( 
            $this->factory->get( '\PrashantWP\Email_Tracker\Admin\Email_List\Setup' )
                                ->get_menu_slug()
        );
        $settings_sub_menu_page->set_page_title( __( 'Settings', 'email-tracker' ) );
        $settings_sub_menu_page->set_menu_title( __( 'Settings', 'email-tracker' ) );

        $settings_sub_menu_page->set_capability( 'manage_options' );
        $settings_sub_menu_page->set_menu_slug( $this->get_menu_slug() );
        $settings_sub_menu_page->set_pos( 2 );

        $sub_menu_page_hooker = new Sub_Menu_Page_Hooker( $settings_sub_menu_page );
        $sub_menu_page_hooker->hook();
    }

    public function admin_init() {
        $this->register_setting();
        $this->add_settings_section();
        $this->add_settings_fields();
    }

    public function register_setting() {
        register_setting(
            'email-tracker-settings',
            'email-tracker-settings',
            array(
                'type' => 'array',
                'description' => __( 'Email Tracker Settings', 'email-tracker' ),
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
            )
        );
    }

    public function sanitize_settings( $data ) {
        $clean_data = array();

        $allowed_roles_to_access_all_emails = array_keys( $this->get_roles_to_access_all_emails() );

        if ( isset( $data['roles_to_access_all_emails'] ) && ! empty( $data['roles_to_access_all_emails'] ) ) {
            $is_invalid_role = false;
            foreach ( $data['roles_to_access_all_emails'] as $role ) {
                if ( in_array( $role, $allowed_roles_to_access_all_emails ) ) {
                    $clean_data['roles_to_access_all_emails'][] = $role;
                } else {
                    $is_invalid_role = true;
                }
            }
            if ( true === $is_invalid_role ) {
                add_settings_error( $this->get_menu_slug(), 'invalid-role', __('Invalid role passed to save!', 'email-tracker'), 'error' );
            }
        }

        if ( isset( $data['delete_emails_after_days'] ) && ! empty( $data['delete_emails_after_days'] ) ) {
	        if ( ! is_numeric( $data['delete_emails_after_days'] ) ) {
		        add_settings_error( $this->get_menu_slug(), 'invalid-delete_emails_after_days', __('Invalid days to delete after days value!', 'email-tracker'), 'error' );
	        } else {
                $clean_data['delete_emails_after_days'] = intval( $data['delete_emails_after_days'] );
            }
        }


        return $clean_data;
    }

    public function add_settings_section() {
        add_settings_section( 'email-tracker-basic-settings', __( 'Basic Settings', 'email-tracker' ), '__return_empty_string', $this->get_menu_slug() );
    }

    public function add_settings_fields() {
        add_settings_field( 'email-tracker-roles', __( 'Roles manage all Emails', 'email-tracker'), array( $this, 'render_role_field' ), $this->get_menu_slug(), 'email-tracker-basic-settings' );
	    add_settings_field( 'email-tracker-delete-emails-after-days', __( 'Delete Emails after Days ', 'email-tracker'), array( $this, 'render_delete_emails_after_days' ), $this->get_menu_slug(), 'email-tracker-basic-settings' );
    }

    private function get_roles_to_access_all_emails() {
        $all_roles = $GLOBALS['wp_roles']->role_names;
        $remove_roles = array(
            'administrator',
            'subscriber',
            'customer',
        );
        $ret_roles = array();
        foreach ($all_roles as $role_key => $role_val) {
            if ( in_array( $role_key, $remove_roles ) ) {
                continue;
            }
            $ret_roles[$role_key] = $role_val;
        }
        return $ret_roles;
    }

    public function render_role_field() {
        $options_obj = $this->factory->get( '\PrashantWP\Email_Tracker\Options' );
        $roles_to_access_all_emails = $options_obj->get( 'roles_to_access_all_emails', array() );
    ?>
        <?php
        foreach ( $this->get_roles_to_access_all_emails() as $role_key => $role_val ) {
        ?>
            <label>
                <input type="checkbox"
                    name="email-tracker-settings[roles_to_access_all_emails][]"
                    value="<?php echo esc_attr( $role_key ); ?>"
                    <?php if ( in_array( $role_key, $roles_to_access_all_emails ) ) { echo 'checked'; } ?>
                />
                <?php echo esc_html( $role_val ); ?>
            </label>
            <br />
        <?php
        }
        ?>
        <p class="description">
            <?php _e( 'The administrator role has the right to access all emails by default.', 'email-tracker' ); ?></p>
        </p>
    <?php
    }

	public function render_delete_emails_after_days() {
		$options_obj = $this->factory->get( '\PrashantWP\Email_Tracker\Options' );
		$delete_emails_after_days = $options_obj->get( 'delete_emails_after_days', 30 );
        ?>
        <input type="number"
               name="email-tracker-settings[delete_emails_after_days]"
               value="<?php echo intval( $delete_emails_after_days ); ?>"
        />
        <br />
        <p class="description">
            <?php _e( 'After deleting emails, The related links will show the 404 not found error. Please be aware of it. Generally, no one email receiver will see the email you send.', 'email-tracker' ); ?></p>
        </p>
        <?php
    }
}