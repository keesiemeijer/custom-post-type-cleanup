=== Custom Post Type Cleanup ===
Contributors: keesiemeijer
Requires at least: 4.2
Tested up to: 5.2
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Detect and delete posts from custom post types that are no longer in use 

== Description ==

Custom post type posts are left in the database if a post type is no longer registered (in use).

Plugins and themes can (without you knowing) use custom post types as a way to store data. These posts stay in the database forever if they're not cleaned up by the plugin/theme itself upon deletion.

There are a lot of plugins that clean your database (by removing revisions, drafts etc.), but I haven't found one that does a cleanup of unused post type posts. 

This plugin provides an easy way to detect and remove posts from post types that are no longer in use. The settings page for this plugin is at wp-admin > Tools > Custom Post Type Cleanup.

**Note**: The proper WordPress delete function [wp_delete_post](https://developer.wordpress.org/reference/functions/wp_delete_post/) is used instead of running a direct MySQL query to delete the posts. This way all associated post data (comments, post meta etc.) are also deleted from the database.

Since version 1.2.0 you can re-register unused custom post types for a limited period of time. This allows you to inspect and delete the posts like you would normally (in the wp-admin).

== Installation ==
Follow these instructions to install the plugin.

1. In your WordPress admin panel, go to Plugins > New Plugin, search for "custom post type cleanup" and click "Install now".
2. Alternatively, download the plugin and upload the contents of custom-post-type-cleanup.zip to your plugins directory, which usually is /wp-content/plugins/.
3. Activate the plugin
4. Go to wp-admin > Tools > Custom Post Type Cleanup and see if there are unused custom post type posts in the database.

== Changelog ==
= 1.3.0 =
* Add batch size option to admin form.
= 1.2.0 =
* Add ability to re-register unused custom post types.

== Screenshots ==

1. Plugin page
2. Plugin page after re-registering unused custom post types.
3. Plugin page after cleaning up all unused post type posts

== Upgrade Notice ==
= 1.3.0 =
This upgrade adds a batch size option to the admin form.