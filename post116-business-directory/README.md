# Post 116 Business Directory (WordPress)

A lightweight business directory plugin built for American Legion Post 116. It registers a custom post type for businesses, a hierarchical category taxonomy, a front‑end Directory block with search and infinite scroll, and a single business template with contact options.

This was created for use at our American Legion Post, but you can freely use it as a starting point for your own site. Just fork the repo and make any updates you need.

## Features

- Custom Post Type: `p116_business` with media/logo, contact fields, owners, links, services, and visibility toggle.
- Taxonomy: `p116_business_category` (e.g., “Handyman Services”, “Golf”).
- Gutenberg Block: `p116/directory` renders the directory on a standard page with:
  - Search box (business title, category name, owner names, services offered)
  - Category filter + ownership flags (Veteran, SAL, Auxiliary)
  - Infinite scroll with server‑side ordering by Category → Business (A–Z)
  - Accessible keyboard/focus states
- Single business template with contact buttons and optional reCAPTCHA.
- Admin settings for the directory hero banner and reCAPTCHA keys.
- REST API powering search and autocomplete.

## Requirements

- WordPress 6.0+
- PHP 7.4+ (8.x recommended)

## Install

1. Copy the `post116-business-directory/` folder into `wp-content/plugins/`.
2. Activate “Post 116 Business Directory” in WP Admin → Plugins.
3. Create a normal page at `/directory` and insert the “Business Directory” block (`p116/directory`).
4. Optional: configure WP Admin → Settings → Business Directory for hero image and captions. Add reCAPTCHA keys if you want the contact form protected.

If permalinks misroute after activation, re‑save Settings → Permalinks.

## Data Model

- Businesses are posts of type `p116_business`.
- Categories are terms of `p116_business_category`.
- Owners, links, and flags are stored as post meta. Phone and email are duplicated per owner and business for clean CTAs.

## REST API

- `GET /wp-json/p116/v1/search`
  - Params: `q`, `category`, `flags[]`, `per_page`, `page`
  - Returns rows sorted by category label then business title; each item includes `cat_label`, `cat_slug`, `title`, `permalink`, `logo`, `owners`, `services`, `flags`.
- `GET /wp-json/p116/v1/autocomplete?q=abc`
  - Returns a small set of suggestions across businesses, owners, services, and categories.
- `POST /wp-json/p116/v1/contact`
  - Sends a message to the business/owner. Optional reCAPTCHA if configured.

## Templates & Assets

- Front‑end styles: `public/css/public.css`
- Front‑end scripts: `public/js/directory.js`, `public/js/single.js`
- Single template: `templates/single-p116_business.php`
- Category template: `templates/taxonomy-p116_business_category.php`

You can override templates in your theme by copying them under a `post116-business-directory/` folder.

## Development

- PHP Lint: `find post116-business-directory -name '*.php' -print0 | xargs -0 -n1 php -l`
- Tests: PHPUnit bootstrap included (requires local WP test suite)
- Build zip: `zip -r post116-business-directory.zip post116-business-directory -x '*.git*' '*/vendor/*' '*/node_modules/*'`

## Notes

- Created for American Legion Post 116’s member‑owned business directory.
- Feel free to fork and adapt for your own use cases.
- No warranty; please vet and secure before production.

