<?php
/**
 * Renderer class for custom slides and base class for dynamic slide renderers.
 *
 * @since  1.0.0
 */
class BQW_SPL_Slide_Renderer {

	/**
	 * Data of the slide.
	 *
	 * @since 1.0.0
	 * 
	 * @var array
	 */
	protected $data = null;

	/**
	 * ID of the slider to which the slide belongs.
	 *
	 * @since 1.0.0
	 * 
	 * @var int
	 */
	protected $slider_id = null;

	/**
	 * index of the slide.
	 *
	 * @since 1.0.0
	 * 
	 * @var int
	 */
	protected $slide_index = null;

	/**
	 * HTML markup of the slide.
	 *
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	protected $html_output = '';

	/**
	 * No implementation yet
	 * .
	 * @since 1.0.0
	 */
	public function __construct() {
		
	}

	/**
	 * Set the data of the slide.
	 *
	 * @since 1.0.0
	 * 
	 * @param array $data        The data of the slide.
	 * @param int   $slider_id   The id of the slider.
	 * @param int   $slide_index The index of the slide.
	 * @param bool  $extra_data  Extra settings data for the slider.
	 */
	public function set_data( $data, $slider_id, $slide_index ) {
		$this->data = $data;
		$this->slider_id = $slider_id;
		$this->slide_index = $slide_index;
	}

	/**
	 * Create the main image(s), link, inline HTML and layers, and return the HTML markup of the slide.
	 *
	 * @since  1.0.0
	 *
	  * @return string the HTML markup of the slide.
	 */
	public function render() {
		$classes = 'sp-slide';
		$classes = apply_filters( 'sliderpro_slide_classes' , $classes, $this->slider_id, $this->slide_index );

		$this->html_output = "\r\n" . '		<div class="' . $classes . '">';

		if ( $this->has_main_image() ) {
			$this->html_output .= "\r\n" . '			' . ( $this->has_main_image_link() ? $this->add_link_to_main_image( $this->create_main_image() ) : $this->create_main_image() );
		}

		if ( $this->has_caption() ) {
			$classes = "sp-caption";
			$classes = apply_filters( 'sliderpro_caption_classes', $classes, $this->slider_id, $this->slide_index );
			
			$this->html_output .= "\r\n" . '			<div class="' . $classes . '">' . $this->create_caption() . '</div>';
		}

		$this->html_output .= "\r\n" . '		</div>';

		return $this->html_output;
	}

	/**
	 * Check if the slide has a main image.
	 *
	 * @since  1.0.0
	 * 
	 * @return boolean
	 */
	protected function has_main_image() {
		if ( isset( $this->data['main_image_source'] ) && $this->data['main_image_source'] !== '' ) {
			return true;
		}

		return false;
	}

	/**
	 * Create the HTML markup for the main image.
	 *
	 * @since  1.0.0
	 * 
	 * @return string HTML markup
	 */
	protected function create_main_image() {
		$main_image_source = ' src="' . esc_attr( $this->data['main_image_source'] ) . '"';
		$main_image_alt = isset( $this->data['main_image_alt'] ) && $this->data['main_image_alt'] !== '' ? ' alt="' . esc_attr( $this->data['main_image_alt'] ) . '"' : '';
		$main_image_title = isset( $this->data['main_image_title'] ) && $this->data['main_image_title'] !== '' ? ' title="' . esc_attr( $this->data['main_image_title'] ) . '"' : '';	
		$main_image_width = '';
		$main_image_height = '';

		$classes = "sp-image";

		$main_image = '<img class="' . $classes . '"' . $main_image_source . $main_image_alt . $main_image_title . $main_image_width . $main_image_height . ' />';

		return $main_image;
	}

	/**
	 * Check if the slide has a link for the main image(s).
	 *
	 * @since  1.0.0
	 * 
	 * @return boolean
	 */
	protected function has_main_image_link() {
		if ( ( isset( $this->data['main_image_link'] ) && $this->data['main_image_link'] !== '' ) ) {
			return true;
		} 

		return false;
	}

	/**
	 * Create a link for the main image(s).
	 *
	 * If the lightbox is enabled and a link was not specified,
	 * add the main image URL as a link.
	 *
	 * @since 1.0.0
	 * 
	 * @param  string  $image The image markup.
	 * @return string         The link markup.
	 */
	protected function add_link_to_main_image( $image ) {
		$main_image_link_href = '';

		if ( isset( $this->data['main_image_link'] ) && $this->data['main_image_link'] !== '' ) {
			$main_image_link_href = $this->data['main_image_link'];
		} else if ( $this->lightbox === true ) {
			$main_image_link_href = $this->data['main_image_source'];
		}

		$main_image_link_href = apply_filters( 'sliderpro_slide_link_url', $main_image_link_href, $this->slider_id, $this->slide_index );

		$classes = "";
		$classes = apply_filters( 'sliderpro_slide_link_classes', $classes, $this->slider_id, $this->slide_index );

		$main_image_link_title = isset( $this->data['main_image_link_title'] ) && $this->data['main_image_link_title'] !== '' ? ' title="' . esc_attr( $this->data['main_image_link_title'] ) . '"' : '';
		$main_image_link = 
			'<a class="' . $classes . '" href="' . $main_image_link_href . '"' . $main_image_link_title . '">' . 
				"\r\n" . '				' . $image . 
			"\r\n" . '			' . '</a>';
		
		return $main_image_link;
	}

	/**
	 * Check if the slide has a caption.
	 *
	 * @since  1.0.0
	 * 
	 * @return boolean
	 */
	protected function has_caption() {
		if ( isset( $this->data['caption'] ) && $this->data['caption'] !== '' ) {
			return true;
		} 

		return false;
	}

	/**
	 * Create caption for the slide.
	 *
	 * @since 1.0.0
	 * 
	 * @return string The caption.
	 */
	protected function create_caption() {
		$caption = $this->data['caption'];
		$caption = do_shortcode( $caption );
		$caption = apply_filters( 'sliderpro_slide_caption', $caption, $this->slider_id, $this->slide_index );

		return $caption;
	}
}