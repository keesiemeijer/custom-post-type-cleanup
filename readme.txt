=== Custom Post Type Cleanup ===
Contributors: keesiemeijer
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Detect and delete posts from custom post types that are no longer in use 

== Description ==

Custom post type posts are left in the database if a post type is no longer registered (in use).

Plugins and themes can (without you knowing) use custom post types as a way to store data. These posts stay in the database forever if they're not cleaned up by the plugin/theme itself upon deletion.

There are a lot of plugins that clean your database (by removing revisions, drafts etc.), but I haven't found one that does a cleanup of unused post type posts. 

This plugin provides an easy way to detect and remove posts from post types that are no longer in use. The settings page for this plugin is at wp-admin > Tools > Custom Post Type Cleanup.