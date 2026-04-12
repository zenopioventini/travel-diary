<?php
/**
 * Template partial: Tabella delle Spese della Tappa.
 *
 * @package Travel_Diary
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$costi = function_exists('get_field') ? get_field( 'td_costi', get_the_ID() ) : false;

if ( empty( $costi ) ) return;

$totale = 0;
// Raggruppiamo i costi per valuta nel caso ci fossero spese miste (raro, ma utile preventivarlo)
$totali_per_valuta = array();

foreach ( $costi as $costo ) {
	$importo = floatval( $costo['importo'] ?? 0 );
	$valuta  = $costo['valuta'] ?? 'EUR';
	
	if ( ! isset( $totali_per_valuta[ $valuta ] ) ) {
		$totali_per_valuta[ $valuta ] = 0;
	}
	$totali_per_valuta[ $valuta ] += $importo;
}
?>

<section class="td-expenses-section" aria-label="<?php esc_attr_e( 'Spese della Tappa', 'travel-diary' ); ?>">
	<h3 class="td-expenses-section__title">
		<?php echo Travel_Diary_Icons::get('euro', ['width'=>20,'height'=>20,'class'=>'td-inline-icon','style'=>'vertical-align:-4px;margin-right:6px;']); ?>
		<?php _e( 'Dashboard Spese', 'travel-diary' ); ?>
	</h3>

	<div class="td-expenses-container">
		<div class="td-expenses-table-wrap">
			<table class="td-expenses-table">
				<thead>
					<tr>
						<th><?php _e( 'Categoria', 'travel-diary' ); ?></th>
						<th><?php _e( 'Dettaglio', 'travel-diary' ); ?></th>
						<th class="td-text-right"><?php _e( 'Importo', 'travel-diary' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $costi as $costo ) : 
						$cat = $costo['categoria'] ?? '';
						$dettaglio = $costo['nota'] ?? '';
						$importo = floatval( $costo['importo'] ?? 0 );
						$valuta = $costo['valuta'] ?? 'EUR';
						
						// Tradurre la categoria in SVG icon
						$cat_label = ucfirst( $cat );
						$icon_svg = Travel_Diary_Icons::get($cat, ['width'=>14, 'height'=>14, 'class'=>'td-inline-icon', 'style'=>'margin-right:4px;vertical-align:-2px;']);
						
						if ($cat === 'cibo') $cat_label = 'Cibo / Ristorazione';
					?>
					<tr>
						<td><span class="td-exp-cat"><?php echo $icon_svg . esc_html( $cat_label ); ?></span></td>
						<td class="td-exp-note"><?php echo esc_html( $dettaglio ); ?></td>
						<td class="td-text-right"><strong><?php echo number_format( $importo, 2, ',', '.' ) . ' ' . esc_html( $valuta ); ?></strong></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div class="td-expenses-summary">
			<h4><?php _e( 'Totale Speso', 'travel-diary' ); ?></h4>
			<?php foreach ( $totali_per_valuta as $v => $tot ) : ?>
				<div class="td-exp-total">
					<span class="td-exp-total-val"><?php echo number_format( $tot, 2, ',', '.' ); ?></span>
					<span class="td-exp-total-cur"><?php echo esc_html( $v ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<style>
.td-expenses-section { margin: 3rem 0; background: #1a1a1a; padding: 1.5rem; border-radius: 8px; border: 1px solid #333; }
.td-expenses-section__title {
	font-size: 1.25rem; margin-top: 0; margin-bottom: 1.5rem; color: #fff;
}
.td-expenses-container {
	display: flex; gap: 2rem; align-items: flex-start;
}
@media (max-width: 768px) {
	.td-expenses-container { flex-direction: column; }
}
.td-expenses-table-wrap {
	flex-grow: 1; width: 100%; overflow-x: auto;
}
.td-expenses-table {
	width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;
}
.td-expenses-table th {
	border-bottom: 2px solid #444; padding: 10px 8px; color: #aaa; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.8rem;
}
.td-expenses-table td {
	padding: 12px 8px; border-bottom: 1px solid #333; color: #ddd; vertical-align: middle;
}
.td-text-right { text-align: right; }
.td-exp-cat { background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; white-space: nowrap; }
.td-exp-note { color: #888; font-style: italic; }

.td-expenses-summary {
	min-width: 200px; background: #222; padding: 1.5rem; border-radius: 8px; border: 1px solid #444; text-align: center;
}
.td-expenses-summary h4 {
	margin-top: 0; margin-bottom: 1rem; font-size: 0.9rem; color: #aaa; text-transform: uppercase; letter-spacing: 0.05em;
}
.td-exp-total { margin-bottom: 10px; }
.td-exp-total:last-child { margin-bottom: 0; }
.td-exp-total-val { font-size: 2rem; font-weight: 700; color: var(--td-accent, #d4943a); display: block; line-height: 1; }
.td-exp-total-cur { font-size: 0.9rem; color: #fff; font-weight: bold; }
</style>
