# Custom Post Type Cleanup [![Build Status](https://travis-ci.org/keesiemeijer/custom-post-type-cleanup.svg?branch=master)](https://travis-ci.org/keesiemeijer/custom-post-type-cleanup) #

Version: 1.2.2-alpha  
Requires at least: 4.0  
Tested up to: 5.0  

This is the development repository for the WordPress plugin [Custom Post Type Cleanup](https://wordpress.org/plugins/custom-post-type-cleanup/)

---

Custom post type posts are left in the database if a post type is no longer registered (in use).

Plugins and themes can (without you knowing) use custom post types as a way to store data. These posts stay in the database forever if they're not cleaned up by the plugin/theme itself upon deletion.

There are a lot of plugins that clean your database (by removing revisions, drafts etc.), but I haven't found one that does a cleanup of unused post type posts. 

This plugin provides an easy way to detect and remove posts from post types that are no longer in use. The settings page for this plugin is at wp-admin > Tools > Custom Post Type Cleanup.

**Note**: The proper WordPress delete function `wp_delete_post()` is used instead of running a direct MySQL query to delete the posts. This way all associated post data (comments, post meta etc.) are also deleted from the database. The only exception being term relationships from taxonomies that are also not in use anymore. Delete the terms from those taxonomies as well with this [sister plugin](https://github.com/keesiemeijer/custom-taxonomy-cleanup)

Since version 1.2.0 you can re-register unused custom post types for a limited period of time. This allows you to inspect and delete the posts like you would normally (in the wp-admin).

It's recommended you **make a database backup** before deleting posts.

## Screenshots

### No posts found
The settings page if there are no posts from unused post types in the database. De-activate or delete the plugin and use it again later.

![screenshot-4](https://user-images.githubusercontent.com/1436618/33768783-bf490bf4-dc27-11e7-8a1f-a17322d4d4cc.png)

### Settings Page
The settings page if posts where found (after deleting a batch of 100 posts).
![screenshot-1](https://user-images.githubusercontent.com/1436618/33768773-b198f4ba-dc27-11e7-9586-d65907d8510c.png)

### Re-registering unused custom post types
The settings page when unused custom post types are re-registered.
![screenshot-2](https://user-images.githubusercontent.com/1436618/33768777-b69357bc-dc27-11e7-894d-e995d3a0038b.png)

### Done
The settings page if all posts were deleted. De-activate or delete the plugin and use it again later.
![screenshot-3](https://user-images.githubusercontent.com/1436618/33768780-badf8232-dc27-11e7-8c8b-7b4c757971b0.png)

## Contributing

Anyone is welcome to contribute to the Custom Post Type Cleanup plugin. If you find an issue, let us know [here](https://github.com/keesiemeijer/custom-post-type-cleanup/issues?state=open) or open up a forum topic in the [WordPress forums](https://wordpress.org/support/plugin/custom-post-type-cleanup)

### Help Tranlate this plugin

If you want to contribute translations so other people can use them as well, you can do so by visiting the [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/custom-post-type-cleanup) page for this plugin. You can read about how translating works in the [WordPress Translator's Handbook](https://make.wordpress.org/polyglots/handbook/tools/glotpress-translate-wordpress-org/).

Let me know in the [WordPress forums](https://wordpress.org/support/plugin/custom-post-type-cleanup) if you want to become a [Translation Editor](https://make.wordpress.org/polyglots/handbook/rosetta/theme-plugin-directories/#requesting-new-translation-editors) for this plugin.

### Development

The `master` branch is where you'll find the most recent, stable release.
The `develop` branch is the current working branch for development. Both branches are required to pass all unit tests. Any pull requests are first merged with the `develop` branch before being merged into the `master` branch. See [Pull Requests](#pull-requests)

#### Pull Requests
When starting work on a new feature, branch off from the `develop` branch.
```bash
# clone the repository
git clone https://github.com/keesiemeijer/custom-post-type-cleanup.git

# cd into the custom-post-type-cleanup directory
cd custom-post-type-cleanup

# switch to the develop branch
git checkout develop

# create new branch newfeature and switch to it
git checkout -b newfeature develop
```

#### Creating a new build
To compile the plugin without all the development files use the following commands:
```bash
# Go to the master branch
git checkout master

# Install Grunt tasks
npm install

# Build the production plugin
grunt build
```
The plugin will be compiled in the `build` directory.
