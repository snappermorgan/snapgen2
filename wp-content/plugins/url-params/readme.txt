=== Plugin Name ===
Contributors: jeremyshapiro
Tags: url, query string, url parameters, urlparam, url params, url param, query, jeremy shapiro, infusion, infusionsoft, purl
Requires at least: 2.0.2
Tested up to: 4.0
Stable tag: 1.7

Short Code to grab any URL parameter from the Query String and display it or display conditional content.

== Description ==

The URL Params WordPress Plugin allows you to access URL parameters in the Query String of the URL.

The plugin even allows you to specify a default value in the shortcode if the parameter isn't set, so if you want to say "Hello, FirstName" and FirstName isn't set, it could say something like "Hello, Friend!"

To specify a backup url parameter, enter multiple parameters seperated by commas. The plugin will check for each paramter, in order, until a matching one is found and return that. Failing finding any of the parameters you listed, the default will be returned. For example, you can specify `[urlparam param="FirstName, First, name" default="Friend" /]` to check for FirstName, and if not found, then First, if not found, then name, and if not, then just return "Friend".

If the parameter is a date, you can also specify the `dateformat` option using a [PHP friendly date format](http://php.net/manual/en/function.date.php), for example `[urlparam param="somedate" dateformat="F Js" /]`.

This is great if you have personalized links, like from Infusionsoft, as it lets you personalize a landing page with a persons name.

You can also use this to pre-fill out form fields for folks based on the querystring. For example, if their first name is passed in the URL, your landing page can greet the viewer by name and pre-fill their name on a form.

== Installation ==

To install the plugin, download the zip file and upload via the plugin interface of your WordPress site.

== Usage ==

Use the shortcode urlparam with the optional parameter of "default". For example `[urlparam param="FirstName" /]` or `[urlparam param="FirstName" default="Friend"/]`.

For conditional content use `[ifurlparam][/ifurlparam]`. For example, `[ifurlparam param="FirstName"]Hey, I know you, [urlparam param="FirstName"]![/ifurlparam]` would greet known visitors, but display nothing to users without a FirstName in the query string.

If you want to show content when a value does NOT exist, you can set `empty` in `[ifurlparam]`. For example `[ifurlparam param="FirstName" empty="1"]Welcome to the site, visitor![/ifurlparam]` would greet visitors without a FirstName in the query string, but display nothing for visitors with FirstName in the query string.

If you want to show content only to visitors with a specific value in their query string, you can set `is` in `[ifurlparam]`. For example, `[ifurlparam param="FirstName" is="Bob"]Hi, Bob![/ifurlparam]`, would only greet visitors with the FirstName param set to Bob.

== Security ==

To help protect your site against [Reflected Cross Site Scripting](http://en.wikipedia.org/wiki/Cross-site_scripting), we sanitize output with [esc_html()](http://codex.wordpress.org/Function_Reference/esc_html) which prevents any HTML tags from being passed in and displayed. This would prevent someone from passing in javascript, for example, and having it execute on your site.

== Changelog ==

* 1.7: Released 7/2/2014. Changed escaping via htmlspecialchars() to esc_html() and removed option to allow not escaping HTML
* 1.6: Released 7/1/2014. Security update to patch against Reflected Cross Site Scripting.
* 1.5: Nudge. WordPress didn't pick up this latest trunk version on commit. Will remove this comment in the future.
* 1.5: Released 12/13/2013. Added support for conditional content via `ifurlparam` shortcode
* 0.4: Released 7/11/2011. Added support for date formatting via the `dateformat` option
* 0.3: Released 6/25/2011. Added support for alternative paramters, i.e. param="FirstName, First, name"
