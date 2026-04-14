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
							"show_in_rest" => true,
							"rest_base" => "",
							"has_archive" => false,
							"show_in_menu" => true,
							"exclude_from_search" => false,
							"capability_type" => "post",
							"map_meta_cap" => true,
							"hierarchical" => false,
							"rewrite" => array( "slug" => "tappa", "with_front" => true ),
							"query_var" => true,
							"supports" => array( "title", "editor", "excerpt", "thumbnail", "comments", "revisions" ),
					);
				
					register_post_type( self::POST_TYPE, $args );
				
					
					if(function_exists("acf_add_local_field_group"))
					{
						acf_add_local_field_group(array (
								'id' => 'acf_campi-della-tappa',
								'title' => 'Campi della tappa',
								'fields' => array (
										array (
												'key' => self::FIELD_PREFIX . 'data_principale',
												'label' => 'Data e Ora Principale',
												'name' => self::FIELD_PREFIX . 'data_principale',
												'type' => 'date_time_picker',
												'instructions' => 'Se questa è l\'unica tappa del giorno, metti un orario indicativo (es: 10:00). Se è un riassunto di 3 giorni, metti il primo giorno (es: 12 Agosto)',
												'display_format' => 'd/m/Y H:i',
												'return_format'  => 'Y-m-d H:i:s',
												'first_day'      => 1,
										),
										array (
												'key' => self::FIELD_PREFIX . 'data_fine',
												'label' => 'Data Fine (Opzionale)',
												'name' => self::FIELD_PREFIX . 'data_fine',
												'type' => 'date_picker',
												'instructions' => 'Compila SOLO se la tappa riassume più giorni consecutivi nello stesso luogo (Caso "Soggiorno")',
												'date_format'    => 'yymmdd',
												'display_format' => 'dd/mm/yy',
												'return_format'  => 'Ymd',
												'first_day'      => 1,
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
										array (
												'key' => self::FIELD_PREFIX . 'km_reali',
												'label' => 'Km percorsi (reali)',
												'name' => self::FIELD_PREFIX . 'km_reali',
												'type' => 'number',
												'instructions' => 'Inserisci i km reali percorsi per arrivare a questa tappa. Se lasciato vuoto, verrà usata la stima automatica in linea d\'aria.',
												'placeholder' => 'es. 247',
												'min' => 0,
												'step' => 1,
										),
										array (
												'key' => self::FIELD_PREFIX . 'mezzo_trasporto',
												'label' => 'Mezzo di trasporto',
												'name' => self::FIELD_PREFIX . 'mezzo_trasporto',
												'type' => 'select',
												'instructions' => 'Come hai raggiunto questa tappa?',
												'choices' => array(
													'auto'       => 'Auto',
													'moto'       => 'Moto',
													'treno'      => 'Treno',
													'aereo'      => 'Aereo',
													'nave'       => 'Nave / Traghetto',
													'autobus'    => 'Autobus',
													'bicicletta' => 'Bicicletta',
													'piedi'      => 'A piedi',
													'altro'      => 'Altro',
												),
												'allow_null' => 1,
												'return_format' => 'value',
										),
										array (
												'key' => self::FIELD_PREFIX . 'meteo',
												'label' => 'Meteo',
												'name' => self::FIELD_PREFIX . 'meteo',
												'type' => 'select',
												'instructions' => 'Com\'era il tempo durante questa tappa?',
												'choices' => array(
													'sole'      => 'Sole',
													'nuvoloso'  => 'Parzialmente nuvoloso',
													'coperto'   => 'Coperto',
													'pioggia'   => 'Pioggia',
													'temporale' => 'Temporale',
													'neve'      => 'Neve',
													'vento'     => 'Vento forte',
													'nebbia'    => 'Nebbia',
												),
												'allow_null' => 1,
												'return_format' => 'value',
										),
										array (
												'key' => self::FIELD_PREFIX . 'valutazione',
												'label' => 'Valutazione',
												'name' => self::FIELD_PREFIX . 'valutazione',
												'type' => 'select',
												'instructions' => 'Quanto ti è piaciuta questa tappa?',
												'choices' => array(
													'1' => '1 Stella',
													'2' => '2 Stelle',
													'3' => '3 Stelle',
													'4' => '4 Stelle',
													'5' => '5 Stelle',
												),
												'allow_null' => 1,
												'return_format' => 'value',
										),
										array (
												'key' => self::FIELD_PREFIX . 'costi',
												'label' => 'Spese della tappa',
												'name' => self::FIELD_PREFIX . 'costi',
												'type' => 'repeater',
												'instructions' => 'Aggiungi le spese sostenute durante questa tappa.',
												'layout' => 'table',
												'button_label' => '+ Aggiungi spesa',
												'sub_fields' => array(
													array(
														'key'   => self::FIELD_PREFIX . 'costo_categoria',
														'label' => 'Categoria',
														'name'  => 'categoria',
														'type'  => 'select',
														'choices' => array(
															'trasporto'  => 'Trasporto',
															'alloggio'   => 'Alloggio',
															'cibo'       => 'Cibo & Ristorazione',
															'esperienze' => 'Esperienze',
															'shopping'   => 'Shopping',
															'varie'      => 'Varie',
														),
														'return_format' => 'value',
													),
													array(
														'key'         => self::FIELD_PREFIX . 'costo_importo',
														'label'       => 'Importo',
														'name'        => 'importo',
														'type'        => 'number',
														'placeholder' => '0.00',
														'min'         => 0,
														'step'        => 0.01,
													),
													array(
														'key'         => self::FIELD_PREFIX . 'costo_nota',
														'label'       => 'Dettaglio / Causa',
														'name'        => 'nota',
														'type'        => 'text',
														'placeholder' => 'es. Ristorante La Pergola',
													),
													array(
														'key'         => self::FIELD_PREFIX . 'costo_valuta',
														'label'       => 'Valuta',
														'name'        => 'valuta',
														'type'        => 'select',
														'choices'     => array(
															'EUR' => 'EUR',
															'USD' => 'USD',
															'GBP' => 'GBP',
															'CHF' => 'CHF',
															'Altra' => 'Altra',
														),
														'default_value' => 'EUR',
													),
												),
										),
										array (
												'key' => self::FIELD_PREFIX . 'poi_list',
												'label' => 'Punti di Interesse (POI)',
												'name' => self::FIELD_PREFIX . 'poi_list',
												'type' => 'repeater',
												'instructions' => 'Segnala luoghi notevoli visitati in questa tappa.',
												'layout' => 'block',
												'button_label' => '+ Aggiungi POI',
												'sub_fields' => array(
													array(
														'key'   => self::FIELD_PREFIX . 'poi_titolo',
														'label' => 'Nome Luogo',
														'name'  => 'titolo',
														'type'  => 'text',
														'required' => 1,
													),
													array(
														'key'   => self::FIELD_PREFIX . 'poi_categoria',
														'label' => 'Categoria',
														'name'  => 'categoria',
														'type'  => 'select',
														'choices' => array(
															'cultura'   => '🏛️ Cultura & Musei',
															'cibo'      => '🍽️ Cibo & Ristorazione',
															'natura'    => '🌲 Natura & Parchi',
															'viewpoint' => '📸 Punto Panoramico',
															'relax'     => '💆 Relax',
															'alloggio'  => '🏨 Alloggio',
														),
													),
													array(
														'key'   => self::FIELD_PREFIX . 'poi_valutazione',
														'label' => 'Valutazione',
														'name'  => 'valutazione',
														'type'  => 'select',
														'choices' => array(
															'must'        => '🔥 Imperdibile',
															'recommended' => '👍 Consigliato',
															'if_time'     => '⏱️ Se hai tempo',
														),
														'allow_null' => 1,
													),
													array(
														'key'           => self::FIELD_PREFIX . 'poi_immagine',
														'label'         => 'Immagine',
														'name'          => 'immagine',
														'type'          => 'image',
														'return_format' => 'id',
														'preview_size'  => 'thumbnail',
													),
													array(
														'key'         => self::FIELD_PREFIX . 'poi_info',
														'label'       => 'Info / Orari',
														'name'        => 'info',
														'type'        => 'text',
														'placeholder' => 'es. 10:00 - 18:00 (Lunedì chiuso)',
													),
													array(
														'key'   => self::FIELD_PREFIX . 'poi_descrizione',
														'label' => 'Descrizione',
														'name'  => 'descrizione',
														'type'  => 'textarea',
														'rows'  => 3,
													),
													array(
														'key'          => self::FIELD_PREFIX . 'poi_posizione',
														'label'        => 'Posizione sulla mappa',
														'name'         => 'posizione',
														'type'         => 'google_map',
														'instructions' => 'Cerca il nome del luogo per posizionarlo sulla mappa del viaggio.',
														'center_lat'   => '42.972502',
														'center_lng'   => '12.304688',
														'zoom'         => 14,
														'height'       => '',
													),
												),
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
