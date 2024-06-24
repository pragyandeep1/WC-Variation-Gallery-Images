<?php

namespace Rtwpvg\Controllers;

use Rtwpvg\Helpers\Options;
use Rtwpvgp\Controllers\Licensing;

class SettingsAPI {

	private $setting_id = 'rtwpvg';
	private $defaults = array();
	private $sections = array();

	public function __construct() {
		$this->sections = Options::get_settings_sections();
		add_action( 'init', array( $this, 'set_defaults' ), 8 );
		add_filter( 'plugin_action_links_' . rtwpvg()->basename(), array(
			$this,
			'plugin_action_links'
		) );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		add_action( 'woocommerce_settings_tabs_' . $this->setting_id, array( $this, 'settings_tab' ) );
		add_action( 'woocommerce_update_options_' . $this->setting_id, array( $this, 'update_settings' ) );
		add_action( 'woocommerce_admin_field_' . $this->setting_id, array( $this, 'global_settings' ) );

		if ( ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' ) && ( isset( $_GET['tab'] ) && $_GET['tab'] == 'rtwpvg' ) ) {
            add_action('admin_footer', array($this, 'pro_alert_html'));
        }
	}

	public function set_defaults() {
		foreach ( $this->sections as $section ) {
			foreach ( $section['fields'] as $field ) {
				$field['default'] = isset( $field['default'] ) ? $field['default'] : null;
				$this->set_default( $field['id'], $field['type'], $field['default'] );
			}
		}
	}

	private function set_default( $key, $type, $value ) {
		$this->defaults[ $key ] = array( 'id' => $key, 'type' => $type, 'value' => $value );
	}

	private function get_default( $key ) {
		return isset( $this->defaults[ $key ] ) ? $this->defaults[ $key ] : null;
	}

	public function get_defaults() {
		return $this->defaults;
	}

	public function plugin_action_links( $links ) {
		$new_links = array(
		);

		return array_merge( $links, $new_links );
	}

	public function add_settings_tab( $settings_tabs ) {

		return $settings_tabs;
	}

	public function settings_tab() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	public function update_settings() {
		woocommerce_update_options( $this->get_settings() );
		$this->update_licencing_status();
	}

	public function get_settings() {
		$settings = array(
			array(
				'name' => 'Variation Images Gallery for WooCommerce Settings',
				'type' => 'title',
				'desc' => '',
				'id'   => 'rtwpvg_settings_section'
			),
			array(
				'type' => $this->setting_id,
				'id'   => $this->setting_id
			),
			'section_end' => array(
				'type' => 'sectionend',
				'id'   => 'rtwpvg_settings_section'
			)
		);

		return apply_filters( 'rtwpvg_get_settings', $settings );
	}

	private function do_settings_fields( $fields ) {
		foreach ( (array) $fields as $field ) {
			$custom_attributes = $this->array2html_attr( isset( $field['attributes'] ) ? $field['attributes'] : array() );
			$wrapper_id        = ! empty( $field['id'] ) ? esc_attr( $field['id'] ) . '-wrapper' : '';
			$dependency        = ! empty( $field['require'] ) ? '' : '';
			$html              = '';
			if ( $field['type'] == 'title' ) {
				$html .= sprintf( '<div class="rtwpvg-item-title">%s%s</div>',
					isset( $field['title'] ) && $field['title'] ? "<h3>{$field['title']}</h3>" : '',
					$this->get_field_description( $field )
				);
			} else if ( $field['type'] == 'feature' ) {
				$html .= sprintf( '<div class="rtwpvg-item-feature">%s%s%s</div>',
					isset( $field['title'] ) && $field['title'] ? "<h3>{$field['title']}</h3>" : '',
					$this->get_field_description( $field ),
					$this->field_callback( $field )
				);
			} else { 

				$pro_label = ( isset( $field['is_pro'] ) && $field['is_pro'] ) && !function_exists('rtwpvgp') ? '<span class="rtvg-pro rtvg-tooltip">' . esc_html__( '[Pro]', 'woo-product-variation-gallery' ) . '<span class="rtvg-tooltiptext">'.esc_html__( 'This is premium field', 'woo-product-variation-gallery' ).'</span></span>' : '';
                $pro_label = apply_filters('rtvg_pro_label', $pro_label);

                $html .= sprintf('<div class="rtwpvg-field-label">%s %s</div>',
                    isset($field['label_for']) && !empty($field['label_for']) ?
                        sprintf('<label for="%s">%s</label>', esc_attr($field['label_for']), $field['title']) :
                        $field['title'],
                    wp_kses($pro_label, array( 'div' => array( 'class' => array() ), 'span' => array( 'class' => array() ) ) )
                );

                $pro_class = ( isset( $field['is_pro'] ) && $field['is_pro'] ) && !function_exists('rtwpvgp') ? 'pro-field' : ''; 
				$pro_overlay_div = ( isset( $field['is_pro'] ) && $field['is_pro'] ) && !function_exists('rtwpvgp') ? '<div class="pro-field-overlay"></div>' : '';
                $html .= sprintf('<div class="rtwpvg-field %s">%s %s</div>', $pro_class, $pro_overlay_div, $this->field_callback($field));
			}
			echo sprintf( '<div id="%s" class="rtwpvg-setting-field" %s %s>%s</div>', $wrapper_id, $custom_attributes, $dependency, $html );
		}
	}

	private function last_tab_input() {
		printf('<input type="hidden" id="_last_active_tab" name="%s[_last_active_tab]" value="%s">', esc_attr($this->setting_id), esc_attr($this->get_last_active_tab()));
	}

	public function field_callback( $field ) {

		switch ( $field['type'] ) {
			case 'radio':
				$field_html = $this->radio_field_callback( $field );
				break;

			case 'checkbox':
				$field_html = $this->checkbox_field_callback( $field );
				break;

			case 'switch':
				$field_html = $this->switch_field_callback( $field );
				break;

			case 'select':
				$field_html = $this->select_field_callback( $field );
				break;

			case 'number':
				$field_html = $this->number_field_callback( $field );
				break;

			case 'image':
				$field_html = $this->image_field_callback( $field );
				break;

			case 'color':
				$field_html = $this->color_field_callback( $field );
				break;

			case 'post_select':
				$field_html = $this->post_select_field_callback( $field );
				break;

			case 'feature':
				$field_html = $this->feature_field_callback( $field );
				break; 

			default:
				$field_html = $this->text_field_callback( $field );
				break;
		}
		ob_start();
		echo $field_html;
		do_action( 'rtwpvg_settings_field_callback', $field );

		return ob_get_clean();

	}

	public function checkbox_field_callback( $args ) {

		$value = (bool) $this->get_option( $args['id'] );  

		$attrs = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';

		if ( ( isset( $args['is_pro'] ) && $args['is_pro'] ) && !function_exists('rtwpvgp') ) {
            $attrs .= 'readonly';
        }

		return sprintf( '<fieldset><label><input %1$s type="checkbox" id="%2$s-field" name="%4$s[%2$s]" value="%3$s" %5$s/> %6$s</label></fieldset>',
			$attrs,
			$args['id'],
			true,
			$this->setting_id,
			checked( $value, true, false ),
			isset( $args['desc'] ) ? esc_attr( $args['desc'] ) : null
		);

	}

	public function switch_field_callback( $args ) {

		$value = (bool) $this->get_option( $args['id'] );  

		$attrs = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';

		if ( ( isset( $args['is_pro'] ) && $args['is_pro'] ) && !function_exists('rtwpvgp') ) {
            $attrs .= 'readonly';
        } 
		return sprintf( '<fieldset><label class="rtwpvg-switch"><input %1$s type="checkbox" id="%2$s-field" name="%4$s[%2$s]" value="%3$s" %5$s/><span class="rtwpvg-switch-slider round"></span></label>%6$s</fieldset>',
			$attrs,
			$args['id'],
			true,
			$this->setting_id,
			checked( $value, true, false ),
			isset( $args['desc'] ) && $args['desc'] ? '<p class="description">' . $args['desc'] . '</p>' : null
		); 
	}

	public function radio_field_callback( $args ) {
		$options = apply_filters( "rtwpvg_settings_{$args[ 'id' ]}_radio_options", $args['options'] );
		$value   = esc_attr( $this->get_option( $args['id'] ) );

		$attrs = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';


		$html = '<fieldset>';
		$html .= implode( '<br />', array_map( function ( $key, $option ) use ( $attrs, $args, $value ) {
			return sprintf( '<label><input %1$s type="radio" id="%2$s-field" name="%4$s[%2$s]" value="%3$s" %5$s/> %6$s</label>', $attrs, $args['id'], $key, $this->setting_id, checked( $value, $key, false ), $option );
		}, array_keys( $options ), $options ) );
		$html .= $this->get_field_description( $args );
		$html .= '</fieldset>';

		return $html;
	}

	public function select_field_callback( $args ) {
		$options = apply_filters( "rtwpvg_settings_{$args[ 'id' ]}_select_options", $args['options'] );
		$value   = esc_attr( $this->get_option( $args['id'] ) );
		$options = array_map( function ( $key, $option ) use ( $value ) {
			return "<option value='{$key}'" . selected( $key, $value, false ) . ">{$option}</option>";
		}, array_keys( $options ), $options );
		$size    = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$attrs = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';

		if ( ( isset( $args['is_pro'] ) && $args['is_pro'] ) && !function_exists('rtwpvgp') ) {
            $attrs .= 'disabled';
        }

		$html = sprintf( '<select %5$s class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]">%3$s</select>', $size, $args['id'], implode( '', $options ), $this->setting_id, $attrs );
		$html .= $this->get_field_description( $args );

		return $html;
	}

	public function get_field_description( $args ) {
		if ( isset( $args['desc'] ) && ! empty( $args['desc'] ) ) {
			$desc = sprintf( '<p class="description">%s%s</p>',
				$args['id'] == 'license_key' ? sprintf( '<span class="license-status">%s</span>',
					trim( $this->get_option( $args['id'] ) ?? '' ) ? sprintf(
						'<span class="rt-licensing-btn button-secondary %s">%s</span>',
						$this->get_option( 'license_status' ) == "valid" ? "danger license_deactivate" : "button-primary license_activate",
						$this->get_option( 'license_status' ) == "valid" ? esc_html__( "Deactivate License", "woo-product-variation-gallery" ) : esc_html__( "Activate License", "woo-product-variation-gallery" )
					) : null
				) : null,
				$args['desc']
			);
		} else {
			$desc = '';
		}

		return $desc;
	}

	public function post_select_field_callback( $args ) {

		$options = apply_filters( "rtwpvg_settings_{$args[ 'id' ]}_post_select_options", $args['options'] );

		$value = esc_attr( $this->get_option( $args['id'] ) );

		$options = array_map( function ( $option ) use ( $value ) {
			return "<option value='{$option->ID}'" . selected( $option->ID, $value, false ) . ">$option->post_title</option>";
		}, $options );

		$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$html = sprintf( '<select class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]">%3$s</select>', $size, $args['id'], implode( '', $options ), $this->setting_id );
		$html .= $this->get_field_description( $args );
		return $html;
	}

	public function text_field_callback( $args ) {
		$value = esc_attr( $this->get_option( $args['id'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$attrs = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';

		$html = sprintf( '<input %5$s type="text" class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]" value="%3$s"/>', $size, $args['id'], $value, $this->setting_id, $attrs );
		$html .= $this->get_field_description( $args );

		return $html;
	}

	public function feature_field_callback( $args ) {

		$is_html = isset( $args['html'] );

		if ( $is_html ) {
			$html = $args['html'];
		} else {
			$image = esc_url( $args['screen_shot'] );
			$link  = esc_url( $args['product_link'] );


			$width = isset( $args['width'] ) ? $args['width'] : '70%';

			$html = sprintf( '<a target="_blank" href="%s"><img style="width: %s" src="%s" /></a>', $link, $width, $image );
			$html .= $this->get_field_description( $args );
		}


		return $html;
	}

	public function color_field_callback( $args ) {
		$value = esc_attr( $this->get_option( $args['id'] ) );
		$alpha = isset( $args['alpha'] ) && $args['alpha'] === true ? ' data-alpha="true"' : '';
		$html  = sprintf( '<input type="text" %1$s class="rtwpvg-color-picker" id="%2$s-field" name="%4$s[%2$s]" value="%3$s"  data-default-color="%3$s" />', $alpha, $args['id'], $value, $this->setting_id );
		$html  .= $this->get_field_description( $args );

		return $html;
	}

	public function number_field_callback( $args ) {
		$value  = esc_attr( $this->get_option( $args['id'] ) );
		$size   = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'small';
		$min    = isset( $args['min'] ) && ! is_null( $args['min'] ) ? 'min="' . $args['min'] . '"' : '';
		$max    = isset( $args['max'] ) && ! is_null( $args['max'] ) ? 'max="' . $args['max'] . '"' : '';
		$step   = isset( $args['step'] ) && ! is_null( $args['step'] ) ? 'step="' . $args['step'] . '"' : '';
		$suffix = isset( $args['suffix'] ) && ! is_null( $args['suffix'] ) ? ' <span>' . $args['suffix'] . '</span>' : '';
		$attrs  = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';
		$html   = sprintf( '<input %9$s type="number" class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]" value="%3$s" %5$s %6$s %7$s /> %8$s', $size, $args['id'], $value, $this->setting_id, $min, $max, $step, $suffix, $attrs );
		$html   .= $this->get_field_description( $args );
		return $html;
	}

	public function image_field_callback( $args ) {
		$h = null; 
        $value = esc_attr( $this->get_option( $args['id'] ) );
		$name = sprintf( '%1$s[%2$s]', $this->setting_id, $args['id'] );
        $h .= sprintf("<div class='rtwpvg-image' id='%s'>", esc_attr( $args['id'] ) ); 
		$h .= sprintf("<div class='rtwpvg-form-group'><div class='rtwpvg-preview-imgs %s'>", esc_attr($args['id']) ); 

        if ( $value ) { 
            $img_url = '';
            $img_src = wp_get_attachment_url( $value );
            if ( $img_src ) { 
                $img_url = $img_src;
            }

            $h .= "<div class='rtwpvg-preview-img'><img src='".$img_url."' /><input type='hidden' name='".$name."' value='".$value."'><button class='rtwpvg-file-remove' data-id='".$value."'>x</button></div>"; 
        } else {
            $h .= "<div class='rtwpvg-preview-img'><input type='hidden' name='".$name."' value='0'></div>"; 
        }

        $h .= sprintf("</div>
                        <button data-name='%s' data-field='image' type='button' class='rtwpvg-upload-box'> 
                            <span>%s</span>
                        </button>
                    </div>", 
                    $name, 
                    esc_html__( 'Upload Image', 'woo-product-variation-gallery' ));
        $h .= "</div>";

		$h .= $this->get_field_description( $args );
        
        return $h;
	}  

	/**
	 * @param $option
	 * @param $givenDefault
	 *
	 * @return mixed|void
	 */
	public function get_option( $option, $givenDefault = null ) {
		$default = $this->get_default( $option );
		$options = get_option( $this->setting_id );
		$is_new  = ( ! is_array( $options ) && is_bool( $options ) );
		if ( $is_new ) {
			$value = isset($default['value']) ? $default['value'] : $givenDefault;
		} else {
			$value = isset( $options[ $option ] ) ? $options[ $option ] : '';
			if ( $givenDefault && ! $value ) {
				$value = $givenDefault;
			}
		}

		return apply_filters( 'rtwpvg_get_option', $value, $default, $option, $options, $is_new );
	}

}

