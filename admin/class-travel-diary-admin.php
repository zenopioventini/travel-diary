<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/zeno-pioventini-a117399/
 * @since      1.0.0
 *
 * @package    Travel_Diary
 * @subpackage Travel_Diary/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Travel_Diary
 * @subpackage Travel_Diary/admin
 * @author     Zeno Pioventini <zeno.pioventini@gmail.com>
 */
class Travel_Diary_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Travel_Diary_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Travel_Diary_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/travel-diary-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Travel_Diary_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Travel_Diary_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/travel-diary-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	public function entry_filter_query ($args, $field, $post_id) {
		//error_log(print_r($args, true));
		$author = get_current_user_id();
		$args['author'] = $author;
		return $args;
	}
	
	/**
	 * Settings Menu
	 */
	public function add_plugin_admin_menu() {
		add_submenu_page(
			'options-general.php', 
			'Travel Diary Settings', 
			'Travel Diary', 
			'manage_options', 
			$this->plugin_name, 
			array($this, 'display_plugin_setup_page')
		);
	}

	public function display_plugin_setup_page() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/travel-diary-admin-display.php';
	}

	public function register_settings() {
		register_setting( 'travel_diary_options_group', 'td_gmap_api_key' );
		register_setting( 'travel_diary_options_group', 'td_root_category' );

		add_settings_section(
			'td_setting_section',
			'Global Settings',
			array( $this, 'td_settings_section_callback' ),
			$this->plugin_name
		);

		add_settings_field(
			'td_gmap_api_key',
			'Google Maps API Key',
			array( $this, 'td_gmap_api_key_render' ),
			$this->plugin_name,
			'td_setting_section'
		);

		add_settings_field(
			'td_root_category',
			'Root Category (Slug)',
			array( $this, 'td_root_cat_render' ),
			$this->plugin_name,
			'td_setting_section'
		);
	}

	public function td_settings_section_callback() {
		echo __( 'Configure the global settings for the Travel Diary plugin.', 'twentyseventeen' );
	}

	public function td_gmap_api_key_render() {
		$options = get_option( 'td_gmap_api_key' );
		echo "<input type='text' name='td_gmap_api_key' value='".esc_attr($options)."' size='50'>";
		echo "<p class='description'>Questa chiave API è fondamentale per abilitare e mostrare la minimappa interattiva all'interno delle Tappe. Puoi reperirla dalla <a href='https://console.cloud.google.com/' target='_blank'>Google Cloud Console</a> abilitando la Maps JavaScript API.</p>";
	}

	public function td_root_cat_render() {
		$options = get_option( 'td_root_category' );
		echo "<input type='text' name='td_root_category' value='".esc_attr($options)."' size='50'>";
		echo "<p class='description'>Inserisci lo slug (testo minuscolo senzaspazi, es. 'diari' o 'itinerari') della pagina principale sotto cui desideri raggruppare visivamente i viaggi nel blocco Frontend (per costruire URL ordinati).</p>";
	}

	/**
	 * Custom Admin Columns for Entries
	 */
	public function set_custom_td_entry_columns($columns) {
		$columns['td_trip_cat'] = __( 'Trip Category', 'twentyseventeen' );
		return $columns;
	}

	public function custom_td_entry_column($column, $post_id) {
		if ($column == 'td_trip_cat') {
			$terms = get_the_term_list($post_id, 'td_trip_cat', '', ', ', '');
			if (!is_wp_error($terms) && !empty($terms)) {
				echo $terms;
			} else {
				echo '<em style="color:red;">Orphan</em>';
			}
		}
	}

	/**
	 * Dropdown Filter for Entries
	 */
	public function filter_td_entry_by_trip() {
		global $typenow;
		if ($typenow == 'td_entry') {
			$selected = isset($_GET['td_trip_cat_filter']) ? $_GET['td_trip_cat_filter'] : '';
			$info_taxonomy = get_taxonomy('td_trip_cat');
			
			wp_dropdown_categories(array(
				'show_option_all' => __("Show All {$info_taxonomy->label}"),
				'taxonomy' => 'td_trip_cat',
				'name' => 'td_trip_cat_filter',
				'orderby' => 'name',
				'selected' => $selected,
				'show_count' => true,
				'hide_empty' => false,
			));
		}
	}

	public function filter_td_entry_query($query) {
		global $pagenow;
		$type = 'post';
		if (isset($_GET['post_type'])) {
			$type = $_GET['post_type'];
		}
		if ('td_entry' == $type && is_admin() && $pagenow == 'edit.php' && isset($_GET['td_trip_cat_filter']) && $_GET['td_trip_cat_filter'] != '0') {
			$term = get_term_by('id', $_GET['td_trip_cat_filter'], 'td_trip_cat');
			if ($term) {
				$query->query_vars['tax_query'] = array(
					array(
						'taxonomy' => 'td_trip_cat',
						'field'    => 'id',
						'terms'    => $_GET['td_trip_cat_filter']
					)
				);
			}
		}
	}
	
}
