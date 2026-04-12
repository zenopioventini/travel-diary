<?php
/**
 * Travel_Diary_Privacy
 *
 * Gestisce il sistema di visibilita'/privacy dei Viaggi e delle Tappe.
 *
 * Livelli di visibilita':
 *   public  -> visibile a tutti (default)
 *   token   -> solo chi ha il link con token valido
 *   members -> solo utenti loggati
 *   friends -> solo utenti nella lista amici (da implementare)
 *   private -> solo l'autore del viaggio
 *
 * @package Travel_Diary
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Travel_Diary_Privacy {

	const META_VISIBILITY = '_td_visibility';
	const META_TOKEN      = '_td_share_token';
	const META_EXPIRY     = '_td_token_expiry';

	// --- Registrazione meta REST-accessible ----------------------------------

	/**
	 * Espone i meta alla REST API di WordPress.
	 * Cosi' Gutenberg puo' salvare la visibilita' con il bottone Aggiorna.
	 */
	public function register_privacy_meta() {
		register_post_meta( Travel_Diary_Cpt_Trip::POST_TYPE, self::META_VISIBILITY, array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'string',
			'default'           => 'public',
			'auth_callback'     => function() { return current_user_can( 'edit_posts' ); },
			'sanitize_callback' => 'sanitize_key',
		) );
		register_post_meta( Travel_Diary_Cpt_Trip::POST_TYPE, self::META_EXPIRY, array(
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'integer',
			'default'       => 0,
			'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
		) );
	}

	// --- Frontend: verifica accesso ------------------------------------------

	/**
	 * Verifica accesso a Trip e Entry prima di renderizzare la pagina.
	 * Chiamato su template_redirect.
	 */
	public function check_access() {
		if ( ! is_singular( array( Travel_Diary_Cpt_Trip::POST_TYPE, Travel_Diary_Cpt_Entry::POST_TYPE ) ) ) {
			return;
		}

		$post_id = get_queried_object_id();

		// Se e' una Entry, ottieni il trip padre
		if ( get_post_type( $post_id ) === Travel_Diary_Cpt_Entry::POST_TYPE ) {
			$post_id = $this->get_parent_trip_id( $post_id );
			if ( ! $post_id ) return;
		}

		$visibility = get_post_meta( $post_id, self::META_VISIBILITY, true ) ?: 'public';

		switch ( $visibility ) {
			case 'public':
				return;

			case 'token':
				$this->check_token_access( $post_id );
				break;

			case 'members':
				if ( ! is_user_logged_in() ) {
					$this->deny_access( 'members', $post_id );
				}
				break;

			case 'private':
				$author_id = (int) get_post_field( 'post_author', $post_id );
				if ( get_current_user_id() !== $author_id && ! current_user_can( 'manage_options' ) ) {
					$this->deny_access( 'private', $post_id );
				}
				break;

			case 'friends':
				// Placeholder — futura implementazione
				if ( ! is_user_logged_in() ) {
					$this->deny_access( 'members', $post_id );
				}
				break;
		}
	}

	// --- Token ---------------------------------------------------------------

	/**
	 * Verifica che il token nell'URL sia valido e non scaduto.
	 */
	private function check_token_access( int $trip_id ) {
		$stored_token = get_post_meta( $trip_id, self::META_TOKEN, true );

		// Token non ancora generato: autore e admin passano
		if ( empty( $stored_token ) ) return;

		$url_token = sanitize_text_field( $_GET['token'] ?? '' );

		if ( ! hash_equals( $stored_token, $url_token ) ) {
			$this->deny_access( 'token', $trip_id );
			return;
		}

		// Controlla scadenza
		$expiry = get_post_meta( $trip_id, self::META_EXPIRY, true );
		if ( ! empty( $expiry ) && time() > (int) $expiry ) {
			$this->deny_access( 'token_expired', $trip_id );
		}
	}

	/**
	 * Genera (o rigenera) il token per un viaggio.
	 */
	public static function generate_token( int $trip_id ): string {
		$token = bin2hex( random_bytes( 16 ) );
		update_post_meta( $trip_id, self::META_TOKEN, $token );
		return $token;
	}

	/**
	 * Genera il token automaticamente quando Gutenberg salva via REST
	 * e la visibilita' e' impostata su 'token'.
	 */
	public function rest_after_insert_trip( WP_Post $post ) {
		$vis = get_post_meta( $post->ID, self::META_VISIBILITY, true );
		if ( $vis === 'token' && empty( get_post_meta( $post->ID, self::META_TOKEN, true ) ) ) {
			self::generate_token( $post->ID );
		}
	}

	/**
	 * AJAX: rigenera il token (invalida il precedente).
	 */
	public function ajax_regenerate_token() {
		check_ajax_referer( 'td_privacy_nonce', 'nonce' );

		$post_id = (int) ( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( 'Permesso negato.' );
		}

		$token     = self::generate_token( $post_id );
		$share_url = add_query_arg( 'token', $token, get_permalink( $post_id ) );

		wp_send_json_success( array(
			'token'     => $token,
			'share_url' => $share_url,
		) );
	}

	/**
	 * AJAX: salva solo la scadenza del token.
	 */
	public function ajax_save_token_expiry() {
		check_ajax_referer( 'td_privacy_nonce', 'nonce' );

		$post_id = (int) ( $_POST['post_id'] ?? 0 );
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( __( 'Permesso negato.', 'travel-diary' ) );
		}

		$expiry_date = sanitize_text_field( $_POST['expiry'] ?? '' );
		if ( ! empty( $expiry_date ) ) {
			$ts = strtotime( $expiry_date );
			if ( $ts ) {
				update_post_meta( $post_id, self::META_EXPIRY, $ts );
			} else {
				wp_send_json_error( __( 'Data non valida.', 'travel-diary' ) );
			}
		} else {
			delete_post_meta( $post_id, self::META_EXPIRY );
		}

		wp_send_json_success();
	}

	// --- Accesso negato ------------------------------------------------------

	/**
	 * Mostra la pagina di accesso negato (403) e interrompe l'esecuzione.
	 */
	private function deny_access( string $reason, int $trip_id ) {
		$author_id = (int) get_post_field( 'post_author', $trip_id );
		if ( get_current_user_id() === $author_id || current_user_can( 'manage_options' ) ) {
			return;
		}

		status_header( 403 );
		nocache_headers();
		include plugin_dir_path( __FILE__ ) . '../public/partials/travel-diary-access-denied.php';
		exit;
	}

	// --- Admin: Meta Box -----------------------------------------------------

	/**
	 * Registra il meta box nella sidebar dell'editor del Viaggio.
	 */
	public function add_privacy_meta_box() {
		add_meta_box(
			'td_privacy_box',
			'<span class="dashicons dashicons-lock" style="vertical-align:middle;"></span> ' . __( 'Privacy', 'travel-diary' ),
			array( $this, 'render_privacy_meta_box' ),
			Travel_Diary_Cpt_Trip::POST_TYPE,
			'side',
			'high'
		);

		// Stessa sezione EXIF (solo opt-out, niente privacy) anche sulle Tappe
		add_meta_box(
			'td_entry_exif_box',
			'<span class="dashicons dashicons-location" style="vertical-align:middle;"></span> ' . __( 'Geolocalizzazione Foto', 'travel-diary' ),
			array( $this, 'render_exif_meta_box_entry' ),
			Travel_Diary_Cpt_Entry::POST_TYPE,
			'side',
			'low'
		);
	}

	/**
	 * Meta box EXIF semplice per le Tappe (solo opt-out, nessuna logica privacy).
	 */
	public function render_exif_meta_box_entry( WP_Post $post ) {
		?>
		<label style="font-size:11px; display:flex; align-items:flex-start; gap:6px; cursor:pointer; margin-top:4px;">
			<input type="checkbox" name="td_exif_disabled" value="1"
				<?php checked( get_post_meta( $post->ID, Travel_Diary_Exif::META_DISABLED, true ), '1' ); ?>
				style="margin-top:2px; flex-shrink:0;">
			<span style="color:#555; line-height:1.4; font-size:11px;">
				<?php _e( 'Non usare i dati GPS delle foto di questa tappa sulla mappa.', 'travel-diary' ); ?>
			</span>
		</label>
		<p style="font-size:10px; color:#888; margin-top:6px; margin-bottom:0;">
			<span class="dashicons dashicons-info-outline" style="font-size:12px;vertical-align:middle;"></span>
			<?php _e( ' Se le foto contengono coordinate GPS (EXIF), vengono usate per posizionarle automaticamente sulla mappa del viaggio.', 'travel-diary' ); ?>
		</p>
		<?php
	}

	/**
	 * Nasconde i pulsanti di riordinamento (frecce su/giu') del meta box Privacy
	 * perche' in sidebar Gutenberg non hanno utilita'.
	 */
	public function hide_reorder_buttons() {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== Travel_Diary_Cpt_Trip::POST_TYPE ) return;
		?>
		<style>
			#td_privacy_box .order-higher-indicator,
			#td_privacy_box .order-lower-indicator {
				display: none !important;
			}
		</style>
		<?php
	}

	/**
	 * Render del meta box.
	 * La visibilita' e' salvata da Gutenberg via REST (meta registrato con show_in_rest).
	 * Il token viene generato automaticamente via rest_after_insert_td_trip.
	 * La scadenza e la rigenerazione usano AJAX dedicato.
	 */
	public function render_privacy_meta_box( WP_Post $post ) {
		$visibility = get_post_meta( $post->ID, self::META_VISIBILITY, true ) ?: 'public';
		$token      = get_post_meta( $post->ID, self::META_TOKEN, true );
		$expiry_ts  = get_post_meta( $post->ID, self::META_EXPIRY, true );
		$expiry_val = $expiry_ts ? date( 'Y-m-d', (int) $expiry_ts ) : '';
		$share_url  = $token ? add_query_arg( 'token', $token, get_permalink( $post->ID ) ) : '';
		?>
		<p style="margin-bottom:6px; font-weight:600; font-size:12px;">
			<span class="dashicons dashicons-visibility" style="vertical-align:middle;"></span>
			<?php _e( ' Visibilita\'', 'travel-diary' ); ?>
		</p>
		<select name="td_visibility" id="td_visibility" style="width:100%; max-width:100%; box-sizing:border-box; margin-bottom:6px;">
			<?php
			$options = array(
				'public'  => __( 'Pubblico',         'travel-diary' ),
				'token'   => __( 'Link Segreto',      'travel-diary' ),
				'members' => __( 'Solo Community',    'travel-diary' ),
				'friends' => __( 'Solo Amici',         'travel-diary' ),
				'private' => __( 'Solo io (privato)', 'travel-diary' ),
			);
			foreach ( $options as $val => $label ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $val ),
					selected( $visibility, $val, false ),
					esc_html( $label )
				);
			}
			?>
		</select>
		<p style="font-size:10px; color:#888; margin-bottom:10px;">
			<span class="dashicons dashicons-info-outline" style="font-size:13px;vertical-align:middle;"></span>
			<?php _e( ' Salvata col pulsante Aggiorna di Gutenberg.', 'travel-diary' ); ?>
		</p>

		<!-- Sezione token -->
		<div id="td_token_section" style="display:<?php echo $visibility === 'token' ? 'block' : 'none'; ?>; border-top:1px solid #ddd; padding-top:10px; margin-top:4px;">

			<p style="font-size:11px; color:#555; margin-bottom:6px;">
				<span class="dashicons dashicons-admin-links" style="vertical-align:middle;font-size:14px;"></span>
				<?php _e( ' Link da condividere:', 'travel-diary' ); ?>
			</p>

			<?php if ( $share_url ) : ?>
				<input type="text" id="td_share_url" value="<?php echo esc_url( $share_url ); ?>" readonly
					style="width:100%; font-size:11px; margin-bottom:8px;" onclick="this.select();">
			<?php else : ?>
				<p style="font-size:11px; color:#999; margin-bottom:8px;" id="td_no_token">
					<span class="dashicons dashicons-warning" style="font-size:13px;vertical-align:middle;color:#dba617;"></span>
					<?php _e( ' Salva il viaggio per generare il link.', 'travel-diary' ); ?>
				</p>
			<?php endif; ?>

			<button type="button" id="td_regenerate_token" class="button button-secondary"
				style="width:100%; margin-bottom:6px;"
				data-post-id="<?php echo esc_attr( $post->ID ); ?>"
				data-nonce="<?php echo wp_create_nonce( 'td_privacy_nonce' ); ?>">
				<span class="dashicons dashicons-update" style="vertical-align:middle;font-size:15px;"></span>
				<?php _e( ' Rigenera Link', 'travel-diary' ); ?>
			</button>
			<p style="font-size:10px; color:#cc1818; margin-bottom:10px;">
				<span class="dashicons dashicons-warning" style="font-size:13px;vertical-align:middle;"></span>
				<?php _e( ' Rigenerare invalida il vecchio link.', 'travel-diary' ); ?>
			</p>

			<p style="font-size:11px; font-weight:600; margin:8px 0 4px;">
				<span class="dashicons dashicons-calendar-alt" style="vertical-align:middle;font-size:14px;"></span>
				<?php _e( ' Scadenza (opzionale):', 'travel-diary' ); ?>
			</p>
			<input type="date" id="td_token_expiry" value="<?php echo esc_attr( $expiry_val ); ?>"
				style="width:100%; margin-bottom:4px;">
			<button type="button" id="td_save_expiry" class="button"
				style="width:100%; margin-top:4px;"
				data-post-id="<?php echo esc_attr( $post->ID ); ?>"
				data-nonce="<?php echo wp_create_nonce( 'td_privacy_nonce' ); ?>">
				<span class="dashicons dashicons-saved" style="vertical-align:middle;font-size:15px;"></span>
				<?php _e( ' Salva scadenza', 'travel-diary' ); ?>
			</button>
			<p id="td_expiry_feedback" style="font-size:11px; color:#888; margin-top:4px;"></p>

		</div>

		<script>
		(function($){
			// Toggle sezione token al cambio visibilita'
			$('#td_visibility').on('change', function(){
				var isToken = $(this).val() === 'token';
				$('#td_token_section').toggle( isToken );
				
				// Generazione proattiva se vuoto
				if (isToken && $('#td_no_token').is(':visible') && $('#td_share_url').length === 0) {
					// Disabilita momentaneamente alert
					window.td_auto_gen = true;
					$('#td_regenerate_token').trigger('click');
				}
			});

			// Rigenera token
			$('#td_regenerate_token').on('click', function(){
				if (!window.td_auto_gen && !confirm('<?php echo esc_js( __( 'Sicuro? Il vecchio link smettera di funzionare.', 'travel-diary' ) ); ?>') ) return;
				window.td_auto_gen = false;
				var btn = $(this);
				btn.prop('disabled', true);
				$.post(ajaxurl, {
					action:  'td_regenerate_token',
					post_id: btn.data('post-id'),
					nonce:   btn.data('nonce')
				}, function(res){
					if ( res.success ) {
						if ( $('#td_share_url').length ) {
							$('#td_share_url').val( res.data.share_url );
						} else {
							$('#td_no_token').before('<input type="text" id="td_share_url" value="'+ res.data.share_url +'" readonly style="width:100%;font-size:11px;margin-bottom:8px;" onclick="this.select();">');
							$('#td_no_token').hide();
						}
					} else {
						alert( res.data );
					}
					btn.prop('disabled', false);
				});
			});

			// Salva scadenza
			$('#td_save_expiry').on('click', function(){
				var btn = $(this);
				btn.prop('disabled', true);
				$('#td_expiry_feedback').text('...');
				$.post(ajaxurl, {
					action:  'td_save_token_expiry',
					post_id: btn.data('post-id'),
					nonce:   btn.data('nonce'),
					expiry:  $('#td_token_expiry').val()
				}, function(res){
					$('#td_expiry_feedback')
						.css('color', res.success ? '#46b450' : '#dc3232')
						.text( res.success ? '<?php echo esc_js( __( 'Scadenza salvata.', 'travel-diary' ) ); ?>' : res.data );
					btn.prop('disabled', false);
				});
			});
		})(jQuery);
		</script>
		<!-- Sezione EXIF / GPS opt-out -->
		<div style="border-top:1px solid #ddd; padding-top:10px; margin-top:8px;">
			<p style="font-size:11px; font-weight:600; margin-bottom:6px;">
				<span class="dashicons dashicons-location" style="vertical-align:middle;font-size:14px;"></span>
				<?php _e( ' Geolocalizzazione Foto', 'travel-diary' ); ?>
			</p>
			<label style="font-size:11px; display:flex; align-items:flex-start; gap:6px; cursor:pointer;">
				<input type="checkbox" name="td_exif_disabled" value="1"
					<?php checked( get_post_meta( $post->ID, Travel_Diary_Exif::META_DISABLED, true ), '1' ); ?>
					style="margin-top:2px; flex-shrink:0;">
				<span style="color:#555; line-height:1.4;">
					<?php _e( 'Non usare i dati GPS delle foto di questo viaggio sulla mappa.', 'travel-diary' ); ?>
				</span>
			</label>
			<p style="font-size:10px; color:#888; margin-top:4px;">
				<span class="dashicons dashicons-info-outline" style="font-size:12px;vertical-align:middle;"></span>
				<?php _e( ' Se le foto contengono dati GPS (EXIF), vengono usati automaticamente per posizionarle sulla mappa del viaggio.', 'travel-diary' ); ?>
			</p>
		</div>

		<?php
	}

	/**
	 * Salva i meta tramite save_post (editor classico / wp-admin form).
	 * Per Gutenberg la visibilita' e' gestita via show_in_rest.
	 */
	public function save_privacy_meta( int $post_id ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return;
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		// Salva visibilita' se viene dal form classico (non Gutenberg)
		if ( isset( $_POST['td_visibility'] ) ) {
			$allowed = array( 'public', 'token', 'members', 'friends', 'private' );
			$vis     = sanitize_key( $_POST['td_visibility'] );
			if ( in_array( $vis, $allowed, true ) ) {
				update_post_meta( $post_id, self::META_VISIBILITY, $vis );
				if ( $vis === 'token' && empty( get_post_meta( $post_id, self::META_TOKEN, true ) ) ) {
					self::generate_token( $post_id );
				}
			}
		}

		// Salva opt-out EXIF (checkbox) per Trip e Entry
		if ( in_array( get_post_type( $post_id ), array( Travel_Diary_Cpt_Trip::POST_TYPE, Travel_Diary_Cpt_Entry::POST_TYPE ), true ) ) {
			$disabled = isset( $_POST['td_exif_disabled'] ) ? '1' : '';
			update_post_meta( $post_id, Travel_Diary_Exif::META_DISABLED, $disabled );
		}
	}

	// --- Helper --------------------------------------------------------------

	/**
	 * Ottiene l'ID del Trip padre di una Entry tramite tassonomia.
	 */
	private function get_parent_trip_id( int $entry_id ): ?int {
		$terms = get_the_terms( $entry_id, 'td_trip_cat' );
		if ( ! $terms || is_wp_error( $terms ) ) return null;

		$trips = get_posts( array(
			'post_type'      => Travel_Diary_Cpt_Trip::POST_TYPE,
			'name'           => $terms[0]->slug,
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		) );

		return $trips[0] ?? null;
	}
}
