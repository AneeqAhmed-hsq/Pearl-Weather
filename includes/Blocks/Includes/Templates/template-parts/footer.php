<?php
/**
 * Weather Card Footer Template Part
 *
 * Displays footer content including weather attribution,
 * data source links, last update time, and optional custom text.
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
 * - $api_source: API source ('openweather_api' or 'weather_api')
 */

// Display settings.
$show_attribution = isset( $attributes['displayWeatherAttribution'] ) ? (bool) $attributes['displayWeatherAttribution'] : true;

// Exit if attribution is disabled.
if ( ! $show_attribution ) {
    return;
}

$show_link = isset( $attributes['displayLinkToOpenWeatherMap'] ) ? (bool) $attributes['displayLinkToOpenWeatherMap'] : false;
$show_last_update = isset( $attributes['displayDateUpdateTime'] ) ? (bool) $attributes['displayDateUpdateTime'] : false;
$show_detailed_link = isset( $attributes['displayDetailedWeatherLink'] ) ? (bool) $attributes['displayDetailedWeatherLink'] : false;
$show_separator = isset( $attributes['showFooterSeparator'] ) ? (bool) $attributes['showFooterSeparator'] : true;

// Custom footer text.
$custom_footer_text = isset( $attributes['customFooterText'] ) ? sanitize_text_field( $attributes['customFooterText'] ) : '';
$custom_footer_link = isset( $attributes['customFooterLink'] ) ? esc_url_raw( $attributes['customFooterLink'] ) : '';

// Get weather data.
$city_id = isset( $weather_data['city_id'] ) ? $weather_data['city_id'] : '';
$city_name = isset( $weather_data['city'] ) ? $weather_data['city'] : '';
$updated_time = isset( $weather_data['updated_time'] ) ? $weather_data['updated_time'] : '';

// API source attribution data.
$attribution_data = array(
    'openweather_api' => array(
        'name' => 'OpenWeatherMap',
        'text' => __( 'Weather data by OpenWeatherMap', 'pearl-weather' ),
        'url'  => 'https://openweathermap.org/',
        'attribution_url' => 'https://openweathermap.org/attribution',
    ),
    'weather_api' => array(
        'name' => 'WeatherAPI',
        'text' => __( 'Weather data by WeatherAPI', 'pearl-weather' ),
        'url'  => 'https://www.weatherapi.com/',
        'attribution_url' => 'https://www.weatherapi.com/terms.aspx',
    ),
);

// Get attribution for current API source.
$api_source_key = isset( $api_source ) ? $api_source : 'openweather_api';
$attribution = isset( $attribution_data[ $api_source_key ] ) ? $attribution_data[ $api_source_key ] : $attribution_data['openweather_api'];

// Use custom text if provided.
$footer_text = ! empty( $custom_footer_text ) ? $custom_footer_text : $attribution['text'];
$footer_url = ! empty( $custom_footer_link ) ? $custom_footer_link : $attribution['url'];

// Additional CSS classes.
$wrapper_classes = array( 'pw-weather-footer' );

if ( ! empty( $attributes['footerCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['footerCustomClass'] );
}

$layout = isset( $attributes['footerLayout'] ) ? sanitize_text_field( $attributes['footerLayout'] ) : 'inline';

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" data-layout="<?php echo esc_attr( $layout ); ?>">
    
    <!-- Separator Line (optional) -->
    <?php if ( $show_separator ) : ?>
        <div class="pw-footer-separator"></div>
    <?php endif; ?>
    
    <div class="pw-footer-content">
        
        <!-- Left Column: Attribution -->
        <div class="pw-footer-attribution">
            <?php if ( $show_link && ! empty( $footer_url ) ) : ?>
                <a href="<?php echo esc_url( $footer_url ); ?>" 
                   class="pw-attribution-link"
                   target="_blank" 
                   rel="noopener noreferrer nofollow"
                   aria-label="<?php esc_attr_e( 'Weather data source attribution', 'pearl-weather' ); ?>">
                    
                    <?php if ( isset( $attributes['showAttributionIcon'] ) && $attributes['showAttributionIcon'] ) : ?>
                        <span class="pw-attribution-icon">
                            <?php if ( 'openweather_api' === $api_source_key ) : ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                    <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                </svg>
                            <?php else : ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 12h18M12 3v18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                    
                    <span class="pw-attribution-text"><?php echo esc_html( $footer_text ); ?></span>
                </a>
            <?php else : ?>
                <span class="pw-attribution-text"><?php echo esc_html( $footer_text ); ?></span>
            <?php endif; ?>
            
            <!-- Additional Attribution Link (e.g., for legal requirements) -->
            <?php if ( isset( $attributes['showLegalAttribution'] ) && $attributes['showLegalAttribution'] && ! empty( $attribution['attribution_url'] ) ) : ?>
                <a href="<?php echo esc_url( $attribution['attribution_url'] ); ?>" 
                   class="pw-legal-link"
                   target="_blank" 
                   rel="noopener noreferrer nofollow">
                    <?php esc_html_e( 'Attribution', 'pearl-weather' ); ?>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Center Column: Last Update Time -->
        <?php if ( $show_last_update && ! empty( $updated_time ) ) : ?>
            <div class="pw-footer-update">
                <span class="pw-update-label"><?php esc_html_e( 'Last updated:', 'pearl-weather' ); ?></span>
                <time class="pw-update-time" datetime="<?php echo esc_attr( date( 'Y-m-d H:i:s', strtotime( $updated_time ) ) ); ?>">
                    <?php echo esc_html( $updated_time ); ?>
                </time>
            </div>
        <?php endif; ?>
        
        <!-- Right Column: Detailed Weather Link -->
        <?php if ( $show_detailed_link && ! empty( $city_id ) ) : ?>
            <div class="pw-footer-detailed-link">
                <a href="<?php echo esc_url( 'https://openweathermap.org/city/' . $city_id ); ?>" 
                   class="pw-detailed-link"
                   target="_blank" 
                   rel="noopener noreferrer">
                    <?php esc_html_e( 'Detailed Forecast', 'pearl-weather' ); ?>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        <?php endif; ?>
        
    </div>
    
</div>

<style>
/* Weather Footer Styles */
.pw-weather-footer {
    margin-top: 16px;
    padding-top: 12px;
    font-size: 11px;
    color: #757575;
}

/* Separator */
.pw-footer-separator {
    height: 1px;
    background: rgba(0, 0, 0, 0.08);
    margin-bottom: 12px;
}

/* Footer Content Layout */
.pw-footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

/* Layout variants */
[data-layout="inline"] .pw-footer-content {
    flex-direction: row;
}

[data-layout="stacked"] .pw-footer-content {
    flex-direction: column;
    text-align: center;
}

[data-layout="stacked"] .pw-footer-attribution,
[data-layout="stacked"] .pw-footer-update,
[data-layout="stacked"] .pw-footer-detailed-link {
    width: 100%;
    text-align: center;
}

/* Attribution Styles */
.pw-footer-attribution {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.pw-attribution-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #757575;
    text-decoration: none;
    transition: color 0.2s ease;
}

.pw-attribution-link:hover {
    color: var(--pw-primary-color, #f26c0d);
    text-decoration: underline;
}

.pw-attribution-icon {
    display: inline-flex;
    align-items: center;
}

.pw-legal-link {
    font-size: 10px;
    color: #999;
    text-decoration: none;
}

.pw-legal-link:hover {
    text-decoration: underline;
}

/* Last Update Styles */
.pw-footer-update {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
}

.pw-update-label {
    opacity: 0.7;
}

.pw-update-time {
    font-weight: 500;
}

/* Detailed Link Styles */
.pw-detailed-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--pw-primary-color, #f26c0d);
    text-decoration: none;
    font-size: 11px;
    font-weight: 500;
    transition: opacity 0.2s ease;
}

.pw-detailed-link:hover {
    text-decoration: underline;
    opacity: 0.8;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-footer-content {
        flex-direction: column;
        text-align: center;
    }
    
    .pw-footer-attribution {
        justify-content: center;
    }
    
    .pw-footer-update {
        justify-content: center;
    }
}
</style>

<?php
/**
 * Additional helper for generating attribution badges.
 * This can be extended for different API providers.
 */

if ( ! function_exists( 'get_attribution_badge' ) ) {
    /**
     * Get attribution badge HTML.
     *
     * @param string $api_source API source.
     * @return string
     */
    function get_attribution_badge( $api_source ) {
        $badges = array(
            'openweather_api' => '<span class="pw-badge pw-badge-owm">OpenWeather</span>',
            'weather_api'     => '<span class="pw-badge pw-badge-weatherapi">WeatherAPI</span>',
        );
        
        return isset( $badges[ $api_source ] ) ? $badges[ $api_source ] : '';
    }
}

/**
 * JavaScript for dynamic last update (optional).
 */
if ( isset( $attributes['enableLiveLastUpdate'] ) && $attributes['enableLiveLastUpdate'] && ! empty( $updated_time ) ) :
?>
<script>
(function() {
    const updateTimeElement = document.querySelector('.pw-update-time');
    if (!updateTimeElement) return;
    
    const updateTimestamp = new Date(updateTimeElement.getAttribute('datetime'));
    
    function updateLastUpdated() {
        const now = new Date();
        const diffMs = now - updateTimestamp;
        const diffMins = Math.floor(diffMs / 60000);
        
        let text = '';
        if (diffMins < 1) {
            text = '<?php esc_html_e( 'Just now', 'pearl-weather' ); ?>';
        } else if (diffMins < 60) {
            text = diffMins + ' <?php esc_html_e( 'minutes ago', 'pearl-weather' ); ?>';
        } else if (diffMins < 1440) {
            const hours = Math.floor(diffMins / 60);
            text = hours + ' <?php esc_html_e( 'hours ago', 'pearl-weather' ); ?>';
        } else {
            const days = Math.floor(diffMins / 1440);
            text = days + ' <?php esc_html_e( 'days ago', 'pearl-weather' ); ?>';
        }
        
        updateTimeElement.textContent = text;
    }
    
    // Update every minute
    setInterval(updateLastUpdated, 60000);
})();
</script>
<?php endif;