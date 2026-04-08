<?php
/**
 * Daily Weather Details Template (Additional Data)
 *
 * Displays additional weather data including humidity, pressure,
 * wind, clouds, visibility, sunrise, and sunset.
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
 * - $layout: Layout type ('vertical' or 'horizontal')
 * - $active_additional_data_layout: Layout alignment ('center', 'left', 'justified')
 * - $show_humidity, $show_pressure, $show_wind, etc.: Display flags
 * - $humidity_icon, $pressure_icon, etc.: Icon HTML
 * - $humidity_title, $pressure_title, etc.: Label text
 */

// Check if any weather details should be displayed.
$show_any_details = (isset($show_humidity) && $show_humidity) ||
                    (isset($show_pressure) && $show_pressure) ||
                    (isset($show_wind) && $show_wind) ||
                    (isset($show_wind_gusts) && $show_wind_gusts) ||
                    (isset($show_visibility) && $show_visibility) ||
                    (isset($show_sunrise_sunset) && $show_sunrise_sunset) ||
                    (isset($show_clouds) && $show_clouds);

if ( ! $show_any_details ) {
    return;
}

// Layout class for alignment.
$layout_class = '';
if ( 'vertical' === $layout && isset( $active_additional_data_layout ) ) {
    $layout_class = ' pw-layout-' . $active_additional_data_layout;
}

// Show icons wrapper flag.
$show_icons_wrapper = isset( $show_weather_icon_wrapper ) ? $show_weather_icon_wrapper : false;

// CSS classes.
$wrapper_classes = array( 'pw-additional-data', 'pw-daily-details' );
if ( ! empty( $layout_class ) ) {
    $wrapper_classes[] = ltrim( $layout_class, ' ' );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    <div class="pw-details-grid">
        
        <?php if ( $show_icons_wrapper ) : ?>
            <div class="pw-details-icons-wrapper">
        <?php endif; ?>
        
        <!-- Humidity -->
        <?php if ( isset( $show_humidity ) && $show_humidity && ! empty( $weather_data['humidity'] ) ) : ?>
            <div class="pw-detail-item pw-detail-humidity">
                <div class="pw-detail-label-wrapper">
                    <?php if ( isset( $humidity_icon ) ) : ?>
                        <span class="pw-detail-icon"><?php echo $humidity_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    <?php endif; ?>
                    <span class="pw-detail-label"><?php echo isset( $humidity_title ) ? esc_html( $humidity_title ) : esc_html__( 'Humidity', 'pearl-weather' ); ?></span>
                </div>
                <span class="pw-detail-value"><?php echo esc_html( $weather_data['humidity'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Pressure -->
        <?php if ( isset( $show_pressure ) && $show_pressure && ! empty( $weather_data['pressure'] ) ) : ?>
            <div class="pw-detail-item pw-detail-pressure">
                <div class="pw-detail-label-wrapper">
                    <?php if ( isset( $pressure_icon ) ) : ?>
                        <span class="pw-detail-icon"><?php echo $pressure_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    <?php endif; ?>
                    <span class="pw-detail-label"><?php echo isset( $pressure_title ) ? esc_html( $pressure_title ) : esc_html__( 'Pressure', 'pearl-weather' ); ?></span>
                </div>
                <span class="pw-detail-value"><?php echo esc_html( $weather_data['pressure'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Wind -->
        <?php if ( isset( $show_wind ) && $show_wind && ! empty( $weather_data['wind'] ) ) : ?>
            <div class="pw-detail-item pw-detail-wind">
                <div class="pw-detail-label-wrapper">
                    <?php if ( isset( $wind_icon ) ) : ?>
                        <span class="pw-detail-icon"><?php echo $wind_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    <?php endif; ?>
                    <span class="pw-detail-label"><?php echo isset( $wind_title ) ? esc_html( $wind_title ) : esc_html__( 'Wind', 'pearl-weather' ); ?></span>
                </div>
                <span class="pw-detail-value"><?php echo wp_kses_post( $weather_data['wind'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ( $show_icons_wrapper ) : ?>
            </div>
        <?php endif; ?>
        
        <!-- Wind Gusts -->
        <?php if ( isset( $show_wind_gusts ) && $show_wind_gusts && ! empty( $weather_data['gust'] ) ) : ?>
            <div class="pw-detail-item pw-detail-gust">
                <div class="pw-detail-label-wrapper">
                    <?php if ( isset( $wind_gust_icon ) ) : ?>
                        <span class="pw-detail-icon"><?php echo $wind_gust_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    <?php endif; ?>
                    <span class="pw-detail-label"><?php echo isset( $wind_gust_title ) ? esc_html( $wind_gust_title ) : esc_html__( 'Wind Gust', 'pearl-weather' ); ?></span>
                </div>
                <span class="pw-detail-value"><?php echo wp_kses_post( $weather_data['gust'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Clouds -->
        <?php if ( isset( $show_clouds ) && $show_clouds && ! empty( $weather_data['clouds'] ) ) : ?>
            <div class="pw-detail-item pw-detail-clouds">
                <div class="pw-detail-label-wrapper">
                    <?php if ( isset( $clouds_icon ) ) : ?>
                        <span class="pw-detail-icon"><?php echo $clouds_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    <?php endif; ?>
                    <span class="pw-detail-label"><?php echo isset( $clouds_title ) ? esc_html( $clouds_title ) : esc_html__( 'Clouds', 'pearl-weather' ); ?></span>
                </div>
                <span class="pw-detail-value"><?php echo esc_html( $weather_data['clouds'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Visibility -->
        <?php if ( isset( $show_visibility ) && $show_visibility && ! empty( $weather_data['visibility'] ) ) : ?>
            <div class="pw-detail-item pw-detail-visibility">
                <div class="pw-detail-label-wrapper">
                    <?php if ( isset( $visibility_icon ) ) : ?>
                        <span class="pw-detail-icon"><?php echo $visibility_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    <?php endif; ?>
                    <span class="pw-detail-label"><?php echo isset( $visibility_title ) ? esc_html( $visibility_title ) : esc_html__( 'Visibility', 'pearl-weather' ); ?></span>
                </div>
                <span class="pw-detail-value"><?php echo esc_html( $weather_data['visibility'] ); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Sunrise & Sunset -->
        <?php if ( isset( $show_sunrise_sunset ) && $show_sunrise_sunset ) : ?>
            <?php if ( ! empty( $weather_data['sunrise'] ) ) : ?>
                <div class="pw-detail-item pw-detail-sunrise">
                    <div class="pw-detail-label-wrapper">
                        <?php if ( isset( $sunrise_icon ) ) : ?>
                            <span class="pw-detail-icon"><?php echo $sunrise_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <?php endif; ?>
                        <span class="pw-detail-label"><?php echo isset( $sunrise_title ) ? esc_html( $sunrise_title ) : esc_html__( 'Sunrise', 'pearl-weather' ); ?></span>
                    </div>
                    <span class="pw-detail-value"><?php echo esc_html( $weather_data['sunrise'] ); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ( ! empty( $weather_data['sunset'] ) ) : ?>
                <div class="pw-detail-item pw-detail-sunset">
                    <div class="pw-detail-label-wrapper">
                        <?php if ( isset( $sunset_icon ) ) : ?>
                            <span class="pw-detail-icon"><?php echo $sunset_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <?php endif; ?>
                        <span class="pw-detail-label"><?php echo isset( $sunset_title ) ? esc_html( $sunset_title ) : esc_html__( 'Sunset', 'pearl-weather' ); ?></span>
                    </div>
                    <span class="pw-detail-value"><?php echo esc_html( $weather_data['sunset'] ); ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
    </div>
</div>

<style>
/* Additional Data Styles */
.pw-additional-data {
    margin-top: 16px;
}

.pw-details-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

/* Layout alignments */
.pw-layout-center .pw-details-grid {
    justify-content: center;
}

.pw-layout-center .pw-detail-item {
    text-align: center;
    flex: 0 1 auto;
}

.pw-layout-left .pw-details-grid {
    justify-content: flex-start;
}

.pw-layout-justified .pw-detail-item {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Detail Item */
.pw-detail-item {
    background: rgba(0, 0, 0, 0.02);
    border-radius: 8px;
    padding: 10px 14px;
    min-width: 120px;
    transition: all 0.2s ease;
}

.pw-detail-item:hover {
    background: rgba(0, 0, 0, 0.05);
}

.pw-detail-label-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}

.pw-detail-icon {
    display: inline-flex;
    align-items: center;
}

.pw-detail-icon i,
.pw-detail-icon svg {
    width: 16px;
    height: 16px;
}

.pw-detail-label {
    font-size: 12px;
    opacity: 0.7;
}

.pw-detail-value {
    font-size: 14px;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-detail-item {
        flex: 1 0 calc(50% - 12px);
        min-width: auto;
    }
    
    .pw-layout-justified .pw-detail-item {
        flex-direction: column;
        text-align: center;
        gap: 4px;
    }
}

@media (max-width: 480px) {
    .pw-detail-item {
        flex: 1 0 100%;
    }
}
</style>