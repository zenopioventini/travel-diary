(function( $ ) {
	'use strict';
	$(function() {
		// Eventuali listener addizionali DOM
	});
})( jQuery );

function initTravelDiaryMap() {
	if (typeof tdTripMapData !== 'undefined' && tdTripMapData.length > 0) {
		var mapContainer = document.getElementById('td-trip-map') || document.getElementById('td-entry-map');
		if (mapContainer) {
			var bounds = new google.maps.LatLngBounds();
			var map = new google.maps.Map(mapContainer, {
				zoom: 4,
				center: {lat: parseFloat(tdTripMapData[0].lat), lng: parseFloat(tdTripMapData[0].lng)},
				mapTypeId: google.maps.MapTypeId.ROADMAP
			});
			
			var pathCoordinates = [];

			tdTripMapData.forEach(function(markerData) {
				var position = new google.maps.LatLng(parseFloat(markerData.lat), parseFloat(markerData.lng));
				bounds.extend(position);
				pathCoordinates.push(position);
				
				var marker = new google.maps.Marker({
					position: position,
					map: map,
					title: markerData.title
				});

				if (markerData.url && markerData.url !== '') {
					marker.addListener('click', function() {
						window.location.href = markerData.url;
					});
				}
			});

			if (tdTripMapData.length > 1) {
				map.fitBounds(bounds);
				var flightPath = new google.maps.Polyline({
					path: pathCoordinates,
					geodesic: true,
					strokeColor: '#FF6b6b',
					strokeOpacity: 1.0,
					strokeWeight: 4
				});
				flightPath.setMap(map);
			} else if (tdTripMapData.length === 1) {
				map.setCenter(bounds.getCenter());
				map.setZoom(12);
			}
		}
	}
}
