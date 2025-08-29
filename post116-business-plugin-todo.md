# Post 116 Legion Family Owned Businesses and Services Directory Plugin - Todo Plan

## Scope
Create a WordPress plugin that lets admins add and manage member businesses, assign multiple categories, attach multiple owners with separate contact info, and present a front-end directory page with category grouping, AJAX search with autocomplete, individual business pages, and an optional map view later. Keep the site’s disclaimer and follow the site’s visual style from alpost116nc.org for colors, spacing, and typography. Keep data entry and publishing in the admin only.

## Legal note to display in the directory
Use this text exactly, site-wide on directory pages:

American Legion Post 116 is not liable for or endorsing any listed businesses. Please independently verify their work quality, licenses, and insurance.

## Deliverables
1. Installable plugin zip  
2. Custom post type and taxonomy  
3. Admin UI for businesses, owners, categories  
4. Front-end directory page with AJAX search and autocomplete  
5. Single business page template  
6. JSON-LD LocalBusiness schema on single pages  
7. Settings page  
8. Optional map view in Phase 2  
9. Readme and QA checklist  
10. Minimal unit and integration tests

## Data model
**Custom Post Type**
- Key: p116_business
- Public: true
- Supports: title, editor, thumbnail, excerpt, revisions
- Rewrite
  - Directory hub page path: /directory
  - Category: /directory/category/{slug}
  - Single: /directory/{business-slug}

**Taxonomy**
- Key: p116_business_category
- Hierarchical: true
- Multiple categories per business allowed

**Meta fields for p116_business**
- Owners (repeatable group)
  - owner_name
  - owner_role (optional)
  - owner_email (optional)
  - owner_phone (optional)
  - owner_website (optional)
- Contact
  - business_phone (primary)
  - business_email (primary)
  - website_url (primary)
- Address
  - city (required)
  - address1, address2, state, postal_code (optional)
- Ownership flags
  - veteran_owned (bool)
  - sons_owned (bool)
  - auxiliary_owned (bool)
- Links (repeatable, sortable)
  - link_label
  - link_url
- Services offered (short list, used on list view and single)
- Description long (use the post content)
- Show in directory (bool, default true)

**Search helpers**
- owners_search (lowercased concatenation of all owner names, for LIKE matching)
- city_search (lowercased city for filtering if needed later)

## Capabilities and roles
**Capabilities**
- business read, create, edit, delete
- manage business categories

**Default role access**
- Grant to all built-in roles per your instruction.

## Admin UI
- Owners repeater with add, remove, reorder
- Contact fields with validation and sanitization
- Address fields with city required
- Ownership flags checkboxes
- Services offered (textarea, short)
- Links repeater with drag to sort
- Show in directory toggle

**Admin list table columns**
- Categories
- Owners (first two shown, with “+N more”)
- City
- Phone
- Ownership flags badges
- Quick filter by category and flags

**Validation**
- Sanitize all text and URLs
- Normalize phone numbers for display
- Lowercase owners_search and city_search on save

## Front-end
**Directory hub page**
- Normal WordPress page at /directory
- Plugin registers a Gutenberg block `p116/directory`
- Search bar with text input and category dropdown
- Ownership flag filters
- Results grouped by category, sorted A–Z

**Search and autocomplete**
- AJAX via REST API
- Autocomplete suggestions for businesses, owners, and categories

**Single business page**
- Logo, business name, flags, owners, contact, city/address, services offered, long description, links
- JSON-LD schema

**Design**
- Follow alpost116nc.org styles
- Neutral CSS that inherits fonts/colors
- Card grid with badges and spacing

**Optional map view (Phase 2)**
- Toggle in settings
- Use city/address to plot pins

## REST API
**Routes**
- GET /p116/v1/search
- GET /p116/v1/autocomplete

## Gutenberg block
- Name: p116/directory
- Server-side render
- Attributes: showFlags, perPage, placeholderText

## Template loader
- single-p116_business.php
- taxonomy-p116_business_category.php
- parts/card-business.php

## Schema
- LocalBusiness JSON-LD with name, url, image, telephone, email, address, sameAs

## Settings page
- Directory page selector
- Optional filter toggles
- Map view toggle
- CSS variable editor

## Performance
- Meta indexes for owners_search and city_search
- Cache category-grouped results
- Debounce autocomplete client-side

## Security
- Sanitize and escape
- Nonces
- URL allowlist
- Capability checks

## Tests and QA
**Unit tests**
- CPT and taxonomy registration
- Meta validation

**Integration tests**
- Sample businesses with multiple owners/categories
- Search results order

**Manual QA**
- Add/edit/delete business
- Verify city required
- Search by all supported fields
- Autocomplete
- Schema present
- Directory renders correctly

## File layout
post116-business-directory/  
- post116-business-directory.php  
- includes/  
- public/  
- templates/  
- blocks/  
- languages/  
- readme.md  

## Activation
- Register CPT and taxonomy
- Create “Business Directory” page with block
- Add meta indexes
- Capabilities to all roles
- Flush rewrites
