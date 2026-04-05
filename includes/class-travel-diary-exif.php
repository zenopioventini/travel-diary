<?php
/**
 * Travel_Diary_Exif
 *
 * Estrae silenziosamente i metadati GPS (EXIF) dalle foto appena caricate
 * e li salva come post meta dell'allegato, in modo da non doverli
 * ricalcolare a runtime a ogni visita della pagina.
 *
 * Meta salvati sull'attachment:
 *   _td_exif_lat  -> float  latitudine decimale  (es.  45.4215)
 *   _td_exif_lng  -> float  longitudine decimale (es.  12.3168)
 *
 * La funzionalità può essere disattivata singolarmente su ogni Post
 * (Trip o Entry) tramite il meta:
 *   _td_exif_disabled -> '1'
 *
 * @package Travel_Diary
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Travel_Diary_Exif {

	const META_LAT      = '_td_exif_lat';
	const META_LNG      = '_td_exif_lng';
	const META_DISABLED = '_td_exif_disabled';

	// ─── Registrazione meta REST ──────────────────────────────────────────────

	public function register_exif_meta() {
		// Meta disabilitazione sull'Entry (ha il suo box in più, la Tappa)
		foreach ( array( Travel_Diary_Cpt_Trip::POST_TYPE, Travel_Diary_Cpt_Entry::POST_TYPE ) as $pt ) {
			register_post_meta( $pt, self::META_DISABLED, array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => '',
				'auth_callback'     => function() { return current_user_can( 'edit_posts' ); },
				'sanitize_callback' => 'sanitize_key',
			) );
		}
	}

	// ─── Hook principale: estrai EXIF al caricamento di un media ─────────────

	/**
	 * Viene chiamata da 'add_attachment' ogni volta che WordPress
	 * registra un nuovo allegato. Tentiamo l'estrazione GPS.
	 *
	 * @param int $attachment_id ID dell'allegato appena caricato.
	 */
	public function extract_on_upload( int $attachment_id ) {
		$this->process_attachment( $attachment_id );
	}

	/**
	 * Permette di (ri)estrarre manualmente i metadati di un allegato già
	 * esistente richiamando il processamento.
	 *
	 * @param int $attachment_id
	 */
	public function process_attachment( int $attachment_id ) {
		// Solo JPEG supporta EXIF nelle librerie PHP standard.
		$mime = get_post_mime_type( $attachment_id );
		if ( ! in_array( $mime, array( 'image/jpeg', 'image/jpg' ), true ) ) {
			return;
		}

		// Già calcolato? Saltiamo (evita doppio lavoro se richiamato più volte).
		if ( get_post_meta( $attachment_id, self::META_LAT, true ) !== '' ) {
			return;
		}

		$file = get_attached_file( $attachment_id );
		if ( ! $file || ! file_exists( $file ) ) {
			return;
		}

		// Verifica disponibilità dell'estensione PHP EXIF.
		if ( ! function_exists( 'exif_read_data' ) ) {
			return;
		}

		// Sopprimiamo gli errori: file corrotti, EXIF mancanti, ecc. 
		$exif = @exif_read_data( $file, 'GPS' );
		if ( ! $exif || empty( $exif['GPSLatitude'] ) || empty( $exif['GPSLongitude'] ) ) {
			return;
		}

		$lat = $this->gps_to_decimal(
			$exif['GPSLatitude'],
			$exif['GPSLatitudeRef'] ?? 'N'
		);
		$lng = $this->gps_to_decimal(
			$exif['GPSLongitude'],
			$exif['GPSLongitudeRef'] ?? 'E'
		);

		if ( null === $lat || null === $lng ) {
			return;
		}

		update_post_meta( $attachment_id, self::META_LAT, $lat );
		update_post_meta( $attachment_id, self::META_LNG, $lng );
	}

	// ─── Helper: Conversione GPS → Decimale ───────────────────────────────────

	/**
	 * Converte un valore GPS in formato EXIF (Gradi/Minuti/Secondi come array
	 * di frazioni es. ["51/1","30/1","3600/100"]) in un float decimale.
	 *
	 * @param array  $gps_array Array di 3 elementi [gradi, minuti, secondi].
	 * @param string $ref       'N', 'S', 'E' o 'W'.
	 * @return float|null
	 */
	private function gps_to_decimal( array $gps_array, string $ref ): ?float {
		if ( count( $gps_array ) !== 3 ) return null;

		$deg = $this->fraction_to_float( $gps_array[0] );
		$min = $this->fraction_to_float( $gps_array[1] );
		$sec = $this->fraction_to_float( $gps_array[2] );

		if ( null === $deg ) return null;

		$decimal = $deg + ( ( $min ?? 0 ) / 60 ) + ( ( $sec ?? 0 ) / 3600 );

		// Latitutdini Sud e Longitudini Ovest sono negative.
		if ( in_array( strtoupper( $ref ), array( 'S', 'W' ), true ) ) {
			$decimal *= -1;
		}

		return round( $decimal, 8 );
	}

	/**
	 * Converte una stringa frazione tipo "3600/100" in un float.
	 *
	 * @param mixed $fraction
	 * @return float|null
	 */
	private function fraction_to_float( $fraction ): ?float {
		if ( is_numeric( $fraction ) ) {
			return (float) $fraction;
		}

		if ( is_string( $fraction ) && strpos( $fraction, '/' ) !== false ) {
			list( $num, $den ) = explode( '/', $fraction, 2 );
			$den = (float) $den;
			if ( $den == 0 ) return null;
			return (float) $num / $den;
		}

		return null;
	}

	// ─── Helper pubblico per i template ──────────────────────────────────────

	/**
	 * Restituisce latitudine e longitudine di un allegato, se disponibili.
	 *
	 * @param int $attachment_id
	 * @return array|null  Array ['lat' => float, 'lng' => float] o null se assenti.
	 */
	public static function get_coords( int $attachment_id ): ?array {
		$lat = get_post_meta( $attachment_id, self::META_LAT, true );
		$lng = get_post_meta( $attachment_id, self::META_LNG, true );

		if ( $lat === '' || $lng === '' ) return null;

		return array(
			'lat' => (float) $lat,
			'lng' => (float) $lng,
		);
	}

	/**
	 * Verifica se un post ha disabilitato la geolocalizzazione EXIF.
	 *
	 * @param int $post_id
	 * @return bool
	 */
	public static function is_disabled( int $post_id ): bool {
		return (bool) get_post_meta( $post_id, self::META_DISABLED, true );
	}

	/**
	 * Raccoglie tutti i marker GPS validi e non duplicati dalle gallery di un Trip
	 * e di tutte le sue Entry. Rispetta il flag di opt-out sia sul Trip sia sulle singole Entry.
	 *
	 * @param int $trip_id  ID del post td_trip.
	 * @return array  Array di [ 'id' => int, 'lat' => float, 'lng' => float, 'thumb' => string ]
	 */
	public static function get_trip_map_markers( int $trip_id ): array {
		$markers   = array();
		$seen_ids  = array();  // Deduplicazione per attachment ID

		// Helper closure per aggiungere marker da lista ID galleria
		$add_from_gallery = function( array $ids ) use ( &$markers, &$seen_ids ) {
			foreach ( $ids as $attachment_id ) {
				if ( in_array( $attachment_id, $seen_ids, true ) ) continue;
				$seen_ids[] = $attachment_id;

				$coords = self::get_coords( $attachment_id );
				if ( ! $coords ) continue;

				$thumb = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );

				$markers[] = array(
					'id'    => $attachment_id,
					'lat'   => $coords['lat'],
					'lng'   => $coords['lng'],
					'thumb' => $thumb ?: '',
				);
			}
		};

		// Trip Gallery (se non disabilitato)
		if ( ! self::is_disabled( $trip_id ) ) {
			$trip_gallery = Travel_Diary_Gallery::get_gallery_ids( $trip_id );
			$add_from_gallery( $trip_gallery );
		}

		// Tutte le Entry collegate (via tassonomia td_trip_cat)
		$trip          = get_post( $trip_id );
		$trip_slug     = $trip ? $trip->post_name : '';
		if ( $trip_slug ) {
			$entries = get_posts( array(
				'post_type'  => Travel_Diary_Cpt_Entry::POST_TYPE,
				'tax_query'  => array( array(
					'taxonomy' => 'td_trip_cat',
					'field'    => 'slug',
					'terms'    => $trip_slug,
				) ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'post_status'    => 'publish',
			) );

			foreach ( $entries as $entry_id ) {
				if ( self::is_disabled( $entry_id ) ) continue;

				$entry_gallery = Travel_Diary_Gallery::get_gallery_ids( $entry_id );
				$add_from_gallery( $entry_gallery );
			}
		}

		return $markers;
	}
}
