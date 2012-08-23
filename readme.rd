# Simple Taxonomy 

WordPress 3.1 and up allow for reasonably simple custom taxonomy, this plugin makes it even simpler, removing the need for you to write <em>any</em> code.

* Contributors: http://profiles.wordpress.org/momo360modena/
* Donate link: http://www.beapi.fr/donate/
* Tags: tags, taxonomies, custom taxonomies, taxonomy, category, categories, hierarchical, termmeta, meta, term meta, term conversion, conversion
* Requires at least: 3.1
* Stable tag: 3.4.1
* Tested up to: 3.4.1
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description

WordPress 3.1 and up allow for reasonably simple custom taxonomy, this plugin makes it even simpler, removing the need for you to write <em>any</em> code.

Add support for taxonomy with or without hierarchical. Choose the selector type for write post.

Allow to add taxonomy to any objects registered on your installation. This plugin can be coupled with the plugin Custom Fields for WordPress to a new term meta database that allow to create on fly any field need for the taxonomy. For example, for a "Number" taxonomy, you can add fields : number ID, date of parution, screenshot, etc !

This plugin is developped on WordPress 3.3, with the constant WP_DEBUG to TRUE.

This plugin allows you to add taxonomy just by giving them a name and somes options in the backend. It then creates the taxonomy for you, takes care of the URL rewrites, provides a widget you can use to display a "taxonomy cloud" or a list of all the stuff in there, and it allows you to show the taxonomy contents at the end of posts and excerpts as well.
To boot, it comes with a set of template tags for those who don't mind adapting their own theme.

For full info go the [Simple Taxonomy](https://github.com/herewithme/simple-taxonomy) page.

## Frequently Asked Questions

### Does this plugin handles custom fields?

No, we prefer to use 2 plugins specifically designed to meet two different needs ...

### How to create a custom post type?

You can use a another plugin writted by Me and BeAPI :

* [Simple Custom Post Types](http://wordpress.org/extend/plugins/simple-post-types/)

### How to create a custom role?

You must install a plugin for managing roles and permissions as:

* [Use role editor](http://wordpress.org/extend/plugins/user-role-editor/)
* [Capability Manager](http://wordpress.org/extend/plugins/capsman/)

## Installation

 **Required PHP5.**

1. Download, unzip and upload to your WordPress plugins directory
2. Activate the plugin within you WordPress Administration Backend
3. Go to Settings > Custom Taxonomies and follow the steps on the [Simple Taxonomy](https://github.com/herewithme/simple-taxonomy) page.

## Changelog

* Version 3.4.1 :
	* Fix a bug with query_var on rewrite rules (thanks to Chris Talkington)
	* Change PHP for use __construct()
	* Change PHP for use static function instead class object
	* Change PHP for add private/public for all methods
	* Use WP API for manage actions message
	* Move external CSS to inline (only one line on file... skip 1 http connection)
	* Remove obsolete code
* Version 3.4 :
	* Add export PHP feature
	* Mark as compatible with WP 3.4.1
* Version 3.3.1 :
	* Fix hardcoded french translation
	* Update POT and french translation
* Version 3.3 :
	* Add compatibility 3.3
	* Rewrite convert term taxonomy tools (use bulk dropdown instead specific pages)
	* Add custom taxonomies on dashboard
* Version 3.2.1.1 :
	* Fix possible bug with others plugin's beapi that use import 
* Version 3.2.1 :
	* Fix possible bug with others plugin's beapi that use export
* Version 3.2 :
	* Fix a bug that not call permalink generation when taxonomy is updated/created/deleted (thanks to bniess for reporting)
	* Add a tools for export/import plugin configuration
* Version 3.1.3 :
	* Fix a bug with custom taxonomy that not display on custom post types.
	* Tested on WordPress 3.2
* Version 3.1.2 :
	* Fix a bug with select taxonomies on widget
* Version 3.1.1 :
	* Fix a bug with flush permalink on admin
	* Use default post callback for term count (count only publish object)
* Version 3.1 :
	* Make compatible 3.1
	* Remove unused functions
	* Add redirect on admin settings for skip refreshing
	* Add flush rewriting when settings is upated
	* Add tools for import terms from text/words
	* Add remove and flush with JS confirmation
	* Fix notice PHP
* Version 3.0.0
	* Initial version
