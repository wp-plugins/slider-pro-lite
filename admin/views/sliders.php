<div class="wrap sliderpro-admin">
	<h2><?php _e( 'All Sliders' ); ?></h2>

	<table class="widefat sliders-list">
	<thead>
	<tr>
		<th><?php _e( 'ID', 'sliderpro-lite' ); ?></th>
		<th><?php _e( 'Name', 'sliderpro-lite' ); ?></th>
		<th><?php _e( 'Shortcode', 'sliderpro-lite' ); ?></th>
		<th><?php _e( 'Created', 'sliderpro-lite' ); ?></th>
		<th><?php _e( 'Modified', 'sliderpro-lite' ); ?></th>
		<th><?php _e( 'Actions', 'sliderpro-lite' ); ?></th>
	</tr>
	</thead>
	
	<tbody>
		
	<?php
		global $wpdb;
		$prefix = $wpdb->prefix;

		$sliders = $wpdb->get_results( "SELECT * FROM " . $prefix . "slider_pro_sliders ORDER BY id" );
		
		if ( count( $sliders ) === 0 ) {
			echo '<tr class="no-slider-row">' .
					 '<td colspan="100%">' . __( 'You don\'t have saved sliders.', 'sliderpro-lite' ) . '</td>' .
				 '</tr>';
		} else {
			foreach ( $sliders as $slider ) {
				$slider_id = $slider->id;
				$slider_name = stripslashes( $slider->name );
				$slider_created = $slider->created;
				$slider_modified = $slider->modified;

				include( 'sliders-row.php' );
			}
		}
	?>

	</tbody>
	
	<tfoot>
	<tr>
		<th><?php _e( 'ID', 'sliderpro-lite' ); ?></th>
		<th><?php _e( 'Name', 'sliderpro-lite' ); ?></th>
		<th><?php _e( 'Shortcode', 'sliderpro-lite' ); ?></th>
		<th><?php _e( 'Created', 'sliderpro-lite' ); ?></th>
		<th><?php _e( 'Modified', 'sliderpro-lite' ); ?></th>
		<th><?php _e( 'Actions', 'sliderpro-lite' ); ?></th>
	</tr>
	</tfoot>
	</table>
    
    <div class="new-slider-buttons">    
		<a class="button-secondary" href="<?php echo admin_url( 'admin.php?page=sliderpro-lite-new' ); ?>"><?php _e( 'Create New Slider', 'sliderpro-lite' ); ?></a>
    </div>    
    
</div>