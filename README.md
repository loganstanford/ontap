# Ology Brewing WordPress Plugin

A modern, scalable WordPress plugin for brewery management with Untappd and Dropbox integration.

## 🎯 Features

- **Modern Architecture**: Clean, maintainable code with proper separation of concerns
- **File-Based Logging**: No more database bloat from debug logs
- **API Integration**: Untappd and Dropbox API clients with rate limiting
- **Admin Interface**: Modern, responsive admin interface
- **Error Handling**: Proper exception handling throughout
- **Performance**: Optimized for large datasets
- **Security**: Input validation and sanitization

## 🚀 Quick Start

### Prerequisites

- WordPress 5.0+
- PHP 7.4+
- Composer (for dependencies)

### Installation

1. **Clone the repository**:

   ```bash
   git clone <repository-url> ology-brewing
   cd ology-brewing
   ```

2. **Install dependencies**:

   ```bash
   composer install
   ```

3. **Activate the plugin**:
   - Upload to `/wp-content/plugins/ology-brewing/`
   - Activate through WordPress admin

### Configuration

1. **Go to Ology Brewing → Settings**
2. **Enter your API credentials**:
   - Untappd Client ID
   - Untappd Client Secret
   - Dropbox Access Token
3. **Configure sync settings**
4. **Enable debug logging if needed**

## 📁 Project Structure

```
ology-brewing/
├── ology-brewing.php              # Main plugin file
├── includes/                       # Core classes
│   ├── class-ology-brewing.php    # Main plugin class
│   ├── class-logger.php           # Logging system
│   ├── class-cache-manager.php    # Caching layer
│   ├── class-admin-interface.php  # Admin interface
│   └── class-database.php         # Database management
├── admin/                          # Admin interface
│   └── views/                      # Admin templates
├── assets/                         # CSS/JS/Images
├── logs/                          # Log files
└── tests/                         # Test suite
```

## 🔧 Development

### Local Development Setup

```bash
# Install dependencies
composer install

# Run tests
composer test

# Watch for changes
composer watch
```

### Code Standards

- **PSR-4 Autoloading**: Follow WordPress coding standards
- **Namespacing**: Use `OlogyBrewing\` namespace
- **Documentation**: PHPDoc for all classes and methods
- **Testing**: Unit tests for all business logic

### Testing

```bash
# Run unit tests
composer test

# Run integration tests
composer test:integration

# Run all tests
composer test:all
```

## 📊 Architecture

### Design Principles

1. **Separation of Concerns**: Models, Services, Controllers, Views
2. **Dependency Injection**: Clean, testable code
3. **Interface-Based Design**: Flexible, extensible architecture
4. **Event-Driven**: WordPress hooks and actions

### Core Components

- **Logger**: File-based logging with rotation
- **CacheManager**: Transient-based caching
- **Database**: Schema management and migrations
- **AdminInterface**: Modern admin interface
- **API Clients**: Untappd and Dropbox integration

## 🚨 Migration from Current System

### Current System Problems

- 5,000+ lines in single file
- Debug output pollution
- Database bloat (1.5MB logs in wp_options)
- No error handling
- Performance issues

### Migration Strategy

1. **Parallel Development**: Build alongside current system
2. **Feature Parity**: Ensure all features work
3. **Data Migration**: Migrate existing data
4. **Gradual Cutover**: Switch features one by one
5. **Rollback Plan**: Keep current system as backup

## 📝 API Reference

### Logger Class

```php
$logger = new OlogyBrewing\Logger();

$logger->info('Information message');
$logger->warning('Warning message');
$logger->error('Error message');
$logger->debug('Debug message');
```

### Cache Manager

```php
$cache = new OlogyBrewing\CacheManager();

$cache->set('key', $value, 3600); // 1 hour
$value = $cache->get('key', $default);
$cache->delete('key');
```

### Database

```php
// Create tables
OlogyBrewing\Database::create_tables();

// Drop tables
OlogyBrewing\Database::drop_tables();
```

## 🔍 Debugging

### Enable Debug Logging

1. Go to **Ology Brewing → Settings**
2. Check **Enable debug logging**
3. Select debug level (minimal, normal, verbose)
4. View logs in **Ology Brewing → Sync Logs**

### Log Files

- **Location**: `/wp-content/logs/ology-brewing/`
- **Files**: `error.log`, `warning.log`, `info.log`, `debug.log`
- **Rotation**: Automatic when files exceed 10MB

### Admin Interface

- **Dashboard**: Overview and recent activity
- **Settings**: API credentials and configuration
- **Logs**: Real-time log viewing

## 🚀 Deployment

### Pre-deployment Checklist

- [ ] All tests passing
- [ ] Code review completed
- [ ] Documentation updated
- [ ] Database migrations tested
- [ ] Performance benchmarks recorded

### Deployment Steps

1. **Create feature branch**: `git checkout -b feature/your-feature`
2. **Implement changes**: Follow coding standards
3. **Run tests**: `composer test`
4. **Code review**: Submit pull request
5. **Merge to develop**: After approval
6. **Deploy to staging**: Test in staging environment
7. **Deploy to production**: After staging validation

## 📈 Performance

### Targets

- **Sync Time**: < 5 minutes for 1000 beers
- **Memory Usage**: < 128MB during sync
- **Database Queries**: < 100 queries per sync
- **Error Rate**: < 1% sync failures

### Optimization

- **Caching**: API responses cached for 1 hour
- **Batch Processing**: Process items in batches
- **Memory Management**: Clear unused variables
- **Database Optimization**: Efficient queries

## 🔒 Security

### Best Practices

- **Input Validation**: All user input sanitized
- **Nonce Verification**: CSRF protection
- **Capability Checks**: User permission validation
- **SQL Injection**: Prepared statements
- **XSS Prevention**: Output escaping

### API Security

- **Rate Limiting**: Prevent API abuse
- **Token Management**: Secure credential storage
- **Error Handling**: No sensitive data in logs

## 📞 Support

### Getting Help

- **Documentation**: Check this README
- **Issues**: Create GitHub issue
- **Logs**: Check debug logs for errors
- **Settings**: Verify API credentials

### Common Issues

1. **Plugin won't activate**: Check PHP version (7.4+)
2. **API errors**: Verify credentials in settings
3. **Sync fails**: Check debug logs for details
4. **Performance issues**: Enable debug logging

## 📄 License

GPL v2 or later

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Make changes
4. Add tests
5. Submit pull request

---

**Version**: 1.0.0  
**Last Updated**: 2024-10-23  
**Status**: Development Phase 1 Complete
