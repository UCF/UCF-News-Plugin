<?php
/**
 * Handles registering admin routes
 **/

if ( ! class_exists( 'UCF_News_Admin_API' ) ) {
	class UCF_News_Admin_API {
		public static function ajax_get_sections() {
			$results = UCF_News_Feed::get_sections( $_GET['q'] );
			foreach( $results as $result ) {
				echo $result->slug . "\n";
			}
			die();
		}

		public static function ajax_get_topics() {
			$results = UCF_News_Feed::get_topics( $_GET['q'] );
			foreach( $results as $result ) {
				echo $result->slug . "\n";
			}
			die();
		}
	}
}

add_action( 'wp_ajax_ucf-news-sections', array( 'UCF_News_Admin_API', 'ajax_get_sections' ) );
add_action( 'wp_ajax_ucf-news-topics', array( 'UCF_News_Admin_API', 'ajax_get_topics' ) );

?>
