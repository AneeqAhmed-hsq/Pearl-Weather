<?php
/**
 * Weather Map Template Renderer
 *
 * Renders weather maps (Windy Map, OpenWeatherMap, Leaflet, etc.)
 * based on block attributes and user settings.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Templates/Parts
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template variables:
 * - $attributes: Block attributes (settings)
 * - $block_name: Block name
 * - $unique_id: Unique block identifier
 * - $weather_data: Current weather data array
 */

// Check if weather map should be displayed.
$show_map = isset( $attributes['displayWeatherMap'] ) ? (bool) $attributes['displayWeatherMap'] : true;

if ( ! $show_map ) {
    return;
}

// Map provider selection.
$map_provider = isset( $attributes['weatherMapType'] ) ? sanitize_text_field( $attributes['weatherMapType'] ) : 'windy';

// Get location for map centering.
$latitude = isset( $weather_data['lat'] ) ? (float) $weather_data['lat'] : 51.509865;
$longitude = isset( $weather_data['lon'] ) ? (float) $weather_data['lon'] : -0.118092;
$city_name = isset( $weather_data['city'] ) ? $weather_data['city'] : 'London';

// Map settings.
$map_zoom = isset( $attributes['mapZoomLevel'] ) ? (int) $attributes['mapZoomLevel'] : 8;
$enable_scroll_zoom = isset( $attributes['activeZoomScrollWheel'] ) ? (bool) $attributes['activeZoomScrollWheel'] : true;
$show_legend = isset( $attributes['activeLegends'] ) ? (bool) $attributes['activeLegends'] : false;
$map_height = isset( $attributes['splwMapHeight']['device']['Desktop'] ) ? (int) $attributes['splwMapHeight']['device']['Desktop'] : 400;
$map_width = isset( $attributes['splwMaxWidth']['device']['Desktop'] ) ? (int) $attributes['splwMaxWidth']['device']['Desktop'] : '100%';

// Additional map provider settings.
$default_elevation = isset( $attributes['defaultElevation'] ) ? sanitize_text_field( $attributes['defaultElevation'] ) : 'surface';
$show_marker = isset( $attributes['showMarker'] ) ? (bool) $attributes['showMarker'] : true;
$spot_forecast = isset( $attributes['spotForecast'] ) ? (bool) $attributes['spotForecast'] : false;
$forecast_model = isset( $attributes['forecastModel'] ) ? sanitize_text_field( $attributes['forecastModel'] ) : 'ecmwf';
$show_airflow = isset( $attributes['airflowPressureLines'] ) ? (bool) $attributes['airflowPressureLines'] : false;

// Map styling.
$border_radius = isset( $attributes['weatherMapBorderRadius']['value'] ) ? (int) $attributes['weatherMapBorderRadius']['value'] : 8;
$border_style = isset( $attributes['weatherMapBorder']['style'] ) ? $attributes['weatherMapBorder']['style'] : 'solid';
$border_color = isset( $attributes['weatherMapBorder']['color'] ) ? sanitize_hex_color( $attributes['weatherMapBorder']['color'] ) : '#e2e2e2';
$enable_shadow = isset( $attributes['enableWeatherMapBoxShadow'] ) ? (bool) $attributes['enableWeatherMapBoxShadow'] : true;
$bg_color = isset( $attributes['weatherMapBgColor'] ) ? sanitize_hex_color( $attributes['weatherMapBgColor'] ) : '#f5f5f5';

// CSS classes.
$wrapper_classes = array( 'pw-weather-map-wrapper' );
$wrapper_classes[] = 'pw-map-provider-' . $map_provider;

if ( ! empty( $attributes['mapCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['mapCustomClass'] );
}

// Inline styles.
$inline_styles = array(
    '--pw-map-height: ' . $map_height . 'px',
    '--pw-map-border-radius: ' . $border_radius . 'px',
    '--pw-map-border: ' . $border_style . ' 1px ' . $border_color,
    '--pw-map-bg: ' . $bg_color,
);

if ( $enable_shadow ) {
    $inline_styles[] = '--pw-map-shadow: 0 4px 12px rgba(0, 0, 0, 0.1)';
} else {
    $inline_styles[] = '--pw-map-shadow: none';
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     style="<?php echo esc_attr( implode( '; ', $inline_styles ) ); ?>"
     data-map-provider="<?php echo esc_attr( $map_provider ); ?>"
     data-lat="<?php echo esc_attr( $latitude ); ?>"
     data-lon="<?php echo esc_attr( $longitude ); ?>"
     data-zoom="<?php echo esc_attr( $map_zoom ); ?>"
     data-city="<?php echo esc_attr( $city_name ); ?>">
    
    <!-- Map Container -->
    <div id="pw-map-<?php echo esc_attr( $unique_id ?? uniqid() ); ?>" 
         class="pw-map-container"
         style="height: var(--pw-map-height); width: 100%;">
        
        <?php if ( 'windy' === $map_provider ) : ?>
            <!-- Windy Map Integration -->
            <div class="pw-windy-map-placeholder" data-loading="true">
                <div class="pw-map-loading">
                    <div class="pw-loading-spinner"></div>
                    <span><?php esc_html_e( 'Loading map...', 'pearl-weather' ); ?></span>
                </div>
            </div>
            
            <?php if ( $show_legend ) : ?>
                <div class="pw-map-legend">
                    <div class="pw-legend-title"><?php esc_html_e( 'Wind Speed', 'pearl-weather' ); ?></div>
                    <div class="pw-legend-gradient"></div>
                    <div class="pw-legend-labels">
                        <span>0</span>
                        <span>10</span>
                        <span>20</span>
                        <span>30+</span>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php elseif ( 'leaflet' === $map_provider ) : ?>
            <!-- Leaflet/OpenStreetMap Integration -->
            <div id="pw-leaflet-map-<?php echo esc_attr( $unique_id ?? uniqid() ); ?>" 
                 class="pw-leaflet-map"
                 style="height: 100%; width: 100%;"></div>
                 
            <?php if ( $show_marker ) : ?>
                <div class="pw-map-marker-info" style="display: none;">
                    <div class="pw-marker-title"><?php echo esc_html( $city_name ); ?></div>
                    <div class="pw-marker-temp"><?php echo esc_html( $weather_data['temperature'] ?? '--' ); ?>°</div>
                </div>
            <?php endif; ?>
            
        <?php elseif ( 'openweather' === $map_provider ) : ?>
            <!-- OpenWeatherMap Static Map -->
            <?php
            $api_key = isset( $attributes['openweather_api_key'] ) ? $attributes['openweather_api_key'] : '';
            $map_layer = isset( $attributes['mapLayer'] ) ? $attributes['mapLayer'] : 'precipitation';
            $map_width_px = is_numeric( $map_width ) ? $map_width : 600;
            
            if ( ! empty( $api_key ) ) :
                $map_url = add_query_arg( array(
                    'appid' => $api_key,
                    'lat'   => $latitude,
                    'lon'   => $longitude,
                    'zoom'  => $map_zoom,
                    'layers' => $map_layer,
                    'width' => $map_width_px,
                    'height' => $map_height,
                ), 'https://tile.openweathermap.org/map/{layer}/{z}/{x}/{y}.png' );
                ?>
                <img src="<?php echo esc_url( $map_url ); ?>" 
                     alt="<?php esc_attr_e( 'Weather Map', 'pearl-weather' ); ?>"
                     class="pw-static-map"
                     loading="lazy">
            <?php else : ?>
                <div class="pw-map-error">
                    <p><?php esc_html_e( 'API key required for OpenWeatherMap.', 'pearl-weather' ); ?></p>
                </div>
            <?php endif; ?>
            
        <?php else : ?>
            <!-- Fallback: Simple Google Maps Static Map -->
            <div class="pw-map-fallback">
                <iframe 
                    width="100%" 
                    height="100%" 
                    frameborder="0" 
                    scrolling="no" 
                    marginheight="0" 
                    marginwidth="0"
                    src="https://maps.google.com/maps?q=<?php echo esc_attr( $latitude ); ?>,<?php echo esc_attr( $longitude ); ?>&z=<?php echo esc_attr( $map_zoom ); ?>&output=embed">
                </iframe>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Map Controls (Optional) -->
    <?php if ( isset( $attributes['showMapControls'] ) && $attributes['showMapControls'] ) : ?>
        <div class="pw-map-controls">
            <button class="pw-map-zoom-in" aria-label="<?php esc_attr_e( 'Zoom In', 'pearl-weather' ); ?>">+</button>
            <button class="pw-map-zoom-out" aria-label="<?php esc_attr_e( 'Zoom Out', 'pearl-weather' ); ?>">-</button>
            <button class="pw-map-reset" aria-label="<?php esc_attr_e( 'Reset Map', 'pearl-weather' ); ?>">⟳</button>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Weather Map Styles */
.pw-weather-map-wrapper {
    position: relative;
    margin: 16px 0;
    border-radius: var(--pw-map-border-radius);
    border: var(--pw-map-border);
    background: var(--pw-map-bg);
    box-shadow: var(--pw-map-shadow);
    overflow: hidden;
}

.pw-map-container {
    position: relative;
    min-height: 200px;
}

/* Map Loading State */
.pw-map-loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.9);
    z-index: 10;
}

.pw-loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(0, 0, 0, 0.1);
    border-top-color: var(--pw-primary-color, #f26c0d);
    border-radius: 50%;
    animation: pw-spin 0.8s linear infinite;
    margin-bottom: 12px;
}

@keyframes pw-spin {
    to { transform: rotate(360deg); }
}

/* Map Legend */
.pw-map-legend {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 11px;
    z-index: 5;
}

.pw-legend-title {
    font-weight: 600;
    margin-bottom: 4px;
}

.pw-legend-gradient {
    width: 120px;
    height: 8px;
    background: linear-gradient(90deg, #00ff00, #ffff00, #ff0000);
    border-radius: 4px;
    margin: 6px 0;
}

.pw-legend-labels {
    display: flex;
    justify-content: space-between;
    width: 120px;
}

/* Map Controls */
.pw-map-controls {
    position: absolute;
    bottom: 12px;
    left: 12px;
    display: flex;
    gap: 6px;
    z-index: 5;
}

.pw-map-controls button {
    width: 32px;
    height: 32px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: all 0.2s ease;
}

.pw-map-controls button:hover {
    background: var(--pw-primary-color, #f26c0d);
    color: #fff;
    border-color: var(--pw-primary-color, #f26c0d);
}

/* Map Error */
.pw-map-error {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 200px;
    background: #f8f9fa;
    color: #dc3545;
    text-align: center;
    padding: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-map-legend {
        display: none;
    }
    
    .pw-map-controls {
        bottom: 8px;
        left: 8px;
    }
    
    .pw-map-controls button {
        width: 28px;
        height: 28px;
        font-size: 14px;
    }
}
</style>

<?php
/**
 * Map provider specific scripts.
 * These would be conditionally enqueued based on the selected provider.
 */

// Windy Map API script (example).
if ( 'windy' === $map_provider && ! wp_script_is( 'windy-api', 'enqueued' ) ) {
    ?>
    <script>
    (function() {
        // Windy API initialization
        const windyContainer = document.querySelector('.pw-windy-map-placeholder');
        if (!windyContainer) return;
        
        // Load Windy API
        const script = document.createElement('script');
        script.src = 'https://api.windy.com/api/map/v2/loader.js';
        script.onload = function() {
            windyInit({
                key: '<?php echo esc_js( $attributes['windy_api_key'] ?? '' ); ?>',
                lat: <?php echo esc_js( $latitude ); ?>,
                lon: <?php echo esc_js( $longitude ); ?>,
                zoom: <?php echo esc_js( $map_zoom ); ?>,
                elevation: '<?php echo esc_js( $default_elevation ); ?>',
                marker: <?php echo esc_js( $show_marker ? 'true' : 'false' ); ?>,
                spotForecast: <?php echo esc_js( $spot_forecast ? 'true' : 'false' ); ?>,
                model: '<?php echo esc_js( $forecast_model ); ?>',
                pressureLines: <?php echo esc_js( $show_airflow ? 'true' : 'false' ); ?>,
                scrollZoom: <?php echo esc_js( $enable_scroll_zoom ? 'true' : 'false' ); ?>,
                container: windyContainer
            });
            windyContainer.setAttribute('data-loading', 'false');
            const loadingEl = windyContainer.querySelector('.pw-map-loading');
            if (loadingEl) loadingEl.remove();
        };
        document.head.appendChild(script);
    })();
    </script>
    <?php
}

// Leaflet Map script (example).
if ( 'leaflet' === $map_provider ) {
    ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function() {
        const mapContainer = document.querySelector('.pw-leaflet-map');
        if (!mapContainer) return;
        
        const lat = <?php echo esc_js( $latitude ); ?>;
        const lon = <?php echo esc_js( $longitude ); ?>;
        const zoom = <?php echo esc_js( $map_zoom ); ?>;
        
        const map = L.map(mapContainer).setView([lat, lon], zoom);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        <?php if ( $show_marker ) : ?>
        const markerInfo = document.querySelector('.pw-map-marker-info');
        const popupContent = markerInfo ? markerInfo.innerHTML : '<?php echo esc_js( $city_name ); ?>';
        L.marker([lat, lon]).addTo(map).bindPopup(popupContent).openPopup();
        <?php endif; ?>
        
        // Zoom controls
        const zoomInBtn = document.querySelector('.pw-map-zoom-in');
        const zoomOutBtn = document.querySelector('.pw-map-zoom-out');
        const resetBtn = document.querySelector('.pw-map-reset');
        
        if (zoomInBtn) zoomInBtn.addEventListener('click', () => map.zoomIn());
        if (zoomOutBtn) zoomOutBtn.addEventListener('click', () => map.zoomOut());
        if (resetBtn) resetBtn.addEventListener('click', () => map.setView([lat, lon], zoom));
    })();
    </script>
    <?php
}