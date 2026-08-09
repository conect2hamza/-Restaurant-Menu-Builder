# Restaurant Menu Builder — WordPress Plugin Development Specification

## 1. Project Overview

Build a production-ready WordPress plugin named **Restaurant Menu Builder** from scratch.

The plugin will allow restaurant owners and administrators to create, manage, customize, preview, and publish responsive digital restaurant menus directly from the WordPress dashboard.

The uploaded reference video is the **functional and visual reference** for the product workflow. Analyze the reference carefully and reproduce the relevant user experience and functionality with an **independent, original implementation**.

> **Important:** Do not copy proprietary source code, CSS, JavaScript, database structures, class names, assets, branding, or other protected implementation details from any existing plugin or product.

---

# 2. Plugin Identity

| Property | Value |
|---|---|
| Plugin Name | Restaurant Menu Builder |
| Plugin Slug | `restaurant-menu-builder` |
| Text Domain | `restaurant-menu-builder` |
| Initial Version | `1.0.0` |
| Primary Prefix | `RMB_` |
| Suggested Namespace | `RestaurantMenuBuilder` |
| Shortcode | `[restaurant_menu id="123"]` |

The final plugin must be installable as a standard WordPress ZIP.

Expected package:

```text
restaurant-menu-builder.zip
```

---

# 3. Expert Developer Role

You are an **elite senior WordPress plugin architect, PHP developer, JavaScript developer, UI/UX engineer, database architect, security engineer, performance engineer, and QA specialist**.

Work as if you are building a commercial plugin that will be installed on thousands of real WordPress websites.

You have expert knowledge of:

- WordPress Core
- PHP 8+
- Object-Oriented PHP
- WordPress Coding Standards
- WordPress Hooks and Filters
- WordPress AJAX
- WordPress REST API
- `$wpdb`
- `dbDelta()`
- WordPress Media Library
- JavaScript
- Modern CSS
- Responsive UI/UX
- Accessibility
- Database architecture
- WordPress security
- Performance optimization
- Gutenberg
- Elementor
- Plugin compatibility
- Software testing

Do not behave like a beginner-level code generator.

Make sensible engineering decisions independently. Only ask questions when a missing requirement would materially change the product.

---

# 4. Core Product Goal

The plugin must allow an administrator to:

1. Create multiple menus.
2. Create menu categories.
3. Create menu items.
4. Upload/select food images.
5. Add descriptions.
6. Add prices.
7. Support multiple price variations.
8. Reorder categories.
9. Reorder menu items.
10. Customize menu appearance.
11. Preview the menu.
12. Publish menus using a shortcode.
13. Display the menu responsively.
14. Manage everything from a modern WordPress admin interface.

The first version should remain focused on restaurant menu creation and publishing.

---

# 5. Version 1.0 Scope

## 5.1 Must Have

### Menu Management

- Create menu
- Edit menu
- Delete menu
- Duplicate menu
- Multiple menus
- Menu status
- Menu title
- Menu slug
- Menu shortcode
- Menu settings

### Category Management

- Create category
- Edit category
- Delete category
- Duplicate category
- Category description
- Category icon
- Category image
- Category status
- Drag-and-drop ordering

### Menu Item Management

- Create item
- Edit item
- Delete item
- Duplicate item
- Item name
- Item description
- Item image
- Category assignment
- Price
- Sale price
- Multiple prices
- Item status
- Drag-and-drop ordering

### Frontend

- Responsive menu
- Category navigation
- Smooth scrolling
- Active category state
- Menu items
- Images
- Descriptions
- Prices
- Multiple layout options
- Mobile support
- Desktop support

### Customization

- Primary color
- Accent color
- Text color
- Secondary text color
- Background color
- Border color
- Typography sizes
- Layout selection
- Image display settings
- Category navigation settings

### Admin

- Modern dashboard UI
- Menu editor
- Category editor
- Item editor
- Style editor
- Settings
- Preview
- Success/error notices
- Loading states
- Empty states

---

# 6. Explicit Non-Requirements for Version 1.0

Do **not** implement the following unless explicitly requested:

- WooCommerce integration
- Shopping cart
- Online ordering
- Payment gateways
- Stripe
- PayPal
- Restaurant reservations
- POS
- Kitchen display system
- Delivery management
- Inventory management
- Customer accounts
- Customer reviews
- Loyalty system
- CRM
- Email marketing
- AI functionality
- Mandatory SaaS account
- Google OAuth
- Facebook login
- External licensing server
- Mandatory remote API
- Analytics tracking
- Telemetry
- Subscription system
- Complex membership system

Avoid feature bloat.

---

# 7. Future Features

The architecture should allow future implementation of:

## Version 1.1

- Additional layouts
- Menu duplication improvements
- Import/export
- More icon packs
- Advanced image settings

## Version 1.2

- QR menu
- Print menu
- PDF menu
- Restaurant schema

## Version 1.3

- WooCommerce integration
- Online ordering

## Version 2.0

- Multi-location restaurants
- Menu scheduling
- Analytics
- Seasonal menus
- Cloud synchronization

Do not build these features in Version 1.0.

---

# 8. Menu Structure

A menu contains categories.

A category contains menu items.

Example:

```text
Main Restaurant Menu
│
├── Pizza
│   ├── Margherita
│   ├── Pepperoni
│   └── Burrata
│
├── Burgers
│   ├── Classic Burger
│   ├── Cheese Burger
│   └── BBQ Burger
│
├── Snacks & Sides
│
├── Salads
│
└── Drinks
```

---

# 9. Menu Data

Each menu should support:

- ID
- Name
- Slug
- Status
- Settings
- Created date
- Updated date

Possible statuses:

```text
active
draft
```

---

# 10. Category Data

Each category should support:

- ID
- Menu ID
- Name
- Description
- Icon
- Image ID
- Sort order
- Status
- Created date
- Updated date

Categories must belong to a specific menu.

---

# 11. Menu Item Data

Each item should support:

- ID
- Menu ID
- Category ID
- Name
- Description
- Image ID
- Price
- Sale price
- Price type
- Sort order
- Status
- Additional settings
- Created date
- Updated date

---

# 12. Pricing

Support both single and multiple pricing.

### Single Price

```text
Margherita
$17
```

### Multiple Prices

```text
Margherita

Small    $17
Large    $25
```

The currency must not be hard-coded.

Supported examples:

- USD
- EUR
- GBP
- PKR
- AED
- CAD
- AUD

The architecture should allow additional currencies.

---

# 13. Image Management

Use the native WordPress Media Library.

Requirements:

- Select existing image
- Upload new image
- Change image
- Remove image
- Preview image
- Store attachment ID
- Use responsive WordPress image sizes
- Use lazy loading where appropriate

Supported formats should follow WordPress-supported image formats.

Do not create unnecessary duplicate media files.

---

# 14. Frontend Menu

The frontend should have a modern restaurant-menu appearance.

Example:

```text
------------------------------------------------

        Pizza   Burgers   Snacks   Salads

------------------------------------------------

                    PIZZA

          12" / 30cm • 16" / 40cm

 [IMAGE]   Margherita             $17 / $25

           Classic tomato sauce,
           fresh mozzarella,
           basil and olive oil.

 [IMAGE]   Pepperoni              $19 / $27

           Tomato sauce,
           mozzarella and pepperoni.

------------------------------------------------
```

---

# 15. Category Navigation

Display category navigation above the menu.

Example:

```text
Pizza
Burgers
Snacks & Sides
Salads
Drinks
```

Each category may have an icon.

Behavior:

- Click category
- Smooth scroll to category
- Highlight active category
- Update navigation state
- Work on mobile
- Work without page reload

Use accessible buttons/links rather than clickable generic `<div>` elements.

---

# 16. Frontend Layouts

Version 1.0 should support at least:

## Classic

```text
Image | Item Name | Description | Price
```

## Card

```text
[IMAGE]

Item Name
Description
$17
```

## Two Column

```text
Item 1                  Item 2
Item 3                  Item 4
```

The template system must make future layouts easy to add.

---

# 17. Responsive Requirements

The frontend must support:

- Desktop
- Laptop
- Tablet
- Mobile

Minimum target viewport:

```text
320px
```

Mobile requirements:

- Single-column layout where appropriate
- Touch-friendly navigation
- Horizontal category navigation
- Optimized images
- Readable text
- No horizontal overflow
- Proper spacing

---

# 18. Admin Architecture

Create a dedicated WordPress admin menu:

```text
Restaurant Menu
│
├── All Menus
├── Add New
├── Categories
├── Items
├── Style
├── Settings
└── Help
```

The admin interface should feel modern and professional while remaining compatible with WordPress admin conventions.

---

# 19. Menu Editor

Recommended editor layout:

```text
------------------------------------------------
| Sidebar              | Editor / Preview       |
|                      |                       |
| Menu                 | Menu Preview          |
| Categories           |                       |
| Items                | Categories            |
| Style                | Menu Items            |
| Settings             |                       |
------------------------------------------------
```

Provide:

- Clear navigation
- Add buttons
- Edit controls
- Delete controls
- Save controls
- Cancel controls
- Preview
- Loading states
- Empty states
- Error states

---

# 20. Live Preview

Provide a live or near-live preview.

When the administrator changes supported style settings, the preview should update without unnecessary page reloads.

Preview should reflect:

- Categories
- Items
- Images
- Prices
- Colors
- Typography
- Layout
- Navigation

Do not call something "live preview" if it is only a static screenshot.

---

# 21. Style Controls

Create a Style section.

## Colors

- Primary
- Accent
- Text
- Secondary text
- Background
- Border
- Active category

## Typography

- Heading size
- Body size
- Category size
- Price size
- Font family selection where appropriate

## Layout

- Classic
- Card
- Two Column

## Display

- Show images
- Show descriptions
- Show prices
- Show category navigation

All settings must actually affect the frontend.

---

# 22. Custom CSS

Allow optional custom CSS.

Scope plugin styles with a unique prefix.

Example:

```css
.rmb-menu {}
.rmb-category {}
.rmb-item {}
.rmb-navigation {}
```

Do not globally style generic elements such as:

```css
body {}
h1 {}
p {}
img {}
button {}
```

unless strictly scoped under the plugin wrapper.

---

# 23. Icon System

Categories should support icons.

Prefer:

- Inline SVG
- Plugin-controlled SVG icon set

Avoid unnecessary third-party icon dependencies.

Icons should be stored as safe identifiers rather than arbitrary unsafe HTML.

---

# 24. Drag and Drop

Implement drag-and-drop ordering for:

### Categories

```text
Pizza
Burgers
Snacks
Salads
Drinks
```

### Items

```text
Margherita
Pepperoni
Burrata
Panna
```

After reordering:

1. Save the new order.
2. Persist it in the database.
3. Return a success/error response.
4. Update the UI appropriately.

---

# 25. Shortcode

Primary shortcode:

```text
[restaurant_menu id="123"]
```

Optional:

```text
[restaurant_menu id="123" layout="classic"]
```

The shortcode must:

- Validate menu ID
- Load menu data
- Render templates
- Escape output
- Avoid unnecessary queries
- Work in Gutenberg
- Work in Elementor
- Work in standard WordPress content

---

# 26. Database Architecture

Do not store the complete application inside one huge serialized option.

Use a scalable database architecture.

Recommended tables:

```text
{$wpdb->prefix}rmb_menus
{$wpdb->prefix}rmb_categories
{$wpdb->prefix}rmb_items
```

## Menus

```text
id
name
slug
status
settings
created_at
updated_at
```

## Categories

```text
id
menu_id
name
description
icon
image_id
sort_order
status
created_at
updated_at
```

## Items

```text
id
menu_id
category_id
name
description
image_id
price
sale_price
price_type
sort_order
status
settings
created_at
updated_at
```

Use:

```php
$wpdb->prefix
```

Never hard-code `wp_`.

Use appropriate indexes for frequently queried fields such as:

- menu_id
- category_id
- sort_order
- status

---

# 27. Database Installation

Use WordPress-compatible database installation methods.

Preferred:

```php
dbDelta()
```

Store the plugin database version.

Example:

```php
RMB_DB_VERSION
```

Future database changes must use migrations.

Never require users to manually edit database tables.

---

# 28. Plugin File Structure

Use clean OOP architecture.

Recommended structure:

```text
restaurant-menu-builder/
│
├── restaurant-menu-builder.php
├── uninstall.php
├── readme.txt
│
├── includes/
│   ├── class-plugin.php
│   ├── class-installer.php
│   ├── class-database.php
│   ├── class-menu.php
│   ├── class-category.php
│   ├── class-item.php
│   ├── class-shortcode.php
│   ├── class-ajax.php
│   ├── class-rest-api.php
│   ├── class-assets.php
│   ├── class-security.php
│   └── helpers.php
│
├── admin/
│   ├── class-admin.php
│   ├── views/
│   │   ├── menus.php
│   │   ├── menu-editor.php
│   │   ├── categories.php
│   │   ├── items.php
│   │   ├── style.php
│   │   └── settings.php
│   │
│   ├── css/
│   │   └── admin.css
│   │
│   └── js/
│       └── admin.js
│
├── frontend/
│   ├── class-frontend.php
│   ├── templates/
│   │   ├── menu.php
│   │   ├── category.php
│   │   └── item.php
│   │
│   ├── css/
│   │   └── frontend.css
│   │
│   └── js/
│       └── frontend.js
│
├── assets/
│   └── icons/
│
└── languages/
```

The exact structure may be improved if a better architecture is identified, but responsibilities must remain clearly separated.

---

# 29. OOP and Namespace Rules

Use namespacing or strong prefixes.

Preferred:

```php
namespace RestaurantMenuBuilder;
```

Avoid generic global classes such as:

```php
class Menu {}
class Admin {}
class Settings {}
class Database {}
```

without proper namespacing.

Do not pollute the global namespace.

---

# 30. WordPress Coding Standards

Follow WordPress Coding Standards.

Use:

- Actions
- Filters
- WordPress APIs
- Proper escaping
- Proper sanitization
- Proper capability checks
- Proper nonce handling
- Translation functions

Use translation functions such as:

```php
__();
_e();
esc_html__();
esc_attr__();
```

with:

```text
restaurant-menu-builder
```

as the text domain.

---

# 31. Security

Every admin operation must verify authorization.

Use appropriate capability checks such as:

```php
current_user_can()
```

Use nonces for state-changing requests:

```php
wp_nonce_field()
check_admin_referer()
check_ajax_referer()
```

Validate and sanitize input.

Examples:

```php
sanitize_text_field()
sanitize_textarea_field()
sanitize_key()
absint()
esc_url_raw()
```

Escape output:

```php
esc_html()
esc_attr()
esc_url()
wp_kses_post()
```

Protect against:

- SQL injection
- XSS
- CSRF
- Unauthorized access
- Privilege escalation
- Unsafe uploads
- Direct file access
- Invalid IDs
- Malicious request data

---

# 32. AJAX / REST

Use AJAX or REST where it improves UX.

Potential operations:

```text
Create menu
Update menu
Delete menu
Create category
Update category
Delete category
Create item
Update item
Delete item
Reorder categories
Reorder items
Save style settings
```

Every endpoint must have:

- Authentication
- Capability checks
- Nonce verification where appropriate
- Input validation
- Sanitization
- Structured success/error responses

Never trust client-side authorization.

---

# 33. Performance

Performance is a core requirement.

The plugin must:

- Load admin assets only in the plugin admin screens.
- Load frontend assets only when the shortcode/menu is present.
- Avoid N+1 queries.
- Avoid unnecessary AJAX calls.
- Avoid unnecessary external libraries.
- Optimize database queries.
- Use WordPress image sizes.
- Use lazy loading where appropriate.
- Avoid massive serialized data.
- Keep frontend JavaScript lightweight.
- Keep CSS scoped and optimized.

Target support:

```text
100+ categories
1,000+ menu items
```

without obvious architectural problems.

---

# 34. Accessibility

Follow modern accessibility practices.

Requirements:

- Semantic HTML
- Keyboard navigation
- Visible focus states
- Accessible buttons
- Meaningful image alt text
- Sufficient contrast
- Screen-reader-friendly controls
- Accessible category navigation

Do not rely on color alone to communicate state.

---

# 35. SEO

Frontend HTML should be clean and crawlable.

Use appropriate heading hierarchy.

Menu item names and descriptions should be available as normal HTML.

Do not create unnecessary duplicate content.

Schema.org structured data is optional for a future release and must not be implemented incorrectly.

---

# 36. Global Settings

Create:

```text
Restaurant Menu → Settings
```

## General

- Currency
- Currency position
- Default menu
- Image size
- Lazy loading

## Display

- Default layout
- Category navigation
- Show images
- Show descriptions
- Show prices

## Performance

- Caching option
- Cache duration

## Advanced

- Custom CSS
- Reset settings
- Delete data on uninstall

---

# 37. Uninstall Behavior

Normal plugin deactivation must NOT delete user data.

Uninstall should only delete all plugin data if the administrator explicitly enabled:

```text
Delete all plugin data on uninstall
```

Default:

```text
OFF
```

Never delete data unexpectedly.

---

# 38. Activation

On activation:

1. Verify required environment.
2. Create database tables.
3. Run required migrations.
4. Store plugin version.
5. Store database version.
6. Set safe defaults.
7. Do not perform expensive operations.
8. Do not unnecessarily flush rewrite rules.

Activation must not crash WordPress.

---

# 39. Deactivation

On deactivation:

- Do not delete database tables.
- Do not delete settings.
- Do not delete menu data.
- Clean temporary resources only when required.

---

# 40. Error Handling

Never silently fail.

Examples:

```text
Menu saved successfully.
```

```text
Unable to save the menu. Please try again.
```

```text
Menu item deleted successfully.
```

For AJAX/REST, return structured errors.

Example concept:

```json
{
  "success": false,
  "message": "Unable to save the menu."
}
```

Do not expose sensitive server/database information to users.

---

# 41. Admin UX Requirements

The admin UI should be understandable by non-technical restaurant owners.

Use:

- Clear labels
- Helpful descriptions
- Consistent buttons
- Confirmation dialogs
- Empty states
- Loading states
- Error states
- Success states
- Undo where practical

Avoid unnecessary technical terminology.

---

# 42. Mobile Admin

The admin interface should remain usable on smaller screens.

Requirements:

- Responsive layout
- No unnecessary horizontal scrolling
- Touch-friendly controls
- Responsive forms
- Mobile-friendly navigation
- Reasonable drag-and-drop behavior

---

# 43. Compatibility

The plugin should be designed for modern supported WordPress environments.

Test compatibility with:

- Current WordPress
- Modern PHP
- Gutenberg
- Classic Editor
- Elementor
- Common themes
- Common caching plugins

Avoid deprecated WordPress APIs.

Avoid unnecessary conflicts with themes/plugins.

---

# 44. Reference Video Analysis

Before implementation, analyze the uploaded reference video and identify:

- Main screens
- Navigation structure
- Menu editor workflow
- Category workflow
- Item workflow
- Image behavior
- Pricing behavior
- Preview behavior
- Style controls
- Responsive behavior
- Visual hierarchy

The reference is a product/design reference only.

Create an original implementation.

Do not reproduce proprietary implementation details.

---

# 45. No Fake Functionality

This rule is mandatory.

Never create:

- Dummy buttons
- Fake CRUD
- Fake AJAX
- Hard-coded menu data
- Placeholder database operations
- Fake preview
- Non-functional settings
- UI controls that do nothing

Every visible feature must connect to real functionality.

---

# 46. No Hard-Coded Business Data

Do not hard-code:

- Menu IDs
- Category IDs
- Item IDs
- Restaurant names
- Menu items
- Prices
- Database prefixes
- Site URLs
- User IDs

All business data must be dynamic.

---

# 47. Code Quality

Code must be:

- Modular
- Readable
- Maintainable
- Secure
- Testable
- Extensible
- Properly structured

Avoid:

- Giant PHP files
- Duplicated code
- Unnecessary dependencies
- Obfuscated code
- Hidden external requests
- Unnecessary tracking
- Global variables
- Hard-coded URLs

---

# 48. Development Workflow

Follow this implementation order.

## Phase 1 — Architecture

- Analyze requirements
- Define classes
- Define namespace
- Define file structure
- Define database schema
- Define data flow

## Phase 2 — Bootstrap

- Main plugin file
- Constants
- Autoloading
- Plugin initialization

## Phase 3 — Database

- Installer
- Tables
- Indexes
- Versioning
- Migrations

## Phase 4 — Menu CRUD

- Create
- Read
- Update
- Delete
- Duplicate

## Phase 5 — Category CRUD

- Create
- Read
- Update
- Delete
- Ordering

## Phase 6 — Item CRUD

- Create
- Read
- Update
- Delete
- Images
- Pricing
- Ordering

## Phase 7 — Frontend

- Shortcode
- Templates
- Categories
- Items
- Images
- Pricing
- Navigation

## Phase 8 — Styling

- Layouts
- Colors
- Typography
- Display settings

## Phase 9 — Preview

- Live/near-live preview
- Responsive preview where practical

## Phase 10 — Security

- Capability checks
- Nonces
- Validation
- Sanitization
- Escaping

## Phase 11 — Performance

- Query optimization
- Asset optimization
- Image optimization
- Caching where appropriate

## Phase 12 — QA

- Full functional testing
- Security testing
- Responsive testing
- Compatibility testing

---

# 49. Self-Review Requirement

After implementing each major module, review the code like a senior engineer.

Check:

### Architecture

- Is responsibility separated?
- Is the code reusable?
- Is there duplicated logic?

### Security

- Are capabilities checked?
- Are nonces checked?
- Is input sanitized?
- Is output escaped?

### Performance

- Are database queries efficient?
- Are assets conditionally loaded?
- Is unnecessary JavaScript avoided?

### WordPress

- Are WordPress APIs used correctly?
- Are hooks registered correctly?
- Are naming conventions correct?

### UX

- Are loading states handled?
- Are errors clear?
- Is the interface responsive?

Fix issues before moving to the next phase.

---

# 50. Testing Checklist

## Plugin

- [ ] Install
- [ ] Activate
- [ ] Deactivate
- [ ] Uninstall
- [ ] Upgrade

## Menus

- [ ] Create
- [ ] Edit
- [ ] Delete
- [ ] Duplicate
- [ ] Multiple menus

## Categories

- [ ] Create
- [ ] Edit
- [ ] Delete
- [ ] Duplicate
- [ ] Icon
- [ ] Image
- [ ] Ordering

## Items

- [ ] Create
- [ ] Edit
- [ ] Delete
- [ ] Duplicate
- [ ] Image
- [ ] Description
- [ ] Price
- [ ] Multiple prices
- [ ] Ordering

## Frontend

- [ ] Shortcode
- [ ] Category navigation
- [ ] Smooth scrolling
- [ ] Active category
- [ ] Images
- [ ] Prices
- [ ] Layouts
- [ ] Desktop
- [ ] Tablet
- [ ] Mobile

## Styling

- [ ] Colors
- [ ] Typography
- [ ] Layout
- [ ] Display options
- [ ] Preview

## Security

- [ ] Authentication
- [ ] Authorization
- [ ] Nonces
- [ ] Sanitization
- [ ] Validation
- [ ] Escaping
- [ ] SQL injection protection
- [ ] XSS protection
- [ ] CSRF protection

## Performance

- [ ] Conditional assets
- [ ] Optimized queries
- [ ] No N+1 queries
- [ ] Optimized images
- [ ] Lightweight frontend

---

# 51. Final Deliverable

The final deliverable must be a complete WordPress plugin.

Expected:

```text
restaurant-menu-builder.zip
```

The ZIP must contain:

```text
restaurant-menu-builder/
├── restaurant-menu-builder.php
├── uninstall.php
├── readme.txt
├── includes/
├── admin/
├── frontend/
├── assets/
└── languages/
```

The plugin must install through:

```text
WordPress Admin
→ Plugins
→ Add New
→ Upload Plugin
```

No manual file copying should be required.

---

# 52. Readme

Create a professional `readme.txt` containing:

- Plugin name
- Description
- Features
- Requirements
- Installation
- Configuration
- Usage
- Shortcode documentation
- FAQ
- Changelog
- Support information placeholder

---

# 53. Final Engineering Rules

The following rules override convenience:

1. **Security over speed.**
2. **Real functionality over visual demos.**
3. **Maintainability over unnecessary complexity.**
4. **Performance over excessive dependencies.**
5. **WordPress standards over custom hacks.**
6. **Scalability over fragile shortcuts.**
7. **Accessibility over decorative interactions.**
8. **Original implementation over copied code.**
9. **Clean architecture over one-file implementations.**
10. **Production quality over prototype quality.**

---

# 54. Final Instruction to the AI Coding Agent

Build this plugin as a **real, production-ready WordPress product**.

Do not generate a superficial prototype.

Do not generate fake functionality.

Do not leave core features as placeholders.

Do not copy another plugin's source code or proprietary implementation.

Use the uploaded reference video only to understand the intended functionality, workflow, and general visual direction.

Make all engineering decisions required to turn this specification into a stable, secure, scalable WordPress plugin.

Before declaring the project complete, perform a full code review and QA pass and fix any issues you identify.

The final result must be a **clean, installable, functional, responsive, secure, performant, and maintainable WordPress plugin ZIP**.
