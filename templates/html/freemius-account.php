<?php
/**
 * Template variables:
 *
 * @var string $account_html The Freemius account HTML.
 */
?>
<style>
    ul.ui-tabs-nav {
    margin: 25px 0 35px 0;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    justify-content: left;
    list-style: none;
    border-bottom: 1px solid #4A4A4A;
}

ul.ui-tabs-nav li {
  margin: 0;
}

ul.ui-tabs-nav a {
    padding: 0.5rem 0.75rem;
    font-size: 80%;
    font-weight: bold;
    text-decoration: none;
    display: flex;
    align-items: flex-start;
    color: #fff;
    background:#4A4A4A;
    margin: -4px 1px 0 0;
    -webkit-border-top-left-radius: 5px;
    -webkit-border-top-right-radius: 15px;
    -moz-border-radius-topleft: 5px;
    -moz-border-radius-topright: 15px;
    border-top-left-radius: 5px;
    border-top-right-radius: 15px;
    border-top: 1px solid #23282D;
  }
ul.ui-tabs-nav li.ui-state-active a,ul.ui-tabs-nav li a:hover {
  background: #43C0C7;
  border-top: 1px solid #43C0C7;
}

ul.ui-tabs-nav li#prompt-tab-upgrade a {
  background: #D54E21;
  border: 1px solid #FA5923;
}

 ul.ui-tabs-nav li#prompt-tab-upgrade.ui-state-active a {
  background: #D54E21;
  border: 1px solid #FA5923;
 }
 h2#prompt-settings-header {
  background: url(<?php echo Prompt_Core::$url_path; ?>/media/replyable.png) no-repeat top left;
  background-size: 250px;
  height: 65px;
  margin-bottom: 10px;
  margin-top: 30px; 
  text-align: center;
}
.wrap {
  padding: 1% 5% 2% 2%;
  width: auto;
  margin: 0;
}

h2#prompt-settings-header span {
  display: none; }
</style>
<div class="wrap">
    <h2 id="prompt-settings-header"><span>Replyable</span></h2>
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all" role="tablist"><li id="prompt-tab-core" class="ui-state-default ui-corner-left" style="" role="tab" tabindex="0" aria-controls="prompt-settings-core" aria-labelledby="ui-id-1" aria-expanded="true"><a href="/wp-admin/options-general.php?page=postmatic" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-1">Get Started</a></li><li id="prompt-tab-configure-your-template" class="ui-state-default ui-corner-left" style="" role="tab" tabindex="-1" aria-controls="prompt-settings-configure-your-template" aria-labelledby="ui-id-2" aria-selected="false" aria-expanded="false"><a href="/wp-admin/options-general.php?page=postmatic#prompt-settings-configure-your-template" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-2">Configure Your Template</a></li><li id="prompt-tab-comment-delivery" class="ui-state-default ui-corner-left" style="" role="tab" tabindex="-1" aria-controls="prompt-settings-comment-delivery" aria-labelledby="ui-id-3" aria-selected="false" aria-expanded="false"><a href="/wp-admin/options-general.php?page=postmatic#prompt-settings-comment-delivery" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-3">Comment Subscription Options</a></li><li id="prompt-tab-import-subscribe-reloaded" class="ui-state-default ui-corner-left" style="" role="tab" tabindex="-1" aria-controls="prompt-settings-import-subscribe-reloaded" aria-labelledby="ui-id-4" aria-selected="false" aria-expanded="false"><a href="/wp-admin/options-general.php?page=postmatic#prompt-settings-import-subscribe-reloaded" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-4">Importer</a></li><li id="prompt-tab-recommended-plugins" class="ui-state-default ui-corner-left" style="" role="tab" tabindex="-1" aria-controls="prompt-settings-recommended-plugins" aria-labelledby="ui-id-5" aria-selected="false" aria-expanded="false"><a href="/wp-admin/options-general.php?page=postmatic#prompt-settings-recommended-plugins" class="ui-tabs-anchor" role="presentation" tabindex="-1" id="ui-id-5">Recommended Plugins</a></li></ul>
<?php echo $account_html; ?>
</div>

