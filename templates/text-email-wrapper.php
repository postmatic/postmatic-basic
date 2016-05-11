<?php
/**
 * Text email template
 *
 * @var string $brand_text
 * @var string $text_content
 * @var string $footer_text
 * @var string $footnote_text
 * @var string $credit_text
 * @var string $unsubscribe_url
 */
?>

··· <?php echo $brand_text; ?> ···

<?php echo $text_content; ?>

<?php echo $footer_text; ?>


<?php if ( ! empty( $unsubscribe_url ) ) : ?>
<?php
printf(
	__( 'To stop receiving email from %s visit:', 'Postmatic' ),
	get_bloginfo()
);
?>

<?php echo $unsubscribe_url; ?>
<?php endif; ?>

<?php echo $footnote_text; ?>

<?php echo $credit_text; ?>

http://gopostmatic.com
postmatic-ref-{{{ref_id}}}



