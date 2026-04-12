<?php
/**
 * Gestione centralizzata delle Icone SVG in stile WP
 */
class Travel_Diary_Icons {

	/**
	 * Restituisce il markup SVG per l'icona richiesta.
	 * Utilizza principalmente SVGs in stile outline (es. base Lucide/Phosphor)
	 * che si integrano bene con l'interfaccia di WordPress.
	 *
	 * @param string $icon_name Nome dell'icona (es. 'auto', 'sole', 'cibo').
	 * @param array  $args      Attributi extra (es. classi css, dimensioni).
	 * @return string Markup SVG.
	 */
	public static function get( string $icon_name, array $args = array() ): string {
		
		$defaults = array(
			'class'  => 'td-icon td-icon-' . esc_attr( $icon_name ),
			'width'  => '24',
			'height' => '24',
			'stroke' => 'currentColor',
			'fill'   => 'none',
		);
		$args = wp_parse_args( $args, $defaults );

		$svg_meta = sprintf(
			'xmlns="http://www.w3.org/2000/svg" width="%s" height="%s" viewBox="0 0 24 24" fill="%s" stroke="%s" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="%s"',
			esc_attr( $args['width'] ),
			esc_attr( $args['height'] ),
			esc_attr( $args['fill'] ),
			esc_attr( $args['stroke'] ),
			esc_attr( $args['class'] )
		);

		$paths = self::get_paths();

		// Se l'icona non esiste, ritorna un fallback vuoto o un'icona generica (cerchio)
		$path = $paths[ $icon_name ] ?? '<circle cx="12" cy="12" r="10"></circle>';

		return sprintf( '<svg %s>%s</svg>', $svg_meta, $path );
	}

	/**
	 * Mappa dei percorsi (paths/shapes) SVG.
	 *
	 * @return array
	 */
	private static function get_paths(): array {
		return array(
			// ─── UI & METRICHE ──────────────────────────────────────────────────
			'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
			'map-pin'  => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
			'star'     => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
			'user'     => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',

			// ─── MEZZI DI TRASPORTO ─────────────────────────────────────────────
			'auto'       => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>',
			'moto'       => '<circle cx="7" cy="17" r="3"/><circle cx="17" cy="17" r="3"/><path d="M10 14h6"/><path d="m5 17 2-6h3"/><path d="M2 14v-2c0-.6.4-1 1-1h1l3-4h3l3 4h3l2-2"/><path d="M12 9v5"/>', // approximated generic bike/moto
			'treno'      => '<rect width="16" height="16" x="4" y="3" rx="2"/><path d="M4 11h16"/><path d="M12 3v8"/><path d="m8 19-2 3"/><path d="m16 19 2 3"/><path d="M2 22h20"/><path d="M8 15h0"/><path d="M16 15h0"/>',
			'aereo'      => '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.2-1.1.6L3 8l6 4-6 6L2 19l4 2 6-1 4 6 .8-.8c.4-.2.7-.6.6-1.1z"/>',
			'nave'       => '<path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1 .6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 10v4"/>',
			'autobus'    => '<path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/>',
			'bicicletta' => '<circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-3 11.5V14l-3-3 4-3 2 3h2"/><path d="m10.5 13.5-3-3"/><path d="M18.5 14 15 6"/><path d="M5.5 14 9 6h2"/><path d="M7 6h3.5"/>',
			'piedi'      => '<path d="m13 14 1 7"/><path d="M13 14v-4l-3-1M10 9l2 3"/><path d="M13 10h4l2 3"/><circle cx="12" cy="4" r="1.5"/><path d="m9 21-1-4-3-1"/>',
			'altro'      => '<path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8"/><path d="m21 3-6 6"/><path d="m3 3 6 6"/>',
			'trasporto'  => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>', // alias auto

			// ─── METEO ──────────────────────────────────────────────────────────
			'sole'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
			'nuvoloso'  => '<path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M22 10a3 3 0 0 0-3-3h-2.207a5.502 5.502 0 0 0-10.702.5"/>', // semi-nuvoloso
			'coperto'   => '<path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/>', // cloud
			'pioggia'   => '<path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M16 23v-6"/><path d="M8 23v-6"/><path d="M12 23v-6"/>',
			'temporale' => '<path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="m13 19-3 3h4l-3 3"/>',
			'neve'      => '<path d="m20 17.5-6.5-6.5M20 6.5 13.5 13M4 17.5 10.5 11M4 6.5 10.5 13M12 2v20M2 12h20"/><path d="m15.5 10.5 2-2M15.5 13.5l2 2M8.5 10.5l-2-2M8.5 13.5l-2 2M10.5 15.5l-2 2M13.5 15.5l2 2M10.5 8.5l-2-2M13.5 8.5l2-2"/>',
			'vento'     => '<path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/><path d="M12.6 19.4A2 2 0 1 0 14 16H2"/>',
			'nebbia'    => '<path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/><path d="M4 22h16"/><path d="M4 19h16"/>',

			// ─── COSTI & SPESE ──────────────────────────────────────────────────
			'euro'       => '<path d="M4 10h12"/><path d="M4 14h9"/><path d="M19 6a7.7 7.7 0 0 0-5.2-2A7.9 7.9 0 0 0 6 12c0 4.4 3.5 8 7.8 8 2 0 3.8-.8 5.2-2"/>',
			'alloggio'   => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>', // Bed / Home
			'cibo'       => '<path d="M3 2v7c0 2.2 1.8 4 4 4h0c2.2 0 4-1.8 4-4V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>', // Utensils
			'esperienze' => '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>', // Briefcase -> Maybe 'ticket' is better. Using ticket:
			// Let's replace esperienze with a ticket icon
			// 'esperienze' => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><line x1="13" x2="13" y1="5" y2="19"/>',
			'shopping'   => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>', // Shopping Cart
			'varie'      => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>', // Info
		);
	}
}
