<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Funzione Haversine per distanza in linea d'aria (km) tra due coordinate
function td_haversine($lat1, $lng1, $lat2, $lng2) {
	$R = 6371;
	$dLat = deg2rad($lat2 - $lat1);
	$dLng = deg2rad($lng2 - $lng1);
	$a = sin($dLat/2) * sin($dLat/2) +
	     cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
	     sin($dLng/2) * sin($dLng/2);
	return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

$trip_id   = get_the_ID();
$entries   = get_field(Travel_Diary_Cpt_Trip::FIELD_PREFIX . 'entry_of_trip', $trip_id);
$map_data  = array();

// Aggregazione dati da tutte le tappe
$km_totali_reali   = 0;
$km_totali_stimati = 0;
$costo_totale      = 0;
$costi_per_cat     = array();
$prev_lat = null; $prev_lng = null;

if ($entries && is_array($entries)) {
	foreach ($entries as $entry_id) {
		$entry = get_post($entry_id);
		if (!$entry || $entry->post_status !== 'publish') continue;

		$map        = get_field('field_entry_posizione', $entry_id);
		$km_reali   = get_field('field_entry_km_reali', $entry_id);
		$costi      = get_field('field_entry_costi', $entry_id);

		// Coordinate per la mappa
		if ($map && isset($map['lat']) && isset($map['lng'])) {
			$lat = floatval($map['lat']);
			$lng = floatval($map['lng']);
			$map_data[] = array('lat' => $lat, 'lng' => $lng, 'title' => $entry->post_title, 'url' => get_permalink($entry_id));

			// Km stimati (Haversine rispetto alla tappa precedente)
			if ($prev_lat !== null) {
				$km_totali_stimati += td_haversine($prev_lat, $prev_lng, $lat, $lng);
			}
			$prev_lat = $lat; $prev_lng = $lng;
		}

		// Km reali inseriti manualmente
		if (!empty($km_reali)) {
			$km_totali_reali += floatval($km_reali);
		}

		// Spese
		if ($costi && is_array($costi)) {
			foreach ($costi as $spesa) {
				$importo = floatval($spesa['importo'] ?? 0);
				$cat     = $spesa['categoria'] ?? 'varie';
				$costo_totale += $importo;
				$costi_per_cat[$cat] = ($costi_per_cat[$cat] ?? 0) + $importo;
			}
		}
	}
}
?>

<div class="travel-diary-trip-container" style="margin-top:40px; padding-top:20px; border-top:2px solid #eee;">

	<!-- Statistiche aggregate -->
	<?php if ($entries && count($entries) > 0) : ?>
	<div class="td-trip-stats" style="display:flex; flex-wrap:wrap; gap:16px; margin-bottom:28px;">

		<?php if ($km_totali_reali > 0) : ?>
		<div style="flex:1; min-width:160px; background:#f0f7ff; border-radius:8px; padding:16px; text-align:center;">
			<div style="font-size:1.8em;">🛣️</div>
			<div style="font-size:1.4em; font-weight:700;"><?php echo number_format($km_totali_reali, 0, ',', '.'); ?> km</div>
			<div style="color:#666; font-size:0.85em;">Km percorsi (reali)</div>
		</div>
		<?php endif; ?>

		<?php if ($km_totali_stimati > 0) : ?>
		<div style="flex:1; min-width:160px; background:#f5f5f5; border-radius:8px; padding:16px; text-align:center;">
			<div style="font-size:1.8em;">📐</div>
			<div style="font-size:1.4em; font-weight:700;"><?php echo number_format($km_totali_stimati, 0, ',', '.'); ?> km</div>
			<div style="color:#666; font-size:0.85em;">Km stimati (in linea d'aria)</div>
		</div>
		<?php endif; ?>

		<?php if ($costo_totale > 0) : ?>
		<div style="flex:1; min-width:160px; background:#fff8e1; border-radius:8px; padding:16px; text-align:center;">
			<div style="font-size:1.8em;">💶</div>
			<div style="font-size:1.4em; font-weight:700;">€ <?php echo number_format($costo_totale, 2, ',', '.'); ?></div>
			<div style="color:#666; font-size:0.85em;">Spesa totale</div>
		</div>
		<?php endif; ?>

		<div style="flex:1; min-width:160px; background:#f0fff4; border-radius:8px; padding:16px; text-align:center;">
			<div style="font-size:1.8em;">📍</div>
			<div style="font-size:1.4em; font-weight:700;"><?php echo count($entries); ?></div>
			<div style="color:#666; font-size:0.85em;">Tappe</div>
		</div>

	</div>

	<!-- Dettaglio spese per categoria -->
	<?php if (!empty($costi_per_cat)) : ?>
		<div style="background:#fafafa; border:1px solid #eee; border-radius:8px; padding:16px; margin-bottom:28px;">
			<h4 style="margin:0 0 12px 0;">Riepilogo Spese per Categoria</h4>
			<div style="display:flex; flex-wrap:wrap; gap:10px;">
				<?php
				$cat_labels = array(
					'trasporto' => '🚗 Trasporto', 'alloggio' => '🏨 Alloggio',
					'cibo' => '🍽️ Cibo', 'esperienze' => '🎭 Esperienze',
					'shopping' => '🛍️ Shopping', 'varie' => '💸 Varie',
				);
				foreach ($costi_per_cat as $cat => $tot) :
					$label = $cat_labels[$cat] ?? $cat;
				?>
				<div style="background:#fff; border:1px solid #ddd; border-radius:6px; padding:8px 14px; font-size:0.9em;">
					<strong><?php echo esc_html($label); ?></strong> — € <?php echo number_format($tot, 2, ',', '.'); ?>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php endif; ?>

	<!-- Mappa aggregata -->
	<?php if (!empty($map_data)) : ?>
		<h3 style="margin-bottom:15px;">🗺️ Mappa del Percorso</h3>
		<div id="td-trip-map" style="width:100%; height:420px; background:#eaebec; border-radius:8px; margin-bottom:28px;"></div>
		<script>var tdTripMapData = <?php echo json_encode($map_data); ?>;</script>
	<?php endif; ?>

	<!-- Lista tappe -->
	<?php if ($entries && count($entries) > 0) : ?>
		<h3 style="margin-bottom:15px;">🏁 Le Tappe del Viaggio</h3>
		<div style="display:flex; flex-direction:column; gap:14px;">
		<?php
		$count = 1;
		foreach ($entries as $entry_id) :
			$entry    = get_post($entry_id);
			if (!$entry || $entry->post_status !== 'publish') continue;
			$date     = get_field('field_entry_arrivo', $entry_id);
			$mezzo    = get_field('field_entry_mezzo_trasporto', $entry_id);
			$meteo    = get_field('field_entry_meteo', $entry_id);
			$valutaz  = get_field('field_entry_valutazione', $entry_id);
			$km_r     = get_field('field_entry_km_reali', $entry_id);
			$excerpt  = has_excerpt($entry_id) ? get_the_excerpt($entry_id) : wp_trim_words(strip_tags(get_post_field('post_content', $entry_id)), 20);
		?>
		<a href="<?php echo esc_url(get_permalink($entry_id)); ?>" style="display:block; padding:16px 20px; background:#fff; border:1px solid #ddd; border-radius:8px; text-decoration:none; color:inherit;">
			<div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px;">
				<h4 style="margin:0; color:#0073aa;"><?php echo $count; ?>. <?php echo esc_html($entry->post_title); ?></h4>
				<span style="font-size:0.85em; color:#888; display:flex; gap:10px; flex-wrap:wrap;">
					<?php if ($date)    echo '<span>📅 ' . esc_html($date) . '</span>'; ?>
					<?php if ($mezzo)   echo '<span>' . esc_html($mezzo) . '</span>'; ?>
					<?php if ($meteo)   echo '<span>' . esc_html($meteo) . '</span>'; ?>
					<?php if ($valutaz) echo '<span>' . esc_html($valutaz) . '</span>'; ?>
					<?php if ($km_r)    echo '<span>📍 ' . esc_html($km_r) . ' km</span>'; ?>
				</span>
			</div>
			<?php if ($excerpt) : ?>
				<p style="margin:10px 0 0 0; font-size:0.9em; color:#555;"><?php echo esc_html($excerpt); ?></p>
			<?php endif; ?>
		</a>
		<?php $count++; endforeach; ?>
		</div>
	<?php else : ?>
		<p>Nessuna tappa aggiunta a questo viaggio.</p>
	<?php endif; ?>

</div>
