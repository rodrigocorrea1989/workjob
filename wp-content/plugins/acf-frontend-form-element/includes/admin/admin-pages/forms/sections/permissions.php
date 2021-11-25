<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(	
    array(
        'key' => 'not_allowed',
        'label' => __( 'No Permissions Message', 'acf-frontend-form-element' ),
        'type' => 'select',
        'instructions' => '',
        'required' => 0,
        'choices' => array(
            'show_nothing'   => __( 'None', 'acf-frontend-form-element' ),
			'show_message'   => __( 'Message', 'acf-frontend-form-element' ),
			'custom_content' => __( 'Custom Content', 'acf-frontend-form-element' ),
        ),
    ),	
    array(
        'key' => 'not_allowed_message',
        'label' => __( 'Message', 'acf-frontend-form-element' ),
        'type' => 'textarea',
        'instructions' => '',
        'required' => 0,
        'rows' => 3,
        'placeholder' => __( 'You do not have the proper permissions to view this form', 'acf-frontend-form-element' ),
        'default_value' => __( 'You do not have the proper permissions to view this form', 'acf-frontend-form-element' ),
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'not_allowed',
                    'operator' => '==',
                    'value' => 'show_message',
                ),
            ),
        ),
    ),	
    array(
        'key' => 'not_allowed_content',
        'label' => __( 'Content', 'acf-frontend-form-element' ),
        'type' => 'wysiwyg',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'not_allowed',
                    'operator' => '==',
                    'value' => 'custom_content',
                ),
            ),
        ),
    ),	
    array(
        'key' => 'who_can_see',
        'label' => __( 'Who Can See This...', 'acf-frontend-form-element' ),
        'type' => 'select',
        'instructions' => '',
        'required' => 0,
        'choices' => array(
            'logged_in'  => __( 'Only Logged In Users', 'acf-frontend-form-element' ),
            'logged_out' => __( 'Only Logged Out', 'acf-frontend-form-element' ),
            'all'        => __( 'All Users', 'acf-frontend-form-element' ),
        ),
    ),
    array(
        'key' => 'email_verification',
        'label' => __( 'Email Address', ACFF_NS ),
        'type'  => 'select',
        'required' => 0,
        'choices' => array(
            'all'        => __( 'All', 'acf-frontend-form-element' ),
            'verified'  => __( 'Verified', 'acf-frontend-form-element' ),
            'unverified' => __( 'Unverified', 'acf-frontend-form-element' ),
        ),
        'instructions' => 'Only show to users who verified their email address or only to those who haven\'t.',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
    ),
    array(
        'key' => 'by_role',
        'label' => __( 'Select By Role', 'acf-frontend-form-element' ),
        'type' => 'select',
        'instructions' => '',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
        'default_value' => array( 'administrator' ),
        'multiple' => 1,
        'ui' => 1,
        'choices' => acf_frontend_get_user_roles( array(), true ),
    ),
    array(
        'key' => 'by_user_id',
        'label' => __( 'Select By User', 'acf-frontend-form-element' ),
        'type' => 'user',
        'instructions' => '',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
        'allow_null' => 0,
        'multiple' => 1,
        'ajax' => 1,
        'ui' => 1,
        'return_format' => 'id',
    ), 
    array(
        'key' => 'dynamic',
        'label' => __( 'Dynamic Permissions', 'acf-frontend-form-element' ),
        'type' => 'select',
        'instructions' => '',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
        'choices' => acf_frontend_user_id_fields(),
        'allow_null' => 1,
    ),
);
