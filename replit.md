# Pearl Weather - WordPress Plugin

## Overview
Pearl Weather is a WordPress plugin that displays real-time weather forecasts using the OpenWeatherMap API. It supports Gutenberg blocks, shortcodes, auto-location detection, and customizable weather widgets.

## Tech Stack
- **Backend:** PHP 8.2 (WordPress plugin)
- **Frontend (Blocks/Admin):** React.js, built via @wordpress/scripts (webpack)
- **Database:** SQLite via WordPress SQLite Database Integration plugin
- **Build Tools:** npm + @wordpress/scripts for JS, Composer for PHP autoloading

## Project Structure
```
/                          - Plugin root (PHP entry point: main.php)
├── main.php               - WordPress plugin entry point
├── includes/              - PHP classes
│   ├── Admin.php          - Admin controller (namespace-based)
│   ├── Frontend.php       - Frontend controller (namespace-based)
│   ├── class-api-handler.php      - OpenWeatherMap API handler
│   ├── class-location-detector.php - IP-based location detection
│   ├── class-cache-manager.php    - WordPress transient cache
│   ├── admin/             - Admin class files
│   │   ├── class-admin-settings.php
│   │   └── class-admin-notices.php
│   ├── frontend/          - Frontend class files
│   │   ├── class-shortcode.php
│   │   └── class-assets-loader.php
│   ├── Admin/             - Admin submodules (namespace-based)
│   ├── Blocks/            - Gutenberg block PHP + pre-built JS
│   │   └── build/         - Pre-compiled block JS/CSS
│   └── Frontend/          - Frontend submodules (namespace-based)
├── assets/                - Static CSS, JS, fonts, images
├── vendor/                - Composer autoload
├── wordpress/             - WordPress installation (development only)
│   ├── wp-config.php      - WordPress configuration (SQLite)
│   ├── router.php         - PHP built-in server router
│   └── wp-content/
│       ├── plugins/pearl-weather -> /home/runner/workspace (symlink)
│       └── plugins/sqlite-database-integration/
├── package.json           - npm dependencies
└── composer.json          - PHP dependencies (PSR-4 autoload)
```

## Development Setup

### WordPress Environment
WordPress runs via PHP's built-in development server using SQLite (no MySQL required).

**Start server:** `php -S 0.0.0.0:5000 -t /home/runner/workspace/wordpress /home/runner/workspace/wordpress/router.php`

**Admin credentials:**
- URL: `http://localhost:5000/wp-admin`
- Username: `admin`
- Password: `admin123`

### Building JavaScript
Pre-built JS/CSS files are committed to the repo under:
- `includes/Blocks/build/` - Gutenberg blocks
- `includes/Admin/AdminDashboard/build/` - Admin dashboard React app

To rebuild (requires npm install):
```bash
npm run build:blocks   # Build Gutenberg blocks
npm run build:admin    # Build admin dashboard
```

### Composer / PHP Autoload
```bash
composer install   # Install PHP dependencies + generate autoload
```

Note: `composer.js` is the original config file (misnamed). `composer.json` is the working copy.

## Key Notes
- The `package-lock.js` file is misnamed (should be `package-lock.json`) - npm install may fail due to this
- `includes/Functions .php` was renamed to `includes/functions.php` (had a space in the name)
- Pre-built JS files are committed so npm install is not required for basic operation
- The plugin symlink: `wordpress/wp-content/plugins/pearl-weather` → project root
- WordPress data stored in SQLite: `wordpress/wp-content/database/.ht.sqlite`
