<?php


class Prompt_Optins_Style {


	public static function themes() {
		return array(
			'light' => array(
				'colors' => array(
					'background'   => '#fff',
					'background-image' => 'e-black-50.png',
					'button-background' => '#b7b7b7',
					'accent' => '#d7d7d7',
					'button-type' => '#000',
					'type' => '#4F4F4F',
					'grad-start' => 'RGB(255,255,255)',	
					'grad-end' => 'RGB(237,237,237)',
					'border-color' => '#ddd',
				),
				'label' => __( 'Light', 'Postmatic' ),
			),
			'dark' => array(
				'colors' => array(
					'background' => '#000',
					'background-image' => 'e-white-50.png',
					'button-background' => 'RGB(229, 124, 88)',
					'accent' => '#2f2f2f',
					'button-type' => '#fff',
					'type' => '#fff',
					'grad-end' => 'RGB(54, 54, 54)',	
					'grad-start' => 'RGB(66,66,66)',
					'border-color' => '#000',
				),
				'label' => __( 'Dark', 'Postmatic' ),
			),
			'spruce' => array(
				'colors' => array(
					'background' => '#727362',
					'background-image' => 'e-white.png',
					'button-background' => '#883435',
					'accent' => '#9D9D8A',
					'button-type' => '#fff',
					'type' => '#fff',
					'grad-start' => '#727362',	
					'grad-end' => '#727362',
					'border-color' => '#727362',
				),
			'label' => __( 'Spruce', 'Postmatic' ),
			),
			'orange' => array(
				'colors' => array(
					'background' => '#EB593C',
					'background-image' => 'e-white.png',
					'button-background' => '#4F4F4F',
					'accent' => '#F0F0F0',
					'button-type' => '#fff',
					'grad-start' => 'RGB(235,138,60)',	
					'grad-end' => 'RGB(239,89,60)',
					'type' => '#fff',
					'border-color' => 'gray',
				),
				'label' => __( 'Orange', 'Postmatic' ),
			),
			'blue' => array(
				'colors' => array(
					'background' => 'RGB(44, 148, 242)',
					'background-image' => 'e-white.png',
					'button-background' => 'RGB(35, 117, 192)',
					'accent' => '#273038',
					'button-type' => '#fff',
					'type' => '#fff',
					'grad-start' => 'RGB(44, 148, 242)',	
					'grad-end' => 'RGB(97, 175, 232)',
					'border-color' => 'black',
				),
				'label' => __( 'Blue', 'Postmatic' ),
			),
			'green' => array(
				'colors' => array(
					'background' => 'RGB(146, 201, 39)',
					'background-image' => 'e-white.png',
					'button-background' => 'RGB(116, 160, 31)',
					'accent' => 'RGB(100, 100, 100)',
					'button-type' => '#fff',
					'type' => '#fff',
					'grad-end' => 'RGB(146, 201, 39)',	
					'grad-start' => 'RGB(167, 211, 82)',
					'border-color' => 'RGB(167, 211, 82)',
				),
				'label' => __( 'Green', 'Postmatic' ),
			),
			'lilac' => array(
				'colors' => array(
					'background' => '#eaecee',
					'background-image' => 'e-white.png',
					'button-background' => '#967095',
					'accent' => '#eaecee',
					'button-type' => '#fff',
					'type' => '#171717',
					'grad-end' => '#eaecee',	
					'grad-start' => '#ffffff',
					'border-color' => '#eaecee',
				),
				'label' => __( 'Lilac', 'Postmatic' ),
			),
		);
	}



	public static function make_css( $type, $theme, $custom_image_url ) {
		$background_image_url = $custom_image_url;
		if ( ! $background_image_url ) {
			$background_image_url = Prompt_Core::$url_path . '/media/optins/{{background-image}}';
		}
		$css = self::$type( $background_image_url );
		$themes = self::themes();
		$theme = $themes[ $theme ];
		$colors = $theme[ 'colors' ];
		foreach( $colors as $color => $hex ) {
			$color = '{{' . $color . '}}';
			$css = str_replace( $color, $hex, $css );
		}

		return self::reset() . $css;

	}
	
	protected static function reset() {
		ob_start(); ?>
		.postmatic-optin-widget div, .postmatic-optin-widget span, .postmatic-optin-widget h2, .postmatic-optin-widget h3, .postmatic-optin-widget h4, .postmatic-optin-widget p, .postmatic-optin-widget a, .postmatic-optin-widget font, .postmatic-optin-widget img, .postmatic-optin-widget strike, .postmatic-optin-widget strong, .postmatic-optin-widget b, .postmatic-optin-widget u, .postmatic-optin-widget i, .postmatic-optin-widget ol, .postmatic-optin-widget ul, .postmatic-optin-widget li, .postmatic-optin-widget label, .postmatic-optin-widget input { text-transform: none; font-weight: normal; margin: 0; padding: 0; border: 0; outline: 0; font-size: 100%; vertical-align: baseline; background-image:none; -webkit-box-sizing: content-box; -moz-box-sizing: content-box; box-sizing: content-box; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        .postmatic-optin-widget button { background: none; }
		<?php
		return ob_get_clean();
	}

	protected static function popup( $background_image_url ) {
		ob_start(); ?>
		#postmatic-widget-popup_calderaModal {
		padding: 35px 25px 0 25px !important;
		width: 680px !important;;
		min-height: 315px !important;
		text-align:left !important;
		overflow:visible !important;
		line-height:115% !important;
		background: {{grad-start}} !important;
		background: -moz-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -webkit-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -o-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -ms-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: linear-gradient(180deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		}
		
		#postmatic-widget-popup_calderaModal a {
		color: {{type}} !important;
		text-decoration: underline !important;
		}
		
		#postmatic-widget-popup_calderaModal h2, #postmatic-widget-popup_calderaModal h3, #postmatic-widget-popup_calderaModal h4 {
		color: {{accent}};
		}
		#postmatic-widget-popup_calderaModal h2 {
		font-size: 140% !important;
		}
		
		#postmatic-widget-popup_calderaModal h3 {
		font-size: 125% !important;
		}
		
		#postmatic-widget-popup_calderaModal h4 {
		font-size: 110% !important;
		}
		
		#postmatic-widget-popup_calderaModal ul, #postmatic-widget-popup_calderaModal ol {
		margin-left: 45px;
		}
		
		#postmatic-widget-popup_calderaModal .prompt-subscribe-widget-content {
		display: block !important;
		}
		
		#postmatic-widget-popup_calderaModal input.placeholder {
		font-family: inherit;
		}
		
		#postmatic-widget-popup_calderaModal div.widget_prompt_subscribe_widget {
		background: url(<?php echo $background_image_url ?>) no-repeat !important;
		background-size: 150px !important;
		min-height: 150px !important;
		}
		
		#postmatic-widget-popup_calderaModal * {
		font-size: 16px !important;
		}
		
		#postmatic-widget-popup_calderaModal .caldera-modal-content {
		margin: 0;
		}
		
		#postmatic-widget-popup_calderaModal h2.widgettitle {
		color: {{type}} !important;
		margin: 0 0 15px 175px !important;
		font-size: 180% !important;
		padding: 0 !important;
		text-align: left !important;
		}

		#postmatic-widget-popup_calderaModal div.primary.prompt {
		margin: 0 0 25px 175px !important;
		font-size: 110% !important;
		min-height: 130px !important;
		line-height: normal !important;
		}
		
		
		#postmatic-widget-popup_calderaModal div.subscribe {
		color: {{type}} !important;
		}
		
		#postmatic-widget-popup_calderaModal input.submit {
		padding: 7px 1%;
		text-align: center !important;
		border: 1px solid {{button-background}} !important;
		background: {{button-background}} !important;
		-webkit-border-radius: 5px !important;
		-moz-border-radius: 5px !important;
		border-radius: 5px !important;
		color: {{button-type}} !important;
		font-weight: normal !important;
		height: 34px !important;
		width: 15% !important;
		margin-top: 3px !important;;
		}
		
		body.logged-in 	#postmatic-widget-popup_calderaModal input.submit {
		margin-left: 175px !important;
		}
		
		#postmatic-widget-popup_calderaModal input.prompt-subscribe-email,#postmatic-widget-popup_calderaModal input.prompt-subscribe-name {
		padding: 7px;
		text-align: left !important;
		background: #fff !important;
		-webkit-border-radius: 5px !important;
		-moz-border-radius: 5px !important;
		border-radius: 5px !important;
		margin-top: 3px !important;
		color: #343434 !important;
		width: 35% !important;
		margin-right: 1% !important;
		border-color: {{border-color}} !important;
		border-width: 1px !important;
		border-style: solid !important;
		height: 35px !important;
		float: left !important;
		}
		
		#postmatic-widget-popup_calderaModal .message {
		color: {{type}} !important;
		font-size: 100% !important;
		margin-left: 175px !important;
		margin-bottom: 25px !important;
		}
		
		#postmatic-widget-popup_calderaModal div.loading-indicator {
		left: 270px !important;
		width: 30px !important;
		min-height: 0;
		}
		


		#postmatic-widget-popup_calderaModal .caldera-modal-closer {
		display: block !important;
		width: 35px !important;
		height: 35px !important;
		background: url(<?php echo Prompt_Core::$url_path; ?>/media/optins/close.png) !important;
		background-size: 35px !important;
		right: -11px !important;
		top: -9px !important;
		overflow: visible !important;
		}
		
		#postmatic-widget-popup_calderaModal .inputs {
		width: 100% !important;
		}
		
		body.logged-in #postmatic-widget-popup_calderaModal .inputs {
		display: inline !important;
		}
		
		#postmatic-widget-popup_calderaModal .unsubscribe {
		margin-left: 175px !important;
		color: {{type}} !important;
		margin-bottom: 25px !important;
		}
		
		@media only screen and (max-width : 550px) {
		
		
		#postmatic-widget-popup_calderaModal {
		padding: 7% !important;
		width:auto !important;
		height: auto !important;
		}
		
		
		#postmatic-widget-popup_calderaModal div.widget_prompt_subscribe_widget {
		background: url(<?php echo $background_image_url ?>) no-repeat !important;
		background-size: auto 150px !important;
		background-position: top center !important;
		height: 100% !important;
		border: 1px solid transparent !important;
		}
		
		#postmatic-widget-popup_calderaModal h2.widgettitle {
		color: {{type}} !important;
		margin: 175px 0 15px 0 !important;
		line-height: normal !important;
		text-align: center !important;
		}

		#postmatic-widget-popup_calderaModal div.primary.prompt {
		margin: 0 0 25px 0 !important;
		font-size: 110% !important;
		min-height: 0 !important;
		text-align:center !important;
		}
		
		#postmatic-widget-popup_calderaModal .message {
		margin-left: 0 !important;
		margin-bottom: 25px !important;
		}
		
		#postmatic-widget-popup_calderaModal div.loading-indicator {
		left: 45% !important;
		margin: 0 auto !important;
		width: 30px !important;
		text-align: center !important;
		}
		
		.caldera-backdrop {
		padding: 7% !important;
		}
		
		#postmatic-widget-popup_calderaModal input.prompt-subscribe-email,#postmatic-widget-popup_calderaModal input.prompt-subscribe-name {
		width: 100% !important;
		margin: 0 0 15px 0 !important;
		height: 35px !important;
		}
		
		#postmatic-widget-popup_calderaModal input.submit {
		height: auto !important;
		font-size: 25px !important;
		width: 85% !important;
		margin: 5% auto !important;
		}
		
		body.logged-in 	#postmatic-widget-popup_calderaModal input.submit {
		right: 2% !important;
		position: absolute !important;
		}
		
		#postmatic-widget-popup_calderaModal .unsubscribe, #postmatic-widget-popup_calderaModal .message {
		margin-left: 0 !important;
		margin-bottom: 25px !important;
		text-align:center !important;
		}
}
		<?php
		return ob_get_clean();
	}

	protected static function topbar( $no_background_image_url ) {
		ob_start(); ?>
		#postmatic-optin-topbar-wrap {
		margin-bottom: 30px !important;
		z-index: 100000 !important;
			}
			
		#postmatic-optin-topbar-wrap a {
		color: {{type}} !important;
		text-decoration: underline !important;
		}
		
		#postmatic-optin-topbar-wrap h2, #postmatic-optin-topbar-wrap h3, #postmatic-optin-topbar-wrap h4 {
		color: {{accent}};
		}
		
		#postmatic-optin-topbar-wrap h2 {
		font-size: 140% !important;
		}
		
		#postmatic-optin-topbar-wrap h3 {
		font-size: 125% !important;
		}
		
		#postmatic-optin-topbar-wrap h4 {
		font-size: 110% !important;
		}
		
		#postmatic-optin-topbar-wrap ul, #postmatic-optin-topbar-wrap ol {
		margin-left: 45px;
		}

		.prompt-subscribe-widget-content {
		display:inline-block !important;
		}

		#postmatic-topbar-optin-widget * {
		margin: 0 !important;
		padding: 0 !important;
		font-family:'Helvetica Neue', 'open-sans', 'arial', sans-serif !important;
		font-size: 14px !important;
		}
		
		#postmatic-optin-topbar h2.widgettitle {
		color: {{type}} !important;
		padding-right: 15px !important;
		display: inline-block !important;
		text-align: left !important;
		font-weight: bold !important;
		}
		
		#postmatic-optin-topbar div.subscribe {
		color: {{type}} !important;
		}
		
		#postmatic-optin-topbar input {
		display:inline-block !important;
		}
		
		#postmatic-optin-topbar input.submit {
		padding: 3px 7px !important;
		text-align: center !important;
		border: none !important;
		height: 24px !important;
		background: {{button-background}} !important;
		-webkit-border-radius: 6px !important;
		-moz-border-radius: 6px !important;
		border-radius: 6px !important;
		color: {{button-type}} !important;
		font-weight: normal !important;
		border-width:1px !important;
		border-style: solid !important;
		border-color: {{border-color}} !important;
		}
		
		#postmatic-optin-topbar input.prompt-subscribe-email {
		padding: 3px 7px !important;
		text-align: left !important;
		border: none !important;
		height: 22px !important;
		background: #fff !important;
		-webkit-border-radius: 8px !important;
		-moz-border-radius: 8px !important;
		border-radius: 8px !important;
		margin-top: 0 !important;
		color: #343434 !important;
		margin-left: 10px !important;
		width: 150px !important;
		font-size: 80% !important;
		border-color: {{border-color}} !important;
		border-width: 1px !important;
		border-style: solid !important;
		}
		
		#postmatic-optin-topbar input.prompt-subscribe-name {
		display: none !important;
		}
		
		#postmatic-optin-topbar .error,#postmatic-optin-topbar .message {
		color: {{type}} !important;
		font-size: 90% !important;
		}
		
			
		#postmatic-optin-topbar{
		background: {{grad-start}} !important;
		background: -moz-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -webkit-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -o-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -ms-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: linear-gradient(180deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		width:100% !important;
		text-align:center !important;
		color:#fff !important;
		padding:5px !important;
		overflow:hidden !important;
		height:40px !important;
		z-index:1000 !important;
		font-family:'Helvetica Neue', 'open-sans', 'arial', sans-serif !important;
		font-size:14px !important;
		line-height:normal !important;
		position:fixed !important;
		top:0 !important;
		left:0 !important;
		border-bottom:3px {{type}} !important;
		box-shadow:0 1px 5px rgba(0,0,0,.7) !important;
		}

		#postmatic-optin-topbar .postmatic-optin-topbar-button {
		-webkit-box-shadow:rgba(0,0,0,0.278431) 1px 1px 3px !important;
		background:#333 !important;
		border-bottom-left-radius:4px !important;
		border-bottom-right-radius:4px !important;
		border-top-left-radius:4px !important;
		border-top-right-radius:4px !important;
		border:none !important;
		box-shadow:rgba(0,0,0,0.278431) 1px 1px 3px !important;
		color:white !important;
		cursor:pointer !important;
		font-size:0.854em !important;
		margin:0px 0px 0px 7px !important;
		outline:none !important;
		padding:2px 10px 1px !important;
		position:relative !important;
		text-decoration:initial
		}

		#postmatic-optin-topbar button:hover{
		cursor:pointer !important;
		background:#444
		}

		#postmatic-optin-topbar button:active{
		top:1px !important;
		}

		#postmatic-optin-topbar-wrap close{
		width:20px !important;
		height:19px !important;
		bottom:6px !important;
		right:20px !important;
		background:url(http://hb-assets.s3.amazonaws.com/system/modules/hellobar/lib/sprite-8bit.png) no-repeat 0px -247px !important;
		position:absolute
		}

		#postmatic-optin-topbar-wrap close:hover{
		background:url(http://hb-assets.s3.amazonaws.com/system/modules/hellobar/lib/sprite-8bit.png) no-repeat 0px -228px !important;
		cursor:pointer !important;
		height:19px
		}

		#postmatic-optin-topbar-wrap open {
		-webkit-box-shadow:rgba(0,0,0,0.34902) 0px 0px 5px !important;
		background-image:url(http://hb-assets.s3.amazonaws.com/system/modules/hellobar/lib/sprite-8bit.png) !important;
		background-position:0px -8px !important;background-repeat:no-repeat no-repeat !important;
		border-bottom-left-radius:5px !important;border-bottom-right-radius:5px !important;
		border:3px {{type}} !important;
		box-shadow:rgba(0,0,0,0.34902) 0px 0px 5px !important;display:block !important;
		height:0px !important;
		outline:none !important;
		overflow:hidden !important;
		padding:80px 0px 0px !important;
		position:absolute !important;
		right:10px !important;top:-40px !important;
		width:35px !important;
		z-index:100 !important;
		background-color:{{background}} !important;
		display:none !important;
		}

		#postmatic-optin-topbar-wrap open:hover{
		background-color:#ff5a3d !important;
		cursor:pointer
		}
		
		@media only screen and (max-width : 800px) {
		#postmatic-optin-topbar {
		height: 70px !important;
		}

		}
		<?php
		return ob_get_clean();
	}

	/**
	 * Style template for bottom of page/ slide-in
	 *
	 * @param string $background_image_url
	 * @return string
	 */
	protected static function bottom( $background_image_url ) {
		ob_start(); ?>
		.nc_wrapper {
		background: transparent  !important;
		}
		
		#postmatic-widget-bottom_calderaModal {
		overflow:hidden !important;
		background: {{grad-start}} !important;
		background: -moz-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -webkit-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -o-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -ms-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: linear-gradient(180deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		width: 340px  !important;
		margin-right: 30px !important;
		-webkit-border-top-left-radius: 15px !important;
		-webkit-border-top-right-radius: 15px !important;
		-moz-border-radius-topleft: 15px !important;
		-moz-border-radius-topright: 15px !important;
		border-top-left-radius: 15px !important;
		border-top-right-radius: 15px !important;
		}
		
		#postmatic-widget-bottom_calderaModal a {
		color: {{type}} !important;
		text-decoration: underline !important;
		}
		
		#postmatic-widget-bottom_calderaModal h2, #postmatic-widget-bottom_calderaModal h3, #postmatic-widget-bottom_calderaModal h4 {
		color: {{accent}};
		}
		
				
		#postmatic-widget-bottom_calderaModal h2 {
		font-size: 140% !important;
		}
		
		#postmatic-widget-bottom_calderaModal h3 {
		font-size: 125% !important;
		}
		
		#postmatic-widget-bottom_calderaModal h4 {
		font-size: 110% !important;
		}
		
		
		#postmatic-widget-bottom_calderaModal ul, #postmatic-widget-bottom_calderaModal ol {
		margin-left: 45px !important;
		}
		
		#postmatic-widget-bottom_calderaModal .caldera-modal-closer {
		position: relative !important;
		}
		
		#postmatic-widget-bottom_calderaModalTitle {
		min-height: 50px !important;
		font-weight:bold !important;
		background: url(<?php echo $background_image_url ?>) 5px center no-repeat !important;
		background-size: 35px !important;
		width: auto !important;
		display:block !important;
		}
		
		#postmatic-widget-bottom_calderaModal .unsubscribe.prompt {
		color:{{type}} !important;
		}
		#postmatic-widget-bottom_calderaModal * {
		font-size: 16px !important;
		}
		
		h3#postmatic-widget-bottom_calderaModalLable {
		padding: 25px 20px 20px 50px !important;
		color: {{type}} !important;
		margin: 0 !important;
		font-size: 110% !important;
		text-align: left !important;
		width: auto !important;
		}

		#postmatic-widget-bottom_calderaModal div.primary.prompt {
		margin: 0 !important;
		font-size: 90% !important;
		margin-bottom: 15px !important;
		line-height: normal !important;
		}
		
		#postmatic-widget-bottom_calderaModal div.subscribe {
		color: {{type}} !important;
		}
		
		#postmatic-widget-bottom_calderaModal input {
		margin-right: 7px !important;
		clear: none  !important;
		}
		
		#postmatic-widget-bottom_calderaModal input.submit {
		padding: 7px 13px !important;
		text-align: center !important;
		border: 1px solid {{button-background}} !important;
		background: {{button-background}} !important;
		-webkit-border-radius: 5px !important;
		-moz-border-radius: 5px !important;
		border-radius: 5px !important;
		color: {{button-type}} !important;
		font-weight: normal !important;
		height: 34px !important;
		margin-top: 5px  !important;
		}
		
		#postmatic-widget-bottom_calderaModal input.prompt-subscribe-email,#postmatic-widget-bottom_calderaModal input.prompt-subscribe-name {
		padding: 5px  !important;
		width: 95% !important;
		text-align: left !important;
		background: #fff !important;
		color: {{type}} !important;
		-webkit-border-radius: 5px !important;
		-moz-border-radius: 5px !important;
		border-radius: 5px !important;
		margin-top: 3px !important;
		color: #343434 !important;
		border-color: {{border-color}} !important;
		border-width: 1px !important;
		border-style: solid !important;
		height: 30px !important;
		}
		
		#postmatic-widget-bottom_calderaModal .error,#postmatic-widget-bottom_calderaModal .message {
		color: {{type}} !important;
		font-size: 100% !important;
		}
		
		
		#postmatic-widget-bottom_calderaModal div.loading-indicator {

		}

		@media only screen and (max-width : 550px) {
		#postmatic-widget-bottom_calderaModal {
		width: 100%  !important;
		margin-right: 0 !important;
		}

		}
	
		<?php
		return ob_get_clean();
	}

	protected static function inpost( $background_image_url ) {
		ob_start(); ?>
				
		#postmatic-inpost-optin-widget {
		clear: both !important;
		margin: 25px 0 !important;
		padding: 0 !important;
		text-align:left !important;
		overflow:hidden !important;
		line-height:115% !important;
		background: {{grad-start}} !important;
		background: -moz-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -webkit-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -o-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: -ms-linear-gradient(90deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		background: linear-gradient(180deg, {{grad-start}} 35%, {{grad-end}} 85%) !important;
		-webkit-border-top-left-radius: 15px !important;
		-webkit-border-bottom-right-radius: 15px !important;
		-moz-border-radius-topleft: 15px !important;
		-moz-border-radius-bottomright: 15px !important;
		border-top-left-radius: 15px !important;
		border-bottom-right-radius: 15px !important;
		min-height: 230px !important;
		}
		
				
		#postmatic-inpost-optin-widget a {
		color: {{type}} !important;
		text-decoration: underline !important;
		}
		
		#postmatic-inpost-optin-widget h2, #postmatic-inpost-optin-widget h3, #postmatic-inpost-optin-widget h4 {
		color: {{button-background}} !important;
		}
		
		#postmatic-inpost-optin-widget h2 {
		font-size: 140% !important;
		}
		
		#postmatic-inpost-optin-widget h3 {
		font-size: 125% !important;
		}
		
		#postmatic-inpost-optin-widget h4 {
		font-size: 110% !important;
		}
		
		#postmatic-inpost-optin-widget ul, #postmatic-inpost-optin-widget ol {
		margin-left: 25px !important;
		}
		
		#postmatic-inpost-optin-widget div.widget_prompt_subscribe_widget {
		background: url(<?php echo $background_image_url ?>) no-repeat 4% 20% !important;
		background-size: 150px !important;
		min-height: 150px !important;
		padding: 4% 4% 0 4% !important;
		}
		
		
		#postmatic-inpost-optin-widget * {
		font-size: 16px !important;
		}
		
		#postmatic-inpost-optin-widget h2.widgettitle {
		color: {{type}} !important;
		margin: 0 0 15px 175px !important;
		font-size: 140% !important;
		padding: 0 !important;
		text-align: left !important;
		line-height: normal !important;
		}

		#postmatic-inpost-optin-widget div.primary.prompt {
		margin: 0 0 25px 175px !important;
		font-size: 110% !important;
		line-height: normal !important;
		display: block !important;
		}
		
		#postmatic-inpost-optin-widget div.subscribe {
		color: {{type}} !important;
		}
		
		#postmatic-inpost-optin-widget div.inputs {
		background: {{accent}} !important;
		padding: 10px 25px !important;
		min-height: 60px !important;
		margin: 0 -5% 0 -5% !important;
		}
		
		#postmatic-inpost-optin-widget input {
		margin-right: 7px !important;
		clear: none !important;
		display: inline !important;
		}
		
		#postmatic-inpost-optin-widget input.submit {
		padding: 7px 15px !important;
		text-align: center !important;
		border: 1px solid {{button-background}} !important;
		background: {{button-background}} !important;
		-webkit-border-radius: 5px !important;
		-moz-border-radius: 5px !important;
		border-radius: 5px !important;
		color: {{button-type}} !important;
		font-weight: normal !important;
		height: 34px !important;
		max-width: 20% !important;
		}
		
		#postmatic-inpost-optin-widget input.prompt-subscribe-email, #postmatic-inpost-optin-widget input.prompt-subscribe-name {
		padding: 7px !important;
		text-align: left !important;
		background: #fff !important;
		-webkit-border-radius: 5px !important;
		-moz-border-radius: 5px !important;
		border-radius: 5px !important;
		margin-top: 3px !important;
		color: #343434 !important;
		width: 30% !important;
		border-color: {{border-color}} !important;
		border-width: 1px !important;
		border-style: solid !important;
		height: 35px !important;
		}
		
		#postmatic-inpost-optin-widget .message {
		color: {{type}} !important;
		font-size: 100% !important;
		margin-left: 175px !important;
		}
		
		#postmatic-inpost-optin-widget div.loading-indicator {
		left: 270px !important;
		}
		
		body.logged-in #postmatic-inpost-optin-widget .inputs {
		text-align: right;
		}
	
		#postmatic-inpost-optin-widget .unsubscribe.prompt {
		color: {{type}} !important;
		float: left !important;
		font-size: 85% !important;
		width: 250px !important;
		}
		
		@media only screen and (max-width : 550px) {
		
		#postmatic-inpost-optin-widget {
		-webkit-border-top-left-radius: 5px !important;
		-webkit-border-bottom-right-radius: 5px !important;
		-moz-border-radius-topleft: 5px !important;
		-moz-border-radius-bottomright: 5px !important;
		border-top-left-radius: 5px !important;
		border-bottom-right-radius: 5px !important;
		}
		
		#postmatic-inpost-optin-widget div.widget_prompt_subscribe_widget {
		background:none !important;
		}
		
		#postmatic-inpost-optin-widget input.prompt-subscribe-email,#postmatic-inpost-optin-widget input.prompt-subscribe-name,#postmatic-inpost-optin-widget input.submit,#postmatic-inpost-optin-widget input.prompt-subscribe-email, #postmatic-inpost-optin-widget input.prompt-subscribe-name, #postmatic-inpost-optin-widget input.submit {
		width: 98% !important;
		margin: 0 0 10px 0 !important;
		padding: 1% !important;
		}
		
		#postmatic-inpost-optin-widget h2.widgettitle,#postmatic-inpost-optin-widget div.primary.prompt,#postmatic-inpost-optin-widget .message {
		margin: 0 0 10px 0 !important;
		line-height: normal !important;
		}
		
		}	
			
		<?php
		return ob_get_clean();
	}


}
