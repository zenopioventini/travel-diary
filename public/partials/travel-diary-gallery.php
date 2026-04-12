<?php
/**
 * Template partial: Galleria fotografica di un Viaggio o Tappa.
 *
 * Usato in single-td_trip.php e single-td_entry.php.
 * Richiede che la classe Travel_Diary_Gallery sia disponibile.
 *
 * Lightbox: CSS-only con :target.
 * - Chiusura ancora → scroll alla galleria (non al top pagina)
 * - Frecce prev/next per navigare tra le immagini senza richiudere
 *
 * @package Travel_Diary
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Travel_Diary_Gallery' ) ) return;

$post_id  = get_the_ID();
$ids      = Travel_Diary_Gallery::get_gallery_ids( $post_id );
$total    = count( $ids );

if ( empty( $ids ) ) return;

// Ancora testata galleria — la X chiude qui, non a #top
$gallery_anchor = 'td-gallery-' . $post_id;
?>

<section id="<?php echo esc_attr( $gallery_anchor ); ?>" class="td-gallery" aria-label="<?php esc_attr_e( 'Galleria fotografica', 'travel-diary' ); ?>">
	<h2 class="td-gallery__title">
		<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="vertical-align:middle;margin-right:6px;">
			<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
		</svg>
		<?php _e( 'Galleria', 'travel-diary' ); ?>
		<span class="td-gallery__count"><?php echo $total; ?> foto</span>
	</h2>

	<div class="td-gallery__grid">
		<?php foreach ( $ids as $i => $attachment_id ) :
			$full  = wp_get_attachment_image_src( $attachment_id, 'large' );
			$thumb = wp_get_attachment_image_src( $attachment_id, 'medium_large' );
			$alt   = trim( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
			if ( ! $full || ! $thumb ) continue;
			$lb_id   = 'td-lb-' . $post_id . '-' . $i;
		?>
			<a href="#<?php echo esc_attr( $lb_id ); ?>" class="td-gallery__thumb" aria-label="<?php echo $alt ? esc_attr( $alt ) : esc_attr__( 'Ingrandisci immagine', 'travel-diary' ); ?>">
				<img src="<?php echo esc_url( $thumb[0] ); ?>"
					alt="<?php echo esc_attr( $alt ); ?>"
					loading="lazy"
					width="<?php echo (int) $thumb[1]; ?>"
					height="<?php echo (int) $thumb[2]; ?>">
			</a>
		<?php endforeach; ?>
	</div>
</section>

<?php
// Lightbox fuori dalla griglia per non sporcare il grid flow
foreach ( $ids as $i => $attachment_id ) :
	$full  = wp_get_attachment_image_src( $attachment_id, 'large' );
	$alt   = trim( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	if ( ! $full ) continue;

	$lb_id   = 'td-lb-' . $post_id . '-' . $i;
	$lb_prev = ( $i > 0 )           ? 'td-lb-' . $post_id . '-' . ( $i - 1 ) : '';
	$lb_next = ( $i < $total - 1 )  ? 'td-lb-' . $post_id . '-' . ( $i + 1 ) : '';
?>
<!-- Lightbox <?php echo $i + 1; ?>/<?php echo $total; ?> -->
<div id="<?php echo esc_attr( $lb_id ); ?>" class="td-lightbox" role="dialog" aria-modal="true" aria-label="<?php echo $alt ? esc_attr( $alt ) : esc_attr__( 'Immagine ingrandita', 'travel-diary' ); ?>">

	<!-- Chiude e torna all'ancora della galleria (non #top) -->
	<a href="#<?php echo esc_attr( $gallery_anchor ); ?>" class="td-lightbox__close" aria-label="<?php esc_attr_e( 'Chiudi', 'travel-diary' ); ?>">×</a>

	<!-- Freccia PREV -->
	<?php if ( $lb_prev ) : ?>
	<a href="#<?php echo esc_attr( $lb_prev ); ?>" class="td-lightbox__nav td-lightbox__prev" aria-label="<?php esc_attr_e( 'Immagine precedente', 'travel-diary' ); ?>">&#8249;</a>
	<?php endif; ?>

	<!-- Freccia NEXT -->
	<?php if ( $lb_next ) : ?>
	<a href="#<?php echo esc_attr( $lb_next ); ?>" class="td-lightbox__nav td-lightbox__next" aria-label="<?php esc_attr_e( 'Immagine successiva', 'travel-diary' ); ?>">&#8250;</a>
	<?php endif; ?>

	<figure class="td-lightbox__figure">
		<img src="<?php echo esc_url( $full[0] ); ?>"
			alt="<?php echo esc_attr( $alt ); ?>"
			width="<?php echo (int) $full[1]; ?>"
			height="<?php echo (int) $full[2]; ?>">
		<?php if ( $alt ) : ?>
			<figcaption class="td-lightbox__caption"><?php echo esc_html( $alt ); ?></figcaption>
		<?php endif; ?>

		<!-- Contatore immagini -->
		<p class="td-lightbox__counter"><?php echo ( $i + 1 ) . ' / ' . $total; ?></p>
	</figure>
</div>
<?php endforeach; ?>

<style>
/* ── Gallery Grid ─────────────────────────────────────────────────────── */
.td-gallery {
	margin: 2.5rem 0;
	scroll-margin-top: 80px; /* Offset per navbar fissa, se presente */
}
.td-gallery__title {
	font-size: 1.25rem;
	margin-bottom: 1rem;
	color: var(--td-heading, #e8e8e8);
	display: flex;
	align-items: center;
	gap: 8px;
}
.td-gallery__count {
	font-size: 0.8rem;
	font-weight: 400;
	color: #888;
	margin-left: 4px;
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

/* ── Pulsante chiusura ── */
.td-lightbox__close {
	position: absolute;
	top: 1rem;
	right: 1.5rem;
	color: #fff;
	font-size: 2.8rem;
	line-height: 1;
	text-decoration: none;
	opacity: 0.7;
	transition: opacity 0.2s, transform 0.2s;
	z-index: 2;
}
.td-lightbox__close:hover {
	opacity: 1;
	transform: scale(1.1);
}

/* ── Frecce navigazione prev/next ── */
.td-lightbox__nav {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	color: #fff;
	font-size: 4rem;
	line-height: 1;
	text-decoration: none;
	opacity: 0.6;
	transition: opacity 0.2s, transform 0.15s;
	padding: 0 1rem;
	z-index: 2;
	user-select: none;
}
.td-lightbox__nav:hover {
	opacity: 1;
}
.td-lightbox__prev {
	left: 0.5rem;
}
.td-lightbox__prev:hover {
	transform: translateY(-50%) translateX(-3px);
}
.td-lightbox__next {
	right: 0.5rem;
}
.td-lightbox__next:hover {
	transform: translateY(-50%) translateX(3px);
}

/* ── Figura e immagine ── */
.td-lightbox__figure {
	max-width: 86vw;
	max-height: 90vh;
	text-align: center;
	margin: 0;
}
.td-lightbox__figure img {
	max-width: 100%;
	max-height: 80vh;
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
.td-lightbox__counter {
	color: rgba(255,255,255,0.4);
	font-size: 0.75rem;
	margin-top: 0.4rem;
	letter-spacing: 0.05em;
}

/* ── Frecce nascoste su mobile troppo piccolo ── */
@media (max-width: 480px) {
	.td-lightbox__nav { font-size: 2.5rem; padding: 0 0.5rem; }
}
</style>
