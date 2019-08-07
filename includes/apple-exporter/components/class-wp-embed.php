<?php
/**
 * Publish to Apple News Includes: Apple_Exporter\Components\WP_Embed class
 *
 * Contains a class which is used to transform WP_Embed embeds into Apple News format.
 *
 * @package Apple_News
 * @subpackage Apple_Exporter
 * @since 0.2.0
 */

namespace Apple_Exporter\Components;

/**
 * A class to transform an WP_Embed embed into an WP_Embed Apple News component.
 *
 * @since 0.2.0
 */
class WP_Embed extends Component {

	/**
	 * Look for node matches for this component.
	 *
	 * @param \DOMElement $node The node to examine for matches.
	 *
	 * @access public
	 * @return \DOMElement|null The node on success, or null on no match.
	 */
	public static function node_matches( $node ) {

		// Match the src attribute against a flickr regex
		if (
			'figure' === $node->nodeName && self::node_has_class( $node, 'is-type-wp-embed' )
		) {
			return $node;
		}

		return null;
	}

	/**
	 * Register all specs for the component.
	 *
	 * @access public
	 */
	public function register_specs() {
		$this->register_spec(
			'wp-embed-json',
			__( 'WP_Embed JSON', 'apple-news' ),
			array(
				'role'       => 'container',
				'components' => '#components#',
			)
		);
	}

	/**
	 * Build the component.
	 *
	 * @param string $html The HTML to parse into text for processing.
	 *
	 * @access protected
	 */
	protected function build( $html ) {

		$caption = '';
		$title   = '';
		$url     = '';

		// If we have a url, parse.
		if ( preg_match( '#https?://[^"]+#', $html, $url_matches ) ) {
			$url = $url_matches[0];

			// If the url has a host, set as caption.
			if ( $url && isset( wp_parse_url( $url )['host'] ) ) {
				$parsed_url = wp_parse_url( $url );
				$caption = '<a href="' . esc_url( $url ) . '">' . sprintf( esc_html__( 'View on %s.', 'apple-news' ), esc_html( $parsed_url['host'] ) ) . '</a>';

				if ( preg_match( '#<\s*?a href\b[^>]*>(.*?)</a\b[^>]*>#s', $html, $title_matches ) ) {
					$title = $title_matches[1];
				}
			}
		}

		$registration_array = [
			'#components#' => [
				[
					'role' => 'heading2',
					'text' => $title,
				],
				[
					'role'   => 'body',
					'text'   => $caption,
					'format' => 'html',
					'textStyle' => [
						'fontSize' => 14,
					]
				],
			],
		];

		$this->register_json(
			'wp-embed-json',
			$registration_array
		);
	}
}
