=== Managed posts rating ★ Like button ===
Contributors: levenyatko
Tags: like button, rating, voting, rating system, rate post
Requires at least: 4.9
Tested up to: 6.3
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Rating system for your WordPress site with a simple "like" button and advanced admin panel.

== Description ==
The Managed posts rating ★ Like button plugin is a rating system for your WordPress site with a simple "like" button and advanced admin panel.
This lightweight plugin empowers you to enhance user engagement by enabling rating functionality for your posts, pages, or any custom post type. You can automatically integrate the like button or use shortcodes to customize its placement.

== Key Features ==
- Lightweight.
- Integrate the like button automatically or use shortcodes for custom placement.
- Access a detailed logs page to track user interactions and ratings.
- The chart page displays users' voting activity.
- Ability to allow only logged-in users to vote.
- Ability to customize the maximum number of votes per post from one user.
- Easy voting management.
- Ability to rewrite the voting button template in your theme.

== Usage ==
To automatically add the "like" button to your posts in the admin panel
- Go to the "MPRating" -> "Settings" page
- Change the "Display" select value to "Before Content" or "After Content"
- Save settings

For more advanced control, select the "Manually" value for the "Display" select and use the provided shortcodes in your post content or templates:
- `[mpr-button]` - Display the like button.
- `[mpr-button id="XX" disabled="false"]` - Display the like button for a specific post (replace "XX" with the post ID). Use the "disabled" attribute if you want to show the "like" button but disallow voting.

You can also display the voting button using the mpr_button function. The function parameters are similar to the shortcode.
`mpr_button(['id' => 1, 'disabled' => false, 'return' => false ]);`

== Admin Panel ==

Visit the "MPRating" section in your WordPress admin dashboard to access the admin panel. From here, you can:
- View and manage user ratings.
- Customize the plugin settings to match your preferences.

== Installation ==

**Modern way**

1. In the admin panel to the Plugins' menu in WordPress.
2. On the top of the page press the 'Add new' button.
3. In the search field type 'Managed posts rating', then click 'Search Plugins' or press Enter.
4. Once you’ve found it, install it by clicking 'Install Now' and WordPress will take it from there.
5. Activate the plugin after installation.
6. Go to the "MPRating" -> "Settings" page to configure the plugin.

**Manually**

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the "MPRating" -> "Settings" page to configure the plugin.

== Frequently Asked Questions ==

= How do I add the like button to my posts? =

The like button can be added automatically. Go to the "MPRating" -> "Settings" page, select the post types in which you want to display the "Like" button, and specify the location where you want to add the button.
You can also use the provided shortcodes to add the button to specific posts or templates.

= How to customize the rating template? =

Create an `mpr` folder in your theme and copy the `partials/front` folder into it.

= Is the plugin compatible with custom post types? =

Yes, the plugin works with custom post types. Make sure that you enabled support of new CPT on plugin settings page.

= Can I translate the plugin into other languages? =

Yes, the plugin is translation-ready. You can use tools like WPML or Loco Translate to create translations for different languages.

= Does the plugin slow down my website? =

No, the plugin is designed to be lightweight and efficient, ensuring that it doesn't negatively impact your website's performance.

== Screenshots ==

1. MPR Plugin Settings page in default state
2. New Rating column for posts list table in the admin panel
3. Shortcode to show rating star manually and panel to add a rating for the post by hands
4. Voting log page for all posts
5. Chart page with statistic graphs

== Changelog ==

= 2.0.0 =
* Updated Logs page display
* Added a page with a chart to display voting activity
* Fixed rating block display on WooCommerce pages
* Added the 'mpr_get_post_rating' function to retrieve post total rating or rating for the specified time period
* Updated Tested up to WP version
* Updated Readme files

= 1.2.0 =
* Added display settings for the "like" button on unsupported post types
* Added mpr_is_user_can_vote_unsupported filter to change if a user can vote for the post unsupported post type.
* Added the_mpr_button_shortcode_hidden filter to show notification shortcode when display disabled.
* Added Compatibility with WordPress 6.3.

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
