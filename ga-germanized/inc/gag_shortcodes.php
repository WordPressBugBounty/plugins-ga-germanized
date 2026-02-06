<?php
/* Security-Check */
if ( !class_exists('WP') ) {
	die();
}

if( ! class_exists('gag_shortcodes') ):
	class gag_shortcodes
	{
		public static function init()
		{
			add_action(
				'wp_enqueue_scripts',
				array(
					__CLASS__,
					'ga_optout_scripts'
				)
			);

			add_shortcode(
				'ga-optout',
				array(
					__CLASS__,
					'ga_optout'
				)
			);
		}

		public static function ga_optout_scripts()
		{
			$disable_ga_optout_scripts = apply_filters('gag-disable-ga-optout-scripts', false);

			if( $disable_ga_optout_scripts )
				return;

			$settings = gag_settings_handler::current_settings();

			wp_register_script(
				'google-analytics-germanized-gaoptout',
				plugins_url(dirname(PBGAG_BASE)).'/assets/js/gaoptout.js',
				array('jquery'),
				PBGAG_VERSION,
				true
			);

			wp_localize_script(
				'google-analytics-germanized-gaoptout',
				'gaoptoutSettings', array(
					'ua' => $settings['analytics-id'],
					'disabled' => esc_attr__('Google Analytics Opt-out Cookie was set!', 'ga-germanized')
				)
			);

			wp_enqueue_script('google-analytics-germanized-gaoptout');
		}

		public static function ga_optout( $atts ) {
			// Defaults
			$defaults = array(
				'text' => __( 'Disable Google Analytics', 'ga-germanized' ),
			);

			// Merge attributes with defaults; include shortcode tag for filters.
			$a = shortcode_atts( $defaults, $atts, 'ga-optout' );

			// Sanitize user-controllable input early
			// We treat "text" strictly as plain text label.
			$link_text = sanitize_text_field( $a['text'] ?? $defaults['text'] );

			// Fetch settings defensively
			$settings = is_array( gag_settings_handler::current_settings() ) ? gag_settings_handler::current_settings() : array();
			$analytics_id = isset( $settings['analytics-id'] ) ? (string) $settings['analytics-id'] : '';

			// (Optional) light validation of the analytics ID; if invalid, leave empty.
			// Accept common GA formats like "UA-XXXXXX-Y" or "G-XXXXXXXXXX".
			if ( $analytics_id !== '' ) {
				$is_valid_ga = preg_match( '/^(UA-\d{4,}-\d+|G-[A-Z0-9]{6,})$/i', $analytics_id ) === 1;
				if ( ! $is_valid_ga ) {
					$analytics_id = '';
				}
			}

			// Build safe HTML
			$html = sprintf(
				'<a href="#" data-ua="%1$s" class="gaoptout">%2$s</a>',
				esc_attr( $analytics_id ),
				esc_html( $link_text )
			);

			return $html;
		}
	}
endif;