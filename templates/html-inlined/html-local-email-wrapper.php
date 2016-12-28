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

<html xmlns="http://www.w3.org/1999/xhtml" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
<head>
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title><?php bloginfo( 'name' ); ?> | <?php echo esc_html( $subject ); ?></title>
	<style>

<?php include( dirname( __FILE__ ) . '/unlined.css' ); ?>

@media only screen and (max-width: 480px) {
  #the_title {
    font-size: 17px !important;
    line-height: normal !important;
  }
  table.wrap {
    width: 100% !important;
    padding: 0% !important;
  }
  table.wrap .container, .wrap {
    padding: 0;
    border: 0 !important;
  }
  .padded {
    padding: 2% !important;
  }
  .header {
    border: 0 !important;
  }
  .credit {
    text-align: left !important;
  }
  .left, .right {
    width: 100% !important;
    float: none !important;
  }
  .padding img {
    width: auto !important;
    height: auto !important;
  }
  .widgets {
    padding: 0 !important;
  }
  .midwidget {
    padding: 0 !important;
  }
  .wrap {
    padding: 0 !important;
  }
  .logo {
    margin: 20px 20px 15px 20px;
    width: 80%;
    max-width: 90%;
  }
  #content img {
    float: none !important;
    margin: 10px auto !important;
  }
  img.avatar {
    float: left !important;
  }
  #content img.avatar, #content img.reply-icon {
    float: left !important;
    clear: left !important;
    margin: 0 10px 0 0 !important;
  }
  .gallery br {
    display: none !important;
    clear: none !important;
  }
  .gallery-item, .ngg-gallery-thumbnail-box {
    margin: 5px auto !important;
    float: none !important;
    display: block !important;
    width: 100% !important;
    text-align: center !important;
  }
  .gallery-item img, .ngg-gallery-thumbnail-box img {
    margin: 0 auto !important;
    display: block !important;
  }
  .gallery-caption {
    width: auto !important;
    text-align: center !important;
  }
  .sd-social-icon .sd-content ul li a {
    width: 20px !important;
    height: 20px !important;
  }
  #comments {
    margin: 0 !important;
  }
  .depth-2 {
    margin-bottom: 15px !important;
    border-left: 1px solid #ddd !important;
    padding-left: 5px !important;
  }
  .depth-3 {
    margin-left: 10px !important;
    margin-bottom: 15px !important;
    border-left: 1px solid #ddd !important;
    padding-left: 5px !important;
  }
  .depth-4 {
    margin-left: 10px !important;
    margin-bottom: 15px !important;
    border-left: 1px solid #ddd !important;
    padding-left: 5px !important;
  }
  .depth-5 {
    margin-left: 10px !important;
    margin-bottom: 15px !important;
    border-left: 1px solid #ddd !important;
    padding-left: 5px !important;
  }
  div.bypostauthor > div:first-child {
    background: url(https://s3-us-west-2.amazonaws.com/postmatic/assets/icons/et.png) !important;
    padding: 4px !important;
  }
  .comment-date {
    float: none !important;
    text-align: left !important;
    margin-right: 0 !important;
    margin-left: 12px !important;
  }
  .footer .gutter {
    display: block !important;
  }
  .reply-content {
    margin-left: 25px;
  }
  h3.reply {
    margin-left: 40px !important;
  }
  h3.reply small {
    margin-left: 0 !important;
  }
}

<?php echo $site_css; ?>
</style>
</head>
<body bgcolor="#ffffff" style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100%; height: 100%;">
   <table class="header wrap commentheader" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 25px auto 0 auto; padding: 0; border-collapse: collapse; width: 100%; border: 1px solid #f0f0f0; border-bottom: none; max-width: 722px;" width="100%">
        <tr style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
            <td class="brand" bgcolor="#FFFFFF" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; background: #fff; max-width: 760px; border-bottom: 0; padding: 20px 20px 0 20px;">
                <img width="32" height="32" src="<?php echo $site_icon_url; ?>" class="favicon" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; display: block; max-width: 100%; height: auto; width: 32px; float: left; margin-right: 10px;">

                <h2 class="sitename" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 28px; line-height: 1.2; margin-bottom: 15px; font-weight: 200; padding: 0; margin-top: 0;"><?php echo $brand_text; ?></h2>
            </td>
        </tr>
    </table>

<table class="body wrap" style="padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0 auto; border-collapse: collapse; width: 100%; max-width: 722px;" width="100%">
    <tr style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
        <td class="post container" bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0 auto; clear: both; max-width: 720px; display: block; border: 1px solid #f0f0f0; margin-bottom: 20px; padding: 0; border-top: none;">
            
           <!-- content -->
            <div class="content" style="padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0 auto; max-width: 720px; display: block;">
                <table style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; border-collapse: collapse; width: 100%;" width="100%">

                    <tr style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
                        <td style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
                            <?php echo $html_content; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
        <td style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;"></td>
    </tr>
</table><!-- /body --><!-- footer -->


<table class="footer-wrap padded" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; border-collapse: collapse; margin: 10px auto 0 auto; padding: 0 2%; font-size: 90%; width: 100%; clear: both; max-width: 720px;" width="100%">
    <tr style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
        <td class="container" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6; margin: 0 auto; display: block; clear: both; padding: 0; max-width: 720px;">
                  </td></tr><tr style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
                      <td class="footnote" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; padding: 1% 0; font-size: 90%; clear: both;">
                          <?php echo $footer_text; ?>
                          <?php echo $footnote_html; ?>
                      </td>
                  </tr>

                  <tr style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
                      <td class="credit" style="margin: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; padding: 1% 0; font-size: 90%; color: #666; clear: both;">
                          <?php echo $credit_html; ?>
                      </td>
                      <td style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
                      <?php if ( !empty( $unsubscribe_url ) ) : ?>
						<p style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; line-height: 1.6; font-size: 14px; margin-bottom: 10px; font-weight: normal;">
							<unsubscribe style="margin: 0; padding: 0; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 100%; line-height: 1.6;">
								<?php
								printf(
									__(
										'To immediately stop receiving all posts and comments from %s you can <a href="%s">unsubscribe with a single click.',
										'Postmatic'
									),
									get_bloginfo( 'name' ),
									$unsubscribe_url
								);
								?>
							</unsubscribe>
						</p>
					<?php endif; ?>
                      </td>
                  </tr>
        
    
</table><!-- /footer -->

</body>
</html>