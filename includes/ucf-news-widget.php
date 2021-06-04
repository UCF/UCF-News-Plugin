<?php
/**
 * Defines the news widget
 **/

if ( ! class_exists( 'UCF_News_Widget' ) ) {
	class UCF_News_Widget extends WP_Widget {
		/**
		* Sets up the widget
		**/
		public function __construct() {
			$widget_opts = array(
				'classname'   => 'ucf-news-widget',
				'description' => 'UCF News Widget'
			);
			parent::__construct( 'ucf_news_widget', 'UCF News Widget', $widget_opts );
		}

		/**
		* Outputs the content of the widget
		* @param array $args
		* @param array $instance
		**/
		public function widget( $args, $instance ) {
			$items_args = UCF_News_Config::apply_default_options( $instance );

			$items_args['title'] = apply_filters( 'widget_title', $items_args['title'], $this->id_base );
			if ( isset( $args['before_title'] ) && isset( $args['after_title'] ) ) {
				$items_args['title'] = $args['before_title'] . $items_args['title'] . $args['after_title'];
			}

			$items = UCF_News_Feed::get_news_items( $items_args );

			ob_start();

			if ( $items ) {
				echo $args['before_widget'];
				echo UCF_News_Common::display_news_items( $items, $items_args['layout'], $items_args, 'widget' );
				echo $args['after_widget'];
			}

			echo ob_get_clean();
		}

		public function form( $instance ) {
			$options = UCF_News_Config::apply_default_options( $instance );

			$title = $options['title'];
			$layout = $options['layout'];
			$sections = $options['sections'];
			$topics = $options['topics'];
			$search = $options['search'];
			$limit = $options['limit'];
			$offset = $options['offset'];
			$per_row = $options['per_row'];
			$show_image = $options['show_image'];
	?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo __( 'Title' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>"><?php echo __( 'Select Layout' ); ?>:</label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>" type="text">
				<?php foreach( UCF_News_Config::get_layouts() as $key=>$value ) : ?>
					<option value="<?php echo $key; ?>" <?php echo ( $layout == $key ) ? 'selected' : ''; ?>><?php echo $value; ?></option>
				<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'sections' ) ); ?>"><?php echo __( 'Filter by sections' ); ?></label>
				<input class="widefat section-input" id="<?php echo esc_attr( $this->get_field_id( 'sections' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sections' ) ); ?>" type="text" value="<?php echo esc_attr( $sections ); ?>" >
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'topics' ) ); ?>"><?php echo __( 'Filter by topics' ); ?></label>
				<input class="widefat topic-input" id="<?php echo esc_attr( $this->get_field_id( 'topics' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'topics' ) ); ?>" type="text" value="<?php echo esc_attr( $topics ); ?>" >
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'search' ) ); ?>"><?php echo __( 'Filter by search query' ); ?></label>
				<input class="widefat topic-input" id="<?php echo esc_attr( $this->get_field_id( 'search' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search' ) ); ?>" type="text" value="<?php echo esc_attr( $search ); ?>" >
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php echo __( 'Limit results' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" value="<?php echo esc_attr( $limit ); ?>" >
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>"><?php echo __( 'Offset results' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'offset' ) ); ?>" type="number" value="<?php echo esc_attr( $offset ); ?>" >
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'per_row' ) ); ?>"><?php echo __( 'No. of items per row*' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'per_row' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'per_row' ) ); ?>" type="number" value="<?php echo esc_attr( $per_row ); ?>" >
			</p>
			<p>
				<input id="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_image' ) ); ?>" type="checkbox" <?php checked( $show_image ); ?> >
				<label for="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>"><?php echo __( 'Show images*' ); ?></label>
			</p>
			<p><small>* only applied in layouts that support this feature</small></p>
	<?php
		}

		public function update( $new_instance, $old_instance ) {
			// $new_instance may not contain all form input values,
			// so we have to ensure all expected option keys are
			// present before passing the instance along; otherwise
			// UCF_News_Config::apply_default_options() may apply
			// default values in unintended ways
			$options_base = array_fill_keys( array_keys( UCF_News_Config::$default_options ), null );
			$instance = array_merge( $options_base, $new_instance );
			$instance = UCF_News_Config::format_options( $instance );

			return $instance;
		}
	}

	add_action( 'widgets_init', function() {
		return register_widget( 'UCF_News_Widget' );
	} );

}

?>
