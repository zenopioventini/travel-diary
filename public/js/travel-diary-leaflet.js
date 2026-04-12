/**
 * Travel Diary - Leaflet.js Interactive Map Engine
 */
(function($) {
	$(document).ready(function() {
		
		var mapContainerId = '';
		var isTripMap = false;

		if ($('#td-trip-map').length) {
			mapContainerId = 'td-trip-map';
			isTripMap = true;
		} else if ($('#td-entry-map').length) {
			mapContainerId = 'td-entry-map';
		}

		// Se non c'è un contenitore valido o tdTripMapData non è definito, uscita.
		if (!mapContainerId || typeof tdTripMapData === 'undefined' || tdTripMapData.length === 0) {
			return;
		}

		// 1. Inizializzazione della mappa Base (Centrata provvisoriamente a 0,0)
		var map = L.map(mapContainerId, {
			scrollWheelZoom: false // Evita blocchi dello scrolling pagina accidentali
		}).setView([0, 0], 2);

		// 2. Livello Cartografico (Tile Layer) - CartoDB Dark Matter per un look scuro elegante
		L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
			subdomains: 'abcd',
			maxZoom: 19
		}).addTo(map);

		// 3. Creazione del Cluster Group per ammassare i marker (fondamentale per moli di foto)
		var markers = L.markerClusterGroup({
			maxClusterRadius: 50,
			spiderfyOnMaxZoom: true,
			showCoverageOnHover: false,
			zoomToBoundsOnClick: true,
			iconCreateFunction: function(cluster) {
				var count = cluster.getChildCount();
				var size = count < 10 ? 'small' : count < 50 ? 'medium' : 'large';
				return new L.DivIcon({ 
					html: '<div><span>' + count + '</span></div>', 
					className: 'td-cluster td-cluster-' + size, 
					iconSize: new L.Point(40, 40) 
				});
			}
		});

		var bounds = new L.LatLngBounds();
		var pathCoords = []; // Array per disegnare la linea dell'itinerario

		// 4. Popolamento dei dati
		tdTripMapData.forEach(function(point) {
			if (!point.lat || !point.lng) return;

			var latLng = new L.LatLng(point.lat, point.lng);
			bounds.extend(latLng);

			// Riconoscimento del tipo di marker
			var isPhoto = (point.type === 'photo');
			var isPoi   = (point.type === 'poi');
			var isEntry = !isPhoto && !isPoi;

			// Solo le tappe primarie concorrono alla linea tracciante
			if (isEntry && isTripMap) {
				pathCoords.push(latLng);
			}

			// Custom Icon Engine
			var customIconHtml = '';
			var iconClass = '';

			if (isPhoto) {
				if (point.thumb) {
					customIconHtml = '<img src="' + point.thumb + '" alt="Photo" />';
					iconClass = 'td-marker-photo td-marker-has-img';
				} else {
					customIconHtml = '<span class="dashicons dashicons-camera"></span>';
					iconClass = 'td-marker-photo td-marker-icon-only';
				}
			} else if (isPoi) {
				customIconHtml = '<span class="dashicons dashicons-star-filled"></span>';
				iconClass = 'td-marker-poi';
			} else { // Entry / Tappa principale
				customIconHtml = '<span class="dashicons dashicons-location"></span>';
				iconClass = 'td-marker-entry';
			}

			var icon = L.divIcon({
				html: customIconHtml,
				className: 'td-custom-div-icon ' + iconClass,
				iconSize: [36, 36],
				iconAnchor: [18, 36],
				popupAnchor: [0, -36]
			});

			var marker = L.marker(latLng, { icon: icon });

			// Costruzione dinamica del Popup
			var popupHtml = '<div class="td-map-popup">';

			if (isPhoto && point.thumb) {
				popupHtml += '<img src="' + point.thumb + '" alt="Thumbnail" style="width:100%; height:auto; border-radius:4px; margin-bottom:8px;" />';
			}

			if (isPoi) {
				// Immagine del POI (se disponibile)
				if (point.thumb) {
					popupHtml += '<img src="' + point.thumb + '" alt="" style="width:100%; height:80px; object-fit:cover; border-radius:4px; margin-bottom:8px;" />';
				}
				// Badge categoria con emoji
				var catLabels = {
					'cultura':   '\uD83C\uDFDB\uFE0F Cultura & Musei',
					'cibo':      '\uD83C\uDF7D\uFE0F Cibo & Ristorazione',
					'natura':    '\uD83C\uDF32 Natura & Parchi',
					'viewpoint': '\uD83D\uDCF8 Punto Panoramico',
					'relax':     '\uD83D\uDC86 Relax',
					'alloggio':  '\uD83C\uDFE8 Alloggio'
				};
				var catLabel = point.cat ? (catLabels[point.cat] || point.cat) : 'Punto di Interesse';
				popupHtml += '<span style="font-size:10px; text-transform:uppercase; letter-spacing:1px; color:#d4943a; display:block; margin-bottom:4px;">' + catLabel + '</span>';
			}

			popupHtml += '<strong style="display:block; margin-bottom:4px; color:#1a1a1a;">' + point.title + '</strong>';
			if (point.url) {
				popupHtml += '<a href="' + point.url + '" style="color:#0073aa; text-decoration:none;">Vedi dettaglio \u2192</a>';
			}
			popupHtml += '</div>';

			marker.bindPopup(popupHtml);
			markers.addLayer(marker);
		});

		// Aggiunge tutte le foto e tappe raggruppate sulla mappa
		map.addLayer(markers);

		// 5. Tracciare la rotta (Polyline) fra le tappe del Viaggio
		if (isTripMap && pathCoords.length > 1) {
			var routeLine = L.polyline(pathCoords, {
				color: '#d4943a',
				weight: 3,
				opacity: 0.8,
				dashArray: '10, 10', // Linea tratteggiata per stile viaggio aereo/on-the-road
				lineJoin: 'round'
			}).addTo(map);
		}

		// 6. Inquadratura finale
		if (tdTripMapData.length > 0) {
			map.fitBounds(bounds, { padding: [30, 30] });
		}

	});
})(jQuery);
