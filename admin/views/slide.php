<div class="slide">
	<span class="spinner slide-spinner"></span>
	
	<div class="slide-preview">
		<?php 
			if ( $slide_image !== '' ) {
				echo '<img src="' . esc_url( $slide_image ) . '" />';
			} else {
				echo '<p class="no-image">' . __( 'Click to add image', 'sliderpro-lite' ) . '</p>';
			}
		?>
	</div>

	<div class="slide-controls">
		<a class="delete-slide" href="#" title="Delete Slide">Delete</a>
		<a class="duplicate-slide" href="#" title="Duplicate Slide">Duplicate</a>
	</div>
</div>
