<?php
/**
 * Renders the slider.
 * 
 * @since 1.0.0
 */
class BQW_SPL_Slider_Renderer {

	/**
	 * Data of the slider.
	 *
	 * @since 1.0.0
	 * 
	 * @var array
	 */
	protected $data = null;

	/**
	 * ID of the slider.
	 *
	 * @since 1.0.0
	 * 
	 * @var int
	 */
	protected $id = null;

	/**
	 * Settings of the slider.
	 *
	 * @since 1.0.0
	 * 
	 * @var array
	 */
	protected $settings = null;

	/**
	 * Default slider settings data.
	 *
	 * @since 1.0.0
	 * 
	 * @var array
	 */
	protected $default_settings = null;

	/**
	 * HTML markup of the slider.
	 *
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	protected $html_output = '';

	/**
	 * List of id's for the CSS files that need to be loaded for the slider.
	 *
	 * @since 1.0.0
	 * 
	 * @var array
	 */
	protected $css_dependencies = array();

	/**
	 * List of id's for the JS files that need to be loaded for the slider.
	 *
	 * @since 1.0.0
	 * 
	 * @var array
	 */
	protected $js_dependencies = array();

	/**
	 * Initialize the slider renderer by retrieving the id and settings from the passed data.
	 * 
	 * @since 1.0.0
	 *
	 * @param array $data The data of the slider.
	 */
	public function __construct( $data ) {
		$this->data = $data;
		$this->id = $this->data['id'];
		$this->settings = $this->data['settings'];
		$this->default_settings = BQW_SliderPro_Lite_Settings::getSettings();
	}

	/**
	 * Return the slider's HTML markup.
	 *
	 * @since 1.0.0
	 * 
	 * @return string The HTML markup of the slider.
	 */
	public function render() {
		$classes = 'slider-pro sp-no-js';

		$width = isset( $this->settings['width'] ) ? $this->settings['width'] : $this->default_settings['width']['default_value'];
		$height = isset( $this->settings['height'] ) ? $this->settings['height'] : $this->default_settings['height']['default_value'];

		if ( is_numeric( $width ) ) {
			$width .= 'px';
		}

		if ( is_numeric( $height ) ) {
			$height .= 'px';
		}

		$this->html_output .= "\r\n" . '<div id="slider-pro-' . $this->id . '" class="' . $classes . '" style="width: ' . $width . '; height: ' . $height . ';">';

		if ( $this->has_slides() ) {
			$this->html_output .= "\r\n" . '	<div class="sp-slides">';
			$this->html_output .= "\r\n" . '		' . $this->create_slides();
			$this->html_output .= "\r\n" . '	</div>';
		}

		$this->html_output .= "\r\n" . '</div>';
		
		$this->html_output = apply_filters( 'sliderpro_markup', $this->html_output, $this->id );

		return $this->html_output;
	}

	/**
	 * Check if the slider has slides.
	 *
	 * @since  1.0.0
	 * 
	 * @return boolean Whether or not the slider has slides.
	 */
	protected function has_slides() {
		if ( isset( $this->data['slides'] ) && ! empty( $this->data['slides'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Create the slider's slides and get their HTML markup.
	 *
	 * @since  1.0.0
	 * 
	 * @return string The HTML markup of the slides.
	 */
	protected function create_slides() {
		$slides_output = '';
		$slides = $this->data['slides'];
		$slide_counter = 0;

		foreach ( $slides as $slide ) {
			$slides_output .= $this->create_slide( $slide, $slide_counter );
			$slide_counter++;
		}

		return $slides_output;
	}

	/**
	 * Create a slide.
	 * 
	 * @since 1.0.0
	 *
	 * @param  array  $data          The data of the slide.
	 * @param  int    $slide_counter The index of the slide.
	 * @return string                The HTML markup of the slide.
	 */
	protected function create_slide( $data, $slide_counter ) {
		$slide = BQW_SPL_Slide_Renderer_Factory::create_slide( $data );
		$slide->set_data( $data, $this->id, $slide_counter );
		
		return $slide->render();
	}

	/**
	 * Return the inline JavaScript code of the slider and identify all CSS and JS
	 * files that need to be loaded for the current slider.
	 *
	 * @since 1.0.0
	 * 
	 * @return string The inline JavaScript code of the slider.
	 */
	public function render_js() {
		$js_output = '';
		$settings_js = '';

		foreach ( $this->default_settings as $name => $setting ) {
			if ( ! isset( $setting['js_name'] ) ) {
				continue;
			}

			$setting_default_value = $setting['default_value'];
			$setting_value = isset( $this->settings[ $name ] ) ? $this->settings[ $name ] : $setting_default_value;

			if ( $setting_value != $setting_default_value ) {
				if ( $settings_js !== '' ) {
					$settings_js .= ',';
				}

				if ( is_bool( $setting_value ) ) {
					$setting_value = $setting_value === true ? 'true' : 'false';
				} else if ( is_numeric( $setting_value ) === false ) {
					$setting_value = "'" . $setting_value . "'";
				}

				$settings_js .= "\r\n" . '			' . $setting['js_name'] . ': ' . $setting_value;
			}
		}

		$this->add_js_dependency( 'plugin' );

		$js_output .= "\r\n" . '		$( "#slider-pro-' . $this->id . '" ).sliderPro({' .
											$settings_js .
						"\r\n" . '		});' . "\r\n";

		return $js_output;
	}

	/**
	 * Add the id of a CSS file that needs to be loaded for the current slider.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $id The id of the file.
	 */
	protected function add_css_dependency( $id ) {
		$this->css_dependencies[] = $id;
	}

	/**
	 * Add the id of a JS file that needs to be loaded for the current slider.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $id The id of the file.
	 */
	protected function add_js_dependency( $id ) {
		$this->js_dependencies[] = $id;
	}

	/**
	 * Return the list of id's for CSS files that need to be loaded for the current slider.
	 *
	 * @since 1.0.0
	 * 
	 * @return array The list of id's for CSS files.
	 */
	public function get_css_dependencies() {
		return $this->css_dependencies;
	}

	/**
	 * Return the list of id's for JS files that need to be loaded for the current slider.
	 *
	 * @since 1.0.0
	 * 
	 * @return array The list of id's for JS files.
	 */
	public function get_js_dependencies() {
		return $this->js_dependencies;
	}
}