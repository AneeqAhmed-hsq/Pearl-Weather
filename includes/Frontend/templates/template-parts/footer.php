<?php
/**
 * Weather Footer Template
 *
 * Displays footer content including detailed weather link,
 * last updated time, and attribution.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Templates/Parts
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template variables:
 * - $weather_data: Current weather data array
 * - $api_source: API source ('openweather_api' or 'weather_api')
 * - $appid: API key (for checking if attribution should show)
 * - $show_weather_detailed: Show detailed weather link
 * - $show_weather_updated_time: Show last updated time
 * - $show_weather_attr: Show attribution
 * - $splw_meta: Widget meta settings
 */

// Check if any footer content should be displayed.
$show_any_footer = ( isset( $show_weather_detailed ) && $show_weather_detailed ) ||
                   ( isset( $show_weather_updated_time ) && $show_weather_updated_time ) ||
                   ( isset( $show_weather_attr ) && $show_weather_attr && ! empty( $appid ) );

if ( ! $show_any_footer ) {
    return;
}

// Get API source.
$api_source_key = isset( $api_source ) ? $api_source : 'openweather_api';

// Attribution URLs and labels.
$attribution_data = array(
    'openweather_api' => array(
        'url'   => 'https://openweathermap.org/',
        'label' => __( 'OpenWeatherMap', 'pearl-weather' ),
        'text'  => __( 'Weather from OpenWeatherMap', 'pearl-weather' ),
    ),
    'weather_api' => array(
        'url'   => 'https://www.weatherapi.com/',
        'label' => __( 'WeatherAPI', 'pearl-weather' ),
        'text'  => __( 'Weather from WeatherAPI', 'pearl-weather' ),
    ),
);

$attribution = isset( $attribution_data[ $api_source_key ] ) 
    ? $attribution_data[ $api_source_key ] 
    : $attribution_data['openweather_api'];

// Detailed weather link.
$show_detailed_link = isset( $show_weather_detailed ) ? (bool) $show_weather_detailed : false;
$city_name = isset( $weather_data['city'] ) ? $weather_data['city'] : '';
$city_id = isset( $weather_data['city_id'] ) ? $weather_data['city_id'] : '';

if ( 'weather_api' === $api_source_key && ! empty( $city_name ) ) {
    $details_url = 'https://www.weatherapi.com/weather/q/' . sanitize_title( $city_name );
} else {
    $details_url = 'https://openweathermap.org/city/' . $city_id;
}

// Last updated time.
$show_updated_time = isset( $show_weather_updated_time ) ? (bool) $show_weather_updated_time : false;
$updated_time = isset( $weather_data['updated_time'] ) ? $weather_data['updated_time'] : '';

// Attribution.
$show_attribution = isset( $show_weather_attr ) ? (bool) $show_weather_attr : false;
$open_weather_link = isset( $splw_meta['lw-openweather-links'] ) ? (bool) $splw_meta['lw-openweather-links'] : false;

// CSS classes.
$wrapper_classes = array( 'pw-weather-footer' );

if ( ! empty( $splw_meta['footer_custom_class'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $splw_meta['footer_custom_class'] );
}

$layout = isset( $splw_meta['footer_layout'] ) ? sanitize_text_field( $splw_meta['footer_layout'] ) : 'inline';

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" data-layout="<?php echo esc_attr( $layout ); ?>">
    
    <!-- Detailed Weather & Last Updated Section -->
    <?php if ( $show_detailed_link || $show_updated_time ) : ?>
        <div class="pw-footer-info">
            
            <?php if ( $show_detailed_link && ! empty( $details_url ) ) : ?>
                <div class="pw-detailed-weather">
                    <a href="<?php echo esc_url( $details_url ); ?>" 
                       class="pw-detailed-link"
                       target="_blank" 
                       rel="noopener noreferrer nofollow">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 10C18 6.686 15.314 4 12 4C8.686 4 6 6.686 6 10C6 10.548 6.07 11.08 6.2 11.59" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M17 16C19.209 16 21 14.209 21 12C21 9.791 19.209 8 17 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M7 16C4.791 16 3 14.209 3 12C3 9.791 4.791 8 7 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <?php esc_html_e( 'Detailed weather', 'pearl-weather' ); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <?php if ( $show_updated_time && ! empty( $updated_time ) ) : ?>
                <div class="pw-last-updated">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span class="pw-updated-label"><?php esc_html_e( 'Last updated:', 'pearl-weather' ); ?></span>
                    <span class="pw-updated-time"><?php echo esc_html( $updated_time ); ?></span>
                </div>
            <?php endif; ?>
            
        </div>
    <?php endif; ?>
    
    <!-- Attribution Section -->
    <?php if ( $show_attribution && ! empty( $appid ) ) : ?>
        <div class="pw-attribution">
            <?php if ( $open_weather_link ) : ?>
                <a href="<?php echo esc_url( $attribution['url'] ); ?>" 
                   class="pw-attribution-link"
                   target="_blank" 
                   rel="noopener noreferrer nofollow">
                    <?php echo esc_html( $attribution['text'] ); ?>
                </a>
            <?php else : ?>
                <span class="pw-attribution-text"><?php echo esc_html( $attribution['text'] ); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Footer Styles */
.pw-weather-footer {
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    font-size: 11px;
    color: #757575;
}

/* Info Section */
.pw-footer-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 8px;
}

/* Detailed Link */
.pw-detailed-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #757575;
    text-decoration: none;
    transition: color 0.2s ease;
}

.pw-detailed-link:hover {
    color: var(--pw-primary-color, #f26c0d);
    text-decoration: underline;
}

/* Last Updated */
.pw-last-updated {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Attribution */
.pw-attribution {
    text-align: center;
    margin-top: 4px;
}

.pw-attribution-link,
.pw-attribution-text {
    color: #999;
    text-decoration: none;
}

.pw-attribution-link:hover {
    color: var(--pw-primary-color, #f26c0d);
    text-decoration: underline;
}

/* Layout Variants */
[data-layout="inline"] .pw-footer-info {
    justify-content: space-between;
}

[data-layout="stacked"] .pw-footer-info {
    flex-direction: column;
    text-align: center;
}

[data-layout="stacked"] .pw-detailed-weather,
[data-layout="stacked"] .pw-last-updated {
    width: 100%;
    justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-footer-info {
        flex-direction: column;
        text-align: center;
    }
    
    .pw-detailed-weather,
    .pw-last-updated {
        justify-content: center;
    }
}
</style>