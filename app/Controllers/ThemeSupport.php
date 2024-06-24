<?php

namespace Rtwpvg\Controllers;
use Rtwpvg\Helpers\Functions;

class ThemeSupport
{
    /**
     * ThemeSupport constructor.
     * Add Theme Support for different theme
     */
    public function __construct() {
        add_action('init', array($this, 'add_theme_support'), 200); 
        // Flatsome Theme Custom Layout Support.
        add_filter( 'wc_get_template_part', array($this, 'rtwpvg_gallery_template_part_override'), 30, 2 );
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ] );
	    add_action( 'rtwpvg_product_badge', [ $this, 'rtwpvg_product_badge' ] );
    }

	/**
	 * @param object $product Product.
	 *
	 * @return void
	 */
	public function rtwpvg_product_badge( $product ) {
		// BeRocket - Advanced Product Labels for WooCommerce.
		do_action( 'berocket_apl_set_label', true, $product );
	}

    function after_setup_theme() {
        $single_product_summary = [
            'woostify',
            // 'blocksy'
        ];
        if ( function_exists( 'woostify_is_woocommerce_activated' ) ) {
            remove_action('woocommerce_before_single_product_summary', 'woostify_single_product_gallery_image_slide', 30);
            remove_action('woocommerce_before_single_product_summary', 'woostify_single_product_gallery_thumb_slide', 40);
        }

        if ( in_array( rtwpvg()->active_theme(), $single_product_summary ) ) {
            add_action( 'woocommerce_before_single_product_summary', [ $this, 'woocommerce_show_product_images'], 22 );
        }
        if( 'blocksy' === rtwpvg()->active_theme() ){
            //woocommerce_before_template_part
            add_filter( 'woocommerce_single_product_image_thumbnail_html', function(){
                ob_start();
                $this->woocommerce_show_product_images();
                return ob_get_clean();
            }, 20 );
        }

        // TODO:: woocommerce_single_product_image_thumbnail_html  Maybe We can soleve permant solution by this hooks.
        
		// Astra Pro Addons Theme Support
        if ( defined('ASTRA_EXT_FILE') ) {
            add_filter( 'astra_addon_override_single_product_layout', '__return_false' );
        }

    }

	public function woocommerce_show_product_images() {
		$using_swiper               = rtwpvg()->get_option( 'upgrade_slider_scripts' );
		$template_prefix = $using_swiper ? 'swiper-' : null;
		Functions::get_template( $template_prefix . 'product-images' );
	}


    function add_theme_support() {
        if (apply_filters('rtwpvg_add_electro_theme_support', true)) {
            remove_action('woocommerce_before_single_product_summary', 'electro_show_product_images', 20);
        }

    }
	 
    function rtwpvg_gallery_template_part_override( $template, $template_name ) {
	    $using_swiper               = rtwpvg()->get_option( 'upgrade_slider_scripts' );
	    $template_prefix = $using_swiper ? 'swiper-' : null;
        $old_template = $template;
        
        // Disable gallery on specific product
        if ( apply_filters( 'disable_woo_variation_gallery', false ) ) {
            return $old_template;
        } 

        if ( $template_name == 'single-product/product-image' ) {
            $template = rtwpvg()->locate_template($template_prefix . 'product-images');
        }
        
        if ( $template_name == 'single-product/product-thumbnails' ) {
            $template = rtwpvg()->locate_template('product-thumbnails');
        }
        
        return apply_filters( 'rtwpvg_gallery_template_part_override_location', $template, $template_name, $old_template );
    } 
} 