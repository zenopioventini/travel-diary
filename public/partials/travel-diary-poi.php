<?php
/**
 * Template partial: Punti di Interesse (POI) della Tappa.
 *
 * @package Travel_Diary
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Usa il prenome per i field ACF
$poi_list = function_exists('get_field') ? get_field( 'td_poi_list', get_the_ID() ) : false;

if ( empty( $poi_list ) ) return;
?>

<section class="td-poi-section" aria-label="<?php esc_attr_e( 'Punti di Interesse', 'travel-diary' ); ?>">
	<h3 class="td-poi-section__title">
		<span class="dashicons dashicons-location-alt" style="vertical-align:middle;"></span>
		<?php _e( 'Punti di Interesse', 'travel-diary' ); ?>
	</h3>

	<div class="td-poi-grid">
		<?php foreach ( $poi_list as $poi ) : 
			// Dati POI
			$titolo      = $poi['titolo'] ?? '';
			$categoria   = $poi['categoria'] ?? '';
			$valutazione = $poi['valutazione'] ?? '';
			$info        = $poi['info'] ?? '';
			$desc        = $poi['descrizione'] ?? '';
			$img_id      = $poi['immagine'] ?? 0;
			
			// Badge Valutazione
			$badge_html = '';
			if ( $valutazione === 'must' ) {
				$badge_html = '<span class="td-badge td-badge--must">🔥 Imperdibile</span>';
			} elseif ( $valutazione === 'recommended' ) {
				$badge_html = '<span class="td-badge td-badge--rec">👍 Consigliato</span>';
			} elseif ( $valutazione === 'if_time' ) {
				$badge_html = '<span class="td-badge td-badge--iftime">⏱️ Se hai tempo</span>';
			}
		?>
			<div class="td-poi-card">
				<?php if ( $img_id ) : 
					$img_url = wp_get_attachment_image_url( $img_id, 'medium' );
					if ( $img_url ) :
				?>
					<div class="td-poi-card__image" style="background-image: url('<?php echo esc_url( $img_url ); ?>');">
						<?php echo $badge_html; ?>
					</div>
				<?php 
					endif; 
				endif; 
				?>
				
				<div class="td-poi-card__content">
					<?php if ( ! $img_id && $badge_html ) : ?>
						<div style="margin-bottom: 8px;">
							<?php echo $badge_html; ?>
						</div>
					<?php endif; ?>

					<h4 class="td-poi-card__title"><?php echo esc_html( $titolo ); ?></h4>
					
					<?php if ( $categoria || $info ) : ?>
						<div class="td-poi-card__meta">
							<?php if ( $categoria ) : ?>
								<span class="td-poi-cat"><?php echo esc_html( ucfirst( $categoria ) ); ?></span>
							<?php endif; ?>
							<?php if ( $info ) : ?>
								<span class="td-poi-info">🕒 <?php echo esc_html( $info ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( $desc ) : ?>
						<div class="td-poi-card__desc">
							<?php echo wpautop( wp_kses_post( $desc ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<style>
.td-poi-section { margin: 3rem 0; }
.td-poi-section__title {
	font-size: 1.3rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--td-accent, #d4943a); padding-bottom: 8px; display: inline-block;
}
.td-poi-grid {
	display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;
}
.td-poi-card {
	background: #1e1e1e; border: 1px solid #333; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;
}
.td-poi-card__image {
	width: 100%; height: 160px; background-size: cover; background-position: center; position: relative;
}
.td-poi-card__image .td-badge {
	position: absolute; bottom: 8px; left: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.5);
}
.td-poi-card__content { padding: 1rem; flex-grow: 1; display:flex; flex-direction:column; }
.td-poi-card__title { margin: 0 0 8px 0; font-size: 1.1rem; color: #fff; }
.td-poi-card__meta { font-size: 0.8rem; color: #aaa; margin-bottom: 12px; display: flex; flex-wrap: wrap; gap: 10px; }
.td-poi-cat { text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; color: var(--td-accent, #d4943a); }
.td-poi-card__desc { font-size: 0.9rem; color: #ccc; line-height: 1.5; margin-bottom:0; }
.td-poi-card__desc p:last-child { margin-bottom:0; }

.td-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; color: #fff; text-transform: uppercase; letter-spacing: 0.5px;}
.td-badge--must { background: #e53935; }
.td-badge--rec { background: #43a047; }
.td-badge--iftime { background: #fb8c00; }
</style>
