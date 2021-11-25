<?php
namespace ACFFrontend\Actions;

use ACFFrontend\Plugin;
use ACFFrontend\Classes\ActionBase;
use ACFFrontend\Widgets;
use Elementor\Controls_Manager;
use ElementorPro\Modules\QueryControl\Module as Query_Module;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( ! class_exists( 'ActionPost' ) ) :

class ActionPost extends ActionBase {
	
	public function get_name() {
		return 'post';
	}

	public function get_label() {
		return __( 'Post', 'acf-frontend-form-element' );
	}

	public function get_fields_display( $form_field, $local_field, $element = ''){
		$field_appearance = isset( $form_field['field_taxonomy_appearance'] ) ? $form_field['field_taxonomy_appearance'] : 'checkbox';
		$field_add_term = isset( $form_field['field_add_term'] ) ? $form_field['field_add_term'] : 0;

		switch( $form_field['field_type'] ){
			case 'title':
				$local_field['type'] = 'post_title';
			break;
			case 'slug':
				$local_field['type'] = 'post_slug';
				$local_field['wrapper']['class'] .= ' post-slug-field';
			break;
			case 'content':
				$local_field['type'] = 'post_content';
				$local_field['field_type'] = isset( $form_field['editor_type'] ) ? $form_field['editor_type'] : 'wysiwyg';
			break;
			case 'featured_image':
				$local_field['type'] = 'featured_image';
				$local_field['default_value'] = empty( $form_field['default_featured_image']['id'] ) ? '' : $form_field['default_featured_image']['id'];
			break;
			case 'excerpt':
				$local_field['type'] = 'post_excerpt';
			break;
			case 'author':
				$local_field['type'] = 'post_author';
			break;
			case 'published_on':
				$local_field['type'] = 'post_date';
			break;
			case 'menu_order':
				$local_field['type'] = 'menu_order';
			break;
			case 'taxonomy':
				$taxonomy = isset( $form_field['field_taxonomy'] ) ? $form_field['field_taxonomy'] : 'category';
				$local_field['type'] = 'taxonomy';
				$local_field['taxonomy'] = $taxonomy;
				$local_field['field_type'] = $field_appearance;
				$local_field['allow_null'] = 0;
				$local_field['add_term'] = $field_add_term;
				$local_field['load_post_terms'] = 1;
				$local_field['save_terms'] = 1;
				$local_field['custom_taxonomy'] = true;
			break;
			case 'categories':
				$local_field['type'] = 'taxonomy';
				$local_field['taxonomy'] = 'category';
				$local_field['field_type'] = $field_appearance;
				$local_field['allow_null'] = 0;
				$local_field['add_term'] = $field_add_term;
				$local_field['load_post_terms'] = 1;
				$local_field['save_terms'] = 1;
				$local_field['custom_taxonomy'] = true;
			break;
			case 'tags':
				$local_field['type'] = 'taxonomy';
				$local_field['taxonomy'] = 'post_tag';
				$local_field['field_type'] = $field_appearance;
				$local_field['allow_null'] = 0;
				$local_field['add_term'] = $field_add_term;
				$local_field['load_post_terms'] = 1;
				$local_field['save_terms'] = 1;
				$local_field['custom_taxonomy'] = true;
			break;
			case 'post_type':				
				$local_field['type'] = 'post_type';
				$local_field['field_type'] = isset( $form_field['role_appearance'] ) ? $form_field['role_appearance'] : 'select';
				$local_field['layout'] = isset( $form_field['role_radio_layout'] ) ? $form_field['role_radio_layout'] : 'vertical';
				$local_field['default_value'] = isset( $form_field['default_post_type'] ) ? $form_field['default_post_type'] : 'post';
				if( isset( $form_field[ 'post_type_field_options' ] ) ){
					$local_field['post_type_options'] = $form_field['post_type_field_options']; 
				}
			break;
		}
		return $local_field;
	}

	public function get_default_fields( $form ){
		$default_fields = array(
			'post_title', 'post_content', 'post_excerpt', 'featured_image'			
		);
		$this->get_valid_defaults( $default_fields, $form );	
	}

	public function get_form_builder_options( $form ){
		if( $form['acff_form_type'] != 'general' ){
			$save_to = $form['acff_form_type'];
			$display_mode = 'hidden';
		}else{
			$save_to = $form['save_to_post'];
			$display_mode = 'edit';
		}

		$options = array(		
			array(
				'key' => 'save_to_post',
				'field_label_hide' => 1,
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'choices' => array(            
					'edit_post' => __( 'Edit Post', 'acf-frontend-form-element' ),
					'new_post' => __( 'New Post', 'acf-frontend-form-element' ),
					'duplicate_post' => __( 'Duplicate Post', 'acf-frontend-form-element' ),
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
				'key' => 'new_post_type',
				'label' => __( 'Post Type', 'acf-frontend-form-element' ),
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_post',
							'operator' => '==',
							'value' => 'new_post',
						),
					),
				),
				'choices' => acf_get_pretty_post_types(),
				'default_value' => false,
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'post_to_edit',
				'label' => __( 'Post', 'acf-frontend-form-element' ),
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_post',
							'operator' => '!=',
							'value' => 'new_post',
						),
					),
				),
				'choices' => array(
					'current_post' => __( 'Current Post', 'acf-frontend-form-element' ),
					'url_query' => __( 'URL Query', 'acf-frontend-form-element' ),
					'select_post' => __( 'Specific Post', 'acf-frontend-form-element' ),
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
				'key' => 'url_query_post',
				'label' => __( 'URL Query Key', 'acf-frontend-form-element' ),
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_post',
							'operator' => '!=',
							'value' => 'new_post',
						),
						array(
							'field' => 'post_to_edit',
							'operator' => '==',
							'value' => 'url_query',
						),
					),
				),
				'placeholder' => '',
			),
			array(
				'key' => 'select_post',
				'label' => __( 'Specific Post', 'acf-frontend-form-element' ),
				'name' => 'select_post',
				'prefix' => 'form',
				'type' => 'post_object',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_post',
							'operator' => '!=',
							'value' => 'new_post',
						),
						array(
							'field' => 'post_to_edit',
							'operator' => '==',
							'value' => 'select_post',
						),
					),
				),
				'post_type' => '',
				'taxonomy' => '',
				'allow_null' => 0,
				'multiple' => 0,
				'return_format' => 'object',
				'ui' => 1,
			),	
		);

		return $options;
	}

	public function register_settings_section( $widget ) {		
						
		$widget->start_controls_section(
			'section_edit_post',
			[
				'label' => $this->get_label(),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->action_controls( $widget );		
				
		$widget->end_controls_section();
	}
	
	public function action_controls( $widget, $step = false, $type = '' ){
		if( ! empty( $widget->form_defaults['save_to_post'] ) ){
			$type = $widget->form_defaults['save_to_post'];
		}

		if( $step ){
			$condition = [
				'field_type' => 'step',
				'overwrite_settings' => 'true',
			];
		}
		$args = [
            'label' => __( 'Post', 'acf-frontend-form-element' ),
            'type'      => Controls_Manager::SELECT,
            'options'   => [				
				'edit_post' => __( 'Edit Post', 'acf-frontend-form-element' ),
				'new_post' => __( 'New Post', 'acf-frontend-form-element' ),
			],
            'default'   => 'edit_post',
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
			'save_post_data',
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

		$widget->add_control( 'save_to_post', $args );

		//add option to determine when the post will be save: 1. on form submit
		//2. when user confirms email  3. when admin approves submission
		//4. on woocommerce purchase  
		
		$condition['save_to_post'] = ['edit_post', 'delete_post', 'duplicate_post'];

		$widget->add_control(
			'post_to_edit',
			[
				'label' => __( 'Specific Post', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'current_post',
				'options' => [
					'current_post'  => __( 'Current Post', 'acf-frontend-form-element' ),
					'url_query' => __( 'Url Query', 'acf-frontend-form-element' ),
					'select_post' => __( 'Specific Post', 'acf-frontend-form-element' ),
				],
				'condition' => $condition,
			]
		);

		$condition['post_to_edit'] = 'url_query';
		$widget->add_control(
			'url_query_post',
			[
				'label' => __( 'URL Query', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'post_id', 'acf-frontend-form-element' ),
				'default' => __( 'post_id', 'acf-frontend-form-element' ),
				'required' => true,
				'description' => __( 'Enter the URL query parameter containing the id of the post you want to edit', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);	
		$condition['post_to_edit'] = 'select_post';
		$widget->add_control(
			'post_select',
			[
				'label' => __( 'Post', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( '18', 'acf-frontend-form-element' ),
				'description' => __( 'Enter the post ID', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);		
	
		unset( $condition['post_to_edit'] );
		$condition['save_to_post'] = 'new_post';

		$post_type_choices = acf_frontend_get_post_type_choices();    
		
		$widget->add_control(
			'new_post_type',
			[
				'label' => __( 'New Post Type', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'post',
				'options' => $post_type_choices,
				'condition' => $condition,
			]
		);
		$widget->add_control(
			'new_post_terms',
			[
				'label' => __( 'New Post Terms', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'post',
				'options' => [
					'current_term'  => __( 'Current Term', 'acf-frontend-form-element' ),
					'select_terms' => __( 'Specific Term', 'acf-frontend-form-element' ),
				],
				'condition' => $condition,
			]
		);

		$condition['new_post_terms'] = 'select_terms';
		if( ! class_exists( 'ElementorPro\Modules\QueryControl\Module' ) ){
			$widget->add_control(
				'new_terms_select',
				[
					'label' => __( 'Terms', 'acf-frontend-form-element' ),
					'type' => Controls_Manager::TEXT,
					'placeholder' => __( '18, 12, 11', 'acf-frontend-form-element' ),
					'description' => __( 'Enter the a comma-seperated list of term ids', 'acf-frontend-form-element' ),
					'condition' => $condition,
				]
			);		
		}else{		
			$widget->add_control(
				'new_terms_select',
				[
					'label' => __( 'Terms', 'acf-frontend-form-element' ),
					'type' => Query_Module::QUERY_CONTROL_ID,
					'label_block' => true,
					'autocomplete' => [
						'object' => Query_Module::QUERY_OBJECT_TAX,
						'display' => 'detailed',
					],		
					'multiple' => true,
					'condition' => $condition,
				]
			);
		}
		
		unset( $condition['new_post_terms'] );

		$condition['save_to_post'] = [ 'new_post', 'edit_post', 'duplicate_post' ];

		$widget->add_control(
			'new_post_status',
			[
				'label' => __( 'Post Status', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'no_change',
				'options' => [
					'draft' => __( 'Draft', 'acf-frontend-form-element' ),
					'private' => __( 'Private', 'acf-frontend-form-element' ),
					'pending' => __( 'Pending Review', 'acf-frontend-form-element' ),
					'publish'  => __( 'Published', 'acf-frontend-form-element' ),
				],
				'condition' => $condition
			]
		);	
		$condition['new_post_status!'] = 'draft';

		$widget->add_control(
			'save_progress_button',
			[
				'label' => __( 'Save Progress Option', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'acf-frontend-form-element' ),
				'label_off' => __( 'No','acf-frontend-form-element' ),
				'return_value' => 'true',
				'condition' => $condition,
			]
		);
		$condition['save_progress_button'] = 'true';
		$widget->add_control(
			'saved_draft_text',
			[
				'label' => __( 'Save Progress Text', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Save Progress', 'acf-frontend-form-element' ),
				'placeholder' => __( 'Save Progress', 'acf-frontend-form-element' ),
				'dynamic' => [
					'active' => true,
				],				
				'condition' => $condition,
			]
		);
	
 		$widget->add_control(
			'saved_draft_message',
			[
				'label' => __( 'Progress Saved Message', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => __( 'Progress Saved', 'acf-frontend-form-element' ),
				'placeholder' => __( 'Progress Saved', 'acf-frontend-form-element' ),
				'dynamic' => [
					'active' => true,
				],
				'condition' => $condition,
			]
		);  

		$condition['save_to_post'] = 'new_post';
		$widget->add_control(
			'saved_drafts',
			[
				'label' => __( 'Show Saved Drafts Selection', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'acf-frontend-form-element' ),
				'label_off' => __( 'No','acf-frontend-form-element' ),
				'return_value' => 'true',
				'condition' => $condition,
				'seperator' => 'before',
			]
		);
		$condition['saved_drafts'] = 'true';
		$widget->add_control(
			'saved_drafts_label',
			[
				'label' => __( 'Edit Draft Label', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Edit a draft', 'acf-frontend-form-element' ),
				'placeholder' => __( 'Edit a draft', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);		
		$widget->add_control(
			'saved_drafts_new',
			[
				'label' => __( 'New Draft Text', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( '&mdash; New Post &mdash;', 'acf-frontend-form-element' ),
				'placeholder' => __( '&mdash; New Post &mdash;', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);
		unset( $condition['saved_drafts'] );

		$condition['save_to_post'] = 'edit_post';
		$widget->add_control(
			'saved_revisions',
			[
				'label' => __( 'Show Saved Revisions Selection', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'acf-frontend-form-element' ),
				'label_off' => __( 'No','acf-frontend-form-element' ),
				'return_value' => 'true',
				'condition' => $condition,
				'seperator' => 'before',
			]
		);
 		$condition['saved_revisions'] = 'true';
		$widget->add_control(
			'saved_revisions_label',
			[
				'label' => __( 'Edit Revisions Label', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( 'Edit a revision', 'acf-frontend-form-element' ),
				'placeholder' => __( 'Edit a revision', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);		
		$widget->add_control(
			'saved_revisions_edit_main',
			[
				'label' => __( 'Edit Post Text', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'default' => __( '&mdash; Edit Main Post &mdash;', 'acf-frontend-form-element' ),
				'placeholder' => __( '&mdash; Edit Main Post &mdash;', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);
 
	}

	public function run( $form, $step = false ){	
		$record = $form['record'];
		if( empty( $record['_acf_post'] ) || empty( $record['fields']['post'] ) ) return $form;

		$post_id = wp_kses( $record['_acf_post'], 'strip' );

		// allow for custom save
		$post_id = apply_filters('acf/pre_save_post', $post_id, $form);
	
		$post_to_edit = array();

		$element = isset( $record['_acf_element_id'] ) ? '_' . $record['_acf_element_id'] : '';

		if( isset( $form['step_index'] ) ){
			$current_step = $form['fields']['steps'][$form['step_index']];
		}

		$old_status = '';
		$post_to_duplicate = false;

		switch( $form['save_to_post'] ){
			case 'edit_post':
				if( get_post_type( $post_id ) == 'revision' && isset( $record['_acf_status'] ) && $record['_acf_status'] == 'publish' ){
					$revision_id = $post_id;
					$post_id = wp_get_post_parent_id( $revision_id );
					wp_delete_post_revision( $revision_id );
				}
				$old_status = get_post_field( 'post_status', $post_id );
				$post_to_edit['ID'] = $post_id;
			break;
			case 'new_post':
				$post_to_edit['ID'] = 0;	
				$post_to_edit['post_type'] = $form['new_post_type'];
				if( ! empty( $current_step['overwrite_settings'] ) ) $post_to_edit['post_type'] = $current_step['new_post_type'];	
			break;
			case 'duplicate_post':				
				$post_to_duplicate = get_post( $post_id );
				$post_to_edit = get_object_vars( $post_to_duplicate );	
				$post_to_edit['ID'] = 0;	
				$post_to_edit['post_author'] = get_current_user_id();
			break;
			default: 
				return $form;
		}
		
		$metas = array();
		
		$core_fields = array(
			'post_title',
			'post_slug',
			'post_status',
			'post_content',
			'post_author',
			'post_excerpt',
			'post_date',
			'post_type',
			'menu_order',
			'allow_comments',
		);

		if( ! empty( $record['fields']['post'] ) ){
			foreach( $record['fields']['post'] as $name => $field ){
				if( ! is_array( $field ) ) continue;

				$field_type = $field['type'];
				$value = $field['_input'];
				
				if( ! in_array( $field_type, $core_fields ) ){
					$metas[] = $field; 
					continue;
				} 
				if( is_string( $value ) && strpos( $value, '[' ) !== false ){
					$dynamic_value = acff()->dynamic_values->get_dynamic_values( $field['default_value'] ); 
					if( $dynamic_value ) $value = $dynamic_value;
				} 

				$submit_key = $field_type == 'post_slug' ? 'post_name' : $field_type;

				$post_to_edit[ $submit_key ] = $value;
			}
		}

		if( $form['save_to_post'] == 'duplicate_post' ){
			if( $post_to_edit[ 'post_name' ] == $post_to_duplicate->post_name ){
				$post_name = sanitize_title( $post_to_edit['post_title'] );
				if( ! acf_frontend_slug_exists( $post_name ) ){				
					$post_to_edit['post_name'] = $post_name;
				}else{
					$post_to_edit['post_name'] = acf_frontend_duplicate_slug( $post_to_duplicate->post_name );
				}
			}
		}

		if( empty( $post_to_edit['post_status'] ) ){
			if( isset( $current_step ) && empty( $form['last_step'] ) && empty( $current_step['overwrite_settings'] ) ){
				$post_to_edit['post_status'] = 'auto-draft';
			}else{		
				if( isset( $record['_acf_status'] ) && $record['_acf_status'] == 'draft' ){
					$post_to_edit['post_status'] = 'draft';
				}else{
					$status = $form['new_post_status'];

					if( ! empty( $current_step['overwrite_settings'] ) ) $status = $current_step['new_post_status'];

					if( $status != 'no_change' ){
						$post_to_edit['post_status'] = $status;
					}elseif( empty( $old_status ) || $old_status == 'auto-draft' ){
						$post_to_edit['post_status'] = 'publish';
					}
				}
			}
		}

		$form = $this->save_post( $form, $post_to_edit, $metas, $element, $post_to_duplicate );
		return $form;
	}

	public function save_post( $form, $post_to_edit, $metas, $element, $post_to_duplicate ){
		if( $post_to_edit['ID'] == 0 ){
			$post_to_edit['meta_input'] = array(
				'acff_form_source' => str_replace( '_', '', $element ),
			);
			if( empty( $post_to_edit['post_title'] ) ){
				$post_to_edit['post_title'] = '(no-name)';
			}

			if( $post_to_edit['post_type'] == 'acf_frontend_form' ){
				$post_to_edit['post_name'] = 'form_' . uniqid();
				$form_type = $form['record']['acf']['acff_form_types'];
				$post_to_edit['meta_input']['acff_form_type'] = $form_type;
				if( $form_type != 'general' ){
					$create_fields = str_replace( array( 'new_', 'edit_', 'duplicate_' ), '', $form_type );
				}
			}
			
			$post_id = wp_insert_post( $post_to_edit );

			if( ! empty( $create_fields ) ){
				acff()->local_actions[$create_fields]->get_default_fields( $post_id );
			}else{
				$this->get_valid_defaults( array( 'text' ), $post_id );
			}

		}else{
			$post_id = $post_to_edit['ID'];
			wp_update_post( $post_to_edit );
			update_metadata( 'post', $post_id, 'acff_form_edited', $element );
		}

		if( isset( $form['post_terms'] ) && $form['post_terms'] != '' ){
			$new_terms = $form['post_terms'];				
			if( is_string( $new_terms ) ){
				$new_terms = explode( ',', $new_terms );
			}
			if( is_array( $new_terms ) ){
				foreach( $new_terms as $term_id ){
					$term = get_term( $term_id );
					if( $term ){
						wp_set_object_terms( $post_id, $term->term_id, $term->taxonomy, true );
					}
				}
			}
		}

		if( $form['save_to_post'] == 'duplicate_post' ){
			$taxonomies = get_object_taxonomies($post_to_duplicate->post_type);
			foreach ($taxonomies as $taxonomy) {
			  $post_terms = wp_get_object_terms($post_to_duplicate->ID, $taxonomy, array('fields' => 'slugs'));
			  wp_set_object_terms($post_id, $post_terms, $taxonomy, false);		
			}
 
			global $wpdb;
			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_to_duplicate->ID");
			if( count($post_meta_infos) != 0 ) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach($post_meta_infos as $meta_info) {
					$meta_key        = $meta_info->meta_key;
					$meta_value      = addslashes($meta_info->meta_value);
					$sql_query_sel[] = "SELECT $post_id, '$meta_key', '$meta_value'";
				}
				$sql_query .= implode(" UNION ALL ", $sql_query_sel);
				$wpdb->query($sql_query);
			}
		}

		if( ! empty( $metas ) ){
			foreach( $metas as $meta ){
				acf_update_value( $meta['_input'], $post_id, $meta );
			}
		}
		$form['record']['post'] = $post_id;
		
		do_action( 'acf_frontend/save_post', $form, $post_id );
		return $form;
	}

	public function __construct(){
		add_filter( 'acf_frontend/save_form', array( $this, 'save_form' ), 4 );
	}
	
}

acff()->local_actions['post'] = new ActionPost();

endif;	