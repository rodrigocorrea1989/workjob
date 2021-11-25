<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( function_exists('register_post_type') ):
    $labels = array(
        'name'                  => _x( 'Forms', 'Post Type General Name', ACFF_NS ),
        'singular_name'         => _x( 'Form', 'Post Type Singular Name', ACFF_NS ),
        'menu_name'             => __( 'Forms', ACFF_NS ),
        'name_admin_bar'        => __( 'Form', ACFF_NS ),
        'archives'              => __( 'Form Archives', ACFF_NS ),
        'all_items'             => __( 'Forms', ACFF_NS ),
        'add_new_item'          => __( 'Add New Form', ACFF_NS ),
        'add_new'               => __( 'Add New', ACFF_NS ),
        'new_item'              => __( 'New Form', ACFF_NS ),
        'edit_item'             => __( 'Edit Form', ACFF_NS ),
        'update_item'           => __( 'Update Form', ACFF_NS ),
        'view_item'             => __( 'View Form', ACFF_NS ),
        'search_items'          => __( 'Search Form', ACFF_NS ),
        'not_found'             => __( 'Not found', ACFF_NS ),
        'not_found_in_trash'    => __( 'Not found in Trash', ACFF_NS ),
        'items_list'            => __( 'Forms list', ACFF_NS ),
        'item_published'        => __( 'Settings Saved', ACFF_NS ),
        'item_updated'          => __( 'Settings Saved', ACFF_NS ),
        'items_list_navigation' => __( 'Forms list navigation', ACFF_NS ),
        'filter_items_list'     => __( 'Filter forms list', ACFF_NS ),
    );
    $args = array(
        'label'                 => __( 'Form', ACFF_NS ),
        'description'           => __( 'Form', ACFF_NS ),
        'labels'                => $labels,
        'supports'              => array( 'title' ),
        'show_in_rest'          => true,
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => 'acff-settings',
        'menu_position'         => 80,
        'show_in_admin_bar'     => false,
        'can_export'            => true,
        'rewrite'               => false,
        'capability_type'       => 'page',
        'query_var'				=> false,
    );
    register_post_type( 'acf_frontend_form', $args );

    do_action( 'acf_frontend_post_types' );

    add_filter( 'post_updated_messages', function( $messages ){
        $messages['acf_frontend_form'] = array(
            '',
            __( 'Form updated.' ),
            __( 'Custom field updated.' ),
            __( 'Custom field deleted.' ),
            __( 'Form updated.' ),
            '',
            __( 'Form published.' ),
            __( 'Form saved.' ),
            __( 'Form submitted.' ),
            '',
            __( 'Form draft updated.' ),
        );
        return $messages;
    } );

endif;