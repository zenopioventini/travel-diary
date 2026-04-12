<?php
/**
 * Template partial: Galleria fotografica di un Viaggio o Tappa.
 *
 * Usato in single-td_trip.php e single-td_entry.php.
 * Richiede che la classe Travel_Diary_Gallery sia disponibile.
 *
 * @package Travel_Diary
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Travel_Diary_Gallery' ) ) return;

$post_id = get_the_ID();
$ids     = Travel_Diary_Gallery::get_gallery_ids( $post_id );

if ( empty( $ids ) ) return;
?>

<section class="td-gallery" aria-label="<?php esc_attr_e( 'Galleria fotografica', 'travel-diary' ); ?>">
	<h2 class="td-gallery__title">
		<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="vertical-align:middle;margin-right:6px;">
			<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
		</svg>
		<?php _e( 'Galleria', 'travel-diary' ); ?>
	</h2>

	<div class="td-gallery__grid">
		<?php foreach ( $ids as $i => $attachment_id ) :
			$full  = wp_get_attachment_image_src( $attachment_id, 'large' );
			$thumb = wp_get_attachment_image_src( $attachment_id, 'medium_large' );
			$alt   = trim( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
			if ( ! $full || ! $thumb ) continue;
			$lb_id = 'td-lb-' . $post_id . '-' . $i;
		?>
			<a href="#<?php echo esc_attr( $lb_id ); ?>" class="td-gallery__thumb" aria-label="<?php echo $alt ? esc_attr( $alt ) : esc_attr__( 'Ingrandisci immagine', 'travel-diary' ); ?>">
				<img src="<?php echo esc_url( $thumb[0] ); ?>"
					alt="<?php echo esc_attr( $alt ); ?>"
					loading="lazy"
					width="<?php echo (int) $thumb[1]; ?>"
					height="<?php echo (int) $thumb[2]; ?>">
			</a>

			<!-- Lightbox CSS-only -->
			<div id="<?php echo esc_attr( $lb_id ); ?>" class="td-lightbox" role="dialog" aria-modal="true" aria-label="<?php echo $alt ? esc_attr( $alt ) : esc_attr__( 'Immagine ingrandita', 'travel-diary' ); ?>">
				<a href="#" class="td-lightbox__close" aria-label="<?php esc_attr_e( 'Chiudi', 'travel-diary' ); ?>">×</a>
				<figure class="td-lightbox__figure">
					<img src="<?php echo esc_url( $full[0] ); ?>"
						alt="<?php echo esc_attr( $alt ); ?>"
						width="<?php echo (int) $full[1]; ?>"
						height="<?php echo (int) $full[2]; ?>">
					<?php if ( $alt ) : ?>
						<figcaption class="td-lightbox__caption"><?php echo esc_html( $alt ); ?></figcaption>
					<?php endif; ?>
				</figure>
			</div>

		<?php endforeach; ?>
	</div>
</section>

<style>
/* ── Gallery Grid ─────────────────────────────────────────────────────── */
.td-gallery {
	margin: 2.5rem 0;
}
.td-gallery__title {
	font-size: 1.25rem;
	margin-bottom: 1rem;
	color: var(--td-heading, #1a1a1a);
}
.td-gallery__grid {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 6px;
}
@media (max-width: 768px) {
	.td-gallery__grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
	.td-gallery__grid { grid-template-columns: 1fr; }
}
.td-gallery__thumb {
	display: block;
	overflow: hidden;
	border-radius: 4px;
	aspect-ratio: 4/3;
}
.td-gallery__thumb img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
	transition: transform 0.3s ease, opacity 0.2s;
}
.td-gallery__thumb:hover img {
	transform: scale(1.04);
	opacity: 0.9;
}

/* ── Lightbox CSS-only ────────────────────────────────────────────────── */
.td-lightbox {
	display: none;
	position: fixed;
	inset: 0;
	background: rgba(0, 0, 0, 0.92);
	z-index: 9999;
	align-items: center;
	justify-content: center;
	flex-direction: column;
}
.td-lightbox:target {
	display: flex;
}
.td-lightbox__close {
	position: absolute;
	top: 1rem;
	right: 1.5rem;
	color: #fff;
	font-size: 2.5rem;
	line-height: 1;
	text-decoration: none;
	opacity: 0.7;
	transition: opacity 0.2s;
}
.td-lightbox__close:hover {
	opacity: 1;
}
.td-lightbox__figure {
	max-width: 90vw;
	max-height: 90vh;
	text-align: center;
	margin: 0;
}
.td-lightbox__figure img {
	max-width: 100%;
	max-height: 82vh;
	object-fit: contain;
	border-radius: 4px;
	display: block;
	margin: 0 auto;
}
.td-lightbox__caption {
	color: rgba(255,255,255,0.75);
	font-size: 0.875rem;
	margin-top: 0.75rem;
}
</style>
