<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( function_exists( 'acf_add_local_field' ) ) {
    acf_add_local_field( array(
        'key'      => 'acff_title',
        'label'    => __( 'Title', 'acf-frontend-form-element' ),
        'required' => true,
        'name'     => 'acff_title',
        'type'     => 'post_title',
    ) );
    acf_add_local_field( array(
        'key'      => 'acf_frontend_custom_term',
        'label'    => __( 'Value', 'acf-frontend-form-element' ),
        'required' => true,
        'name'     => 'acf_frontend_custom_term',
        'type'     => 'text',
    ) );
    $form_types = array(
        'general'             => __( 'ACF Frontend Form', ACFF_NS ),
        __( 'Post', ACFF_NS ) => array(
        'edit_post'      => __( 'Edit Post Form', ACFF_NS ),
        'duplicate_post' => __( 'Duplicate Post Form', ACFF_NS ),
        'new_post'       => __( 'New Post Form', ACFF_NS ),
    ),
        __( 'User', ACFF_NS ) => array(
        'edit_user' => __( 'Edit User Form', ACFF_NS ),
        'new_user'  => __( 'New User Form', ACFF_NS ),
    ),
        __( 'Term', ACFF_NS ) => array(
        'edit_term' => __( 'Edit Term Form', ACFF_NS ),
        'new_term'  => __( 'New Term Form', ACFF_NS ),
    ),
    );
    acf_add_local_field( array(
        'label'      => __( 'Select Type', ACFF_NS ),
        'name'       => 'acff_form_types',
        'key'        => 'acff_form_types',
        'required'   => true,
        'allow_null' => 1,
        'type'       => 'select',
        'choices'    => $form_types,
    ) );
}
