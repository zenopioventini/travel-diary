<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.linkedin.com/in/zeno-pioventini-a117399/
 * @since      1.0.0
 *
 * @package    Travel_Diary
 * @subpackage Travel_Diary/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Travel_Diary
 * @subpackage Travel_Diary/includes
 * @author     Zeno Pioventini <zeno.pioventini@gmail.com>
 */
class Travel_Diary_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Registra i CPT prima del flush, altrimenti le loro regole non vengono incluse
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/post-types/class-travel-diary-cpt-trip.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/post-types/class-travel-diary-cpt-entry.php';

		$cpt_trip  = new Travel_Diary_Cpt_Trip();
		$cpt_entry = new Travel_Diary_Cpt_Entry();
		$cpt_trip->create_post_type();
		$cpt_entry->create_post_type();

		// Flush hard: riscrive anche il .htaccess
		flush_rewrite_rules( true );
	}

}
