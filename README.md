# OnTap - Brewery Taplist Plugin

A commercial WordPress plugin for breweries to integrate with Untappd and display live taproom menus on their websites.

## Description

OnTap automatically syncs your taplist with Untappd and displays what's currently on tap at your brewery locations. Perfect for breweries who want to keep their website up-to-date with minimal effort.

### Features

- **Untappd Integration**: Automatically sync your taplist from Untappd
- **Multi-Location Support**: Manage multiple taproom locations
- **Flexible Display**: Grid, list, or card layouts
- **Custom Post Types**: Beers stored as WordPress posts for full ecosystem compatibility
- **Automated Syncing**: Scheduled sync with Untappd (hourly, daily, or manual)
- **Admin Dashboard**: Easy-to-use settings and management interface
- **Developer Friendly**: Hooks, filters, and template overrides

## Installation

1. Upload the `ontap` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to OnTap > Settings to configure your Untappd API credentials
4. Add your taproom locations via OnTap > Taprooms
5. Run your first sync!

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- Untappd API credentials ([Register here](https://untappd.com/api/register))

## Development Status

### Phase 1: Foundation & Core Infrastructure ✅ COMPLETE

- ✅ Plugin structure with PSR-4 autoloading
- ✅ Custom post type for beers
- ✅ Taxonomies for taprooms and beer styles
- ✅ Custom table for tap availability tracking
- ✅ Settings framework with admin UI
- ✅ Activation/deactivation/uninstall handlers
- ✅ Basic admin and public assets

### Phase 2: Untappd API Integration (Next)

- API client class
- Authentication handling
- Menu data fetching
- Automated sync scheduling
- Data mapping and storage

## Plugin Structure

```
ontap/
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── public.css
│   └── js/
│       ├── admin.js
│       └── public.js
├── includes/
│   ├── admin/
│   │   ├── class-admin.php
│   │   └── class-settings.php
│   ├── class-autoloader.php
│   ├── class-plugin.php
│   ├── class-post-types.php
│   ├── class-activator.php
│   └── class-deactivator.php
├── languages/
├── ontap.php
├── uninstall.php
└── README.md
```

## Database Schema

### Custom Post Type: `ontap_beer`
Stores beer information with support for title, description, featured image (beer label), and custom fields.

### Taxonomies
- `ontap_taproom`: Taproom locations
- `ontap_style`: Beer styles (IPA, Stout, Lager, etc.)

### Custom Table: `wp_ontap_taplist`
Tracks which beers are available at which taprooms:

```sql
- id (bigint): Primary key
- beer_id (bigint): Reference to beer post
- taproom_id (bigint): Reference to taproom term
- tap_number (int): Position on tap wall
- is_available (boolean): Current availability
- pour_size (varchar): e.g., "16oz", "Flight"
- price (decimal): Price per pour
- untappd_menu_item_id (varchar): Reference to Untappd
- date_added (datetime): When added to tap
- date_modified (datetime): Last update
```

## Settings

Access via **OnTap > Settings** in WordPress admin:

### Untappd API Settings
- Client ID
- Client Secret

### Sync Settings
- Sync Frequency (hourly, twice daily, daily, manual)
- Cache Duration (300-86400 seconds)

### Display Settings
- Show/hide out of stock beers
- Default layout (grid, list, card)

### Advanced Settings
- Delete data on uninstall

## Development Roadmap

- [x] **Phase 1**: Foundation & Core Infrastructure (COMPLETE)
- [ ] **Phase 2**: Untappd API Integration
- [ ] **Phase 3**: Admin Management Interface
- [ ] **Phase 4**: Frontend Display System
- [ ] **Phase 5**: Performance & Caching
- [ ] **Phase 6**: Commercial Features & Licensing
- [ ] **Phase 7**: Testing & QA

## Support

For issues, questions, or feature requests, please contact support.

## License

Copyright 2025 OnTap. All rights reserved.

## Changelog

### 1.0.0 (2025-10-25)
- Initial release
- Phase 1: Core plugin foundation complete
