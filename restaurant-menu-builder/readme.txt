=== Restaurant Menu Builder ===
Contributors: hamzadezinr
Tags: restaurant, menu, food menu, digital menu, cafe
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build, style and publish responsive restaurant menus from the WordPress dashboard. No account, no external service, no tracking.

== Description ==

Restaurant Menu Builder turns the WordPress dashboard into a menu editor. Create a menu, group dishes into categories, add photos, descriptions and prices, then publish it anywhere with a shortcode.

Everything runs on your own site. The plugin makes no external requests, requires no account, and collects no analytics.

**What you can do**

* Build as many menus as you need, each with its own categories and items
* Drag categories and dishes into the order you want, on desktop or touch
* Add a photo from the media library, a description, and a price to every dish
* Price a dish once, or by size — Small, Large, and up to eight variations
* Show a sale price alongside the original
* Pick from three layouts: classic, card and two column
* Choose colours, type sizes and a font, globally or per menu
* Watch a live preview update as you edit, at desktop, tablet and mobile widths
* Publish with `[restaurant_menu id="1"]` in the block editor, Elementor, or any page builder that runs shortcodes

**Built for real sites**

* Menus, categories and items live in their own database tables, not in one giant option
* A menu of a hundred categories and a thousand items loads in a small, fixed number of queries
* Frontend CSS and JavaScript only load on pages that actually contain a menu
* Rendered menus are cached, and the cache clears itself the moment you change anything
* Every admin action checks a capability and a nonce, sanitizes what comes in and escapes what goes out
* Category navigation is built from real links and buttons, works with a keyboard, and never signals state with colour alone

== Installation ==

1. In WordPress, go to Plugins → Add New → Upload Plugin.
2. Choose `restaurant-menu-builder.zip` and click Install Now.
3. Activate the plugin.
4. Open Restaurant Menu in the admin sidebar and create your first menu.

No files need to be copied by hand and no database tables need to be created manually.

== Configuration ==

Go to Restaurant Menu → Settings to set:

* Currency and whether the symbol sits before or after the amount
* A default menu, used when the shortcode is added without an ID
* The image size served from your media library, and lazy loading
* Display defaults for new menus: layout, navigation, images, descriptions, prices
* Caching and cache duration
* Custom CSS
* Whether uninstalling should delete your data — off by default

Colours and type live in Restaurant Menu → Style. Any single menu can override them from its own Style tab.

== Usage ==

Create a menu, add categories such as Starters and Mains, then add dishes to each category. Set the menu to Active and paste its shortcode into a page.

A draft menu shows nothing to visitors, so you can build it in the open.

== Shortcode ==

`[restaurant_menu id="1"]`

Options:

* `id` — the menu to show. Falls back to the default menu in Settings.
* `slug` — use the menu slug instead of its ID.
* `layout` — `classic`, `card` or `two-column`. Overrides the menu setting for this placement only.
* `navigation` — `yes` or `no`. Show the category bar.
* `images` — `yes` or `no`.
* `descriptions` — `yes` or `no`.
* `prices` — `yes` or `no`.

Example: `[restaurant_menu id="1" layout="two-column" images="no"]`

== Frequently Asked Questions ==

= Does deactivating the plugin delete my menus? =

No. Deactivation never touches your data. Uninstalling only deletes data if you first switch on "Delete all menus, categories, items and settings when the plugin is uninstalled" in Settings → Advanced.

= Does it work with Elementor and the block editor? =

Yes. Add the shortcode with a Shortcode widget or block. The plugin detects menus stored in Elementor's page data so the styles load correctly.

= Can I use a currency that isn't in the list? =

Yes, through the `rmb_currencies` filter. Add an entry with a label, symbol and number of decimals.

= Can I change the markup? =

Yes. Copy any template from `frontend/templates/` into `restaurant-menu-builder/` in your theme, for example `wp-content/themes/your-theme/restaurant-menu-builder/item.php`.

= Who can manage menus? =

Administrators by default. Use the `rmb_manage_capability` filter to change the required capability.

= Does the plugin call any external service? =

No. There are no remote APIs, no licensing server, no fonts loaded from a CDN and no telemetry.

== Screenshots ==

1. All menus, with status, contents and shortcode for each.
2. The menu editor, with categories and items on the left and a live preview on the right.
3. The style editor, with colour and type controls.
4. A published menu in the classic layout.

== Changelog ==

= 1.1.0 =
* New icon pack: 85 hand drawn line-art food and restaurant icons, grouped into
  mains, seafood, fruit and vegetables, desserts, drinks, pantry and tools,
  service and labels.
* Category icons are now chosen from a searchable visual grid instead of a
  dropdown, and every icon in the pack is listed on the Help screen.
* Icons support multiple paths and circles, so they can carry real detail while
  still being stored as safe identifiers. Path data is sanitized on render.
* New Dashboard screen with live counts for menus, categories, items and the
  number of posts embedding a menu shortcode, plus quick actions and the
  shortcode for your default menu.
* Refreshed admin interface: card based panels, a new colour system, switch
  controls, icon row actions, a redesigned modal and clearer empty states.
* Frontend category navigation now shows the category icon above its label with
  an underlined active state.
* Third party icons registered through the rmb_icons filter keep working with
  the 1.0 single path format.

= 1.0.0 =
* Initial release.
* Menus, categories and items with full create, edit, duplicate and delete.
* Drag and drop ordering with a keyboard equivalent.
* Media library images, single and multiple pricing, sale prices.
* Classic, card and two column layouts.
* Global and per-menu style controls with a live preview.
* Shortcode with layout and display overrides.
* Rendered menu caching with automatic invalidation.

== Upgrade Notice ==

= 1.1.0 =
Adds the full icon pack, the dashboard and the refreshed admin interface.
Existing category icons keep working; no database change is required.

= 1.0.0 =
First release.

== Support ==

Report a problem or request a feature at https://hamzadezinr.com/support — replace this address with your own before distributing the plugin.
