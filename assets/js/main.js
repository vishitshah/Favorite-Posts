( function ( $ ) {

	"use strict";

	/* Window ready event start code */
	$( document ).ready( function () {

		/* Bookmark Button on click */
		$( document ).on( 'click', '.tb-bookmark-btn', function() {

			var postId = $( this ).attr( 'data-post_id' );

			$.ajax({
				type: 'POST',
				url: favoritePosts.ajaxurl,
				data: {
					'action': 'tb_bookmark_ajax',
					'postid': postId
				},
				success: function ( response ) {
					if (response === 'inserted') {
						$( '.tb-bookmark-btn' ).text( favoritePosts.bookmarked_text );
					} else {
						$( '.tb-bookmark-btn' ).text( favoritePosts.bookmark_text );
					}
				}
			});
		});

		/* Clear Saved Articles */
		$( document ).on( 'click', '.clear-save-article', function() {

			var userId   = $( this ).attr( 'data-user_id' );

			$.ajax({
				type: 'POST',
				url: favoritePosts.ajaxurl,
				data: {
					'action':'tb_clear_Favorite_posts',
					'userid' : userId
				},
				success:function ( response ) {
					if ( response == 'deleted' ) {
						$( '.wrapper-post-list' ).html( favoritePosts.nodata );
					}
				}
			});

		});

	});

})( jQuery );
