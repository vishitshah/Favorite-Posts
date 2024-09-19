<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Creating Custom Bookmark
if ( ! function_exists( 'tb_favorite_posts_shortcode' ) ) {
	function tb_favorite_posts_shortcode() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'tb_favorite_posts';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			// Table Creation
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                post_id bigint(20) NOT NULL,
                post_type varchar(256) NOT NULL,
                bookmark_note longtext,
                date_created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		// Check data
		$id           = get_the_ID();
		$userid       = get_current_user_id();
		$results      = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %s WHERE user_id = %d AND post_id = %d',
				$table_name,
				$userid,
				$id
			),
			ARRAY_A
		);
		$rowcount     = $wpdb->num_rows;
		$bookmarktext = ( $rowcount ) ? __( 'Bookmarked', 'favorite-posts' ) : __( 'Bookmark', 'favorite-posts' );
		echo '<a href="javascript:void(0)" class="tb-bookmark-btn" data-post_id="' . esc_attr($id) . '">' . esc_html($bookmarktext) . '</a>';
	}
}
add_shortcode( 'tb_favorite_posts', 'tb_favorite_posts_shortcode' );

// AJAX To Add compare product functionality
if ( ! function_exists( 'tb_favorite_posts_ajax_function' ) ) {
	function tb_favorite_posts_ajax_function() {

		global $wpdb;
		$postid = ! empty( $_POST['postid'] ) ? sanitize_text_field( wp_unslash( $_POST['postid'] ) ) : '';

		if ( isset( $_POST['my_nonce_field'] ) && wp_verify_nonce( $_POST['my_nonce_field'], 'my_nonce_action' ) ) {
			$user_id       = get_current_user_id();
			$post_data     = get_post( $postid );
			$postype       = $post_data->post_type;
			$bookmark_note = '';
			$date          = gmdate( 'Y-m-d H:i:s' );
			$table_name    = $wpdb->prefix . 'tb_favorite_posts';
			$results       = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT id FROM %s WHERE user_id = %d AND post_id = %d',
					$table_name,
					$user_id,
					$postid
				),
				ARRAY_A
			);
			if ( $results ) {
				$wpdb->delete( $table_name, array( 'id' => $results['0']['id'] ) );
				echo esc_html__( 'deleted', 'favorite-posts' );
			} else {
				$data = array(
					'user_id'       => $user_id,
					'post_id'       => $postid,
					'post_type'     => $postype,
					'bookmark_note' => $bookmark_note,
					'date_created'  => $date,
				);
				$result_check = $wpdb->insert(
					$table_name,
					$data
				);

				if ( $result_check ) {
					echo esc_html__( 'inserted', 'favorite-posts' );
				}
			}
			die();
		}
	}
}
add_action( 'wp_ajax_tb_bookmark_ajax', 'tb_favorite_posts_ajax_function' );
add_action( 'wp_ajax_nopriv_tb_bookmark_ajax', 'tb_favorite_posts_ajax_function' );

// Display Save bookmarks Page shortcode
if ( ! function_exists( 'show_favorite_posts_shortcode' ) ) {
	function show_favorite_posts_shortcode() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'tb_favorite_posts';
		$user_id    = get_current_user_id();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name ) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT post_id FROM %s WHERE user_id = %d AND post_type = 'post'",
					$table_name,
					$user_id
				),
				ARRAY_A
			);
			echo '<div class="wrapper-post-list">';
			if ( ! empty( $results ) ) {
				echo '<ul class="saved-articles-list">';
				foreach ( $results as $key => $postid ) {
					$id = $postid['post_id'];
					echo '<li>';
					echo '<p style="text-align: left;">';
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'thumbnail' );
					if ( $image ) {
						echo '<img src="' . esc_url($image[0]) . '" >';
					}
					echo '<a href="' . esc_url(get_the_permalink($id)) . '"><strong>' . esc_html(get_the_title($id)) . '</strong></a>';
					echo '<br/>';
					echo esc_html(get_the_excerpt($id));
					echo '<br/>';
					echo '<a href="' . esc_url(get_the_permalink($id)) . '"><em>' . esc_html__( 'Continue Reading', 'favorite-posts' ) . '</em></a>';
					echo '</p>';
					echo '<li>';
				}
				echo '</ul>';
				echo '<a href="javascript:void(0)" class="clear-save-article" data-user_id="' . esc_attr( $user_id ) . '">' . esc_html__( 'Clear Saved Articles', 'favorite-posts' ) . '</a>';
			} else {
				echo esc_html__( 'No Posts are saved..', 'favorite-posts' );
			}
			echo '</div>';
		}
	}
}
add_shortcode( 'show_favorite_posts', 'show_favorite_posts_shortcode' );

// Clear save article ajax function
if ( ! function_exists( 'bookmark_tb_clear_favorite_posts' ) ) {
	function bookmark_tb_clear_favorite_posts() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'tb_favorite_posts';
		$user_id    = get_current_user_id();
		$results    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id FROM %s WHERE user_id = %d AND post_type = %s',
				$table_name,
				$user_id,
				'post'
			),
			ARRAY_A
		);
		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $id ) {
				$wpdb->delete( $table_name, array( 'id' => $id['id'] ) );
			}
			echo esc_html__( 'deleted', 'favorite-posts' );
		}
		die();
	}
}
add_action( 'wp_ajax_tb_clear_favorite_posts', 'bookmark_tb_clear_favorite_posts' );
add_action( 'wp_ajax_nopriv_tb_clear_favorite_posts', 'bookmark_tb_clear_favorite_posts' );
