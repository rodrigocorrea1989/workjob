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
if( ! class_exists( 'SendWebhook' ) ) :
	
class SendWebhook extends ActionBase{

	public $site_domain = '';

	public function get_name() {
		return 'webhook';
	}

	public function get_label() {
		return __( 'Webhook', 'acf-frontend-form-element' );
	}

	public function admin_fields(){
		return array (
			array(
				'key' => 'webhooks',
				'label' => __( 'Webhooks', 'acf-frontend-form-element' ),
				'type' => 'list_items',
				'instructions' => '',
				'required' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'collapsed' => 'webhook_id',
				'collapsable' => true,
				'min' => '',
				'max' => '',
				'layout' => 'block',
				'button_label' => __( 'Add Webhook', 'acf-frontend-form-element' ),
				'remove_label' => __( 'Remove Webhook', 'acf-frontend-form-element' ),
				'conditional_logic' => array(
					array(
						array(
							'field' => 'more_actions',
							'operator' => '==contains',
							'value' => $this->get_name(),
						),
					),
				),
				'sub_fields' => array (
					array (
						'key' => 'webhook_id',
						'label' => __( 'Webhook Name', 'acf-frontend-form-element' ),
						'name' => 'webhook_id',
						'type' => 'text',
						'instructions' => __( 'Give this webhook an identifier', 'acf-frontend-form-element' ),
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '70',
							'class' => '',
							'id' => '',
						),
						'placeholder' => __( 'Webhook Name', 'acf-frontend-form-element' ),
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array (
						'key' => 'webhook_url',
						'label' => __( 'Webhook URL', 'acf-frontend-form-element' ),
						'name' => 'webhook_url',
						'type' => 'text',
						'instructions' => __( 'Enter the integration URL that will receive the form\'s submitted data.', 'acf-frontend-form-element' ),
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '70',
							'class' => '',
							'id' => '',
						),
						'placeholder' => 'https://your-webhook-url.com?key=',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
				),
			),
		);
				
	}

	public function register_settings_section( $widget ) {

		$site_domain = acf_frontend_get_site_domain();
		
		$repeater = new \Elementor\Repeater();


		$widget->start_controls_section(
			 'section_webhook',
			[
				'label' => $this->get_label(),
				'tab' => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'more_actions' => $this->get_name(),
				],
			]
		);
        
        $repeater->add_control(
            'webhook_id',
           [
               'label' => __( 'Webhook Name', 'acf-frontend-form-element' ),
               'type' => Controls_Manager::TEXT,
               'placeholder' => __( 'Webhook Name', 'acf-frontend-form-element' ),
               'label_block' => true,
               'description' => __( 'Give this webhook an identifier', 'acf-frontend-form-element' ),
			   'render_type' => 'none',
           ]
       );
	
		$repeater->add_control(
			 'webhook_url',
			[
				'label' => __( 'Webhook URL', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => 'https://your-webhook-url.com?key=',
				'label_block' => true,
				'separator' => 'before',
				'description' => __( 'Enter the integration URL that will receive the form\'s submitted data.', 'acf-frontend-form-element' ),
				'render_type' => 'none',
			]
		);
		
		
		$widget->add_control(
			'webhooks_to_send',
			[
				'label' => __( 'Webhooks', 'acf-frontend-form-element' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '{{{ webhook_id }}}',
				'render_type' => 'none',
			]
		);

		$widget->end_controls_section();
	}
	
	public function run( $form, $step = false ){	
		$webhooks = $form['webhooks'];

		if( empty( $webhooks ) ) return;

		$record = $form['record'];

		foreach( $webhooks as $webhook ){
			if( empty( $webhook['url'] ) || ! filter_var( $webhook['url'], FILTER_SANITIZE_URL ) ) continue; 

			/**
			 * Forms webhook request arguments.
			 *
			 * Filters the request arguments delivered by the form webhook when executing
			 * an ajax request.
			 *
			 * @since 1.0.0
			 *
			 * @param array    $record   The submission's recorded data sent through the webhook .
			 */
			$data = apply_filters( 'acf_frontend/forms/webhooks/request_data', array(
				$webhook['webhook_id'] => $record
			) );

			$response = wp_remote_post( $webhook['webhook_url'], $data );

			/**
			 * Form webhook response.
			 *
			 * Fires when the webhook response is retrieved.
			 *
			 * @since 1.0.0
			 *
			 * @param \WP_Error|array $response The response or WP_Error on failure.
			 * @param array     $record   An instance of the form record.
			 */
			do_action( 'acf_frontend/forms/webhooks/response', $response, $record );

			if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				throw new \Exception( __( 'Webhook Error', 'acf-frontend-form-element' ) );
			}
		}

	}

}
acff()->remote_actions['webhook'] = new SendWebhook();

endif;	