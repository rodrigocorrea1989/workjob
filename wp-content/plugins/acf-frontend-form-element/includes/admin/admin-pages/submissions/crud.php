<?php

namespace ACFFrontend\Admin;


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( !class_exists( 'Submissions_Crud' ) ) {
    class Submissions_Crud
    {
        public function create_submissions()
        {
            global  $wpdb ;
            $charset_collate = $wpdb->get_charset_collate();
            $table_name = $wpdb->prefix . 'acff_submissions';
            $sql = "CREATE TABLE {$table_name} (\r\n\t\t\t\tid mediumint(9) NOT NULL AUTO_INCREMENT,\r\n\t\t\t\ttitle text NOT NULL,\r\n\t\t\t\tcreated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,\r\n\t\t\t\tuser int NOT NULL,\r\n\t\t\t\tfields longtext NOT NULL,\r\n\t\t\t\tform text NOT NULL,\r\n\t\t\t\tstatus text NOT NULL,\r\n\t\t\t\tUNIQUE KEY id (id)\r\n\t\t\t) {$charset_collate};";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            maybe_create_table( $table_name, $sql );
        }
        
        public function insert_submission( $args )
        {
            global  $wpdb ;
            if ( empty($args['title']) ) {
                $args['title'] = '';
            }
            $wpdb->insert( $wpdb->prefix . 'acff_submissions', $args );
            return $wpdb->insert_id;
        }
        
        public function update_submission( $id, $args )
        {
            if ( isset( $args['fields'] ) && is_array( $args['fields'] ) ) {
                $args['fields'] = acf_encrypt( json_encode( $args['fields'] ) );
            }
            global  $wpdb ;
            $wpdb->update( $wpdb->prefix . 'acff_submissions', $args, array(
                'id' => $id,
            ) );
        }
        
        public function approve_submission( $id )
        {
            global  $wpdb ;
            $submission = $this->get_submission( $id );
            
            if ( $submission->status == 'pending' ) {
                $form = $this->get_submission_form( $submission );
                foreach ( acff()->local_actions as $action ) {
                    $name = $action->get_name();
                    $form = $action->run( $form );
                }
                $wpdb->update( $wpdb->prefix . 'acff_submissions', array(
                    'status' => 'approved',
                ), array(
                    'id' => $id,
                ) );
            }
        
        }
        
        public function get_submission( $id = 0 )
        {
            if ( !$id ) {
                return $id;
            }
            global  $wpdb ;
            $id = (int) $id;
            $submission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}acff_submissions WHERE id = %d", $id ) );
            return $submission;
        }
        
        /**
         * Retrieve submissions data from the database
         *
         * @param array $args query arguments
         *
         * @return mixed
         */
        public static function get_submissions( $args = array() )
        {
            global  $wpdb ;
            $args = acf_frontend_parse_args( $args, array(
                'per_page'     => 20,
                'current_page' => 1,
            ) );
            $sql = "SELECT * FROM {$wpdb->prefix}acff_submissions";
            
            if ( !empty($_REQUEST['s']) ) {
                $value = $_REQUEST['s'] . '%';
                $sql .= $wpdb->prepare( ' WHERE title LIKE %s', $value );
            }
            
            
            if ( !empty($_REQUEST['orderby']) ) {
                $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
                $sql .= ( !empty($_REQUEST['order']) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC' );
            } else {
                $sql .= ' ORDER BY ' . sanitize_sql_orderby( 'created_at DESC' );
            }
            
            $sql .= $wpdb->prepare( " LIMIT %d", $args['per_page'] );
            $sql .= $wpdb->prepare( " OFFSET %d", ($args['current_page'] - 1) * $args['per_page'] );
            $result = $wpdb->get_results( $sql, 'ARRAY_A' );
            return $result;
        }
        
        /**
         * Returns the count of records in the database.
         *
         * @return null|string
         */
        public static function record_count()
        {
            global  $wpdb ;
            $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}acff_submissions";
            return $wpdb->get_var( $sql );
        }
        
        public function delete_submission( $id = 0 )
        {
            if ( $id == 0 ) {
                return $id;
            }
            global  $wpdb ;
            $wpdb->delete( $wpdb->prefix . 'acff_submissions', array(
                'id' => $id,
            ) );
            return 1;
        }
        
        public function display_submissions()
        {
            require_once __DIR__ . '/list.php';
            $display_list = true;
            
            if ( isset( $_REQUEST['id'] ) ) {
                $submission_id = $_REQUEST['id'];
                $submission = $this->get_submission( $submission_id );
                
                if ( $submission ) {
                    $user = get_user_by( 'ID', $submission->user );
                    
                    if ( is_object( $user ) ) {
                        $title = $user->display_name . ' (' . $user->user_login . ')';
                    } else {
                        $title = '--';
                    }
                    
                    ?>
					<h2><?php 
                    echo  sprintf( 'Submission #%d: %s', $submission_id, $title ) ;
                    ?></h2>
					<?php 
                    $action = acff()->submissions_list->current_action();
                    
                    if ( $action == 'edit' ) {
                        $form = json_decode( acf_decrypt( $submission->fields ), true );
                        
                        if ( isset( $form['record'] ) ) {
                            $approval_form = $this->get_submission_form( $submission, $form, true );
                            acff()->form_display->render_form( $approval_form );
                            
                            if ( !empty($form['record']['emails_to_verify']) ) {
                                echo  '<div class="emails-to-verify">' ;
                                echo  '<h3>' . __( 'Pending Verification:', ACFF_NS ) . '</h3><ul>' ;
                                foreach ( $form['record']['emails_to_verify'] as $address ) {
                                    echo  '<li>' . $address . '</li>' ;
                                }
                                echo  '</ul></div>' ;
                            }
                            
                            
                            if ( !empty($form['record']['verified_emails']) ) {
                                echo  '<div class="verified-emails">' ;
                                echo  '<h3>' . __( 'Verified Emails:', ACFF_NS ) . '</h3><ul>' ;
                                foreach ( $form['record']['verified_emails'] as $address ) {
                                    echo  '<li>' . $address . '</li>' ;
                                }
                                echo  '</ul></div>' ;
                            }
                        
                        }
                        
                        $display_list = false;
                    } elseif ( $action == 'delete' ) {
                        if ( empty($_REQUEST['nonce']) ) {
                            die( 'Nonce required to perform this action.' );
                        }
                        $nonce = esc_attr( $_REQUEST['nonce'] );
                        if ( !wp_verify_nonce( $nonce, 'acff_delete_submission' ) ) {
                            die( 'Go get a life script kiddies' );
                        }
                        $this->delete_submission( $submission_id );
                    }
                
                } else {
                    $display_list = true;
                }
            
            }
            
            
            if ( $display_list ) {
                acff()->submissions_list->prepare_items();
                ?>
				<h1 class="wp-heading-inline"><?php 
                echo  __( 'Submissions', 'acf-frontend-form-element' ) ;
                ?></h1>
				<form method="post">
				<?php 
                acff()->submissions_list->search_box( 'search', 'search_id' );
                acff()->submissions_list->display();
                ?> </form> <?php 
            }
        
        }
        
        public function get_submission_form( $submission, $form = array(), $approval = false )
        {
            if ( is_string( $submission ) ) {
                $submission = $this->get_submission( $submission );
            }
            if ( empty($form) ) {
                $form = json_decode( acf_decrypt( $submission->fields ), true );
            }
            $approval_form = array_merge( $form, array(
                'field_objects' => call_user_func_array( 'array_merge', $form['record']['fields'] ),
            ) );
            
            if ( $approval ) {
                
                if ( $submission->status == 'approved' ) {
                    $submit_value = __( 'Update', 'acf-frontend-form-element' );
                    $success_message = __( 'Data has been updated.', 'acf-frontend-form-element' );
                } else {
                    $submit_value = __( 'Approve', 'acf-frontend-form-element' );
                    $success_message = __( 'Submission has been approved. Data has been saved.', 'acf-frontend-form-element' );
                }
                
                $approval_form = array_merge( $approval_form, array(
                    'submission'          => $submission->id,
                    'id'                  => $form['id'],
                    'submit_value'        => $submit_value,
                    'kses'                => 0,
                    'no_cookies'          => 1,
                    'approval'            => 1,
                    'return'              => admin_url( 'admin.php?page=acff-submissions&action=edit&id=' . $_REQUEST['id'] ),
                    'ajax_submit'         => 0,
                    'update_message'      => $success_message,
                    'show_update_message' => 1,
                ) );
            }
            
            $data_types = array( 'post', 'user', 'term' );
            foreach ( $data_types as $type ) {
                
                if ( isset( $form['record'][$type] ) ) {
                    $approval_form["{$type}_id"] = $form['record'][$type];
                    $approval_form["save_to_{$type}"] = $form["save_to_{$type}"];
                }
            
            }
            return $approval_form;
        }
        
        public function submissions_page_options()
        {
            
            if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'acff-submissions' ) {
                $option = 'per_page';
                $args = [
                    'label'   => 'Submissions',
                    'default' => 20,
                    'option'  => 'submissions_per_page',
                ];
                add_screen_option( $option, $args );
            }
        
        }
        
        function set_submissions_per_page( $status, $option, $value )
        {
            if ( 'submissions_per_page' == $option ) {
                return $value;
            }
            return $status;
        }
        
        public function custom_admin_pages()
        {
            global  $acff_submissions_page ;
            $acff_submissions_page = add_submenu_page(
                'acff-settings',
                __( 'Submissions', 'acf-frontend-form-element' ),
                __( 'Submissions', 'acf-frontend-form-element' ),
                'manage_options',
                'acff-submissions',
                [ $this, 'display_submissions' ],
                81
            );
            add_action( "load-{$acff_submissions_page}", array( $this, 'submissions_page_options' ) );
        }
        
        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'custom_admin_pages' ), 19 );
            $this->create_submissions();
            add_filter(
                'set-screen-option',
                array( $this, 'set_submissions_per_page' ),
                11,
                3
            );
        }
    
    }
    acff()->submissions_handler = new Submissions_Crud();
}
