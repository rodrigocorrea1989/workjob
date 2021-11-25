<?php
namespace ACFFrontend\Actions;

use ACFFrontend\Plugin;
use ACFFrontend;
use ACFFrontend\Classes\ActionBase;
use ACFFrontend\Widgets;
use Elementor\Controls_Manager;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( ! class_exists( 'ActionUser' ) ) :

class ActionUser extends ActionBase {
	
	public function get_name() {
		return 'user';
	}

	public function get_label() {
		return __( 'User', 'acf-frontend-form-element' );
	}

	public function get_fields_display( $form_field, $local_field ){

		switch( $form_field['field_type'] ){
			case 'username':
				$local_field['type'] = 'text';
				$local_field['disabled'] = isset( $form_field['allow_edit'] ) ? ! $form_field['allow_edit'] : 1;
				$local_field['custom_username'] = true;
			break;
			case 'password':
				$local_field['type'] = 'user_password';
				$local_field['edit_password'] = isset( $form_field['edit_password'] ) ? $form_field['edit_password'] : 'Edit Password';
				$local_field['cancel_edit_password'] = isset( $form_field['cancel_edit_password'] ) ? $form_field['cancel_edit_password'] : 'Cancel';
				$local_field['force_edit'] = isset( $form_field['force_edit_password'] ) ? $form_field['force_edit_password'] : 0;
				$local_field['password_strength'] = isset( $form_field['password_strength'] ) ? $form_field['password_strength'] : 3;
			break;				
			case 'confirm_password':
				$local_field['type'] = 'user_password_confirm';
			break;			
			case 'email':
				$local_field['type'] = 'email';
				if( isset( $form_field['set_as_username'] ) ){
					$local_field['set_as_username'] = true;
				}
				$local_field['custom_email'] = true;
			break;
			case 'first_name':
				$local_field['type'] = 'text';
				$local_field['custom_first_name'] = true;
			break;
			case 'last_name':
				$local_field['type'] = 'text';
				$local_field['custom_last_name'] = true;
			break;					
			case 'nickname':
				$local_field['type'] = 'text';
				$local_field['custom_nickname'] = true;
			break;				
			case 'display_name':
				$local_field['type'] = 'display_name';
				
			break;			
			case 'bio':
				$local_field['type'] = 'textarea';
				$local_field['custom_user_bio'] = true;
			break;
			case 'bio':
				$local_field['type'] = 'url';
				$local_field['custom_user_url'] = true;
			break;
			case 'role':
				$local_field['type'] = 'role';
				if( isset( $form_field['role_field_options'] ) ){
					$local_field['role_options'] = $form_field['role_field_options'];
				}
				$local_field['field_type'] = isset( $form_field['role_appearance'] ) ? $form_field['role_appearance'] : 'radio';
				$local_field['layout'] = isset( $form_field['role_radio_layout'] ) ? $form_field['role_radio_layout'] : 'vertical';
				$local_field['default_value'] = isset( $form_field['default_role'] ) ? $form_field['default_role'] : 'subscriber';
			break;
			
		}
		return $local_field;
	}
	
	public function get_default_fields( $form ){
		$default_fields = array(
			'username', 'user_email', 'user_password', 'first_name', 'last_name',			
		);
		$this->get_valid_defaults( $default_fields, $form );	
	}

	public function get_form_builder_options( $form ){
		if( $form['acff_form_type'] != 'general' ){
			$save_to = $form['acff_form_type'];
			$display_mode = 'hidden';
		}else{
			$save_to = $form['save_to_user'];
			$display_mode = 'edit';
		}

		return array(		
			array(
				'key' => 'save_to_user',
				'field_label_hide' => 0,
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'choices' => array(            
					'edit_user' => __( 'Edit User', 'acf-frontend-form-element' ),
					'new_user' => __( 'New User', 'acf-frontend-form-element' ),
				),
				'acff_display_mode' => $display_mode,	
				'value' => $save_to,
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
			),	
			array(
				'key' => 'user_to_edit',
				'label' => __( 'User to Edit', 'acf-frontend-form-element' ),
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_user',
							'operator' => '==',
							'value' => 'edit_user',
						),
					),
				),
				'choices' => array(
					'current_user' => __( 'Current User', 'acf-frontend-form-element' ),
					'current_author' => __( 'Current Author', 'acf-frontend-form-element' ),
					'post_author' => __( 'Form Post Author', 'acf-frontend-form-element' ),
					'url_query' => __( 'URL Query', 'acf-frontend-form-element' ),
					'select_user' => __( 'Specific User', 'acf-frontend-form-element' ),
				),
				'default_value' => false,
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'url_query_user',
				'label' => __( 'URL Query Key', 'acf-frontend-form-element' ),
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_user',
							'operator' => '==',
							'value' => 'edit_user',
						),
						array(
							'field' => 'user_to_edit',
							'operator' => '==',
							'value' => 'url_query',
						),
					),
				),
				'placeholder' => '',
			),
			array(
				'key' => 'select_user',
				'label' => __( 'Specific User', 'acf-frontend-form-element' ),
				'name' => 'select_user',
				'prefix' => 'form',
				'type' => 'user',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_user',
							'operator' => '==',
							'value' => 'edit_user',
						),
						array(
							'field' => 'user_to_edit',
							'operator' => '==',
							'value' => 'select_user',
						),
					),
				),
				'role' => '',
				'allow_null' => 0,
				'multiple' => 0,
				'return_format' => 'object',
				'ui' => 1,
			),
		);
	}

	public function register_settings_section( $widget ) {
		
					
		$widget->start_controls_section(
			'section_edit_user',
			[
				'label' => $this->get_label(),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->action_controls( $widget );
				
		$widget->end_controls_section();
	}
	
	
	public function action_controls( $widget, $step = false, $type = '' ){
		if( ! empty( $widget->form_defaults['save_to_user'] ) ){
			$type = $widget->form_defaults['save_to_user'];
		}

		if( $step ){
			$condition = [
				'field_type' => 'step',
				'overwrite_settings' => 'true',
			];
		}
		$args = [
			'label' => __( 'User', 'acf-frontend-form-element' ),
            'type'      => Controls_Manager::SELECT,
            'options'   => [				
				'edit_user' => __( 'Edit User', 'acf-frontend-form-element' ),
				'new_user' => __( 'New User', 'acf-frontend-form-element' ),
			],
            'default'   => 'edit_user',
        ];
		if( $step ){
			$condition = [
				'field_type' => 'step',
				'overwrite_settings' => 'true',
			];
			$args['condition'] = $condition;
		}else{
			$condition = array();
		}
		if( $type ){
			$args = [
				'type' => Controls_Manager::HIDDEN,
				'default' => $type,
			];
		}
		$widget->add_control(
			'save_user_data',
			[
				'label' => __( 'Save Data After...', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'default' => '',
				'options' => [
					'require_approval' => __( 'Admin Approval', ACFF_NS ),
                    'verify_email' => __( 'Email is Verified', ACFF_NS ),					'verify_email' => __( 'Email is Verified', 'acf-frontend-form-element' ),
				],
			]
		);
		$widget->add_control( 'save_to_user', $args );
		
		$condition['save_to_user'] = [ 'edit_user', 'delete_user'];

		$widget->add_control(
			'user_to_edit',
			[
				'label' => __( 'Specific User', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'current_user',
				'options' => [
					'current_user'  => __( 'Current User', 'acf-frontend-form-element' ),
					'current_author'  => __( 'Current Author', 'acf-frontend-form-element' ),
					'url_query' => __( 'URL Query', 'acf-frontend-form-element' ),
					'select_user' => __( 'Specific User', 'acf-frontend-form-element' ),
				],
				'condition' => $condition,
			]
		);
		$condition['user_to_edit'] = 'url_query';
		$widget->add_control(
			'url_query_user',
			[
				'label' => __( 'URL Query', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'user_id', 'acf-frontend-form-element' ),
				'default' => __( 'user_id', 'acf-frontend-form-element' ),
				'description' => __( 'Enter the URL query parameter containing the id of the user you want to edit', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);	
		$condition['user_to_edit'] = 'select_user';
			$widget->add_control(
				'user_select',
				[
					'label' => __( 'User', 'acf-frontend-form-element' ),
					'type' => Controls_Manager::TEXT,
					'placeholder' => __( '18', 'acf-frontend-form-element' ),
					'default' => get_current_user_id(),
					'description' => __( 'Enter user id', 'acf-frontend-form-element' ),
					'condition' => $condition,
				]
			);		

		unset( $condition['user_to_edit'] );
		$condition['save_to_user'] = 'new_user';

		$widget->add_control(
			'username_default',
			[
				'label' => __( 'Default Username', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'generate',
				'options' => [
					'generate' => __( 'Generate Random Number', 'acf-frontend-form-element' ),
					'id' => __( 'Generate From ID', 'acf-frontend-form-element' )
				],
				'description' => __( 'Will be overwritten if your form has a "Username" field', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);
		$widget->add_control(
			'username_prefix',
			[
				'label' => __( 'Username Prefix', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'user_',
				'description' => __( 'Please enter only lowercase latin letters, numbers, @, -, . and _', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);
		$widget->add_control(
			'username_suffix',
			[
				'label' => __( 'Username Suffix', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'description' => __( 'Please enter only lowercase latin letters, numbers, @, -, . and _', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);
		$widget->add_control(
			'display_name_default',
			[
				'label' => __( 'Default Display Name', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'user_login' => __( 'Username', 'acf-frontend-form-element' ),
					'user_email' => __( 'Email', 'acf-frontend-form-element' ),
					'first_name' => __( 'First Name', 'acf-frontend-form-element' ),
					'last_name' => __( 'Last Name', 'acf-frontend-form-element' ),
					'first_last' => __( 'First and Last Name', 'acf-frontend-form-element' ),
					'nickname' => __( 'Nickname', 'acf-frontend-form-element' ),
				],
				'description' => __( 'Will be overwritten if your form has a "Display Name" field', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);

		$widget->add_control(
			'new_user_role',
			[
				'label' => __( 'New User Role', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'subscriber',
				'options' => acf_frontend_get_user_roles( ['administrator'] ),
				'condition' => $condition,
			]
		);
		
		$widget->add_control(
			'hide_admin_bar',
			[
				'label' => __( 'Hide WordPress Admin Area?', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Hide', 'acf-frontend-form-element' ),
				'label_off' => __( 'Show','acf-frontend-form-element' ),
				'return_value' => 'true',
				'condition' => $condition,
			]
		);
		if( ! $step ){

			$widget->add_control(
				'login_user',
				[
					'label' => __( 'Log in as new user?', 'acf-frontend-form-element' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', 'acf-frontend-form-element' ),
					'label_off' => __( 'No','acf-frontend-form-element' ),
					'return_value' => 'true',
					'condition' => $condition,			
				]
			);			
		}
		
		$condition['save_to_user'] = ['new_user', 'edit_user'];

		$widget->add_control(
			'user_manager',
			[
				'label' => __( 'Managing User', 'acf-frontend-form-element' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none' => __( 'No Manager','acf-frontend-form-element' ),
					'current_user' => __( 'Current User','acf-frontend-form-element' ),
					//'current_author' => __( 'Current Post Author','acf-frontend-form-element' ),
					'select_user' => __( 'Specific User','acf-frontend-form-element' ),
				],
				'description' => __( 'Who will be in charge of editing this user\'s data?', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);
		$condition['user_manager'] = 'select_user';
			$widget->add_control(
				'manager_select',
				[
					'label' => __( 'User', 'acf-frontend-form-element' ),
					'type' => Controls_Manager::TEXT,
					'placeholder' => __( '18', 'acf-frontend-form-element' ),
					'default' => get_current_user_id(),
					'description' => __( 'Enter user id', 'acf-frontend-form-element' ),
					'condition' => $condition,
				]
			);		
		
	}
		
	public function run( $form, $step = false ){	
		$record = $form['record'];
		if( empty( $record['_acf_user'] )|| empty( $record['fields']['user'] ) ) return $form;

		$user_id = wp_kses( $record['_acf_user'], 'strip' );

		// allow for custom save
		$user_id = apply_filters('acf/pre_save_user', $user_id, $form);
		
		$username_generated = false;
		$user_to_insert = [];			 
		$metas = array();

		$element_id = isset( $record['_acf_element_id'] ) ? '_' . $record['_acf_element_id'] : '';

		$core_fields = array(
			'username', 'user_password', 'user_email', 'first_name',  'last_name', 'nickname', 'display_name', 'role'    
		);

		if( ! empty( $record['fields']['user'] ) ){
			foreach( $record['fields']['user'] as $name => $field ){
				if( ! is_array( $field ) ) continue;

				$field_type = $field['type'];
				$value = $field['_input'];
				
				if( ! in_array( $field_type, $core_fields ) || ( $field_type == 'username' && is_numeric( $user_id ) ) ){
					$metas[] = $field; 
					continue;
				}

				if( $field['key'] == 'display_name' && $value ){
					$form['record']['custom_display_name'] = 1;
				} 

				if( ! empty( $field['save_prepend'] ) ) $value = $field['prepend'] . $value;
				if( ! empty( $field['save_append'] ) ) $value .= $field['append'];
				if( ! empty( $field['set_as_username'] ) ) $user_to_insert[ 'user_login' ] = $value;

				if( $field_type == 'username' ){
					$submit_key = 'user_login';
				}elseif( $field_type == 'user_password' ){
					$submit_key = 'user_pass';
				}else{
					$submit_key = $field_type;
				}

				if( is_string( $field['default_value'] ) && strpos( $field['default_value'], '[' ) !== false ){
					$dynamic_value = acff()->dynamic_values->get_dynamic_values( $field['default_value'] ); 
					if( $dynamic_value ) $value = $dynamic_value;
				} 
			
				$user_to_insert[$submit_key] = $value;
			}
		}
		
		if( $user_id == 'add_user' ){
			if( empty( $user_to_insert['user_login'] ) ){ 
				$prefix = sanitize_title( $form['username_prefix'] );
				$suffix = sanitize_title( $form['username_suffix'] );
				$user_to_insert['user_login'] = $this->generate_username( $prefix, $suffix );
				$username_generated = true;
			}
	
			if( empty( $user_to_insert['user_pass'] ) ){ 
				$user_to_insert['user_pass'] = wp_generate_password();
			}	
			if( empty( $user_to_insert['role'] ) ){ 
				$user_to_insert['role'] = $form['new_user_role'];
			}		
			$user_to_insert['show_admin_bar_front'] = $form['hide_admin_bar'];

			$user_id = wp_insert_user( $user_to_insert );  

			if ( is_wp_error( $user_id ) ) return $form;
			update_user_meta( $user_id, 'hide_admin_area', $form['hide_admin_bar'] );
			$form['record']['added_new_user'] = 1;
		}else{
			$user_to_insert['ID'] = $user_id;
			wp_update_user( $user_to_insert );
		}			

		if( isset( $form['user_manager'] ) ){
			update_user_meta( $user_id, 'acff_manager', $form['user_manager'] );
		}

		if( $username_generated && $form['username_default'] == 'id' ){
			$prefix = sanitize_title( $form['username_prefix'] );
			$suffix = sanitize_title( $form['username_suffix'] );
			$new_username = sprintf( '%s%s%s', $prefix, $user_id, $suffix );
			if ( ! username_exists( $new_username ) ) {
				global $wpdb;
				$wpdb->update( $wpdb->users, array( 'user_login' => $new_username ), ['ID' => $user_id ] );
				update_user_meta( $user_id, 'nickname', $new_username );
				wp_update_user( ['ID' => $user_id, 'display_name' => $new_username ] );
			}
		}
		if( ! empty( $metas ) ){
			foreach( $metas as $meta ){
				acf_update_value( $meta['_input'], 'user_'.$user_id, $meta );
			}
		}

		$form['record']['user'] = $user_id;

		if( ! empty( $form['login_user'] ) ){
			$user = get_user_by( 'ID', $user_id );
			if( !empty( $user->user_login ) ){
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
			}
		}

		do_action( 'acf_frontend/save_user', $form, $user_id );

		return $form;
	}  
	
	public function generate_username( $prefix = '', $suffix = '' ) {	
		static $i;
		if ( null === $i ) {
			$i = 1;
		} else {
			$i ++;
		}
		$new_username = sprintf( '%s%s%s', $prefix, $i, $suffix );
		if ( ! username_exists( $new_username ) ) {
			return $new_username;
		} else {
			return $this->generate_username( $prefix, $suffix );
		}
	}
	

	public function __construct(){
		add_filter( 'acf_frontend/save_form', array( $this, 'save_form' ), 4 );
	}
}
acff()->local_actions['user'] = new ActionUser();

endif;	