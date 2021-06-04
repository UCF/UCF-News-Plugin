<?php
/**
 * Handles all feed related code.
 **/

if ( ! class_exists( 'UCF_News_Feed' ) ) {
	class UCF_News_Feed {
		public static function get_json_feed( $feed_url ) {
			$http_timeout = get_option( 'ucf_news_http_timeout' );
			$http_timeout = $http_timeout ?: 5;

			$response = wp_remote_get( $feed_url, array( 'timeout' =>  $http_timeout) );

			if ( is_array( $response ) && wp_remote_retrieve_response_code( $response ) == 200 ) {
				$result = json_decode( wp_remote_retrieve_body( $response ) );
			}
			else {
				$result = false;
			}

			return $result;
		}

		public static function format_tax_arg( $terms, $tax=null ) {
			if ( is_string( $terms ) ) {
				$terms = array_map( 'trim', explode( ',', $terms ) );
			}

			if ( is_array( $terms ) ) {
				$terms = array_filter( $terms );
			}

			return is_array( $terms ) && ! empty( $terms ) ? $terms : null;
		}

		public static function non_empty_allow_zero( $arg ) {
			return !(
				is_array( $arg ) && empty( $arg )
				|| is_null( $arg )
				|| is_string( $arg ) && empty( $arg )
			);
		}

		public static function get_news_items( $args ) {
			$custom_feed_url  = isset( $args['feed_url'] ) && ! empty( $args['feed_url'] ) ? $args['feed_url'] : false;
			$default_feed_url = get_option( 'ucf_news_feed_url' ) ?: UCF_News_Config::$default_plugin_options['ucf_news_feed_url'];
			$feed_url         = $custom_feed_url ?: $default_feed_url;

			// Set up query params
			$query_args = array_filter( array(
				'per_page'       => isset( $args['limit'] ) ? (int) $args['limit'] : 3,
				'offset'         => isset( $args['offset'] ) ? (int) $args['offset'] : 0,
				'category_slugs' => isset( $args['sections'] ) ? self::format_tax_arg( $args['sections'] ) : null,
				'tag_slugs'      => isset( $args['topics'] ) ? self::format_tax_arg( $args['topics'] ) : null,
				'_embed'         => true
			), array( 'UCF_News_Feed', 'non_empty_allow_zero' ) );

			$query = urldecode( http_build_query( $query_args ) );

			// Fetch feed
			if ( $custom_feed_url === false ) {
				$feed_url .= 'posts';
			}

			if ( $query ) {
				$feed_url .= strpos( $feed_url, '?' ) !== false ? '&' : '?';
				$feed_url .= $query;
			}

			return self::get_json_feed( $feed_url );
		}

		public static function get_external_stories( $args ) {
			$params = array();

			if ( isset( $args['limit'] ) ) {
				$params['limit'] = $args['limit'];
			}

			if ( isset( $args['offset'] ) ) {
				$params['offset'] = $args['offset'];
			}

			$param_string = urldecode( http_build_query( $params ) );

			$feed_url = $args['feed_url'];

			$url = "$feed_url?$param_string";

			return self::get_json_feed( $url );
		}

		public static function get_sections( $search ) {
			$base_url = get_option( 'ucf_news_feed_url', UCF_News_Config::$default_plugin_options['ucf_news_feed_url'] );
			$url      = $base_url . 'categories/?search=' . $search;

			return self::get_json_feed( $url );
		}

		public static function get_topics( $search ) {
			$base_url = get_option( 'ucf_news_feed_url', UCF_News_Config::$default_plugin_options['ucf_news_feed_url'] );
			$url      = $base_url . 'tags/?search=' . $search;

			return self::get_json_feed( $url );
		}

		/**
		 * Returns statements from the statements wp-json feed
		 * @author Jim Barnes
		 * @since 2.3.0
		 * @param array The argument array
		 * @return array An array of statement items
		 */
		public static function get_statements( $args ) {
			$params = array();

			if ( isset( $args['limit'] ) ) {
				$params['per_page'] = $args['limit'];
			}

			if ( isset( $args['offset'] ) ) {
				$params['offset'] = $args['offset'];
			}

			$param_string = urldecode( http_build_query( $params ) );

			$feed_url = $args['feed_url'];

			$url = "{$feed_url}statements/";

			if ( $param_string && strlen( $param_string ) > 0 ) {
				$url .= "?$param_string";
			}

			return self::get_json_feed( $url );
		}
	}
}
?>
