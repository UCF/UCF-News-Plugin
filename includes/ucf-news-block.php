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

			// Metadata-based block registration requires WordPress 5.8+, because
			// register_block_type() only accepts a block.json path starting in 5.8.
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
				array( 'wp-api-fetch', 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n' ),
				file_exists( $editor_script_path ) ? filemtime( $editor_script_path ) : '3.1.0',
				true
			);

			wp_register_style(
				'ucf-news-feed-block',
				UCF_NEWS__PLUGIN_URL . '/blocks/ucf-news-feed/style.css',
				array(),
				file_exists( $style_path ) ? filemtime( $style_path ) : '3.1.0'
			);

			wp_register_style(
				'ucf-news-feed-block-editor',
				UCF_NEWS__PLUGIN_URL . '/blocks/ucf-news-feed/editor.css',
				array( 'ucf-news-feed-block' ),
				file_exists( $editor_style_path ) ? filemtime( $editor_style_path ) : '3.1.0'
			);

			register_block_type(
				UCF_NEWS__PLUGIN_DIR . 'blocks/ucf-news-feed',
				array(
					'render_callback' => array( 'UCF_News_Block', 'render' ),
				)
			);
		}


		// Editor REST API.

		/**
		 * Registers the editor data endpoint used by the client-side block preview.
		 *
		 * The public block remains dynamically rendered by PHP. This endpoint only
		 * exposes the existing feed data to authenticated editors.
		 */
		public static function register_rest_routes() {
			register_rest_route(
				'ucf-news/v1',
				'/stories',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( 'UCF_News_Block', 'rest_get_stories' ),
					'permission_callback' => function() {
						return current_user_can( 'edit_posts' );
					},
					'args'                => array(
						'limit' => array(
							'default'           => 6,
							'sanitize_callback' => 'absint',
							'validate_callback' => function( $value ) {
								$value = (int) $value;
								return $value >= 1 && $value <= 24;
							},
						),
						'sections' => array(
							'default'           => '',
							'sanitize_callback' => 'sanitize_text_field',
						),
						'topics' => array(
							'default'           => '',
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				)
			);
		}

		// Shared story data.

		/**
		 * Normalizes a UCF News API story for block rendering.
		 *
		 * Keeps editor REST responses and frontend rendering on the same
		 * presentation data without duplicating field cleanup.
		 *
		 * @param object $item Feed item returned by UCF News.
		 * @return array Normalized story data.
		 */
		private static function normalize_story( $item ) {
			$image   = UCF_News_Common::get_story_image_or_fallback( $item, true );
			$section = UCF_News_Common::get_story_primary_section( $item );

			return array(
				'id'      => isset( $item->id ) ? absint( $item->id ) : 0,
				'title'   => isset( $item->title->rendered ) ? wp_specialchars_decode( wp_strip_all_tags( $item->title->rendered ), ENT_QUOTES ) : '',
				'link'    => isset( $item->link ) ? esc_url_raw( $item->link ) : '',
				'image'   => ! empty( $image[0] ) ? esc_url_raw( $image[0] ) : '',
				'width'   => ! empty( $image[1] ) ? absint( $image[1] ) : 0,
				'height'  => ! empty( $image[2] ) ? absint( $image[2] ) : 0,
				'excerpt' => isset( $item->excerpt->rendered ) ? wp_trim_words( wp_specialchars_decode( wp_strip_all_tags( $item->excerpt->rendered ), ENT_QUOTES ), 25 ) : '',
				'date'    => isset( $item->date ) ? date_i18n( 'M d', strtotime( $item->date ) ) : '',
				'section' => $section && isset( $section->name ) ? wp_specialchars_decode( wp_strip_all_tags( $section->name ), ENT_QUOTES ) : '',
			);
		}

		/**
		 * Returns normalized story data for the Gutenberg editor preview.
		 *
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response|WP_Error
		 */
		public static function rest_get_stories( $request ) {
			$limit    = absint( $request->get_param( 'limit' ) );
			$sections = $request->get_param( 'sections' );
			$topics   = $request->get_param( 'topics' );

			$items = UCF_News_Feed::get_news_items(
				array(
					'sections' => $sections,
					'topics'   => $topics,
					'offset'   => 0,
					'limit'    => $limit ?: 6,
				)
			);

			if ( false === $items || null === $items ) {
				return new WP_Error(
					'ucf_news_feed_unavailable',
					__( 'UCF News stories could not be loaded. Please try again.', 'ucf-news' ),
					array( 'status' => 502 )
				);
			}

			if ( ! is_array( $items ) ) {
				$items = array( $items );
			}

			return rest_ensure_response( array_map( array( 'UCF_News_Block', 'normalize_story' ), $items ) );
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

			$wrapper_attributes = function_exists( 'get_block_wrapper_attributes' )
				? get_block_wrapper_attributes()
				: 'class="wp-block-ucf-news-news-feed"';

			ob_start();
			?>
			<div <?php echo $wrapper_attributes; ?>>
				<?php if ( $title ) : ?>
					<h2 class="ucf-news-feed-title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<ul class="ucf-news-feed-list">
					<?php foreach ( $items as $item ) :
						$story        = self::normalize_story( $item );
						$image_url    = $story['image'];
						$image_width  = $story['width'];
						$image_height = $story['height'];
						$item_link    = $story['link'];
						$item_title   = $story['title'];
						$excerpt      = $story['excerpt'];
						$date         = $story['date'];
						$section_name = $story['section'];
						$item_class   = $image_url ? 'ucf-news-feed-item' : 'ucf-news-feed-item ucf-news-feed-item--no-image';
					?>
						<li class="<?php echo esc_attr( $item_class ); ?>">
							<?php if ( $image_url ) : ?>
								<figure class="ucf-news-feed-thumbnail">
									<img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy"<?php echo $image_width ? ' width="' . esc_attr( $image_width ) . '"' : ''; ?><?php echo $image_height ? ' height="' . esc_attr( $image_height ) . '"' : ''; ?>>
								</figure>
							<?php endif; ?>

							<div class="ucf-news-feed-item-content">
								<?php if ( 'modern' === $style && $section_name ) : ?>
									<div class="ucf-news-feed-section"><?php echo esc_html( $section_name ); ?></div>
								<?php endif; ?>

								<?php if ( $item_link ) : ?>
									<a class="ucf-news-feed-story-title" href="<?php echo esc_url( $item_link ); ?>"><?php echo esc_html( $item_title ); ?></a>
								<?php else : ?>
									<span class="ucf-news-feed-story-title"><?php echo esc_html( $item_title ); ?></span>
								<?php endif; ?>

								<?php if ( 'modern' === $style && $excerpt ) : ?>
									<div class="ucf-news-feed-excerpt"><?php echo esc_html( $excerpt ); ?></div>
								<?php endif; ?>

								<?php if ( 'card' === $style && $date ) : ?>
									<div class="ucf-news-feed-date"><?php echo esc_html( $date ); ?></div>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php

			return trim( ob_get_clean() );
		}
	}
}
