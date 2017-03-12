<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.linkedin.com/in/zeno-pioventini-a117399/
 * @since      1.0.0
 *
 * @package    Travel_Diary
 * @subpackage Travel_Diary/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Travel_Diary
 * @subpackage Travel_Diary/includes
 * @author     Zeno Pioventini <zeno.pioventini@gmail.com>
 */
class Travel_Diary_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'travel-diary',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
