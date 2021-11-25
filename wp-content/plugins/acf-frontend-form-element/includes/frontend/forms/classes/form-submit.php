<?php

namespace ACFFrontend\Classes;


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}


if ( !class_exists( 'ACFFrontend\\Classes\\Submit_Form' ) ) {
    class Submit_Form
    {
        public function validate_submitted_form()
        {
            // validate
            if ( !acf_verify_ajax() ) {
                die;
            }
            do_action( 'acf/validate_save_post' );
            // vars
            $json = array(
                'valid'  => 1,
                'errors' => 0,
            );
            
            if ( !empty($_POST['acff']) ) {
                $data_types = array(
                    'post',
                    'user',
                    'term',
                    'options',
                    'woo_product'
                );
                foreach ( $data_types as $type ) {
                    if ( isset( $_POST['acff'][$type] ) ) {
                        acf_validate_values( $_POST['acff'][$type], 'acff[' . $type . ']' );
                    }
                }
            } else {
                acf_validate_values( $_POST['acf'], 'acf' );
            }
            
            // vars
            $errors = acf_get_validation_errors();
            // bail ealry if no errors
            if ( !$errors ) {
                wp_send_json_success( $json );
            }
            // update vars
            $json['valid'] = 0;
            $json['errors'] = $errors;
            // return
            wp_send_json_success( $json );
        }
        
        public function check_submit_form()
        {
            // verify nonce
            if ( !acf_verify_nonce( 'acff_form' ) ) {
                wp_send_json_error();
            }
            // bail ealry if form not submit
            if ( empty($_POST['_acf_form']) ) {
                wp_send_json_error();
            }
            // load form
            $form = json_decode( acf_decrypt( $_POST['_acf_form'] ), true );
            // bail ealry if form is corrupt
            if ( empty($form) ) {
                wp_send_json_error();
            }
            // kses
            if ( $form['kses'] && isset( $_POST['acff'] ) ) {
                $_POST['acff'] = wp_kses_post_deep( $_POST['acff'] );
            }
            // remove validate email field before it saves an empty row in the database
            if ( isset( $_POST['acff']['_validate_email'] ) ) {
                unset( $_POST['acff']['_validate_email'] );
            }
            // submit
            $this->submit_form( $form );
        }
        
        function create_record( $form, $save = false )
        {
            // Retrieve all form fields and their values
            if ( empty($form['record']) ) {
                $form['record'] = array(
                    'fields' => false,
                );
            }
            if ( !empty($_POST) ) {
                foreach ( $_POST as $key => $value ) {
                    
                    if ( $key == 'acff' ) {
                        $actions = array(
                            'post',
                            'user',
                            'term',
                            'woo_product',
                            'options',
                            'admin_options'
                        );
                        foreach ( $value as $k => $inputs ) {
                            
                            if ( in_array( $k, $actions ) ) {
                                if ( is_array( $inputs ) ) {
                                    foreach ( $inputs as $i => $input ) {
                                        $form['record'] = $this->add_value_to_record(
                                            $form,
                                            $i,
                                            $input,
                                            $k
                                        );
                                    }
                                }
                            } else {
                                $form['record'] = $this->add_value_to_record( $form, $k, $inputs );
                            }
                        
                        }
                    } else {
                        if ( $key == '_acf_form' ) {
                            continue;
                        }
                        $form['record'][$key] = $value;
                    }
                
                }
            }
            if ( isset( $form['record']['submission_title_filled'] ) ) {
                unset( $form['record']['submission_title_filled'] );
            }
            if ( $save ) {
                $form = $this->save_record( $form, $save );
            }
            return $form;
        }
        
        public function add_value_to_record(
            $form,
            $key,
            $input,
            $group = false
        )
        {
            $record = $form['record'];
            $field = acf_get_field( $key );
            if ( !$field && !empty($form['field_objects'][$key]) ) {
                $field = $form['field_objects'][$key];
            }
            
            if ( $field ) {
                if ( in_array( $field['type'], array( 'email', 'user_email' ) ) ) {
                    
                    if ( $input ) {
                        
                        if ( email_exists( $input ) ) {
                            $user = get_user_by( 'email', $input );
                            if ( isset( $user->ID ) ) {
                                $verified = get_user_meta( $user->ID, 'acff_email_verified', 1 );
                            }
                        } else {
                            $verified_emails = get_option( 'acff_verified_emails' );
                            
                            if ( $verified_emails ) {
                                $verified_emails = explode( ',', $verified_emails );
                                if ( in_array( $input, $verified_emails ) ) {
                                    $verified = true;
                                }
                            }
                        
                        }
                        
                        
                        if ( empty($verified) ) {
                            $record['emails_to_verify'][$input] = $input;
                        } else {
                            $record['verified_emails'][$input] = $input;
                        }
                    
                    }
                
                }
                $field['_input'] = $input;
                $field['value'] = acf_format_value( $input, 0, $field );
                
                if ( is_string( $field['value'] ) && empty($record['submission_title_filled']) ) {
                    $record['submission_title'] = $field['value'];
                    $record['submission_title_filled'] = true;
                }
                
                
                if ( $group ) {
                    $record['fields'][$group][$field['name']] = $field;
                } else {
                    $record['fields'][$field['name']] = $field;
                }
            
            }
            
            return $record;
        }
        
        public function submit_form( $form )
        {
            if ( empty($form['approval']) ) {
                do_action( 'acf_frontend/form/on_submit', $form );
            }
            $form = $this->create_record( $form );
            // add global for backwards compatibility
            $GLOBALS['acff_form'] = $form;
            $form['submission_status'] = 'approved';
            $submission_requirements = array();
            foreach ( acff()->local_actions as $name => $action ) {
                
                if ( empty($form["save_{$name}_data"]) && empty($form["save_all_data"]) || isset( $form['approval'] ) ) {
                    $form = $action->run( $form );
                } else {
                    
                    if ( !empty($form["save_all_data"]) ) {
                        $prerequisites = $form["save_all_data"];
                    } else {
                        $prerequisites = $form["save_{$name}_data"];
                    }
                    
                    foreach ( $prerequisites as $prerequisite ) {
                        if ( !in_array( $prerequisite, $submission_requirements ) ) {
                            $submission_requirements[$prerequisite] = $prerequisite;
                        }
                    }
                    if ( $name != 'options' && isset( $form["{$name}_id"] ) ) {
                        $form['record'][$name] = $form["{$name}_id"];
                    }
                }
            
            }
            
            if ( $submission_requirements ) {
                
                if ( in_array( 'verify_email', $submission_requirements ) ) {
                    
                    if ( empty($form['record']['emails_to_verify']) && empty($form['record']['verified_emails']) ) {
                        $current_user = wp_get_current_user();
                        
                        if ( isset( $current_user->ID ) ) {
                            $verified = get_user_meta( $current_user->ID, 'acff_email_verified', 1 );
                            if ( !$verified ) {
                                $form['record']['emails_to_verify'][$current_user->user_email] = $current_user->user_email;
                            }
                        }
                    
                    }
                    
                    if ( empty($form['record']['emails_to_verify']) ) {
                        unset( $submission_requirements['verify_email'] );
                    }
                } else {
                    unset( $form['record']['emails_to_verify'] );
                }
                
                $form['submission_status'] = implode( ',', $submission_requirements );
            } else {
                unset( $form['record']['emails_to_verify'] );
            }
            
            $this->return_form( $form );
        }
        
        public function send_verification_emails( $form )
        {
            foreach ( $form['record']['emails_to_verify'] as $email_address ) {
                $subject = __( 'Please verify your email.', 'acf-frontend-form-element' );
                $message = '<h1>' . $subject . '</h1>';
                $token = wp_create_nonce( 'acff-verify-' . $email_address );
                $message .= '<p>' . sprintf( __( 'Please click <a href="%s">here</a> to verify your email. Thank you.', 'acf-frontend-form-element' ) . '</p>', add_query_arg( array(
                    'submit_id'     => $form['submission'],
                    'email-address' => $email_address,
                    'token'         => $token,
                ), $form['return'] ) );
                // Set the type of email to HTML.
                $headers[] = 'Content-type: text/html; charset=UTF-8';
                $header_string = implode( "\r\n", $headers );
                return wp_mail(
                    $email_address,
                    $subject,
                    $message,
                    $header_string
                );
            }
        }
        
        public function return_form( $form )
        {
            // get form id
            
            if ( isset( $_POST['_acf_element_id'] ) ) {
                $form_id = $_POST['_acf_element_id'];
            } elseif ( isset( $_POST['_acf_field_id'] ) ) {
                $form_id = $_POST['_acf_field_id'];
            } elseif ( isset( $_POST['_acf_admin_page'] ) ) {
                $form_id = $_POST['_acf_admin_page'];
            } else {
                wp_send_json_error();
            }
            
            $types = array(
                'post',
                'user',
                'term',
                'product'
            );
            $form_ids = array();
            foreach ( $types as $type ) {
                if ( isset( $form['record'][$type] ) ) {
                    $form[$type . '_id'] = $form['record'][$type];
                }
            }
            
            if ( isset( $_POST['_acf_status'] ) && $_POST['_acf_status'] != 'publish' ) {
                $form['save_to_post'] = 'edit_post';
                ob_start();
                acff()->form_display->render_form( $form );
                $response['clear_form'] = ob_get_clean();
                $response['saved_message'] = $form['saved_draft_message'];
                wp_send_json_success( $response );
                exit;
            }
            
            $update_message = $form['update_message'];
            if ( is_string( $update_message ) && strpos( $update_message, '[' ) !== 'false' ) {
                $update_message = acff()->dynamic_values->get_dynamic_values( $update_message, $form );
            }
            
            if ( isset( $_POST['log_back_in'] ) ) {
                $user_data = $_POST['log_back_in'];
                
                if ( is_array( $user_data ) ) {
                    $user_id = $user_data[0];
                    $user_login = $user_data[1];
                }
                
                $user_object = get_user_by( 'ID', $user_id );
                
                if ( $user_object ) {
                    clean_user_cache( $user_id );
                    wp_clear_auth_cookie();
                    wp_set_current_user( $user_id, $user_login );
                    wp_set_auth_cookie( $user_id, true, true );
                    do_action( 'wp_login', $user_login, $user_object );
                    update_user_caches( $user_object );
                    $response['message_token'] = wp_generate_password();
                    update_user_meta( $user_id, 'message_token', $response['message_token'] );
                }
            
            }
            
            
            if ( $form['show_update_message'] ) {
                $response['success_message'] = $update_message;
                $response['location'] = $form['message_location'];
                $response['form_element'] = $form['hidden_fields']['element_id'];
            }
            
            
            if ( !empty($form['ajax_submit']) ) {
                $response = array(
                    'to_top' => true,
                );
                
                if ( isset( $form['form_attributes']['data-field'] ) ) {
                    $response['post_id'] = $form['post_id'];
                    $response['field_key'] = $form['form_attributes']['data-field'];
                    $title = get_post_field( 'post_title', $form['post_id'] ) . '<a href="#" class="acf-icon -pencil small dark edit-rel-post" data-name="edit_item"></a>';
                    $rel_field = acf_get_field( $response['field_key'] );
                    
                    if ( $rel_field && acf_in_array( 'featured_image', $rel_field['elements'] ) ) {
                        // vars
                        $class = 'thumbnail';
                        $thumbnail = acf_get_post_thumbnail( $form['post_id'], array( 17, 17 ) );
                        // icon
                        if ( $thumbnail['type'] == 'icon' ) {
                            $class .= ' -' . $thumbnail['type'];
                        }
                        // append
                        $title = '<div class="' . $class . '">' . $thumbnail['html'] . '</div>' . $title;
                    }
                    
                    $response['append'] = [
                        'id'     => $form['post_id'],
                        'text'   => $title,
                        'action' => ( $form['save_to_post'] == 'edit_post' ? 'edit' : 'add' ),
                    ];
                }
                
                if ( isset( $form['redirect_action'] ) ) {
                    
                    if ( $form['redirect_action'] == 'clear' ) {
                        foreach ( $types as $type ) {
                            
                            if ( $form["save_to_{$type}"] == "new_{$type}" ) {
                                $form[$type . '_id'] = "add_{$type}";
                                $form["save_to_{$type}"] = "new_{$type}";
                            }
                        
                        }
                    } else {
                        foreach ( $types as $type ) {
                            $form["save_to_{$type}"] = "edit_{$type}";
                        }
                    }
                
                }
                ob_start();
                acff()->form_display->render_form( $form );
                $response['clear_form'] = ob_get_clean();
            } else {
                // vars
                $return = acf_maybe_get( $form, 'return', '' );
                // redirect
                
                if ( $return ) {
                    // update %placeholders%
                    $return = str_replace( '%post_id%', $form['post_id'], $return );
                    $return = str_replace( '%post_url%', get_permalink( $form['post_id'] ), $return );
                    $query_args = [];
                    if ( !empty($form['url_query']) ) {
                        $query_args = array_merge( $query_args, $form['url_query'] );
                    }
                    if ( isset( $form['redirect_params'] ) ) {
                        $query_args = array_merge( $query_args, $form['redirect_params'] );
                    }
                    $return = add_query_arg( $query_args, $return );
                    $return = acff()->dynamic_values->get_dynamic_values( $return, $form );
                    if ( isset( $form['last_step'] ) ) {
                        $return = remove_query_arg( [ 'form_id', 'modal', 'step' ], $return );
                    }
                    $response['redirect'] = $return;
                    if ( isset( $form['redirect_action'] ) && $form['redirect_action'] == 'edit' ) {
                        foreach ( $types as $type ) {
                            $response['edit_data'][$type] = $form[$type . '_id'];
                        }
                    }
                    if ( !empty($_POST['_acf_modal']) ) {
                        $response['modal'] = true;
                    }
                    $response['acff-nonce'] = wp_create_nonce( 'acff-form' );
                    $expiration_time = time() + 600;
                    setcookie(
                        'acff_form_success',
                        json_encode( $response ),
                        $expiration_time,
                        '/'
                    );
                }
            
            }
            
            if ( empty($form['no_record']) ) {
                $this->save_record( $form, $form['submission_status'] );
            }
            wp_send_json_success( $response );
            die;
        }
        
        public function reload_form(
            $form_ids,
            $form,
            $step,
            $step_index
        )
        {
            $form['step_index'] = $form['step_index'] + 1;
            $types = array(
                'post',
                'user',
                'term',
                'product'
            );
            foreach ( $types as $type ) {
                
                if ( !empty($form['record'][$type]) ) {
                    $form[$type . '_id'] = $form['record'][$type];
                    $form["save_to_{$type}"] = "edit_{$type}";
                }
            
            }
            $form = $this->save_record( $form );
            ob_start();
            acff()->form_display->render_form( $form );
            $reload_form = ob_get_contents();
            ob_end_clean();
            wp_send_json_success( [
                'clear_form' => $reload_form,
                'widget'     => $form['hidden_fields']['element_id'],
                'step'       => $form['step_index'],
            ] );
            die;
        }
        
        public function save_record( $form, $status = 'in_progress' )
        {
            
            if ( isset( $form['no_cookies'] ) ) {
                unset( $form['no_cookies'] );
                $no_cookie = true;
            }
            
            if ( !empty($form['approval']) ) {
                $status = 'approved';
            }
            global  $wpdb ;
            $args = [
                'fields' => acf_encrypt( json_encode( $form ) ),
                'user'   => get_current_user_id(),
                'status' => $status,
                'title'  => $form['record']['submission_title'],
            ];
            
            if ( empty($form['submission']) ) {
                $args['created_at'] = current_time( 'mysql' );
                
                if ( $form['form_title'] ) {
                    $args['form'] = "{$form['form_title']} ({$form['id']})";
                } else {
                    $args['form'] = $form['id'];
                }
                
                $form['submission'] = acff()->submissions_handler->insert_submission( $args );
            } else {
                acff()->submissions_handler->update_submission( $form['submission'], $args );
            }
            
            if ( !empty($form['record']['emails_to_verify']) ) {
                $this->send_verification_emails( $form );
            }
            
            if ( empty($no_cookie) ) {
                $expiration_time = time() + 86400;
                setcookie(
                    $form['id'],
                    $form['submission'],
                    $expiration_time,
                    '/'
                );
            }
            
            return $form;
        }
        
        public function form_message()
        {
            
            if ( isset( $_COOKIE['acff_form_success'] ) ) {
                $form_success = json_decode( stripslashes( $_COOKIE['acff_form_success'] ), true );
            } else {
                return;
            }
            
            
            if ( empty($form_success['acff-nonce']) || !wp_verify_nonce( $form_success['acff-nonce'], 'acff-form' ) ) {
                $user_id = get_current_user_id();
                if ( empty($form_success['message_token']) || get_user_meta( $user_id, 'message_token', true ) !== $form_success['message_token'] ) {
                    return;
                }
            }
            
            
            if ( isset( $form_success['success_message'] ) && $form_success['location'] == 'other' ) {
                $message = wp_unslash( wp_kses( $form_success['success_message'], 'post' ) );
                $return = '<div class="-fixed acff-message">
					<div class="elementor-element elementor-element-' . $form_success['form_element'] . '">
						<div class="acf-notice -success acf-success-message -dismiss"><p class="success-msg">' . $message . '</p><span class="acff-dismiss close-msg acf-notice-dismiss acf-icon -cancel small"></span></div>
					</div>
					</div>';
                acf_enqueue_scripts();
                echo  $return ;
                unset( $_COOKIE['acff_form_success'] );
            }
        
        }
        
        public function delete_object()
        {
            if ( !acf_verify_nonce( 'acff_form' ) ) {
                wp_send_json_error();
            }
            // bail ealry if form not submit
            if ( empty($_POST['_acf_form']) ) {
                wp_send_json_error();
            }
            // load form
            $form = json_decode( acf_decrypt( $_POST['_acf_form'] ), true );
            // bail ealry if form is corrupt
            if ( empty($form) ) {
                wp_send_json_error();
            }
            //$form = $this->create_record( $form );
            // add global for backwards compatibility
            $GLOBALS['acff_form'] = $form;
            $button_id = $_POST['_acf_element_id'];
            $redirect_args = array(
                'redirect' => $form['return'],
            );
            
            if ( isset( $_POST['_acf_post'] ) ) {
                $post_id = intval( $_POST['_acf_post'] );
                
                if ( isset( $form['force_delete'] ) && $form['force_delete'] == 'true' ) {
                    $deleted = wp_delete_post( $post_id, true );
                } else {
                    $deleted = wp_trash_post( $post_id );
                    $redirect_args['trashed'] = true;
                }
                
                $form['record']['post'] = $post_id;
                $redirect_args['type'] = 'post';
            }
            
            
            if ( isset( $_POST['_acf_term'] ) ) {
                $term_id = intval( $_POST['_acf_term'] );
                $deleted = wp_delete_term( $term_id, sanitize_text_field( $_POST['_acf_taxonomy_type'] ) );
                $form['record']['term'] = $term_id;
                $redirect_args['type'] = 'term';
            }
            
            
            if ( isset( $_POST['_acf_user'] ) ) {
                $user_id = intval( $_POST['_acf_user'] );
                $deleted = wp_delete_user( $user_id, $form['reassign_posts'] );
                $form['record']['user'] = $user_id;
                $redirect_args['type'] = 'user';
            }
            
            
            if ( $form['show_delete_message'] ) {
                $message = $form['delete_message'];
                if ( strpos( $message, '[' ) !== 'false' ) {
                    $message = acff()->dynamic_values->get_dynamic_values( $message, $form );
                }
                $redirect_args['success_message'] = $message;
                $redirect_args['location'] = 'other';
                $redirect_args['acff-nonce'] = wp_create_nonce( 'acff-form' );
            }
            
            $redirect_args['button_element'] = $button_id;
            //$this->save_record( $form, 'pending' );
            $expiration_time = time() + 600;
            setcookie(
                'acff_form_success',
                json_encode( $redirect_args ),
                $expiration_time,
                '/'
            );
            wp_send_json_success( $redirect_args );
            die;
        }
        
        public function delete_records()
        {
            $record_args = array(
                'post_type'      => 'acf_form_record',
                'post_status'    => 'all',
                'date_query'     => array( array(
                'before'    => '60 minutes ago',
                'inclusive' => true,
            ) ),
                'posts_per_page' => -1,
            );
            if ( get_posts( $record_args ) ) {
                foreach ( get_posts( $record_args ) as $post ) {
                    wp_delete_post( $post->ID, true );
                }
            }
        }
        
        public function verify_email_address()
        {
            
            if ( isset( $_GET['submit_id'] ) && isset( $_GET['email-address'] ) && isset( $_GET['token'] ) ) {
                $request = $_GET;
            } else {
                return;
            }
            
            $submission = acff()->submissions_handler->get_submission( $request['submit_id'] );
            if ( empty($submission->fields) ) {
                wp_redirect( home_url() );
            }
            $form = json_decode( acf_decrypt( $submission->fields ), true );
            $record = $form['record'];
            $address = $request['email-address'];
            
            if ( isset( $record['emails_to_verify'][$address] ) ) {
                if ( !wp_verify_nonce( $request['token'], 'acff-verify-' . $address ) ) {
                    return;
                }
                
                if ( email_exists( $address ) ) {
                    $user = get_user_by( 'email', $address );
                    if ( isset( $user->ID ) ) {
                        update_post_meta( $user->ID, 'acff_email_verified', 1 );
                    }
                } else {
                    $verified_emails = get_option( 'acff_verified_emails', '' );
                    
                    if ( $verified_emails = '' ) {
                        $verified_emails = $address;
                    } else {
                        $verified_emails .= ',' . $address;
                    }
                    
                    update_option( 'acff_verified_emails', $verified_emails );
                }
                
                unset( $form['record']['emails_to_verify'][$address] );
                $form['record']['verified_emails'][$address] = $address;
                $args = array();
                
                if ( empty($form['record']['emails_to_verify']) ) {
                    
                    if ( $submission->status ) {
                        $old_status = explode( ',', $submission->status );
                        if ( !in_array( 'require_approval', $old_status ) ) {
                            foreach ( acff()->local_actions as $name => $action ) {
                                $form = $action->run( $form );
                            }
                        }
                        $new_status = str_replace( 'verify_email', 'email_verified', $submission->status );
                    }
                    
                    $args['status'] = $new_status;
                }
                
                $args['fields'] = acf_encrypt( json_encode( $form ) );
                acff()->submissions_handler->update_submission( $request['submit_id'], $args );
                $GLOBAL[$address . '_verified'] = true;
            }
        
        }
        
        public function __construct()
        {
            add_action( 'admin_init', array( $this, 'delete_records' ) );
            add_action( 'wp_footer', array( $this, 'form_message' ) );
            add_action( 'wp_ajax_acf/validate_save_post', array( $this, 'validate_submitted_form' ), 2 );
            add_action( 'wp_ajax_nopriv_acf/validate_save_post', array( $this, 'validate_submitted_form' ), 2 );
            add_action( 'wp_ajax_acff/form_submit', array( $this, 'check_submit_form' ) );
            add_action( 'wp_ajax_nopriv_acff/form_submit', array( $this, 'check_submit_form' ) );
            add_action( 'admin_post_acff/form_submit', array( $this, 'check_submit_form' ) );
            add_action( 'admin_post_nopriv_acff/form_submit', array( $this, 'check_submit_form' ) );
            add_action( 'wp_ajax_acff/delete_object', array( $this, 'delete_object' ) );
            add_action( 'wp_ajax_nopriv_acff/delete_object', array( $this, 'delete_object' ) );
            add_action( 'init', array( $this, 'verify_email_address' ) );
        }
    
    }
    acff()->form_submit = new Submit_Form();
}
