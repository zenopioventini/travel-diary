<?php
/**
 * Travel_Diary_Video
 *
 * Gestisce l'inserimento di un Video in Evidenza (YouTube/Vimeo)
 * su Viaggi (`td_trip`) e Tappe (`td_entry`).
 *
 * @package Travel_Diary
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Travel_Diary_Video {

	public const META_VIDEO = '_td_featured_video';

	/** I CPT supportati */
	private static function post_types(): array {
		return array(
			Travel_Diary_Cpt_Trip::POST_TYPE,
			Travel_Diary_Cpt_Entry::POST_TYPE,
		);
	}

	public function register_video_meta() {
		foreach ( self::post_types() as $pt ) {
			register_post_meta( $pt, self::META_VIDEO, array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => '',
				'auth_callback'     => function() { return current_user_can( 'edit_posts' ); },
				'sanitize_callback' => 'sanitize_url',
			) );
		}
	}

	public function add_video_meta_boxes() {
		foreach ( self::post_types() as $pt ) {
			add_meta_box(
				'td_featured_video_box',
				'<span class="dashicons dashicons-video-alt3" style="vertical-align:middle;"></span> ' . __( 'Video in Evidenza', 'travel-diary' ),
				array( $this, 'render_video_meta_box' ),
				$pt,
				'side',
				'low'
			);
		}
	}

	public function render_video_meta_box( WP_Post $post ) {
		$video_url = get_post_meta( $post->ID, self::META_VIDEO, true );
		?>
		<div style="margin-top:8px;">
			<label for="td_featured_video" style="display:block; margin-bottom:6px;">
				<?php _e( 'Inserisci URL YouTube o Vimeo:', 'travel-diary' ); ?>
			</label>
			<input type="url" id="td_featured_video" name="td_featured_video" value="<?php echo esc_url( $video_url ); ?>" style="width:100%;" placeholder="https://youtube.com/watch?v=..." />
			<p class="description">
				<?php _e( 'Se specificato, questo video prenderà il posto dell\'immagine in evidenza all\'interno della pagina.', 'travel-diary' ); ?>
			</p>
		</div>
		<?php
	}

	public function save_video_meta( int $post_id ) {
		// Se salvato via REST (Gutenberg), WordPress gestisce il salvataggio automatico 
		// poiche' il meta e' registrato con show_in_rest = true.
		// Fallback per salvataggio form classico:
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return;
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( ! isset( $_POST['td_featured_video'] ) ) return;

		$url = sanitize_url( wp_unslash( $_POST['td_featured_video'] ) );
		update_post_meta( $post_id, self::META_VIDEO, $url );
	}
}
