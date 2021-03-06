<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.linkedin.com/in/zeno-pioventini-a117399/
 * @since             1.0.0
 * @package           Travel_Diary
 *
 * @wordpress-plugin
 * Plugin Name:       TravelDiary
 * Plugin URI:        https://github.com/zenopioventini/travel-diary
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Zeno Pioventini
 * Author URI:        https://www.linkedin.com/in/zeno-pioventini-a117399/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       travel-diary
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-travel-diary-activator.php
 */
function activate_travel_diary() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-travel-diary-activator.php';
	Travel_Diary_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-travel-diary-deactivator.php
 */
function deactivate_travel_diary() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-travel-diary-deactivator.php';
	Travel_Diary_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_travel_diary' );
register_deactivation_hook( __FILE__, 'deactivate_travel_diary' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-travel-diary.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_travel_diary() {

	$plugin = new Travel_Diary();
	$plugin->run();

}
run_travel_diary();
