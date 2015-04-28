<?php
/**
 * Factory for slide renderers.
 *
 * Implements the appropriate functionality for each slide, depending on the slide's type.
 *
 * @since  1.0.0
 */
class BQW_SPL_Slide_Renderer_Factory {

	/**
	 * Return an instance of the renderer class based on the type of the slide.
	 *
	 * @since 1.0.0
	 * 
	 * @param  array  $data The data of the slide.
	 * @return object       An instance of the appropriate renderer class.
	 */
	public static function create_slide( $data ) {
		return new BQW_SPL_Slide_Renderer();
	}
}