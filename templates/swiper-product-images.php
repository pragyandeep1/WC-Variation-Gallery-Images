<?php

use Rtwpvg\Helpers\Functions;

defined( 'ABSPATH' ) || exit;

$columns    = absint( apply_filters( 'rtwpvg_thumbnails_columns', rtwpvg()->get_option( 'thumbnails_columns' ) ) );
$columns_sm = absint( apply_filters( 'rtwpvg_sm_thumbnails_columns', rtwpvg()->get_option( 'thumbnails_columns_sm' ) ) ) ?? 4;
$columns_xs = absint( apply_filters( 'rtwpvg_xs_thumbnails_columns', rtwpvg()->get_option( 'thumbnails_columns_xs' ) ) ) ?? 3;

global $product;

$product_id           = $product->get_id();
$default_attributes   = Functions::get_product_default_attributes( $product_id );
$default_variation_id = Functions::get_product_default_variation_id( $product, $default_attributes );
$product_type         = $product->get_type();
$post_thumbnail_id    = $product->get_image_id();

$attachment_ids     = $product->get_gallery_image_ids();
$has_post_thumbnail = $product->get_image_id() ?? false; //has_post_thumbnail(); ShopBuilder support

if ( 'variable' === $product_type && $default_variation_id > 0 ) {

	$product_variation = Functions::get_product_variation( $product_id, $default_variation_id );

	if ( isset( $product_variation['image_id'] ) ) {
		$post_thumbnail_id  = $product_variation['image_id'];
		$has_post_thumbnail = true;
	}

	if ( isset( $product_variation['variation_gallery_images'] ) ) {
		$attachment_ids = wp_list_pluck( $product_variation['variation_gallery_images'], 'image_id' );
		array_shift( $attachment_ids );
	}
}
$has_gallery_thumbnail = ( $has_post_thumbnail && ( count( $attachment_ids ) > 0 ) );

$only_has_post_thumbnail = ( $has_post_thumbnail && ( count( $attachment_ids ) === 0 ) );

$thumbnail_position = apply_filters( 'rtwpvg_thumbnail_position', 'bottom' );

$gallery_slider_js_options = apply_filters( 'rtwpvg_slider_js_options',
	array(
		'autoHeight'    => ! rtwpvg()->get_option( 'slider_adaptive_height' ) ? false : true,
		'slidesPerView' => 1,
        'navigation'   => false
	)
);

$gallery_thumbnails_gap = absint( apply_filters( 'rtwpvg_thumbnails_gap', rtwpvg()->get_option( 'thumbnails_gap' ) ) );

$thumbnail_slider_js_options = apply_filters( 'rtwpvg_thumbnail_slider_js_options', [
	'rtl'                  => ! in_array( $thumbnail_position, array( 'left', 'right' ) ) && is_rtl(), //Rtl is not working properly
	'loop'                 => false,
	'autoplay'             => false,
	'pagination'           => false,
	'centeredSlides'       => $columns % 2 !== 0 ? true : false,
	'centeredSlidesBounds' => $columns % 2 !== 0 ? true : false,
	'direction'            => in_array( $thumbnail_position, array( 'left', 'right' ) ) ? 'vertical' : 'horizontal',
	'navigation'           => [
		'nextEl' => '.rtwpvg-thumbnail-next-arrow',
		'prevEl' => '.rtwpvg-thumbnail-prev-arrow',
	],
	'spaceBetween'         => $gallery_thumbnails_gap,
	'breakpoints'          => [
		// when window width is >= 320px
		0   => [
			'slidesPerView'  => $columns_xs,
			'slidesPerGroup' => $columns_xs
		],
		// when window width is >= 480px
		768 => [
			'slidesPerView'  => $columns_sm,
			'slidesPerGroup' => $columns_sm
		],
		// when window width is >= 640px
		992 => [
			'slidesPerView'  => $columns,
			'slidesPerGroup' => $columns
		]
	]
] );

$gallery_width = absint( apply_filters( 'rtwpvg_width', rtwpvg()->get_option( 'gallery_width' ) ) );

$inline_style = apply_filters( 'rtwpvg_product_inline_style', array() );

$wrapper_classes   = apply_filters( 'rtwpvg_image_classes', array(
	'rtwpvg-images',
	'rtwpvg-images-thumbnail-columns-' . absint( $columns ),
	'rtwpvg-has-product-thumbnail',
	count( $attachment_ids ) > 0 ? 'rtwpvg-has-gallery-images' : 'rtwpvg-no-gallery-images',
) );
$post_thumbnail_id = (int) apply_filters( 'rtwpvg_post_thumbnail_id', $post_thumbnail_id, $attachment_ids, $product );
$attachment_ids    = (array) apply_filters( 'rtwpvg_attachment_ids', $attachment_ids, $post_thumbnail_id, $product );
?>

<div style="<?php echo esc_attr( Functions::generate_inline_style( $inline_style ) ) ?>"
     class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', array_unique( $wrapper_classes ) ) ) ); ?>">

    <div class="<?php echo rtwpvg()->get_option( 'preloader' ) ? 'loading-rtwpvg' : ''; ?> rtwpvg-wrapper rtwpvg-thumbnail-position-<?php echo esc_attr( $thumbnail_position ) ?> rtwpvg-product-type-<?php echo esc_attr( $product_type ) ?>" data-thumbnail_position='<?php echo esc_attr( $thumbnail_position ) ?>'>

        <div class="rtwpvg-container rtwpvg-preload-style-<?php echo trim( rtwpvg()->get_option( 'preload_style' ) ?? '' ) ?>">

            <div class="rtwpvg-slider-wrapper ">
				<?php do_action( 'rtwpvg_product_badge', $product ); ?>
				<?php

				if ( $has_post_thumbnail && rtwpvg()->get_option( 'lightbox' ) ): ?>
                    <a href="#"
                       class="rtwpvg-trigger rtwpvg-trigger-position-<?php echo rtwpvg()->get_option( 'zoom_position' ); ?><?php echo rtwpvg()->get_option( 'lightbox_image_click' ) ? ' rtwpvg-image-trigger' : '' ?>">
						<?php ob_start(); ?>
                        <span class="dashicons dashicons-search">
                                    <span class="screen-reader-text">
                                        <?php echo esc_html( 'Zoom' ); ?>
                                    </span>
                                </span>
						<?php
						$icon_html = ob_get_clean();
						echo apply_filters( 'rtwpvg_trigger_icon', $icon_html );
						?>
                    </a>
				<?php endif; ?>

                <div class="rtwpvg-slider swiper"
                     data-options='<?php echo htmlspecialchars( wp_json_encode( $gallery_slider_js_options ) ); // WPCS: XSS ok. ?>'>
                    <!-- Additional required wrapper -->
                    <div class="swiper-wrapper">
						<?php

						if ( ! $has_post_thumbnail && ! count( $attachment_ids ) > 0 ) {
							echo '<div class="rtwpvg-gallery-image rtwpvg-gallery-image-placeholder swiper-slide">';
							echo sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src() ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
							echo '</div>';
						}
						// Main  Image
						if ( $has_post_thumbnail ) {
							echo Functions::get_gallery_image_html( $post_thumbnail_id, array(
								'is_main_thumbnail'  => true,
								'has_only_thumbnail' => $only_has_post_thumbnail
							) );
						}
						// Gallery attachment Images
						if ( count( $attachment_ids ) > 0 ) :
							foreach ( $attachment_ids as $attachment_id ) :
								echo Functions::get_gallery_image_html( $attachment_id, array(
									'is_main_thumbnail'  => true,
									'has_only_thumbnail' => $only_has_post_thumbnail
								) );
							endforeach;
						endif;
						?>
                    </div>  <!-- end swiper-wrapper -->
					<?php if ( is_array( $gallery_slider_js_options['navigation'] ?? '' ) ) { ?>
                        <!-- If we need navigation buttons -->
                        <i class="rtwpvg-slider-prev-arrow dashicons dashicons-arrow-left-alt2"></i>
                        <i class="rtwpvg-slider-next-arrow dashicons dashicons-arrow-right-alt2"></i>
					<?php } ?>
                </div>


            </div> <!-- .Slider-wrapper -->

			<?php if ( apply_filters( 'rtwpvg_show_product_thumbnail_slider', true ) ) {
				?>
                <div class="rtwpvg-thumbnail-wrapper">
                    <div class="rtwpvg-thumbnail-slider swiper <?php echo rtwpvg()->get_option( 'thumbnail_slide' ) ? 'thumbnail-slider-active' : 'thumbnail-slider-deactive'; ?> rtwpvg-thumbnail-columns-<?php echo esc_attr( $columns ) ?> rtwpvg-thumbnail-sm-columns-<?php echo esc_attr( $columns_sm ) ?> rtwpvg-thumbnail-xs-columns-<?php echo esc_attr( $columns_xs ) ?>" data-options='<?php echo htmlspecialchars( wp_json_encode( $thumbnail_slider_js_options ) ); // WPCS: XSS ok. ?>'>
                        <div class="swiper-wrapper">
							<?php
							if ( $has_gallery_thumbnail ):
								echo Functions::get_gallery_image_html( $post_thumbnail_id, array( 'is_main_thumbnail' => false ) );
							endif;
							if ( count( $attachment_ids ) > 0 ) :
								foreach ( $attachment_ids as $key => $attachment_id ) :
									echo Functions::get_gallery_image_html( $attachment_id, array( 'is_main_thumbnail' => false ) );
								endforeach;
							endif;
							?>
                        </div>
                        <i class="rtwpvg-thumbnail-prev-arrow dashicons dashicons-arrow-left-alt2"></i>
                        <i class="rtwpvg-thumbnail-next-arrow dashicons dashicons-arrow-right-alt2"></i>
                    </div>
                </div> <!-- .Thumb-wrapper -->
			<?php } ?>
        </div> <!-- .container -->
    </div> <!-- .rtwpvg-wrapper -->
</div>


