# Managed posts rating ★ Like button

Rating system for your WordPress site with a simple "like" button and advanced admin panel.

To install the plugin you could download package from this repository or install plugin as usual from WordPress plugins page.

## Highlights

* Lightweight.
* Integrate the like button automatically or use shortcodes for custom placement.
* Access a detailed logs page to track user interactions and ratings.
* The chart page displays users' voting activity.
* Ability to allow only logged-in users to vote.
* Ability to customize the maximum number of votes per post from one user.
* Easy voting management.
* Ability to rewrite the voting button template in your theme.

## Usage

To automatically add the "like" button to your posts in the admin panel
* Go to the "MPRating" -> "Settings" page
* Change the "Display" select value to "Before Content" or "After Content"
* Save settings

For more advanced control, select the "Manually" value for the "Display" select and use the provided shortcodes in your post content or templates:
* `[mpr-button]` - Display the like button.
* `[mpr-button id="XX" disabled="false"]` - Display the like button for a specific post (replace "XX" with the post ID). Use the "disabled" attribute if you want to show the "like" button but disallow voting.

You can also display the voting button using the mpr_button function. The function parameters are similar to the shortcode.
`mpr_button(['id' => 1, 'disabled' => false, 'return' => false ]);`

## Frequently Asked Questions

### How to customize rating template?

Create an `mpr` folder in your theme and copy the `partials/front` folder into it.
