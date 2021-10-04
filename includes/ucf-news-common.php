<?php
/**
 * Place common functions here.
 **/

if ( ! class_exists( 'UCF_News_Common' ) ) {

	class UCF_News_Common {
		public static function display_news_items( $items, $layout, $args, $display_type='default', $content='' ) {
			ob_start();

			// Before
			$layout_before = ucf_news_display_classic_before( '', $items, $args, $display_type );
			if ( has_filter( 'ucf_news_display_' . $layout . '_before' ) ) {
				$layout_before = apply_filters( 'ucf_news_display_' . $layout . '_before', $layout_before, $items, $args, $display_type );
			}
			echo $layout_before;

			// Title
			$layout_title = ucf_news_display_classic_title( '', $items, $args, $display_type );
			if ( has_filter( 'ucf_news_display_' . $layout . '_title' ) ) {
				$layout_title = apply_filters( 'ucf_news_display_' . $layout . '_title', $layout_title, $items, $args, $display_type );
			}
			echo $layout_title;

			// Main content/loop
			$layout_content = ucf_news_display_classic( '', $items, $args, $display_type, $content );
			if ( has_filter( 'ucf_news_display_' . $layout ) ) {
				$layout_content = apply_filters( 'ucf_news_display_' . $layout, $layout_content, $items, $args, $display_type, $content );
			}
			echo $layout_content;

			// After
			$layout_after = ucf_news_display_classic_after( '', $items, $args, $display_type );
			if ( has_filter( 'ucf_news_display_' . $layout . '_after' ) ) {
				$layout_after = apply_filters( 'ucf_news_display_' . $layout . '_after', $layout_after, $items, $args, $display_type );
			}
			echo $layout_after;

			return ob_get_clean();
		}

		public static function display_external_stories( $items, $layout, $args ) {
			if ( has_filter( "ucf_external_stories_{$layout}_before" ) ) {
				$before = apply_filters( "ucf_external_stories_{$layout}_before", null, $items, $args );
			} else {
				$before = ucf_external_stories_classic_before( null, $items, $args );
			}

			if ( has_filter( "ucf_external_stories_{$layout}_title" ) ) {
				$title = apply_filters( "ucf_external_stories_{$layout}_title", null, $items, $args );
			} else {
				$title = ucf_external_stories_classic_title( null, $items, $args );
			}

			if ( has_filter( "ucf_external_stories_{$layout}_content" ) ) {
				$content = apply_filters( "ucf_external_stories_{$layout}_content", null, $items, $args );
			} else {
				$content = ucf_external_stories_classic_content( null, $items, $args );
			}

			if ( has_filter( "ucf_external_stories_{$layout}_after" ) ) {
				$after = apply_filters( "ucf_external_stories_{$layout}_after", null, $items, $args );
			} else {
				$after = ucf_external_stories_classic_after( null, $items, $args );
			}

			return $before . $title . $content . $after;
		}

		/**
		 * The default filters and functions for display a list
		 * of UCF Statements
		 * @author Jim Barnes
		 * @since 2.3.0
		 * @param array $items The array of statement items
		 * @param string $layout The layout to use
		 * @param array $args Additional arguments
		 * @return string
		 */
		public static function display_statements( $items, $layout, $args ) {
			if ( has_filter( "ucf_statements_{$layout}_before" ) ) {
				$before = apply_filters( "ucf_statements_{$layout}_before", null, $items, $args );
			} else {
				$before = ucf_statements_classic_before( null, $items, $args );
			}

			if ( has_filter( "ucf_statements_{$layout}_title" ) ) {
				$title = apply_filters( "ucf_statements_{$layout}_title", null, $items, $args );
			} else {
				$title = ucf_statements_classic_title( null, $items, $args );
			}

			if ( has_filter( "ucf_statements_{$layout}_content" ) ) {
				$content = apply_filters( "ucf_statements_{$layout}_content", null, $items, $args );
			} else {
				$content = ucf_statements_classic_content( null, $items, $args );
			}

			if ( has_filter( "ucf_statements_{$layout}_after" ) ) {
				$after = apply_filters( "ucf_statements_{$layout}_after", null, $items, $args );
			} else {
				$after = ucf_statements_classic_after( null, $items, $args );
			}

			return $before . $title . $content . $after;
		}

		/**
		 * Returns an image URL or image details to use for
		 * a story's fallback thumbnail.
		 *
		 * @param string $size Registered image size that the fallback thumbnail should be sized at
		 * @param bool $return_details If true, this function will return an array of image data
		 * @return mixed array of image data if $return_details=true, or image URL string
		 */
		public static function get_fallback_image( $size='thumbnail', $return_details=false ) {
			$img_url     = '';
			$img_details = array( 0 => '', 1 => '', 2 => '' );
			$img_id      = get_option( 'ucf_news_fallback_image', null );

			if ( $img_id ) {
				$wp_img_details = wp_get_attachment_image_src( $img_id, $size );
				if ( $wp_img_details ) {
					$img_details = $wp_img_details;
					$img_url     = $img_details ? $img_details[0] : '';
				}
			}

			return $return_details ? $img_details : $img_url;
		}

		/**
		 * Returns an image URL or image details to use for
		 * a story's thumbnail.
		 *
		 * @param object $item Feed item for the story
		 * @param bool $return_details If true, this function will return an array of image data
		 * @return mixed array of image data if $return_details=true, or image URL string
		 */
		public static function get_story_image_or_fallback( $item, $return_details=false ) {
			$img_url        = '';
			$img_details    = array( 0 => '', 1 => '', 2 => '' );
			$thumbnail      = property_exists( $item, 'thumbnail' ) ? $item->thumbnail : false;
			$featured_media = isset( $item->_embedded->{'wp:featuredmedia'} ) ? $item->_embedded->{'wp:featuredmedia'} : false;

			// Check for Today's custom 'thumbnail' field
			if ( $thumbnail ) {
				$img_url = $thumbnail;

				// Try to sniff for img dim's in featured
				// image metadata:
				if ( $return_details && is_array( $featured_media ) ) {
					$img_details[0] = $img_url;
					$img_obj = $featured_media[0];
					if ( isset( $img_obj->media_details->sizes ) ) {
						foreach ( (array) $img_obj->media_details->sizes as $size ) {
							if ( $size->source_url === $img_url ) {
								$img_details[1] = $size->width ?? '';
								$img_details[2] = $size->height ?? '';
								break;
							}
						}
					}
				}
			}
			// As a fallback for sites not pointing to Today,
			// try to use featured image data instead
			else if ( is_array( $featured_media ) ) {
				$img_obj = $featured_media[0];

				if ( isset( $img_obj->media_details->sizes->medium->source_url ) ) {
					$img_url = $img_obj->media_details->sizes->medium->source_url ?? '';

					if ( $return_details ) {
						$img_details[0] = $img_url;
						$img_details[1] = $img_obj->media_details->sizes->medium->width ?? '';
						$img_details[2] = $img_obj->media_details->sizes->medium->height ?? '';
					}
				}
			}

			// If the feature image isn't defined, use the fallback image
			if ( ! $img_url ) {
				$img_details = self::get_fallback_image( 'thumbnail', true );
				$img_url     = $img_details[0];
			}

			return $return_details ? $img_details : $img_url;
		}

		/**
		 * Returns an <img> tag to use for a story's thumbnail.
		 *
		 * @since 2.2.2
		 * @author Jo Dickson
		 * @param object $item Feed item for the story
		 * @param string $css_class CSS class(es) to apply to the <img> tag
		 * @return string HTML <img> tag
		 */
		public static function get_story_img_tag( $item, $css_class='ucf-news-thumbnail-image' ) {
			$img_details = self::get_story_image_or_fallback( $item, true );
			if ( ! $img_details || ! $img_details[0] ) return '';

			ob_start();
		?>
			<img src="<?php echo $img_details[0]; ?>" class="<?php echo $css_class; ?>" alt="" width="<?php echo $img_details[1]; ?>" height="<?php echo $img_details[2]; ?>">
		<?php
			return trim( ob_get_clean() );
		}

		public static function get_story_terms( $item, $taxonomy ) {
			$tax_terms     = array();
			$taxonomy_list = isset( $item->_links->{'wp:term'} ) ? $item->_links->{'wp:term'} : false;
			$all_terms     = isset( $item->_embedded->{'wp:term'} ) ? $item->_embedded->{'wp:term'} : false;
			$all_terms_key = false;

			if ( is_array( $taxonomy_list ) && is_array( $all_terms ) ) {
				// Determine the position in $all_terms that the terms for
				// $taxonomy are located.  $taxonomy_list contains a list of
				// taxonomy objects that should be listed in the same order
				// that groups of their terms are listed in $all_terms.
				foreach ( $taxonomy_list as $key => $tax_obj ) {
					if ( $tax_obj->taxonomy == $taxonomy ) {
						$all_terms_key = $key;
						break;
					}
				}

				if ( $all_terms_key !== false ) {
					$tax_terms = $all_terms[$all_terms_key];
				}
			}

			return $tax_terms;
		}

		public static function get_story_sections( $item ) {
			return self::get_story_terms( $item, 'category' );
		}

		public static function get_story_topics( $item ) {
			return self::get_story_terms( $item, 'post_tag' );
		}

		/**
		 * Returns the primary section (category) for a given story.
		 *
		 * @since 2.1.9
		 * @author Jo Dickson
		 * @param object REST API result for a post
		 * @return mixed Term object, or null
		 */
		public static function get_story_primary_section( $item ) {
			$primary  = null;
			$sections = self::get_story_sections( $item );

			if ( property_exists( $item, 'primary_category' ) ) {
				foreach ( $sections as $section ) {
					if ( $section->id === $item->primary_category ) {
						$primary = $section;
						break;
					}
				}
			}
			else {
				$primary = $sections[0];
			}

			return $primary;
		}

		/**
		 * Returns the primary topic (tag) for a given story.
		 *
		 * @since 2.1.9
		 * @author Jo Dickson
		 * @param object REST API result for a post
		 * @return mixed Term object, or null
		 */
		public static function get_story_primary_topic( $item ) {
			$primary = null;
			$topics  = self::get_story_topics( $item );

			if ( property_exists( $item, 'primary_tag' ) ) {
				foreach ( $topics as $topic ) {
					if ( $topic->id === $item->primary_tag ) {
						$primary = $topic;
						break;
					}
				}
			}
			else {
				$primary = $topics[0];
			}

			return $primary;
		}
	}

}
?>
