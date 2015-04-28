<?php

/*
	Plugin Name: Slider Pro Lite
	Plugin URI:  http://bqworks.net/slider-pro/
	Description: Elegant and professional sliders. The lite version.
	Version:     1.0.0
	Author:      bqworks
	Author URI:  http://bqworks.com
*/

// if the file is called directly, abort
if ( ! defined( 'WPINC' ) ) {
	die();
}

require_once( plugin_dir_path( __FILE__ ) . 'public/class-sliderpro.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/class-slider-renderer.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/class-slide-renderer.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/class-slide-renderer-factory.php' );

require_once( plugin_dir_path( __FILE__ ) . 'includes/class-sliderpro-activation.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-sliderpro-widget.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-sliderpro-settings.php' );

register_activation_hook( __FILE__, array( 'BQW_SliderPro_Lite_Activation', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'BQW_SliderPro_Lite_Activation', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'BQW_SliderPro_Lite', 'get_instance' ) );
add_action( 'plugins_loaded', array( 'BQW_SliderPro_Lite_Activation', 'get_instance' ) );

// register the widget
add_action( 'widgets_init', 'bqw_spl_register_widget' );

if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-sliderpro-admin.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-sliderpro-updates.php' );
	add_action( 'plugins_loaded', array( 'BQW_SliderPro_Lite_Admin', 'get_instance' ) );
	add_action( 'admin_init', array( 'BQW_SliderPro_Lite_Updates', 'get_instance' ) );
}