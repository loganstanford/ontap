# Ology Brewing System - Architecture Plan

## 🎯 Long-term Goal

Create a modern, scalable WordPress plugin system for brewery management that handles:

- Beer inventory sync from Untappd API
- File management via Dropbox API
- Location-based availability tracking
- Clean, maintainable code architecture
- Proper logging and error handling
- Performance optimization

## 🚨 Current System Problems

### Technical Debt Issues

1. **Monolithic Plugin**: 5,000+ lines in single file
2. **Poor Separation of Concerns**: Business logic mixed with WordPress hooks
3. **Debug Output Pollution**: `print_r()` statements outputting to browser
4. **Database Bloat**: 1.5MB debug logs in `wp_options` causing binary log explosion
5. **No Error Handling**: Silent failures, no proper exception handling
6. **Hardcoded Values**: API endpoints, timeouts, retry logic scattered throughout
7. **No Testing**: No unit tests, integration tests, or validation
8. **Performance Issues**: N+1 queries, inefficient database operations
9. **Security Concerns**: No input validation, potential SQL injection
10. **Maintenance Nightmare**: Changes require touching core files

### Current Architecture (What NOT to do)

```
ology-custom.php (5,000+ lines)
├── WordPress hooks mixed with business logic
├── Direct database queries
├── Hardcoded API calls
├── Debug output to browser
├── No error handling
└── No separation of concerns
```

## 🏗️ Target Architecture

### Modern WordPress Plugin Structure

```
ology-brewing/
├── ology-brewing.php                 # Main plugin file
├── includes/
│   ├── class-ology-brewing.php      # Main plugin class
│   ├── class-api-client.php          # API abstraction layer
│   ├── class-sync-manager.php        # Sync orchestration
│   ├── class-logger.php              # Centralized logging
│   ├── class-cache-manager.php       # Caching layer
│   └── class-admin-interface.php     # Admin UI
├── api/
│   ├── class-untappd-api.php         # Untappd API client
│   ├── class-dropbox-api.php        # Dropbox API client
│   └── class-api-rate-limiter.php    # Rate limiting
├── models/
│   ├── class-beer.php                # Beer data model
│   ├── class-location.php            # Location data model
│   └── class-sync-log.php            # Sync log model
├── services/
│   ├── class-beer-sync-service.php    # Beer sync logic
│   ├── class-file-sync-service.php   # File sync logic
│   └── class-location-service.php   # Location management
├── admin/
│   ├── class-settings-page.php       # Settings interface
│   ├── class-debug-page.php          # Debug interface
│   └── class-sync-page.php           # Manual sync interface
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── tests/
│   ├── unit/
│   ├── integration/
│   └── fixtures/
└── logs/                             # File-based logging
```

## 🔧 Core Design Principles

### 1. **Separation of Concerns**

- **Models**: Data structures and business rules
- **Services**: Business logic and operations
- **API Clients**: External API communication
- **Controllers**: Request/response handling
- **Views**: UI presentation

### 2. **Dependency Injection**

```php
class BeerSyncService {
    private $untappd_api;
    private $logger;
    private $cache;

    public function __construct(UntappdAPI $untappd_api, Logger $logger, CacheManager $cache) {
        $this->untappd_api = $untappd_api;
        $this->logger = $logger;
        $this->cache = $cache;
    }
}
```

### 3. **Interface-Based Design**

```php
interface APIInterface {
    public function authenticate(): bool;
    public function getBeers(): array;
    public function getLocations(): array;
}

class UntappdAPI implements APIInterface {
    // Implementation
}
```

### 4. **Event-Driven Architecture**

```php
// Events
do_action('ology_beer_synced', $beer_id, $sync_data);
do_action('ology_sync_failed', $error_message, $context);

// Hooks
add_action('ology_beer_synced', 'send_notification', 10, 2);
```

## 📋 Implementation Phases

### Phase 1: Foundation (Week 1)

**Goal**: Create basic plugin structure and core classes

**Tasks**:

1. ✅ Create main plugin file with proper headers
2. ✅ Implement autoloader for classes
3. ✅ Create core plugin class with activation/deactivation
4. ✅ Set up basic logging system (file-based)
5. ✅ Create settings framework
6. ✅ Basic admin interface

**Deliverables**:

- Working plugin that can be activated
- Settings page with API credentials
- File-based logging system
- Basic admin interface

### Phase 2: API Layer (Week 2)

**Goal**: Abstract external API calls

**Tasks**:

1. ✅ Create API client base class
2. ✅ Implement Untappd API client
3. ✅ Implement Dropbox API client
4. ✅ Add rate limiting and retry logic
5. ✅ Error handling and logging

**Deliverables**:

- Working API clients
- Rate limiting
- Proper error handling
- API response caching

### Phase 3: Data Models (Week 3)

**Goal**: Create clean data models

**Tasks**:

1. ✅ Create Beer model with validation
2. ✅ Create Location model
3. ✅ Create SyncLog model
4. ✅ Database schema management
5. ✅ Data validation and sanitization

**Deliverables**:

- Clean data models
- Database migrations
- Data validation
- CRUD operations

### Phase 4: Sync Services (Week 4)

**Goal**: Implement sync logic

**Tasks**:

1. ✅ Create BeerSyncService
2. ✅ Create FileSyncService
3. ✅ Create LocationService
4. ✅ Implement sync orchestration
5. ✅ Add progress tracking

**Deliverables**:

- Working sync services
- Progress tracking
- Error recovery
- Sync scheduling

### Phase 5: Admin Interface (Week 5)

**Goal**: Modern admin interface

**Tasks**:

1. ✅ Settings page with validation
2. ✅ Debug/logging interface
3. ✅ Manual sync interface
4. ✅ Progress monitoring
5. ✅ Error reporting

**Deliverables**:

- Complete admin interface
- Real-time progress updates
- Error reporting
- Log viewing

### Phase 6: Testing & Optimization (Week 6)

**Goal**: Ensure reliability and performance

**Tasks**:

1. ✅ Unit tests for core classes
2. ✅ Integration tests for API clients
3. ✅ Performance optimization
4. ✅ Memory usage optimization
5. ✅ Database query optimization

**Deliverables**:

- Test suite
- Performance benchmarks
- Memory usage reports
- Optimization recommendations

## 🔄 Migration Strategy

### From Current System

1. **Parallel Development**: Build new system alongside current
2. **Feature Parity**: Ensure all current features work
3. **Data Migration**: Migrate existing data to new structure
4. **Gradual Cutover**: Switch features one by one
5. **Rollback Plan**: Keep current system as backup

### Data Migration Plan

```sql
-- Migrate existing beer data
INSERT INTO ology_beers (id, name, style, abv, ibu, description, untappd_id)
SELECT ID, post_title, meta_value, ... FROM wp_posts
WHERE post_type = 'beer';

-- Migrate location data
INSERT INTO ology_locations (id, name, slug, availability)
SELECT ID, post_title, post_name, meta_value FROM wp_posts
WHERE post_type = 'location';
```

## 🛠️ Development Guidelines

### Code Standards

- **PSR-4 Autoloading**: Follow WordPress coding standards
- **Namespacing**: Use `OlogyBrewing\` namespace
- **Documentation**: PHPDoc for all classes and methods
- **Testing**: Unit tests for all business logic
- **Error Handling**: Proper exception handling throughout

### File Organization

```
ology-brewing/
├── ology-brewing.php              # Plugin header and bootstrap
├── includes/                       # Core classes
├── api/                           # API clients
├── models/                        # Data models
├── services/                      # Business logic
├── admin/                         # Admin interface
├── assets/                        # CSS/JS/Images
├── tests/                         # Test suite
├── logs/                          # Log files
└── README.md                      # Documentation
```

### Database Design

```sql
-- Beers table
CREATE TABLE ology_beers (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    style VARCHAR(100),
    abv DECIMAL(3,1),
    ibu INT,
    description TEXT,
    untappd_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Locations table
CREATE TABLE ology_locations (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE,
    availability JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sync logs table
CREATE TABLE ology_sync_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    sync_type ENUM('beer', 'file', 'location'),
    status ENUM('success', 'error', 'warning'),
    message TEXT,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🚀 Getting Started

### Prerequisites

- WordPress 5.0+
- PHP 7.4+
- Composer (for dependencies)
- Git (for version control)

### Development Setup

```bash
# Clone repository
git clone <repository-url> ology-brewing
cd ology-brewing

# Install dependencies
composer install

# Run tests
composer test

# Build assets
npm install
npm run build
```

### Testing Strategy

- **Unit Tests**: Test individual classes and methods
- **Integration Tests**: Test API interactions
- **End-to-End Tests**: Test complete sync workflows
- **Performance Tests**: Test with large datasets

## 📊 Success Metrics

### Performance Targets

- **Sync Time**: < 5 minutes for 1000 beers
- **Memory Usage**: < 128MB during sync
- **Database Queries**: < 100 queries per sync
- **Error Rate**: < 1% sync failures

### Quality Targets

- **Test Coverage**: > 80% code coverage
- **Code Quality**: A rating on PHPStan
- **Documentation**: 100% class/method documentation
- **Security**: No critical vulnerabilities

## 🔧 Maintenance Plan

### Regular Tasks

- **Weekly**: Review error logs
- **Monthly**: Update API rate limits
- **Quarterly**: Performance optimization
- **Annually**: Security audit

### Monitoring

- **Error Tracking**: Sentry or similar
- **Performance Monitoring**: New Relic or similar
- **Log Analysis**: ELK stack or similar
- **Uptime Monitoring**: Pingdom or similar

## 📝 Notes for Future Developers

### Key Files to Reference

- **Current System**: `/Users/lkstanford/repos/ology-custom/ology-custom.php`
- **WordPress Standards**: https://developer.wordpress.org/coding-standards/
- **Plugin Development**: https://developer.wordpress.org/plugins/

### Common Pitfalls to Avoid

1. **Don't mix business logic with WordPress hooks**
2. **Don't output debug info to browser**
3. **Don't store large data in wp_options**
4. **Don't make synchronous API calls**
5. **Don't ignore error handling**
6. **Don't skip input validation**
7. **Don't forget about rate limiting**
8. **Don't hardcode configuration values**

### Debugging Tips

- Use file-based logging instead of database
- Implement proper error handling
- Add progress tracking for long operations
- Use WordPress transients for caching
- Monitor memory usage during sync
- Test with large datasets

---

**Last Updated**: 2024-10-23
**Version**: 1.0.0
**Status**: Planning Phase
