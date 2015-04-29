<?php
/**
 * Slider Pro admin class.
 * 
 * @since 1.0.0
 */
class BQW_SliderPro_Lite_Admin {

	/**
	 * Current class instance.
	 * 
	 * @since 1.0.0
	 * 
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Stores the hook suffixes for the plugin's admin pages.
	 * 
	 * @since 1.0.0
	 * 
	 * @var array
	 */
	protected $plugin_screen_hook_suffixes = null;

	/**
	 * Current class instance of the public Slider Pro class.
	 * 
	 * @since 1.0.0
	 * 
	 * @var object
	 */
	protected $plugin = null;

	/**
	 * Plugin class.
	 * 
	 * @since 1.0.0
	 * 
	 * @var object
	 */
	protected $plugin_slug = null;

	/**
	 * Initialize the admin by registering the required actions.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->plugin = BQW_SliderPro_Lite::get_instance();
		$this->plugin_slug = $this->plugin->get_plugin_slug();

		// load the admin CSS and JavaScript
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		add_action( 'wp_ajax_sliderpro_lite_get_slider_data', array( $this, 'ajax_get_slider_data' ) );
		add_action( 'wp_ajax_sliderpro_lite_save_slider', array( $this, 'ajax_save_slider' ) );
		add_action( 'wp_ajax_sliderpro_lite_preview_slider', array( $this, 'ajax_preview_slider' ) );
		add_action( 'wp_ajax_sliderpro_lite_delete_slider', array( $this, 'ajax_delete_slider' ) );
		add_action( 'wp_ajax_sliderpro_lite_duplicate_slider', array( $this, 'ajax_duplicate_slider' ) );
		add_action( 'wp_ajax_sliderpro_lite_add_slides', array( $this, 'ajax_add_slides' ) );
		add_action( 'wp_ajax_sliderpro_lite_clear_all_cache', array( $this, 'ajax_clear_all_cache' ) );
	}

	/**
	 * Return the current class instance.
	 *
	 * @since 1.0.0
	 * 
	 * @return object The instance of the current class.
	 */
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Loads the admin CSS files.
	 *
	 * It loads the public and admin CSS, and also the public custom CSS.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_styles() {
		if ( ! isset( $this->plugin_screen_hook_suffixes ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( in_array( $screen->id, $this->plugin_screen_hook_suffixes ) ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-style', plugins_url( 'slider-pro-lite/admin/assets/css/sliderpro-admin.min.css' ), array(), BQW_SliderPro_Lite::VERSION );
			wp_enqueue_style( $this->plugin_slug . '-plugin-style', plugins_url( 'slider-pro-lite/public/assets/css/slider-pro.min.css' ), array(), BQW_SliderPro_Lite::VERSION );
		}
	}

	/**
	 * Loads the admin JS files.
	 *
	 * It loads the public and admin JS, and also the public custom JS.
	 * Also, it passes the PHP variables to the admin JS file.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {
		if ( ! isset( $this->plugin_screen_hook_suffixes ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( in_array( $screen->id, $this->plugin_screen_hook_suffixes ) ) {
			if ( function_exists( 'wp_enqueue_media' ) ) {
		    	wp_enqueue_media();
			}

			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'slider-pro-lite/admin/assets/js/sliderpro-admin.min.js' ), array( 'jquery' ), BQW_SliderPro_Lite::VERSION );
			wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'slider-pro-lite/public/assets/js/jquery.sliderPro.min.js' ), array( 'jquery' ), BQW_SliderPro_Lite::VERSION );

			$id = isset( $_GET['id'] ) ? $_GET['id'] : -1;

			wp_localize_script( $this->plugin_slug . '-admin-script', 'sp_js_vars', array(
				'admin' => admin_url( 'admin.php' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'plugin' => plugins_url( 'sliderpro-lite' ),
				'page' => isset( $_GET['page'] ) && ( $_GET['page'] === 'sliderpro-lite-new' || ( isset( $_GET['id'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) ) ? 'single' : 'all',
				'id' => $id,
				'lad_nonce' => wp_create_nonce( 'load-slider-data' . $id ),
				'sa_nonce' => wp_create_nonce( 'save-slider' . $id ),
				'no_image' => __( 'Click to add image', 'sliderpro-lite' ),
				'slider_delete' => __( 'Are you sure you want to delete this slider?', 'sliderpro-lite' ),
				'slide_delete' => __( 'Are you sure you want to delete this slide?', 'sliderpro-lite' ),
				'yes' => __( 'Yes', 'sliderpro-lite' ),
				'cancel' => __( 'Cancel', 'sliderpro-lite' ),
				'save' => __( 'Save', 'sliderpro-lite' ),
				'slider_update' => __( 'Slider updated.', 'sliderpro-lite' ),
				'slider_create' => __( 'Slider created.', 'sliderpro-lite' )
			) );
		}
	}

	/**
	 * Create the plugin menu.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {
		$plugin_settings = BQW_SliderPro_Lite_Settings::getPluginSettings();
		$access = get_option( 'sliderpro_access', $plugin_settings['access']['default_value'] );

		add_menu_page(
			'Slider Pro',
			'Slider Pro',
			$access,
			$this->plugin_slug,
			array( $this, 'render_slider_page' ),
			plugins_url( '/slider-pro-lite/admin/assets/css/images/sp-icon.png' )
		);
		
		$this->plugin_screen_hook_suffixes[] = add_submenu_page(
			$this->plugin_slug,
			__( 'Slider Pro', $this->plugin_slug ),
			__( 'All Sliders', $this->plugin_slug ),
			$access,
			$this->plugin_slug,
			array( $this, 'render_slider_page' )
		);
	
		$this->plugin_screen_hook_suffixes[] = add_submenu_page(
			$this->plugin_slug,
			__( 'Add New Slider', $this->plugin_slug ),
			__( 'Add New', $this->plugin_slug ),
			$access,
			$this->plugin_slug . '-new',
			array( $this, 'render_new_slider_page' )
		);

		$this->plugin_screen_hook_suffixes[] = add_submenu_page(
			$this->plugin_slug,
			__( 'Plugin Settings', $this->plugin_slug ),
			__( 'Plugin Settings', $this->plugin_slug ),
			$access,
			$this->plugin_slug . '-settings',
			array( $this, 'render_plugin_settings_page' )
		);

		$this->plugin_screen_hook_suffixes[] = add_submenu_page(
			$this->plugin_slug,
			__( 'Upgrade', $this->plugin_slug ),
			__( 'Upgrade', $this->plugin_slug ),
			$access,
			$this->plugin_slug . '-ugrade',
			array( $this, 'render_upgrade_page' )
		);
	}

	/**
	 * Renders the slider page.
	 *
	 * Based on the 'action' parameter, it will render
	 * either an individual slider page or the list
	 * of all the sliders.
	 *
	 * If an individual slider page is rendered, delete
	 * the transients that store the post names and posts data,
	 * in order to trigger a new fetching of them.
	 * 
	 * @since 1.0.0
	 */
	public function render_slider_page() {
		if ( isset( $_GET['id'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
			$slider = $this->plugin->get_slider( $_GET['id'] );

			if ( $slider !== false ) {
				$slider_id = $slider['id'];
				$slider_name = $slider['name'];
				$slider_settings = $slider['settings'];
				$slider_panels_state = $slider['panels_state'];

				$slides = isset( $slider['slides'] ) ? $slider['slides'] : false;

				delete_transient( 'sliderpro_post_names' );
				delete_transient( 'sliderpro_posts_data' );

				include_once( 'views/slider.php' );
			} else {
				include_once( 'views/sliders.php' );
			}
		} else {
			include_once( 'views/sliders.php' );
		}
	}

	/**
	 * Renders the page for a new slider.
	 *
	 * Also, delete the transients that store
	 * the post names and posts data,
	 * in order to trigger a new fetching of them.
	 * 
	 * @since 1.0.0
	 */
	public function render_new_slider_page() {
		$slider_name = 'My Slider';

		delete_transient( 'sliderpro_post_names' );
		delete_transient( 'sliderpro_posts_data' );

		include_once( 'views/slider.php' );
	}

	/**
	 * Renders the plugin settings page.
	 *
	 * It also checks if new data was posted, and saves
	 * it in the options table.
	 *
	 * It verifies the purchase code supplied and displays
	 * if it's valid.
	 * 
	 * @since 1.0.0
	 */
	public function render_plugin_settings_page() {
		$plugin_settings = BQW_SliderPro_Lite_Settings::getPluginSettings();
		$load_stylesheets = get_option( 'sliderpro_load_stylesheets', $plugin_settings['load_stylesheets']['default_value'] );
		$cache_expiry_interval = get_option( 'sliderpro_cache_expiry_interval', $plugin_settings['cache_expiry_interval']['default_value'] );
		$access = get_option( 'sliderpro_access', $plugin_settings['access']['default_value'] );

		if ( isset( $_POST['plugin_settings_update'] ) ) {
			check_admin_referer( 'plugin-settings-update', 'plugin-settings-nonce' );

			if ( isset( $_POST['load_stylesheets'] ) ) {
				$load_stylesheets = $_POST['load_stylesheets'];
				update_option( 'sliderpro_load_stylesheets', $load_stylesheets );
			}

			if ( isset( $_POST['cache_expiry_interval'] ) ) {
				$cache_expiry_interval = $_POST['cache_expiry_interval'];
				update_option( 'sliderpro_cache_expiry_interval', $cache_expiry_interval );
			}

			if ( isset( $_POST['access'] ) ) {
				$access = $_POST['access'];
				update_option( 'sliderpro_access', $access );
			}
		}
		
		include_once( 'views/plugin-settings.php' );
	}

	/**
	 * Renders the 'Upgrade' page.
	 * 
	 * @since 4.1.1
	 */
	public function render_upgrade_page() {
		include_once( 'views/upgrade.php' );
	}

	/**
	 * AJAX call for getting the slider's data.
	 *
	 * @since 1.0.0
	 * 
	 * @return string The slider data, as JSON-encoded array.
	 */
	public function ajax_get_slider_data() {
		$nonce = $_GET['nonce'];
		$id = $_GET['id'];

		if ( ! wp_verify_nonce( $nonce, 'load-slider-data' . $id ) ) {
			die( 'This action was stopped for security purposes.' );
		}

		$slider = $this->get_slider_data( $_GET['id'] );

		echo json_encode( $slider );

		die();
	}

	/**
	 * Return the slider's data.
	 *
	 * @since 1.0.0
	 * 
	 * @param  int   $id The id of the slider.
	 * @return array     The slider data.
	 */
	public function get_slider_data( $id ) {
		return $this->plugin->get_slider( $id );
	}

	/**
	 * AJAX call for saving the slider.
	 *
	 * It can be called when the slider is created, updated
	 * or when a slider is imported. If the slider is 
	 * imported, it returns a row in the list of sliders.
	 *
	 * @since 1.0.0
	 */
	public function ajax_save_slider() {
		$slider_data = json_decode( stripslashes( $_POST['data'] ), true );
		$nonce = $slider_data['nonce'];
		$id = intval( $slider_data['id'] );
		$action = $slider_data['action'];

		if ( ! wp_verify_nonce( $nonce, 'save-slider' . $id ) ) {
			die( 'This action was stopped for security purposes.' );
		}

		$slider_id = $this->save_slider( $slider_data );

		if ( $action === 'save' ) {
			echo $slider_id;
		} else if ( $action === 'import' ) {
			$slider_name = $slider_data['name'];
			$slider_created = date( 'm-d-Y' );
			$slider_modified = date( 'm-d-Y' );

			include( 'views/sliders-row.php' );
		}

		die();
	}

	/**
	 * Save the slider.
	 *
	 * It either creates a new slider or updates and existing one.
	 *
	 * For existing sliders, the slides and layers are deleted and 
	 * re-inserted in the database.
	 *
	 * The cached slider is deleted every time the slider is saved.
	 *
	 * @since 1.0.0
	 * 
	 * @param  array $slider_data The data of the slider that's saved.
	 * @return int                The id of the saved slider.
	 */
	public function save_slider( $slider_data ) {
		global $wpdb;

		$id = intval( $slider_data['id'] );
		$slides_data = $slider_data['slides'];

		if ( $id === -1 ) {
			$wpdb->insert($wpdb->prefix . 'slider_pro_sliders', array( 'name' => $slider_data['name'],
																		'settings' => json_encode( $slider_data['settings'] ),
																		'created' => date( 'm-d-Y' ),
																		'modified' => date( 'm-d-Y' ),
																		'panels_state' => json_encode( $slider_data['panels_state'] ) ), 
																		array( '%s', '%s', '%s', '%s', '%s' ) );
			
			$id = $wpdb->insert_id;
		} else {
			$wpdb->update( $wpdb->prefix . 'slider_pro_sliders', array( 'name' => $slider_data['name'], 
																	 	'settings' => json_encode( $slider_data['settings'] ),
																	 	'modified' => date( 'm-d-Y' ),
																		'panels_state' => json_encode( $slider_data['panels_state'] ) ), 
																	   	array( 'id' => $id ), 
																	   	array( '%s', '%s', '%s', '%s' ), 
																	   	array( '%d' ) );
				
			$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "slider_pro_slides WHERE slider_id = %d", $id ) );

			$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "slider_pro_slides WHERE slider_id = %d", $id ) );
		}

		foreach ( $slides_data as $slide_data ) {
			$slide = array('slider_id' => $id,
							'label' => isset( $slide_data['label'] ) ? $slide_data['label'] : '',
							'position' => isset( $slide_data['position'] ) ? $slide_data['position'] : '',
							'visibility' => isset( $slide_data['visibility'] ) ? $slide_data['visibility'] : '',
							'main_image_id' => isset( $slide_data['main_image_id'] ) ? $slide_data['main_image_id'] : '',
							'main_image_source' => isset( $slide_data['main_image_source'] ) ? $slide_data['main_image_source'] : '',
							'main_image_alt' => isset( $slide_data['main_image_alt'] ) ? $slide_data['main_image_alt'] : '',
							'main_image_title' => isset( $slide_data['main_image_title'] ) ? $slide_data['main_image_title'] : '',
							'main_image_width' => isset( $slide_data['main_image_width'] ) ? $slide_data['main_image_width'] : '',
							'main_image_height' => isset( $slide_data['main_image_height'] ) ? $slide_data['main_image_height'] : '',
							'main_image_link' => isset( $slide_data['main_image_link'] ) ? $slide_data['main_image_link'] : '',
							'main_image_link_title' => isset( $slide_data['main_image_link_title'] ) ? $slide_data['main_image_link_title'] : '',
							'caption' => isset( $slide_data['caption'] ) ? $slide_data['caption'] : '',
							'settings' => isset( $slide_data['settings'] ) ? json_encode( $slide_data['settings'] ) : '');

			$wpdb->insert( $wpdb->prefix . 'slider_pro_slides', $slide, array( '%d', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) );
		}
		
		delete_transient( 'sliderpro_cache_' . $id );

		return $id;
	}

	/**
	 * AJAX call for previewing the slider.
	 *
	 * Receives the current data from the database (in the sliders page)
	 * or from the current settings (in the slider page) and prints the
	 * HTML markup and the inline JavaScript for the slider.
	 *
	 * @since 1.0.0
	 */
	public function ajax_preview_slider() {
		$slider = json_decode( stripslashes( $_POST['data'] ), true );
		$slider_name = $slider['name'];
		$slider_output = $this->plugin->output_slider( $slider, false ) . $this->plugin->get_inline_scripts();

		echo $slider_output;

		die();	
	}

	/**
	 * AJAX call for duplicating a slider.
	 *
	 * Loads a slider from the database and re-saves it with an id of -1, 
	 * which will determine the save function to add a new slider in the 
	 * database.
	 *
	 * It returns a new slider row in the list of all sliders.
	 *
	 * @since 1.0.0
	 */
	public function ajax_duplicate_slider() {
		$nonce = $_POST['nonce'];
		$original_slider_id = $_POST['id'];

		if ( ! wp_verify_nonce( $nonce, 'duplicate-slider' . $original_slider_id ) ) {
			die( 'This action was stopped for security purposes.' );
		}

		if ( ( $original_slider = $this->plugin->get_slider( $original_slider_id ) ) !== false ) {
			$original_slider['id'] = -1;
			$slider_id = $this->save_slider( $original_slider );
			$slider_name = $original_slider['name'];
			$slider_created = date( 'm-d-Y' );
			$slider_modified = date( 'm-d-Y' );

			include( 'views/sliders-row.php' );
		}

		die();
	}

	/**
	 * AJAX call for deleting a slider.
	 *
	 * It's called from the list of sliders, when the
	 * 'Delete' link is clicked.
	 *
	 * It calls the 'delete_slider()' method and passes
	 * it the id of the slider to be deleted.
	 *
	 * @since 1.0.0
	 */
	public function ajax_delete_slider() {
		$nonce = $_POST['nonce'];
		$id = intval( $_POST['id'] );

		if ( ! wp_verify_nonce( $nonce, 'delete-slider' . $id ) ) {
			die( 'This action was stopped for security purposes.' );
		}

		echo $this->delete_slider( $id ); 

		die();
	}

	/**
	 * Delete the slider indicated by the id.
	 *
	 * @since 1.0.0
	 * 
	 * @param  int $id The id of the slider to be deleted.
	 * @return int     The id of the slider that was deleted.
	 */
	public function delete_slider( $id ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "slider_pro_slides WHERE slider_id = %d", $id ) );

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "slider_pro_sliders WHERE id = %d", $id ) );

		return $id;
	}

	/**
	 * Create a slide from the passed data.
	 *
	 * Receives some data, like the main image, or
	 * the slide's content type. A new slide is created by 
	 * passing 'false' instead of any data.
	 *
	 * @since 1.0.0
	 * 
	 * @param  array|bool $data The data of the slide or false, if the slide is new.
	 */
	public function create_slide( $data ) {
		$slide_image = '';

		if ( $data !== false ) {
			$slide_image = isset( $data['main_image_source'] ) ? $data['main_image_source'] : $slide_image;
		}

		include( 'views/slide.php' );
	}
	
	/**
	 * AJAX call for adding multiple or a single slide.
	 *
	 * If it receives any data, it tries to create multiple
	 * slides by padding the data that was received, and if
	 * it doesn't receive any data it tries to create a
	 * single slide.
	 *
	 * @since 1.0.0
	 */
	public function ajax_add_slides() {
		if ( isset( $_POST['data'] ) ) {
			$slides_data = json_decode( stripslashes( $_POST['data'] ), true );

			foreach ( $slides_data as $slide_data ) {
				$this->create_slide( $slide_data );
			}
		} else {
			$this->create_slide( false );
		}

		die();
	}

	/**
	 * AJAX call for displaying the Caption editor.
	 *
	 * @since 1.0.0
	 */
	public function ajax_load_caption_editor() {
		$slide_default_settings = BQW_SliderPro_Lite_Settings::getSlideSettings();

		$caption_content = $_POST['data'];

		include( 'views/caption-editor.php' );

		die();
	}

	/**
	 * AJAX call for deleting the cached sliders
	 * stored using transients.
	 *
	 * It's called from the Plugin Settings page.
	 *
	 * @since 1.0.0
	 */
	public function ajax_clear_all_cache() {
		$nonce = $_POST['nonce'];

		if ( ! wp_verify_nonce( $nonce, 'clear-all-cache' ) ) {
			die( 'This action was stopped for security purposes.' );
		}

		global $wpdb;

		$wpdb->query( "DELETE FROM " . $wpdb->prefix . "options WHERE option_name LIKE '%sliderpro_cache%'" );

		echo true;

		die();
	}
}