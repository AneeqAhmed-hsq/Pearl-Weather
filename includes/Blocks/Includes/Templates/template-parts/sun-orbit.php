<?php
/**
 * Sun Orbit Visualization Template Part
 *
 * Displays an animated sun orbit showing sunrise time, sunset time,
 * current sun position, and total day length.
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
 * - $block_name: Block name
 * - $template: Template variant name
 * - $single_forecast: Single forecast data (for combined layout)
 * - $index: Index for combined layout
 */

// Get sunrise and sunset times.
if ( 'combined' === $block_name && isset( $single_forecast ) ) {
    $sunrise = isset( $single_forecast['sunrise_time'] ) ? $single_forecast['sunrise_time'] : ( isset( $weather_data['sunrise_time'] ) ? $weather_data['sunrise_time'] : '' );
    $sunset  = isset( $single_forecast['sunset_time'] ) ? $single_forecast['sunset_time'] : ( isset( $weather_data['sunset_time'] ) ? $weather_data['sunset_time'] : '' );
    $sun_position = ( 0 === $index && isset( $weather_data['sun_position'] ) ) ? $weather_data['sun_position'] : '';
    
    // Calculate day length for combined layout.
    if ( ! empty( $sunrise ) && ! empty( $sunset ) ) {
        $sunrise_time = new DateTime( $sunrise );
        $sunset_time = new DateTime( $sunset );
        $interval = $sunrise_time->diff( $sunset_time );
        $day_length = sprintf(
            __( '%d hr %d min', 'pearl-weather' ),
            $interval->h,
            $interval->i
        );
    }
    $translate = '-60px';
} else {
    $sunrise = isset( $weather_data['sunrise_time'] ) ? $weather_data['sunrise_time'] : '';
    $sunset  = isset( $weather_data['sunset_time'] ) ? $weather_data['sunset_time'] : '';
    $sun_position = isset( $weather_data['sun_position'] ) ? $weather_data['sun_position'] : '';
    $day_length = '';
    
    // Determine translation based on template.
    $translate_templates = array( 'table-two', 'tabs-one', 'grid-card' );
    $translate = in_array( $template, $translate_templates, true ) ? '-140px' : '-60px';
}

// Validate sun position.
$sun_angle = is_numeric( $sun_position ) ? (int) $sun_position : 0;
$show_sun = $sun_angle > 10;
$safe_angle = $show_sun ? $sun_angle : 10;

// Animation ID for unique keyframes.
$animation_id = 'pw-sun-orbit-' . wp_unique_id();

// Settings.
$show_day_length = isset( $attributes['showDayLength'] ) ? (bool) $attributes['showDayLength'] : true;
$show_sun_icon = isset( $attributes['showSunIcon'] ) ? (bool) $attributes['showSunIcon'] : true;
$orbit_color = isset( $attributes['sunOrbitColor'] ) ? sanitize_hex_color( $attributes['sunOrbitColor'] ) : '#FF7D7D';
$icon_color = isset( $attributes['sunOrbitIconColor'] ) ? sanitize_hex_color( $attributes['sunOrbitIconColor'] ) : '#FFDF00';
$orbit_size = isset( $attributes['sunOrbitSize'] ) ? sanitize_text_field( $attributes['sunOrbitSize'] ) : 'medium';

// Size mapping.
$size_classes = array(
    'small'  => 'pw-sun-orbit-small',
    'medium' => 'pw-sun-orbit-medium',
    'large'  => 'pw-sun-orbit-large',
);
$size_class = isset( $size_classes[ $orbit_size ] ) ? $size_classes[ $orbit_size ] : 'pw-sun-orbit-medium';

// Additional CSS classes.
$wrapper_classes = array( 'pw-sun-orbit', $size_class );

if ( ! empty( $attributes['sunOrbitCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['sunOrbitCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ); ) ?>" 
     style="--pw-orbit-color: <?php echo esc_attr( $orbit_color ); ?>; --pw-sun-color: <?php echo esc_attr( $icon_color ); ?>;">
    
    <!-- Day Length Display (Combined Layout) -->
    <?php if ( ! empty( $day_length ) && $show_day_length ) : ?>
        <div class="pw-day-length">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span class="pw-day-length-text"><?php echo esc_html( $day_length ); ?></span>
        </div>
    <?php endif; ?>
    
    <div class="pw-sun-orbit-container">
        
        <!-- Sunrise Section -->
        <?php if ( ! empty( $sunrise ) ) : ?>
            <div class="pw-sunrise-info">
                <div class="pw-sun-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 16h16M4 20h16M12 4v4m-4-2 4-4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="12" cy="14" r="3" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </div>
                <div class="pw-sun-info">
                    <span class="pw-sun-label"><?php esc_html_e( 'Sunrise', 'pearl-weather' ); ?></span>
                    <span class="pw-sun-time"><?php echo esc_html( $sunrise ); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Sun Orbit Arc -->
        <div class="pw-sun-orbit-arc">
            
            <!-- Orbit Background -->
            <div class="pw-orbit-bg"></div>
            
            <!-- Sun Position Indicator -->
            <?php if ( $sun_angle > 0 ) : ?>
                <style>
                    @keyframes <?php echo esc_attr( $animation_id ); ?> {
                        0% {
                            transform: rotate(0deg) translate(<?php echo esc_attr( $translate ); ?>) rotate(10deg);
                        }
                        100% {
                            transform: rotate(<?php echo esc_attr( $safe_angle ); ?>deg) translate(<?php echo esc_attr( $translate ); ?>) rotate(10deg);
                        }
                    }
                </style>
                
                <div class="pw-sun-position"
                     style="transform: rotate(<?php echo esc_attr( $safe_angle ); ?>deg) translate(<?php echo esc_attr( $translate ); ?>) rotate(0deg); animation: <?php echo esc_attr( $animation_id ); ?> 1.5s ease-out;">
                    
                    <?php if ( $show_sun && $show_sun_icon ) : ?>
                        <div class="pw-sun-marker">
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="5" fill="currentColor" stroke="currentColor" stroke-width="1"/>
                                <line x1="12" y1="2" x2="12" y2="4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="12" y1="20" x2="12" y2="22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="2" y1="12" x2="4" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <line x1="20" y1="12" x2="22" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Sun Angle Indicator (tooltip) -->
                    <?php if ( isset( $attributes['showSunAngle'] ) && $attributes['showSunAngle'] ) : ?>
                        <div class="pw-sun-angle-tooltip">
                            <?php echo esc_html( $sun_angle ); ?>°
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Sunrise/Sunset Markers on Arc -->
            <div class="pw-orbit-marker pw-marker-sunrise"></div>
            <div class="pw-orbit-marker pw-marker-sunset"></div>
            
        </div>
        
        <!-- Sunset Section -->
        <?php if ( ! empty( $sunset ) ) : ?>
            <div class="pw-sunset-info">
                <div class="pw-sun-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 16h16M4 20h16M12 4v4m-4-2 4-4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="12" cy="14" r="3" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </div>
                <div class="pw-sun-info">
                    <span class="pw-sun-label"><?php esc_html_e( 'Sunset', 'pearl-weather' ); ?></span>
                    <span class="pw-sun-time"><?php echo esc_html( $sunset ); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Day Length Bar (optional) -->
    <?php if ( $show_day_length && empty( $day_length ) && ! empty( $sunrise ) && ! empty( $sunset ) ) : ?>
        <?php
        $sunrise_ts = strtotime( $sunrise );
        $sunset_ts = strtotime( $sunset );
        $day_seconds = $sunset_ts - $sunrise_ts;
        $day_hours = floor( $day_seconds / 3600 );
        $day_minutes = floor( ( $day_seconds % 3600 ) / 60 );
        $day_length_display = sprintf( __( '%d hr %d min', 'pearl-weather' ), $day_hours, $day_minutes );
        ?>
        <div class="pw-day-length-bar">
            <div class="pw-day-length-label"><?php esc_html_e( 'Day Length', 'pearl-weather' ); ?></div>
            <div class="pw-day-length-progress">
                <div class="pw-day-length-fill" style="width: <?php echo esc_attr( min( 100, ( $day_seconds / 86400 ) * 100 ) ); ?>%"></div>
            </div>
            <div class="pw-day-length-value"><?php echo esc_html( $day_length_display ); ?></div>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Sun Orbit Styles */
.pw-sun-orbit {
    position: relative;
    margin: 20px 0;
}

/* Size Variants */
.pw-sun-orbit-small .pw-sun-orbit-arc {
    width: 200px;
    height: 100px;
}

.pw-sun-orbit-medium .pw-sun-orbit-arc {
    width: 280px;
    height: 140px;
}

.pw-sun-orbit-large .pw-sun-orbit-arc {
    width: 360px;
    height: 180px;
}

/* Orbit Container */
.pw-sun-orbit-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

/* Sunrise/Sunset Info */
.pw-sunrise-info,
.pw-sunset-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 4px;
    min-width: 80px;
}

.pw-sun-icon svg {
    width: 24px;
    height: 24px;
    stroke: currentColor;
}

.pw-sun-label {
    font-size: 12px;
    opacity: 0.7;
}

.pw-sun-time {
    font-size: 14px;
    font-weight: 500;
}

/* Sun Orbit Arc */
.pw-sun-orbit-arc {
    position: relative;
    background: transparent;
    border-radius: 50% / 100% 100% 0 0;
    border-bottom: 2px solid var(--pw-orbit-color, #FF7D7D);
    transform: scaleX(1.5);
    margin: 0 auto;
}

.pw-orbit-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: inherit;
    background: radial-gradient(ellipse at 50% 100%, rgba(255, 125, 125, 0.1), transparent);
}

/* Sun Position Marker */
.pw-sun-position {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform-origin: center bottom;
    transition: transform 0.3s ease;
}

.pw-sun-marker {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    color: var(--pw-sun-color, #FFDF00);
    filter: drop-shadow(0 0 4px rgba(255, 223, 0, 0.5));
}

.pw-sun-marker svg {
    width: 30px;
    height: 30px;
}

/* Sun Angle Tooltip */
.pw-sun-angle-tooltip {
    position: absolute;
    bottom: 35px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.2s ease;
    pointer-events: none;
}

.pw-sun-position:hover .pw-sun-angle-tooltip {
    opacity: 1;
}

/* Orbit Markers */
.pw-orbit-marker {
    position: absolute;
    bottom: -4px;
    width: 8px;
    height: 8px;
    background: var(--pw-orbit-color, #FF7D7D);
    border-radius: 50%;
    transform: translateX(-50%);
}

.pw-marker-sunrise {
    left: 0;
}

.pw-marker-sunset {
    right: 0;
    transform: translateX(50%);
}

/* Day Length Bar */
.pw-day-length {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    margin-bottom: 12px;
    padding: 4px 8px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 20px;
}

.pw-day-length-bar {
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-day-length-label {
    font-size: 11px;
    opacity: 0.7;
    margin-bottom: 4px;
}

.pw-day-length-progress {
    height: 4px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 2px;
    overflow: hidden;
    margin: 8px 0;
}

.pw-day-length-fill {
    height: 100%;
    background: var(--pw-orbit-color, #FF7D7D);
    border-radius: 2px;
    transition: width 0.5s ease;
}

.pw-day-length-value {
    font-size: 12px;
    font-weight: 500;
    text-align: right;
}

/* Combined Layout Specific */
.pw-sun-orbit .pw-day-length {
    position: absolute;
    top: -20px;
    right: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-sun-orbit-container {
        flex-direction: column;
    }
    
    .pw-sunrise-info,
    .pw-sunset-info {
        flex-direction: row;
        gap: 12px;
        width: 100%;
        justify-content: center;
    }
    
    .pw-sun-orbit-small .pw-sun-orbit-arc {
        width: 160px;
        height: 80px;
    }
    
    .pw-sun-orbit-medium .pw-sun-orbit-arc {
        width: 220px;
        height: 110px;
    }
    
    .pw-sun-orbit-large .pw-sun-orbit-arc {
        width: 280px;
        height: 140px;
    }
}

/* Reduced Motion Preference */
@media (prefers-reduced-motion: reduce) {
    .pw-sun-position {
        animation: none !important;
        transition: none !important;
    }
}
</style>