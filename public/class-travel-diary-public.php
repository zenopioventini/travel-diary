<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/zeno-pioventini-a117399/
 * @since      1.0.0
 *
 * @package    Travel_Diary
 * @subpackage Travel_Diary/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Travel_Diary
 * @subpackage Travel_Diary/public
 * @author     Zeno Pioventini <zeno.pioventini@gmail.com>
 */
class Travel_Diary_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/travel-diary-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		$api_key = get_option('td_gmap_api_key');
		if (!empty($api_key)) {
			wp_enqueue_script( $this->plugin_name . '-maps-api', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&callback=initTravelDiaryMap', array(), $this->version, true );
		}
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/travel-diary-public.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * Create new category when is created a new trip attached to wp_insert_post hook
	 */
	public function new_category_trip($post_id, $post, $update){
		
		if(wp_is_post_revision($post_id))
			return;
		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']))
			return;
		
		if ($post->post_type == Travel_Diary_Cpt_Trip::POST_TYPE && $post->post_status == 'publish'){
			$exists_term = get_term_by('slug', $post->post_name, 'td_trip_cat', OBJECT);
			if(!$exists_term) {
				$term_name = ucfirst(str_replace("-", " ", $post->post_name));
				$result = wp_insert_term($term_name, 'td_trip_cat', array('slug' => $post->post_name));
				if (is_wp_error($result)) {
					error_log ("Trip category insert error: " . $result->get_error_message());
				} else {
					$term_id = $result['term_id'];
					wp_set_post_terms( $post_id, array($term_id), 'td_trip_cat', false );
				}
			} else {
				wp_set_post_terms( $post_id, array($exists_term->term_id), 'td_trip_cat', false );
			}
		}
	}
	
	/** pulizia per i dati del viaggio :
	 * se cancello un viaggio devo:
	 * - cancellare la categoria con lo stesso slug 
	 * - mettere in bozza le tappe collegate? 
	 * - ...**/
	public function trip_delete($post_id){
		global $post_type;
		if ($post_type == Travel_Diary_Cpt_Trip::POST_TYPE){
			$deleting_post = get_post($post_id);
			if ($deleting_post) {
				$orig_slug = str_replace("__trashed", "", $deleting_post->post_name);
				$trip_term = get_term_by( 'slug', $orig_slug, 'td_trip_cat', OBJECT );
				if ($trip_term) {
					error_log("eliminato il post $post_id con slug " . $deleting_post->post_name . ", elimino la categoria ". $orig_slug . " che ha lo stesso slug");
					wp_delete_term( $trip_term->term_id, 'td_trip_cat' );
				} else {
					error_log("eliminato il post $post_id con slug " . $deleting_post->post_name . ", nessuna categoria con slug " . $orig_slug . " trovata!");
				}
				$entries = get_field(Travel_Diary_Cpt_Trip::FIELD_PREFIX . 'entry_of_trip', $post_id);
				if ($entries && is_array($entries) && count($entries) > 0) {
					foreach ($entries as $entry_id) {
						$entry = get_post($entry_id);
						if ($entry) {
							$entry->post_status = 'draft';
							wp_update_post($entry);
						}
					}
				}
			}
		}
	}
	
	/**
	 * Sincronizza la tassonomia alle tappe (entries) associate tramite il campo ACF relazionale
	 */
	public function sync_trip_entries_taxonomies($post_id) {
		if(wp_is_post_revision($post_id)) return;
		
		if (get_post_type($post_id) == Travel_Diary_Cpt_Trip::POST_TYPE) {
			$trip = get_post($post_id);
			if ($trip->post_status == 'publish') {
				// get the assigned term for this trip
				$trip_term = get_term_by('slug', $trip->post_name, 'td_trip_cat', OBJECT);
				if ($trip_term) {
					$entries = get_field(Travel_Diary_Cpt_Trip::FIELD_PREFIX . 'entry_of_trip', $post_id);
					if ($entries && is_array($entries)) {
						foreach ($entries as $entry_id) {
							wp_set_post_terms( $entry_id, array($trip_term->term_id), 'td_trip_cat', false );
						}
					}
				}
			}
		}
	}
	
	/**
	 * Append Custom Templates to Single Trip and Single Entry
	 */
	public function append_travel_diary_templates( $content ) {
		return $content;
	}

}
