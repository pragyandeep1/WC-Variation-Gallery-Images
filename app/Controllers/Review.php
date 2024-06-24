<?php

namespace Rtwpvg\Controllers;

/**
 * Review class
 */
class Review {
	/**
	 * Init
	 *
	 * @return void
	 */
	public function __construct() {
		$current      = time();
	}

	/**
	 * Add plugin activation time.
	 *
	 * @return void
	 */
	public static function rtwpvg_activation_time() {
		$get_activation_time = strtotime( 'now' );
		add_option( 'rtwpvg_plugin_activation_time', $get_activation_time ); // replace your_plugin with Your plugin name
	}

	/**
     * Check if review notice should be shown or not
     *
     * @return void
     */
	public static function rtwpvg_check_installation_time() {
		if ( isset($GLOBALS['woobundle_release_notice']) ) {
			return;
		}
		if ( isset($GLOBALS['woobundle_notice']) ) {
			return;
		}
		// Added Lines Start
		$nobug = get_option( 'rtwpvg_spare_me', '0' );

		if ( $nobug == '1' || $nobug == '3' ) {
			return; 
		}

		$install_date = get_option( 'rtwpvg_plugin_activation_time' );
		$past_date    = strtotime( '-10 days' );

		$remind_time = get_option( 'rtwpvg_remind_me' );
		$remind_due  = strtotime( '+15 days', $remind_time );
		$now         = strtotime( 'now' );
        
		if ( $now >= $remind_due ) {
			add_action( 'admin_notices', [ __CLASS__, 'rtwpvg_display_admin_notice' ] );
		} elseif ( ( $past_date >= $install_date ) && $nobug !== '2' ) {
			add_action( 'admin_notices', [ __CLASS__, 'rtwpvg_display_admin_notice' ] );
		}
	}
    /**
     * Remove the notice for the user if review already done or if the user does not want to
     *
     * @return void
     */
	public static function rtwpvg_spare_me() {

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'rtwpvg_notice_nonce' ) ) {
			return;
		}

		if ( isset( $_GET['rtwpvg_spare_me'] ) && ! empty( $_GET['rtwpvg_spare_me'] ) ) {
			$spare_me = $_GET['rtwpvg_spare_me'];
			if ( 1 == $spare_me ) {
				update_option( 'rtwpvg_spare_me', '1' );
			}
		}

		if ( isset( $_GET['rtwpvg_remind_me'] ) && ! empty( $_GET['rtwpvg_remind_me'] ) ) {
			$remind_me = $_GET['rtwpvg_remind_me'];
			if ( 1 == $remind_me ) {
				$get_activation_time = strtotime( 'now' );
				update_option( 'rtwpvg_remind_me', $get_activation_time );
				update_option( 'rtwpvg_spare_me', '2' );
			}
		}

		if ( isset( $_GET['rtwpvg_rated'] ) && ! empty( $_GET['rtwpvg_rated'] ) ) {
			$rtwpvg_rated = $_GET['rtwpvg_rated'];
			if ( 1 == $rtwpvg_rated ) {
				update_option( 'rtwpvg_rated', 'yes' );
				update_option( 'rtwpvg_spare_me', '3' );
			}
		}
	}

	protected static function rtwpvg_current_admin_url() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );

		if ( ! $uri ) {
			return '';
		}
		return remove_query_arg( [ '_wpnonce', '_wc_notice_nonce', 'wc_db_update', 'wc_db_update_nonce', 'wc-hide-notice', 'rtwpvg_spare_me', 'rtwpvg_remind_me', 'rtwpvg_rated' ], admin_url( $uri ) );
	}


	/**
	 * Display Admin Notice, asking for a review
	 **/
	public static function rtwpvg_display_admin_notice() {
		// WordPress global variable
		global $pagenow;
		$exclude = [ 'themes.php', 'users.php', 'tools.php', 'options-general.php', 'options-writing.php', 'options-reading.php', 'options-discussion.php', 'options-media.php', 'options-permalink.php', 'options-privacy.php', 'edit-comments.php', 'upload.php', 'media-new.php', 'admin.php', 'import.php', 'export.php', 'site-health.php', 'export-personal-data.php', 'erase-personal-data.php' ];

		if ( ! in_array( $pagenow, $exclude ) ) {

			$args = [ '_wpnonce' => wp_create_nonce( 'rtwpvg_notice_nonce' ) ];

			$dont_disturb = add_query_arg( $args + [ 'rtwpvg_spare_me' => '1' ], self::rtwpvg_current_admin_url() );
			$remind_me    = add_query_arg( $args + [ 'rtwpvg_remind_me' => '1' ], self::rtwpvg_current_admin_url() );
			$rated        = add_query_arg( $args + [ 'rtwpvg_rated' => '1' ], self::rtwpvg_current_admin_url() );
			$reviewurl    = 'https://wordpress.org/support/plugin/woo-product-variation-gallery/reviews/?filter=5#new-post';

			?>
			<div class="notice rtrs-review-notice rtrs-review-notice--extended"> 
				<div class="rtrs-review-notice_content">
					<h3>Enjoying Variation Images Gallery?</h3>
					<p>Thank you for choosing Variation Images Gallery. If you have found our plugin useful and makes you smile, please consider giving us a 5-star rating on WordPress.org. It will help us to grow.</p>
					<div class="rtrs-review-notice_actions">
						<a href="<?php echo esc_url( $reviewurl ); ?>" class="rtrs-review-button rtrs-review-button--cta" target="_blank"><span>⭐ Yes, You Deserve It!</span></a>
						<a href="<?php echo esc_url( $rated ); ?>" class="rtrs-review-button rtrs-review-button--cta rtrs-review-button--outline"><span>😀 Already Rated!</span></a>
						<a href="<?php echo esc_url( $remind_me ); ?>" class="rtrs-review-button rtrs-review-button--cta rtrs-review-button--outline"><span>🔔 Remind Me Later</span></a>
						<a href="<?php echo esc_url( $dont_disturb ); ?>" class="rtrs-review-button rtrs-review-button--cta rtrs-review-button--error rtrs-review-button--outline"><span>😐 No Thanks</span></a>
					</div>
				</div> 
			</div>
			<style> 
			.rtrs-review-button--cta {
				--e-button-context-color: #5d3dfd;
				--e-button-context-color-dark: #5d3dfd;
				--e-button-context-tint: rgb(75 47 157/4%);
				--e-focus-color: rgb(75 47 157/40%);
			} 
			.rtrs-review-notice {
				position: relative;
				margin: 5px 20px 5px 2px;
				border: 1px solid #ccd0d4;
				background: #fff;
				box-shadow: 0 1px 4px rgba(0,0,0,0.15);
				font-family: Roboto, Arial, Helvetica, Verdana, sans-serif;
				border-inline-start-width: 4px;
			}
			.rtrs-review-notice.notice {
				padding: 0;
			}
			.rtrs-review-notice:before {
				position: absolute;
				top: -1px;
				bottom: -1px;
				left: -4px;
				display: block;
				width: 4px;
				background: -webkit-linear-gradient(bottom, #5d3dfd 0%, #6939c6 100%);
				background: linear-gradient(0deg, #5d3dfd 0%, #6939c6 100%);
				content: "";
			} 
			.rtrs-review-notice_content {
				padding: 20px;
			} 
			.rtrs-review-notice_actions > * + * {
				margin-inline-start: 8px;
				-webkit-margin-start: 8px;
				-moz-margin-start: 8px;
			} 
			.rtrs-review-notice p {
				margin: 0;
				padding: 0;
				line-height: 1.5;
			}
			p + .rtrs-review-notice_actions {
				margin-top: 1rem;
			}
			.rtrs-review-notice h3 {
				margin: 0;
				font-size: 1.0625rem;
				line-height: 1.2;
			}
			.rtrs-review-notice h3 + p {
				margin-top: 8px;
			} 
			.rtrs-review-button {
				display: inline-block;
				padding: 0.4375rem 0.75rem;
				border: 0;
				border-radius: 3px;;
				background: var(--e-button-context-color);
				color: #fff;
				vertical-align: middle;
				text-align: center;
				text-decoration: none;
				white-space: nowrap; 
			}
			.rtrs-review-button:active {
				background: var(--e-button-context-color-dark);
				color: #fff;
				text-decoration: none;
			}
			.rtrs-review-button:focus {
				outline: 0;
				background: var(--e-button-context-color-dark);
				box-shadow: 0 0 0 2px var(--e-focus-color);
				color: #fff;
				text-decoration: none;
			}
			.rtrs-review-button:hover {
				background: var(--e-button-context-color-dark);
				color: #fff;
				text-decoration: none;
			} 
			.rtrs-review-button.focus {
				outline: 0;
				box-shadow: 0 0 0 2px var(--e-focus-color);
			} 
			.rtrs-review-button--error {
				--e-button-context-color: #d72b3f;
				--e-button-context-color-dark: #ae2131;
				--e-button-context-tint: rgba(215,43,63,0.04);
				--e-focus-color: rgba(215,43,63,0.4);
			}
			.rtrs-review-button.rtrs-review-button--outline {
				border: 1px solid;
				background: 0 0;
				color: var(--e-button-context-color);
			}
			.rtrs-review-button.rtrs-review-button--outline:focus {
				background: var(--e-button-context-tint);
				color: var(--e-button-context-color-dark);
			}
			.rtrs-review-button.rtrs-review-button--outline:hover {
				background: var(--e-button-context-tint);
				color: var(--e-button-context-color-dark);
			} 
			</style>
			<?php
		}
	}




}
