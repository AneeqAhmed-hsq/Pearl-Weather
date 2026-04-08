<?php
/**
 * Grid Two/Three Additional Data Renderer
 *
 * Renders additional weather data cards for Grid Two and Grid Three layouts
 * including UV index, sunrise/sunset, moon phases, wind, humidity,
 * pressure, precipitation, air quality, visibility, and clouds.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Templates/Parts
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if additional data should be displayed.
$show_additional = isset( $attributes['displayAdditionalData'] ) ? (bool) $attributes['displayAdditionalData'] : true;

if ( ! $show_additional ) {
    return;
}

// Process additional data options.
$additional_options = isset( $attributes['additionalDataOptions'] ) ? $attributes['additionalDataOptions'] : array();
$active_options = array();

foreach ( $additional_options as $option ) {
    if ( isset( $option['isActive'] ) && true === $option['isActive'] ) {
        $active_options[] = isset( $option['value'] ) ? $option['value'] : '';
    }
}

if ( empty( $active_options ) ) {
    return;
}

// Extract weather data.
$uv_index = isset( $weather_data['uv_index'] ) ? (float) $weather_data['uv_index'] : 0;
$uv_description = get_uv_description( $uv_index );
$uv_position = min( 100, max( 0, ( $uv_index / 11 ) * 100 ) );

$sunrise = isset( $weather_data['sunrise'] ) ? $weather_data['sunrise'] : '';
$sunset = isset( $weather_data['sunset'] ) ? $weather_data['sunset'] : '';

$moonrise = isset( $weather_data['moonrise'] ) ? $weather_data['moonrise'] : '';
$moonset = isset( $weather_data['moonset'] ) ? $weather_data['moonset'] : '';
$moon_phase = isset( $weather_data['moon_phase'] ) ? (float) $weather_data['moon_phase'] : 0;

$wind = isset( $weather_data['wind'] ) ? $weather_data['wind'] : '';
$wind_gust = isset( $weather_data['gust'] ) ? $weather_data['gust'] : '';
$wind_direction = isset( $weather_data['wind_direction'] ) ? $weather_data['wind_direction'] : '';

$humidity = isset( $weather_data['humidity'] ) ? $weather_data['humidity'] : '';
$dew_point = isset( $weather_data['dew_point'] ) ? $weather_data['dew_point'] : '';

$pressure = isset( $weather_data['pressure'] ) ? $weather_data['pressure'] : '';
$pressure_desc = get_pressure_description( $pressure );

$precipitation = isset( $weather_data['precipitation'] ) ? $weather_data['precipitation'] : '';
$rain_chance = isset( $weather_data['rain_chance'] ) ? $weather_data['rain_chance'] : '';
$snow = isset( $weather_data['snow'] ) ? $weather_data['snow'] : '';

$air_quality = isset( $aqi_data['iaqi'] ) ? (int) $aqi_data['iaqi'] : 0;
$air_quality_name = get_aqi_condition_label( $air_quality );
$air_position = min( 92, max( 0, ( $air_quality / 300 ) * 100 ) );

$visibility = isset( $weather_data['visibility'] ) ? $weather_data['visibility'] : '';
$visibility_desc = get_visibility_description( $visibility );

$clouds = isset( $weather_data['clouds'] ) ? $weather_data['clouds'] : '';
$clouds_desc = get_clouds_description( $clouds );

// Grid settings.
$grid_columns = isset( $attributes['gridAdditionalColumns'] ) ? (int) $attributes['gridAdditionalColumns'] : 2;
$gap = isset( $attributes['gridAdditionalGap'] ) ? (int) $attributes['gridAdditionalGap'] : 16;

// CSS classes.
$wrapper_classes = array( 'pw-grid-additional-data' );
$wrapper_classes[] = 'pw-grid-cols-' . $grid_columns;

if ( ! empty( $attributes['gridAdditionalCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['gridAdditionalCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     style="--pw-grid-gap: <?php echo esc_attr( $gap ); ?>px;">
    
    <?php foreach ( $active_options as $option ) : ?>
        
        <!-- UV Index Card -->
        <?php if ( 'uv_index' === $option ) : ?>
            <div class="pw-grid-card pw-uv-card">
                <div class="pw-card-header">
                    <span class="pw-card-icon">
                        <i class="pw-icon-uv-index"></i>
                    </span>
                    <span class="pw-card-title"><?php esc_html_e( 'UV Index', 'pearl-weather' ); ?></span>
                </div>
                <div class="pw-card-value">
                    <span class="pw-main-value"><?php echo esc_html( $uv_index ); ?></span>
                    <span class="pw-sub-value"><?php echo esc_html( $uv_description ); ?></span>
                </div>
                <div class="pw-progress-bar">
                    <div class="pw-progress-fill" style="width: <?php echo esc_attr( $uv_position ); ?>%;"></div>
                    <div class="pw-progress-marker" style="left: <?php echo esc_attr( $uv_position ); ?>%;"></div>
                </div>
                <div class="pw-progress-labels">
                    <span>Low</span>
                    <span>Moderate</span>
                    <span>High</span>
                    <span>Very High</span>
                    <span>Extreme</span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Sunrise/Sunset Card -->
        <?php if ( 'sunriseSunset' === $option || ( 'sunrise' === $option && 'sunset' === $option ) ) : ?>
            <?php if ( ! empty( $sunrise ) && ! empty( $sunset ) ) : ?>
                <div class="pw-grid-card pw-sun-card">
                    <div class="pw-sunrise-section">
                        <div class="pw-card-header">
                            <span class="pw-card-icon">
                                <i class="pw-icon-sunrise"></i>
                            </span>
                            <span class="pw-card-title"><?php esc_html_e( 'Sunrise', 'pearl-weather' ); ?></span>
                        </div>
                        <div class="pw-card-value"><?php echo esc_html( $sunrise ); ?></div>
                    </div>
                    
                    <div class="pw-sun-diagram">
                        <svg viewBox="0 0 320 40" preserveAspectRatio="none">
                            <path d="M0,40 Q160,0 320,40" fill="none" stroke="url(#sunGradient)" stroke-width="3"/>
                            <defs>
                                <linearGradient id="sunGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#2c3e50"/>
                                    <stop offset="30%" stop-color="#f39c12"/>
                                    <stop offset="70%" stop-color="#f39c12"/>
                                    <stop offset="100%" stop-color="#2c3e50"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    
                    <div class="pw-sunset-section">
                        <div class="pw-card-header">
                            <span class="pw-card-icon">
                                <i class="pw-icon-sunset"></i>
                            </span>
                            <span class="pw-card-title"><?php esc_html_e( 'Sunset', 'pearl-weather' ); ?></span>
                        </div>
                        <div class="pw-card-value"><?php echo esc_html( $sunset ); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Moon Phase Card -->
        <?php if ( 'moonriseMoonset' === $option || 'moon_phase' === $option ) : ?>
            <div class="pw-grid-card pw-moon-card">
                <?php if ( ! empty( $moonrise ) ) : ?>
                    <div class="pw-moonrise-section">
                        <div class="pw-card-header">
                            <span class="pw-card-icon">
                                <i class="pw-icon-moonrise"></i>
                            </span>
                            <span class="pw-card-title"><?php esc_html_e( 'Moonrise', 'pearl-weather' ); ?></span>
                        </div>
                        <div class="pw-card-value"><?php echo esc_html( $moonrise ); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $moonset ) ) : ?>
                    <div class="pw-moonset-section">
                        <div class="pw-card-header">
                            <span class="pw-card-icon">
                                <i class="pw-icon-moonset"></i>
                            </span>
                            <span class="pw-card-title"><?php esc_html_e( 'Moonset', 'pearl-weather' ); ?></span>
                        </div>
                        <div class="pw-card-value"><?php echo esc_html( $moonset ); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ( $moon_phase > 0 ) : ?>
                    <div class="pw-moon-phase-section">
                        <div class="pw-card-header">
                            <span class="pw-card-icon">
                                <i class="pw-icon-moon-phase"></i>
                            </span>
                            <span class="pw-card-title"><?php esc_html_e( 'Moon Phase', 'pearl-weather' ); ?></span>
                        </div>
                        <div class="pw-card-value"><?php echo esc_html( round( $moon_phase * 100 ) ); ?>%</div>
                        <div class="pw-moon-phase-icon"><?php echo get_moon_phase_icon( $moon_phase ); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Wind Card -->
        <?php if ( 'wind' === $option && ! empty( $wind ) ) : ?>
            <div class="pw-grid-card pw-wind-card">
                <div class="pw-card-header">
                    <span class="pw-card-icon">
                        <i class="pw-icon-wind"></i>
                    </span>
                    <span class="pw-card-title"><?php esc_html_e( 'Wind', 'pearl-weather' ); ?></span>
                </div>
                <div class="pw-card-value">
                    <?php echo wp_kses_post( $wind ); ?>
                    <?php if ( ! empty( $wind_direction ) ) : ?>
                        <span class="pw-wind-direction"><?php echo esc_html( $wind_direction ); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ( ! empty( $wind_gust ) && in_array( 'gust', $active_options, true ) ) : ?>
                    <div class="pw-wind-gust">
                        <span class="pw-gust-label"><?php esc_html_e( 'Gust', 'pearl-weather' ); ?>:</span>
                        <span class="pw-gust-value"><?php echo wp_kses_post( $wind_gust ); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Humidity/Dew Point Card -->
        <?php if ( 'humidity' === $option && ! empty( $humidity ) ) : ?>
            <div class="pw-grid-card pw-humidity-card">
                <div class="pw-card-header">
                    <span class="pw-card-icon">
                        <i class="pw-icon-humidity"></i>
                    </span>
                    <span class="pw-card-title"><?php esc_html_e( 'Humidity', 'pearl-weather' ); ?></span>
                </div>
                <div class="pw-card-value"><?php echo esc_html( $humidity ); ?>%</div>
                
                <?php if ( ! empty( $dew_point ) && in_array( 'dew_point', $active_options, true ) ) : ?>
                    <div class="pw-dew-point">
                        <span class="pw-dew-label"><?php esc_html_e( 'Dew Point', 'pearl-weather' ); ?>:</span>
                        <span class="pw-dew-value"><?php echo esc_html( $dew_point ); ?>°</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Pressure Card -->
        <?php if ( 'pressure' === $option && ! empty( $pressure ) ) : ?>
            <div class="pw-grid-card pw-pressure-card">
                <div class="pw-card-header">
                    <span class="pw-card-icon">
                        <i class="pw-icon-pressure"></i>
                    </span>
                    <span class="pw-card-title"><?php esc_html_e( 'Pressure', 'pearl-weather' ); ?></span>
                </div>
                <div class="pw-card-value"><?php echo esc_html( $pressure ); ?> hPa</div>
                <?php if ( ! empty( $pressure_desc ) ) : ?>
                    <div class="pw-pressure-desc"><?php echo esc_html( $pressure_desc ); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Precipitation Card -->
        <?php if ( 'precipitation' === $option && ( ! empty( $precipitation ) || ! empty( $rain_chance ) || ! empty( $snow ) ) ) : ?>
            <div class="pw-grid-card pw-precip-card">
                <div class="pw-card-header">
                    <span class="pw-card-icon">
                        <i class="pw-icon-precipitation"></i>
                    </span>
                    <span class="pw-card-title"><?php esc_html_e( 'Precipitation', 'pearl-weather' ); ?></span>
                </div>
                <?php if ( ! empty( $precipitation ) ) : ?>
                    <div class="pw-card-value"><?php echo esc_html( $precipitation ); ?> mm</div>
                <?php endif; ?>
                <div class="pw-precip-details">
                    <?php if ( ! empty( $rain_chance ) && in_array( 'rain_chance', $active_options, true ) ) : ?>
                        <div class="pw-rain-chance">
                            <span class="pw-detail-label"><?php esc_html_e( 'Rain Chance', 'pearl-weather' ); ?>:</span>
                            <span class="pw-detail-value"><?php echo esc_html( $rain_chance ); ?>%</span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $snow ) && in_array( 'snow', $active_options, true ) ) : ?>
                        <div class="pw-snow">
                            <span class="pw-detail-label"><?php esc_html_e( 'Snow', 'pearl-weather' ); ?>:</span>
                            <span class="pw-detail-value"><?php echo esc_html( $snow ); ?> mm</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Air Quality Card -->
        <?php if ( 'air_quality' === $option && $air_quality > 0 ) : ?>
            <div class="pw-grid-card pw-aqi-card">
                <div class="pw-card-header">
                    <span class="pw-card-icon">
                        <i class="pw-icon-air-quality"></i>
                    </span>
                    <span class="pw-card-title"><?php esc_html_e( 'Air Quality', 'pearl-weather' ); ?></span>
                </div>
                <div class="pw-card-value">
                    <span class="pw-main-value"><?php echo esc_html( $air_quality ); ?></span>
                    <span class="pw-sub-value"><?php echo esc_html( $air_quality_name ); ?></span>
                </div>
                <div class="pw-progress-bar pw-aqi-bar">
                    <div class="pw-progress-fill" style="width: <?php echo esc_attr( $air_position ); ?>%;"></div>
                    <div class="pw-progress-marker" style="left: <?php echo esc_attr( $air_position ); ?>%;"></div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Visibility Card -->
        <?php if ( 'visibility' === $option && ! empty( $visibility ) ) : ?>
            <div class="pw-grid-card pw-visibility-card">
                <div class="pw-card-header">
                    <span class="pw-card-icon">
                        <i class="pw-icon-visibility"></i>
                    </span>
                    <span class="pw-card-title"><?php esc_html_e( 'Visibility', 'pearl-weather' ); ?></span>
                </div>
                <div class="pw-card-value"><?php echo esc_html( $visibility ); ?></div>
                <?php if ( ! empty( $visibility_desc ) ) : ?>
                    <div class="pw-visibility-desc"><?php echo esc_html( $visibility_desc ); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Clouds Card -->
        <?php if ( 'clouds' === $option && ! empty( $clouds ) ) : ?>
            <div class="pw-grid-card pw-clouds-card">
                <div class="pw-card-header">
                    <span class="pw-card-icon">
                        <i class="pw-icon-clouds"></i>
                    </span>
                    <span class="pw-card-title"><?php esc_html_e( 'Clouds', 'pearl-weather' ); ?></span>
                </div>
                <div class="pw-card-value"><?php echo esc_html( $clouds ); ?>%</div>
                <?php if ( ! empty( $clouds_desc ) ) : ?>
                    <div class="pw-clouds-desc"><?php echo esc_html( $clouds_desc ); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    <?php endforeach; ?>
    
</div>

<style>
/* Grid Additional Data Styles */
.pw-grid-additional-data {
    display: grid;
    grid-template-columns: repeat(var(--pw-grid-cols, 2), 1fr);
    gap: var(--pw-grid-gap, 16px);
    margin-top: 20px;
}

/* Grid Cards */
.pw-grid-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.pw-grid-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Card Header */
.pw-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.pw-card-icon i {
    font-size: 20px;
    color: var(--pw-primary-color, #f26c0d);
}

.pw-card-title {
    font-size: 13px;
    font-weight: 500;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Card Value */
.pw-card-value {
    margin-bottom: 12px;
}

.pw-main-value {
    font-size: 28px;
    font-weight: 700;
    line-height: 1.2;
}

.pw-sub-value {
    font-size: 13px;
    color: #666;
    margin-left: 8px;
}

/* Progress Bar */
.pw-progress-bar {
    position: relative;
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    margin: 12px 0 6px;
    overflow: visible;
}

.pw-progress-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #2ecc71, #f39c12, #e74c3c);
}

.pw-progress-marker {
    position: absolute;
    top: -4px;
    width: 12px;
    height: 12px;
    background: #fff;
    border: 2px solid #333;
    border-radius: 50%;
    transform: translateX(-50%);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.pw-progress-labels {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
    color: #999;
    margin-top: 4px;
}

/* Sunrise/Sunset Card */
.pw-sun-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pw-sun-diagram {
    flex: 1;
    margin: 0 12px;
}

.pw-sun-diagram svg {
    width: 100%;
    height: 40px;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-grid-additional-data {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .pw-main-value {
        font-size: 22px;
    }
    
    .pw-grid-card {
        padding: 12px;
    }
}

@media (max-width: 480px) {
    .pw-grid-additional-data {
        grid-template-columns: repeat(1, 1fr);
    }
}
</style>

<?php
/**
 * Helper functions for additional data display.
 */
if ( ! function_exists( 'get_uv_description' ) ) {
    function get_uv_description( $uv ) {
        if ( $uv <= 2 ) return __( 'Low', 'pearl-weather' );
        if ( $uv <= 5 ) return __( 'Moderate', 'pearl-weather' );
        if ( $uv <= 7 ) return __( 'High', 'pearl-weather' );
        if ( $uv <= 10 ) return __( 'Very High', 'pearl-weather' );
        return __( 'Extreme', 'pearl-weather' );
    }
}

if ( ! function_exists( 'get_pressure_description' ) ) {
    function get_pressure_description( $pressure ) {
        if ( $pressure < 1013 ) return __( 'Low pressure system', 'pearl-weather' );
        if ( $pressure > 1013 ) return __( 'High pressure system', 'pearl-weather' );
        return __( 'Normal pressure', 'pearl-weather' );
    }
}

if ( ! function_exists( 'get_visibility_description' ) ) {
    function get_visibility_description( $visibility ) {
        $vis_km = is_numeric( $visibility ) ? $visibility / 1000 : 10;
        if ( $vis_km < 1 ) return __( 'Very poor visibility', 'pearl-weather' );
        if ( $vis_km < 4 ) return __( 'Poor visibility', 'pearl-weather' );
        if ( $vis_km < 10 ) return __( 'Moderate visibility', 'pearl-weather' );
        return __( 'Good visibility', 'pearl-weather' );
    }
}

if ( ! function_exists( 'get_clouds_description' ) ) {
    function get_clouds_description( $clouds ) {
        if ( $clouds < 10 ) return __( 'Clear sky', 'pearl-weather' );
        if ( $clouds < 30 ) return __( 'Few clouds', 'pearl-weather' );
        if ( $clouds < 60 ) return __( 'Scattered clouds', 'pearl-weather' );
        if ( $clouds < 90 ) return __( 'Broken clouds', 'pearl-weather' );
        return __( 'Overcast', 'pearl-weather' );
    }
}

if ( ! function_exists( 'get_moon_phase_icon' ) ) {
    function get_moon_phase_icon( $phase ) {
        if ( $phase < 0.03 ) return '🌑';
        if ( $phase < 0.25 ) return '🌒';
        if ( $phase < 0.5 ) return '🌓';
        if ( $phase < 0.75 ) return '🌔';
        if ( $phase < 0.97 ) return '🌖';
        return '🌕';
    }
}