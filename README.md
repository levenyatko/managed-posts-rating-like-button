# Managed posts rating - Like button

The plugin adds a rating system to your WordPress site.

## Highlights

* Lightweight
* Add rating functionality to your website automatically or use shortcodes
* Page with detailed information about each rating such as time, IP (optionally), username, title etc.
* Easily change ratings in the admin panel
* Supports structured data for rich snippets
* Custom templates for complete customization

## Usage

* The easiest way to add a rating to page - use the "Before Content" and "After Content" display settings.
* You can also add a rating using the shortcode `[mpr-button]`
* If you want to embed other post ratings use `[mpr-button id="1" disabled="false" ]`, where 1 is the ID of the post/page ratings that you want to display.
* You can show post rating via function `mpr_button(['id' => 1, 'disabled' => false, 'return' => false ])`

## For developers

### Filters

`mpr_edit_meta_box_screens` - list of screens on which the metabox of manual rating addition will be displayed.

```php
apply_filters('mpr_edit_meta_box_screens', $screens);
```

`mpr_log_table_headings` - list of headings for log table

```php
apply_filters('mpr_log_table_headings', []);
```

`mpr_log_table_row` - filter data to display in log table

```php
apply_filters('mpr_log_table_row', $result );
```

`mpr_column_screens_display` - post types list on which the rating column will be displayed.

```php
apply_filters('mpr_column_screens_display', $screens);
```

`mpr_is_user_can_vote` - if you need change current user caps to voting

```php
apply_filters('mpr_is_user_can_vote', $is_vote_allowed);
```

`mpr_allowed_settings_cell_tags` - allowed tags to display in the logs table cell.

```php
$allowed_cell_tags = apply_filters('mpr_allowed_settings_cell_tags', $allowed_cell_tags);
```

### Actions

`mpr_after_post_voted` - is fired after a new voting record is added to the log

```php
do_action( 'mpr_after_post_voted', $post_id, $rating_value, $parent_id, $log_row_id );
```

## Frequently Asked Questions

### How to customize rating template?

Create an `mpr` folder in your theme and copy the `partials/front` folder into it.

