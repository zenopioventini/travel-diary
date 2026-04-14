<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linkedin.com/in/zeno-pioventini-a117399/
 * @since      1.0.0
 *
 * @package    Travel_Diary
 * @subpackage Travel_Diary/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Travel_Diary
 * @subpackage Travel_Diary/includes
 * @author     Zeno Pioventini <zeno.pioventini@gmail.com>
 */
class Travel_Diary {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Travel_Diary_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'travel-diary';
		$this->version = '1.0.0';

		$this->load_plugin_files("../public/post-types");
//		$this->load_plugin_files("../public/taxonomies");
//		$this->load_plugin_files("../public/widgets");
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}
	
	/**
	 * Load all files with extension in a directory
	 */
	private function load_plugin_files( $directory, $extension = 'php' ){
		$plugin_directory = sprintf("%s/%s/", dirname(__FILE__), $directory );
		if (file_exists($plugin_directory)){
			if ($handle = opendir( $plugin_directory )) {
				$files = array();
				while (false !== ($file = readdir($handle))){
					if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == $extension){
						$files[] = sprintf("%s/%s", $plugin_directory, $file);
					}
				}
				closedir($handle);
				sort($files);
				foreach ($files as $file) {
					require_once($file);
				}
			}
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Travel_Diary_Loader. Orchestrates the hooks of the plugin.
	 * - Travel_Diary_i18n. Defines internationalization functionality.
	 * - Travel_Diary_Admin. Defines all hooks for the admin area.
	 * - Travel_Diary_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-travel-diary-loader.php';

		/**
		 * The class responsible for handling SVG icons.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-travel-diary-icons.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-travel-diary-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-travel-diary-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-travel-diary-public.php';

		/**
		 * Sistema di Privacy Viaggi.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-travel-diary-privacy.php';

		/**
		 * Sistema di Galleria Fotografica.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-travel-diary-gallery.php';

		/**
		 * Sistema di Video in Evidenza.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-travel-diary-video.php';

		/**
		 * Sistema di Geolocalizzazione EXIF.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-travel-diary-exif.php';

		$this->loader = new Travel_Diary_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Travel_Diary_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Travel_Diary_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Travel_Diary_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_filter( 'acf/fields/relationship/query/name='.Travel_Diary_Cpt_Trip::FIELD_PREFIX .'entry_of_trip', $plugin_admin, 'entry_filter_query', 10, 3 );
		
		// Settings Menu & Option Registration
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
		$this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
		
		// Custom Columns & Filters for the "Tappe" (Entries) lists
		$this->loader->add_filter('manage_td_entry_posts_columns', $plugin_admin, 'set_custom_td_entry_columns');
		$this->loader->add_action('manage_td_entry_posts_custom_column', $plugin_admin, 'custom_td_entry_column', 10, 2);
		
		$this->loader->add_action('restrict_manage_posts', $plugin_admin, 'filter_td_entry_by_trip');
		$this->loader->add_action('pre_get_posts', $plugin_admin, 'filter_td_entry_query');

		// Inietta la API Key in ACF Backend
		$this->loader->add_filter('acf/fields/google_map/api', $plugin_admin, 'setup_acf_google_map_api');

		// Sistema Privacy: Meta Box in Admin
		$plugin_privacy = new Travel_Diary_Privacy();
		$this->loader->add_action( 'init',             $plugin_privacy, 'register_privacy_meta' );
		$this->loader->add_action( 'add_meta_boxes',   $plugin_privacy, 'add_privacy_meta_box' );
		$this->loader->add_action( 'admin_head',       $plugin_privacy, 'hide_reorder_buttons' );
		$this->loader->add_action( 'save_post',        $plugin_privacy, 'save_privacy_meta' );
		$this->loader->add_action( 'rest_after_insert_' . Travel_Diary_Cpt_Trip::POST_TYPE, $plugin_privacy, 'rest_after_insert_trip' );
		$this->loader->add_action( 'wp_ajax_td_regenerate_token',  $plugin_privacy, 'ajax_regenerate_token' );
		$this->loader->add_action( 'wp_ajax_td_save_token_expiry', $plugin_privacy, 'ajax_save_token_expiry' );

		// Sistema Galleria Foto e Media Library
		$plugin_gallery = new Travel_Diary_Gallery();
		$this->loader->add_action( 'init', $plugin_gallery, 'register_gallery_meta' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_gallery, 'add_gallery_meta_boxes' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_gallery, 'enqueue_admin_scripts' );
		$this->loader->add_action( 'save_post', $plugin_gallery, 'save_gallery_meta' );
		$this->loader->add_filter( 'ajax_query_attachments_args', $plugin_gallery, 'filter_media_library' );

		// Sistema Video in Evidenza
		$plugin_video = new Travel_Diary_Video();
		$this->loader->add_action( 'init', $plugin_video, 'register_video_meta' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_video, 'add_video_meta_boxes' );
		$this->loader->add_action( 'save_post', $plugin_video, 'save_video_meta' );

		// Sistema Geolocalizzazione EXIF
		$plugin_exif = new Travel_Diary_Exif();
		$this->loader->add_action( 'init',           $plugin_exif, 'register_exif_meta' );
		$this->loader->add_action( 'add_attachment', $plugin_exif, 'extract_on_upload' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Travel_Diary_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		/**
		 * Definiamo il post type delle Tappe.
		 * **/
		$post_types_ex = new Travel_Diary_Cpt_Entry();
		$this->loader->add_action( 'init', $post_types_ex, 'create_post_type' );
		
		/**
		 * Definiamo il post type del viaggio.
		 * **/
		$post_types_ex = new Travel_Diary_Cpt_Trip();
		$this->loader->add_action( 'init', $post_types_ex, 'create_post_type' );		

		/**
		 * Definisco hook per la creazione delle categorie per i nuovi viaggi
		 */
		$this->loader->add_action( 'wp_insert_post', $plugin_public, 'new_category_trip', 10, 3);
		$this->loader->add_action( 'before_delete_post', $plugin_public, 'trip_delete' );
		
		/**
		 * Sincronizziamo la Categoria assegnata al Viaggio sulle Tappe associate dopo che i custom fields sono salvati.
		 */
		$this->loader->add_action( 'acf/save_post', $plugin_public, 'sync_trip_entries_taxonomies', 20 );

		/**
		 * Gestione Layout Frontend (Frontend Templates)
		 */
		$this->loader->add_filter( 'the_content', $plugin_public, 'append_travel_diary_templates', 20 );

		// Sistema Privacy: controllo accesso frontend
		$plugin_privacy = new Travel_Diary_Privacy();
		$this->loader->add_action( 'template_redirect', $plugin_privacy, 'check_access' );

		// Localizzazione url autore (author -> viaggiatore)
		$this->loader->add_action( 'init', $plugin_public, 'change_author_base' );

		// Filtro Frontend Queries (Archivi e Autore)
		$this->loader->add_action( 'pre_get_posts', $plugin_public, 'filter_public_archives' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Travel_Diary_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
