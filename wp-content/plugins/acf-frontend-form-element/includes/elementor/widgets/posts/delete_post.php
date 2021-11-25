<?php

namespace ACFFrontend\Widgets;

use  ACFFrontend\Plugin ;
use  ACFFrontend\Classes ;
use  Elementor\Controls_Manager ;
use  Elementor\Widget_Base ;
use  ElementorPro\Modules\QueryControl\Module as Query_Module ;
/**
 * Elementor ACF Frontend Form Widget.
 *
 * Elementor widget that inserts an ACF frontend form into the page.
 *
 * @since 1.0.0
 */
class Delete_Post_Widget extends Widget_Base
{
    /**
     * Get widget name.
     *
     * Retrieve acf ele form widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name()
    {
        return 'delete_post';
    }
    
    /**
     * Get widget title.
     *
     * Retrieve acf ele form widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return __( 'Trash/Delete Post', 'acf-frontend-form-element' );
    }
    
    /**
     * Get widget icon.
     *
     * Retrieve acf ele form widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'fas fa-trash-alt frontend-icon';
    }
    
    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the acf ele form widget belongs to.
     *
     * @since 1.0.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories()
    {
        return array( 'acff-posts' );
    }
    
    /**
     * Register acf ele form widget controls.
     *
     * Adds different input fields to allow the post to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function _register_controls()
    {
        $this->start_controls_section( 'delete_button_section', [
            'label' => __( 'Trash Button', 'acf-frontend-form-element' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );
        $this->add_control( 'delete_button_text', [
            'label'       => __( 'Delete Button Text', 'acf-frontend-form-element' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => __( 'Delete', 'acf-frontend-form-element' ),
            'placeholder' => __( 'Delete', 'acf-frontend-form-element' ),
        ] );
        $this->add_control( 'delete_button_icon', [
            'label' => __( 'Delete Button Icon', 'acf-frontend-form-element' ),
            'type'  => Controls_Manager::ICONS,
        ] );
        $this->add_control( 'confirm_delete_message', [
            'label'       => __( 'Confirm Delete Message', 'acf-frontend-form-element' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => __( 'The post will be deleted. Are you sure?', 'acf-frontend-form-element' ),
            'placeholder' => __( 'The post will be deleted. Are you sure?', 'acf-frontend-form-element' ),
        ] );
        $this->add_control( 'show_delete_message', [
            'label'        => __( 'Show Success Message', 'acf-frontend-form-element' ),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __( 'Yes', 'acf-frontend-form-element' ),
            'label_off'    => __( 'No', 'acf-frontend-form-element' ),
            'default'      => 'true',
            'return_value' => 'true',
        ] );
        $this->add_control( 'delete_message', [
            'label'       => __( 'Success Message', 'acf-frontend-form-element' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => __( 'You have deleted this post', 'acf-frontend-form-element' ),
            'placeholder' => __( 'You have deleted this post', 'acf-frontend-form-element' ),
            'dynamic'     => [
            'active'    => true,
            'condition' => [
            'show_delete_message' => 'true',
        ],
        ],
        ] );
        $this->add_control( 'force_delete', [
            'label'        => __( 'Force Delete', 'acf-frontend-form-element' ),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'true',
            'return_value' => 'true',
            'description'  => __( 'Whether or not to completely delete the posts right away.' ),
        ] );
        $this->add_control( 'delete_redirect', [
            'label'   => __( 'Redirect After Delete', 'acf-frontend-form-element' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'custom_url',
            'options' => [
            'current'     => __( 'Reload Current Url', 'acf-frontend-form-element' ),
            'custom_url'  => __( 'Custom Url', 'acf-frontend-form-element' ),
            'referer_url' => __( 'Referer', 'acf-frontend-form-element' ),
        ],
        ] );
        $this->add_control( 'redirect_after_delete', [
            'label'         => __( 'Custom URL', 'acf-frontend-form-element' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => __( 'Enter Url Here', 'acf-frontend-form-element' ),
            'show_external' => false,
            'dynamic'       => [
            'active' => true,
        ],
            'condition'     => [
            'delete_redirect' => 'custom_url',
        ],
        ] );
        $this->end_controls_section();
        $this->start_controls_section( 'post_section', [
            'label' => __( 'Post', 'acf-frontend-form-element' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );
        acff()->local_actions['post']->action_controls( $this, false, 'delete_post' );
        $this->end_controls_section();
        do_action( 'acff/permissions_section', $this );
        $this->start_controls_section( 'style_promo_section', [
            'label' => __( 'Styles', 'acf-frontend-form-elements' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );
        $this->add_control( 'styles_promo', [
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => __( '<p><a target="_blank" href="https://www.frontendform.com/"><b>Go Pro</b></a> to unlock styles.</p>', 'acf-frontend-form-element' ),
            'content_classes' => 'acf-fields-note',
        ] );
        $this->end_controls_section();
    }
    
    /**
     * Render acf ele form widget output on the frontend.
     *
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render()
    {
        $wg_id = $this->get_id();
        $current_post_id = acff()->elementor->get_current_post_id();
        global  $post ;
        $settings = $this->get_settings_for_display();
        $btn_args = array(
            'id'             => $wg_id,
            'kses'           => true,
            'force_delete'   => $settings['force_delete'],
            'delete_message' => $settings['confirm_delete_message'],
            'delete_icon'    => $settings['delete_button_icon'],
            'delete_text'    => $settings['delete_button_text'],
            'data_type'      => 'post',
            'post_select'    => $settings['post_select'],
            'save_to_post'   => 'delete_post',
            'post_to_edit'   => $settings['post_to_edit'],
            'url_query_post' => $settings['url_query_post'],
            'redirect'       => $settings['delete_redirect'],
            'custom_url'     => $settings['redirect_after_delete'],
        );
        $settings = apply_filters( 'acf_frontend/show_form', $settings );
        
        if ( $settings['display'] ) {
            acff()->form_display->delete_button( $btn_args );
        } else {
            
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo  '<div class="preview-display">' ;
                acff()->form_display->delete_button( $btn_args );
                echo  '</div>' ;
            }
        
        }
    
    }

}