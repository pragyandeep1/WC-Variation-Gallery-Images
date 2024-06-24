<?php

namespace Rtwpvg\Controllers;


class ScriptLoader {

	private $suffix;
	private $version;

	public function __construct() {
		$this->suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$this->version = defined( 'WP_DEBUG' ) ? time() : rtwpvg()->version();

		add_action( 'admin_footer', array( $this, 'admin_template_js' ) );
		add_action( 'wp_footer', array( $this, 'slider_thumbnail_template_js' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {

		if ( apply_filters( 'rtwpvg_disable_enqueue_scripts', false ) ) {
			return;
		}

		$gallery_thumbnails_columns = absint( apply_filters( 'rtwpvg_thumbnails_columns', rtwpvg()->get_option( 'thumbnails_columns' ) ) );
		$gallery_width              = absint( apply_filters( 'rtwpvg_gallery_width', rtwpvg()->get_option( 'gallery_width' ) ) );
		$gallery_md_width           = absint( apply_filters( 'rtwpvg_gallery_md_width', rtwpvg()->get_option( 'gallery_md_width' ) ) );
		$gallery_sm_width           = absint( apply_filters( 'rtwpvg_gallery_sm_width', rtwpvg()->get_option( 'gallery_sm_width' ) ) );
		$gallery_xsm_width          = absint( apply_filters( 'rtwpvg_gallery_xsm_width', rtwpvg()->get_option( 'gallery_xsm_width' ) ) );
		$using_swiper               = rtwpvg()->get_option( 'upgrade_slider_scripts' );
		$gallery_sm_width  = $gallery_sm_width < 100 ? $gallery_sm_width : 100;
		$gallery_xsm_width = $gallery_xsm_width < 100 ? $gallery_xsm_width : 100;

		$thumbnail_position = apply_filters( 'rtwpvg_thumbnail_position', 'bottom' );


	    if( $using_swiper ){
		    wp_enqueue_script( 'swiper', esc_url( rtwpvg()->get_assets_uri( "/js/swiper-bundle.min.js" ) ), array( 'jquery' ), '8.4.5', true );
		    wp_enqueue_style( 'swiper', esc_url( rtwpvg()->get_assets_uri( "/css/swiper-bundle.min.css" ) ), array(), '8.4.5' );
		    wp_enqueue_script( 'rtwpvg', esc_url( rtwpvg()->get_assets_uri( "/js/rtwpvg{$this->suffix}.js" ) ), array(
			    'jquery',
			    'wp-util',
			    'imagesloaded',
		    ), $this->version, true );
	    } else {
		    // legacy support
            wp_enqueue_script( 'rtwpvg-slider', esc_url( rtwpvg()->get_assets_uri( "/js/slick{$this->suffix}.js" ) ), array( 'jquery' ), '1.8.1', true );
            wp_enqueue_style( 'rtwpvg-slider', esc_url( rtwpvg()->get_assets_uri( "/css/slick{$this->suffix}.css" ) ), array(), '1.8.1' );
		    wp_enqueue_script( 'rtwpvg', esc_url( rtwpvg()->get_assets_uri( "/js/slick-rtwpvg{$this->suffix}.js" ) ), array(
			    'jquery',
			    'wp-util',
			    'imagesloaded',
		    ), $this->version, true );
        }


		wp_localize_script( 'rtwpvg', 'rtwpvg', apply_filters( 'rtwpvg_js_options', array(
			'reset_on_variation_change' => rtwpvg()->get_option( 'reset_on_variation_change' ),
			'enable_zoom'               => rtwpvg()->get_option( 'zoom' ),
			'enable_lightbox'           => rtwpvg()->get_option( 'lightbox' ),
			'lightbox_image_click'      => rtwpvg()->get_option( 'lightbox_image_click' ),
			'enable_thumbnail_slide'    => rtwpvg()->active_pro() && rtwpvg()->get_option( 'thumbnail_slide' ) ? true : false, // Mobile Crash Issue fixed
			'thumbnails_columns'        => $gallery_thumbnails_columns,
			'is_vertical'               => in_array( $thumbnail_position, array( 'left', 'right' ) ) ? true : false,
			'thumbnail_position'        => $thumbnail_position,
			'is_mobile'                 => function_exists( 'wp_is_mobile' ) && wp_is_mobile(),
			'gallery_width'             => $gallery_width,
			'gallery_md_width'          => $gallery_md_width,
			'gallery_sm_width'          => $gallery_sm_width,
			'gallery_xsm_width'         => $gallery_xsm_width,
			'using_swiper'              => boolval( $using_swiper ),
		) ) );

		if( $using_swiper ) {
			wp_enqueue_style( 'rtwpvg', esc_url( rtwpvg()->get_assets_uri( "/css/rtwpvg{$this->suffix}.css" ) ), array( 'dashicons' ), $this->version );
		} else {
			// legacy support
			wp_enqueue_style( 'rtwpvg', esc_url( rtwpvg()->get_assets_uri( "/css/slick-rtwpvg{$this->suffix}.css" ) ), array( 'dashicons' ), $this->version );
        }
		$this->add_inline_style();

	}

	public function admin_enqueue_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		if ( in_array( $screen_id, array( 'product', 'edit-product' ) ) ) {
			// wp_deregister_script('wc-admin-variation-meta-boxes');
			// wp_register_script('wc-admin-variation-meta-boxes', rtwpvg()->get_assets_uri("/js/meta-boxes-product-variation{$this->suffix}.js"), ['wc-admin-meta-boxes', 'serializejson', 'media-models', 'backbone', 'jquery-ui-sortable', 'wc-backbone-modal'], $this->version);
			wp_dequeue_script( 'wc-admin-variation-meta-boxes' );
			wp_enqueue_script( 'wc-admin-variation-meta-boxes' );

		}
		if ( ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) || $screen_id === 'product' || ( ( isset( $_GET['page'] ) && $_GET['page'] == "wc-settings" ) && ( isset( $_GET['tab'] ) && $_GET['tab'] == "rtwpvg" ) ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			if ( apply_filters( 'rtwpvg_disable_alpha_color_picker', false ) ) {
				wp_enqueue_script( 'wp-color-picker' );
			} else {
				wp_enqueue_script( 'wp-color-picker-alpha', rtwpvg()->get_assets_uri( "/js/wp-color-picker-alpha{$this->suffix}.js" ), array( 'wp-color-picker' ), '2.1.4', true );
				$colorpicker_l10n = [
					'clear'            => __( 'Clear', 'woo-product-variation-gallery' ),
					'defaultString'    => __( 'Default', 'woo-product-variation-gallery' ),
					'pick'             => __( 'Select Color', 'woo-product-variation-gallery' ),
					'clearAriaLabel'   => __( "Clear color", 'woo-product-variation-gallery' ),
					'defaultAriaLabel' => __( "Select default color", 'woo-product-variation-gallery' ),
					'defaultLabel'     => __( "Color value", 'woo-product-variation-gallery' ),
				];
				wp_localize_script( 'wp-color-picker-alpha', 'wpColorPickerL10n', $colorpicker_l10n );
			}

			wp_enqueue_media();
			wp_enqueue_style( 'rtwpvg-admin', esc_url( rtwpvg()->get_assets_uri( "css/admin{$this->suffix}.css" ) ), array(), $this->version );
			wp_enqueue_script( 'rtwpvg-admin', esc_url( rtwpvg()->get_assets_uri( "js/admin{$this->suffix}.js" ) ), array(
				'jquery',
				'jquery-ui-sortable',
				'wp-util'
			), $this->version, true );

			wp_localize_script( 'rtwpvg-admin', 'rtwpvg_admin', array(
				'ajaxurl'      => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
				'nonce'        => wp_create_nonce( 'rtwpvg_nonce' ),
				'choose_image' => esc_html__( 'Choose Image', 'woo-product-variation-gallery' ),
				'choose_video' => esc_html__( 'Choose Video', 'woo-product-variation-gallery' ),
				'add_image'    => esc_html__( 'Add Images', 'woo-product-variation-gallery' ),
				'add_video'    => esc_html__( 'Add Video', 'woo-product-variation-gallery' ),
				'sure_txt'     => esc_html__( 'Are you sure to delete?', 'woo-product-variation-gallery' )
			) );
		}
	}

	public function add_inline_style() {
		if ( apply_filters( 'rtwpvg_disable_inline_style', false ) ) {
			return;
		}
		// $single_image_width = absint( wc_get_theme_support( 'single_image_width', get_option( 'woocommerce_single_image_width', 600 ) ) );
		$gallery_margin         = absint( apply_filters( 'rtwpvg_gallery_margin', rtwpvg()->get_option( 'gallery_margin' ) ) );
		$gallery_thumbnails_gap = absint( apply_filters( 'rtwpvg_thumbnails_gap', rtwpvg()->get_option( 'thumbnails_gap' ) ) );
		$gallery_width          = absint( apply_filters( 'rtwpvg_gallery_width', rtwpvg()->get_option( 'gallery_width' ) ) );
		$gallery_md_width       = absint( apply_filters( 'rtwpvg_gallery_md_width', rtwpvg()->get_option( 'gallery_md_width' ) ) );
		$gallery_sm_width       = absint( apply_filters( 'rtwpvg_gallery_sm_width', rtwpvg()->get_option( 'gallery_sm_width' ) ) );
		$gallery_xsm_width      = absint( apply_filters( 'rtwpvg_gallery_xsm_width', rtwpvg()->get_option( 'gallery_xsm_width' ) ) );

		$gallery_sm_width  = $gallery_sm_width < 100 ? $gallery_sm_width : 100;
		$gallery_xsm_width = $gallery_xsm_width < 100 ? $gallery_xsm_width : 100;

		$preloader_image = absint( apply_filters( 'rtwpvg_preloader_image', rtwpvg()->get_option( 'preloader_image' ) ) );
		ob_start();
		?>
        <style type="text/css">
            :root {
                --rtwpvg-thumbnail-gap: <?php echo absint( $gallery_thumbnails_gap ); ?>px;
                --rtwpvg-gallery-margin-bottom: <?php echo absint( $gallery_margin ); ?>px;
            }

            /* Large Screen / Default Width */
            .rtwpvg-images {
                max-width: <?php echo absint( $gallery_width ); ?>%;
                width: 100%;
                float: none;
            }

            /* MD, Desktops */
            <?php if( $gallery_md_width > 0 ): ?>
            @media only screen and (max-width: 992px) {
                .rtwpvg-images {
                    max-width: <?php echo absint( $gallery_md_width ); ?>%;
                }
            }

            <?php endif; ?>

            /* SM Devices, Tablets */
            <?php if( $gallery_sm_width > 0 ): ?>
            @media only screen and (max-width: 768px) {
                .rtwpvg-images {
                    max-width: <?php echo absint( $gallery_sm_width ); ?>% !important;
                }
            }

            <?php endif; ?>

            /* XSM Devices, Phones */
            <?php if( $gallery_xsm_width > 0 ): ?>
            @media only screen and (max-width: 480px) {
                .rtwpvg-images {
                    max-width: <?php echo absint( $gallery_xsm_width ); ?>% !important;
                }
            }

            <?php endif; ?>

            <?php if ( $preloader_image ): 
                $img_url = '';
                $img_src = wp_get_attachment_url( $preloader_image );
                if ( $img_src ) { 
                    $img_url = $img_src;
                }
            ?>
            .rtwpvg-images .rtwpvg-wrapper.loading-rtwpvg::after {
                background: url(<?php echo esc_url($img_url); ?>) no-repeat center center;
            }

            <?php endif; ?>
        </style>
		<?php
		$css = ob_get_clean();
		$css = str_ireplace( array( '<style type="text/css">', '</style>' ), '', $css );

		$css = apply_filters( 'rtwpvg_inline_style', $css );
		wp_add_inline_style( 'rtwpvg', $css );
	}

	public function admin_template_js() {
		require_once rtwpvg()->locate_template( 'template-admin-thumbnail' );
	}

	function slider_thumbnail_template_js() {
		require_once rtwpvg()->locate_template( 'template-slider' );
		require_once rtwpvg()->locate_template( 'template-thumbnail' );
	}

}

