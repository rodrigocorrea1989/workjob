<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( !class_exists( 'ACFFrontend_Hooks' ) ) {
    class ACFFrontend_Hooks
    {
        public function frontend_only_setting( $field )
        {
            acf_render_field_setting( $field, array(
                'label'        => __( 'Display Mode' ),
                'instructions' => __( 'Lets you show the editable field or display the value only. You may also hide the field, which is useful if you need to pass hidden data', ACFF_NS ),
                'name'         => 'acff_display_mode',
                'type'         => 'select',
                'choices'      => array(
                'edit'      => __( 'Edit', ACFF_NS ),
                'read_only' => __( 'Read Only', ACFF_NS ),
                'hidden'    => __( 'Hidden', ACFF_NS ),
            ),
            ), true );
            global  $post ;
            
            if ( isset( $post->post_type ) && $post->post_type == 'acf_frontend_form' ) {
                acf_render_field_setting( $field, array(
                    'label'        => __( 'Hide Field Label', ACFF_NS ),
                    'instructions' => __( 'Lets you hide the field\'s label including HTML markup.', ACFF_NS ),
                    'name'         => 'field_label_hide',
                    'type'         => 'true_false',
                    'ui'           => 1,
                ), true );
            } else {
                acf_render_field_setting( $field, array(
                    'label'        => __( 'Show On Frontend Only' ),
                    'instructions' => __( 'Lets you hide the field on the backend to avoid duplicate fields.', ACFF_NS ),
                    'name'         => 'only_front',
                    'type'         => 'true_false',
                    'ui'           => 1,
                    'conditions'   => [ [
                    'field'    => 'acff_display_mode',
                    'operator' => '!=',
                    'value'    => 'hidden',
                ] ],
                ), true );
            }
        
        }
        
        public function read_only_setting( $field )
        {
            $types = array(
                'text',
                'textarea',
                'email',
                'number'
            );
            if ( in_array( $field['type'], $types ) ) {
                acf_render_field_setting( $field, array(
                    'label'        => __( 'Read Only', ACFF_NS ),
                    'instructions' => 'Prevent users from changing the data.',
                    'name'         => 'readonly',
                    'type'         => 'true_false',
                    'ui'           => 1,
                ) );
            }
        }
        
        public function hide_acff_fields( $groups )
        {
            global  $post ;
            if ( isset( $post->post_type ) && $post->post_type == 'acf-field-group' ) {
                unset( $groups['Form'] );
            }
            unset( $groups['acff-hidden'] );
            return $groups;
        }
        
        /* 		public function acff_load_text_value( $value, $post_id = false, $field = false ){
        			if( ! $this->acff_is_custom( $field ) ){
        				return $value;
        			}
        			if( $post_id ){
        				
        			if( strpos( $post_id, 'comment' ) !== false ){
        					$current_user = wp_get_current_user();
        					if( $current_user !== 0 ){
        						if( isset( $field['custom_author'] ) && $field['custom_author'] == 1 ){
        							$value = esc_html( $current_user->display_name );
        						}				
        					}
        				}
        			}
        
        			return $value;
        		}
        
        
        		public function acff_load_email_value( $value, $post_id = false, $field = false ){
        			if( ! $this->acff_is_custom( $field ) ){
        				return $value;
        			}
        			if( $post_id ){
        				if( strpos( $post_id, 'comment' ) !== false ){
        					$current_user = wp_get_current_user();
        					if( $current_user !== 0 ){			
        						if( isset( $field['custom_author_email'] ) && $field['custom_author_email'] == 1 ){
        							$value = esc_html( $current_user->user_email );
        						}
        					}
        				}
        			}
        			return $value;
        		}
        			 */
        public function update_acff_values( $value, $post_id = false, $field = false )
        {
            if ( !empty($field['no_save']) ) {
                return null;
            }
            
            if ( isset( $_POST['_acf_status'] ) && $_POST['_acf_status'] == 'publish' ) {
                $revisions = wp_get_post_revisions( $post_id );
                
                if ( !empty($revisions[0]) ) {
                    remove_filter(
                        'acf/update_value',
                        [ $this, 'update_acff_values' ],
                        7,
                        3
                    );
                    acf_update_value( $value, $revisions[0]->ID, $field );
                    add_filter(
                        'acf/update_value',
                        [ $this, 'update_acff_values' ],
                        7,
                        3
                    );
                }
            
            }
            
            return $value;
        }
        
        /* 		public function acff_update_text_value( $value, $post_id = false, $field = false ){
        			if( ! $this->acff_is_custom( $field ) ){
        				return $value;
        			}
        
        			if( strpos( $post_id, 'term' ) !== false ){
        				$term_id = explode( '_', $post_id )[1];
        				$edit_term = get_term( $term_id );
        				if( ! is_wp_error( $edit_term ) ){
        					if( isset( $field['custom_term_name'] ) && $field['custom_term_name'] == 1 ){
        						$update_args = array( 'name' => $value );
        						if( $field['change_slug'] )$update_args['slug'] = sanitize_title( $value );
        						wp_update_term( $term_id, $edit_term->taxonomy, $update_args );
        					}
        				}
        			}elseif( strpos( $post_id, 'comment' ) !== false ){
        				$comment_id = explode( '_', $post_id )[1];
        				$comment_to_edit = [
        					'comment_ID' => $comment_id,
        				];
        				if( isset( $field['custom_author'] ) && $field['custom_author'] == 1 ){
        					$comment_to_edit['comment_author'] = esc_attr( $value );
        				}
        				wp_update_comment( $comment_to_edit );
        			}
        			
        			return null;
        		}
        		
        		
        		public function acff_update_email_value( $value, $post_id = false, $field = false ){
        			if( ! $this->acff_is_custom( $field ) ){
        				return $value;
        			}
        			if( strpos( $post_id, 'comment' ) !== false ){
        				$comment_id = explode( '_', $post_id )[1];
        				$comment_to_edit = [
        					'comment_ID' => $comment_id,
        				];
        				if( isset( $field['custom_author_email'] ) && $field['custom_author_email'] == 1 ){
        					$comment_to_edit['comment_author_email'] = esc_attr( $value );
        				}
        				wp_update_comment( $comment_to_edit );
        			}
        			
        			return null;
        		} */
        public function exclude_groups( $field_group )
        {
            
            if ( empty($field_group['acff_group']) ) {
                return $field_group;
            } elseif ( is_admin() ) {
                
                if ( function_exists( 'get_current_screen' ) ) {
                    $current_screen = get_current_screen();
                    
                    if ( isset( $current_screen->post_type ) && $current_screen->post_type == 'acf_frontend_form' ) {
                        return $field_group;
                    } else {
                        return null;
                    }
                
                }
            
            }
        
        }
        
        public function load_invisible_field( $field )
        {
            if ( empty($field['invisible']) ) {
                return $field;
            }
            $field['acff_display_mode'] = 'hidden';
            unset( $field['invisible'] );
            acf_update_field( $field );
            return $field;
        }
        
        public function before_validation()
        {
            if ( isset( $_POST['_acf_field_id'] ) ) {
                acf_add_local_field( array(
                    'key'    => 'acff_post_type',
                    'label'  => __( 'Post Type', ACFF_NS ),
                    'name'   => 'acff_post_type',
                    'type'   => 'post_type',
                    'layout' => 'vertical',
                ) );
            }
        }
        
        public function skip_validation()
        {
            if ( isset( $_POST['_acf_status'] ) && $_POST['_acf_status'] != 'publish' ) {
                acf_reset_validation_errors();
            }
        }
        
        public function enqueue_scripts()
        {
            wp_enqueue_style( 'acff' );
            wp_enqueue_style( 'acff-modal' );
            wp_enqueue_script( 'acff' );
            wp_enqueue_script( 'acff-modal' );
            wp_enqueue_style( 'dashicons' );
        }
        
        public function prepare_field_display( $field )
        {
            if ( empty($field['acff_display_mode']) ) {
                return $field;
            }
            $mode = $field['acff_display_mode'];
            if ( $mode == 'hidden' ) {
                
                if ( isset( $field['wrapper']['class'] ) ) {
                    $field['wrapper']['class'] .= ' acf-hidden';
                } else {
                    $field['wrapper']['class'] = 'acf-hidden';
                }
            
            }
            return $field;
        }
        
        public function prepare_field_frontend( $field )
        {
            // bail early if no 'admin_only' setting
            if ( empty($field['only_front']) ) {
                return $field;
            }
            $render = true;
            // return false if is admin (removes field)
            if ( is_admin() && !wp_doing_ajax() ) {
                $render = false;
            }
            if ( acf_frontend_edit_mode() ) {
                $render = true;
            }
            if ( !$render ) {
                return false;
            }
            // return\
            return $field;
        }
        
        public function prepare_field_column( $field )
        {
            if ( !empty($field['start_column']) ) {
                echo  '<div style="width:' . $field['start_column'] . '%" class="acf-column">' ;
            }
            if ( isset( $field['end_column'] ) ) {
                echo  '</div>' ;
            }
            // return\
            return $field;
        }
        
        public function include_field_types()
        {
            //general
            include_once 'fields/general/related-terms.php';
            include_once 'fields/general/upload-file.php';
            include_once 'fields/general/upload-files.php';
            include_once 'fields/general/list-items.php';
            include_once 'fields/general/group.php';
            //include_once('fields/general/flexible-content.php');
            include_once 'fields/general/text.php';
            include_once 'fields/general/file.php';
            include_once 'fields/general/relationship.php';
            include_once 'fields/general/text-input.php';
            include_once 'fields/general/url-upload.php';
            include_once 'fields/general/fields-select.php';
            global  $acff_field_types ;
            if ( !empty($acff_field_types) ) {
                foreach ( $acff_field_types as $group => $fields ) {
                    if ( $group == 'options' ) {
                        $group = 'site';
                    }
                    foreach ( $fields as $field ) {
                        include_once "fields/{$group}/{$field}.php";
                    }
                }
            }
        }
        
        public function hide_field_name_setting()
        {
            global  $post ;
            if ( empty($post->post_type) ) {
                return;
            }
            
            if ( $post->post_type == 'acf-field-group' || $post->post_type == 'acf_frontend_form' ) {
                global  $acff_field_types ;
                
                if ( !empty($acff_field_types) ) {
                    echo  '<style>' ;
                    foreach ( $acff_field_types as $group => $fields ) {
                        foreach ( $fields as $field ) {
                            echo  '.acf-field-object-' . $field . ' .acf-field-setting-name{display:none}.acf-field-object-' . $field . ' .li-field-name{visibility:hidden}' ;
                        }
                    }
                    $basic_settings = array(
                        'instructions',
                        'required',
                        'conditional_logic',
                        'wrapper',
                        'acff_display_mode',
                        'field_label_hide',
                        'only_front'
                    );
                    foreach ( $basic_settings as $setting ) {
                        echo  ".acf-field-object-submit-button .acf-field-setting-{$setting}, .acf-field-object-fields-select .acf-field-setting-{$setting}{display:none}" ;
                    }
                    echo  ".acf-field-object-submit-button .acf-field-setting-label{display:none}" ;
                    echo  '</style>' ;
                }
            
            }
        
        }
        
        public function get_field_types()
        {
            $field_types = array(
                'post' => array(
                'post-title',
                'post-content',
                'post-excerpt',
                'post-slug',
                'post-status',
                'featured-image',
                'post-type',
                'post-date',
                'post-author',
                'menu-order',
                'allow-comments'
            ),
                'user' => array(
                'username',
                'user-email',
                'user-password',
                'user-password-confirm',
                'first-name',
                'last-name',
                'nickname',
                'display-name',
                'user-bio',
                'role'
            ),
                'term' => array( 'term-name', 'term-slug', 'term-description' ),
            );
            $field_types['general'] = array( 'submit-button', 'fields-select' );
            return $field_types;
        }
        
        public function echo_after_input( $field )
        {
            if ( !empty($field['after_input']) ) {
                echo  $field['after_input'] ;
            }
        }
        
        public function __construct()
        {
            global  $acff_field_types ;
            $acff_field_types = $this->get_field_types();
            add_action( 'acf/include_field_types', array( $this, 'include_field_types' ), 6 );
            add_action( 'acf/enqueue_scripts', [ $this, 'enqueue_scripts' ] );
            add_action( 'admin_footer', array( $this, 'hide_field_name_setting' ) );
            add_filter( 'acf/prepare_field', array( $this, 'prepare_field_display' ), 3 );
            add_filter( 'acf/prepare_field', array( $this, 'prepare_field_frontend' ), 3 );
            add_filter( 'acf/prepare_field', array( $this, 'prepare_field_column' ), 3 );
            add_action( 'acf/render_field', array( $this, 'echo_after_input' ) );
            //Add field settings by type
            add_action( 'acf/render_field_settings', [ $this, 'frontend_only_setting' ] );
            add_action( 'acf/render_field_settings', [ $this, 'read_only_setting' ] );
            add_filter( 'acf/get_field_types', [ $this, 'hide_acff_fields' ] );
            add_filter(
                'acf/update_value',
                [ $this, 'update_acff_values' ],
                7,
                3
            );
            add_filter( 'acf/load_field_group', [ $this, 'exclude_groups' ] );
            add_filter( 'acf/load_field', [ $this, 'load_invisible_field' ] );
            add_action( 'acf/validate_save_post', [ $this, 'before_validation' ], 1 );
            add_action( 'acf/validate_save_post', [ $this, 'skip_validation' ], 999 );
            require_once __DIR__ . '/forms/classes/form-submit.php';
            require_once __DIR__ . '/forms/classes/form-display.php';
            require_once __DIR__ . '/forms/classes/limit-submit.php';
            require_once __DIR__ . '/forms/classes/permissions.php';
            require_once __DIR__ . '/forms/helpers/addon-installer.php';
            require_once __DIR__ . '/forms/helpers/data-fetch.php';
            require_once __DIR__ . '/forms/classes/shortcodes.php';
            require_once __DIR__ . '/forms/helpers/permissions.php';
            require_once __DIR__ . '/forms/actions/action-base.php';
            //actions
            require_once __DIR__ . '/forms/actions/user.php';
            require_once __DIR__ . '/forms/actions/post.php';
            require_once __DIR__ . '/forms/actions/term.php';
            require_once __DIR__ . '/forms/actions/options.php';
        }
    
    }
    acff()->acf_extension = new ACFFrontend_Hooks();
}
