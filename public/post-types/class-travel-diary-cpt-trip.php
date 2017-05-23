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
if(!class_exists('Travel_Diary_Cpt_Trip'))
{
		class Travel_Diary_Cpt_Trip {
			
			const POST_TYPE = "td_trip";
			const FIELD_PREFIX = "field_trip_";
			
			public function __construct(){
				
			} // END public function __construct()
			
			public function create_post_type(){
				
					/**
					 * Post Type: Entries.
					 */
				
					$labels = array(
							"name" => __( 'Trip', 'twentyseventeen' ),
							"singular_name" => __( 'Trip', 'twentyseventeen' ),
							"menu_name" => __( 'Trip', 'twentyseventeen' ),
							"all_items" => __( 'All Trips', 'twentyseventeen' ),
							"add_new" => __( 'Add New', 'twentyseventeen' ),
							"add_new_item" => __( 'Add New Trip', 'twentyseventeen' ),
							"edit_item" => __( 'Edit Trip', 'twentyseventeen' ),
							"new_item" => __( 'New Trip', 'twentyseventeen' ),
							"view_item" => __( 'View Trip', 'twentyseventeen' ),
							"view_items" => __( 'View Trips', 'twentyseventeen' ),
							"search_items" => __( 'Search Trip', 'twentyseventeen' ),
							"not_found" => __( 'No Trips Found', 'twentyseventeen' ),
							"not_found_in_trash" => __( 'No Trips Found in Trash', 'twentyseventeen' ),
							"featured_image" => __( 'Featured Image for this trip', 'twentyseventeen' ),
							"set_featured_image" => __( 'Set featured image for this trip', 'twentyseventeen' ),
							"remove_featured_image" => __( 'Remove featured image for this trip', 'twentyseventeen' ),
							"use_featured_image" => __( 'Use as featured image for this trip', 'twentyseventeen' ),
							"archives" => __( 'Trip achives', 'twentyseventeen' ),
							"insert_into_item" => __( 'Insert into trip', 'twentyseventeen' ),
							"uploaded_to_this_item" => __( 'Uploaded to this trip', 'twentyseventeen' ),
							"filter_items_list" => __( 'Filter trip list', 'twentyseventeen' ),
							"items_list_navigation" => __( 'Trip list navigation', 'twentyseventeen' ),
							"items_list" => __( 'Trip list', 'twentyseventeen' ),
							"attributes" => __( 'Trips Attributes', 'twentyseventeen' ),
					);
				
					$args = array(
							"label" => __( 'Trip', 'twentyseventeen' ),
							"labels" => $labels,
							"description" => "This is the trip",
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
							"rewrite" => array( "slug" => "trip", "with_front" => true ),
							"query_var" => true,
							"supports" => array( "title", "editor", "thumbnail", "comments", "revisions",  ),
							'taxonomies' => array('category', "post_tag"),
					);
				
					register_post_type( self::POST_TYPE, $args );
					
				if(function_exists("register_field_group"))
				{
					register_field_group(array (
						'id' => 'acf_entries-of-trip',
						'title' => 'Entries of Trip',
						'fields' => array (
							array (
								'key' => self::FIELD_PREFIX . 'entry_of_trip',
								'label' => 'Entry of trip',
								'name' => self::FIELD_PREFIX . 'entry_of_trip',
								'type' => 'relationship',
								'return_format' => 'id',
								'post_type' => array (
									0 => 'td_entry',
								),
								'taxonomy' => array (
									0 => 'all',
								),
								'filters' => array (
									0 => 'search',
								),
								'result_elements' => array (
									0 => 'post_type',
									1 => 'post_title',
								),
								'max' => '',
							),
						),
						'location' => array (
							array (
								array (
									'param' => 'post_type',
									'operator' => '==',
									'value' => 'td_trip',
									'order_no' => 0,
									'group_no' => 0,
								),
							),
						),
						'options' => array (
							'position' => 'normal',
							'layout' => 'no_box',
							'hide_on_screen' => array (
							),
						),
						'menu_order' => 0,
					));
				}
					
			}
		}
}
