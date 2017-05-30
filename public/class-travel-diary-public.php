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
			$trip_category_slug = 'trip';
			// TODO verificare se è selezionata la categoria padre dei viaggi
			// $trip_category_slug = get_term('trip_parent');
			// Verificare se esite
			$trip_category = get_term_by('slug', $trip_category_slug, 'category', OBJECT);
			// se non è selezionata o non esiste creare un default e impostarla
			if(!$trip_category) {
				error_log ("Parent trip category not found, I'll do it!");
				$trip_category_id = wp_create_category('Trip');
			} else {
				$trip_category_id = (int)$trip_category->term_id;
			}
			// utilizzare la categoria padre dei viaggi e creare una nuova categoria per il 
			// viaggio con lo slug del post (potrebbe infatti esistere un viaggio con lo stesso nome).
			// non dovrebbe mai esistere ma nel caso uso l'esistente... 

			$exists_category = get_term_by('slug', $post->post_name, 'category', OBJECT);
			$result = false;
			if(!$exists_category) {
				$cat_args = array('cat_name' => ucfirst(str_replace("-", " ", $post->post_name)),
						'category_description' => '',
						'category_nicename' => $post->post_name,
						'category_parent' => $trip_category_id,
						'taxonomy' => 'category' );
				$result = wp_insert_category($cat_args, true);
				if (is_wp_error($result)) {
					error_log ("Trip category insert error!");
					error_log ($result->get_error_message());
				}
			} else {
				$result = (int) $exists_category->term_id;
			}
			if($result){
				// aggancio al post la nuova tassonomia creata
				wp_set_post_categories( $post_id, $result, false );
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
			//TODO ignorare il codice per le revisioni
			if ($deleting_post) {
				$orig_slug = str_replace("__trashed", "", $deleting_post->post_name);
				//TODO no non va bene devo cercare tra le categorie associate al post
				$trip_category = get_term_by( 'slug', $orig_slug, 'category', OBJECT );
				if ($trip_category) {
					error_log("eliminato il post $post_id con slug " . $deleting_post->post_name . ", elimino la categoria ". $orig_slug . " che ha lo stesso slug");
					wp_delete_term( $trip_category->term_id, 'category' );
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
	//add_action( 'before_delete_post', 'my_func' );

}
