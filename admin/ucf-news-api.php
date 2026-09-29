<?php
/**
 * Handles registering admin routes
 **/

if ( ! class_exists( 'UCF_News_Admin_API' ) ) {
	class UCF_News_Admin_API {
		/**
		 * Outputs taxonomy results for the legacy suggest fields or block editor.
		 *
		 * The default newline-delimited response is preserved for backwards
		 * compatibility. Passing format=json returns name/slug pairs for Gutenberg.
		 *
		 * @param array|false $results Taxonomy results returned by UCF News.
		 */
		private static function output_taxonomy_results( $results ) {
			$results = is_array( $results ) ? $results : array();
			$format  = isset( $_GET['format'] ) ? sanitize_key( wp_unslash( $_GET['format'] ) ) : '';

			if ( 'json' === $format ) {
				$data = array_map(
					function( $result ) {
						return array(
							'name' => isset( $result->name ) ? wp_specialchars_decode( wp_strip_all_tags( $result->name ), ENT_QUOTES ) : '',
							'slug' => isset( $result->slug ) ? sanitize_title( $result->slug ) : '',
						);
					},
					$results
				);

				wp_send_json( array_values( array_filter( $data, function( $item ) { return ! empty( $item['slug'] ); } ) ) );
			}

			foreach ( $results as $result ) {
				if ( isset( $result->slug ) ) {
					echo esc_html( $result->slug ) . "\n";
				}
			}
			die();
		}

		public static function ajax_get_sections() {
			$search  = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
			$results = UCF_News_Feed::get_sections( $search );
			self::output_taxonomy_results( $results );
		}

		public static function ajax_get_topics() {
			$search  = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
			$results = UCF_News_Feed::get_topics( $search );
			self::output_taxonomy_results( $results );
		}
	}
}

add_action( 'wp_ajax_ucf-news-sections', array( 'UCF_News_Admin_API', 'ajax_get_sections' ) );
add_action( 'wp_ajax_ucf-news-topics', array( 'UCF_News_Admin_API', 'ajax_get_topics' ) );

?>
