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
- ✅ Custom tables for tap availability and container pricing
- ✅ Multi-container support (flights, pints, crowlers, to-go packages)
- ✅ Helper classes for taplist and container management
- ✅ Settings framework with admin UI
- ✅ Activation/deactivation/uninstall handlers
- ✅ Basic admin and public assets

### Phase 2: Untappd API Integration ✅ COMPLETE

- ✅ Untappd Business API client with Basic Auth
- ✅ Authentication via email + API token
- ✅ Endpoints: locations, menus, menu items
- ✅ Sync Manager for automated data syncing
- ✅ Beer creation/updates from Untappd data
- ✅ Container (serving size/price) syncing
- ✅ Taproom term meta for menu ID mapping
- ✅ Manual and automated sync support
- ✅ Image downloading and attachment
- ✅ Style taxonomy auto-assignment
- ✅ AJAX handlers for sync triggers
- ✅ API response caching with transients

### Phase 3: Admin Management Interface (Next)

- Enhanced taproom management UI
- Manual overrides for availability
- Reorder tap positions
- Sync logs and error reporting

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
│   ├── class-taplist.php (helper for taplist management)
│   ├── class-container.php (helper for container/pricing management)
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
- untappd_menu_item_id (varchar): Reference to Untappd menu item
- date_added (datetime): When added to tap
- date_modified (datetime): Last update
```

### Custom Table: `wp_ontap_containers`
Stores serving sizes and prices (Untappd "containers") for each taplist item. A single beer can have multiple container options (e.g., 3oz flight, 12oz pour, 16oz pint, 32oz crowler, 6-pack to-go).

```sql
- id (bigint): Primary key
- taplist_id (bigint): Reference to taplist item
- container_type (varchar): Type (e.g., "Draft", "Can", "Bottle", "Crowler")
- size (varchar): Size (e.g., "3oz", "12oz", "16oz", "32oz", "6-pack")
- price (decimal): Price for this container
- is_available (boolean): Current availability
- sort_order (int): Display order
- untappd_container_id (varchar): Reference to Untappd container
- date_added (datetime): When added
- date_modified (datetime): Last update
```

**Example Data Flow:**
```
Beer: "Hazy IPA" (post_id: 123)
├─ Taproom: "Main Location" (term_id: 1)
│  ├─ Taplist Item (id: 1, beer_id: 123, taproom_id: 1)
│  │  ├─ Container: 3oz - $2.50 (Flight)
│  │  ├─ Container: 6oz - $4.00 (Tulip)
│  │  ├─ Container: 12oz - $7.00 (Regular)
│  │  └─ Container: 32oz - $15.00 (Crowler)
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
