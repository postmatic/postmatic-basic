<?php

class Prompt_Email_Comment_Rendering {

	protected static $post_id;
	protected static $flood_comment;

	public static function render( $comment, $args, $depth ) {

		self::set_context( $comment );

		// Note that WordPress closes the div for you, do not close it here!
		// https://codex.wordpress.org/Function_Reference/wp_list_comments
		printf(
			'<div class="%s">',
			implode( ' ', get_comment_class( self::base_classes( $comment ), $comment, $comment->comment_post_ID ) )
		);

		$comment_actions = '';
		if ( comments_open( $comment->comment_post_ID ) ) {
			$comment_actions = html( 'a',
				array(
					'class' => 'reply-link',
					'href' => sprintf(
						'mailto:{{{reply_to_comment_%s}}}?subject=%s',
						$comment->comment_ID,
						rawurlencode( sprintf( __( 'Reply to %s', 'Postmatic' ), $comment->comment_author ) )
					)
				),
				html(
					'img',
					array( 'src' => 'https://s3-us-west-2.amazonaws.com/postmatic/assets/icons/reply.png', 'width' => '13', 'height' => '8', )
				),
				__( 'Reply', 'Postmatic' )
			);
		}

		echo html( 'div class="comment-header"',
			get_avatar( $comment ),
			html( 'div class="author-name"',
				get_comment_author_link( $comment->comment_ID )
			),
			html( 'div class="comment-body"',
				apply_filters( 'comment_text', get_comment_text( $comment->comment_ID ), $comment ),
				$comment_actions
			)
		);
	}

	public static function render_text( $comment, $args, $depth ) {

		self::set_context( $comment );

		echo self::indent( '', $depth );

		echo self::indent(
			'- ' . $comment->comment_author . ' -',
			$depth
		);

		echo self::indent( Prompt_Html_To_Markdown::convert( wpautop( $comment->comment_content ) ), $depth );

		if ( comments_open( $comment->comment_post_ID ) ) {
			echo self::indent(
				sprintf(
					'%1$s: mailto:{{{reply_to_comment_%2$s}}}?subject=%3$s',
					sprintf( __( 'Reply to %s', 'Postmatic' ), $comment->comment_author ),
					$comment->comment_ID,
					rawurlencode( __( 'Reply', 'Postmatic' ) )
				),
				$depth
			);
		}
	}

	protected static function indent( $text, $depth ) {
		$lines = $text ? preg_split( '/$\R?^/m', $text ) : array( '' );
		$indented_text = '';
		foreach( $lines as $line ) {
			$indented_text .= str_repeat( '  ', $depth - 1 ) . $line . "\n";
		}
		return $indented_text;
	}

	protected static function set_context( $comment ) {

		if ( self::$post_id == $comment->comment_post_ID )
			return;

		self::$post_id = $comment->comment_post_ID;
		$prompt_post = new Prompt_Post( self::$post_id );

		$flood_comment_id = $prompt_post->get_flood_control_comment_id();
		if ( $flood_comment_id )
			self::$flood_comment = get_comment( $flood_comment_id );

	}

	protected static function base_classes( $comment ) {

		$classes = array();
		
		if ( 'parent' == $comment->comment_type ) {
			$classes[] = 'context-parent';
		}
		
		if ( ! self::$flood_comment ) {
			return implode( ' ' , $classes );
		}

		if ( self::$flood_comment->comment_ID == $comment->comment_ID ) {
			$classes[] = 'flood-point';
		}

		if ( self::$flood_comment->comment_date_gmt <= $comment->comment_date_gmt ) {
			$classes[] = 'post-flood';
		}

		return implode( ' ' , $classes );
	}

}