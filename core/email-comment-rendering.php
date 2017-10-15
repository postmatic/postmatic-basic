<?php

/**
 * Comment walker callback for email rendering
 * @since 1.0.0
 */
class Prompt_Email_Comment_Rendering {

	/**
	 * @since 1.0.0
	 * @var int
	 */
	protected static $post_id;
	/**
	 * @since 1.0.0
	 * @var int
	 */
	protected static $flood_comment;
	/**
	 * @since 2.0.6
	 * @var array
	 */
	protected static $comment_classes = array();

	/**
	 * Render HTML for a single comment in a thread.
	 *
	 * @since 1.0.0
	 * @param object|WP_Comment $comment
	 * @param array $args
	 * @param int $depth
	 */
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
					'href' => self::reply_url( $comment ),
				),
				html(
					'img',
					array( 'src' => 'https://s3-us-west-2.amazonaws.com/postmatic/assets/icons/reply.png', 'width' => '13', 'height' => '8', )
				),
				__( 'Reply', 'Postmatic' )
			);
		}

		$comment_text = apply_filters( 'comment_text', get_comment_text( $comment->comment_ID ), $comment );

		$comment_text = Prompt_Formatting::escape_handlebars_expressions( $comment_text );

		echo html( 'div class="comment-header"',
			get_avatar( $comment ),
			html( 'div class="author-name"',
				get_comment_author_link( $comment->comment_ID )
			),
			html( 'div class="comment-date"',
				html( 'a',
					array( 'href' => get_comment_link( $comment ) ),
					get_comment_date( '', $comment ),
					' ',
					mysql2date( get_option( 'time_format' ), $comment->comment_date )
				)
			),
			html( 'div class="comment-body"',
				$comment_text,
				$comment_actions
			)
		);
	}

	/**
	 * Render text for a single comment in a thread.
	 * @since 1.0.0
	 * @param object|WP_Comment $comment
	 * @param array $args
	 * @param int $depth
	 */
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

	/**
	 * Provide a base class to apply to a set of comments.
	 *
	 * @since 2.0.6
	 * @param array $comments
	 * @param string $class
	 */
	public static function classify_comments( array $comments, $class ) {
		self::$comment_classes[$class] = wp_list_pluck( $comments, 'comment_ID' );
	}

	/**
	 * Return a mailto or comment URL based on email transport.
	 *
	 * @since 2.1.0
	 * @param Wp_Comment $comment
	 * @return string
	 */
	protected static function reply_url( $comment ) {
		if ( Prompt_Core::is_api_transport() ) {
			return sprintf(
				'mailto:{{{reply_to_comment_%s}}}?subject=%s',
				$comment->comment_ID,
				rawurlencode( sprintf( __( 'Reply to %s', 'Postmatic' ), $comment->comment_author ) )
			);
		}

		return get_comment_link( $comment );
	}

	/**
	 * @since 1.0.0
	 * @param string $text
	 * @param int $depth
	 * @return string
	 */
	protected static function indent( $text, $depth ) {
		$lines = $text ? preg_split( '/$\R?^/m', $text ) : array( '' );
		$indented_text = '';
		foreach ( $lines as $line ) {
			$indented_text .= str_repeat( '  ', $depth - 1 ) . $line . "\n";
		}
		return $indented_text;
	}

	/**
	 * @since 1.0.0
	 * @param object|WP_Comment $comment
	 */
	protected static function set_context( $comment ) {

		if ( self::$post_id == $comment->comment_post_ID )
			return;

		self::$post_id = $comment->comment_post_ID;
		$prompt_post = new Prompt_Post( self::$post_id );

		$flood_comment_id = $prompt_post->get_flood_control_comment_id();
		if ( $flood_comment_id )
			self::$flood_comment = get_comment( $flood_comment_id );

	}

	/**
	 * Get appropriate classes for the comment container element
	 * @since 1.0.0
	 * @param object|WP_Comment $comment
	 * @return string
	 */
	protected static function base_classes( $comment ) {

		$classes = array();

		foreach ( self::$comment_classes as $class => $ids ) {
			if ( in_array( $comment->comment_ID, $ids ) ) {
				$classes[] = $class;
			}
		}

		if ( ! self::$flood_comment ) {
			return implode( ' ', $classes );
		}

		if ( self::$flood_comment->comment_ID == $comment->comment_ID ) {
			$classes[] = 'flood-point';
		}

		if ( self::$flood_comment->comment_date_gmt <= $comment->comment_date_gmt ) {
			$classes[] = 'post-flood';
		}

		return implode( ' ', $classes );
	}

}