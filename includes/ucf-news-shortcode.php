<?php
/**
 * Handles the registration of the UCF News Shortcode
 **/

if ( ! class_exists( 'UCF_News_Shortcode' ) ) {
	class UCF_News_Shortcode {
		public static function register_shortcode() {
			add_shortcode( 'ucf-news-feed', array( 'UCF_News_Shortcode', 'sc_ucf_news_feed' ) );
		}

		public static function register_shortcode_interface( $registered_shortcodes ) {
			if ( class_exists( 'WP_SCIF_Config' ) ) {
				$fields = array(
					array(
						'name'      => 'Title',
						'param'     => 'title',
						'desc'      => 'The title to display before the news feed',
						'type'      => 'text',
						'default'   => 'News'
					),
					array(
						'name'      => 'Layout',
						'param'     => 'layout',
						'desc'      => 'The layout to display the news items',
						'type'      => 'select',
						'options'   => UCF_News_Config::get_layouts(),
						'default'   => 'classic'
					),
					array(
						'name'      => 'Filter by Section',
						'param'     => 'sections',
						'desc'      => 'Allows you to filter the results by section (category). Enter one ore more comma-separated section slugs.',
						'type'      => 'text',
						'default'   => ''
					),
					array(
						'name'      => 'Filter by Topic',
						'param'     => 'topics',
						'desc'      => 'Allows you to filter the results by topic (tag). Enter one or more comma-separated topic slugs.',
						'type'      => 'text',
						'default'   => ''
					),
					array(
						'name'      => 'Filter by Search Query',
						'param'     => 'search',
						'desc'      => 'Allows you to filter the results via a search query.',
						'type'      => 'text',
						'default'   => ''
					),
					array(
						'name'      => 'Number of News Items',
						'param'     => 'limit',
						'desc'      => 'The number of news items to show',
						'type'      => 'number',
						'default'   => 3
					),
					array(
						'name'      => 'News Item Offset',
						'param'     => 'offset',
						'desc'      => 'The number of news items to skip in the feed. For example, set to 1 to skip the first article.',
						'type'      => 'number',
						'default'   => 0
					),
					array(
						'name'      => 'Number of News Items Per Row',
						'param'     => 'per_row',
						'desc'      => 'The number of news items to show per row (for card layout only)',
						'type'      => 'number',
						'default'   => 3
					),
					array(
						'name'      => 'Feed URL',
						'param'     => 'feed_url',
						'desc'      => 'Allows the base URL for the feed to be overidden.',
						'type'      => 'text',
						'default'   => ''
					)
				);
				$shortcode = array(
					'command' => 'ucf-news-feed',
					'name'    => 'UCF News Feed',
					'desc'    => 'Displays a feed of UCF News items.',
					'content' => false,
					'fields'  => $fields,
					'preview' => false,
					'group'   => 'UCF News'
				);

				$registered_shortcodes[] = $shortcode;
				return $registered_shortcodes;
			}
		}

		public static function sc_ucf_news_feed( $attr, $content='' ) {
			$attr = shortcode_atts( array(
				'title'      => 'News',
				'layout'     => 'classic',
				'sections'   => '',
				'topics'     => '',
				'search'     => '',
				'offset'     => 0,
				'limit'      => 3,
				'per_row'    => 3,
				'show_image' => true,
				'feed_url'   => ''
			), $attr );

			$layout = $attr['layout'];

			$args = array(
				'feed_url' => $attr['feed_url'],
				'sections' => $attr['sections'] ?: null,
				'topics'   => $attr['topics'] ?: null,
				'search'   => $attr['search'] ?: null,
				'offset'   => $attr['offset'] ? (int) $attr['offset'] : 0,
				'limit'    => $attr['limit'] ? (int) $attr['limit'] : 3
			);

			$items = UCF_News_Feed::get_news_items( $args );

			ob_start();

			if ( $items !== null ) {
				echo UCF_News_Common::display_news_items( $items, $layout, array_merge( $attr, $args ), 'default', $content );
			}

			return ob_get_clean();
		}
	}
}
