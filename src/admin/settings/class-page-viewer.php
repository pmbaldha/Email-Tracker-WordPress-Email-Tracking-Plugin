<?php
namespace PrashantWP\Email_Tracker\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PrashantWP\Email_Tracker\Core\Contract\Viewer;

class Page_Viewer implements Viewer {
    
    private $page_slug;

    public function __construct( $page_slug ) {
        $this->page_slug = $page_slug;
    }

    public function view() {
    ?>
        <div class="wrap" id="<?php echo esc_attr( $this->page_slug . '-admin' ); ?>">
            <h2><?php _e( 'Settings', 'email-tracker' ); ?></h2>
            <?php
            $settings_errors = get_settings_errors();
            if ( is_array( $settings_errors ) && count( $settings_errors ) > 0 && $settings_errors[0]['type'] === 'success' ) {
                $notice = $settings_errors[0];
                echo '<div id="setting-error-'. esc_attr( $notice['code'] ) . '" class="notice notice-' . esc_attr( $notice['type'] 
                 ) . ' inline is-dismissible"><p>' . esc_html( $notice['message'] ) . '</p></div>';
			}
            settings_errors( $this->page_slug );
            ?>
            <form action="options.php" method="POST">
                <?php settings_fields( $this->page_slug ); ?>
                <?php do_settings_sections( $this->page_slug ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
    }

}