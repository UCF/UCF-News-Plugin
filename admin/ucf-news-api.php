<?php
/**
 * Handles registering admin routes
 **/

if ( ! class_exists( 'UCF_News_Admin_API' ) ) {
	class UCF_News_Admin_API {
		public static function ajax_get_sections() {
			$search  = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
			$results = UCF_News_Feed::get_sections( $search );
			if ( is_array( $results ) ) {
				foreach ( $results as $result ) {
					echo esc_html( $result->slug ) . "\n";
				}
			}
			die();
		}

		public static function ajax_get_topics() {
			$search  = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
			$results = UCF_News_Feed::get_topics( $search );
			if ( is_array( $results ) ) {
				foreach ( $results as $result ) {
					echo esc_html( $result->slug ) . "\n";
				}
			}
			die();
		}
	}
}

add_action( 'wp_ajax_ucf-news-sections', array( 'UCF_News_Admin_API', 'ajax_get_sections' ) );
add_action( 'wp_ajax_ucf-news-topics', array( 'UCF_News_Admin_API', 'ajax_get_topics' ) );

?>
