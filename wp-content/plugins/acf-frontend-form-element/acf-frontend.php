<?php

/**
 * Plugin Name: ACF Frontend
 * Plugin URI: https://wordpress.org/plugins/acf-frontend-form-element/
 * Description: An Advanced Custom Fields extension that allows you to easily display frontend forms with ACF fields on your site so your clients can easily edit content by themselves from the frontend.
 * Version:     3.2.12
 * Author:      Shabti Kaplan
 * Author URI:  https://kaplanwebdev.com/
 * Text Domain: acf-frontend-form-element
 * Domain Path: /languages/
 *
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'acff' ) ) {
    acff()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'acff' ) ) {
        // Create a helper function for easy SDK access.
        function acff()
        {
            global  $acff ;
            
            if ( !isset( $acff ) ) {
                if ( !defined( 'WP_FS__PRODUCT_5212_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_5212_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/includes/freemius/start.php';
                $acff = fs_dynamic_init( array(
                    'id'              => '5212',
                    'slug'            => 'acf-frontend-form-element',
                    'premium_slug'    => 'acf-frontend-form-element-pro',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_771aff8259bcf0305b376eceb7637',
                    'is_premium'      => false,
                    'premium_suffix'  => 'Pro',
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                    'has_affiliation' => 'selected',
                    'menu'            => array(
                    'slug'        => 'acff-settings',
                    'affiliation' => false,
                ),
                    'is_live'         => true,
                ) );
            }
            
            // Turn off dev mode
            $acff->dev_mode = apply_filters( 'acff_dev_mode', false );
            return $acff;
        }
        
        // Init Freemius.
        acff();
        // Signal that SDK was initiated.
        do_action( 'acf_frontend_loaded' );
    }
    
    define( 'ACFF_VERSION', '3.2.12' );
    define( 'ACFF_ASSETS_VERSION', '7.2.30' );
    define( 'ACFF_PATH', __FILE__ );
    define( 'ACFF_NAME', plugin_basename( __FILE__ ) );
    define( 'ACFF_URL', plugin_dir_url( __FILE__ ) );
    define( 'ACFF_NS', 'acf-frontend-form-element' );
    
    if ( !class_exists( 'ACF_Frontend' ) ) {
        /**
         * Main ACF Frontend Class
         *
         * The main class that initiates and runs the plugin.
         *
         * @since 1.0.0
         */
        final class ACF_Frontend
        {
            /**
             * Minimum PHP Version
             *
             * @since 1.0.0
             *
             * @var string Minimum PHP version required to run the plugin.
             */
            const  MINIMUM_PHP_VERSION = '5.2.4' ;
            /**
             * Instance
             *
             * @since 1.0.0
             *
             * @access private
             * @static
             *
             * @var ACF_Frontend The single instance of the class.
             */
            private static  $_instance = null ;
            /**
             * Instance
             *
             * Ensures only one instance of the class is loaded or can be loaded.
             *
             * @since 1.0.0
             *
             * @access public
             * @static
             *
             * @return ACF_Frontend An instance of the class.
             */
            public static function instance()
            {
                if ( is_null( self::$_instance ) ) {
                    self::$_instance = new self();
                }
                return self::$_instance;
            }
            
            /**
             * Constructor
             *
             * @since 1.0.0
             *
             * @access public
             */
            public function __construct()
            {
                add_action( 'init', [ $this, 'i18n' ] );
                add_action( 'after_setup_theme', [ $this, 'init' ] );
                acff()->add_filter(
                    'connect_message_on_update',
                    array( $this, 'custom_connect_message_on_update' ),
                    10,
                    6
                );
            }
            
            public function custom_connect_message_on_update(
                $message,
                $user_first_name,
                $plugin_title,
                $user_login,
                $site_link,
                $freemius_link
            )
            {
                return sprintf(
                    __( 'Hey %1$s' ) . ',<br>' . __( 'Welcome to %2$s! Opt in here to start receiving our essential onboarding email series, walking you through the best ways to use and benefit from our plugin. You’ll also be opting into feature update notifications and non-sensitive diagnostic tracking from our partner over at %3$s.', ACFF_NS ),
                    $user_first_name,
                    '<b>' . $plugin_title . '</b>',
                    $freemius_link
                );
            }
            
            /**
             * Load Textdomain
             *
             * Load plugin localization files.
             *
             * Fired by `init` action hook.
             *
             * @since 1.0.0
             *
             * @access public
             */
            public function i18n()
            {
                load_plugin_textdomain( 'acf-frontend-form-element', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
            }
            
            /**
             * Initialize the plugin
             *
             * Load the plugin only after ACF is loaded.
             * Checks for basic plugin requirements, if one check fail don't continue,
             * If all checks have passed load the files required to run the plugin.
             *
             * Fired by `plugins_loaded` action hook.
             *
             * @since 1.0.0
             *
             * @access public
             */
            public function init()
            {
                
                if ( !class_exists( 'ACF' ) ) {
                    add_action( 'admin_notices', [ $this, 'admin_notice_missing_acf_plugin' ] );
                    return;
                }
                
                // Check for required PHP version
                
                if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
                    add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
                    return;
                }
                
                add_action( 'admin_notices', [ $this, 'admin_notice_get_pro' ] );
                $this->acff_notice_dismissed( 'pro_trial_dismiss' );
                add_filter(
                    'plugin_row_meta',
                    [ $this, 'acff_row_meta' ],
                    10,
                    2
                );
                $this->plugin_includes();
            }
            
            public function plugin_includes()
            {
                
                if ( did_action( 'elementor/loaded' ) ) {
                    require_once __DIR__ . '/includes/elementor/module.php';
                    require_once __DIR__ . '/includes/elementor/classes/migrate_settings.php';
                }
                
                if ( class_exists( 'OxygenElement' ) ) {
                    require_once __DIR__ . '/includes/oxygen/module.php';
                }
                require_once __DIR__ . '/includes/gutenberg/module.php';
                require_once __DIR__ . '/includes/frontend/module.php';
                require_once __DIR__ . '/includes/admin/module.php';
            }
            
            /**
             * Admin notice
             *
             * Warning when the site doesn't have ACF installed or activated.
             *
             * @since 1.0.0
             *
             * @access public
             */
            public function admin_notice_missing_acf_plugin()
            {
                if ( isset( $_GET['activate'] ) ) {
                    unset( $_GET['activate'] );
                }
                $message = sprintf(
                    /* translators: 1: Plugin name 2: Advanced Custom Fields */
                    esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'acf-frontend-form-element' ),
                    '<strong>' . esc_html__( 'ACF Frontend', 'acf-frontend-form-element' ) . '</strong>',
                    '<strong>' . esc_html__( 'Advanced Custom Fields', 'acf-frontend-form-element' ) . '</strong>'
                );
                printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
            }
            
            public function admin_notice_get_pro()
            {
                if ( !is_admin() ) {
                    return;
                }
                $current_screen = get_current_screen();
                if ( !isset( $current_screen->id ) || $current_screen->id !== 'toplevel_page_acff-settings' ) {
                    return;
                }
                $user_id = get_current_user_id();
                if ( get_user_meta( $user_id, 'acff_pro_trial_dismiss' ) ) {
                    return;
                }
                $img_path = ACFF_URL . 'assets/plugin-logo.png';
                $image = '<img width="30px" src="' . $img_path . '" style="width:32px;margin-right:10px;margin-bottom: -11px;"/>';
                $user = wp_get_current_user();
                if ( in_array( 'administrator', (array) $user->roles ) ) {
                    echo  '<div class="notice notice-info " style="padding-right: 38px; position: relative;">
				  <p> ' . $image . ' Try ACF Frontend <b>Pro</b> free for 7 days! <a href="https://frontendform.com/acff-pro/" target="_blank">Check it out!</a> <a class="button button-primary" style="margin-left:20px;" href="https://frontendform.com/acff-pro/" target="_blank">Free trial!</a></p>
				<a href="' . add_query_arg( array(
                        'acff_pro_trial_dismiss' => true,
                    ), admin_url( 'admin.php?page=acff-settings' ) ) . '"><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss notice.</span></button></a>
				</div>' ;
                }
            }
            
            public function acff_notice_dismissed( $notice )
            {
                $user_id = get_current_user_id();
                if ( isset( $_GET['acff_' . $notice] ) ) {
                    add_user_meta(
                        $user_id,
                        'acff_' . $notice,
                        'true',
                        true
                    );
                }
            }
            
            /**
             * Admin notice
             *
             * Warning when the site doesn't have a minimum required PHP version.
             *
             * @since 1.0.0
             *
             * @access public
             */
            public function admin_notice_minimum_php_version()
            {
                if ( isset( $_GET['activate'] ) ) {
                    unset( $_GET['activate'] );
                }
                $message = sprintf(
                    /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
                    esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'acf-frontend-form-element' ),
                    '<strong>' . esc_html__( 'ACF Frontend', 'acf-frontend-form-element' ) . '</strong>',
                    '<strong>' . esc_html__( 'PHP', 'acf-frontend-form-element' ) . '</strong>',
                    self::MINIMUM_PHP_VERSION
                );
                printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
            }
            
            public function acff_row_meta( $links, $file )
            {
                
                if ( ACFF_NAME == $file ) {
                    $row_meta = array(
                        'video' => '<a href="' . esc_url( 'https://www.youtube.com/channel/UC8ykyD--K6pJmGmFcYsaD-w/playlists' ) . '" target="_blank" aria-label="' . esc_attr__( 'Video Tutorials', 'acf-frontend-form-element' ) . '" >' . esc_html__( 'Video Tutorials', 'acf-frontend-form-element' ) . '</a>',
                    );
                    return array_merge( $links, $row_meta );
                }
                
                return (array) $links;
            }
        
        }
        ACF_Frontend::instance();
    }

}
