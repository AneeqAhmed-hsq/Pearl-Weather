# WordPress Weather Plugins – Developer Environment

## Overview
This Replit runs a local WordPress development environment (PHP built-in server + SQLite) hosting two plugins:
1. **Pearl Weather** – original imported plugin (`/` project root)
2. **AtmoPress Weather** – new independently-built plugin (see below)

## WordPress Environment

**URL:** `http://localhost:5000`  
**Admin:** `/wp-admin` — user: `admin` / pass: `admin123`  
**Database:** SQLite via `wordpress/wp-content/database/.ht.sqlite`  
**Server:** `php -S 0.0.0.0:5000 -t wordpress wordpress/router.php`

---

## AtmoPress Weather Plugin

A complete, original WordPress weather plugin at:
`wordpress/wp-content/plugins/atmopress-weather/`

### File Structure

```
atmopress-weather/
├── atmopress-weather.php        # Main plugin entry point (constants, requires, hooks)
├── uninstall.php                # Cleanup on plugin deletion
│
├── core/                        # All PHP business logic
│   ├── class-bootstrap.php      # Service registration, asset enqueuing
│   ├── class-settings.php       # WordPress Options API wrapper (get/save/defaults)
│   ├── class-data-cache.php     # Transient-based caching with auto-prefix
│   ├── class-api-client.php     # OpenWeatherMap + WeatherAPI.com clients, normalizer
│   ├── class-template-loader.php# Template registry, render(), shared helpers
│   ├── class-shortcode.php      # [atmopress] and [atmopress-weather] shortcodes
│   ├── class-rest-api.php       # WP REST API endpoints (atmopress/v1/*)
│   ├── class-gutenberg-block.php# Block registration + server-side render
│   └── admin/
│       └── class-admin-page.php # Admin menu: Settings, Templates, Shortcode Generator
│
├── templates/                   # PHP template files (one per layout)
│   ├── card.php                 # Card: icon + temp + stats + hourly + daily
│   ├── minimal.php              # Minimal: compact single-row layout
│   ├── grid.php                 # Grid: gradient hero + stat cells + day cards
│   ├── horizontal.php           # Horizontal: banner-style row layout
│   └── forecast.php             # Forecast: hourly strip + daily bar graph
│
├── assets/
│   ├── css/
│   │   ├── frontend.css         # All public-facing widget styles (CSS vars, responsive)
│   │   └── admin.css            # Admin settings page styles
│   └── js/
│       ├── frontend.js          # Widget hydration, search, geolocation, unit toggle
│       └── admin.js             # API key test, cache flush, shortcode generator
│
└── block/
    └── editor.js                # Gutenberg block editor (no-build, wp.element API)
```

### REST API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/wp-json/atmopress/v1/weather` | Raw normalized weather JSON |
| GET | `/wp-json/atmopress/v1/render` | Server-rendered template HTML |
| GET | `/wp-json/atmopress/v1/test-api` | Validate API key (admin only) |
| GET | `/wp-json/atmopress/v1/settings` | Get plugin settings (admin only) |
| POST | `/wp-json/atmopress/v1/settings` | Save plugin settings (admin only) |
| POST | `/wp-json/atmopress/v1/flush-cache` | Flush weather transients (admin only) |

### Shortcode Usage

Basic:
```
[atmopress]
```

Full options:
```
[atmopress
  template="card|minimal|grid|horizontal|forecast"
  location="London"
  units="metric|imperial"
  show_search="true"
  show_geolocation="true"
  show_humidity="true"
  show_wind="true"
  show_pressure="true"
  show_visibility="true"
  show_feels_like="true"
  show_sunrise="true"
  show_hourly="true"
  show_daily="true"
  forecast_days="7"
  hourly_count="8"
  color_primary="#2563eb"
  color_bg="#ffffff"
  color_text="#1e293b"
  border_radius="16"
  font_size="14"
  custom_class=""
]
```

### Gutenberg Block

Register in the block editor as **"AtmoPress Weather"** (category: Widgets).  
All shortcode options are available as block controls in the sidebar.  
Live preview is fetched via REST API as you change settings.

### Admin Pages

- **AtmoPress → Settings** — API key, provider, units, cache, location
- **AtmoPress → Templates** — Visual template browser with copyable shortcodes  
- **AtmoPress → Shortcode** — Interactive shortcode generator

---

## How to Add a New Template

1. Create `templates/my-template.php`
2. Register it in `core/class-template-loader.php` → `registered()`:
   ```php
   'my-template' => array( 'label' => __( 'My Template', 'atmopress-weather' ), 'file' => 'my-template.php' ),
   ```
3. Use available variables: `$weather['current']`, `$weather['hourly']`, `$weather['daily']`, `$config`, `$unit`, `$speed`
4. Call `TemplateLoader::css_vars($config)` for inline CSS vars

## How to Add a New Feature

1. **New data field from API** → add normalization in `class-api-client.php` → `normalize_owm()` / `normalize_weatherapi()`
2. **New toggle option** → add to `class-template-loader.php` → `default_config()`, `class-shortcode.php` attr list, `class-gutenberg-block.php` attributes, and the block editor sidebar in `block/editor.js`
3. **New admin page** → add `add_submenu_page()` in `class-admin-page.php`
4. **New REST endpoint** → add `register_rest_route()` in `class-rest-api.php`

## API Key Setup

1. Go to **WordPress Admin → AtmoPress → Settings**
2. Choose provider: OpenWeatherMap (free) or WeatherAPI.com (free)
3. Paste your API key and click **Test Key**
4. Save settings

Get free keys:
- OpenWeatherMap: https://home.openweathermap.org/api_keys
- WeatherAPI.com: https://www.weatherapi.com/my/

---

## Pearl Weather Plugin (Original Import)

Located at project root (`/home/runner/workspace/`), symlinked into WordPress.
- Entry: `main.php`
- PHP autoload: `vendor/` (Composer, PSR-4 namespace `ShapedPlugin\Weather\`)
- Pre-built JS: `includes/Blocks/build/` and `includes/Admin/AdminDashboard/build/`
- Admin credentials: user `admin` / pass `admin123`
