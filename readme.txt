=== Postmatic Basic - Subscribe to Comments & Posts. And simply reply. ===
Contributors: vernal, cyberhobo
Tags: email, notification, subscribe, subscription, email, subscribe to comments, commenting, reply, email, optins, popups, optin forms, subscribe form, subscriptions, newsletter, newsletters, email newsletter, email subscription, post notification, email subscription, mailchimp, mailgun, email commenting, reply to email, email replies, engagement, invitations
Requires at least: 4.3
Tested up to: 4.6.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Relationships are a two-way street. WordPress email can be too.

== Description ==

= Blog engagement is only as limited as the technology which powers it. =
Postmatic Basic sends beautiful replyable emails for post and native comment subscriptions. 

The days of one-way notifications are over. It’s time to let them hit reply.

Postmatic lets your readers subscribe by email when they leave a comment. Subsequent comments and replies will land in their inbox, just like with _Mailpoet_, _Jetpack_ or _Subscribe to Comments_. **Except now with Postmatic, they can comment back and keep the conversation going just by hitting reply**. They never have to leave their inbox.

Online or offline, everyone stays in the loop—regardless of desktop or mobile.

= Your posts and comments delivered to their inbox. =
= Their comments delivered through a simple reply. =
= That’s how the conversation gets started. That’s how you increase engagement. =

Postmatic can also deliver your WordPress posts to readers as a beautiful mobile-ready html email.  
And they can comment just by hitting reply. No accounts. No forms.  
No browsers. Easy.

[Visit gopostmatic.com to learn more](http://gopostmatic.com/basic)

= Key Features =

* Postmatic strictly adheres to WordPress best practices. It uses the native commenting and user systems. We’re just a magical email gateway.
* Start building your subscriber list with 35 different Optins including popups, top bars, sliders, and after-the-post.
* Posts are converted into fully responsive html emails on the fly.
* Users can reply to new post notifications to leave a comment.
* Replies are posted as comments in as little as 6 seconds.
* Users commenting from your posts can subscribe to new comment notifications and reply to each to send back a response.
* Single-click migration tools for Jetpack, MailPoet, and Mailchimp lists as well as Subscribe to Comments and Subscribe to Comments Reloaded subscriptions.
* We’re serious about privacy and doing the right thing. We do not profile users, run ads, or sell data in any way and maintain a very strict [privacy](http://gopostmatic.com/privacy) policy.
* This Basic version of Postmatic, which brings 100% email commenting to WordPress, is free without limits. To learn about enhanced features for publishers please visit [our website](http://gopostmatic.com).

== Frequently Asked Questions ==

= This is really free? Do you sell my data or run advertising? =

Yes to free. [No to bad stuff](http://gopostmatic.com/privacy). We're not in the data brokering or advertising game. Instead we're in the business of making [Postmatic](http://gopostmatic.com/features) _so good_ and _so affordable_ that you'll happily upgrade.You can help fund our development while sending your engagement through the roof by subscribing to the full version of Postmatic.

= Is this a 3rd party commenting system? =
Not at all. Postmatic uses native WordPress commenting. All we do is wire up some magic to turn emails into comments, then push them to your site via the WordPress api. We even have a companion plugin, [Epoch](http://wordpress.org/plugins/epoch) which will serve as a replacement for Disqus if you need to move back to native commenting.

= How quickly do email comments post to my website =
It takes Postmatic **six to ten seconds** after you hit send to turn your email into a WordPress comment.

Find a few hundred more answers at our [big FAQ](http://gopostmatic.com/faq/).

== Screenshots ==
1. Configure your template. The system automatically adapts to the typography and colors of your active theme.
2. New posts are sent as simple mobile-ready emails. The user can just hit reply to send a comment. Nifty.
3. Start growing your list using our built in optins pacakge for popups, flyover, and more.
4. Write posts just as you always have. When you hit publish it will be sent to your subscribers. A built-in precheck alerts you if there are any post elements which may not work well in email. You can then edit an email-specific version if you wish.
5. The post as delivered via email.
6. Send a comment by just hitting reply from your inbox.
7. The comment arrives to the site and is sent to the author as well as other comment subscribers. They in turn can respond by hitting reply.
8. Comments arrive via email to your site in under 6 seconds. Postmatic uses native WordPress commenting.
9. Comments arrive via email to your site in under 6 seconds. Postmatic uses native WordPress commenting.
10. Postmatic is 100% compatible with all your favorite user and commenting plugins because it is fully WordPress native.
11. The popup optin can be configured based on time, page scroll, or after the user leaves a comment.
12. The after-the-post optin displays above the comment area. Shown in Dark.
13. The topbar optin shows across the top of your site on all posts and pages.
14. The bottom slider optin invites users to subscribe with a collapsible animated window.
15.  We're serious about privacy. Your data is yours, and always will be. Postmatic uses fully-native commenting. Just think of us as a magical email > WordPress gateway.

== Changelog ==
= 2.0.14 =

- Better detection of failed email delivery with a new admin notification if there is a continual problem.

= 2.0.13 =

- IMPORTANT - Big changes are coming to Postmatic Basic on December 1st. This release adds a button to your dashboard to tell you about the upgrade and explain what you need to do.
- Fixed - Better handling of failures during the url validation process.

== Changelog ==
= 2.0.12 =

- We're always finding new and fun command misspellings to recognize

= 2.0.11 =

- A fix for the color palette detection script. Give it a try if it wasn't working for you.
- More checks to test local mail delivery and alert the admin if there is a problem.
- Improved handling of 'unsubscribe' typos, including the mysterious ?unsubscribe. 

= 2.0.10 =

- Bug fixes
- Better support for handlebars tags in post content

= 2.0.9 =

- Temporarily removed Freemius to bypass optin bugs

= 2.0.8 =

- Updated German translation
- Misc bug fixes

= 2.0.7 =

- NEW: we added a number of video tutorials to the plugin interface
- Fixed an issue with Freemius when logging in as an editor

= 2.0.6 =

- [Fixed issues with that new tooltip on mobile as well as hover issues](https://github.com/postmatic/postmatic-basic/issues/12)
- Fixed a horrible typo in the tooltip
- Fixed a freemius bug which was affecting logged in users.

= 2.0.5 =

- NEW: A tooltip on the comment form subscription checkbox gives users info about what the subscription means.
- [Added tests for better debugging of malformed inbound messages](https://github.com/postmatic/postmatic-basic/commit/1bbb721351384a2512faec50cf9ca450c2569b33)

= 2.0.4 =

- [We have fixed the possibility of the invitations system going haywire on certain hosts while on the free plan](https://github.com/postmatic/postmatic-basic/commit/00662ab477e67c7ea48886303854cb5dbdf958c1)
- [Updates to the popup modal library](https://github.com/postmatic/postmatic-basic/commit/005e134f993edb8ed5f5725fbbb8cd4982d75c32)
- [Improved PHP 5.2 compatibility](https://github.com/postmatic/postmatic-basic/commit/69fedc90f93e9c61dedc92b591e554a6fbf063c2)

= 2.0.3 =

- [Fixed an issue in which Postmatic caused errors on the edit>post screen of WPML users](https://github.com/postmatic/postmatic-basic/issues/5)
- Fixed errors with Freemius related to logged in users of the subscriber role
- [Simplified the admin UI to get rid of jqueryui tab conflicts](https://github.com/postmatic/postmatic-basic/pull/7)

= 2.0.2 =

- [Fixed a bug which was leading logged-in users to unsubscribe when they thought they were subscribing](https://github.com/postmatic/postmatic-basic/commit/a1c70efdf4cce95221772b6b90a3013ca62a90d3) 

= 2.0.1 =
We have moved development to a public [Github repo](https://github.com/postmatic/postmatic-basic). This is our first atttempt at an awesome changelog which blends user-friendly info with developer-friendly code.

- [Fixed a php error in admin caused by a legacy feed parser](https://github.com/postmatic/postmatic-basic/commit/05141972fe6475b2a087f0134bcde30ba2d45e15)
- [Improved error logging](https://github.com/postmatic/postmatic-basic/commit/37fc0592955a49c38ad69f8491168b721635a63c)
- [New banner assets](https://github.com/postmatic/postmatic-basic/commit/1615304221f87aae7391253d8b0b721ae4d3e111)

= 2.0.0 =
A complete rewrite of Postmatic Basic. We've put a ton of work into our technology and hope you enjoy it.

- Postmatic now uses wp_mail for sending outgoing email.
- The Postmatic template now automatically adapts to the colors and fonts of your active theme.
- A brand new widget for smoother and faster subscriptions.
- All transactional actions (subscribe/unsubscribe commands etc) are now clickable.
- Customize the email version of your post content before it gets sent.


== Upgrade Notice ==
