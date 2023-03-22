=== UCF News Plugin ===
Contributors: ucfwebcom
Requires at least: 5.3
Tested up to: 6.1
Stable tag: 3.0.2
Requires PHP: 7.0
License: GPLv3 or later
License URI: http://www.gnu.org/copyleft/gpl-3.0.html

Provides a shortcode, widget, and functions for displaying UCF news.


== Description ==

This plugin provides a shortcode, widget, and helper functions for displaying news stories from [UCF Today](https://www.ucf.edu/news/).  It is written to work out-of-the-box for non-programmers, but is also extensible and customizable for developers.


== Documentation ==

Head over to the [UCF News Plugin wiki](https://github.com/UCF/UCF-News-Plugin/wiki) for detailed information about this plugin, installation instructions, and more.


== Changelog ==

= 3.0.2 =
Enhancements:
* Added composer file.

= 3.0.1 =
Enhancements:
* Explicitly unset underline styles on news item links to reduce visual clutter in anticipation of themes updating to Athena v1.1.1.  Added underlines on hover/focus where there are no other visual differences in those states.
* Replaced usage of nonexistent `.text-small` class in external stories default layout with `.font-size-sm`
* Upgraded packages

= 3.0.0 =
Enhancements:
* Updated all [ucf-news-feed] layouts provided out-of-the-box to utilize Athena Framework classes.  **This is a breaking change**--please read below ("Upgrade Notice") for details before upgrading this plugin in production.

  This update will make it easier to maintain layouts moving forward, and provides a performance benefit by no longer needing to enqueue an additional stylesheet on every front-facing post/page.
* Removed the "Include Default CSS" plugin option and associated stylesheet.
* Upgraded packages, and removed packages and gulp tasks related to CSS minification.

Bug Fixes:
* Fixed an issue where Today stories with a video header (and, therefore, an oembed poster as a thumbnail) would never display a thumbnail in [ucf-news-feed] layouts.

Upgrade Notice:
* A theme that includes the Athena Framework is now _required_ for layouts provided by the plugin to display as intended.

  You should be able to safely upgrade to v3.0.0 of this plugin if your site meets all of the following criteria:
  * Your site uses the UCF WordPress Theme v0.7.2+, Colleges Theme v1.1.2+, or any other WordPress theme that includes the Athena Framework v1.1.0+
  * Your site _does not_ reference existing CSS classes from the "classic", "modern" or "card" layouts (such as `.ucf-news-item`, `.ucf-news-thumbnail`) in custom layouts, markup on posts/pages, or markup within your theme or another plugin

  However, we recommend testing this upgrade in a development/QA environment before upgrading in production.

  If your site does include references to CSS classes from the "classic", "modern" or "card" layouts and depends on them for styling, you should update or remove them prior to upgrading this plugin.

  If your site's active theme does not include the Athena Framework, you can still [override and/or define your own custom layouts](https://github.com/UCF/UCF-News-Plugin/wiki/Custom-Layouts) as needed.

= 2.4.1 =
Bug Fixes:
* Added default `search` option value to `UCF_News_Config::$default_options`, fixing some undefined index notices.

= 2.4.0 =
Enhancements:
* Added new `search` arg for the `[ucf-news-feed]` shortcode, which allows you to filter results by a search query.
* Tidied up `UCF_News_Feed::get_news_items()` and adjusted `UCF_News_Feed::format_tax_arg()` a bit to omit unset/empty query args from the final feed URL that's requested, and support custom feed URLs that already include query args.
* Upgraded packages

= 2.3.2 =
Bug fixes:
* Added missing `show_image` field for the news widget, and updated widget update logic to ensure its value is saved properly (and ensures images are shown by default in layouts that support them).

= 2.3.1 =
Enhancements:
* Admin options page improvements (props @strmtrpr83):
  * Added the WordPress CSS class 'large-text' to both feed URL inputs so the whole feed URL is visible.
  * Hid the Fallback Image preview img tag when there is no URL instead of showing a broken image tag.
  * Updated the <img> tag: added alt text, removed the hard coded 'width' / 'height' and set 'max-width' and 'max-height' to 150px. This keeps the image from distoring in case the user selects a non square image and makes the image preview a little larger.
  * Added the built in WordPress button CSS classes 'btn' and 'btn-primary' to the Upload link to make it a button.
  * Changed the 'Upload' text to 'Add Image' when no fallback image is selected. It will automatically change to 'Change Image' when a fallback image is selected.
  * Changed the 'Upload Image' text in the Media Library pop-up to 'Select Image'. It's more inline with the final user action.
  * Added text for the user when no fallback image has been selected. Before the broken img tag could be cosntrued as an image being selected but failing to load. This message will automatically hide on adding/selecting an image.
  * Added a 'Remove Image' button that will hide the preview img tag, set the ucf_news_fallback_image hidden field to '', display the no fallback image text, hide the 'Remove Image' button and change the 'Change Image' button back to say 'Add Image'.

= 2.3.0 =
Enhancements:
* Added a `show_image` option to the `ucf-news-feed` shortcode, specifically used by the `card` layout. When set to false, the image from the feed will not be displayed.
* Added the `ucf-statements` shortcode, which provides a way of listing `statement` posts from the News website.
* Added the `classic` layout for the `ucf-statements` shortcode.

= 2.2.2 =
Enhancements:
* Added `width`/`height` attributes to `<img>` tags in this plugin's provided layouts that utilize story thumbnails.  Themes and other plugins can utilize `UCF_News_Common::get_story_img_tag()` to display thumbnails with these attributes applied in custom layouts.
* Updated required files in main plugin files to use absolute paths.
* Upgraded packages

Upgrade notice:
* PHP 7.x is now required.

= 2.2.1 =
Bug Fixes:
* Updated to ensure the `ucf_external_stories_feed_url` has a default value even if there isn't one in the options table.

= 2.2.0 =
Enhancements:
* Added ucf-external-stories shortcode.

= 2.1.9 =
Enhancements:
* Added functions for retrieving a news story's primary category (section) and tag (topic)
* Updated the "modern" news list layout to display the story's primary category (section), if available
* Added plugin version number to enqueued plugin assets for cache-busting purposes
* WordPress Shortcode Interface integration improvements:
  * Added missing `offset` param to WP SCIF shortcode registration
  * Fixed descriptions for `sections` and `topics` params to note that term slugs are expected (not IDs)
* Updated repo packages; added linter configs; added Github issue templates and CONTRIBUTING doc

= 2.1.8 =
Enhancements:
* Removed usage of `create_function()` throughout the plugin for improved compatibility with newer versions of PHP.
* Updated today.ucf.edu references in the plugin to ucf.edu/news/

= 2.1.7 =
Enhancements:
* Added ability to override the default URL per shortcode, specifically so the `main-site-stories` feed on /news/ can be used.

= 2.1.6 =
Enhancements:
* In preparation for a rebuilt UCF Today site, `UCF_News_Common::get_story_image_or_fallback()` has been modified to prioritize the custom `thumbnail` feed value when retrieving a story's image.  If the `thumbnail` value isn't present in the feed, WordPress's standard media details will be referenced, like before, and the "medium" thumbnail size is returned instead.
* Updated `.ucf-news-thumbnail-image` class to force thumbnails to span the full width of their parent container (in case a very small thumbnail is returned for some reason).

= 2.1.5 =
Enhancements:
* Removed duplicate hard-coded default feed url values throughout the plugin

Bug Fixes:
* Updated default `ucf_news_feed_url` option value to exclude "/posts/", so this url works out-of-the-box
* Fixed handling of invalid feed results in provided news layouts: layouts now avoid accessing non-existent object properties when feed results return `false`.

= 2.1.4 =
Enhancements:
* Added Github Plugin URI to allow for installation from Github plugin.

= 2.1.3 =
Bug Fixes:
* Added missing default `offset` value in `UCF_News_Config::$default_options`
* Updated widget markup to respect `before_widget` and `after_widget` markup defined in themes

Enhancements:
* Added http_timeout setting to allow for adjustment.

= 2.1.2 =
Bug Fixes:
* Added some hardening to `UCF_News_Common::get_story_image_or_fallback()` to account for stories that may have an invalid `$featured_media` object/broken thumbnails
* Fixed typo in plugin deactivation function name
* Fixed WP Shortcode Interface registration

Enhancements:
* Added conditional WP Shortcode Interface preview styles

= 2.1.1 =
Bug Fixes:
* Added activation and deactivation hooks to handle default options.

= 2.1.0 =
Enhancements:
* Added `$fallback_message` parameter to allow a no results message to be customized. Add the message by inserting it in between the opening and closing shortcodes (the content area), i.e. `[ucf-news-feed]<insert message here>[/ucf-news-feed]`.

= 2.0.0 =
Enhancements:
* Updated `UCF_News_Common::display_news_items()` to render layout parts using filters instead of actions.  Please note this change is not backward-compatible with layouts registered using hooks provided by older versions of the plugin.

= 1.1.4 =
Bug Fixes:
* Fixed `display_news_items()` in `UCF_News_Common` not being declared as a static method
* Updated filtering of options in `UCF_News_Feed::get_news_items()` to allow 0 values, fixing undefined index notices in some cases.

= 1.1.3 =
Enhancements:
* Updated mobile styles for card layouts.

= 1.1.2 =
Enhancements:
* Added modern and card layouts.

= 1.1.1 =
Bug Fixes:
* Make sure UCF_News_Feed::get_news_items() always has a feed_url set, even if the plugin option's value is empty (thanks @jorgedonoso!)

= 1.1.0 =
Bug Fixes:
* Updated to match the new query params available on UCF Today.

= 1.0.4 (Deprecated) =
Bug Fixes:
* Updates the way the news feed is pulled to prevent error when accessing external host.

= 1.0.3 =
Enhancements:
* Adds empty alt tag to classic layout images for accessibility.

= 1.0.2 =
Bug Fixes:
* Corrects filter name from category to category_name.

= 1.0.1 =
Bug Fixes:
* Corrects a bug with sections and topics filters.

= 1.0.0 =
* Initial release


== Upgrade Notice ==

* v3.0.0: All layouts provided by the plugin require the Athena Framework to display as intended.  [More details](#300)
* v2.2.2: PHP 7.0+ is required.


== Development ==

Note that compiled, minified JS files are included within the repo.  Changes to these files should be tracked via git (so that users installing the plugin using traditional installation methods will have a working plugin out-of-the-box.)

[Enabling debug mode](https://codex.wordpress.org/Debugging_in_WordPress) in your `wp-config.php` file is recommended during development to help catch warnings and bugs.

= Requirements =
* node
* gulp-cli

= Instructions =
1. Clone the UCF-News-Plugin repo into your local development environment, within your WordPress installation's `plugins/` directory: `git clone https://github.com/UCF/UCF-News-Plugin.git`
2. `cd` into the new UCF-News-Plugin directory, and run `npm install` to install required packages for development into `node_modules/` within the repo
3. Optional: If you'd like to enable [BrowserSync](https://browsersync.io) for local development, or make other changes to this project's default gulp configuration, copy `gulp-config.template.json`, make any desired changes, and save as `gulp-config.json`.

    To enable BrowserSync, set `sync` to `true` and assign `target` the base URL of a site on your local WordPress instance that will use this plugin, such as `http://localhost/wordpress/my-site/`.  Your `target` value will vary depending on your local host setup.

    The full list of modifiable config values can be viewed in `gulpfile.js` (see `config` variable).
3. Run `gulp default` to process front-end assets.
4. If you haven't already done so, create a new WordPress site on your development environment to test this plugin against.
5. Activate this plugin on your development WordPress site.
6. Configure plugin settings from the WordPress admin under "UCF News".
7. Run `gulp watch` to continuously watch changes to JS files.  If you enabled BrowserSync in `gulp-config.json`, it will also reload your browser when plugin files change.

= Other Notes =
* This plugin's README.md file is automatically generated. Please only make modifications to the README.txt file, and make sure the `gulp readme` command has been run before committing README changes.  See the [contributing guidelines](https://github.com/UCF/UCF-News-Plugin/blob/master/CONTRIBUTING.md) for more information.


== Contributing ==

Want to submit a bug report or feature request?  Check out our [contributing guidelines](https://github.com/UCF/UCF-News-Plugin/blob/master/CONTRIBUTING.md) for more information.  We'd love to hear from you!
