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

		public static function format_tax_arg( $terms, $tax ) {
			$terms_filtered = is_array( $terms ) ? array_filter( $terms ) : null;

			return $terms_filtered;
		}

		public static function non_empty_allow_zero( $arg ) {
			return !(
				is_array( $arg ) && empty( $arg )
				|| is_null( $arg )
				|| is_string( $arg ) && empty( $arg )
			);
		}

		public static function get_news_items( $args ) {
			$url_override = get_option( 'ucf_news_feed_url' );
			$custom_url   = false;

			if ( ! empty( $args['feed_url'] ) ) {
				$url_override = $args['feed_url'];
				$custom_url = true;
			}

			$args = array(
				'url'        => $url_override ? $url_override : UCF_News_Config::$default_plugin_options['ucf_news_feed_url'],
				'limit'      => isset( $args['limit'] ) ? (int) $args['limit'] : 3,
				'offset'     => isset( $args['offset'] ) ? (int) $args['offset'] : 0,
				'categories' => isset( $args['sections'] ) ? array_map( 'trim', explode( ',', $args['sections'] ) ) : null,
				'tags'       => isset( $args['topics'] ) ? array_map( 'trim', explode( ',', $args['topics'] ) ) : null,
			);

			// Empty array of indexes with no value.
			$args = array_filter( $args, array( 'UCF_News_Feed', 'non_empty_allow_zero' ) );

			// Set up query params.
			$categories = $tags = array();

			if ( isset( $args['categories'] ) ) {
				$categories = self::format_tax_arg( $args['categories'], 'category_slugs' );
			}
			if ( isset( $args['tags'] ) ) {
				$tags = self::format_tax_arg( $args['tags'], 'tag_slugs' );
			}

			$query = urldecode( http_build_query( array(
				'per_page'       => $args['limit'],
				'offset'         => $args['offset'],
				'category_slugs' => $categories,
				'tag_slugs'      => $tags,
				'_embed'         => true
			) ) );

			// Fetch feed
			$feed_url = $args['url'];

			if ( $custom_url === false ) {
				$feed_url .= 'posts';
			}

			$feed_url .=  '?' . $query;

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
