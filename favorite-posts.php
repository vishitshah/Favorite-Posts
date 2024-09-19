<?php
/**
 * Plugin Name: Favorite Posts
 * Plugin URI: https://wordpress.org/plugins/favorite-posts/
 * Description: Simple and flexible favorite buttons for posts.
 * Version: 1.0
 * Author: Vishit shah
 * Author URI: https://vishitshah.com
 * Text Domain: favorite-posts
 * Domain Path: /languages
 *
 * Requires at least: 5.2
 * Requires PHP: 7.4
 *
 * WC requires at least: 7.3.0
 * WC tested up to: 8.4.0
 *
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Defind Class
 */

defined( 'FAVORITE_POSTS_ROOT' ) or define( 'FAVORITE_POSTS_ROOT', __DIR__ );
defined( 'FAVORITE_POSTS_ROOT_DIR' ) or define( 'FAVORITE_POSTS_ROOT_DIR', plugins_url() . '/favorite-posts' );
defined( 'FAVORITE_POSTS_PLUGIN_PATH' ) or define( 'FAVORITE_POSTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );


if ( ! class_exists( 'Favorite_Posts' ) ) {

	class Favorite_Posts {

		// Construct
		public function __construct() {

			require_once FAVORITE_POSTS_ROOT . '/favorite-posts-functions.php';
			/* For Enqueue custom style */
			add_action( 'wp_enqueue_scripts', array( $this, 'favorite_posts_enqueue_custom_style' ), 99 );
		}

		public function favorite_posts_enqueue_custom_style() {

			wp_register_style( 'favorite-posts-main', FAVORITE_POSTS_ROOT_DIR . '/assets/css/main.css', null, '1.0' );
			wp_enqueue_style( 'favorite-posts-main' );

			wp_register_script(
				'favorite-posts-main', 
				FAVORITE_POSTS_ROOT_DIR . '/assets/js/main.js', 
				array(), 
				'1.0', 
				true // This parameter sets the script to load in the footer
			);
			wp_enqueue_script('favorite-posts-main');

			wp_localize_script(
				'favorite-posts-main',
				'favoritePosts',
				array(
					'ajaxurl'         => admin_url( 'admin-ajax.php' ),
					'bookmark_text'   => __( 'Bookmark', 'favorite-posts' ),
					'bookmarked_text' => __( 'Bookmarked', 'favorite-posts' ),
					'nodata'          => __( 'No Posts are saved..', 'favorite-posts' ),
				)
			);
		}
	} // end of class

	if ( ! function_exists( 'favorite_posts_plugins_loaded' ) ) {

		function favorite_posts_plugins_loaded() {

			$favorite_posts = new Favorite_Posts();
		}
	}
	add_action( 'plugins_loaded', 'favorite_posts_plugins_loaded' );

} // end of class_exists
