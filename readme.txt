=== Bypass Transients ===
Contributors: welcher
Donate link: http://www.ryanwelcher.com/donate/
Tags: transients, developer, debug bar, admin bar
Requires at least: 3.0
Tested up to: 4.7
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Bypass any get_transient() calls on a per page basis by clicking the link in the Admin Bar.

== Description ==

We all know that caching is FTW. But, caching can be a nuisance when trying to develop or debug. That's where this plugin comes in.
You can bypass any get_transient() calls on a per page basis by clicking the link in the Admin Bar.

Features:

1. Bypass Transients with a single click
2. Supports Transients saved to the database
3. Object Cache support - currently only tested with memecached
4. Will automatically detected any new transients set.
5. Debug Bar integration to provide details such as lists of known, found and bypassed transients.


Usage:

Install and activate the plugin as normal. Suspend transients will loading the appropriate class based on the results `wp_using_ext_object_cache()`. Anytime a transient set, the plugin will detect it and add it to the
known list.

Standard Transients ( saved to the database )

When activated, the plugin will retrieve any existing transients from the database and add them to the known transients list.

The Admin Bar will have two new buttons:

1. Bypass Transients: bypasses known transients
2. Scan Transients: reloads the transients from the database.

Object Cached Transients ( i.e memcached )
These transients are not stored in the database so the best way to get them is to use the Flush Transients button and reload the page to utilize the transient detection feature of the plugin.
It may require two page loads; One to detect and add the transient, and then a second to actually bypass it.

Admin Bar buttons:

1. Bypass Transients: bypasses known transients
2. Flush Transients: Calls wp_object_cache_flush() to invalidate the caches.

**Disclaimer**
This plugin should NOT be run in a production environment. It is meant for local and MAYBE staging development. By it's very nature this will slow
your site down.

== Installation ==

Install this plugin as described in the [Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

== Frequently Asked Questions ==

= Should I run this on a production server? =

This will bypass your caching and slow down your site. If that is what you want, then yes run it on a production server :)

= Does this delete transients? =

No standard transients are not deleted. In an object cached environment, the caches are flushed so in that sense they are deleted.


== Changelog ==

= 1.0.0 =
* Initial Release

