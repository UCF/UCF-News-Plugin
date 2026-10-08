<?php
/**
 * Gutenberg Modern layout for the UCF News Feed block.
 *
 * @since 4.0.0
 */
?>
<div <?php echo $wrapper_attributes; ?>>
	<?php if ( $title ) : ?>
		<h2 class="ucf-news-feed-title"><?php echo esc_html( $title ); ?></h2>
	<?php endif; ?>

	<ul class="ucf-news-feed-list">
		<?php foreach ( $stories as $story ) :
			$item_class = $story['image'] ? 'ucf-news-feed-item' : 'ucf-news-feed-item ucf-news-feed-item--no-image';
		?>
			<li class="<?php echo esc_attr( $item_class ); ?>">
				<?php if ( $story['image'] ) : ?>
					<figure class="ucf-news-feed-thumbnail">
						<img src="<?php echo esc_url( $story['image'] ); ?>" alt="" loading="lazy"<?php echo $story['width'] ? ' width="' . esc_attr( $story['width'] ) . '"' : ''; ?><?php echo $story['height'] ? ' height="' . esc_attr( $story['height'] ) . '"' : ''; ?>>
					</figure>
				<?php endif; ?>

				<div class="ucf-news-feed-item-content">
					<?php if ( $story['section'] ) : ?>
						<div class="ucf-news-feed-section"><?php echo esc_html( $story['section'] ); ?></div>
					<?php endif; ?>

					<?php if ( $story['link'] ) : ?>
						<a class="ucf-news-feed-story-title" href="<?php echo esc_url( $story['link'] ); ?>"><?php echo esc_html( $story['title'] ); ?></a>
					<?php else : ?>
						<span class="ucf-news-feed-story-title"><?php echo esc_html( $story['title'] ); ?></span>
					<?php endif; ?>

					<?php if ( $story['excerpt'] ) : ?>
						<div class="ucf-news-feed-excerpt"><?php echo esc_html( $story['excerpt'] ); ?></div>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
