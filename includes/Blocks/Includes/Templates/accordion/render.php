<?php
/**
 * Weather Block Accordion Template
 *
 * Renders weather data in an accordion-style layout with
 * expandable/collapsible sections for different weather details.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Templates
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
 * - $forecast_data: Hourly forecast data array
 * - $aqi_data: Air quality data array (if available)
 */

// Default values.
$unique_id = isset( $attributes['uniqueId'] ) ? esc_attr( $attributes['uniqueId'] ) : 'pw-accordion-' . uniqid();
$show_location = isset( $attributes['showLocationName'] ) ? (bool) $attributes['showLocationName'] : true;
$show_date = isset( $attributes['showCurrentDate'] ) ? (bool) $attributes['showCurrentDate'] : true;
$show_time = isset( $attributes['showCurrentTime'] ) ? (bool) $attributes['showCurrentTime'] : true;
$show_temperature = isset( $attributes['displayTemperature'] ) ? (bool) $attributes['displayTemperature'] : true;
$show_description = isset( $attributes['displayWeatherConditions'] ) ? (bool) $attributes['displayWeatherConditions'] : true;
$show_forecast = isset( $attributes['displayWeatherForecastData'] ) ? (bool) $attributes['displayForecastData'] : true;
$show_additional = isset( $attributes['displayAdditionalData'] ) ? (bool) $attributes['displayAdditionalData'] : true;
$show_aqi = isset( $attributes['showAirQuality'] ) ? (bool) $attributes['showAirQuality'] : false;

// Open first accordion item by default.
$default_open = isset( $attributes['defaultOpenSection'] ) ? sanitize_text_field( $attributes['defaultOpenSection'] ) : 'current';

?>

<div class="pw-weather-accordion" data-default-open="<?php echo esc_attr( $default_open ); ?>">
    
    <!-- Current Weather Section -->
    <div class="pw-accordion-item <?php echo 'current' === $default_open ? 'pw-active' : ''; ?>" data-section="current">
        <div class="pw-accordion-header">
            <button class="pw-accordion-trigger" aria-expanded="<?php echo 'current' === $default_open ? 'true' : 'false'; ?>">
                <span class="pw-accordion-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    </svg>
                    <?php esc_html_e( 'Current Weather', 'pearl-weather' ); ?>
                </span>
                <span class="pw-accordion-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </button>
        </div>
        <div class="pw-accordion-content" aria-hidden="<?php echo 'current' === $default_open ? 'false' : 'true'; ?>">
            <div class="pw-current-weather">
                
                <!-- Location Name -->
                <?php if ( $show_location && ! empty( $weather_data['city'] ) ) : ?>
                    <div class="pw-location-name">
                        <h3><?php echo esc_html( $weather_data['city'] ); ?>, <?php echo esc_html( $weather_data['country'] ?? '' ); ?></h3>
                    </div>
                <?php endif; ?>
                
                <!-- Date and Time -->
                <?php if ( $show_date || $show_time ) : ?>
                    <div class="pw-datetime">
                        <?php if ( $show_date && ! empty( $weather_data['date'] ) ) : ?>
                            <span class="pw-date"><?php echo esc_html( $weather_data['date'] ); ?></span>
                        <?php endif; ?>
                        <?php if ( $show_time && ! empty( $weather_data['time'] ) ) : ?>
                            <span class="pw-time"><?php echo esc_html( $weather_data['time'] ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Weather Icon and Temperature -->
                <div class="pw-weather-main">
                    <?php if ( ! empty( $weather_data['icon'] ) ) : ?>
                        <div class="pw-weather-icon">
                            <img src="<?php echo esc_url( $weather_data['icon'] ); ?>" alt="<?php echo esc_attr( $weather_data['description'] ?? '' ); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $show_temperature ) : ?>
                        <div class="pw-temperature">
                            <span class="pw-temp-value"><?php echo esc_html( $weather_data['temperature'] ?? '--' ); ?></span>
                            <span class="pw-temp-unit"><?php echo esc_html( $weather_data['temp_unit'] ?? '°C' ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Weather Description -->
                <?php if ( $show_description && ! empty( $weather_data['description'] ) ) : ?>
                    <div class="pw-weather-description">
                        <?php echo esc_html( $weather_data['description'] ); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Additional Weather Details -->
                <div class="pw-weather-details">
                    <?php if ( ! empty( $weather_data['humidity'] ) ) : ?>
                        <div class="pw-detail-item">
                            <span class="pw-detail-label"><?php esc_html_e( 'Humidity', 'pearl-weather' ); ?>:</span>
                            <span class="pw-detail-value"><?php echo esc_html( $weather_data['humidity'] ); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( ! empty( $weather_data['pressure'] ) ) : ?>
                        <div class="pw-detail-item">
                            <span class="pw-detail-label"><?php esc_html_e( 'Pressure', 'pearl-weather' ); ?>:</span>
                            <span class="pw-detail-value"><?php echo esc_html( $weather_data['pressure'] ); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( ! empty( $weather_data['wind'] ) ) : ?>
                        <div class="pw-detail-item">
                            <span class="pw-detail-label"><?php esc_html_e( 'Wind', 'pearl-weather' ); ?>:</span>
                            <span class="pw-detail-value"><?php echo esc_html( $weather_data['wind'] ); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( ! empty( $weather_data['visibility'] ) ) : ?>
                        <div class="pw-detail-item">
                            <span class="pw-detail-label"><?php esc_html_e( 'Visibility', 'pearl-weather' ); ?>:</span>
                            <span class="pw-detail-value"><?php echo esc_html( $weather_data['visibility'] ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sun Times -->
                <?php if ( ! empty( $weather_data['sunrise'] ) || ! empty( $weather_data['sunset'] ) ) : ?>
                    <div class="pw-sun-times">
                        <?php if ( ! empty( $weather_data['sunrise'] ) ) : ?>
                            <div class="pw-sunrise">
                                <span class="pw-sun-label"><?php esc_html_e( 'Sunrise', 'pearl-weather' ); ?>:</span>
                                <span class="pw-sun-value"><?php echo esc_html( $weather_data['sunrise'] ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ( ! empty( $weather_data['sunset'] ) ) : ?>
                            <div class="pw-sunset">
                                <span class="pw-sun-label"><?php esc_html_e( 'Sunset', 'pearl-weather' ); ?>:</span>
                                <span class="pw-sun-value"><?php echo esc_html( $weather_data['sunset'] ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <!-- Hourly Forecast Section -->
    <?php if ( $show_forecast && ! empty( $forecast_data ) && is_array( $forecast_data ) ) : ?>
    <div class="pw-accordion-item" data-section="forecast">
        <div class="pw-accordion-header">
            <button class="pw-accordion-trigger" aria-expanded="false">
                <span class="pw-accordion-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3V7M12 17V21M3 12H7M17 12H21M5.636 5.636L8.464 8.464M15.536 15.536L18.364 18.364M18.364 5.636L15.536 8.464M8.464 15.536L5.636 18.364" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    <?php esc_html_e( 'Hourly Forecast', 'pearl-weather' ); ?>
                </span>
                <span class="pw-accordion-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </button>
        </div>
        <div class="pw-accordion-content" aria-hidden="true">
            <div class="pw-hourly-forecast">
                <div class="pw-forecast-list">
                    <?php foreach ( $forecast_data as $hour ) : ?>
                        <div class="pw-forecast-item">
                            <?php if ( ! empty( $hour['time'] ) ) : ?>
                                <div class="pw-forecast-time"><?php echo esc_html( $hour['time'] ); ?></div>
                            <?php endif; ?>
                            <?php if ( ! empty( $hour['icon'] ) ) : ?>
                                <div class="pw-forecast-icon">
                                    <img src="<?php echo esc_url( $hour['icon'] ); ?>" alt="<?php echo esc_attr( $hour['condition'] ?? '' ); ?>">
                                </div>
                            <?php endif; ?>
                            <?php if ( ! empty( $hour['temp'] ) ) : ?>
                                <div class="pw-forecast-temp"><?php echo esc_html( $hour['temp'] ); ?></div>
                            <?php endif; ?>
                            <?php if ( ! empty( $hour['condition'] ) ) : ?>
                                <div class="pw-forecast-condition"><?php echo esc_html( $hour['condition'] ); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Additional Weather Data Section -->
    <?php if ( $show_additional ) : ?>
    <div class="pw-accordion-item" data-section="additional">
        <div class="pw-accordion-header">
            <button class="pw-accordion-trigger" aria-expanded="false">
                <span class="pw-accordion-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php esc_html_e( 'Additional Details', 'pearl-weather' ); ?>
                </span>
                <span class="pw-accordion-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </button>
        </div>
        <div class="pw-accordion-content" aria-hidden="true">
            <div class="pw-additional-details">
                <div class="pw-details-grid">
                    <!-- Feels Like -->
                    <?php if ( ! empty( $weather_data['feels_like'] ) ) : ?>
                        <div class="pw-detail-card">
                            <div class="pw-detail-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                </svg>
                            </div>
                            <div class="pw-detail-info">
                                <span class="pw-detail-label"><?php esc_html_e( 'Feels Like', 'pearl-weather' ); ?></span>
                                <span class="pw-detail-value"><?php echo esc_html( $weather_data['feels_like'] ); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Clouds -->
                    <?php if ( ! empty( $weather_data['clouds'] ) ) : ?>
                        <div class="pw-detail-card">
                            <div class="pw-detail-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18 10C18 6.686 15.314 4 12 4C8.686 4 6 6.686 6 10C6 10.548 6.07 11.08 6.2 11.59" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M17 16C19.209 16 21 14.209 21 12C21 9.791 19.209 8 17 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M7 16C4.791 16 3 14.209 3 12C3 9.791 4.791 8 7 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="pw-detail-info">
                                <span class="pw-detail-label"><?php esc_html_e( 'Clouds', 'pearl-weather' ); ?></span>
                                <span class="pw-detail-value"><?php echo esc_html( $weather_data['clouds'] ); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- UV Index -->
                    <?php if ( ! empty( $weather_data['uv_index'] ) ) : ?>
                        <div class="pw-detail-card">
                            <div class="pw-detail-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M12 2V4M12 20V22M2 12H4M20 12H22M4.929 4.929L6.343 6.343M17.657 17.657L19.071 19.071M19.071 4.929L17.657 6.343M6.343 17.657L4.929 19.071" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="pw-detail-info">
                                <span class="pw-detail-label"><?php esc_html_e( 'UV Index', 'pearl-weather' ); ?></span>
                                <span class="pw-detail-value"><?php echo esc_html( $weather_data['uv_index'] ); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Dew Point -->
                    <?php if ( ! empty( $weather_data['dew_point'] ) ) : ?>
                        <div class="pw-detail-card">
                            <div class="pw-detail-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                </svg>
                            </div>
                            <div class="pw-detail-info">
                                <span class="pw-detail-label"><?php esc_html_e( 'Dew Point', 'pearl-weather' ); ?></span>
                                <span class="pw-detail-value"><?php echo esc_html( $weather_data['dew_point'] ); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Air Quality Section (Pro feature - shown conditionally) -->
    <?php if ( $show_aqi && ! empty( $aqi_data ) ) : ?>
    <div class="pw-accordion-item pw-pro-feature" data-section="aqi">
        <div class="pw-accordion-header">
            <button class="pw-accordion-trigger" aria-expanded="false">
                <span class="pw-accordion-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 15H19M5 9H19M12 3L12 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    </svg>
                    <?php esc_html_e( 'Air Quality', 'pearl-weather' ); ?>
                    <span class="pw-pro-badge">PRO</span>
                </span>
                <span class="pw-accordion-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </button>
        </div>
        <div class="pw-accordion-content" aria-hidden="true">
            <div class="pw-air-quality">
                <div class="pw-aqi-value" style="--aqi-color: <?php echo esc_attr( $aqi_data['color'] ?? '#00B150' ); ?>">
                    <span class="pw-aqi-number"><?php echo esc_html( $aqi_data['iaqi'] ?? '--' ); ?></span>
                    <span class="pw-aqi-condition"><?php echo esc_html( $aqi_data['condition_label'] ?? '' ); ?></span>
                </div>
                <div class="pw-aqi-description">
                    <?php echo esc_html( $aqi_data['report'] ?? '' ); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<!-- Accordion JavaScript -->
<script type="text/javascript">
(function() {
    const accordion = document.querySelector('#<?php echo esc_js( $unique_id ); ?> .pw-weather-accordion');
    if (!accordion) return;
    
    const triggers = accordion.querySelectorAll('.pw-accordion-trigger');
    
    triggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            
            const accordionItem = this.closest('.pw-accordion-item');
            const content = accordionItem.querySelector('.pw-accordion-content');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Close all other items (optional - set to false to allow multiple open)
            const closeOthers = true;
            if (closeOthers) {
                const allItems = accordion.querySelectorAll('.pw-accordion-item');
                allItems.forEach(item => {
                    if (item !== accordionItem) {
                        const btn = item.querySelector('.pw-accordion-trigger');
                        const cont = item.querySelector('.pw-accordion-content');
                        btn.setAttribute('aria-expanded', 'false');
                        cont.setAttribute('aria-hidden', 'true');
                        item.classList.remove('pw-active');
                    }
                });
            }
            
            // Toggle current item
            if (isExpanded) {
                this.setAttribute('aria-expanded', 'false');
                content.setAttribute('aria-hidden', 'true');
                accordionItem.classList.remove('pw-active');
            } else {
                this.setAttribute('aria-expanded', 'true');
                content.setAttribute('aria-hidden', 'false');
                accordionItem.classList.add('pw-active');
            }
        });
    });
})();
</script>

<style>
/* Accordion Styles */
.pw-weather-accordion {
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
}

.pw-accordion-item {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.pw-accordion-item:last-child {
    border-bottom: none;
}

.pw-accordion-trigger {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    text-align: left;
    transition: background-color 0.3s ease;
}

.pw-accordion-trigger:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.pw-accordion-title {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pw-accordion-icon svg {
    transition: transform 0.3s ease;
}

.pw-accordion-item.pw-active .pw-accordion-icon svg {
    transform: rotate(180deg);
}

.pw-accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}

.pw-accordion-item.pw-active .pw-accordion-content {
    max-height: 2000px;
    transition: max-height 0.5s ease-in;
}

.pw-accordion-content[aria-hidden="true"] {
    display: none;
}

.pw-accordion-content[aria-hidden="false"] {
    display: block;
    padding: 0 20px 20px 20px;
}

.pw-pro-badge {
    background: #11a10c;
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 8px;
}
</style>