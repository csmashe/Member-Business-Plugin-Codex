# Post 116 Business Directory

WordPress plugin providing a member business directory with categories, owners, search, and custom templates.

## Features
- Custom post type `p116_business`
- Taxonomy `p116_business_category`
- Admin meta UI: owners (repeater), contact, address, flags, links, services
- Directory Gutenberg block `p116/directory` (server-side render)
- REST API: `/wp-json/p116/v1/search`, `/wp-json/p116/v1/autocomplete`
- Single and taxonomy templates, JSON-LD LocalBusiness
- Settings page: directory page, flags visibility, optional map toggle (Phase 2)
- Performance: meta indexes for `owners_search` and `city_search`

## Installation
1. Copy the `post116-business-directory` folder into `wp-content/plugins/`.
2. Activate the plugin in WordPress Admin.
3. On activation, a “Business Directory” page is created at `/directory` with the block.

## QA Checklist
- Add/edit/delete a Business (city is required)
- Assign multiple categories and owners
- Toggle ownership flags; verify badges display
- Directory page renders; search and pagination work
- Autocomplete returns businesses, owners, and categories
- Single business JSON-LD is present in page source
- Taxonomy archive groups display cards; disclaimer visible

## Developer Notes
- Meta keys used: `owners`, `links`, `business_phone`, `business_email`, `website_url`, `city`, `address1`, `address2`, `state`, `postal_code`, `veteran_owned`, `sons_owned`, `auxiliary_owned`, `services_offered`, `show_in_directory`, `owners_search`, `city_search`.
- Activation adds DB indexes on `wp_postmeta (meta_key, meta_value)` for `owners_search` and `city_search`.

## License
Proprietary to Post 116 (do not redistribute).

