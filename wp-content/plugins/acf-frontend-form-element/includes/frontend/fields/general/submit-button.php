<?php

if( ! class_exists('acf_field_submit_button') ) :

class acf_field_submit_button extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'submit_button';
		$this->label = __("Submit Button",'acf');
		$this->category = __( 'Form', ACFF_NS );
		$this->defaults = array(
			'label' 	=> __( 'Submit', 'acf-frontend-form-element' ),
            'field_label_hide'  => 1,
		);
		
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {		
		
			// vars
			$m = '<div class="acff-submit-buttons"><input type="submit" class="acff-submit-button acf-button button button-primary" data-state="publish" value="' .$field['label']. '" /></div>';
					
			// wptexturize (improves "quotes")
			$m = wptexturize( $m );

			echo $m;
	}
	
	
	/*
	*  load_field()
	*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	function load_field( $field ) {
		
		// remove name to avoid caching issue
		$field['name'] = '';
		
		// remove instructions
		$field['instructions'] = '';
		
		// remove required to avoid JS issues
		$field['required'] = 0;
		
		// set value other than 'null' to avoid ACF loading / caching issue
		$field['value'] = false;

		$field['field_label_hide'] = 1;

		if( empty( $field['button_text'] ) ){
			$field['button_text'] = $field['label'];
		}
		
		// return
		return $field;
	}

	function render_field_settings( $field ){
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Button Text', ACFF_NS ),
			'type'			=> 'text',
			'name'			=> 'button_text',
		));
	}
	
}


// initialize
acf_register_field_type( 'acf_field_submit_button' );

endif; // class_exists check

?>