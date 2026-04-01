<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$entry_id   = get_the_ID();
$arrival    = get_field('field_entry_arrivo', $entry_id);
$departure  = get_field('field_entry_partenza', $entry_id);
$map        = get_field('field_entry_posizione', $entry_id);
$km_reali   = get_field('field_entry_km_reali', $entry_id);
$mezzo      = get_field('field_entry_mezzo_trasporto', $entry_id);
$meteo      = get_field('field_entry_meteo', $entry_id);
$valutaz    = get_field('field_entry_valutazione', $entry_id);
$costi      = get_field('field_entry_costi', $entry_id);

// Recupero il viaggio genitore tramite tassonomia
$terms = get_the_terms($entry_id, 'td_trip_cat');
$parent_trip = false;
if ($terms && !is_wp_error($terms)) {
	$trip_posts = get_posts(array(
		'name'           => $terms[0]->slug,
		'post_type'      => Travel_Diary_Cpt_Trip::POST_TYPE,
		'post_status'    => 'publish',
		'posts_per_page' => 1,
	));
	if (!empty($trip_posts)) {
		$parent_trip = $trip_posts[0];
	}
}

// --- Barra di navigazione top ---
$td_entry_nav_html = '';
if ($parent_trip) {
	$td_entry_nav_html  = '<div style="background:#f1f1f1;padding:10px 15px;border-radius:6px;margin-bottom:20px;font-weight:bold;">';
	$td_entry_nav_html .= '⬅ <a href="' . esc_url(get_permalink($parent_trip->ID)) . '">Torna al Viaggio: ' . esc_html($parent_trip->post_title) . '</a>';
	$td_entry_nav_html .= '</div>';
}

// --- Calcolo totale spese ---
$totale_spese = 0;
$spese_per_cat = array();
if ($costi && is_array($costi)) {
	foreach ($costi as $spesa) {
		$importo = floatval($spesa['importo'] ?? 0);
		$cat     = $spesa['categoria'] ?? 'varie';
		$totale_spese += $importo;
		$spese_per_cat[$cat] = ($spese_per_cat[$cat] ?? 0) + $importo;
	}
}
?>

<div class="travel-diary-entry-container" style="margin-top:40px; padding-top:20px; border-top:2px solid #eee;">

	<!-- Scheda Dettagli -->
	<div class="td-entry-meta" style="background:#fafafa; padding:18px 20px; border-radius:8px; border:1px solid #ddd; margin-bottom:24px; display:flex; flex-wrap:wrap; gap:16px;">
		<?php if ($arrival): ?>
			<span><strong>📅 Arrivo:</strong> <?php echo esc_html($arrival); ?></span>
		<?php endif; ?>
		<?php if ($departure): ?>
			<span><strong>📅 Partenza:</strong> <?php echo esc_html($departure); ?></span>
		<?php endif; ?>
		<?php if ($mezzo): ?>
			<span><strong>Mezzo:</strong> <?php echo esc_html($mezzo); ?></span>
		<?php endif; ?>
		<?php if ($meteo): ?>
			<span><strong>Meteo:</strong> <?php echo esc_html($meteo); ?></span>
		<?php endif; ?>
		<?php if ($valutaz): ?>
			<span><strong>Voto:</strong> <?php echo esc_html($valutaz); ?></span>
		<?php endif; ?>
		<?php if ($km_reali): ?>
			<span><strong>📍 Km percorsi:</strong> <?php echo esc_html($km_reali); ?> km</span>
		<?php endif; ?>
		<?php if ($parent_trip): ?>
			<span><strong>Viaggio:</strong> <a href="<?php echo esc_url(get_permalink($parent_trip->ID)); ?>"><?php echo esc_html($parent_trip->post_title); ?></a></span>
		<?php endif; ?>
	</div>

	<!-- Mappa -->
	<?php if ($map && isset($map['lat']) && isset($map['lng'])) : ?>
		<div id="td-entry-map" style="width:100%; height:350px; background:#eaebec; border-radius:8px; margin-bottom:24px;"></div>
		<script>var tdTripMapData = <?php echo json_encode(array(array('lat' => $map['lat'], 'lng' => $map['lng'], 'title' => get_the_title(), 'url' => ''))); ?>;</script>
	<?php endif; ?>

	<!-- Riepilogo Spese -->
	<?php if ($costi && is_array($costi) && count($costi) > 0) : ?>
		<div class="td-entry-costi" style="margin-top:8px;">
			<h4 style="margin-bottom:12px;">💳 Spese della tappa — Totale: <strong>€ <?php echo number_format($totale_spese, 2, ',', '.'); ?></strong></h4>
			<table style="width:100%; border-collapse:collapse; font-size:0.95em;">
				<thead>
					<tr style="background:#f0f0f0; text-align:left;">
						<th style="padding:8px 12px;">Categoria</th>
						<th style="padding:8px 12px;">Importo</th>
						<th style="padding:8px 12px;">Nota</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($costi as $i => $spesa) :
						$bg = ($i % 2 === 0) ? '#fff' : '#f9f9f9';
					?>
					<tr style="background:<?php echo $bg; ?>; border-bottom:1px solid #eee;">
						<td style="padding:8px 12px;"><?php echo esc_html($spesa['categoria'] ?? '—'); ?></td>
						<td style="padding:8px 12px;">€ <?php echo number_format(floatval($spesa['importo'] ?? 0), 2, ',', '.'); ?></td>
						<td style="padding:8px 12px; color:#666;"><?php echo esc_html($spesa['nota'] ?? ''); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

</div>
