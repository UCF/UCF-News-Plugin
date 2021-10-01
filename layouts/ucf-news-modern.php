<?php
/**
 * The default functions for the modern layout
 **/
if ( ! function_exists( 'ucf_news_display_modern_before' ) ) {
	function ucf_news_display_modern_before( $content, $items, $args, $display_type ) {
		ob_start();
	?>
		<div class="ucf-news ucf-news-modern">
	<?php
		return ob_get_clean();
	}

	add_filter( 'ucf_news_display_modern_before', 'ucf_news_display_modern_before', 10, 4 );
}

if ( ! function_exists( 'ucf_news_display_modern_title' ) ) {
	function ucf_news_display_modern_title( $content, $items, $args, $display_type ) {
		$formatted_title = $args['title'];

		switch( $display_type ) {
			case 'widget':
				break;
			case 'default':
			default:
				if ( $formatted_title ) {
					$formatted_title = '<h2 class="ucf-news-title mb-4">' . $formatted_title . '</h2>';
				}
				break;
		}

		return $formatted_title;
	}

	add_filter( 'ucf_news_display_modern_title', 'ucf_news_display_modern_title', 10, 4 );
}

if ( ! function_exists( 'ucf_news_display_modern' ) ) {
	function ucf_news_display_modern( $content, $items, $args, $display_type, $fallback_message ) {
		if ( $items === false ) {
			$items = array();
		}
		else if ( ! is_array( $items ) ) {
			$items = array( $items );
		}

		ob_start();

		if ( count( $items ) === 0 ) :
			echo '<div class="ucf-news-error">' . $fallback_message . '</div>';
		else :

			foreach ( $items as $item ) :
				$item_img = UCF_News_Common::get_story_img_tag( $item, 'ucf-news-thumbnail-image img-fluid' );
				$section = UCF_News_Common::get_story_primary_section( $item );
	?>

			<div class="ucf-news-item position-relative hover-parent p-3 mb-3" style="margin-left: -1rem; margin-right: -1rem;">
				<div class="media-background hover-child-show fade bg-faded"></div>

				<div class="media">
					<?php if ( $item_img ) : ?>
					<div class="ucf-news-item-thumbnail d-flex w-25 mr-3" style="max-width: 150px;">
						<?php echo $item_img; ?>
					</div>
					<?php endif; ?>
					<div class="ucf-news-item-content media-body">
						<?php if ( $section ) : ?>
						<div class="ucf-news-section mb-2 pb-1">
							<span class="ucf-news-section-title badge badge-primary"><?php echo $section->name; ?></span>
						</div>
						<?php endif; ?>

						<div class="ucf-news-item-details">
							<a class="ucf-news-item-title d-block stretched-link text-secondary h5 mb-2 pb-1" href="<?php echo $item->link; ?>">
								<?php echo $item->title->rendered; ?>
							</a>
							<div class="ucf-news-item-excerpt font-size-sm">
								<?php echo wp_trim_words( $item->excerpt->rendered, 25 ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
	<?php
			endforeach;

		endif; // End if item count

		return ob_get_clean();
	}

	add_filter( 'ucf_news_display_modern', 'ucf_news_display_modern', 10, 5 );
}

if ( ! function_exists( 'ucf_news_display_modern_after' ) ) {
	function ucf_news_display_modern_after( $content, $items, $args, $display_type ) {
		ob_start();
	?>
		</div>
	<?php
		return ob_get_clean();
	}

	add_filter( 'ucf_news_display_modern_after', 'ucf_news_display_modern_after', 10, 4 );
}
