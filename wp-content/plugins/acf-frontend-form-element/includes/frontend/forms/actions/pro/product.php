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

if( ! class_exists( 'ActionProduct' ) ) :

class ActionProduct extends ActionBase {
	
	public function get_name() {
		return 'product';
	}

	public function get_label() {
		return __( 'Product', 'acf-frontend-form-element' );
	}


	public function get_fields_display( $form_field, $local_field, $element = '', $sub_fields = false, $saving = false ){
		$field_appearance = isset( $form_field['field_taxonomy_appearance'] ) ? $form_field['field_taxonomy_appearance'] : 'checkbox';
		$field_add_term = isset( $form_field['field_add_term'] ) ? $form_field['field_add_term'] : 0;
		switch( $form_field['field_type'] ){
			case 'price':
				$local_field['type'] = 'product_price';
			break;
			case 'sale_price':
				$local_field['type'] = 'product_sale_price';
			break;
			case 'description':
				$local_field['type'] = 'product_description';
				$local_field['field_type'] = isset( $form_field['editor_type'] ) ? $form_field['editor_type'] : 'wysiwyg';
			break;
			case 'main_image':
				$local_field['type'] = 'main_image';
				$local_field['default_value'] = empty( $form_field['default_featured_image']['id'] ) ? '' : $form_field['default_featured_image']['id'];
			break;			
			case 'images':
				$local_field['type'] = 'product_images';
			break;
			case 'short_description':
				$local_field['type'] = 'product_short_description';
			break;
			case 'product_categories':
				$local_field['type'] = 'related_terms';
				$local_field['taxonomy'] = 'product_cat';
				$local_field['field_type'] = $field_appearance;
				$local_field['allow_null'] = 0;
				$local_field['add_term'] = $field_add_term;
				$local_field['load_post_terms'] = 1;
				$local_field['save_terms'] = 1;
				$local_field['custom_taxonomy'] = true;
			break;
			case 'product_tags':
				$local_field['type'] = 'related_terms';
				$local_field['taxonomy'] = 'product_tag';
				$local_field['field_type'] = $field_appearance;
				$local_field['allow_null'] = 0;
				$local_field['add_term'] = $field_add_term;
				$local_field['load_post_terms'] = 1;
				$local_field['save_terms'] = 1;
				$local_field['custom_taxonomy'] = true;
			break;
			case 'tax_class':
				$local_field['type'] = 'product_tax_class';
			break;
			case 'tax_status':
				$local_field['type'] = 'product_tax_status';
			break;
			case 'product_type':
				$local_field['type'] = 'product_types';
				$local_field['default_value'] = isset( $form_field['default_product_type'] ) ? $form_field['default_product_type'] : 'simple';	
				$local_field['field_type'] = isset( $form_field['role_appearance'] ) ? $form_field['role_appearance'] : 'radio';
				$local_field['layout'] = isset( $form_field['role_radio_layout'] ) ? $form_field['role_radio_layout'] : 'vertical'; 			
			break;
			case 'is_virtual':
			case 'is_downloadable':
			case 'manage_stock':
			case 'product_enable_reviews':
				$local_field['type'] = $form_field['field_type'];
				$local_field['ui_on_text'] = isset( $form_field['ui_on'] ) ? $form_field['ui_on'] : 'Yes';
				$local_field['ui_off_text'] = isset( $form_field['ui_off'] ) ? $form_field['ui_off'] : 'No';
			break;
			case 'attributes':
				$form_field = acf_frontend_parse_args( $form_field, array(
					'button_text' => '',
					'save_button_text' => '',
					'no_value_msg' => '',
				) );

				if( is_array( $sub_fields ) ){
					$sub_settings = array(
						'field_label_on' => 0,
						'label' => '',
						'instructions' => '',
						'placeholder' => '',
						'products_page' => '',
						'for_variations' => '',
						'button_label' => '',
					);
					foreach( $sub_fields as $i => $sub_field ){
						$sub_fields[$i] = acf_frontend_parse_args( $sub_fields[$i], $sub_settings );		
					}			
				}
				$local_field['type'] = 'product_attributes';
				$local_field['button_label'] = $form_field['button_text'];
				$local_field['save_text'] = $form_field['save_button_text'];
				$local_field['no_value_msg'] = $form_field['no_value_msg'];
				$local_field['fields_settings'] = array(
					'name' => array(
						'field_label_hide' => ! $sub_fields[0]['field_label_on'],
						'label' =>  $sub_fields[0]['label'],
						'placeholder' =>  $sub_fields[0]['placeholder'],
						'instructions' =>  $sub_fields[0]['instructions'],
					),
					'locations' => array(
						'field_label_hide' => ! $sub_fields[1]['field_label_on'],
						'label' =>  $sub_fields[1]['label'],
						'instructions' =>  $sub_fields[1]['instructions'],
						'choices' => array(
							'products_page' => $sub_fields[1]['products_page'],
							'for_variations' => $sub_fields[1]['for_variations'],
						),
					),
					'custom_terms' => array(
						'field_label_hide' => ! $sub_fields[2]['field_label_on'],
						'label' =>  $sub_fields[2]['label'],
						'instructions' =>  $sub_fields[2]['instructions'],
						'button_label' =>  $sub_fields[2]['button_label'],
					),
					'terms' => array(
						'field_label_hide' => ! $sub_fields[3]['field_label_on'],
						'label' =>  $sub_fields[3]['label'],
						'instructions' =>  $sub_fields[3]['instructions'],
						'button_label' =>  $sub_fields[3]['button_label'],
					),
				);
			break;
			case 'variations':
				$form_field = acf_frontend_parse_args( $form_field, array(
					'button_text' => '',
					'save_button_text' => '',
					'no_value_msg' => '',
					'no_attrs_msg' => '',
				) );
				$local_field['type'] = 'product_variations';
				$local_field['button_label'] = $form_field['button_text'];
				$local_field['save_text'] = $form_field['save_button_text'];
				$local_field['no_value_msg'] = $form_field['no_value_msg'];
				$local_field['no_attrs_msg'] = $form_field['no_attrs_msg'];
				$local_field['fields_settings'] = $this->variation_fields_display( $element, $sub_fields, $saving );
			break;
			case 'grouped_products':
				$group_field = true;
				$local_field['type'] = 'product_grouped';
			break;
			case 'upsells':
				$group_field = true;
				$local_field['type'] = 'product_upsells';
			break;
			case 'cross_sells':
				$group_field = true;
				$local_field['type'] = 'product_cross_sells';
			break;
			case 'sku':
				$local_field['type'] = 'product_sku';
			break;					
			case 'allow_backorders':
				$local_field['type'] = 'allow_backorders';
				 $local_field['choices'] = array(
					'no' => isset( $form_field['do_not_allow'] ) ? $form_field['do_not_allow'] :__( 'Do not allow', 'woocommerce' ),
					'notify' => isset( $form_field['notify'] ) ? $form_field['notify'] : __( 'Notify', 'woocommerce' ),
					'yes' => isset( $form_field['allow'] ) ? $form_field['allow'] : __( 'Allow', 'woocommerce' ),
				); 
				$local_field['field_type'] = isset( $form_field['role_appearance'] ) ? $form_field['role_appearance'] : 'radio';
				$local_field['layout'] = isset( $form_field['role_radio_layout'] ) ? $form_field['role_radio_layout'] : 'vertical'; 
			break;				
			case 'stock_status':
				$local_field['type'] = 'stock_status';
				$local_field['choices'] = array(
					'instock' => isset( $form_field['instock'] ) ? $form_field['instock'] : __( 'In stock', 'woocommerce' ),
					'outofstock' => isset( $form_field['outofstock'] ) ? $form_field['outofstock'] : __( 'Out of stock', 'woocommerce' ),
					'onbackorder' => isset( $form_field['backorder'] ) ? $form_field['backorder'] : __( 'On backorder', 'woocommerce' ),
				);
				$local_field['field_type'] = isset( $form_field['role_appearance'] ) ? $form_field['role_appearance'] : 'radio';
				$local_field['layout'] = isset( $form_field['role_radio_layout'] ) ? $form_field['role_radio_layout'] : 'vertical';
			break;	
			case 'sold_individually':
				$local_field['type'] = 'sold_individually';
				$local_field['ui'] = 1;
				$local_field['ui_on_text'] = isset( $form_field['ui_on'] ) ? $form_field['ui_on'] : 'Yes';
				$local_field['ui_off_text'] = isset( $form_field['ui_off'] ) ? $form_field['ui_off'] : 'No';
			break;	
			default:
				$local_field['type'] = $form_field['field_type'];
		}

		if( isset( $group_field ) ){
			if( ! empty( $form_field['add_edit_product'] ) ){
				$local_field['add_edit_post'] = 1;
				if( ! empty( $form_field['add_product_text'] ) ){
					$local_field['add_post_button'] = $form_field['add_product_text'];
				}
			}else{
				$local_field['add_edit_post'] = 0;
			}

			if( ! empty( $form_field['product_authors_to_filter'] ) ){
				$user_ids = str_replace( array( '[', ']' ), '', $form_field['product_authors_to_filter'] );
				$local_field['post_author'] = explode( ',', $user_ids );
			}else{
				$local_field['post_author'] = array();
			}
		}

		return $local_field;
	}
	
	public function variation_fields_display( $element, $sub_fields, $saving ){
		$prefix = $saving ? 'acfef_' : '';

		if( is_array( $sub_fields ) ){
			$sub_settings = array(
				'field_label_on' => 1,
				'label' => '',
				'instructions' => '',
				'placeholder' => '',
				'default_value' => '',
				'default_number_value' => '',
				'default_image_value' => '',
				'required' => 0,
				'disabled' => 0,
				'hidden' => 0,
				'minimum' => '',
				'maximum' => '',
				'prepend' => '',
				'append' => '',
				'field_type' => '',
			);
			foreach( $sub_fields as $i => $sub_field ){
				$sub_fields[$i] = acf_frontend_parse_args( $sub_fields[$i], $sub_settings );		
			}			
		}

		$fields_settings = array(
			array(
				'ID' => 0,
				'prefix' => 'acf',
				'parent' => $prefix.$element. '_variables',
				'type' => 'multiple_selection',
				'key' => $element. '_variable_attributes',
				'name' => '_variable_attributes',
				'_name' => '_variable_attributes',
				'field_label_hide' => 1,
				'wrapper' => array(
					'class' => '-collapsed-target'
				),
			),
		);

		foreach( $sub_fields as $sub_field ){
			if( ! $sub_field['field_type'] ) continue;
			$field_type = $sub_field['field_type'];
			$default_label = ucwords( str_replace( '_', ' ', $field_type ) );
			$field_label = $sub_field['label'] ? $sub_field['label'] : $default_label;

			$local_field = array(
				'ID' => 0,
				'prefix' => 'acf',
				'parent' => $saving.$element. '_variables',
				'key' => $element. '_variable_' .$field_type,
				'name' => '_variable_' .$field_type,
				'_name' => '_variable_' .$field_type,
				'field_label_hide' => ! $sub_field['field_label_on'],
				'label' => $field_label,
				'instructions' => $sub_field['instructions'],
				'required' => $sub_field['required'],
				'min' => $sub_field['minimum'],
				'max' => $sub_field['maximum'],
				'prepend' => $sub_field['prepend'],
				'append' => $sub_field['append'],
				'disabled' => $sub_field['disabled'],
				'wrapper' => array(
					'class' => '',
					'id' => '',
					'width' => '',					
				),
			);

			switch( $field_type ){
				case 'description':
					$local_field['type'] = 'textarea';
					$local_field['default_value'] = $sub_field['default_value'];
				break;
				case 'image':
					$local_field['type'] = 'image';
					$local_field['default_value'] = $sub_field['default_image_value'];
				break;
				case 'price':
				case 'sale_price':	
					$local_field['type'] = 'number';
					$local_field['default_value'] = $sub_field['default_number_value'];
				break;
				case 'sku':
					$local_field['type'] = 'text';
					$local_field['placeholder'] = $sub_field['placeholder'];
					$local_field['default_value'] = $sub_field['default_value'];
				break;
				case 'manage_stock':
					$local_field['type'] = 'true_false';
					$local_field['ui'] = 1;
					$local_field['ui_on_text'] = isset( $sub_field['ui_on'] ) ? $sub_field['ui_on'] : 'Yes';
					$local_field['ui_off_text'] = isset( $sub_field['ui_off'] ) ? $sub_field['ui_off'] : 'No';
					$local_field['custom_manage_stock'] = true;
				break;
				case 'stock_quantity':
					$local_field['type'] = 'number';
					$local_field['custom_stock_quantity'] = true;
					$local_field['conditional_logic'] = [
						[
							[
								'field' => $element. '_variable_manage_stock',
								'operator' => '==',
								'value' => '1',
							]
						]
					];
				break;			
				case 'allow_backorders':
					$local_field['type'] = 'select';
					$local_field['choices'] = array(
						'no' => isset( $sub_field['do_not_allow'] ) ? $sub_field['do_not_allow'] :__( 'Do not allow', 'woocommerce' ),
						'notify' => isset( $sub_field['notify'] ) ? $sub_field['notify'] : __( 'Notify', 'woocommerce' ),
						'yes' => isset( $sub_field['allow'] ) ? $sub_field['allow'] : __( 'Allow', 'woocommerce' ),
					);
					$local_field['custom_backorders'] = true;
					$local_field['conditional_logic'] = [
						[
							[
								'field' => $element. '_variable_manage_stock',
								'operator' => '==',
								'value' => '1',
							]
						]
					];
				break;		
				case 'stock_status':
					$local_field['type'] = 'select';
					$local_field['choices'] = array(
						'instock' => isset( $sub_field['instock'] ) ? $sub_field['instock'] : __( 'In stock', 'woocommerce' ),
						'outofstock' => isset( $sub_field['outofstock'] ) ? $sub_field['outofstock'] : __( 'Out of stock', 'woocommerce' ),
						'onbackorder' => isset( $sub_field['backorder'] ) ? $sub_field['backorder'] : __( 'On backorder', 'woocommerce' ),
					);
					$local_field['custom_stock_status'] = true;
					$local_field['conditional_logic'] = [
						[
							[
								'field' => $element. '_variable_manage_stock',
								'operator' => '!=',
								'value' => '1',
							]
						]
					];
				break;	
				case 'tax_class':
					$local_field['type'] = 'select';
					$local_field['choices'] = array();
				break;
			}

			if( $sub_field['hidden'] ){
				$local_field['wrapper']['class'] .= ' acf-hidden';
			}
			$fields_settings[] = $local_field;
		}			

		return $fields_settings;
	}

	public function get_default_fields( $form ){
		$default_fields = array(
			'product_title', 'product_description', 'product_short_description', 'main_image', 'product_images'			
		);
		$this->get_valid_defaults( $default_fields, $form );	
	}

	public function get_form_builder_options( $form ){
		if( $form['acff_form_type'] != 'general' ){
			$save_to = $form['acff_form_type'];
			$display_mode = 'hidden';
		}else{
			$save_to = $form['save_to_product'];
			$display_mode = 'edit';
		}

		return array(	
			array(
				'key' => 'save_to_product',
				'field_label_hide' => 0,
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'choices' => array(            
					'edit_product' => __( 'Edit Product', 'acf-frontend-form-element' ),
					'new_product' => __( 'New Product', 'acf-frontend-form-element' ),
					'duplicate_product' => __( 'Duplicate Product', 'acf-frontend-form-element' ),
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
				'key' => 'product_to_edit',
				'label' => __( 'Product', 'acf-frontend-form-element' ),
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_product',
							'operator' => '!=',
							'value' => 'new_product',
						),
					),
				),
				'choices' => array(
					'current_product' => __( 'Current Product', 'acf-frontend-form-element' ),
					'url_query' => __( 'URL Query', 'acf-frontend-form-element' ),
					'select_product' => __( 'Specific Product', 'acf-frontend-form-element' ),
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
				'key' => 'url_query_product',
				'label' => __( 'URL Query Key', 'acf-frontend-form-element' ),
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_product',
							'operator' => '!=',
							'value' => 'new_product',
						),
						array(
							'field' => 'product_to_edit',
							'operator' => '==',
							'value' => 'url_query',
						),
					),
				),
				'placeholder' => '',
			),
			array(
				'key' => 'select_product',
				'label' => __( 'Specific Product', 'acf-frontend-form-element' ),
				'name' => 'select_product',
				'prefix' => 'form',
				'type' => 'post_object',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_product',
							'operator' => '!=',
							'value' => 'new_product',
						),
						array(
							'field' => 'product_to_edit',
							'operator' => '==',
							'value' => 'select_product',
						),
					),
				),
				'post_type' => 'product',
				'taxonomy' => '',
				'allow_null' => 0,
				'multiple' => 0,
				'return_format' => 'object',
				'ui' => 1,
			),
		);
	}

	public function register_settings_section( $widget ) {
						
		$widget->start_controls_section(
			'section_edit_product',
			[
				'label' => $this->get_label(),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);			
		$this->action_controls( $widget );

		$widget->add_control(
			'delete_button_deprecated_product',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => __( 'The delete button option is now a different widget. Search for the "Trash Button"', 'acf-frontend-form-element' ),
				'content_classes' => 'acf-fields-note',
			]
		);

		$widget->end_controls_section();
		
	}
	
	
	public function action_controls( $widget, $step = false, $type = '' ){
		if( ! empty( $widget->form_defaults['save_to_product'] ) ){
			$type = $widget->form_defaults['save_to_product'];
		}
		if( $step ){
			$condition = [
				'field_type' => 'step',
				'overwrite_settings' => 'true',
			];
		}
		$args = [
			'label' => __( 'Product', 'acf-frontend-form-element' ),
            'type'      => Controls_Manager::SELECT,
            'options'   => [				
				'edit_product' => __( 'Edit Product', 'acf-frontend-form-element' ),
				'new_product' => __( 'New Product', 'acf-frontend-form-element' ),
			],
            'default'   => 'edit_product',
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
			'save_product_data',
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
		$widget->add_control( 'save_to_product', $args );
		$condition['save_to_product'] = ['edit_product', 'new_product', 'duplicate_product'];


		$condition['save_to_product'] = [ 'edit_product', 'duplicate_product' ];

		$widget->add_control(
			'product_to_edit',
			[
				'label' => __( 'Specific Product', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'current_product',
				'options' => [
					'current_product'  => __( 'Current Product', 'acf-frontend-form-element' ),
					'url_query' => __( 'Url Query', 'acf-frontend-form-element' ),
					'select_product' => __( 'Specific Product', 'acf-frontend-form-element' ),
				],
				'condition' => $condition,
			]
		);
		$condition['product_to_edit'] = 'url_query';
		$widget->add_control(
			'url_query_product',
			[
				'label' => __( 'URL Query', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'product_id', 'acf-frontend-form-element' ),
				'default' => __( 'product_id', 'acf-frontend-form-element' ),
				'required' => true,
				'description' => __( 'Enter the URL query parameter containing the id of the product you want to edit', 'acf-frontend-form-element' ),
				'condition' => $condition,
			]
		);	
		$condition['product_to_edit'] = 'select_product';
			$widget->add_control(
				'product_select',
				[
					'label' => __( 'Product', 'acf-frontend-form-element' ),
					'type' => Controls_Manager::TEXT,
					'placeholder' => __( '18', 'acf-frontend-form-element' ),
					'description' => __( 'Enter the product ID', 'acf-frontend-form-element' ),
					'condition' => $condition,
				]
			);		
	

		unset( $condition['product_to_edit'] );
		
		$condition['save_to_product'] = 'new_product';
	
		$widget->add_control(
			'new_product_terms',
			[
				'label' => __( 'New Product Terms', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'product',
				'options' => [
					'current_term'  => __( 'Current Term', 'acf-frontend-form-element' ),
					'select_terms' => __( 'Specific Term', 'acf-frontend-form-element' ),
				],
				'condition' => $condition,
			]
		);
		$condition['new_product_terms'] = 'select_terms';
		if( ! class_exists( 'ElementorPro\Modules\QueryControl\Module' ) ){
			$widget->add_control(
				'new_product_terms_select',
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
				'new_product_terms_select',
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
		unset( $condition['save_to_product'] );
		unset( $condition['new_product_terms'] );
		$widget->add_control(
			'new_product_status',
			[
				'label' => __( 'Product Status', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'no_change',
				'options' => [
					'draft' => __( 'Draft', 'acf-frontend-form-element' ),
					'private' => __( 'Private', 'acf-frontend-form-element' ),
					'pending' => __( 'Pending Review', 'acf-frontend-form-element' ),
					'publish'  => __( 'Published', 'acf-frontend-form-element' ),
				],
				'condition' => $condition,
			]
		);
	}
	

/* 	public function create_product( $args ){	
		// Get an empty instance of the product object (defining it's type)
		$product = $this->get_product_object_type( $args['type'] );
		if( ! $product )
			return false;
	
		// Product name (Title) and slug
		$product->set_name( $args['name'] ); // Name (title).
		if( isset( $args['slug'] ) )
			$product->set_name( $args['slug'] );
	
		// Description and short description:
		$product->set_description( $args['description'] );
		$product->set_short_description( $args['short_description'] );
	
		// Status ('publish', 'pending', 'draft' or 'trash')
		$product->set_status( isset($args['status']) ? $args['status'] : 'publish' );
	
		// Visibility ('hidden', 'visible', 'search' or 'catalog')
		$product->set_catalog_visibility( isset($args['visibility']) ? $args['visibility'] : 'visible' );
	
		// Featured (boolean)
		$product->set_featured(  isset($args['featured']) ? $args['featured'] : false );
	
		// Virtual (boolean)
		$product->set_virtual( isset($args['virtual']) ? $args['virtual'] : false );
	
		// Prices
		$product->set_regular_price( $args['regular_price'] );
		$product->set_sale_price( isset( $args['sale_price'] ) ? $args['sale_price'] : '' );
		$product->set_price( isset( $args['sale_price'] ) ? $args['sale_price'] :  $args['regular_price'] );
		if( isset( $args['sale_price'] ) ){
			$product->set_date_on_sale_from( isset( $args['sale_from'] ) ? $args['sale_from'] : '' );
			$product->set_date_on_sale_to( isset( $args['sale_to'] ) ? $args['sale_to'] : '' );
		}
	
		// Downloadable (boolean)
		$product->set_downloadable(  isset($args['downloadable']) ? $args['downloadable'] : false );
		if( isset($args['downloadable']) && $args['downloadable'] ) {
			$product->set_downloads(  isset($args['downloads']) ? $args['downloads'] : array() );
			$product->set_download_limit(  isset($args['download_limit']) ? $args['download_limit'] : '-1' );
			$product->set_download_expiry(  isset($args['download_expiry']) ? $args['download_expiry'] : '-1' );
		}
	
		// Taxes
		if ( get_option( 'woocommerce_calc_taxes' ) === 'yes' ) {
			$product->set_tax_status(  isset($args['tax_status']) ? $args['tax_status'] : 'taxable' );
			$product->set_tax_class(  isset($args['tax_class']) ? $args['tax_class'] : '' );
		}
	
		// SKU and Stock (Not a virtual product)
		if( isset($args['virtual']) && ! $args['virtual'] ) {
			$product->set_sku( isset( $args['sku'] ) ? $args['sku'] : '' );
			$product->set_manage_stock( isset( $args['manage_stock'] ) ? $args['manage_stock'] : false );
			$product->set_stock_status( isset( $args['stock_status'] ) ? $args['stock_status'] : 'instock' );
			if( isset( $args['manage_stock'] ) && $args['manage_stock'] ) {
				$product->set_stock_status( $args['stock_qty'] );
				$product->set_backorders( isset( $args['backorders'] ) ? $args['backorders'] : 'no' ); // 'yes', 'no' or 'notify'
			}
		}
	
		// Sold Individually
		$product->set_sold_individually( isset( $args['sold_individually'] ) ? $args['sold_individually'] : false );
	
		// Weight, dimensions and shipping class
		$product->set_weight( isset( $args['weight'] ) ? $args['weight'] : '' );
		$product->set_length( isset( $args['length'] ) ? $args['length'] : '' );
		$product->set_width( isset(  $args['width'] ) ?  $args['width']  : '' );
		$product->set_height( isset( $args['height'] ) ? $args['height'] : '' );
		if( isset( $args['shipping_class_id'] ) )
			$product->set_shipping_class_id( $args['shipping_class_id'] );
	
		// Upsell and Cross sell (IDs)
		$product->set_upsell_ids( isset( $args['upsells'] ) ? $args['upsells'] : '' );
		$product->set_cross_sell_ids( isset( $args['cross_sells'] ) ? $args['upsells'] : '' );
	
		// Attributes et default attributes
		if( isset( $args['attributes'] ) )
			$product->set_attributes( $this->prepare_product_attributes($args['attributes']) );
		if( isset( $args['default_attributes'] ) )
			$product->set_default_attributes( $args['default_attributes'] ); // Needs a special formatting
	
		// Reviews, purchase note and menu order
		$product->set_reviews_allowed( isset( $args['reviews'] ) ? $args['reviews'] : false );
		$product->set_purchase_note( isset( $args['note'] ) ? $args['note'] : '' );
		if( isset( $args['menu_order'] ) )
			$product->set_menu_order( $args['menu_order'] );
	
		// Product categories and Tags
		if( isset( $args['category_ids'] ) )
			$product->set_category_ids( $args['category_ids'] );
		if( isset( $args['tag_ids'] ) )
			$product->set_tag_ids( $args['tag_ids'] );
	
	
		// Images and Gallery
		$product->set_image_id( isset( $args['image_id'] ) ? $args['image_id'] : "" );
		$product->set_gallery_image_ids( isset( $args['gallery_ids'] ) ? $args['gallery_ids'] : array() );
	
		## --- SAVE PRODUCT --- ##
		$product_id = $product->save();
	
		return $product_id;
	}
	
	// Utility function that returns the correct product object instance
	public function get_product_object_type( $type ) {
		// Get an instance of the WC_Product object (depending on his type)
		if( isset($args['type']) && $args['type'] === 'variable' ){
			$product = new WC_Product_Variable();
		} elseif( isset($args['type']) && $args['type'] === 'grouped' ){
			$product = new WC_Product_Grouped();
		} elseif( isset($args['type']) && $args['type'] === 'external' ){
			$product = new WC_Product_External();
		} else {
			$product = new WC_Product_Simple(); // "simple" By default
		} 
		
		if( ! is_a( $product, 'WC_Product' ) )
			return false;
		else
			return $product;
	}
	
	// Utility function that prepare product attributes before saving
	public function prepare_product_attributes( $attributes ){
		global $woocommerce;
	
		$data = array();
		$position = 0;
	
		foreach( $attributes as $taxonomy => $values ){
			if( ! taxonomy_exists( $taxonomy ) )
				continue;
	
			// Get an instance of the WC_Product_Attribute Object
			$attribute = new WC_Product_Attribute();
	
			$term_ids = array();
	
			// Loop through the term names
			foreach( $values['term_names'] as $term_name ){
				if( term_exists( $term_name, $taxonomy ) )
					// Get and set the term ID in the array from the term name
					$term_ids[] = get_term_by( 'name', $term_name, $taxonomy )->term_id;
				else
					continue;
			}
	
			$taxonomy_id = wc_attribute_taxonomy_id_by_name( $taxonomy ); // Get taxonomy ID
	
			$attribute->set_id( $taxonomy_id );
			$attribute->set_name( $taxonomy );
			$attribute->set_options( $term_ids );
			$attribute->set_position( $position );
			$attribute->set_visible( $values['is_visible'] );
			$attribute->set_variation( $values['for_variation'] );
	
			$data[$taxonomy] = $attribute; // Set in an array
	
			$position++; // Increase position
		}
		return $data;
	} */


	public function run( $form, $step = false ){	
		$record = $form['record'];
		if( empty( $record['_acf_product'] ) || empty( $record['fields']['woo_product'] ) ) return $form;

		$product_id = wp_kses( $record['_acf_product'], 'strip' );

		// allow for custom save
		$product_id = apply_filters('acf/pre_save_product', $product_id, $form);

		$element = isset( $record['_acf_element_id'] ) ? '_' . $record['_acf_element_id'] : '';
				
		switch( $form['save_to_product'] ){
			case 'edit_product':
				$product_to_edit['ID'] = $product_id;
			break;	
			case 'new_product':
				$product_to_edit['ID'] = 0;	
				$product_to_edit['post_type'] = 'product';					
			break;
			case 'duplicate_product':
				$product_to_duplicate = get_post( $product_id );
				$product_to_edit = get_object_vars( $product_to_duplicate );	
				$product_to_edit['ID'] = 0;	
				$product_to_edit['post_author'] = get_current_user_id();
			break;
			default:
				return $form;	
		}
		
		$core_fields = array(
			'product_title' => 'post_title',
			'product_slug' => 'post_name',
			'product_description' => 'post_content',
			'product_short_description' => 'post_excerpt',
			'product_date' => 'post_date',
			'product_author' => 'post_author',
			'product_menu_order' => 'menu_order',
			'product_allow_comments' => 'allow_comments',
		);
		$product_type = 'simple';

		if( ! empty( $record['fields']['woo_product'] ) ){
			foreach( $record['fields']['woo_product'] as $name => $field ){
				if( ! is_array( $field ) ) continue;

				$field_type = $field['type'];
				$value = $field['_input'];
				if( ! in_array( $field_type, array_keys( $core_fields ) ) ){
					if( $field_type == 'product_types' ){
						$product_type = $value;
						$pt_field = $field;
					}else{
						$metas[] = $field; 
					}
					continue;
				} 

				if( is_string( $field['default_value'] ) && strpos( $field['default_value'], '[' ) !== false ){
					$dynamic_value = acff()->dynamic_values->get_dynamic_values( $field['default_value'] ); 
					if( $dynamic_value ) $value = $dynamic_value;
				} 

				$product_to_edit[ $core_fields[$field_type] ] = $value;
			}
		}

		if( $form['save_to_product'] == 'duplicate_product' ){
			if( $product_to_edit[ 'post_name' ] == $product_to_duplicate->post_name ){
				$product_name = sanitize_title( $product_to_edit['post_title'] );
				if( ! acf_frontend_slug_exists( $product_name ) ){				
					$product_to_edit['post_name'] = $product_name;
				}else{
					$product_to_edit['post_name'] = acf_frontend_duplicate_slug( $product_to_duplicate->post_name );
				}
			}
		}

		if( isset( $record['_acf_status'] ) && $record['_acf_status'] == 'draft' ){
			$product_to_edit['post_status'] = 'draft';
		}else{
			$status = $form['new_product_status'];

			if( ! empty( $current_step['overwrite_settings'] ) ) $status = $current_step['new_product_status'];

			if( $status != 'no_change' ){
				$product_to_edit['post_status'] = $status;
			}elseif( $form['save_to_product'] == 'new_product' ){
				$product_to_edit['post_status'] = 'publish';
			}elseif( $form['save_to_product'] == 'edit_product' ){
				$product = wc_get_product( $product_id );
				$status = $product->get_status();
				if( $status == 'auto-draft' ) $product_to_edit['post_status'] = 'publish';
			}
			
		}	
			
		if( $product_to_edit['ID'] == 0 ){
			if( empty( $product_to_edit['post_title'] ) ){
				$product_to_edit['post_title'] = '(no-name)';
			}
			$product_id = wp_insert_post( $product_to_edit );
			update_metadata( 'post', $product_id, 'acff_form_source', $element );
		}else{
			wp_update_post( $product_to_edit );
			update_metadata( 'post', $product_id, 'acff_form_edited', $element );
		}

		if( isset( $form['product_terms'] ) && $form['product_terms'] != '' ){
			$new_terms = $form['product_terms'];					
			if( is_string( $new_terms ) ){
				$new_terms = explode( ',', $new_terms );
			}
			if( is_array( $new_terms ) ){
				foreach( $new_terms as $term_id ){
					$term = get_term( $term_id );
					if( $term ){
						wp_set_object_terms( $product_id, $term->term_id, $term->taxonomy, true );
					}
				}
			}
		}

		if( isset( $pt_field ) ){
			acf_update_value( $product_type, $product_id, $pt_field );
		}
		
		if( $form['save_to_product'] == 'duplicate_product' ){
			$taxonomies = get_object_taxonomies('product');
			foreach ($taxonomies as $taxonomy) {
			  $product_terms = wp_get_object_terms($product_to_duplicate->ID, $taxonomy, array('fields' => 'slugs'));
			  wp_set_object_terms($product_id, $product_terms, $taxonomy, false);		
			}
 
			global $wpdb;
			$product_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$product_to_duplicate->ID");
			if( count($product_meta_infos) != 0 ) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach($product_meta_infos as $meta_info) {
					$meta_key        = $meta_info->meta_key;
					$meta_value      = addslashes($meta_info->meta_value);
					$sql_query_sel[] = "SELECT $product_id, '$meta_key', '$meta_value'";
				}
				$sql_query .= implode(" UNION ALL ", $sql_query_sel);
				$wpdb->query($sql_query);
			}
		}

		if( ! empty( $metas ) ){
			foreach( $metas as $meta ){
				acf_update_value( $meta['_input'], $product_id, $meta );
			}
		}

		$form['record']['product'] = $product_id;

		do_action( 'acf_frontend/save_product', $form, $product_id );
		return $form;
	}

	public function __construct(){
		add_filter( 'acf_frontend/save_form', array( $this, 'save_form' ), 4 );
	}

}

acff()->local_actions['product'] = new ActionProduct();

endif;	