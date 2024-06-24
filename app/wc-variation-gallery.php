<?php

use Rtwpvg\Controllers\Hooks;
use Rtwpvg\Controllers\Offer;
use Rtwpvg\Controllers\Install;
use Rtwpvg\Controllers\ProHooks;
use Rtwpvg\Controllers\SettingsAPI;
use Rtwpvg\Controllers\ScriptLoader;
use Rtwpvg\Controllers\ThemeSupport;
use Rtwpvg\Controllers\Notifications;
use Rtwpvg\Controllers\Review;

defined('ABSPATH') or die('Keep Quit');

require_once RTWPVG_PLUGIN_PATH . 'vendor/autoload.php';

if (! class_exists('WooProductVariationGallery')):
	final class WooProductVariationGallery {
		protected static $_instance = null;

		private $_settings_api;

		public static function get_instance() {
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public function __construct() {
			$this->define_constants();
			$this->hooks();
			do_action('rtwpvg_loaded', $this);
		}

		public function define_constants() {
			$this->define('RTWPVG_PLUGIN_URI', plugin_dir_url(RTWPVG_PLUGIN_FILE));
			$this->define('RTWPVG_PLUGIN_DIRNAME', dirname(plugin_basename(RTWPVG_PLUGIN_FILE))); // plugin-slug
			$this->define('RTWPVG_PLUGIN_BASENAME', plugin_basename(RTWPVG_PLUGIN_FILE)); // plugin-slug/plugin-slug.php
		}

		public function hooks() {
			$this->load_plugin_textdomain();
			new Notifications();
			new Offer();
			new Review();
			
			if ($this->is_valid_php_version() && $this->is_wc_active()) {
				add_action('init', [$this, 'settings_api'], 6);
				new ScriptLoader();
				new Hooks();
				new ThemeSupport();
			}
		}

		/**
		 * @return SettingsAPI
		 */
		public function settings_api() {
			if (! $this->_settings_api) {
				$this->_settings_api = new SettingsAPI();
			}

			return $this->_settings_api;
		}

		/**
		 * @param $id
		 * @param null $givenDefault
		 *
		 * @return mixed
		 */
		public function get_option($id, $givenDefault = null) {
			if (! $this->_settings_api) {
				$this->settings_api();
			}

			return $this->_settings_api->get_option($id, $givenDefault);
		}

		/**
		 * @param $id
		 * @param $value
		 *
		 * @return mixed
		 */
		public function update_option($id, $value) {
			if (! $this->_settings_api) {
				$this->settings_api();
			}

			return $this->_settings_api->update_option($id, $value);
		}

		/**
		 * @param      $name
		 * @param      $value
		 */
		public function define($name, $value) {
			if (! defined($name)) {
				define($name, $value);
			}
		}

		public function basename() {
			return RTWPVG_PLUGIN_BASENAME;
		}

		public function dirname() {
			return RTWPVG_PLUGIN_DIRNAME;
		}

		public function version() {
			return RTWPVG_VERSION;
		}

		/**
		 * @return bool
		 */
		public function is_valid_php_version() {
			return version_compare(PHP_VERSION, '5.6.0', '>=');
		}

		/**
		 * @return bool
		 */
		public function is_wc_active() {
			return class_exists('WooCommerce');
		}

		/**
		 * @return bool
		 */
		public function is_valid_wc_version() {
			return version_compare(WC_VERSION, '3.2', '>');
		}

		/*Load Localisation files.*/
		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
			$locale = apply_filters('plugin_locale', $locale, 'woo-product-variation-gallery');
			unload_textdomain('woo-product-variation-gallery');
			load_textdomain('woo-product-variation-gallery', WP_LANG_DIR . '/woo-product-variation-gallery-pro/woo-product-variation-gallery-' . $locale . '.mo');
			load_plugin_textdomain('woo-product-variation-gallery', false, trailingslashit($this->dirname()) . 'languages');
		}

		public function get_assets_uri($file) {
			$file = ltrim($file, '/');

			return trailingslashit(RTWPVG_PLUGIN_URI . 'assets') . $file;
		}

		public function get_images_uri($file) {
			$file = ltrim($file, '/');

			return trailingslashit(RTWPVG_PLUGIN_URI . 'assets/images') . $file;
		}

		public function get_template_file_path($file, $path = null) {
			$file = ltrim($file, '/');
			$path = $path ? $path : RTWPVG_PLUGIN_PATH;
			return trailingslashit($path . 'templates') . $file . '.php';
		}

		public function locate_template($name) {

			$template = apply_filters( 'rtwpvg_add_locate_template', [
				"woo-product-variation-gallery/$name.php",
				"woo-product-variation-gallery-pro/$name.php",
			]);
			//error_log( print_r( $template , true ) . "\n\n" , 3, __DIR__ . '/log.txt' );
			if (! $template_file = locate_template($template)) {
				$template_file = $this->get_template_file_path($name);
			}

			return apply_filters('rtwpvg_locate_template', $template_file, $name);
		}
		public function active_pro() {
			if ( defined( 'RTWPVGP_VERSION' ) ) { 
				return true;
			}
			return false;

		}

		public function active_theme() {
			$my_theme = wp_get_theme();
			return $my_theme->get( 'TextDomain' );
		}


	}

	/**
	 * @return WooProductVariationGallery|null
	 */
	function rtwpvg() {
		return WooProductVariationGallery::get_instance();
	}

	register_activation_hook(RTWPVG_PLUGIN_FILE, [Install::class, 'activated']);
	register_deactivation_hook(RTWPVG_PLUGIN_FILE, [Install::class, 'deactivated']);

	add_action('plugins_loaded', 'rtwpvg');

endif;

