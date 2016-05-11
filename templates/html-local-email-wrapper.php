<?php
/**
 * HTML Email template, called with variables in scope:
 *
 * @var string $subject
 * @var string $html_content
 * @var string $brand_type text or html
 * @var string $brand_text
 * @var string $brand_image_url
 * @var int    $brand_image_height
 * @var int    $brand_image_width
 * @var string $footnote_html
 * @var string $footnote_text
 * @var string $credit_html
 * @var string $credit_text
 * @var string $footer_widgets
 * @var string $footer_type
 * @var string $footer_text
 * @var string $site_icon_url
 * @var string $unsubscribe_url
 * @var bool   $will_strip_content
 * @var string $site_css
 */
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title><?php bloginfo( 'name' ); ?> | <?php echo esc_html( $subject ); ?></title>
	<style>
		body {
			line-height: normal;
			font-family: sans-serif;
			background-color: #ffffff;
		}

		a {
			color: #2980b9;
		}

		h1 {
			font-weight: normal;
			line-height: normal;
		}
		
		h2 {line-height: normal;}

		h1 a {
			text-decoration: none;
		}

		p.padded {
			font-size: 85%;
		}

		blockquote {
			clear: both;
		}

		.btn-secondary {
			text-decoration: none;
			color: #FFF !important;
			background-color: #aaa;
			border: solid #aaa;
			border-width: 5px 10px;
			line-height: 2;
			font-weight: normal;
			margin-right: 10px;
			text-align: center;
			cursor: pointer;
			display: inline-block;
			border-radius: 15px;
			margin-top: 10px;
		}

		.comment-body {
			font-size: 110%;
			padding-bottom: 10px;
			border-bottom: 1px solid #ddd;
		}

		
		 .alignright {
	        float: right;
	        margin: 0 0 20px 20px;
	    }
	
	    .alignleft {
	        float: left;
	        margin: 0 20px 20px 0;
	    }
	
	    .aligncenter, .alignnone {
	        margin: 20px auto;
	        display: block;
	        float: none;
	        width: auto;
	    }
	    
	    #content {
	    	max-width: 600px !important;
	    	overflow: hidden !important;
	    }
	      .utils {
 		   margin-top:65px;
   		 margin-bottom: 65px;
		}
		
		.reply {
		    margin-left: 45px;
		    line-height: normal;
		}
		
		.reply a {
			text-decoration: none;
		}
		
		.reply small {
		    font-weight: normal;
		    font-size: 12px;
		}
		
		.foot_wrap {
		    font-size: 12px;
		    text-align: left;
		    width: 600px;
		    line-height: normal
		}

		<?php echo $site_css; ?>
	</style>
</head>
<body bgcolor="#ffffff">
<?php if ( !empty( $will_strip_content ) ) : ?>
	<p style="background:#F6F6F6; padding: 5px; border: 1px dotted #dddddd; margin-bottom: 15px; font-size: 90%;">
		<?php
		_e( 'This post contains images and other content which are not available in the email version.', 'Postmatic' );
		?>
		 <br/>
		<a href="<?php the_permalink(); ?>">
			<?php _e( 'Click here to view the full post in your browser', 'Postmatic' ); ?>
		</a>
	</p>
<?php endif; ?>
<h2 class="site-title"><?php echo $brand_text; ?></h2>

<div id="content" style="margin-top: 15px;"><?php echo $html_content; ?></div>

<span id="postmatic-ref-{{{ref_id}}}"></span>
<div class="foot_wrap">
<div class="footer"><?php echo $footer_text; ?></div>

<?php if ( empty( $suppress_delivery ) ) : ?>

	<div class="footnote"><?php echo $footnote_html; ?></div>

	<div class="credit"><?php echo $credit_html; ?></div>

	<?php if ( !empty( $unsubscribe_url ) ) : ?>
		<p>
			<unsubscribe>
				<?php
				printf(
					__(
						'To immediately stop receiving all posts and comments from %s you can <a href="%s">unsubscribe with a single click</a>.',
						'Postmatic'
					),
					get_bloginfo( 'name' ),
					$unsubscribe_url
				);
				?>
			</unsubscribe>
		</p>
	<?php endif; ?>

<?php endif; ?>
</div>

</body>
</html>