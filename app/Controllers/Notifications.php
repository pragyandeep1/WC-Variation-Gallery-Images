<?php

namespace Rtwpvg\Controllers;

class Notifications
{

    public function __construct() {
        add_action('admin_notices', array($this, 'php_requirement_notice'));
        add_action('admin_notices', array($this, 'wc_requirement_notice'));
        add_action('admin_notices', array($this, 'wc_version_requirement_notice'));
        // add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
    }

    public function php_requirement_notice() {
        if (!rtwpvg()->is_valid_php_version()) {
            $class = 'notice notice-error';
            $text = esc_html__('Please check PHP version requirement.', 'woo-product-variation-gallery');
            $link = esc_url('https://docs.woocommerce.com/document/server-requirements/');
            $message = wp_kses(__("It's required to use latest version of PHP to use <strong>Variation Images Gallery for WooCommerce</strong>.", 'woo-product-variation-gallery'), array('strong' => array()));

            printf('<div class="%1$s"><p>%2$s <a target="_blank" href="%3$s">%4$s</a></p></div>', $class, $message, $link, $text);
        }
    }

    public function wc_requirement_notice() {
        if (!rtwpvg()->is_wc_active()) {

            $class = 'notice notice-error';

            $text = esc_html__('WooCommerce', 'woo-product-variation-gallery');
            $link = esc_url(add_query_arg(array(
                'tab' => 'plugin-information',
                'plugin' => 'woocommerce',
                'TB_iframe' => 'true',
                'width' => '640',
                'height' => '500',
            ), admin_url('plugin-install.php')));
            $message = wp_kses(__("<strong>Variation Images Gallery for WooCommerce</strong> is an add-on of ", 'woo-product-variation-gallery'), array('strong' => array()));

            printf('<div class="%1$s"><p>%2$s <a class="thickbox open-plugin-details-modal" href="%3$s"><strong>%4$s</strong></a></p></div>', $class, $message, $link, $text);
        }
    }

    public function wc_version_requirement_notice() {
        if (rtwpvg()->is_wc_active() && !rtwpvg()->is_valid_wc_version()) {
            $class = 'notice notice-error';
            $message = sprintf(esc_html__("Currently, you are using older version of WooCommerce. It's recommended to use latest version of WooCommerce to work with %s.", 'woo-product-variation-gallery'), esc_html__('WooCommerce Product Variation Gallery', 'woo-product-variation-gallery'));
            printf('<div class="%1$s"><p><strong>%2$s</strong></p></div>', $class, $message);
        }
    }

    

}