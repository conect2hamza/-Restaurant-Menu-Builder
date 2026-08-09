# Restaurant Menu Builder

A WordPress plugin for building, styling and publishing responsive restaurant
menus from the dashboard. Menus hold categories, categories hold items, and any
menu is published with a shortcode:

```text
[restaurant_menu id="1"]
```

The plugin source lives in [`restaurant-menu-builder/`](restaurant-menu-builder/)
and installs as a standard WordPress ZIP.

- **Version:** 1.1.0
- **Requires:** WordPress 6.0+, PHP 8.0+
- **Text domain:** `restaurant-menu-builder`
- **Prefix / namespace:** `RMB_` / `RestaurantMenuBuilder`

The full product specification this plugin is built against is kept in
[`docs/restaurant-menu-builder-specification.md`](docs/restaurant-menu-builder-specification.md).

---

## What is in the box

| Area | Summary |
|---|---|
| Menus | Create, edit, duplicate, delete, multiple menus, active/draft status, per-menu settings |
| Categories | Create, edit, duplicate, delete, description, icon, image, drag-and-drop ordering |
| Items | Create, edit, duplicate, delete, image, description, single or multiple prices, sale price, badge, ordering |
| Frontend | Classic, card and two-column layouts, category navigation with smooth scrolling, responsive down to 320px |
| Styling | Global style plus per-menu overrides — colours, typography sizes, font, layout, display toggles, custom CSS |
| Publishing | Shortcode with `id`, `slug`, `layout`, `navigation`, `images`, `descriptions` and `prices` attributes |
| Data | Three custom tables created with `dbDelta()`, indexed on `menu_id`, `category_id`, `sort_order` and `status` |

---

## The icon pack

Categories carry an icon that appears in the frontend navigation. Version 1.1
replaces the original twenty icons with a pack of **85 hand-drawn line-art food
and restaurant icons**, organised into eight groups:

| Group | Examples |
|---|---|
| Mains & dishes | pizza, burger, hot dog, taco, steak, chicken leg, pasta, noodles, soup, salad, fried egg, croissant |
| Seafood | fish, salmon fillet, shrimp, sushi, canned fish |
| Fruit & vegetables | carrot, pepper, mushroom, strawberry, banana, citrus |
| Desserts | cupcake, cake, ice cream cone, sundae, donut, cookie, sweets, chocolate |
| Drinks | coffee, coffee to go, espresso, tea, teapot, beer, wine, cocktail, milk, smoothie |
| Pantry & tools | oil, salt, pepper mill, honey, preserves, cutting board, whisk, colander, scale, grinder |
| Service | chef hat, served dish, cutlery, menu card, dining table, takeaway, service bell |
| Labels | chef special, spicy, vegan, kids |

Design notes:

- Every icon is drawn on a 24×24 grid as stroked paths with no fill, so it
  inherits the surrounding text colour and stays sharp at any size.
- Icons are rendered as **inline SVG** — no icon font, no external request.
- A category stores only the icon **key**, never markup. Path data is filtered
  down to valid SVG path syntax on render, so nothing registered through the
  `rmb_icons` filter can inject markup.
- Every key from version 1.0 still exists, so categories created before the
  update keep their icon.

Adding your own icon:

```php
add_filter( 'rmb_icons', function ( array $icons ): array {
    $icons['pretzel'] = array(
        'label'   => 'Pretzel',
        'group'   => 'mains',
        'paths'   => array( 'M6 8a3 3 0 0 1 5 2l1 6 1-6a3 3 0 0 1 5-2' ),
        'circles' => array( '12 18 1' ), // "cx cy r"
    );

    return $icons;
} );
```

The 1.0 single-`path` format is still accepted, so existing filters keep working.

Every icon in the pack is listed with its label under **Restaurant Menu → Help**.

---

## Admin interface

- **Dashboard** — live counts for menus, categories, items and the number of
  posts and pages that embed a menu shortcode, alongside recently updated menus,
  quick actions and the shortcode for your default menu. Every number is queried
  at render time rather than stored.
- **All Menus** — searchable, status-filterable table with inline status
  switching, one-click shortcode copy and icon row actions.
- **Menu editor** — categories and items, style and menu settings in one sidebar
  with a live preview beside it at desktop, tablet and mobile widths.
- **Categories / Items** — standalone screens for working on one menu at a time,
  with drag-and-drop ordering and keyboard arrow buttons as an equivalent.
- **Style** — global colours, typography and font with the same live preview.
- **Settings** — currency and position, default menu, image size, lazy loading,
  display defaults, caching, custom CSS and uninstall behaviour.

The category icon is chosen from a searchable grid of the whole pack rather than
a dropdown, with the groups above as headings.

---

## Development

```text
restaurant-menu-builder/
├── restaurant-menu-builder.php   Bootstrap, constants, environment checks
├── uninstall.php                 Only deletes data when the setting is enabled
├── includes/                     Models, database, settings, REST API, icons, cache
├── admin/                        Admin controller, views, CSS, JS
├── frontend/                     Renderer, templates, CSS, JS
└── languages/                    restaurant-menu-builder.pot
```

Regenerate the translation template after changing any user-facing string:

```bash
python3 bin/makepot.py restaurant-menu-builder
```

Build an installable ZIP:

```bash
cd restaurant-menu-builder/.. && zip -r restaurant-menu-builder.zip restaurant-menu-builder -x '*.DS_Store'
```

### Extension points

| Hook | Purpose |
|---|---|
| `rmb_icons` | Register a category icon |
| `rmb_layouts` | Register a frontend layout |
| `rmb_currencies` | Add a currency |
| `rmb_render_context` | Change display options before a menu renders |
| `rmb_manage_capability` | Change who can manage menus |
| `rmb_needs_frontend_assets` | Force menu CSS and JS onto a template you render yourself |

Templates can be overridden from a theme at
`restaurant-menu-builder/menu.php`, `category.php`, `item.php` or
`layout-{name}.php`.

---

## License

GPL-2.0-or-later.
