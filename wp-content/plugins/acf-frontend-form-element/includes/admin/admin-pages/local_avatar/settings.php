<?php
namespace ACFFrontend;

use ACFFrontend\Plugin;
use Elementor\Core\Base\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ACFF_Local_Avatar_Settings{

	public function local_avatar_field( $args ) {
		$fields = acf_frontend_get_field_data( 'image' );

		echo '<select name="local_avatar" id="local_avatar">';
		
			$default = ( get_option( 'local_avatar' ) == 'none' ) ? ' selected="selected"' : '';
		    echo '<option value="none"' . $default .  '>None</option>';
		
			$selected = get_option( 'local_avatar' );
			foreach( $fields as $key => $value ){
				$select = '';
				if( $key == $selected ){
					$select = ' selected="selected"';
				}
				
				echo '<option value="' . $key . '"' . $select . '>' . $value . '</option>';
			}
		echo '</select>';
	}
	
	
	public function acff_local_avatar_section(){	
		register_setting( 'local_avatar_settings', 'local_avatar' );
		add_settings_section(
			'local_avatar_settings_section',
			__( 'Local Gravatar', 'acf-frontend-form-element' ),
			'',
			'local-avatar-settings-admin'
		);
		add_settings_field(
			'local_avatar', 
			__( 'Replace Gravatar with Local Avatar', 'acf-frontend-form-element' ),
            [ $this, 'local_avatar_field'],
            'local-avatar-settings-admin',
			'local_avatar_settings_section',
			[
				'label_for' => 'local_avatar'
			] 
		);
	}
	
	function acff_get_local_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
		$user = '';

		// Get user by id or email
		if ( is_numeric( $id_or_email ) ) {
			$id   = (int) $id_or_email;
			$user = get_user_by( 'id' , $id );
		} elseif ( is_object( $id_or_email ) ) {
			if ( ! empty( $id_or_email->user_id ) ) {
				$id   = (int) $id_or_email->user_id;
				$user = get_user_by( 'id' , $id );
			}
		} else {
			$user = get_user_by( 'email', $id_or_email );
		}
		if ( ! $user ) {
			return $avatar;
		}
		// Get the user id
		$user_id = $user->ID;
		
		$img_field_key = get_option( 'local_avatar' );
		
		if( $img_field_key == 'none' ){
			return $avatar;
		}
		
		// Get the file id
		$image_id = get_field( $img_field_key, 'user_' . $user_id ); 
		
		if ( ! $image_id ) {
			return $avatar;
		}
		
		if( is_array( $image_id ) ) {
			$image_id = $image_id['ID'];
		}
		
	   if( filter_var( $image_id, FILTER_VALIDATE_URL ) ) { 
            $avatar_url = $image_id;
       }else{
			$image_url  = wp_get_attachment_image_src( $image_id, 'thumbnail' );
			$avatar_url = $image_url[0];
	   }
		
		// Get the img markup
		$avatar = '<img alt="' . $alt . '" src="' . $avatar_url . '" class="avatar avatar-' . $size . '" height="' . $size . '" width="' . $size . '"/>';
		
		// Return our new avatar
		return $avatar;
	}
    
    public function acff_hide_gravatar_field( $hook ) {		
		if( get_option( 'local_avatar' ) == 'none' ){
			return;
		}
        echo '<style>
        tr.user-profile-picture{
            display: none;
        }
        </style>';
    }

	public function get_settings_fields( $field_keys ){
		$default = get_option( 'local_avatar' ) ? get_option( 'local_avatar' ) : 'none';

		$local_fields = array(
			'local_avatar' => array(
				'label' => __( 'Avatar Field', 'acf-frontend-form-element' ),
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'wrapper' => array(
					'width' => '30',
					'class' => '',
					'id' => '',
				),
				'only_front' => 0,
				'choices' => acf_frontend_get_field_data( 'image' ),
				'value' => $default,
				'allow_null' => 1,
				'ajax' => 0,
				'return_format' => 'value',
				'placeholder' => 'None',
			),
		);

		return $local_fields;
	} 

	public function __construct() {
		//add_action( 'admin_init', [ $this, 'acff_local_avatar_section'] );
        add_filter( 'get_avatar', [ $this, 'acff_get_local_avatar'], 10, 5 );
        add_action( 'admin_head', [ $this, 'acff_hide_gravatar_field'] );
		add_filter( 'acff/local_avatar_fields', [ $this, 'get_settings_fields'] );
	}
	
}
new ACFF_Local_Avatar_Settings( $this );		