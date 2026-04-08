<?php
/**
 * OpenWeatherMap Template Renderer
 *
 * Renders OpenWeatherMap layers including precipitation, clouds,
 * temperature, wind, and pressure maps.
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
 * - $weather_data: Current weather data array
 * - $unique_id: Unique block identifier
 */

// Check if map should be displayed.
$show_map = isset( $attributes['displayWeatherMap'] ) ? (bool) $attributes['displayWeatherMap'] : true;

if ( ! $show_map ) {
    return;
}

// Get API key.
$api_key = isset( $attributes['openweather_api_key'] ) ? sanitize_text_field( $attributes['openweather_api_key'] ) : '';

// If no API key, show error message.
if ( empty( $api_key ) ) {
    ?>
    <div class="pw-map-error">
        <p><?php esc_html_e( 'OpenWeatherMap API key is required to display maps. Please add your API key in the plugin settings.', 'pearl-weather' ); ?></p>
    </div>
    <?php
    return;
}

// Get coordinates.
$latitude = isset( $weather_data['lat'] ) ? (float) $weather_data['lat'] : 51.509865;
$longitude = isset( $weather_data['lon'] ) ? (float) $weather_data['lon'] : -0.118092;

// Map settings.
$map_layer = isset( $attributes['mapLayer'] ) ? sanitize_text_field( $attributes['mapLayer'] ) : 'precipitation_new';
$zoom_level = isset( $attributes['mapZoomLevel'] ) ? (int) $attributes['mapZoomLevel'] : 8;
$map_width = isset( $attributes['weatherMapMaxWidth']['device']['Desktop'] ) ? (int) $attributes['weatherMapMaxWidth']['device']['Desktop'] : 600;
$map_height = isset( $attributes['weatherMapMaxHeight']['device']['Desktop'] ) ? (int) $attributes['weatherMapMaxHeight']['device']['Desktop'] : 400;
$map_opacity = isset( $attributes['layerOpacity']['value'] ) ? (int) $attributes['layerOpacity']['value'] : 50;

// Map layer options.
$map_layers = array(
    'precipitation_new' => array(
        'label' => __( 'Precipitation', 'pearl-weather' ),
        'icon'  => '🌧️',
    ),
    'clouds_new' => array(
        'label' => __( 'Clouds', 'pearl-weather' ),
        'icon'  => '☁️',
    ),
    'temp_new' => array(
        'label' => __( 'Temperature', 'pearl-weather' ),
        'icon'  => '🌡️',
    ),
    'wind_new' => array(
        'label' => __( 'Wind', 'pearl-weather' ),
        'icon'  => '💨',
    ),
    'pressure_new' => array(
        'label' => __( 'Pressure', 'pearl-weather' ),
        'icon'  => '📊',
    ),
);

$current_layer = isset( $map_layers[ $map_layer ] ) ? $map_layers[ $map_layer ] : $map_layers['precipitation_new'];

// Build tile URL.
$tile_url = add_query_arg( array(
    'appid' => $api_key,
    'z'     => '{z}',
    'x'     => '{x}',
    'y'     => '{y}',
), "https://tile.openweathermap.org/map/{$map_layer}/{{z}}/{{x}}/{{y}}.png" );

// Attribution URL.
$attribution_url = 'https://openweathermap.org/';

// CSS classes.
$wrapper_classes = array( 'pw-owm-map-wrapper' );

if ( ! empty( $attributes['mapCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['mapCustomClass'] );
}

// Inline styles.
$inline_styles = array(
    '--pw-map-height: ' . $map_height . 'px',
    '--pw-map-width: ' . ( is_numeric( $map_width ) ? $map_width . 'px' : $map_width ),
);

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     style="<?php echo esc_attr( implode( '; ', $inline_styles ) ); ?>"
     data-lat="<?php echo esc_attr( $latitude ); ?>"
     data-lon="<?php echo esc_attr( $longitude ); ?>"
     data-zoom="<?php echo esc_attr( $zoom_level ); ?>"
     data-layer="<?php echo esc_attr( $map_layer ); ?>"
     data-opacity="<?php echo esc_attr( $map_opacity ); ?>">
    
    <!-- Map Header -->
    <div class="pw-map-header">
        <div class="pw-map-title">
            <span class="pw-map-icon"><?php echo esc_html( $current_layer['icon'] ); ?></span>
            <span class="pw-map-label"><?php echo esc_html( $current_layer['label'] ); ?> <?php esc_html_e( 'Map', 'pearl-weather' ); ?></span>
        </div>
        
        <!-- Layer Switcher -->
        <div class="pw-layer-switcher">
            <?php foreach ( $map_layers as $layer_key => $layer ) : ?>
                <button class="pw-layer-btn <?php echo $layer_key === $map_layer ? 'pw-active' : ''; ?>"
                        data-layer="<?php echo esc_attr( $layer_key ); ?>"
                        title="<?php echo esc_attr( $layer['label'] ); ?>">
                    <span class="pw-layer-icon"><?php echo esc_html( $layer['icon'] ); ?></span>
                    <span class="pw-layer-label"><?php echo esc_html( $layer['label'] ); ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Map Container -->
    <div id="pw-owm-map-<?php echo esc_attr( $unique_id ?? uniqid() ); ?>" 
         class="pw-owm-map-container"
         style="height: var(--pw-map-height); width: 100%;">
        
        <!-- Loading Placeholder -->
        <div class="pw-map-loading">
            <div class="pw-loading-spinner"></div>
            <span><?php esc_html_e( 'Loading map...', 'pearl-weather' ); ?></span>
        </div>
        
    </div>
    
    <!-- Map Controls -->
    <div class="pw-map-controls">
        <button class="pw-zoom-in" title="<?php esc_attr_e( 'Zoom In', 'pearl-weather' ); ?>">+</button>
        <button class="pw-zoom-out" title="<?php esc_attr_e( 'Zoom Out', 'pearl-weather' ); ?>">-</button>
        <button class="pw-reset-view" title="<?php esc_attr_e( 'Reset View', 'pearl-weather' ); ?>">⟳</button>
        <input type="range" class="pw-opacity-slider" min="0" max="100" value="<?php echo esc_attr( $map_opacity ); ?>" title="<?php esc_attr_e( 'Layer Opacity', 'pearl-weather' ); ?>">
    </div>
    
    <!-- Attribution -->
    <div class="pw-map-attribution">
        <a href="<?php echo esc_url( $attribution_url ); ?>" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e( 'Weather data by OpenWeatherMap', 'pearl-weather' ); ?>
        </a>
    </div>
    
</div>

<style>
/* OpenWeatherMap Styles */
.pw-owm-map-wrapper {
    position: relative;
    background: #1a1a2e;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Map Header */
.pw-map-header {
    position: absolute;
    top: 12px;
    left: 12px;
    right: 12px;
    z-index: 10;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    padding: 8px 16px;
    border-radius: 40px;
}

.pw-map-title {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
}

.pw-map-icon {
    font-size: 18px;
}

/* Layer Switcher */
.pw-layer-switcher {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.pw-layer-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    padding: 6px 12px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}

.pw-layer-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.pw-layer-btn.pw-active {
    background: var(--pw-primary-color, #f26c0d);
    color: #fff;
}

/* Map Container */
.pw-owm-map-container {
    position: relative;
    min-height: 300px;
    background: #1a1a2e;
}

/* Leaflet Map Styles */
.pw-owm-map-container .leaflet-container {
    background: #1a1a2e;
}

/* Map Controls */
.pw-map-controls {
    position: absolute;
    bottom: 12px;
    right: 12px;
    z-index: 10;
    display: flex;
    flex-direction: column;
    gap: 6px;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    padding: 8px;
    border-radius: 40px;
}

.pw-map-controls button {
    width: 36px;
    height: 36px;
    background: #fff;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pw-map-controls button:hover {
    background: var(--pw-primary-color, #f26c0d);
    color: #fff;
}

.pw-opacity-slider {
    width: 100px;
    margin: 4px 0;
    cursor: pointer;
}

/* Loading State */
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
    background: rgba(26, 26, 46, 0.9);
    color: #fff;
    z-index: 5;
}

.pw-loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top-color: var(--pw-primary-color, #f26c0d);
    border-radius: 50%;
    animation: pw-spin 0.8s linear infinite;
    margin-bottom: 12px;
}

@keyframes pw-spin {
    to { transform: rotate(360deg); }
}

/* Attribution */
.pw-map-attribution {
    position: absolute;
    bottom: 8px;
    left: 8px;
    z-index: 10;
    font-size: 10px;
    background: rgba(0, 0, 0, 0.5);
    padding: 4px 8px;
    border-radius: 4px;
}

.pw-map-attribution a {
    color: #fff;
    text-decoration: none;
}

.pw-map-attribution a:hover {
    text-decoration: underline;
}

/* Error State */
.pw-map-error {
    padding: 40px;
    text-align: center;
    background: #f8f9fa;
    border-radius: 12px;
    color: #dc3545;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-map-header {
        top: 8px;
        left: 8px;
        right: 8px;
        flex-direction: column;
        align-items: flex-start;
        border-radius: 16px;
    }
    
    .pw-layer-switcher {
        width: 100%;
        justify-content: flex-start;
        overflow-x: auto;
        padding-bottom: 4px;
    }
    
    .pw-map-controls {
        bottom: 8px;
        right: 8px;
    }
    
    .pw-map-controls button {
        width: 30px;
        height: 30px;
        font-size: 14px;
    }
    
    .pw-opacity-slider {
        width: 80px;
    }
}
</style>

<script>
/**
 * OpenWeatherMap Tile Layer Map Initialization
 * Uses Leaflet.js for interactive map display
 */
(function() {
    const mapWrapper = document.querySelector('.pw-owm-map-wrapper');
    if (!mapWrapper) return;
    
    const containerId = mapWrapper.querySelector('.pw-owm-map-container')?.id;
    if (!containerId) return;
    
    const lat = parseFloat(mapWrapper.dataset.lat) || 51.509865;
    const lon = parseFloat(mapWrapper.dataset.lon) || -0.118092;
    const zoom = parseInt(mapWrapper.dataset.zoom) || 8;
    const defaultLayer = mapWrapper.dataset.layer || 'precipitation_new';
    const defaultOpacity = parseInt(mapWrapper.dataset.opacity) || 50;
    
    let map;
    let currentLayer;
    
    // Load Leaflet CSS and JS dynamically
    function loadLeaflet() {
        return new Promise((resolve, reject) => {
            if (typeof L !== 'undefined') {
                resolve();
                return;
            }
            
            // Load CSS
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(link);
            
            // Load JS
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Failed to load Leaflet'));
            document.head.appendChild(script);
        });
    }
    
    // Get tile URL for layer
    function getTileUrl(layer, opacity) {
        const apiKey = '<?php echo esc_js( $api_key ); ?>';
        return `https://tile.openweathermap.org/map/${layer}/{z}/{x}/{y}.png?appid=${apiKey}`;
    }
    
    // Initialize map
    async function initMap() {
        try {
            await loadLeaflet();
            
            // Remove loading placeholder
            const loadingEl = mapWrapper.querySelector('.pw-map-loading');
            if (loadingEl) loadingEl.remove();
            
            // Initialize map
            map = L.map(containerId).setView([lat, lon], zoom);
            
            // Add base tile layer (OpenStreetMap for reference)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19,
                minZoom: 3
            }).addTo(map);
            
            // Add weather layer
            const tileUrl = getTileUrl(defaultLayer, defaultOpacity);
            currentLayer = L.tileLayer(tileUrl, {
                opacity: defaultOpacity / 100,
                attribution: 'Weather data by <a href="https://openweathermap.org/">OpenWeatherMap</a>'
            }).addTo(map);
            
            // Hide loading
            const loadingContainer = mapWrapper.querySelector('.pw-map-loading');
            if (loadingContainer) loadingContainer.style.display = 'none';
            
        } catch (error) {
            console.error('Map initialization failed:', error);
            const loadingEl = mapWrapper.querySelector('.pw-map-loading');
            if (loadingEl) {
                loadingEl.innerHTML = '<span>Failed to load map. Please check your connection.</span>';
            }
        }
    }
    
    // Layer switcher functionality
    const layerBtns = mapWrapper.querySelectorAll('.pw-layer-btn');
    layerBtns.forEach(btn => {
        btn.addEventListener('click', async () => {
            const layer = btn.dataset.layer;
            if (!layer || !map) return;
            
            // Update active state
            layerBtns.forEach(b => b.classList.remove('pw-active'));
            btn.classList.add('pw-active');
            
            // Update map title
            const mapTitle = mapWrapper.querySelector('.pw-map-label');
            const mapIcon = mapWrapper.querySelector('.pw-map-icon');
            if (mapIcon) mapIcon.textContent = btn.querySelector('.pw-layer-icon')?.textContent || '';
            if (mapTitle) mapTitle.textContent = btn.querySelector('.pw-layer-label')?.textContent + ' Map';
            
            // Remove old layer and add new one
            if (currentLayer) map.removeLayer(currentLayer);
            
            const tileUrl = getTileUrl(layer, defaultOpacity);
            currentLayer = L.tileLayer(tileUrl, {
                opacity: defaultOpacity / 100,
                attribution: 'Weather data by <a href="https://openweathermap.org/">OpenWeatherMap</a>'
            }).addTo(map);
        });
    });
    
    // Zoom controls
    const zoomInBtn = mapWrapper.querySelector('.pw-zoom-in');
    const zoomOutBtn = mapWrapper.querySelector('.pw-zoom-out');
    const resetBtn = mapWrapper.querySelector('.pw-reset-view');
    
    if (zoomInBtn) zoomInBtn.addEventListener('click', () => map?.zoomIn());
    if (zoomOutBtn) zoomOutBtn.addEventListener('click', () => map?.zoomOut());
    if (resetBtn) resetBtn.addEventListener('click', () => map?.setView([lat, lon], zoom));
    
    // Opacity slider
    const opacitySlider = mapWrapper.querySelector('.pw-opacity-slider');
    if (opacitySlider) {
        opacitySlider.addEventListener('input', (e) => {
            const opacity = parseInt(e.target.value) / 100;
            if (currentLayer) currentLayer.setOpacity(opacity);
        });
    }
    
    // Initialize map
    initMap();
})();
</script>