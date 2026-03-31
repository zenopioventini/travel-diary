<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.linkedin.com/in/zeno-pioventini-a117399/
 * @since      1.0.0
 *
 * @package    Travel_Diary
 * @subpackage Travel_Diary/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h2>Travel Diary Settings</h2>
	<form action="options.php" method="post">
		<?php
		settings_fields( 'travel_diary_options_group' );
		do_settings_sections( $this->plugin_name );
		submit_button();
		?>
	</form>
</div>
