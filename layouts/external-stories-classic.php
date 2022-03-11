<?php
/**
 * Defines the classic layout for
 * external stories
 */
if ( ! function_exists( 'ucf_external_stories_classic_before' ) ) {
	function ucf_external_stories_classic_before( $content, $items, $args ) {
		ob_start();
	?>
		<div class="ucf-external-stories">
	<?php
		return ob_get_clean();
	}

	add_filter( 'ucf_external_stories_classic_before', 'ucf_external_stories_classic_before', 10, 3 );
}

if ( ! function_exists( 'ucf_external_stories_classic_title' ) ) {
	function ucf_external_stories_classic_title( $content, $items, $args ) {
		ob_start();

		if ( isset( $args['title'] ) && ! empty( $args['title'] ) ) :
	?>
		<h2 class="h5 text-uppercase text-default-aw mb-4"><?php echo $args['title']; ?></h2>
	<?php
		endif;

		return ob_get_clean();
	}

	add_filter( 'ucf_external_stories_classic_title', 'ucf_external_stories_classic_title', 10, 3 );
}

if ( ! function_exists( 'ucf_external_stories_classic_content' ) ) {
	function ucf_external_stories_classic_content( $content, $items, $args ) {
		ob_start();

		foreach( $items as $item ) :
	?>
		<article class="mb-3">
			<a class="text-secondary text-decoration-none hover-text-underline" href="<?php echo $item->url; ?>" target="_blank" rel="nofollow">
				<h3 class="h6 external-story-title"><?php echo $item->title; ?></h3>
			</a>
			<cite class="external-story-source text-muted font-size-sm">
				<?php echo $item->source; ?>
			</cite>
		</article>
	<?php
		endforeach;

		return ob_get_clean();
	}

	add_filter( 'ucf_external_stories_classic_content', 'ucf_external_stories_classic_content', 10, 3 );
}

if ( ! function_exists( 'ucf_external_stories_classic_after' ) ) {
	function ucf_external_stories_classic_after( $content, $items, $args ) {
		ob_start();
	?>
		</div>
	<?php
		return ob_get_clean();
	}

	add_filter( 'ucf_external_stories_classic_after', 'ucf_external_stories_classic_after', 10, 3 );
}
