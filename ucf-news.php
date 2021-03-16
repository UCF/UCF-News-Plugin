<?php
/*
Plugin Name: UCF News
Description: Contains shortcode and widget for displaying UCF News Feeds
Version: 2.3.1
Author: UCF Web Communications
License: GPL3
Github Plugin URI: UCF/UCF-News-Plugin
*/
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'UCF_NEWS__PLUGIN_URL', plugins_url( basename( dirname( __FILE__ ) ) ) );
define( 'UCF_NEWS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UCF_NEWS__STATIC_URL', UCF_NEWS__PLUGIN_URL . '/static' );
define( 'UCF_NEWS__PLUGIN_FILE', __FILE__ );

require_once UCF_NEWS__PLUGIN_DIR . 'includes/ucf-news-config.php';
require_once UCF_NEWS__PLUGIN_DIR . 'includes/ucf-news-feed.php';
require_once UCF_NEWS__PLUGIN_DIR . 'includes/ucf-news-common.php';
require_once UCF_NEWS__PLUGIN_DIR . 'includes/ucf-news-shortcode.php';
require_once UCF_NEWS__PLUGIN_DIR . 'includes/external-stories-shortcode.php';
require_once UCF_NEWS__PLUGIN_DIR . 'includes/ucf-statements-shortcode.php';
require_once UCF_NEWS__PLUGIN_DIR . 'includes/ucf-news-widget.php';
require_once UCF_NEWS__PLUGIN_DIR . 'admin/ucf-news-admin.php';
require_once UCF_NEWS__PLUGIN_DIR . 'admin/ucf-news-api.php';

require_once UCF_NEWS__PLUGIN_DIR . 'layouts/ucf-news-classic.php';
require_once UCF_NEWS__PLUGIN_DIR . 'layouts/ucf-news-modern.php';
require_once UCF_NEWS__PLUGIN_DIR . 'layouts/ucf-news-card.php';
require_once UCF_NEWS__PLUGIN_DIR . 'layouts/external-stories-classic.php';
require_once UCF_NEWS__PLUGIN_DIR . 'layouts/ucf-statements-classic.php';

if ( ! function_exists( 'ucf_news_activate' ) ) {
	function ucf_news_activate() {
		UCF_News_Config::add_options();
	}

	register_activation_hook( UCF_NEWS__PLUGIN_FILE, 'ucf_news_activate' );
}

if ( ! function_exists( 'ucf_news_deactivate' ) ) {
	function ucf_news_deactivate() {
		UCF_News_Config::delete_options();
	}

	register_deactivation_hook( UCF_NEWS__PLUGIN_FILE, 'ucf_news_deactivate' );
}

add_action( 'plugins_loaded', function() {

	add_action( 'init', array( 'UCF_News_Shortcode', 'register_shortcode' ) );
	add_action( 'init', array( 'External_Stories_Shortcode', 'register_shortcode' ) );
	add_action( 'init', array( 'UCF_Statements_Shortcode', 'register_shortcode' ) );
	add_action( 'admin_menu', array( 'UCF_News_Config', 'add_options_page' ) );

	if ( class_exists( 'WP_SCIF_Shortcode' ) ) {
		add_filter( 'wp_scif_add_shortcode', array( 'UCF_News_Shortcode', 'register_shortcode_interface' ), 10, 1 );
		add_filter( 'wp_scif_add_shortcode', array( 'External_Stories_Shortcode', 'register_shortcode_interface' ), 10, 1 );
		add_filter( 'wp_scif_add_shortcode', array( 'UCF_Statements_Shortcode', 'register_shortcode_interface' ), 10, 1 );
		add_filter( 'wp_scif_get_preview_stylesheets', 'ucf_news_shortcode_interface_styles', 10, 1 );
	}

} );

?>
