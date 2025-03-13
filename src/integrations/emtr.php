<?php

function emtr() {
    global $emtr;
    if ( !isset( $emtr ) ) {
        // Include Freemius SDK.
        require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/libs/freemius/start.php';
        $emtr = fs_dynamic_init( array(
            'id'             => '1811',
            'slug'           => 'email-tracker',
            'type'           => 'plugin',
            'public_key'     => 'pk_1ed59e732da6955b547b2f0daa319',
            'is_premium'     => true,
            'premium_suffix' => 'Pro',
            'has_addons'     => false,
            'has_paid_plans' => true,
            'trial'          => array(
                'days'               => 30,
                'is_require_payment' => true,
            ),
            'menu'           => array(
                'slug'       => 'emtr_email_list',
                'first-path' => 'admin.php?page=emtr_email_list',
            ),
            'is_live'        => true,
        ) );
    }
    return $emtr;
}
