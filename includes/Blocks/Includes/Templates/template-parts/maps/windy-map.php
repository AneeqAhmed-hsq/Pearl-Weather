<?php
/**
 * Windy.com Weather Map Template
 *
 * Renders an embedded Windy.com interactive weather map with
 * configurable overlays, units, and display options.
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
 * - $unique_id: Unique block identifier
 * - $weather_data: Current weather data array
 */

// Get coordinates from attributes.
$coordinates = isset( $attributes['getDataByCoordinates'] ) 
    ? sanitize_text_field( $attributes['getDataByCoordinates'] ) 
    : '51.509865,-0.118092';

$coords_array = explode( ',', $coordinates );
$latitude = isset( $coords_array[0] ) ? (float) trim( $coords_array[0] ) : 51.509865;
$longitude = isset( $coords_array[1] ) ? (float) trim( $coords_array[1] ) : -0.118092;

// Map settings.
$zoom_level = isset( $attributes['mapZoomLevel'] ) ? (int) $attributes['mapZoomLevel'] : 5;
$overlay = isset( $attributes['defaultDataLayerSelection'] ) ? sanitize_text_field( $attributes['defaultDataLayerSelection'] ) : 'wind';
$show_marker = isset( $attributes['showMarker'] ) ? (bool) $attributes['showMarker'] : true;
$show_pressure_lines = isset( $attributes['airflowPressureLines'] ) ? (bool) $attributes['airflowPressureLines'] : false;
$spot_forecast = isset( $attributes['spotForecast'] ) ? (bool) $attributes['spotForecast'] : true;
$forecast_model = isset( $attributes['forecastModel'] ) ? sanitize_text_field( $attributes['forecastModel'] ) : 'ecmwf';
$forecast_from = isset( $attributes['forecastFrom'] ) ? sanitize_text_field( $attributes['forecastFrom'] ) : 'now';
$default_elevation = isset( $attributes['defaultElevation'] ) ? sanitize_text_field( $attributes['defaultElevation'] ) : 'surface';
$show_attribution = isset( $attributes['weatherAttribution'] ) ? (bool) $attributes['weatherAttribution'] : false;

// Unit settings.
$temperature_unit = isset( $attributes['displayTemperatureUnit'] ) ? sanitize_text_field( $attributes['displayTemperatureUnit'] ) : 'metric';
$wind_unit = isset( $attributes['displayWindSpeedUnit'] ) ? sanitize_text_field( $attributes['displayWindSpeedUnit'] ) : 'kmh';
$precipitation_unit = isset( $attributes['displayPrecipitationUnit'] ) ? sanitize_text_field( $attributes['displayPrecipitationUnit'] ) : 'mm';

// Convert units for Windy API.
$metric_temp = 'imperial' === $temperature_unit ? '°F' : '°C';
$metric_wind = $this->convert_wind_unit_for_windy( $wind_unit );
$metric_rain = 'inch' === $precipitation_unit ? 'in' : 'mm';
$metric_snow = 'inch' === $precipitation_unit ? 'in' : 'mm';

// Map dimensions.
$map_height = isset( $attributes['weatherMapMaxHeight']['device']['Desktop'] ) 
    ? (int) $attributes['weatherMapMaxHeight']['device']['Desktop'] 
    : 500;
$map_width = isset( $attributes['weatherMapMaxWidth']['device']['Desktop'] ) 
    ? (int) $attributes['weatherMapMaxWidth']['device']['Desktop'] 
    : '100%';

// Build query parameters.
$query_params = array(
    'type'       => 'map',
    'location'   => 'coordinates',
    'lat'        => $latitude,
    'lon'        => $longitude,
    'detailLat'  => $latitude,
    'detailLon'  => $longitude,
    'zoom'       => $zoom_level,
    'overlay'    => $overlay,
    'marker'     => $show_marker ? 'true' : 'false',
    'pressure'   => $show_pressure_lines ? 'true' : '',
    'detail'     => $spot_forecast ? 'true' : 'false',
    'product'    => $forecast_model,
    'calendar'   => $forecast_from,
    'level'      => $default_elevation,
    'message'    => $show_attribution ? 'true' : 'false',
    'radarRange' => '-1',
    'metricTemp' => $metric_temp,
    'metricWind' => $metric_wind,
    'metricRain' => $metric_rain,
    'metricSnow' => $metric_snow,
);

// Remove empty parameters.
$query_params = array_filter( $query_params, function( $value ) {
    return null !== $value && '' !== $value;
} );

// Build iframe URL.
$iframe_src = 'https://embed.windy.com/embed.html?' . http_build_query( $query_params );

// CSS classes.
$wrapper_classes = array( 'pw-windy-map-wrapper' );

if ( ! empty( $attributes['windyMapCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['windyMapCustomClass'] );
}

// Inline styles.
$inline_styles = array(
    '--pw-map-height: ' . ( is_numeric( $map_height ) ? $map_height . 'px' : $map_height ),
    '--pw-map-width: ' . ( is_numeric( $map_width ) ? $map_width . 'px' : $map_width ),
);

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     style="<?php echo esc_attr( implode( '; ', $inline_styles ) ); ?>"
     data-lat="<?php echo esc_attr( $latitude ); ?>"
     data-lon="<?php echo esc_attr( $longitude ); ?>"
     data-zoom="<?php echo esc_attr( $zoom_level ); ?>"
     data-overlay="<?php echo esc_attr( $overlay ); ?>">
    
    <!-- Map Container -->
    <div class="pw-windy-map-container">
        
        <!-- Loading Placeholder -->
        <div class="pw-map-loading">
            <div class="pw-loading-spinner"></div>
            <span><?php esc_html_e( 'Loading Windy Map...', 'pearl-weather' ); ?></span>
        </div>
        
        <!-- Windy iframe -->
        <iframe class="pw-windy-iframe"
                title="<?php esc_attr_e( 'Windy Weather Map', 'pearl-weather' ); ?>"
                src="<?php echo esc_url( $iframe_src ); ?>"
                allow="geolocation"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
        </iframe>
        
    </div>
    
    <!-- Map Controls Overlay (Optional) -->
    <?php if ( isset( $attributes['showMapControls'] ) && $attributes['showMapControls'] ) : ?>
        <div class="pw-map-controls-overlay">
            <div class="pw-map-controls-group">
                <button class="pw-map-control pw-map-refresh" title="<?php esc_attr_e( 'Refresh Map', 'pearl-weather' ); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M23 4V10H17M1 20V14H7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M3.51 9C4.017 6.632 5.278 4.534 7.083 3.082C8.889 1.63 11.146 0.9 13.458 1.011C15.77 1.122 17.95 2.069 19.623 3.682L23 7M1 17L4.377 20.318C6.05 21.931 8.23 22.878 10.542 22.989C12.854 23.1 15.111 22.37 16.917 20.918C18.722 19.466 19.983 17.368 20.49 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Attribution (Required by Windy) -->
    <div class="pw-map-attribution">
        <a href="https://www.windy.com/?<?php echo esc_attr( http_build_query( array( 'lat' => $latitude, 'lon' => $longitude, 'zoom' => $zoom_level ) ) ); ?>" 
           target="_blank" 
           rel="noopener noreferrer nofollow">
            <?php esc_html_e( 'Weather map by Windy.com', 'pearl-weather' ); ?>
        </a>
    </div>
    
</div>

<style>
/* Windy Map Styles */
.pw-windy-map-wrapper {
    position: relative;
    width: 100%;
    margin: 16px 0;
    overflow: hidden;
    border-radius: 8px;
}

.pw-windy-map-container {
    position: relative;
    width: 100%;
    height: var(--pw-map-height, 400px);
    background: #1a1a2e;
    border-radius: inherit;
    overflow: hidden;
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
    z-index: 10;
    transition: opacity 0.3s ease;
}

.pw-map-loading.hide {
    opacity: 0;
    pointer-events: none;
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

/* Windy Iframe */
.pw-windy-iframe {
    width: 100%;
    height: 100%;
    border: none;
    display: block;
}

/* Map Controls Overlay */
.pw-map-controls-overlay {
    position: absolute;
    bottom: 16px;
    right: 16px;
    z-index: 5;
}

.pw-map-controls-group {
    display: flex;
    gap: 8px;
}

.pw-map-control {
    width: 36px;
    height: 36px;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.pw-map-control:hover {
    background: var(--pw-primary-color, #f26c0d);
    border-color: var(--pw-primary-color, #f26c0d);
}

/* Map Attribution */
.pw-map-attribution {
    position: absolute;
    bottom: 8px;
    left: 8px;
    font-size: 10px;
    background: rgba(0, 0, 0, 0.5);
    padding: 4px 8px;
    border-radius: 4px;
    z-index: 5;
}

.pw-map-attribution a {
    color: #fff;
    text-decoration: none;
}

.pw-map-attribution a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-windy-map-container {
        height: calc(var(--pw-map-height, 400px) * 0.7);
    }
    
    .pw-map-controls-overlay {
        bottom: 8px;
        right: 8px;
    }
    
    .pw-map-control {
        width: 30px;
        height: 30px;
    }
    
    .pw-map-attribution {
        font-size: 8px;
    }
}
</style>

<script>
/**
 * Windy Map Loader - Handles iframe loading and removes placeholder.
 */
(function() {
    const windyWrapper = document.querySelector('.pw-windy-map-wrapper');
    if (!windyWrapper) return;
    
    const iframe = windyWrapper.querySelector('.pw-windy-iframe');
    const loadingEl = windyWrapper.querySelector('.pw-map-loading');
    
    if (iframe && loadingEl) {
        // Hide loading when iframe loads
        iframe.addEventListener('load', function() {
            loadingEl.classList.add('hide');
            setTimeout(() => {
                if (loadingEl.parentNode) {
                    loadingEl.remove();
                }
            }, 300);
        });
        
        // Fallback timeout (10 seconds)
        setTimeout(() => {
            if (loadingEl && loadingEl.parentNode) {
                loadingEl.classList.add('hide');
                setTimeout(() => {
                    if (loadingEl.parentNode) {
                        loadingEl.remove();
                    }
                }, 300);
            }
        }, 10000);
    }
    
    // Refresh button functionality
    const refreshBtn = windyWrapper.querySelector('.pw-map-refresh');
    if (refreshBtn && iframe) {
        refreshBtn.addEventListener('click', () => {
            const src = iframe.src;
            iframe.src = '';
            iframe.src = src;
        });
    }
})();
</script>

<?php
/**
 * Helper function to convert wind unit for Windy API.
 */
if ( ! function_exists( 'convert_wind_unit_for_windy' ) ) {
    /**
     * Convert wind speed unit to Windy API format.
     *
     * @param string $unit Wind speed unit.
     * @return string
     */
    function convert_wind_unit_for_windy( $unit ) {
        $unit_map = array(
            'ms'   => 'm/s',
            'm/s'  => 'm/s',
            'kmh'  => 'km/h',
            'km/h' => 'km/h',
            'mph'  => 'mph',
            'kt'   => 'kt',
        );
        
        return isset( $unit_map[ $unit ] ) ? $unit_map[ $unit ] : 'km/h';
    }
}