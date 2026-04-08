<?php
/**
 * Forecast Additional Data Renderer
 *
 * Displays additional weather data for forecast items including
 * precipitation, wind, humidity, pressure, UV index, rain chance, etc.
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
 * - $single_forecast: Single forecast item data
 * - $forecast_options: Array of active forecast options
 * - $weather_item_labels: Array of item labels
 * - $block_weather_icons: Array of icon classes (or use helper)
 */

// Get forecast options.
$active_options = isset( $forecast_options ) ? $forecast_options : array();

if ( empty( $active_options ) ) {
    return;
}

// Extract values from forecast data.
$rain_chance = isset( $single_forecast['rain'] ) ? $single_forecast['rain'] : ( isset( $single_forecast['rain_chance'] ) ? $single_forecast['rain_chance'] : '' );
$wind = isset( $single_forecast['wind'] ) ? $single_forecast['wind'] : '';
$wind_gust = isset( $single_forecast['gusts'] ) ? $single_forecast['gusts'] : ( isset( $single_forecast['gust'] ) ? $single_forecast['gust'] : '' );
$uv_index = isset( $single_forecast['uvi'] ) ? $single_forecast['uvi'] : ( isset( $single_forecast['uv_index'] ) ? $single_forecast['uv_index'] : '' );
$humidity = isset( $single_forecast['humidity'] ) ? $single_forecast['humidity'] : '';
$pressure = isset( $single_forecast['pressure'] ) ? $single_forecast['pressure'] : '';
$precipitation = isset( $single_forecast['precipitation'] ) ? $single_forecast['precipitation'] : '';
$clouds = isset( $single_forecast['clouds'] ) ? $single_forecast['clouds'] : '';
$snow = isset( $single_forecast['snow'] ) ? $single_forecast['snow'] : '';

// Layout settings.
$columns = isset( $attributes['forecastAdditionalColumns'] ) ? (int) $attributes['forecastAdditionalColumns'] : 3;
$gap = isset( $attributes['forecastAdditionalGap'] ) ? (int) $attributes['forecastAdditionalGap'] : 12;
$card_style = isset( $attributes['forecastAdditionalCardStyle'] ) ? sanitize_text_field( $attributes['forecastAdditionalCardStyle'] ) : 'default';

// CSS classes.
$wrapper_classes = array( 'pw-forecast-additional-data' );
$wrapper_classes[] = 'pw-additional-cols-' . $columns;
$wrapper_classes[] = 'pw-additional-style-' . $card_style;

if ( ! empty( $attributes['forecastAdditionalCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['forecastAdditionalCustomClass'] );
}

// Inline style for grid.
$grid_style = sprintf(
    '--pw-forecast-additional-columns: %d; --pw-forecast-additional-gap: %dpx;',
    $columns,
    $gap
);

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" style="<?php echo esc_attr( $grid_style ); ?>">
    
    <?php foreach ( $active_options as $option ) : ?>
        <?php
        // Get value for this option.
        $value = '';
        $unit = '';
        
        switch ( $option ) {
            case 'precipitation':
                $value = $precipitation;
                $unit = isset( $attributes['displayPrecipitationUnit'] ) ? $attributes['displayPrecipitationUnit'] : 'mm';
                break;
            case 'rain_chance':
            case 'rainchance':
                $value = $rain_chance;
                $unit = '%';
                break;
            case 'wind':
                $value = $wind;
                break;
            case 'gust':
                $value = $wind_gust;
                break;
            case 'uv_index':
                $value = $uv_index;
                break;
            case 'humidity':
                if ( is_object( $humidity ) ) {
                    $value = isset( $humidity->value ) ? $humidity->value : '';
                    $unit = isset( $humidity->unit ) ? $humidity->unit : '%';
                } elseif ( is_array( $humidity ) ) {
                    $value = isset( $humidity['value'] ) ? $humidity['value'] : '';
                    $unit = isset( $humidity['unit'] ) ? $humidity['unit'] : '%';
                } else {
                    $value = $humidity;
                    $unit = '%';
                }
                break;
            case 'pressure':
                $value = $pressure;
                $unit = isset( $attributes['displayPressureUnit'] ) ? $attributes['displayPressureUnit'] : 'hPa';
                break;
            case 'clouds':
                $value = $clouds;
                $unit = '%';
                break;
            case 'snow':
                $value = $snow;
                $unit = isset( $attributes['displayPrecipitationUnit'] ) ? $attributes['displayPrecipitationUnit'] : 'mm';
                break;
            case 'sunrise':
            case 'sunset':
            case 'sunrise_time':
            case 'sunset_time':
                $value = isset( $single_forecast[ $option ] ) ? $single_forecast[ $option ] : '';
                break;
            default:
                $value = isset( $single_forecast[ $option ] ) ? $single_forecast[ $option ] : '';
                break;
        }
        
        // Skip if no value.
        if ( empty( $value ) && '0' !== $value ) {
            continue;
        }
        
        // Get label and icon.
        $label = get_forecast_additional_label( $option );
        $icon_class = get_forecast_additional_icon_class( $option );
        ?>
        
        <div class="pw-forecast-additional-item" data-option="<?php echo esc_attr( $option ); ?>">
            <div class="pw-additional-card">
                
                <!-- Icon -->
                <div class="pw-additional-icon">
                    <i class="<?php echo esc_attr( $icon_class ); ?>"></i>
                </div>
                
                <!-- Label -->
                <div class="pw-additional-label">
                    <?php echo esc_html( $label ); ?>
                </div>
                
                <!-- Value -->
                <div class="pw-additional-value">
                    <?php 
                    // Special handling for wind (may contain HTML).
                    if ( 'wind' === $option || 'gust' === $option ) {
                        echo wp_kses_post( $value );
                    } else {
                        echo esc_html( $value );
                    }
                    ?>
                    <?php if ( ! empty( $unit ) && 'wind' !== $option && 'gust' !== $option ) : ?>
                        <span class="pw-additional-unit"><?php echo esc_html( $unit ); ?></span>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
        
    <?php endforeach; ?>
    
</div>

<style>
/* Forecast Additional Data Styles */
.pw-forecast-additional-data {
    display: grid;
    grid-template-columns: repeat(var(--pw-forecast-additional-columns, 3), 1fr);
    gap: var(--pw-forecast-additional-gap, 12px);
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

/* Card Styles */
.pw-additional-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 12px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.pw-additional-card:hover {
    background: rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

/* Card Style Variants */
.pw-additional-style-bordered .pw-additional-card {
    border: 1px solid rgba(0, 0, 0, 0.08);
    background: transparent;
}

.pw-additional-style-elevated .pw-additional-card {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    background: #fff;
}

.pw-additional-style-compact .pw-additional-card {
    padding: 8px;
    flex-direction: row;
    justify-content: space-between;
    gap: 8px;
}

.pw-additional-style-compact .pw-additional-icon,
.pw-additional-style-compact .pw-additional-label,
.pw-additional-style-compact .pw-additional-value {
    display: inline-flex;
}

/* Icon */
.pw-additional-icon i {
    font-size: 20px;
    color: var(--pw-primary-color, #f26c0d);
}

/* Label */
.pw-additional-label {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 6px;
}

/* Value */
.pw-additional-value {
    font-size: 14px;
    font-weight: 600;
    margin-top: 4px;
}

.pw-additional-unit {
    font-size: 10px;
    font-weight: 400;
    margin-left: 2px;
    opacity: 0.6;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-forecast-additional-data {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .pw-additional-card {
        padding: 10px;
    }
    
    .pw-additional-icon i {
        font-size: 18px;
    }
    
    .pw-additional-value {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .pw-forecast-additional-data {
        grid-template-columns: repeat(1, 1fr);
    }
}
</style>

<?php
/**
 * Helper functions for forecast additional data.
 */

if ( ! function_exists( 'get_forecast_additional_label' ) ) {
    /**
     * Get label for forecast additional data option.
     *
     * @param string $option Option key.
     * @return string
     */
    function get_forecast_additional_label( $option ) {
        $labels = array(
            'precipitation'  => __( 'Precipitation', 'pearl-weather' ),
            'rain_chance'    => __( 'Rain Chance', 'pearl-weather' ),
            'rainchance'     => __( 'Rain Chance', 'pearl-weather' ),
            'wind'           => __( 'Wind', 'pearl-weather' ),
            'gust'           => __( 'Wind Gust', 'pearl-weather' ),
            'uv_index'       => __( 'UV Index', 'pearl-weather' ),
            'humidity'       => __( 'Humidity', 'pearl-weather' ),
            'pressure'       => __( 'Pressure', 'pearl-weather' ),
            'clouds'         => __( 'Clouds', 'pearl-weather' ),
            'snow'           => __( 'Snow', 'pearl-weather' ),
            'sunrise'        => __( 'Sunrise', 'pearl-weather' ),
            'sunset'         => __( 'Sunset', 'pearl-weather' ),
            'sunrise_time'   => __( 'Sunrise', 'pearl-weather' ),
            'sunset_time'    => __( 'Sunset', 'pearl-weather' ),
        );
        
        return isset( $labels[ $option ] ) ? $labels[ $option ] : ucfirst( str_replace( '_', ' ', $option ) );
    }
}

if ( ! function_exists( 'get_forecast_additional_icon_class' ) ) {
    /**
     * Get icon class for forecast additional data option.
     *
     * @param string $option Option key.
     * @return string
     */
    function get_forecast_additional_icon_class( $option ) {
        // Map options to icon classes.
        $icon_map = array(
            'precipitation'  => 'pw-icon-precipitation',
            'rain_chance'    => 'pw-icon-rain-chance',
            'rainchance'     => 'pw-icon-rain-chance',
            'wind'           => 'pw-icon-wind',
            'gust'           => 'pw-icon-gust',
            'uv_index'       => 'pw-icon-uv-index',
            'humidity'       => 'pw-icon-humidity',
            'pressure'       => 'pw-icon-pressure',
            'clouds'         => 'pw-icon-clouds',
            'snow'           => 'pw-icon-snow',
            'sunrise'        => 'pw-icon-sunrise',
            'sunset'         => 'pw-icon-sunset',
            'sunrise_time'   => 'pw-icon-sunrise',
            'sunset_time'    => 'pw-icon-sunset',
        );
        
        $icon = isset( $icon_map[ $option ] ) ? $icon_map[ $option ] : 'pw-icon-default';
        
        // Append icon set number if available.
        global $pw_icon_set_number;
        if ( ! empty( $pw_icon_set_number ) ) {
            $icon .= '-' . $pw_icon_set_number;
        }
        
        return $icon;
    }
}