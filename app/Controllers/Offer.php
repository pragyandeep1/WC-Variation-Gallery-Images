<?php

namespace Rtwpvg\Controllers;

class Offer {
	public function __construct() {
		add_action(
			'admin_init',
			function () {
				$current = time();

			}
		);

	}
	/**
	 * Check if plugin is validate.
	 *
	 * @return bool
	 */
	public function check_plugin_validity(): bool {
		$license_status = rtwpvg()->get_option( 'license_status' );
		$status         = ( ! empty( $license_status ) && 'valid' === $license_status ) ? true : false;
		return $status;
	}

	/**
	 * Undocumented function.
	 *
	 * @return void
	 */
	public static function new_year_notice() {
		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_script('jquery');
			}
		);

		add_action(
			'admin_notices',
			function () { ?>
				<div class="notice notice-info is-dismissible" data-rtwpvgdismissable="rtwpvg_ny_2023"
					style="display:grid;grid-template-columns: 100px auto;padding-top: 25px; padding-bottom: 22px;">
				</div>
					<?php
			}
		);

		add_action(
			'admin_footer',
			function () {
				?>
				<script type="text/javascript">
					(function ($) {
						$(function () {
							setTimeout(function () {
								$('div[data-rtwpvgdismissable] .notice-dismiss, div[data-rtwpvgdismissable] .button-dismiss')
									.on('click', function (e) {
										e.preventDefault();
										$.post(ajaxurl, {
											'action': 'rtwpvg_dismiss_admin_notice',
											'nonce': <?php echo json_encode(wp_create_nonce('rtwpvg-dismissible-notice')); ?>
										});
										$(e.target).closest('.is-dismissible').remove();
									});
							}, 1000);
						});
					})(jQuery);
				</script>
					<?php
			}
		);

		add_action(
			'wp_ajax_rtwpvg_dismiss_admin_notice',
			function () {
				check_ajax_referer('rtwpvg-dismissible-notice', 'nonce');

				update_option('rtwpvg_ny_2023', '1');
				wp_die();
			}
		);
	}


	/**
	 * Undocumented function.
	 *
	 * @return void
	 */
	public static function wc_release_notice() {
		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_script('jquery');
			}
		);

		add_action(
			'admin_notices',
			function () { ?>
                <style>
                    .woobundle-release-notice {
                        --e-button-context-color: #5d3dfd;
                        --e-button-context-color-dark: #0047FF;
                        --e-button-context-tint: rgb(75 47 157/4%);
                        --e-focus-color: rgb(75 47 157/40%);
                    }

                    .woobundle-release-notice .button-primary,
                    .woobundle-release-notice .button-dismiss {
                        display: inline-block;
                        border: 0;
                        border-radius: 3px;
                        background: var(--e-button-context-color-dark);
                        color: #fff;
                        vertical-align: middle;
                        text-align: center;
                        text-decoration: none;
                        white-space: nowrap;
                        margin-right: 5px;
                    }
                    .woobundle-release-notice .button-dismiss {
                        border: 1px solid;
                        background: 0 0;
                        color: var(--e-button-context-color);
                        background: #fff;
                    }
                    .wp-core-ui .woobundle-release-notice .button-dismiss:hover,
                    .woobundle-release-notice .button-dismiss {
                        background: #fff;
                    }
                    .woobundle-release-notice .button-primary:hover{
                        background: var(--e-button-context-color-dark);
                    }
                </style>
                <div class="woobundle-release-notice notice notice-info is-dismissible" data-woobundle="woobundle_release_notice"
                     style="display:grid;grid-template-columns: 100px auto;padding-top: 25px; padding-bottom: 22px;column-gap: 15px;">
                    <img alt="WooCommerce Bundle"
                         src="<?php echo rtwpvg()->get_assets_uri('images/shop-100-100.svg'); ?>" width="100px"
                         height="100px" style="grid-row: 1 / 4; justify-self: center"/>
                    <h3 style="margin:0;"><?php echo sprintf('%s !!', 'ShopBuilder - Elementor Addon Pro is now available!' ); ?></h3>

                    <p style="margin:0 0 2px; padding: 5px 0; max-width: 100%; font-size: 14px;">
                        Acquire our WooCommerce bundled <b>ShopBuilder Elementor addon</b>, <b>Variation Swatches</b>, and <b>Variation Gallery </b> plugin to enjoy <b>savings of up to 30%!</b>
                    </p>
                </div>
				<?php
			}
		);

		add_action(
			'admin_footer',
			function () {
				?>
                <script type="text/javascript">
                    (function ($) {
                        $(function () {
                            setTimeout(function () {
                                $('div[data-woobundle] .notice-dismiss, div[data-woobundle] .button-dismiss')
                                    .on('click', function (e) {
                                        e.preventDefault();
                                        $.post(ajaxurl, {
                                            'action': 'woobundle_dismiss_admin_notice',
                                            'nonce': <?php echo json_encode(wp_create_nonce('woobundle-dismissible-notice')); ?>
                                        });
                                        $(e.target).closest('.is-dismissible').remove();
                                    });
                            }, 1000);
                        });
                    })(jQuery);
                </script>
				<?php
			}
		);
	}

}
