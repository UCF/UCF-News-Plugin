<?php
/**
 * Gutenberg block support for UCF News.
 *
 * @since 4.0.0
 */

if ( ! class_exists( 'UCF_News_Block' ) ) {
	class UCF_News_Block {
		// Block registration.

		/**
		 * Registers block assets and the dynamic UCF News Feed block.
		 */
		public static function register() {
			global $wp_version;

			// Metadata-based block registration is only enabled on WordPress 5.8+.
			// Older sites keep all existing shortcode/widget functionality unchanged.
			if ( ! function_exists( 'register_block_type' ) || version_compare( $wp_version, '5.8', '<' ) ) {
				return;
			}

			$editor_script_path = UCF_NEWS__PLUGIN_DIR . 'blocks/ucf-news-feed/editor.js';
			$style_path         = UCF_NEWS__PLUGIN_DIR . 'blocks/ucf-news-feed/style.css';
			$editor_style_path  = UCF_NEWS__PLUGIN_DIR . 'blocks/ucf-news-feed/editor.css';

			wp_register_script(
				'ucf-news-feed-block-editor',
				UCF_NEWS__PLUGIN_URL . '/blocks/ucf-news-feed/editor.js',
				array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-html-entities', 'wp-i18n', 'wp-server-side-render' ),
				file_exists( $editor_script_path ) ? filemtime( $editor_script_path ) : '4.0.0',
				true
			);

			wp_add_inline_script(
				'ucf-news-feed-block-editor',
				'window.ucfNewsBlock = ' . wp_json_encode(
					array(
						'feedUrl' => trailingslashit( get_option( 'ucf_news_feed_url', UCF_News_Config::$default_plugin_options['ucf_news_feed_url'] ) ),
					)
				) . ';',
				'before'
			);

			wp_register_style(
				'ucf-news-feed-block',
				UCF_NEWS__PLUGIN_URL . '/blocks/ucf-news-feed/style.css',
				array(),
				file_exists( $style_path ) ? filemtime( $style_path ) : '4.0.0'
			);

			wp_register_style(
				'ucf-news-feed-block-editor',
				UCF_NEWS__PLUGIN_URL . '/blocks/ucf-news-feed/editor.css',
				array( 'ucf-news-feed-block' ),
				file_exists( $editor_style_path ) ? filemtime( $editor_style_path ) : '4.0.0'
			);

			register_block_type(
				UCF_NEWS__PLUGIN_DIR . 'blocks/ucf-news-feed',
				array(
					'render_callback' => array( 'UCF_News_Block', 'render' ),
				)
			);
		}



		// Shared story data.

		/**
		 * Normalizes a UCF News API story for block rendering.
		 *
		 * @param object $item Feed item returned by UCF News.
		 * @return array Normalized story data.
		 */
		private static function normalize_story( $item ) {
			$image   = UCF_News_Common::get_story_image_or_fallback( $item, true );
			$section = UCF_News_Common::get_story_primary_section( $item );

			return array(
				'id'      => isset( $item->id ) ? absint( $item->id ) : 0,
				'title'   => isset( $item->title->rendered ) ? html_entity_decode( wp_strip_all_tags( $item->title->rendered ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) : '',
				'link'    => isset( $item->link ) ? esc_url_raw( $item->link ) : '',
				'image'   => ! empty( $image[0] ) ? esc_url_raw( $image[0] ) : '',
				'width'   => ! empty( $image[1] ) ? absint( $image[1] ) : 0,
				'height'  => ! empty( $image[2] ) ? absint( $image[2] ) : 0,
				'excerpt' => isset( $item->excerpt->rendered ) ? wp_trim_words( wp_specialchars_decode( wp_strip_all_tags( $item->excerpt->rendered ), ENT_QUOTES ), 25 ) : '',
				'date'    => isset( $item->date ) ? date_i18n( 'M d', strtotime( $item->date ) ) : '',
				'section' => $section && isset( $section->name ) ? wp_specialchars_decode( wp_strip_all_tags( $section->name ), ENT_QUOTES ) : '',
			);
		}

		// Frontend rendering.

		/**
		 * Renders the UCF News Feed block on the frontend.
		 *
		 * @param array $attributes Block attributes.
		 * @return string Rendered block markup or an empty string when no stories are available.
		 */
		public static function render( $attributes ) {
			$limit = isset( $attributes['limit'] ) ? absint( $attributes['limit'] ) : 6;
			$limit = $limit > 0 ? $limit : 6;

			$args = array(
				'sections' => isset( $attributes['sections'] ) ? $attributes['sections'] : null,
				'topics'   => isset( $attributes['topics'] ) ? $attributes['topics'] : null,
				'offset'   => 0,
				'limit'    => $limit,
			);

			$items = UCF_News_Feed::get_news_items( $args );

			if ( false === $items || null === $items ) {
				return '';
			}

			if ( ! is_array( $items ) ) {
				$items = array( $items );
			}

			if ( empty( $items ) ) {
				return '';
			}

			$title      = isset( $attributes['title'] ) ? $attributes['title'] : 'News';
			$class_name = isset( $attributes['className'] ) ? $attributes['className'] : '';
			$style      = 'classic';

			if ( false !== strpos( $class_name, 'is-style-modern' ) ) {
				$style = 'modern';
			} elseif ( false !== strpos( $class_name, 'is-style-card' ) ) {
				$style = 'card';
			}

			$layouts = array(
				'classic' => 'ucf-news-block-classic.php',
				'modern'  => 'ucf-news-block-modern.php',
				'card'    => 'ucf-news-block-card.php',
			);
			$layout  = UCF_NEWS__PLUGIN_DIR . 'layouts/' . $layouts[ $style ];

			if ( ! file_exists( $layout ) ) {
				return '';
			}

			$stories = array_map(
				function( $item ) {
					return self::normalize_story( $item );
				},
				$items
			);

			$wrapper_attributes = function_exists( 'get_block_wrapper_attributes' )
				? get_block_wrapper_attributes()
				: 'class="wp-block-ucf-news-news-feed"';

			ob_start();
			include $layout;
			return trim( ob_get_clean() );
		}

	}
}
