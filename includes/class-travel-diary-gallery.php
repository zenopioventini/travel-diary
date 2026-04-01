<?php
/**
 * Travel_Diary_Gallery
 *
 * Gestisce le gallerie fotografiche su Trip (viaggio) e Entry (tappa).
 *
 * - Meta box backend con wp.media multi-selezione e drag & drop
 * - Isolamento Media Library per utente (utenti non-admin vedono solo i propri upload)
 * - Rendering frontend a griglia CSS con lightbox CSS-only
 *
 * @package Travel_Diary
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Travel_Diary_Gallery {

	const META_GALLERY = '_td_gallery';

	/** CPT che supportano la galleria */
	private static function post_types(): array {
		return array(
			Travel_Diary_Cpt_Trip::POST_TYPE,
			Travel_Diary_Cpt_Entry::POST_TYPE,
		);
	}

	// ─── Registrazione meta REST ──────────────────────────────────────────────

	public function register_gallery_meta() {
		foreach ( self::post_types() as $pt ) {
			register_post_meta( $pt, self::META_GALLERY, array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => '',
				'auth_callback'     => function() { return current_user_can( 'edit_posts' ); },
				'sanitize_callback' => 'sanitize_text_field',
			) );
		}
	}

	// ─── Admin: Meta Box ─────────────────────────────────────────────────────

	public function add_gallery_meta_boxes() {
		foreach ( self::post_types() as $pt ) {
			add_meta_box(
				'td_gallery_box',
				'<span class="dashicons dashicons-format-gallery" style="vertical-align:middle;"></span> ' . __( 'Galleria Foto', 'travel-diary' ),
				array( $this, 'render_gallery_meta_box' ),
				$pt,
				'normal',
				'default'
			);
		}
	}

	public function render_gallery_meta_box( WP_Post $post ) {
		$raw       = get_post_meta( $post->ID, self::META_GALLERY, true );
		$ids_array = $raw ? array_filter( array_map( 'intval', explode( ',', $raw ) ) ) : array();
		?>
		<div id="td-gallery-wrap">

			<div id="td-gallery-preview" class="td-gallery-admin-preview">
				<?php foreach ( $ids_array as $attachment_id ) :
					$img = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
					if ( ! $img ) continue;
				?>
					<div class="td-gallery-item" data-id="<?php echo esc_attr( $attachment_id ); ?>">
						<img src="<?php echo esc_url( $img[0] ); ?>" alt="" width="80" height="80">
						<button type="button" class="td-gallery-remove" title="<?php esc_attr_e( 'Rimuovi', 'travel-diary' ); ?>">×</button>
					</div>
				<?php endforeach; ?>
			</div>

			<input type="hidden" id="td-gallery-ids" name="td_gallery_ids"
				value="<?php echo esc_attr( $raw ); ?>">

			<button type="button" id="td-gallery-add" class="button">
				<span class="dashicons dashicons-plus-alt2" style="vertical-align:middle;font-size:15px;"></span>
				<?php _e( ' Aggiungi foto', 'travel-diary' ); ?>
			</button>

			<p style="font-size:11px; color:#888; margin-top:6px;">
				<span class="dashicons dashicons-info-outline" style="font-size:13px;vertical-align:middle;"></span>
				<?php _e( ' Trascina le immagini per riordinarle. Salva col pulsante Aggiorna.', 'travel-diary' ); ?>
			</p>

		</div>

		<style>
			.td-gallery-admin-preview {
				display: flex;
				flex-wrap: wrap;
				gap: 8px;
				margin-bottom: 10px;
				min-height: 48px;
				padding: 6px;
				border: 1px dashed #ddd;
				border-radius: 4px;
				background: #fafafa;
			}
			.td-gallery-admin-preview:empty::before {
				content: '<?php echo esc_js( __( 'Nessuna foto selezionata.', 'travel-diary' ) ); ?>';
				color: #aaa;
				font-size: 12px;
				line-height: 48px;
				padding-left: 4px;
			}
			.td-gallery-item {
				position: relative;
				cursor: move;
			}
			.td-gallery-item img {
				display: block;
				width: 80px;
				height: 80px;
				object-fit: cover;
				border: 2px solid #ddd;
				border-radius: 3px;
			}
			.td-gallery-item:hover img {
				border-color: #2271b1;
			}
			.td-gallery-remove {
				position: absolute;
				top: -7px;
				right: -7px;
				background: #cc1818;
				color: #fff;
				border: 2px solid #fff;
				border-radius: 50%;
				width: 20px;
				height: 20px;
				font-size: 13px;
				line-height: 16px;
				padding: 0;
				cursor: pointer;
				text-align: center;
			}
			.td-gallery-remove:hover {
				background: #a00;
			}
		</style>

		<script>
		(function($){
			var frame;

			// Aggiorna il campo hidden con gli ID nell'ordine corrente
			function updateIds() {
				var ids = [];
				$('#td-gallery-preview .td-gallery-item').each(function(){
					ids.push( $(this).data('id') );
				});
				$('#td-gallery-ids').val( ids.join(',') );
			}

			// Drag & drop reorder con jQuery UI Sortable (bundled in WP admin)
			$('#td-gallery-preview').sortable({
				update: function(){ updateIds(); }
			});

			// Apri Media Library
			$('#td-gallery-add').on('click', function(){
				if ( frame ) { frame.open(); return; }

				frame = wp.media({
					title:    '<?php echo esc_js( __( 'Seleziona foto per la galleria', 'travel-diary' ) ); ?>',
					button:   { text: '<?php echo esc_js( __( 'Aggiungi alla galleria', 'travel-diary' ) ); ?>' },
					multiple: true
				});

				frame.on('select', function(){
					var selection = frame.state().get('selection');
					selection.each(function(attachment){
						var id = attachment.id;
						// Evita duplicati
						if ( $('#td-gallery-preview [data-id="'+id+'"]').length ) return;

						var sizes = attachment.attributes.sizes;
						var thumb = sizes && sizes.thumbnail
							? sizes.thumbnail.url
							: attachment.attributes.url;

						$('#td-gallery-preview').append(
							'<div class="td-gallery-item" data-id="'+id+'">' +
							'<img src="'+thumb+'" width="80" height="80">' +
							'<button type="button" class="td-gallery-remove" title="<?php echo esc_js( __( 'Rimuovi', 'travel-diary' ) ); ?>">×</button>' +
							'</div>'
						);
					});
					updateIds();
				});

				frame.open();
			});

			// Rimuovi singola foto
			$(document).on('click', '.td-gallery-remove', function(){
				$(this).closest('.td-gallery-item').remove();
				updateIds();
			});

		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Carica wp.media e jQuery UI Sortable nelle pagine di editing dei CPT supportati.
	 */
	public function enqueue_admin_scripts( string $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) return;
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, self::post_types() ) ) return;

		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	/**
	 * Salva gli ID della galleria (editor classico / save_post).
	 * Gutenberg: il meta e' registrato con show_in_rest=true, viene gestito via REST.
	 */
	public function save_gallery_meta( int $post_id ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return;
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( ! isset( $_POST['td_gallery_ids'] ) ) return;

		$raw = sanitize_text_field( $_POST['td_gallery_ids'] );
		// Sanifica: solo interi positivi separati da virgola
		$ids = array_filter( array_map( 'intval', explode( ',', $raw ) ) );
		update_post_meta( $post_id, self::META_GALLERY, implode( ',', $ids ) );
	}

	// ─── Isolamento Media Library per utente ──────────────────────────────────

	/**
	 * Filtra la query AJAX della Media Library modal:
	 * gli utenti non-admin vedono solo i propri upload.
	 */
	public function filter_media_library( array $query ): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			$query['author'] = get_current_user_id();
		}
		return $query;
	}

	// ─── Helper pubblico per i template ──────────────────────────────────────

	/**
	 * Restituisce l'array di attachment ID per un dato post.
	 * Usabile nei template del tema.
	 */
	public static function get_gallery_ids( int $post_id ): array {
		$raw = get_post_meta( $post_id, self::META_GALLERY, true );
		if ( empty( $raw ) ) return array();
		return array_filter( array_map( 'intval', explode( ',', $raw ) ) );
	}
}
