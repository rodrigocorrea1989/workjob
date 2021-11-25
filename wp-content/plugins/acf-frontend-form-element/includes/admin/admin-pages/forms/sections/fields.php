<?php

global $post, $form;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$data_types = array(
    'post' => __( 'Post', 'acf-frontend-form-element' ),
    'user' => __( 'User', 'acf-frontend-form-element' ),
    'term' => __( 'Term', 'acf-frontend-form-element' ),
    'options' => __( 'Site Options', 'acf-frontend-form-element' ),
);
if ( class_exists( 'woocommerce' ) ){
    $data_types['product'] = __( 'Product', 'acf-frontend-form-element' );
}

$form_fields = array();

$args = array(
    'post_type' => 'acf-field',
    'posts_per_page' => '-1',
    'post_parent' => $post->ID,
    'fields' => 'ids',
    'orderby' => 'menu_order', 
    'order' => 'ASC'
);

$fields_query = get_posts( $args );

if ( $fields_query ) {
    foreach( $fields_query as $field ){
        $form_fields[] = acf_get_field( $field );
    }
}
global $acff_field_types;

$form_type = $form['acff_form_type'];

acff()->form_display->render_field_wrap( array(
    'name' 			   => 'bulk_add_fields',
    'key' 			   => 'bulk_add_fields',
    'field_label_hide'  => 1,
    'type'			   => 'select',
    'allow_null'	   => 1,
    'multiple'         => 1,  
    'ui'               => 1,
    'choices'		   => acf_frontend_get_field_choices(),
    'wrapper'          => array(
        'data-close-on-select' => 1,
        'data-form-tab' => 'fields', 
    ),
    'after_input'     => '<button class="button button-primary bulk-add-fields">' .__( 'Add Fields', 'acf-frontend-form-element' ). '</button>',       
) );

// get fields
$view = array(
    'fields'	=> $form_fields,
    'parent'	=> 0
);

ob_start();
acff()->form_builder->get_view('form-field-objects', $view);
$field_objects = ob_get_contents();
ob_end_clean();	

$fields = array(
    array(
        'key' => 'custom_fields_wrapper',
        'field_label_hide' => 1,
        'type' => 'message',
        'instructions' => '',
        'new_lines' => '',
        'message' => '<div class="inside">'.$field_objects.'</div>',
        'php_code' => '1',
        'wrapper' => array(
            'width' => '',
            'class' =>'',
            'id' => 'acf-field-group-fields'
        )
    ),
    array(
        'key' => 'no_kses',
        'label' => __( 'Allow Unfiltered HTML', 'acf-frontend-form-element' ),
        'field_label_hide' => 0,
        'type' => 'true_false',
        'instructions' => '',
        'required' => 0,
        'ui' => 1,
        'wrapper' => array(
            'width' => '50',
            'class' =>'',
            'id' => ''
        )
    ),
);    

if( $form_type == 'general' ){
    $custom_fields_hide = 0;
}else{
    $custom_fields_hide = 1;
}
$fields[] = array(
    'key' => 'custom_fields_save',
    'label' => __( 'Save Custom Fields to...', 'acf-frontend-form-element' ),
    'field_label_hide' => 0,
    'type' => 'select',
    'invisible' => $custom_fields_hide,
    'instructions' => '',
    'required' => 0,
    'choices' => $data_types,
    'allow_null' => 0,
    'multiple' => 0,
    'ui' => 0,
    'return_format' => 'value',
    'ajax' => 0,
    'placeholder' => '',
    'wrapper' => array(
        'width' => '75',
        'class' =>'',
        'id' => ''
    )
);

return $fields;