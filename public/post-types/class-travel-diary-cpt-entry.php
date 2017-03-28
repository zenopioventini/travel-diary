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
if(!class_exists('Travel_Diary_Cpt_Entry'))
{
		class Travel_Diary_Cpt_Entry {
			
			const POST_TYPE = "td_entry";
			const FIELD_PREFIX = "field_entry_";
			
			public function __construct(){
				
			} // END public function __construct()
			
			public function create_post_type(){
				
					/**
					 * Post Type: Entries.
					 */
				
					$labels = array(
							"name" => __( 'Entries', 'twentyseventeen' ),
							"singular_name" => __( 'Entry', 'twentyseventeen' ),
							"menu_name" => __( 'Entry of Trip', 'twentyseventeen' ),
							"all_items" => __( 'All Entries', 'twentyseventeen' ),
							"add_new" => __( 'Add New', 'twentyseventeen' ),
							"add_new_item" => __( 'Add New Entry', 'twentyseventeen' ),
							"edit_item" => __( 'Edit Entry', 'twentyseventeen' ),
							"new_item" => __( 'New Entry', 'twentyseventeen' ),
							"view_item" => __( 'View Entry', 'twentyseventeen' ),
							"view_items" => __( 'View Entries', 'twentyseventeen' ),
							"search_items" => __( 'Search Entry', 'twentyseventeen' ),
							"not_found" => __( 'No Entries Found', 'twentyseventeen' ),
							"not_found_in_trash" => __( 'No Entries Found in Trash', 'twentyseventeen' ),
							"featured_image" => __( 'Featured Image for this entry', 'twentyseventeen' ),
							"set_featured_image" => __( 'Set featured image for this entry', 'twentyseventeen' ),
							"remove_featured_image" => __( 'Remove featured image for this entry', 'twentyseventeen' ),
							"use_featured_image" => __( 'Use as featured image for this entry', 'twentyseventeen' ),
							"archives" => __( 'Entry achives', 'twentyseventeen' ),
							"insert_into_item" => __( 'Insert into entry', 'twentyseventeen' ),
							"uploaded_to_this_item" => __( 'Uploaded to this entry', 'twentyseventeen' ),
							"filter_items_list" => __( 'Filter entry list', 'twentyseventeen' ),
							"items_list_navigation" => __( 'Entry list navigation', 'twentyseventeen' ),
							"items_list" => __( 'Entry list', 'twentyseventeen' ),
							"attributes" => __( 'Entries Attributes', 'twentyseventeen' ),
					);
				
					$args = array(
							"label" => __( 'Entries', 'twentyseventeen' ),
							"labels" => $labels,
							"description" => "This is the single leg of the trip",
							"public" => true,
							"publicly_queryable" => true,
							"show_ui" => true,
							"show_in_rest" => false,
							"rest_base" => "",
							"has_archive" => false,
							"show_in_menu" => true,
							"exclude_from_search" => false,
							"capability_type" => "post",
							"map_meta_cap" => true,
							"hierarchical" => false,
							"rewrite" => array( "slug" => "entry", "with_front" => true ),
							"query_var" => true,
							"supports" => array( "title", "editor", "thumbnail", "comments", "revisions" ),
					);
				
					register_post_type( self::POST_TYPE, $args );
				
					
					if(function_exists("register_field_group"))
					{
						register_field_group(array (
								'id' => 'acf_campi-della-tappa',
								'title' => 'Campi della tappa',
								'fields' => array (
										array (
												'key' => self::FIELD_PREFIX . 'arrivo',
												'label' => 'Arrivo',
												'name' => self::FIELD_PREFIX . 'arrivo',
												'type' => 'date_picker',
												'instructions' => 'Data di arrivo',
												'date_format' => 'yymmdd',
												'display_format' => 'dd/mm/yy',
												'first_day' => 1,
										),
										array (
												'key' => self::FIELD_PREFIX . 'partenza',
												'label' => 'Partenza',
												'name' => self::FIELD_PREFIX . 'partenza',
												'type' => 'date_picker',
												'instructions' => 'Data della partenza',
												'date_format' => 'yymmdd',
												'display_format' => 'dd/mm/yy',
												'first_day' => 1,
										),
										array (
												'key' => self::FIELD_PREFIX . 'posizione',
												'label' => 'Posizione',
												'name' => self::FIELD_PREFIX . 'posizione',
												'type' => 'google_map',
												'instructions' => 'Posizione della tappa',
												'center_lat' => '42.972502',
												'center_lng' => '12.304688',
												'zoom' => 14,
												'height' => '',
										),
								),
								'location' => array (
										array (
												array (
														'param' => 'post_type',
														'operator' => '==',
														'value' => 'td_entry',
														'order_no' => 0,
														'group_no' => 0,
												),
										),
								),
								'options' => array (
										'position' => 'normal',
										'layout' => 'default',
										'hide_on_screen' => array (
										),
								),
								'menu_order' => 0,
						));
					}
					
						
			}
		}
}
