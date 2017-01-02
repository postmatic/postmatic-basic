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
	* {
        margin: 0;
        padding: 0;
        font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
        font-size: 100%;
        line-height: 1.6;
    }
    
    a {
        color: #404040;
    }

    a img {
        border: none;
    }

    img {
        outline: none;
        text-decoration: none;
        -ms-interpolation-mode: bicubic;
        width: auto;
        max-width: 100%;
        height: auto;
        display: block;
    }

    img.retina {
        width: 100%;
        max-width: 675px;
    }

    img.featured {
        width: 100%;
        max-width: 720px;
    }

    body {
        -webkit-font-smoothing: antialiased;
        -webkit-text-size-adjust: none;
        width: 100%;
        height: 100%;
    }
    
    hr {
        border: none;
        border-bottom: 1px solid #ddd;
        margin: 15px 0 !important;
        display: block;
    }

    /* -------------------------------------
        ELEMENTS
    ------------------------------------- */
    .padded {
        padding: 0 20px 20px 20px;
    }
    
    .padded.postmatic-header {
        padding: 0 20px 0 20px;
    }

    .gray {
        background: #f6f6f6;
        padding: 25px;
    }


    .padded h3.reply {
        clear: none;
    }

    #the_title {
        padding-bottom: 0;
    }
    
    #the_title small {
        display: block;
        font-size: 55%;
        margin-top: 0;
    }

    #button {
        clear: both;
        margin-top: 25px;
    }

    .btn-primary {
        text-decoration: none;
        color: #FFF;
        background-color: #348eda;
        border: solid #348eda;
        border-width: 10px 20px;
        line-height: 2;
        font-weight: bold;
        margin-right: 10px;
        margin-bottom: 10px;
        text-align: center;
        cursor: pointer;
        display: inline-block;
        border-radius: 25px;
        padding: 5px;
    }

    .btn-secondary {
        text-decoration: none;
        color: #FFF;
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
        padding: 5px;
    }

    .capitalize {
        text-transform: capitalize !important;
    }

    .last {
        margin-bottom: 0;
    }

    .logo {
        display: block;
        text-align: center;
        float: none;
        height: auto;
     
    }

    .brand {
        background: #fff;
        max-width: 760px;
        border-bottom: 0;
    }

    .brand img.favicon {
        width: 32px;
        float: left;
        margin-right: 10px;
    }

    .brand h2.sitename {
        padding: 0;
        margin-top: 0;
    }

    .commentheader td, .noheader {
        padding: 20px 20px 0 20px;
    }

    .post {
        border-top: 0;
    }

    .first {
        margin-top: 0;
    }

    .padding {
        padding: 10px 0;
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
    
    .gallery {
        width: auto !important;
    }

    .gallery-item, .ngg-gallery-thumbnail-box {
        float: left;
        margin: 0 !important;
    }
    

    .gallery-caption {
        margin 0;
    }

    .wp-caption-text.gallery-caption {
        width: 110px;
        font-size: 10px;
    }

    .wp-caption {
        max-width: 665px !important;
        height: auto;
    }

    .alignright .wp-caption-text {
        text-align: right;
        width: 100%;
        clear: right;
    }

    .alignright img {
        float: right;
    }
    
    .aligncenter .wp-caption-text, .aligncenter.wp-caption {
        width: auto;
    }

    /* -------------------------------------
        BODY
    ------------------------------------- */
    table {
        border-collapse: collapse;
    }

    table.wrap {
        width: 100%;
        border-collapse: collapse;
    }
    
    table.body {
         max-width: 722px;
        width: 100%;
        margin: 0 auto;
    }

    table.wrap .container {
        padding: 0;
        margin-bottom: 20px;
        border: 1px solid #f0f0f0;
        border-top: none;
    }

    table.header {
        padding: 0;
        border: 1px solid #f0f0f0;
        border-bottom: none;
        max-width: 722px;
        width: 100%;
        margin: 25px auto 0 auto;
        border-collapse: collapse;
    }

    #web {
        clear: both;
        padding-top: 20px;
    }

    /* -------------------------------------
        FOOTER
    ------------------------------------- */
    table.footer-wrap {
        width: 100%;
        clear: both;
        margin: 0 auto 0 auto;
        padding: 0 2%;
        max-width: 720px;
        font-size: 90% !important;
    }

    .footer-wrap .container * {
        font-size: 12px;
        margin-bottom: 0;
    }

    .credit a {
        color: #666;
    }
    
    
    .footer .gutter {
    display: flex;
    margin: 0 !important;
    border-top: 10px solid #F6F6F6;
    padding: 20px;
    font-size; 90%;
    }
	
 
    .footer .postmatic-widget {
      flex: 1 0 0px !important;
      padding: 2% !important;
    }
    


    .widgets h4 {
        font-size: 110%;
        font-weight: bold;
        margin: 0;
        padding: 0;
    }
    .widgets h4 a {
        text-decoration: none;
    }

    .sidebar.widgets .gutter {
        float: right;
        margin-left: 20px;
        width: 33%;
        margin-top: 55px;
        margin-right: 10px;
        font-size: 85% !important;
        background: #fff;
    }
    .sidebar h4 {
        border-bottom: 1px solid #ddd;
        margin-bottom: 5px;
    }
    
    .sidebar .postmatic-widget {
    margin-bottom: 25px;
    }
    

    
    .header.widgets {
        font-size: 90%;
        padding: 10px 15px;
        margin-bottom: 5px;
        background: #fff;
    }



    .credit {
        color: #666;
    }

    .footnote, .credit {
        clear: both;
        padding: 1% 0;
        font-size: 90%;
    }

    .utils h3, .utils h4 {
        font-size: 12px;
        margin-bottom: 5px !important;
    }

    .utils p {
        margin-bottom: 0;
    }

    .utils {
        margin-top: 15px;
        padding-left: 10px;
    }

    .utils a {
        color: black;
    }

    /* -------------------------------------
        TYPOGRAPHY
    ------------------------------------- */
    h1, h2, h3, h4, blockquote {
        margin-bottom: 15px;
        line-height: 1.2;
        font-weight: 200;
        margin-top: 15px;
    }

    h1 a {
        text-decoration: none;
    }

    h1 {
        font-size: 36px;
    }

    h2 {
        font-size: 28px;
    }

    h3 {
        font-size: 22px;
    }

    h4 {
        font-weight: normal;
        font-size: 16px;
        margin-bottom: 5px;
    }

    p, ul, ol {
        margin-bottom: 10px;
        font-weight: normal;
        font-size: 14px;
    }

    pre {
        display: block;
        font-family: courier;
        font-size: 10px !important;
        margin-bottom: 15px;
        max-width: 680px;
        overflow: hidden;
    }

    ul li, ol li {
        margin-left: 25px;
        list-style-position: inside;
    }

    ol li {
        list-style-type: decimal;
    }

   blockquote {
      background: #f9f9f9;
      border-left: 10px solid #ccc;
      margin: 1.5em 10px;
      padding: 0.5em 10px;
      quotes: "\201C""\201D""\2018""\2019";
    }
    blockquote:before {
      color: #ccc;
      content: open-quote;
      font-size: 4em;
      line-height: 0.1em;
      margin-right: 0.25em;
      vertical-align: -0.4em;
    }
    blockquote p {
      display: inline;
    }

    .alert {
        background: #FFFEBA;
        padding: 2px;
        font-weight: normal;
    }

    .noforward {
        background: #fffeee;
        padding: 2px;
    }

    .slideshowlink {
        margin: 15px 0;
        text-align: center;
    }

    .addtoany_list a {
        float: left;
    }

    /* ---------------------------------------------------
        RESPONSIVENESS
        Nuke it from orbit. It's the only way to be sure.
    ------------------------------------------------------ */
    /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
    .container {
        display: block;
        max-width: 720px;
        margin: 0 auto; /* makes it centered */
        clear: both;
    }

    /* Set the padding on the td rather than the div for Outlook compatibility */
    .wrap .container {
        padding: 20px;
    }

    .footer-wrap .container {
        padding: 0;
        max-width: 720px;
    }

    /* This should also be a block element, so that it will fill 100% of the .container */
    .content {
        max-width: 720px;
        margin: 0 auto;
        display: block;
    }

    .footer-wrap {
        padding: 0;
    }

    .footer-wrap .content {
        max-width: 720px;
    }

    /* Let's make sure tables in the content area are 100% wide */
    .content table {
        width: 100%;
    }

    .widgets li, .widgets ul {
        list-style: none;
        margin-left: 0;
        padding-left: 0;
        margin-bottom: 5px;
    }

    .alignright {
        float: right;
        margin: 0 0 10px 10px;
    }

    /*Sharedaddy by Jetpackm Juiz Social Share, Easy Social Share Buttons*/
    .sd-content ul li, ul.juiz_sps_links_list li, ul.essb_links_list li {
        list-style: none;
        display: inline;
    }

    .sd-title {
        clear: both;
    }

    .content .sd-content ul li, ul.juiz_sps_links_list li, .essb_links_list li {
        margin: 0 5px 10px 0;
        display: block;
        float: left;
    }

    .content .sd-content ul li a, ul.juiz_sps_links_list li a, .essb_links_list li a {
        color: #555;
        font-size: 12px;
        padding: 5px 8px;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
        border: 1px solid #bbb;
        background: #F8F8F8;
        text-decoration: none;

    }

    .sd-social-icon ul li span {
        display: none;
    }

    .sharedaddy {
        padding-bottom: 20px;
    }

    ul.essb_links_list {
        width: 100%;
    }

    .sd-social-icon .sd-content ul li a {
        width: 40px;
        padding: 0;
        height: 40px;
        display: block;
        border: none;
    }

    .sd-social-icon .sd-content ul li.share-email a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_em.gif);
        background-size: 100%;
    }

    .sd-social-icon .sd-content ul li.share-print a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_pr.gif);
        background-size: 100%;
    }

    .sd-social-icon .sd-content ul li.share-google-plus-1 a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_gp.gif);
        background-size: 100%;
    }

    .sd-social-icon .sd-content ul li.share-pinterest a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_pi.gif);
        background-size: 100%;
    }

    .sd-social-icon .sd-content ul li.share-reddit a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_re.gif);
        background-size: 100%;
    }

    .sd-social-icon .sd-content ul li.share-twitter a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_tw.gif);
        background-size: 100%;
    }

    .sd-social-icon .sd-content ul li.share-facebook a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_fb.gif);
        background-size: 100%;
    }

    .sd-social-icon .sd-content ul li.share-tumblr a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_tm.gif);
        background-size: 100%;
    }

    .sd-social-icon .sd-content ul li.share-linkedin a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_li.gif);
        background-size: 100%;
    }

    .sd-social-icon .sd-content ul li.share-pocket a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_po.gif);
        background-size: 100%;
    }

    .sd-social-icon .sd-content ul li.share-end a {
        background: url(http://assets.gopostmatic.com/assets/icons/jetpack/jp_re.gif);
        background-size: 100%;
    }

    .abuse {
        font-size: 85%;
    }

    #socialmedia-container div {
        float: left;
        margin-right: 10px;
    }

    .sd-like {
        display: none;
    }

    .ssba-wrap {
        display: block;
        min-height: 40px;
    }
    
    /*get rid of flare and digg digg and wpulike*/
    .flare-horizontal,.dd_post_share,.wpulike {display: none !important; height: 0 !important;}

    /*Jetpack tiled gallery*/
    .gallery-row {
        width: 675px;
        float: none;
    }

    .gallery-group {
        float: left;
        margin-bottom: 20px;
    }

    .tiled-gallery-caption {
        font-size: 70%;
        padding-left: 5px;
    }

    .tiled-gallery img {
        margin: 1px;
    }
    
    /*Postmatic Pays*/
    .postmatic_ad {
        text-align: center;
        margin: 25px auto;
        width: 95%;
    }
    
    .postmatic_ad:before {
        content: "Ads by Postmatic Pays";
        text-align: right;
        color: gray;
        font-weight: bold;
        font-size: 10px;
        margin-bottom: 5px;
        display: block;
        margin-right: 10px;
    }
    
    .pm_ad_1 img {
        margin: 0 auto;
        width: 100%;
    }
    
    .liveintent .primary_ad img {
        width: 100%;
        margin: 0 auto;
    }
    
    .secondary_ads img {
        
    }
    
    
    img.safe {
        width: auto;
    }

    /*Beta styles*/

    .inverse td {
        padding: 4%;
    }

    .inverse h3, .inverse h4 {
        margin: 0 0 5px 0;
    }

    .brand h1 {
        font-size: 42px;;
        padding: 0;
        margin: 3% 2%;
    }

    .left {
        float: left;
        margin: 0;
        width: 45%
    }

    .right {
        margin-left: 50%;
        width: 45%;
    }

    /*Plugin and shortcode specific*/
    .gallery, .ngg-galleryoverview {
        margin: 25px 0;
        padding: 15px;
        width: 100%;
        float: none;
    }

    .incompatible {
        background: #eee;
        border: 1px solid #ddd;
        padding: 15px;
        margin: 20px 0;
    }

    /*oembed placeholders*/

    .www-youtube-com, .animoto-com, .blip-tv, .www-collegehumor-com, .www-dailymotion-com, .flickr-com, .www-flickr-com, .www-funnyordie-com, .www-hulu-com, .embed-revision3-com, .www-ted-com, .vimeo-com, .vine-co, .wordpress-tv {
        background-image: url(http://assets.gopostmatic.com/assets/embed/video.jpg);
    }

    .www-mixcloud-com, .www-rdio-com, .www-soundcloud-com, .soundcloud-com, .w-soundcloud-com, .www-spotify-com {
        background-image: url(http://assets.gopostmatic.com/assets/embed/audio.jpg);
    }

    .issueembed, .embedarticles-com, .www-scribd-com, .www-slideshare-net {
        background-image: url(http://assets.gopostmatic.com/assets/embed/article.jpg);
    }

    .embed {
        width: 95%;
        overflow: hidden;
    }

    .incompatible.embed {
        background-color: #333;
        background-size: 100%;
        background-repeat: no-repeat;
        background-position: bottom center;
        width: 95%;
        overflow: hidden;
        height: 180px;
        padding-top: 150px;
        color: #fff;
        text-align: center;
    }

    .incompatible.embed a {
        display: block;
        width: 100%;
        height: 90%;
    }

    .et_social_inline {
        display: none;
    }
    
    /*Twitter embeds*/
    .twitter-tweet {
        border: 1px solid #E2E8ED;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        padding: 18px;
        background: #fff;
        quotes:none;
    }

    .twitter-tweet:before {
      color: #ccc;
      content: open-quote;
      font-size: 4em;
      line-height: 0;
      margin-right: 0;
      vertical-align: 0;
    }
    .twitter-tweet p {
      display: block;
    }

    /*zemanta related posts*/
    div.zem_rp_wrap {
        background: #eee;
        border: 1px solid #ddd;
        padding: 5px;
    }

    ul.related_post {
        margin: 0;
        padding: 0;
    }

    ul.related_post li {
        list-style: none;
        float: left;
        text-align: center;
        font-size: 85%;
    }

    .socialmedia-buttons a {
        float: left;
        display: block;
        margin-right: 5px;
    }

    .ssba a {
        float: left;
        margin-right: 5px;
    }

    /*flexible posts widget*/
    ul.dpe-flexible-posts li {
        clear: left;
        margin-bottom: 10px;
    }

    ul.dpe-flexible-posts li a img {
        width: 50px;
        height: auto;
        float: left;
        margin-right: 10px;
        margin-bottom: 10px;
    }

    .shopthepost-widget {
        display: none;
    }

    /*yumrecipes*/
    .blog-yumprint-recipe-published, .blog-yumprint-header, .blog-yumprint-adapted-print, .blog-yumprint-recipe-source {
        display: none;
    }

    .blog-yumprint-recipe {
        border: 1px dashed #ddd;
        border-radius: 5px;
        padding: 25px;
        margin: 10px;
    }

    .blog-yumprint-subheader {
        font-size: 150%;
        border-bottom: 1px solid #eee;
    }

    /*wp ultimate recipes*/
    .wpurp-responsive-desktop, .wpurp-recipe-image, .wpurp-recipe-servings-changer {
        display: none;
    }

    /*Hupso social*/
    .hupso_toolbar {
        display: none;
    }

    /*Official twitter plugin*/
    .twitter-share .twitter-share-button {
        color: #555;
        font-weight: bold;
        font-size: 12px;
        padding: 5px 8px;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
        border: 1px solid #bbb;
        background: #F8F8F8;
        text-decoration: none;
    }
    
/*Easy amazon*/
.easyazon-block {
    display: none !important;
    height: 0;
}
    
/*Better author bio*/
    #better-author-bio-div {
        margin: 25px 0;
        background: #fafafa !important;
        clear: both;
        padding: 15px;
}

    #better-author-bio-div img {
        float: left;
        margin-right: 15px;
}

    #better-author-bio-div h4 {
        margin-bottom: 0;
    }

    #better-author-bio-div ul {
        margin-left: 65px;
}
    #better-author-bio-div ul li {
        display: inline;
        margin: 0 10px 0 0;
        list-style: none;
}
    p.better-author-bio-div-meta {
    margin-left: 65px !important;
    margin-top: -15px !important;
    clear: none !important;
}

/*WP Author Box*/
    .a-tab-nav, .sab-social-wrapper {
        display: none !important;
        height: 0 !important;
    }
    
    .a-tab-container {
        padding: 15px;
        background: #fafafa;
    }
    
    .a-tab-content {
        margin-bottom: 25px !important;
    }
    
    .wpautbox-avatar {
        float: left;
        margin-right: 15px !important;
    }
    #wpautbox_about h4 {
        margin-bottom: 0;
        font-weight: bold;
    }
    .wpautbox-post_type-list {
        margin-left: 40px !important;
    }
    
    /*sexy author box*/
    
    div#sexy-author-bio {
        padding: 15px;
        background:  #fafafa;
    }
    
    div#sab-author {
        margin-bottom: 10px;
        text-align: left;
        margin-left: 65px;
    }
    
    div#sab-description {
        margin-left: 65px;
    }



    /*Social warfare*/
    .nc_socialPanel {
        margin: 25px 0;
        height: 30px;
        clear: both;
    }

    .nc_socialPanel .totes, .nc_socialPanel .sw_count {
        display: none;
    }

    .nc_socialPanel .nc_tweetContainer {
        float: left;
        width: auto;
        margin-right: 8px;
    }

    .nc_socialPanel a {
        display: block;
        -webkit-border-radius: 20px;
        -moz-border-radius: 20px;
        border-radius: 20px;
        color: #ffffff;
        background: #AAAAAA;
        padding: 5px 15px;
        text-decoration: none;
        font-weight: bold;
        font-size: 12px;

    }

    .nc_socialPanel a:hover {
        background-color: #343434;
    }

    .nc_tweetContainer.googlePlus a {
        background: #DF4B37;
    }

    .nc_tweetContainer.twitter a {
        background: #5FA8DC;
    }

    .nc_tweetContainer.fb a {
        background: #3A589E;
    }

    .nc_pinterest {
        display: none;
    }

    .nc_tweetContainer.linkedIn a {
        background: #0D77B7;
    }

    a.sw_CTT {
        display: block;
        margin: 25px 0;
        border: 1px solid #ddd;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        padding: 15px;
        font-size: 115%;
        letter-spacing: 1px;
        line-height: 100%;
        color: gray;
        text-decoration: none;
    }

    a.sw_CTT span.sw-ctt-text {
        color: gray;
        font-style: italic;
    }

    a.sw_CTT span.sw-ctt-btn {
        display: block;
        text-align: right;
        text-transform: uppercase;
        font-size: 60%;
        font-weight: bold;
        letter-spacing: normal;
    }

    .widgets-list-layout li {
        clear: left;
    }

    .widgets-list-layout li img {
        width: 35px;
        height: 35px;
        float: left;
        margin: 0 10px 10px 0;
    }

    /*Darth vader*/
    .darth_vendor_container a {
        display: block;
        float: left;
        width: 48%;
        border: 1px solid #F6F6F6;
        box-sizing: border-box;
    }

    /*Juiz social share*/
    .juiz_sps_maybe_hidden_text {
        display: none;
    }

    .pmcc-comments-report-link {
        display: none;
    }

    /*Mobile syles*/
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
        
        .padded {padding: 2% !important;}

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
        
        #comments { margin: 0 !important;}
        
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

    /*Comments Template*/
    img.avatar {
        width: 48px;
        height: 48px;
        max-height: 48px;
        float: left;
        padding-bottom: 0;
        border-radius: 5px;
    }
    
    img.wp-smiley {
        display: inline !important;
    }

    .inreply {
        font-weight: normal;
        font-size: 120%;
        color: #737373;
        margin-bottom: 15px;
    }

    .inreply a {
        text-decoration: none;
        font-style: italic;
    }
    
    .author-name {display:inline;  margin-left: 12px;}
    
    .comment-date {text-align: right; margin-right: 10px; float: right;}
    
    .comment-date a {font-size: 80%; color: gray; text-decoration: none;}

    .comment-body {
        margin-left: 60px;
    }

    .author-name, .author-name a {
        font-style: italic;
        font-family: serif;
        line-height: normal;
    }

    .comment {
        font-size: 100%;
        line-height: normal;
        min-height: 55px;
        clear: left;
    }

    .comment-date {
        color: gray;
        font-size: 90%;
    }

    .comment-header {
        padding-bottom: 0;
        font-size: 100%;
        margin-bottom: 20px;
    }

    .comment-body {
        color: #000;
    }

    .rejoin .comment-header {
        opacity: 0.5;
    }

    .rejoin .comment.post-flood .comment-header {
        opacity: 1.0;
    }

    .reply {
        padding-bottom: 15px;
        margin-left: 20px;
        clear: none;
    }
    
    .reply a {text-decoration: none;}
    
    .reply small {
        display: block;
        margin-left: 20px;
    }

    .newpost {
        border-bottom: none;
        margin-top: 25px;
        padding-bottom: 15px;
    }

    .reply-prompt {
        clear: both;
        margin-top: 0px;
        margin-bottom: 20px;
    }

    .reply-prompt img {
        float: left;
        margin-right: 10px;
        width: 30px;
        height: 30px;
    }

    .reply-prompt h3 {
        font-size: 100%;
        padding-top: 5px;
        clear: none;
    }

    .reply-prompt h3 small {
        display: block;
        font-size: 90%;
    }

    .reply-prompt p {
        margin-bottom: 0;
    }

    .online-prompt {
        clear: both;
        margin-top: 10px;
        margin-bottom: 10px;
        margin-left: 40px;
    }

    .previous-comments {
        margin: 15px 0 0 0;
        padding: 0;
        background: #f6f6f6;
        clear: both;
    }
    
    .previous-comments.padded {
        padding 2%;
    }

    .previous-comment-3 {
        opacity: .4;
    }

    .previous-comment-2 {
        opacity: .6;
    }

    .previous-comment-1 {
        opacity: .8;
    }

    /*.new-reply {margin-left: 55px; margin-bottom: 25px; font-size: 115%;}*/
     .depth-2 {
        margin-left: 25px;
        margin-bottom: 15px;
    }

    .depth-3 {
        margin-left: 25px;
        margin-bottom: 15px;
    }

    .depth-4 {
        margin-left: 25px;
        margin-bottom: 15px;
    }

    .depth-5 {
        margin-left: 25px;
        margin-bottom: 15px;
    }


    div.bypostauthor > div:first-child {
        background: url(https://s3-us-west-2.amazonaws.com/postmatic/assets/icons/et.png);
        padding: 4px;
    }
    .the-reply, {
        margin-bottom: 25px;
    }

    .reply-content {
        margin-left: 60px;
    }

    .comment blockquote, .previous-comments blockquote, .reply-content blockquote {
        background: #fff;
        border: none;
        border-left: 3px solid #ddd;
        padding: 0;
        padding-left: 10px;
        font-weight: normal;
    }

    .context {
        font-size: 90%;
        line-height: normal;
        margin-bottom: 45px;
    }

    .context .excerpt {
        font-style: italic;
    }

    .context h4 {
        margin-bottom: 10px;
    }

    .context img {
        float: left;
        margin-right: 15px;
        padding: 0px;
        border: 1px solid #ddd;
        margin-bottom: 25px;
    }
    
    .summary {
        clear: left;
    }

    a.reply-link {
        text-decoration: none;
        font-size: 11px !important;
        margin-top: 5px;
        color: gray !important;
    }
    
    .reply-link img {float: left; width: 13px; height: 8px; margin: 5px 5px 0 0; clear: none; border: 0 !important;}
    
    #comments {
        padding: 5px 0 5px 0;
        margin: 25px 0 10px 0;
    }
    
    #comments p:last-of-type {
        margin-bottom: 0 !important;
    }
    
    #comments.comment-digest {
        margin: 25px 0 10px 0;
    }
    
    .comment-digest-intro {
        margin-bottom: 20px;
    }
    
    .comment-digest-intro img {
        width: 150px !important;
        height: auto !important;
    }
    
    .comment-digest-intro h4 {
        margin-top: 0;
        padding-top: 0;
    }
    
    .comment-digest-intro img {
        float: right;
        margin: 0 0 25px 15px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
    }
    
    .contextual,.contextual a,.contextual p  {
      color: rgba(0, 0, 0, 0.3) !important;
    }

    .contextual img {
      opacity: .5;
    }
    
    .contextual .featured,.contextual .featured a,.contextual .featured p  {
      color: rgba(0, 0, 0,1.0) !important;
    }
    
    .contextual .featured img {
      opacity: 1.0;
    }
.author_message {
    padding: 10px; background: #FFFBCC; font-weight: bold; margin: 15px 0; border-top: 1px dashed #ddd; border-bottom: 1px dashed #ddd; font-size: 11px;
}

	</style>
</head>
<body bgcolor="#ffffff">
   <table class="header wrap commentheader">
        <tr>
            <td class="brand" bgcolor="#FFFFFF">
                <img width="32" height="32" src="<?php echo $site_icon_url; ?>" class="favicon"/>

                <h2 class="sitename"><?php echo $brand_text; ?></h2>
            </td>
        </tr>
    </table>

<table class="body wrap">
    <tr>
        <td class="post container" bgcolor="#FFFFFF">
            
           <!-- content -->
            <div class="content">
                <table>

                    <tr>
                        <td>
                            <?php echo $html_content; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
        <td></td>
    </tr>
</table><!-- /body --><!-- footer -->


<table class="footer-wrap">
    <tr>
        <td class="container">
                  <tr>
                      <td class="footnote">
                          <?php echo $footer_text; ?>
                          <?php echo $footnote_html; ?>
                      </td>
                  </tr>

                  <tr>
                      <td class="credit">
                          <?php echo $credit_html; ?>
                      </td>
                      <td>
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
                      </td>
                  </tr>
        </td>
    </tr>
</table><!-- /footer -->

</body>
</html>