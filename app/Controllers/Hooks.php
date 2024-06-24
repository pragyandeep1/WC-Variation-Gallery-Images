<?php

namespace Rtwpvg\Controllers;

use Rtwpvg\Helpers\Functions;
use Rtwpvgp\Helpers\Functions as Fns;
use WPML\FP\Functor\ConstFunctor;

class Hooks {


	public function __construct() {
		add_action( 'admin_init', [ $this, 'after_plugin_active' ] );

		add_filter( 'body_class', [ $this, 'body_class' ] );
		add_filter( 'post_class', [ $this, 'product_loop_post_class' ], 25, 3 );

		add_action( 'after_setup_theme', [ $this, 'enable_theme_support' ], 200 ); // Enable theme support

		add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_gallery' ], 10, 2 );
		add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'gallery_admin_html' ], 10, 3 );

		add_filter( 'woocommerce_available_variation', [ $this, 'available_variation_gallery' ], 90, 3 );
        // 60 For support Avanam.
		add_filter( 'wc_get_template', [ $this, 'gallery_template_override' ], 60, 2 );

		add_action( 'wp_ajax_rtwpvg_get_default_gallery_images', [ $this, 'get_default_gallery_images' ] );
		add_action( 'wp_ajax_nopriv_rtwpvg_get_default_gallery_images', [ $this, 'get_default_gallery_images' ] );

		add_filter( 'rtwpvg_inline_style', [ $this, 'rtwpvg_add_inline_style' ], 9 );
		add_action( 'woocommerce_update_product', [ $this, 'delete_cache_data' ], 10, 1 );
		add_action( 'rtwpvg_product_badge', [ __CLASS__, 'add_yith_badge' ] );
		// rtwpvg_disable_enqueue_scripts
		add_filter( 'rtwpvg_disable_enqueue_scripts', [ $this, 'disable_enqueue_scripts' ], 10 );
        // Old Version Compatibility
        if( defined('RTWPVGP_VERSION') && version_compare(RTWPVGP_VERSION, '2.2.2', '<=') ){
		    add_filter( 'rtwpvg_thumbnail_style', [ $this, 'rtwpvg_thumbnail_style' ], 15 );
        }

		add_filter( 'woocommerce_gallery_thumbnail_size', [ __CLASS__, 'rtwpvg_gallery_thumbnail_size' ], 15 );

        if( ! defined( 'RTWPVGP_VERSION' ) || ( defined( 'RTWPVGP_VERSION' ) && version_compare(RTWPVGP_VERSION, '2.3.6', '>=') ) ){
		    add_filter( 'woocommerce_product_export_meta_value', [ __CLASS__, 'product_export_meta_value' ], 15, 4 );
		    add_filter( 'woocommerce_product_import_process_item_data', [ __CLASS__, 'product_import_process_item_data' ], 15 );
        }

	}

	/**
	 * Export Image
	 * @param $meta_value
	 * @param $meta
	 * @param $product
	 * @param $row
	 *
	 * @return mixed|string
	 */
	public static function product_export_meta_value( $meta_value, $meta, $product, $row) {
		if( 'rtwpvg_images' !== $meta->key || ! ( is_array( $meta_value ) && count( $meta_value ) ) ){
			return $meta_value;
		}
		$images = [];
		foreach ( $meta_value as $image_id ) {
			$images[] = wp_get_attachment_image_url( $image_id, 'full' );
		}
		return implode( ',', $images );
	}

	/**
	 * @param $meta_value
	 * @param $meta
	 * @param $product
	 * @param $row
	 *
	 * @return mixed|string
	 */
	public static function product_import_process_item_data( $data ) {

		if( empty( $data['meta_data'] ) || ! is_array( $data['meta_data'] ) || ! count( $data['meta_data'] ) ){
			return $data;
		}
		foreach ( $data['meta_data'] as $key => $meta ) {
			if( 'rtwpvg_images' !== $meta['key'] ){
				continue;
			}
			if( empty( $meta['value'] ) ){
				unset($data['meta_data'][$key]);
				continue;
			}
			$images_url = explode( ',', $meta['value'] );
			$images_id = [];
			foreach ( $images_url as $url ) {
				$images_id[] = Functions::get_attachment_id_from_url( $url, $data['id'] );
			}
			unset( $data['meta_data'][$key]['value'] );
			$data['meta_data'][$key]['value'] = $images_id;
		}
		return $data;
	}

	/**
	 * 
	 */
	public static function rtwpvg_gallery_thumbnail_size( $size ) {
		$thumbnail_size = rtwpvg()->get_option( 'gallery_thumbnail_size' );
		return $thumbnail_size ? $thumbnail_size : $size; // thumbnail, full, 'medium'
	}
	/**
     * Old Version Compatibility
     *
	 * @param $style
	 *
	 * @return mixed
	 */
	public static function rtwpvg_thumbnail_style( $style ) {
		$style['left']  = esc_html__( 'Position Left', 'woo-product-variation-gallery' );
		$style['right'] = esc_html__( 'Position Right', 'woo-product-variation-gallery' );
        return $style;
	}

	/**
	 * Boolean Return.
	 *
	 * @param boolean $bool boolean.
	 * @return bool
	 */
	public static function disable_enqueue_scripts( $bool ) {
		// TODO:: In the future version we need to load the script for specific page.
		if ( is_admin() ) {
			return $bool;
		}
		if ( is_singular( 'product' ) ) {
			return $bool;
		}
		if ( rtwpvg()->get_option( 'load_scripts' ) ) {
			return false;
		}
		return true;
	}
	/**
	 * @param \WC_Product $product
	 */
	public static function add_yith_badge( $product ) {
		if ( ( defined( 'YITH_WCBM_VERSION' ) && YITH_WCBM_VERSION ) || ( defined( 'YITH_WCBM_PREMIUM' ) && YITH_WCBM_PREMIUM ) ) {
			echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', '<div class="rtwpvg-yith-badge"></div>', $product->get_image_id() );
		}
	}


	public function after_plugin_active() {
		if ( get_option( 'rtwpvg_pro_active' ) === 'yes' ) {
			delete_option( 'rtwpvg_pro_active' );
			wp_safe_redirect(
				add_query_arg(
					[
						'page' => 'wc-settings',
						'tab'  => rtwpvg()->settings_api()->get_setting_id(),
					],
					admin_url( 'admin.php' )
				)
			);
		}
	}

	function delete_cache_data( $product_id ) {
		Functions::delete_transients( $product_id );
	}




	public function body_class( $classes ) {
		array_push( $classes, 'rtwpvg' );

		return array_unique( $classes );
	}

	public function product_loop_post_class( $classes, $class, $product_id ) {

		if ( 'product' === get_post_type( $product_id ) ) {
			$product = wc_get_product( $product_id );
			if ( $product->is_type( 'variable' ) ) {
				$classes[] = 'rtwpvg-product';
			}
		}

		return $classes;
	}

	function rtwpvg_add_inline_style( $styles ) {
		$gallery_width = absint( apply_filters( 'rtwpvg_default_width', 30 ) );
		if ( $gallery_width > 99 ) {
			$styles['float']   = 'none';
			$styles['display'] = 'block';
		}

		return $styles;
	}

	function gallery_template_override( $template, $template_name ) {
		$using_swiper               = rtwpvg()->get_option( 'upgrade_slider_scripts' );
		$template_prefix = $using_swiper ? 'swiper-' : null;
		$old_template = $template;

		// Disable gallery on specific product

		if ( apply_filters( 'disable_woo_variation_gallery', false ) ) {
			return $old_template;
		}

		if ( $template_name == 'single-product/product-image.php' ) {
			$template = rtwpvg()->locate_template( $template_prefix . 'product-images' );
		}

		if ( $template_name == 'single-product/product-thumbnails.php' ) {
			$template = rtwpvg()->locate_template( 'product-thumbnails' );
		}

        return apply_filters( 'rtwpvg_gallery_template_override_location', $template, $template_name, $old_template );
	}

	public function enable_theme_support() {
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
	}

	public function save_variation_gallery( $variation_id, $loop ) {
		if ( isset( $_POST['rtwpvg'] ) ) {
			if ( isset( $_POST['rtwpvg'][ $variation_id ] ) ) {
				$rtwpvg_ids = (array) array_map( 'absint', $_POST['rtwpvg'][ $variation_id ] );
				$rtwpvg_ids = array_values( array_unique( $rtwpvg_ids ) );
				update_post_meta( $variation_id, 'rtwpvg_images', $rtwpvg_ids );
			} else {
				delete_post_meta( $variation_id, 'rtwpvg_images' );
			}
		} else {
			delete_post_meta( $variation_id, 'rtwpvg_images' );
		}
	}

	public function gallery_admin_html( $loop, $variation_data, $variation ) {
		$variation_id   = absint( $variation->ID );
		$gallery_images = get_post_meta( $variation_id, 'rtwpvg_images', true );
		?>
		<div class="form-row form-row-full rtwpvg-gallery-wrapper">
			<h4><?php esc_html_e( 'Variation Image Gallery', 'woo-product-variation-gallery' ); ?></h4>
			<div class="rtwpvg-image-container">
				<ul class="rtwpvg-images">
					<?php
					if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
						$gallery_images = array_values( array_unique( $gallery_images ) );
						foreach ( $gallery_images as $image_id ) :
							$image = wp_get_attachment_image_src( $image_id );
							$video = Functions::gallery_has_video( $image_id );
							if ( empty( $image[0] ) ) {
								continue;
							}
							$add_video_class = $video ? ' video' : '';
							?>
							<li class="image<?php echo esc_html( $add_video_class ); ?>">
								<input type="hidden" name="rtwpvg[<?php echo esc_attr( $variation_id ); ?>][]" value="<?php echo absint( $image_id ); ?>">
								<img src="<?php echo esc_url( $image[0] ); ?>">
								<a href="#" class="delete rtwpvg-remove-image">
									<span  class="dashicons dashicons-dismiss"></span>
								</a>
							</li>
							<?php
						endforeach;
					}
					?>
				</ul>
			</div>
			<p class="rtwpvg-add-image-wrapper hide-if-no-js">
				<a href="#" data-product_variation_loop="<?php echo absint( $loop ); ?>"
				   data-product_variation_id="<?php echo esc_attr( $variation_id ); ?>"
				   class="button rtwpvg-add-image"><?php esc_html_e( 'Add Gallery Images', 'woo-product-variation-gallery' ); ?></a>
			</p>
		</div>
		<?php
	}


	/**
	 * @param $available_variation
	 * @param $variationProductObject
	 * @param $variation
	 *
	 * @return string
	 */
	public function available_variation_gallery( $available_variation, $variationProductObject, $variation ) {

		$product_id                   = absint( $variation->get_parent_id() );
		$variation_id                 = absint( $variation->get_id() );
		$variation_image_id           = absint( $variation->get_image_id() );
		$has_variation_gallery_images = (bool) get_post_meta( $variation_id, 'rtwpvg_images', true );
		if ( $has_variation_gallery_images ) {
			$gallery_images = (array) get_post_meta( $variation_id, 'rtwpvg_images', true );
		} else {
			$gallery_images = $variationProductObject->get_gallery_image_ids();
		}

		$featured_thumbnail = rtwpvg()->get_option( 'remove_featured_thumbnail' ) ? false : true;
		if ( apply_filters( 'rtwpvg_variation_gallery_images_enable_feature_image', $featured_thumbnail ) ) {
			if ( $variation_image_id ) {
				array_unshift( $gallery_images, $variation_image_id );
			} else {
				$parent_product          = wc_get_product( $product_id );
				$parent_product_image_id = $parent_product->get_image_id();

				if ( ! empty( $parent_product_image_id ) ) {
					array_unshift( $gallery_images, $parent_product_image_id );
				}
			}
		}

		$available_variation['variation_gallery_images'] = [];
		$gallery_images                                  = array_values( array_unique( $gallery_images ) );
		foreach ( $gallery_images as $i => $variation_gallery_image_id ) {
			$available_variation['variation_gallery_images'][ $i ] = Functions::get_gallery_image_props( $variation_gallery_image_id );
		}

		return apply_filters( 'rtwpvg_available_variation_gallery', $available_variation, $variation, $product_id );
	}

	public function get_default_gallery_images() {
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$images     = Functions::get_gallery_images( $product_id );
		wp_send_json_success( apply_filters( 'rtwpvg_get_default_gallery_images', $images, $product_id ) );
	}

}
