# Post Content Shortcodes #
**Contributors:** cgrymala

**Tags:** shortcode, clone, syndication, post content, post list

**Requires at least:** 4.0

**Tested up to:** 4.3

**Stable tag:** 0.5.6


Adds shortcodes to display the content of a post or a list of posts.

## Description ##

This plugin adds two shortcodes that allow you to display either the content of a post or a list of posts within the content area of a post. This plugin should be fully compatible with all post types, as it simply uses the `get_post()` and `get_posts()` WordPress functions.

**Post Content**

The first shortcode is the `[post-content]` shortcode. Using that shortcode will allow you to display the content of one post within another post. This shortcode requires a single attribute with a key of "id". To use this shortcode to display the content of a post or page with an ID of 25, you would use this shortcode like `[post-content id=25]`. This shortcode also accepts the following optional arguments:

* post_name => null - The slug of the post that you want to pull. This can be used in place of the `id` attribute
* show_image => false - Determines whether or not to display the featured image (if so, this appears before the content)
* show_excerpt => false - Determines whether to default to showing the post excerpt instead of the post content (still falls back to post content if the excerpt is not set)
* excerpt_length => 0 - If you would like to limit the length of the content/excerpt shown on the page, specify the maximum number of words that should be shown (a read more link will automatically be appended to any entries that exceed that limit).
* image_width => 0 - The width, in pixels, to which the featured image should be sized
* image_height => 0 - The height, in pixels, to which the featured image should be sized
* show_title => false - Whether or not to show the post title at the top of the content. By default, the title is wrapped in `<h2>` tags, but you can use the `post-content-shortcodes-title` filter to modify the title output.

**Post List**

The second shortcode is the `[post-list]` shortcode. This shortcode does not require any arguments, but will accept the following arguments (most of which are the default arguments used with `get_posts()`):

* numberposts => -1
* offset => 0
* category => null (can accept category slug [with quotes] or category ID [without quotes])
* orderby => title
* order => asc
* include => null
* exclude => null
* meta_key => null
* meta_value => null
* post_type => 'post'
* post_mime_type => null
* post_parent => null
* post_status => 'publish'
* exclude_current => true
* ~~blog_id => 0 (the numeric ID of the site from which to pull the posts)~~
* blog => null (can be set to the numeric ID or the blog name [slug] of the site from which to pull the posts - this replaces the old blog_id attribute)
* show_image => false
* show_excerpt => false
* excerpt_length => 0
* image_width => 0
* image_height => 0
* shortcodes => false (determines whether the plugin should attempt to allow shortcodes to be processed within the excerpt/content)

The first 13 arguments are standard arguments for the `get_posts()` function.

The `exclude_current` argument is not a standard argument for the `get_posts()` function. It is a custom argument for this plugin. When that argument is set to `true`, the current page or post will be excluded from the list of posts. If it is set to `false`, `"false"` or `0`, the current page or post will be included in the post list.

The `blog_id` argument is also not standard. That argument allows you to pull a post from a site other than the current site when using WordPress multisite. Simply set that argument to the ID of the site from which you want to pull the post, and the post with the `id` you specify will be pulled from the blog/site with the `blog_id` you specify.

The `show_image`, `image_width` and `image_height` arguments only apply to the `post-list` shortcode. They determine whether to display the featured image and how to display it for each post within the list. If the `image_width` and `image_height` arguments are both set to 0 (which is the default), the "thumbnail" size will be used (assuming the `show_image` argument is set to 1 or "true"). If only one of the `image_width` or `image_height` arguments are set, the other argument will be set to 999999 to ensure that the specified dimension is met.

The 'show_excerpt` and `excerpt_length` arguments also apply to the post-list shortcode. If you set `show_excerpt` to 1 or "true", the post excerpt will be shown if it exists. If it doesn't exist (or is empty), the post content will be shown (with HTML stripped out of it). You can truncate the length of the excerpts that are shown in the post list by setting the `excerpt_length` value. The `excerpt_length` is measured in words, so if you would like each excerpt to display no more than 50 words, you would set the `excerpt_length` parameter to 50. If you leave it set to 0 (which is the default), the entire excerpt or content will be shown in the post list. In the `post-list` shortcode, if `show_excerpt` is set to 0 or false, no content will be shown in the list (as opposed to the behavior of the `show_excerpt` parameter in the `post-content` shortcode).

To read more about the other arguments, please [visit the codex page for the `get_posts()` function](http://codex.wordpress.org/Function_Reference/get_posts).

If you are looking to display a list of attachments in a post, rather than displaying a list of posts or pages, you might want to check out the [List Attachments Shortcode plugin](http://wordpress.org/extend/plugins/list-attachments-shortcode/) instead.

**Multisite - Pulling Posts From Another Blog**

To pull a list of posts from another blog, simply provide the blog's ID as the `blog_id` argument in the shortcode. With that argument, this plugin will pull a list of posts that match the other criteria you provided. If the `blog_id` argument is provided, and the `blog_id` doesn't match the ID of the current blog, the `exclude_current` argument will be ignored (otherwise, this plugin would automatically exclude whatever post on the other blog happens to have the same ID as the current post).

When the list is displayed, shortlinks (that blog's URL with `?p=[post_id]`) will be used, rather than the proper permalink, since it would require a lot more resources to build the proper permalink.

The usage would look something like:

`
[post-list blog_id=12 post_type="page"]
`

When displaying a post list, you can use any `post_type` that is registered on that blog (that post_type does not have to be registered on the current site).

To display the content of a single post from another blog, again, simply provide the blog's ID as the `blog_id` argument. That will pull the content of that post. Unfortunately, at this time, there is no way to invoke all of the plugins from the blog from which you're pulling the content, so any shortcodes, filters, etc. that may be active on the source blog will not be parsed when the content is displayed on the current blog. Obviously, if all of the same plugins and themes are active (or, if any plugins/themes that introduce shortcodes and filters are active) on both the source blog and the current blog, then there is nothing to worry about.

The usage would look something like:

`
[post-content blog_id=12 id=25]
`

That would pull the content for the post with an ID of 25 from the blog with an ID of 12.

## Installation ##

### Automatic Installation ###

The easiest way to install this plugin automatically from within your administration area.

1. Go to Plugins -&gt; Add New in your administration area, then search for the plugin "Post Content Shortcodes".
1. Click the "Install" button.
1. Go to the Plugins dashboard and "Activate" the plugin (for MultiSite users, you can safely "Network Activate" this plugin).

### Manual Installation ###

If that doesn't work, or if you prefer to install it manually, you have two options.

**Upload the ZIP**

1. Download the ZIP file from the WordPress plugin repository.
1. Go to Plugins -&gt; Add New -&gt; Upload in your administration area.
1. Click the "Browse" (or "Choose File") button and find the ZIP file you downloaded.
1. Click the "Upload" button.
1. Go to the Plugins dashboard and "Activate" the plugin (for MultiSite users, you can safely "Network Activate" this plugin).

**FTP Installation**

1. Download the ZIP file from the WordPress plugin repository.
1. Unzip the file somewhere on your harddrive.
1. FTP into your Web server and navigate to the /wp-content/plugins directory.
1. Upload the post-content-shortcodes folder and all of its contents into your plugins directory.
1. Go to the Plugins dashboard and "Activate" the plugin (for MultiSite users, you can safely "Network Activate" this plugin).

### Must-Use Installation ###

If you would like to **force** this plugin to be active (generally only useful for Multi Site installations) without an option to deactivate it, you can upload the post-content-shortcodes.php & class-post-content-shortcodes.php files to your /wp-content/mu-plugins folder. If the mu-plugins folder does not exist, you can safely create it. Make sure **not** to upload the post-content-shortcodes *folder* into your mu-plugins directory, as "Must Use" plugins must reside in the root mu-plugins directory in order to work.

## Frequently Asked Questions ##

### How do I use this plugin? ###

To display the content of a single post within another post, you want to use the `[post-content]` shortcode. To display the content of the post with an ID of 25 and a slug of 'this-is-my-cool-post', the usage would look like:

`
[post-content id=25]
`

or

`
[post-content post_name="this-is-my-cool-post"]
`

To display a list of posts within another post, you want to use the `[post-list]` shortcode. To display a list of all pages (post_type=page) on this site, the usage would look like:

`
[post-list post_type="page"]
`

By default, this plugin will display **all** posts that match the specified criteria (except for the current post). To limit the number of posts that are displayed, you should add the `numberposts` argument to the shortcode. That would look like:

`
[post-list post_type="page" numberposts=15]
`

### Does the shortcode output any extra HTML? ###

The `[post-content]` shortcode will not output any extra HTML at all. It simply outputs the content of the page being cloned. The original title is not output, nor is any sort of wrapper HTML.

The `[post-list]` shortcode, however, does output some HTML to actually format the list. The default HTML code output looks like:

`
&lt;ul class="post-list"&gt;
&lt;li class="listed-post"&gt;&lt;a href="%permalink%" title="%title%"&gt;%title&lt;/a&gt;&lt;/li&gt;
&lt;/ul&gt;
`

### How do I change the HTML output for the post-list? ###

There are some filters available within the plugin that can alter the HTML generated by the shortcode. Those filters are as follows:

* **post-content-shortcodes-open-list** - filters the opening '&lt;ul&gt;' tag
* **post-content-shortcodes-open-item** - filters the opening '&lt;li&gt;' tag
* **post-content-shortcodes-item-link-open** - filters the opening '&lt;a&gt;' tag. Three parameters are available with this filter. The constructed '&lt;a&gt;' tag is sent as the first parameter, the permalink is the second and the title attribute is the third. The 'the_permalink' filter is applied to the permalink before it is sent or used, and the 'the_title_attribute' filter is applied to the title attribute before it is sent or used.
* **post-content-shortcodes-item-link-close** - filters the closing '&lt;/a&gt;' tag
* **post-content-shortcodes-close-item** - filters the closing '&lt;/li&gt;' tag
* **post-content-shortcodes-close-list** - filters the closing '&lt;/ul&gt;' tag

### Are there any other filters in the plugin? ###

Yes.

* If the `[post-list]` shortcode retrieves an empty list of posts/pages, it will normally return an empty string (so as not to disrupt the flow of the page). However, you can have the shortcode output a custom error message by hooking into the `post-content-shortcodes-no-posts-error` filter.
* If you would like to use a different set of default values for the shortcode arguments, you can hook into the `post-content-shortcodes-defaults` filter. The array of default arguments is passed to that filter before it gets used.
* If you would like to alter the output of the `[post-content]` shortcode (for instance, to wrap it in an HTML container, or to add content before or after), you can hook into the `post-content-shortcodes-content` filter. The constructed HTML output is passed as the first parameter, and the WordPress post object is passed as a second parameter.
* If you would like to change the "Read More" link used in the `[post-content]` shortcode (if you are limiting the length of the content/excerpt), you can use the `post-content-shortcodes-read-more` filter to do so.
* If you would like to modify the class used on the featured image (if appropriate), you can use the `post-content-shortcodes-image-class` to do that.
* By default, the plugin uses 'thumbnail' as the size of the featured image. If you would like to use a different registered size, you can change that with the `post-content-shortcodes-default-image-size` filter.

### Why isn't the current post included in the list of posts? ###

By default, the `[post-list]` shortcode excludes the current post (since that would cause somewhat of a loop in the user's mind; clicking on a link in the page only to have the page reload with the same content). To allow the current post to be displayed in the list of posts, set the `exclude_current` argument to `0`. That might look something like:

`
[post-list exclude_current=0]
`

### How do I pull posts from another blog in the network? ###

Use the `blog` attribute to specify which site/blog the post should be pulled from. The `blog` attribute can accept a blog ID (numeric) or a blog name (the slug of the blog).

### Will this plugin work in a multisite environment? ###

Yes. You can safely network-activate this plugin, or even use it as a mu-plugin. To pull a post with a slug of 'this-is-my-cool-post' from a blog with an ID of 10 and a slug of 'mycoolsite', the usage would look something like:

`
[post-content post_name="this-is-my-cool-post" blog=10]
`

or

`
[post-content post_name="this-is-my-cool-post" blog="mycoolsite"]
`

### Will this plugin work with multinetwork? ###

Yes. The way this plugin works, there is no distinction between multi-network & multisite. You can use the `blog_id` argument to pull posts from any site in the entire multi-network installation; regardless of which network they fall under.

### Why is my page getting all messed up when I use this? ###

There is a known issue where HTML (especially [caption] shortcodes) within the excerpt can break the entire page. In order to avoid this, be sure to place the <!-- more --> tag above the [caption] shortcode within the posts being pulled into the post-list shortcode.

## Changelog ##

### 0.5.6 ###

* Fixes issue with widgets disappearing in 4.3

### 0.5 ###

* Fixes error when used in some multisite/non-multisite instances, due to improper checking for multisite
* Fixes bug that stopped the date from showing up in the Post List shortcode/widget
* Remove calls to old-style widget constructor
* Update compatibility

### 0.4.1 ###

* Fix [strict standards warning](https://wordpress.org/support/topic/many-strict-standards-errors) about widget methods - h/t [ux4341](https://wordpress.org/support/profile/ux4341)
* Special thanks also to [spivurno](https://wordpress.org/support/profile/spivurno) for assistance in identifying the strict standards warnings

### 0.4 ###

* Test for 4.0 compatibility
* Minor bug fixes
* Add "current-post-item" CSS class to the appropriate post within the post-list in response to [request from thomas.mery](http://wordpress.org/support/topic/how-to-add-active-class-to-listed-post-li-output?replies=1)
* Add post ID and shortcode attributes to items that can be sent through the `post-content-shortcodes-open-item` filter
* Add post object and shortcode attributes to items sent through most filters
* Add new `post-content-shortcodes-include-thumbnail` filter to change the way the thumbnail is included in post content (if desired)

### 0.3.4.1 ###

* Minor bug fix: On multisite, when pulling items with a custom taxonomy from another blog, an empty list would be returned because the taxonomy wasn't registered

### 0.3.4 ###

* Implement admin options for plugin
* Implement post content widget
* Implement post list widget
* Allow disabling default styles
* Attempt to fix issue with unbalanced shortcodes and HTML tags in post excerpts
* Add shortcode option to strip all HTML from post excerpts
* Begin implementing option to show comments with posts
* Remove manual database calls in favor of new, optimized `switch_to_blog()`
* Improve performance
* Fix bug that stopped images from being displayed on cross-site post lists
* Added ability to specify blog name (slug) rather than blog ID to pull posts from another site
* Added ability to specify post slug rather than post ID to pull post

### 0.3.3 ###

* Fix bug with the number of posts returned by `post_list` shortcode
* Attempt to add tax_query args to `post_list` shortcode
* Fix bug with category parameter
* Test compatibility with 3.6
* Fix image size bug when only width or height is defined (previously, the other dimension defaulted to 0; now, it defaults to 9999999px, instead, to ensure that the specified dimension is used)
* Start to flesh out the widgets a little more

### 0.3.2.1 ###

* Fix image size bug introduced in 0.3.2

### 0.3.2 ###

* Fix bug with the way post-list transients were stored (and therefore retrieved)
* Update class names to better match [WP Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards)
* Add `pcsc-transient-timeout` filter for transient timeout (to allow shorter or longer caching of data)
* Add ability to display title at top of `post-content` shortcode using the `show_title` attribute.

### 0.3.1 ###

* Urgent bugfix (post-content shortcode wasn't showing content)

### 0.3 ###

* Added ability to display content of a post from another site in a multisite installation
* Added ability to list posts from another site in a multisite installation (uses shortlinks rather than permalinks)
* Fixed bug in orderby parameter of post-list shortcode
* Reduced transient timeout from 24 hours to 1 hour
* Added widgets to plugin (one to display a list of posts and one to display a single post)
* Added ability to display excerpt instead of content in post-content shortcode
* Added ability to limit length of content/excerpt shown in post-content shortcode
* Added ability to display featured image with post-content

### 0.2 ###

* Attempted to fix issue with original readme file (no info from readme was showing up in the WordPress repo)

### 0.1a ###

This is the first version of this plugin

## Upgrade Notice ##

### 0.5.6 ###

* Fixes issue with widgets disappearing in WP 4.3

### 0.5 ###

* Finally fixes stupid bug that stopped dates from showing up in Post List shortcode/widget

### 0.4.1 ###

* Fixes warnings that appear when PHP is in strict standards mode
* No new functionality added

### 0.3.4.1 ###

* Multisite bug fix: Custom taxonomies stopped working when pulling from another blog

### 0.3.4 ###

* Added plugin settings, allowing users to disable default style sheet
* Implemented post content and post list widgets
* Added feature to allow choosing blog and post by slug, rather than ID

### 0.3.2.1 ###

* Quick fix for image size bug

### 0.3.1 ###

* Fixes major bug in previous version, where content wasn't displayed with post-content shortcode.

### 0.3 ###

* This is a feature update. It adds quite a few new functions to the plugin, including the ability to pull posts across sites within a multisite environment.

## To Do ##

* Add AJAX features to allow user to choose from a list of posts/sites, instead of requiring them to manually enter the ID
* Add ability to wrap featured images with a link
* Add ability to paginate the [post-list] shortcode
