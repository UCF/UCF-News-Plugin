<?php
/**
 * The default functions for the card layout
 **/
if ( ! function_exists( 'ucf_news_display_card_before' ) ) {
	function ucf_news_display_card_before( $content, $items, $args, $display_type ) {
		ob_start();
	?>
		<div class="ucf-news ucf-news-card">
	<?php
		return ob_get_clean();
	}

	add_filter( 'ucf_news_display_card_before', 'ucf_news_display_card_before', 10, 4 );
}

if ( ! function_exists( 'ucf_news_display_card_title' ) ) {
	function ucf_news_display_card_title( $content, $items, $args, $display_type ) {
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

	add_filter( 'ucf_news_display_card_title', 'ucf_news_display_card_title', 10, 4 );
}

if ( ! function_exists( 'ucf_news_display_card' ) ) {
	function ucf_news_display_card( $content, $items, $args, $display_type, $fallback_message='' ) {
		if ( $items === false ) {
			$items = array();
		}
		else if ( ! is_array( $items ) ) {
			$items = array( $items );
		}

		$per_row = intval( $args['per_row'] );
		$show_image = filter_var( $args['show_image'], FILTER_VALIDATE_BOOLEAN );

		ob_start();

		if ( count( $items ) === 0 && $fallback_message ) :
			echo '<div class="ucf-news-error">' . $fallback_message . '</div>';
		else :
	?>
		<div class="ucf-news-card-deck row">
			<?php
			foreach ( $items as $index => $item ) :
				$date = date( "M d", strtotime( $item->date ) );
				$item_img = UCF_News_Common::get_story_img_tag( $item, 'ucf-news-thumbnail-image img-fluid w-md-100' );

				// Try to use precise column sizes where we can to
				// prevent undesirable column overflow in small containers
				$item_col_size = 12 % $per_row === 0 ? 12 / $per_row : '';
				$item_col_class = $item_col_size ? "col-lg-{$item_col_size}" : 'col-lg';

				if ( $index !== 0 && ( $index % $per_row ) === 0 ) {
					echo '</div><div class="ucf-news-card-deck row">';
				}
			?>
				<div class="ucf-news-item <?php echo $item_col_class; ?> mb-4 pb-lg-2">
					<div class="ucf-news-card card h-lg-100" style="background-color: transparent; border-color: rgba(118, 118, 118, .25);">
						<div class="row no-gutters">
							<?php if ( $item_img && $show_image ): ?>
							<div class="ucf-news-thumbnail col-4 col-md-3 col-lg-12 py-3 pl-3 p-lg-0">
								<?php echo $item_img; ?>
							</div>
							<?php endif; ?>
							<div class="col col-lg-12 position-static">
								<div class="ucf-news-item-content card-block">
									<a class="ucf-news-item-title card-title stretched-link d-block font-weight-bold mb-0 line-height-3" href="<?php echo $item->link; ?>" style="color: inherit;">
										<?php echo $item->title->rendered; ?>
									</a>
								</div>
								<div class="ucf-news-card-text card-footer border-0 bg-transparent font-italic font-size-sm" style="background-color: transparent; opacity: .6;">
									<?php echo $date; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	<?php
		endif; // End if item count

		return ob_get_clean();
	}

	add_filter( 'ucf_news_display_card', 'ucf_news_display_card', 10, 5 );
}

if ( ! function_exists( 'ucf_news_display_card_after' ) ) {
	function ucf_news_display_card_after( $content, $items, $args, $display_type ) {
		ob_start();
	?>
		</div>
	<?php
		return ob_get_clean();
	}

	add_filter( 'ucf_news_display_card_after', 'ucf_news_display_card_after', 10, 4 );
}
