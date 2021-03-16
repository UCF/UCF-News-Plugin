<?php
/**
 * Handles plugin configuration
 */
if ( ! class_exists( 'UCF_News_Config' ) ) {
	class UCF_News_Config {
		public static
			$default_options = array(
				'title'      => 'News',
				'layout'     => 'classic',
				'sections'   => '',
				'topics'     => '',
				'limit'      => 3,
				'per_row'    => 3,
				'show_image' => true,
				'offset'     => 0
			),
			$default_plugin_options = array(
				'ucf_news_feed_url'             => 'https://www.ucf.edu/news/wp-json/wp/v2/',
				'ucf_external_stories_feed_url' => 'https://www.ucf.edu/news/wp-json/ucf-news/v1/external-stories/',
				'ucf_news_include_css'          => 'on',
				'ucf_news_fallback_image'       => '',
				'ucf_news_http_timeout'         => 5
			);

		public static function add_options() {
			$defaults = self::$default_plugin_options;

			add_option( 'ucf_news_feed_url', $defaults['ucf_news_feed_url'] );
			add_option( 'ucf_external_stories_feed_url', $defaults['ucf_external_stories_feed_url'] );
			add_option( 'ucf_news_include_css', $defaults['ucf_news_include_css'] );
			add_option( 'ucf_news_fallback_image', $defaults['ucf_news_fallback_image'] );
			add_option( 'ucf_news_http_timeout', $defaults['ucf_news_http_timeout'] );
		}

		public static function delete_options() {
			delete_option( 'ucf_news_feed_url' );
			delete_option( 'ucf_external_stories_feed_url' );
			delete_option( 'ucf_news_include_css' );
			delete_option( 'ucf_news_fallback_image' );
			delete_option( 'ucf_news_http_timeout' );
		}

		public static function get_layouts() {
			$layouts = array(
				'classic' => 'Classic Layout',
				'modern'  => 'Modern Layout',
				'card'    => 'Card Layout'
			);

			$layouts = apply_filters( 'ucf_news_get_layouts', $layouts );

			return $layouts;
		}

		public static function get_external_stories_layouts() {
			$layouts = array(
				'classic' => 'Classic Layout'
			);

			$layouts = apply_filters( 'ucf_external_stories_layouts', $layouts );

			return $layouts;
		}

		public static function get_default_options() {
			$defaults = self::$default_options;

			return self::format_options( $defaults );
		}

		public static function get_default_plugin_options() {
			$defaults = self::$default_plugin_options;

			return self::format_options( $defaults );
		}

		public static function apply_default_options( $list, $list_keys_only=false ) {
			$defaults = self::get_default_options();
			$options = array();

			if ( $list_keys_only ) {
				foreach( $list as $key => $val ) {
					$options[$key] = ! empty( $val ) ? $val : $defaults['key'];
				}
			} else {
				$options = array_merge( $defaults, $list );
			}

			$options = self::format_options( $options );

			return $options;
		}

		public static function format_options( $list ) {
			foreach( $list as $key => $val ) {
				switch( $key ) {
					case 'limit':
					case 'per_row':
					case 'offset':
					case 'ucf_news_http_timeout':
						$list[$key] = intval( $val );
						break;
					case 'ucf_news_include_css':
					case 'show_image':
						$list[$key] = filter_var( $val, FILTER_VALIDATE_BOOLEAN );
						break;
					default:
						break;
				}
			}

			return $list;
		}

		public static function add_options_page() {
			add_options_page(
				'UCF News',
				'UCF News',
				'manage_options',
				'ucf_news_settings',
				array(
					'UCF_News_Config',
					'add_settings_page'
				)
			);

			add_action( 'admin_init', array( 'UCF_News_Config', 'register_settings' ) );
		}

		public static function register_settings() {
			register_setting( 'ucf-news-group', 'ucf_news_feed_url' );
			register_setting( 'ucf-news-group', 'ucf_external_stories_feed_url' );
			register_setting( 'ucf-news-group', 'ucf_news_include_css' );
			register_setting( 'ucf-news-group', 'ucf_news_fallback_image' );
			register_setting( 'ucf-news-group', 'ucf_news_http_timeout' );
		}

		public static function add_settings_page() {
			if ( function_exists( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			} else {
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_script( 'media-upload' );
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_media();
			}

			$defaults = self::get_default_plugin_options();
			$ucf_news_feed_url = get_option( 'ucf_news_feed_url', $defaults['ucf_news_feed_url'] );
			$ucf_news_external_stories_feed_url = get_option( 'ucf_external_stories_feed_url', $defaults['ucf_external_stories_feed_url'] );
			$ucf_news_include_css = get_option( 'ucf_news_include_css', $defaults['ucf_news_include_css'] );
			$ucf_news_http_timeout = get_option( 'ucf_news_http_timeout', $defaults['ucf_news_http_timeout'] );
			$ucf_news_fallback_image = get_option( 'ucf_news_fallback_image', $defaults['ucf_news_fallback_image'] );
			$ucf_news_fallback_image_src = $ucf_news_fallback_image ? UCF_News_Common::get_fallback_image( $ucf_news_fallback_image ) : '';

	?>
	<div class="wrap">
		<h1>UCF News Settings</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'ucf-news-group' ); ?>
			<?php do_settings_sections( 'ucf-news-groups' ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">UCF News Feed URL</th>
					<td><input class="large-text" type="text" name="ucf_news_feed_url" value="<?php echo esc_attr( $ucf_news_feed_url ); ?>"></td>
				</tr>
				<tr valign="top">
					<th scope="row">UCF External Stories Feed URL</th>
					<td>
						<input class="large-text" type="text" name="ucf_external_stories_feed_url" value="<?php echo esc_attr( $ucf_news_external_stories_feed_url ); ?>">
						<p class="description">URL cannot include query parameters</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Include CSS</th>
					<td><input type="checkbox" name="ucf_news_include_css" <?php echo ( $ucf_news_include_css === 'on' ) ? 'checked' : ''; ?>>
						Include Default CSS
					</input></td>
				</tr>
				<tr valign="top">
					<th scope="row">HTTP Timeout</th>
					<td><input type="number" name="ucf_news_http_timeout" value="<?php echo esc_attr( $ucf_news_http_timeout ); ?>"></td>
				</tr>
				<tr valign="top">
					<th scope="row">Fallback Image</th>
					<td>				
						<img class="ucf_news_fallback_image_preview" alt="Fallback Image Preview" src="<?php echo $ucf_news_fallback_image_src; ?>" style="max-width:150px;max-height:150px;<?php echo(empty($ucf_news_fallback_image_src)?"display:none;":""); ?>"><br/>
						<p class="ucf_news_fallback_message" style="margin:-1em 0 1em 0;<?php echo(empty($ucf_news_fallback_image_src)?"":"display:none;");?>">No fallback image has been selected</p>
						<input class="ucf_news_fallback_image" type="hidden" name="ucf_news_fallback_image" value="<?php echo $ucf_news_fallback_image; ?>">
						<a href="#" class="ucf_news_clear_image_upload button button-primary" style="<?php echo(empty($ucf_news_fallback_image_src)?"display:none;":""); ?>">Remove Image</a>
						<a href="#" class="ucf_news_fallback_image_upload button button-primary"><?php echo(empty($ucf_news_fallback_image_src)?"Add Image":"Change Image");?></a>			
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
		}
	}
}
?>
