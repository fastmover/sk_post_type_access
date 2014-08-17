=== Plugin Name ===
Contributors: Fastmover
Tags: access, access restrictions, content type access, content type restrictions, read access
Requires at least: 3.7
Tested up to: 3.9.1
Stable tag: 0.0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin locks down front end read access to all post types.  Creates an anonymous role, if one doesn't already exist;

== Description ==

This plugin locks down front end post reading, so you'll need to go into the plugin options and modify capabilities on a post_type basis for each role.  After enabling this plugin, all post types will be locked down.

== Installation ==

1. Upload `sk_post_type_access` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. This is the configuration page for Post Type Access.  The checkbox for disabling the plugin will only disable this plugin and not the changes you make to permissions within the settings.
2. This is what it looks like to change settings for post_type 'Posts'.  You can see all current roles defined in Wordpress, and their read_ capabilities available to them.


== Changelog ==

= 0.0.9 =
* Removed debugging output.

= 0.0.8 =
* Removes menu link of post types that the current user doesn't have read_ access.
