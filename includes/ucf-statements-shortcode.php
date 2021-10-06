<?php

if ( ! class_exists( 'UCF_Statements_Shortcode' ) ) {
	class UCF_Statements_Shortcode {
		/**
		 * Registers the `ucf-statements shortcode.
		 * @author Jim Barnes
		 * @since 2.3.0
		 */
		public static function register_shortcode() {
			add_shortcode( 'ucf-statements', array( 'UCF_Statements_Shortcode', 'sc_ucf_statements' ) );
		}

		/**
		 * Adds the ucf-statements shortcode to the WP_SCIF_Config list
		 * of shortcodes.
		 * @author Jim Barnes
		 * @since 2.3.0
		 * @param array The list of registered shortcodes and fields.
		 * @return array
		 */
		public static function register_shortcode_interface( $registered_shortcodes ) {
			$fields = array(

			);

			$shortcode = array(
				'command' => 'ucf-statements',
				'name'    => 'UCF Statement List',
				'desc'    => 'Displays a feed of UCF Statements',
				'content' => false,
				'fields'  => $fields,
				'preview' => false,
				'group'   => 'UCF News'
			);

			$registered_shortcodes[] = $shortcode;
			return $registered_shortcodes;
		}

		/**
		 * Generates the `ucf-statements` markup
		 * @author Jim Barnes
		 * @since 2.3.0
		 * @param array $attr The parsed attribute array
		 * @param string $content Content passed into the shortcode
		 * @return string
		 */
		public static function sc_ucf_statements( $attr, $content='' ) {
			$feed_url = get_option(
				'ucf_news_feed_url',
				UCF_News_Config::$default_plugin_options['ucf_news_feed_url']
			);

			$attr = shortcode_atts( array(
				'title'  => 'Recent Statements',
				'layout' => 'classic',
				'offset' => 0,
				'limit'  => 3
			), $attr );

			$title  = $attr['title'];
			$layout = $attr['layout'];

			$args = array(
				'feed_url' => $feed_url,
				'offset'   => $attr['offset'] ? (int) $attr['offset'] : 0,
				'limit'    => $attr['limit'] ? (int) $attr['limit'] : 3
			);

			$items = UCF_News_Feed::get_statements( $args );

			ob_start();

			if ( $items !== null ) {
				echo UCF_News_Common::display_statements( $items, $layout, array_merge( $attr, $args ) );
			}

			return ob_get_clean();
		}
	}
}
