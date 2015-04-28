<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	global $wpdb;			
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	
	if ( $blog_ids !== false ) {
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			bqw_sliderpro_lite_delete_all_data();
		}

		restore_current_blog();
	}
} else {
	bqw_sliderpro_lite_delete_all_data();
}

function bqw_sliderpro_lite_delete_all_data() {
	if ( ! class_exists( 'BQW_SliderPro' ) ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$sliders_table = $prefix . 'slider_pro_sliders';
		$slides_table = $prefix . 'slider_pro_slides';
		$layers_table = $prefix . 'slider_pro_layers';

		$wpdb->query( "DROP TABLE $sliders_table, $slides_table, $layers_table" );

		delete_option( 'sliderpro_load_stylesheets' );
		delete_option( 'sliderpro_access' );
		delete_option( 'sliderpro_lite_version' );
		
		$wpdb->query( "DELETE FROM " . $prefix . "options WHERE option_name LIKE '%sliderpro_cache%'" );
	}
}