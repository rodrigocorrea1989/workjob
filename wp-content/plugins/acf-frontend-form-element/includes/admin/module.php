<?php

namespace ACFFrontend;


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( !class_exists( 'ACFFrontend_Admin' ) ) {
    class ACFFrontend_Admin
    {
        private  $tabs = array() ;
        public function plugin_page()
        {
            global  $acff_settings ;
            $acff_settings = add_menu_page(
                'ACF Frontend',
                'ACF Frontend',
                'manage_options',
                'acff-settings',
                [ $this, 'admin_settings_page' ],
                'dashicons-feedback',
                '87.87778'
            );
            add_submenu_page(
                'acff-settings',
                __( 'Settings', 'acf-frontend-form-element' ),
                __( 'Settings', 'acf-frontend-form-element' ),
                'manage_options',
                'acff-settings',
                '',
                0
            );
        }
        
        function admin_settings_page()
        {
            global  $acff_active_tab ;
            $acff_active_tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'welcome' );
            ?>

			<h2 class="nav-tab-wrapper">
			<?php 
            do_action( 'acff_settings_tabs' );
            ?>
			</h2>
			<?php 
            do_action( 'acff_settings_content' );
        }
        
        public function add_tabs()
        {
            add_action( 'acff_settings_tabs', [ $this, 'settings_tabs' ], 1 );
            add_action( 'acff_settings_content', [ $this, 'settings_render_options_page' ] );
        }
        
        public function settings_tabs()
        {
            global  $acff_active_tab ;
            foreach ( $this->tabs as $name => $label ) {
                ?>
				<a class="nav-tab <?php 
                echo  ( $acff_active_tab == $name || '' ? 'nav-tab-active' : '' ) ;
                ?>" href="<?php 
                echo  admin_url( '?page=acff-settings&tab=' . $name ) ;
                ?>"><?php 
                _e( $label, 'acf-frontend-form-element' );
                ?> </a>
			<?php 
            }
        }
        
        public function settings_render_options_page()
        {
            global  $acff_active_tab ;
            
            if ( '' || 'welcome' == $acff_active_tab ) {
                ?>
			<style>p.acff-text{font-size:20px}</style>
			<h3><?php 
                _e( 'Hello and welcome', 'acf-frontend-form-element' );
                ?></h3>
			<p class="acff-text"><?php 
                _e( 'If this is your first time using ACF Frontend, we recommend you watch Paul Charlton from WPTuts beautifully explain how to use it.', 'acf-frontend-form-element' );
                ?></p>
			<iframe width="560" height="315" src="https://www.youtube.com/embed/iHx7krTqRN0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			<br>
			<p class="acff-text"><?php 
                _e( 'If you have any questions at all please feel welcome to email support at', 'acf-frontend-form-element' );
                ?> <a href="mailto:support@frontendform.com">support@frontendform.com</a> <?php 
                _e( 'or on whatsapp', 'acf-frontend-form-element' );
                ?> <a href="https://api.whatsapp.com/send?phone=972532323950">+972-53-232-3950</a></p>
			<?php 
            } else {
                foreach ( $this->tabs as $form_tab => $label ) {
                    
                    if ( $form_tab == $acff_active_tab ) {
                        $admin_fields = apply_filters( 'acff/' . $form_tab . '_fields', array() );
                        
                        if ( $admin_fields ) {
                            foreach ( $admin_fields as $key => $field ) {
                                $field['key'] = $key;
                                $field['name'] = $key;
                                $field['value'] = get_option( $key );
                                $field['prefix'] = 'acff[admin_options]';
                                $admin_fields[$key] = $field;
                            }
                            acff()->form_display->render_form( [
                                'admin_options'  => 1,
                                'hidden_fields'  => [
                                'admin_page' => $acff_active_tab,
                                'screen_id'  => 'options',
                            ],
                                'field_objects'  => $admin_fields,
                                'submit_value'   => __( 'Save Settings', 'acf-frontend-form-element' ),
                                'update_message' => __( 'Settings Saved', 'acf-frontend-form-element' ),
                                'redirect'       => 'custom_url',
                                'kses'           => 0,
                                'honeypot'       => 0,
                                'no_record'      => 1,
                                'custom_url'     => admin_url( '?page=acff-settings&tab=' . $_GET['tab'] ),
                            ] );
                        } else {
                            if ( $form_tab == 'payments' ) {
                                
                                if ( isset( $_POST['action'] ) && $_POST['action'] == 'acff_install_plugin' ) {
                                    $this->install_payments_addon();
                                } else {
                                    $this->addon_form();
                                }
                            
                            }
                        }
                    
                    }
                
                }
            }
        
        }
        
        public function addon_form()
        {
            $addon_slug = 'acf-frontend-payments/acf-frontend-payments.php';
            ?>
				<form class="acff-addon-form" method="post" action="">
			<?php 
            
            if ( acff_is_plugin_installed( $addon_slug ) ) {
                echo  '<input type="hidden" name="action" value="acff_activate_plugin"/>' ;
                $submit_value = 'Activate the payments addon';
            } else {
                echo  '<input type="hidden" name="action" value="acff_install_plugin"/>' ;
                $submit_value = 'Install and activate the payments addon';
            }
            
            printf( __( '<button type="submit" class="button acff-settings">%s</button>', ACFF_NS ), $submit_value );
            ?>
				<input type="hidden" name="addon" value="payments"/>
				<input type="hidden" name="nonce" value="<?php 
            echo  wp_create_nonce( 'acff-addon' ) ;
            ?>" />
				</form>
			<?php 
        }
        
        public function configs()
        {
            
            if ( !get_option( 'acff_hide_wp_dashboard' ) ) {
                add_option( 'acff_hide_wp_dashboard', true );
                add_option( 'acff_hide_by', array_map( 'strval', [
                    0 => 'user',
                ] ) );
            }
            
            require_once __DIR__ . '/admin-pages/forms/custom-fields.php';
        }
        
        public function install_payments_addon()
        {
            $args = acf_frontend_parse_args( $_POST, array(
                'nonce' => '',
                'addon' => '',
            ) );
            if ( !wp_verify_nonce( $args['nonce'], 'acff-addon' ) ) {
                echo  __( 'Nonce error', ACFF_NS ) ;
            }
            
            if ( $args['addon'] == 'payments' ) {
                $addon_slug = 'acf-frontend-payments/acf-frontend-payments.php';
                $addon_zip = 'https://stage.frontendform.com/wp-content/uploads/updater/acf-frontend-payments.zip';
                $installed = acff_install_plugin( $addon_zip );
                if ( $installed ) {
                    $this->addon_form();
                }
            }
        
        }
        
        public function activate_payments_addon()
        {
            if ( empty($_POST['action']) || $_POST['action'] != 'acff_activate_plugin' ) {
                return;
            }
            $args = acf_frontend_parse_args( $_POST, array(
                'nonce' => '',
                'addon' => '',
            ) );
            if ( !wp_verify_nonce( $args['nonce'], 'acff-addon' ) ) {
                echo  __( 'Nonce error', ACFF_NS ) ;
            }
            
            if ( $args['addon'] == 'payments' ) {
                $addon_slug = 'acf-frontend-payments/acf-frontend-payments.php';
                activate_plugin( $addon_slug );
            }
            
            wp_redirect( add_query_arg( array(
                'page' => 'acff-settings',
                'tab'  => 'payments',
            ), admin_url() ) );
        }
        
        public function settings_sections()
        {
            require_once __DIR__ . '/admin-pages/submissions/crud.php';
            require_once __DIR__ . '/admin-pages/local_avatar/settings.php';
            require_once __DIR__ . '/admin-pages/uploads_privacy/settings.php';
            require_once __DIR__ . '/admin-pages/hide_admin/settings.php';
            require_once __DIR__ . '/admin-pages/apis/settings.php';
            require_once __DIR__ . '/admin-pages/forms/settings.php';
            do_action( 'acf_frontend/admin_pages' );
        }
        
        public function validate_save_post()
        {
            
            if ( isset( $_POST['_acf_admin_page'] ) ) {
                $page_slug = $_POST['_acf_admin_page'];
                apply_filters( 'acff/' . $page_slug . '_fields', [] );
            }
        
        }
        
        public function scripts()
        {
            
            if ( acff()->dev_mode ) {
                $min = '';
            } else {
                $min = '-min';
            }
            
            wp_register_style(
                'acff-modal',
                ACFF_URL . 'assets/css/modal-min.css',
                array(),
                ACFF_ASSETS_VERSION
            );
            wp_register_style(
                'acff',
                ACFF_URL . 'assets/css/acff-min.css',
                array(),
                ACFF_ASSETS_VERSION
            );
            wp_register_script(
                'acff-modal',
                ACFF_URL . 'assets/js/modal.min.js',
                array( 'jquery' ),
                ACFF_ASSETS_VERSION
            );
            wp_register_script(
                'acff',
                ACFF_URL . 'assets/js/acff' . $min . '.js',
                array( 'jquery', 'acf', 'acf-input' ),
                ACFF_ASSETS_VERSION,
                true
            );
            wp_register_script(
                'acff-password-strength',
                ACFF_URL . 'assets/js/password-strength.min.js',
                array( 'password-strength-meter' ),
                ACFF_ASSETS_VERSION,
                true
            );
            add_action( 'admin_init', array( $this, 'activate_payments_addon' ) );
        }
        
        public function __construct()
        {
            $this->tabs = array(
                'welcome'         => 'Welcome',
                'local_avatar'    => 'Local Avatar',
                'uploads_privacy' => 'Uploads Privacy',
                'hide_admin'      => 'Hide WP Dashboard',
                'apis'            => 'APIs',
                'payments'        => 'Payments',
            );
            $this->tabs = apply_filters( 'acf_frontend/admin_tabs', $this->tabs );
            $this->settings_sections();
            add_action( 'wp_loaded', array( $this, 'scripts' ) );
            add_action( 'init', array( $this, 'configs' ) );
            add_action( 'admin_menu', array( $this, 'plugin_page' ), 15 );
            add_action( 'acf/validate_save_post', array( $this, 'validate_save_post' ) );
            $this->add_tabs();
        }
    
    }
    acff()->admin_settings = new ACFFrontend_Admin();
}
