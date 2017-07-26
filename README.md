# Custom Post Type Cleanup [![Build Status](https://travis-ci.org/keesiemeijer/custom-post-type-cleanup.svg?branch=master)](https://travis-ci.org/keesiemeijer/custom-post-type-cleanup) #

Version: 1.0.0  
Requires at least: 4.0  
Tested up to: 4.8  

Custom post type posts are left in the database if a post type is no longer registered (in use).

Plugins and themes can (without you knowing) use custom post types as a way to store data. These posts stay in the database forever if they're not cleaned up by the plugin/theme itself upon deletion.

There are a lot of plugins that clean your database (by removing revisions, drafts etc.), but I haven't found one that does a cleanup of unused post type posts. 

This plugin provides an easy way to detect and remove posts from post types that are no longer in use. The settings page for this plugin is at wp-admin > Tools > Custom Post Type Cleanup.

**Note**: The proper WordPress delete function `wp_delete_post()` is used instead of running a direct MySQL query to delete the posts. This way all associated post data (comments, post meta etc.) are also deleted from the database. The only exception being term relationships from taxonomies that are also not in use anymore. Delete the terms from those taxonomies as well with this [sister plugin](https://github.com/keesiemeijer/custom-taxonomy-cleanup)

It's recommended you **make a database backup** before deleting posts.

## Screenshots

### No posts found
The settings page if there are no posts from unused post types in the database. De-activate or delete the plugin and use it again later.

![No unused post type posts found](https://user-images.githubusercontent.com/1436618/28619330-24c2ca86-7208-11e7-8964-a19ffe04f826.png)

### Settings Page
The settings page if posts where found (after deleting a batch of 100 posts).
![Settings page for this plugin](https://user-images.githubusercontent.com/1436618/28619332-24c712b2-7208-11e7-80c9-933130c542df.png)

### Done
The settings page if all posts were deleted. De-activate or delete the plugin and use it again later.
![Settings page for this plugin](https://user-images.githubusercontent.com/1436618/28619331-24c5ea7c-7208-11e7-8304-fcc90035de00.png)