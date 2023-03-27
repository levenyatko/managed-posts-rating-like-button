=== Managed posts rating ★ Like button ===
Contributors: levenyatko
Tags: rating, voting, rating system, star rating
Requires at least: 4.9
Tested up to: 6.1.1
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin adds a rating system to your WordPress site.

== Description ==

The plugin adds a rating system to your WordPress site.

* Lightweight
* Add rating functionality to your website automatically or use shortcodes
* Page with detailed information about each rating such as time, IP (optionally), username, title etc.
* Easily change ratings in the admin panel
* Supports structured data for rich snippets
* Custom templates for complete customization

=== Usage ===

* The easiest way to add a rating to page - use the "Before Content" and "After Content" display settings.
* You can also add a rating using the shortcode `[mpr-button]`
* If you want to embed other post ratings use `[mpr-button id="1" disabled="false" ]`, where 1 is the ID of the post/page ratings that you want to display.
* You can show post rating via function `mpr_button(['id' => 1, 'disabled' => false, 'return' => false ])`

== Installation ==

1. Upload the entire `mpr-likebtn` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the "MPRating"->"Settings" item on the admin menu
4. Check the plugin settings and save them.

That's it. You're done!

== Frequently Asked Questions ==

= How to customize rating template? =

Create an `mpr` folder in your theme and copy the `partials/front` folder into it.

== Screenshots ==

1. MPR Plugin Settings page in default state
2. New Rating column for posts list table in the admin pannel
3. Shortcode to show rating star manually and panel to add rating for the post by hands
4. Voting log page for all posts
5. Voting log filtered by page

== Additional Info ==

=== Filters ===

**mpr_edit_meta_box_screens** - list of screens on which the metabox of manual rating addition will be displayed.

*apply_filters('mpr_edit_meta_box_screens', $screens);*

**mpr_log_table_headings** - list of headings for log table

*apply_filters('mpr_log_table_headings', []);*

**mpr_log_table_row** - filter data to display in log table

*apply_filters('mpr_log_table_row', $result );*

**mpr_column_screens_display** - post types list on which the rating column will be displayed.

*apply_filters('mpr_column_screens_display', $screens);*

**mpr_is_user_can_vote** - if you need change current user caps to voting

*apply_filters('mpr_is_user_can_vote', $is_vote_allowed);*

**mpr_allowed_settings_cell_tags** - allowed tags to display in the logs table cell.

*$allowed_cell_tags = apply_filters('mpr_allowed_settings_cell_tags', $allowed_cell_tags);*

=== Actions ===

**mpr_after_post_voted** - is fired after a new voting record is added to the log

*do_action( 'mpr_after_post_voted', $post_id, $rating_value, $parent_id, $log_row_id );*

== Changelog ==

= 1.1.0 =
* Added escaping for echoed variables
* Fixed frontend CSS
* Fixed frontend JS multiple AJAX calls

= 1.0.1 =
* Removed unused code
* Added readme.txt file

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.0.1 =
