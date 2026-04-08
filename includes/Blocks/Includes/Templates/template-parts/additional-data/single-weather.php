<?php
/**
 * Single Additional Weather Data Item Template
 *
 * Renders a single weather data item (humidity, pressure, wind, etc.)
 * with icon, label, and formatted value.
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
 * - $option: Current data option key (humidity, pressure, wind, etc.)
 * - $is_swiper_layout: Whether this is a carousel layout
 * - $show_additional_data_icon: Whether to show icons
 * - $display_colon: Whether to show colon after label
 * - $data_layout: Current layout type
 */

// Validate required variables.
$option_key = isset( $option ) ? $option : ( isset( $value ) ? $value : '' );

if ( empty( $option_key ) ) {
    return;
}

// Get the value for this option.
$raw_value = isset( $weather_data[ $option_key ] ) ? $weather_data[ $option_key ] : '';
$formatted_value = '';
$unit = '';
$value_class = '';

// Format based on option type.
switch ( $option_key ) {
    case 'humidity':
        if ( is_array( $raw_value ) ) {
            $formatted_value = isset( $raw_value['value'] ) ? $raw_value['value'] : '';
            $unit = isset( $raw_value['unit'] ) ? $raw_value['unit'] : '%';
        } else {
            $formatted_value = $raw_value;
            $unit = '%';
        }
        break;
        
    case 'pressure':
        $formatted_value = $raw_value;
        $unit = isset( $weather_data['pressure_unit'] ) ? $weather_data['pressure_unit'] : 'hPa';
        break;
        
    case 'wind':
        $formatted_value = $raw_value;
        $value_class = 'pw-wind-value';
        break;
        
    case 'gust':
        $formatted_value = $raw_value;
        $value_class = 'pw-gust-value';
        break;
        
    case 'visibility':
        $formatted_value = $raw_value;
        $unit = isset( $weather_data['visibility_unit'] ) ? $weather_data['visibility_unit'] : 'km';
        break;
        
    case 'clouds':
        $formatted_value = $raw_value;
        $unit = '%';
        break;
        
    case 'uv_index':
        $formatted_value = $raw_value;
        break;
        
    case 'dew_point':
        $formatted_value = $raw_value;
        $unit = isset( $weather_data['temp_unit'] ) ? $weather_data['temp_unit'] : '°C';
        break;
        
    case 'precipitation':
        $formatted_value = $raw_value;
        $unit = isset( $weather_data['precipitation_unit'] ) ? $weather_data['precipitation_unit'] : 'mm';
        break;
        
    case 'rain_chance':
        $formatted_value = $raw_value;
        $unit = '%';
        break;
        
    case 'sunrise':
    case 'sunset':
    case 'sunrise_time':
    case 'sunset_time':
        $formatted_value = $raw_value;
        break;
        
    default:
        $formatted_value = $raw_value;
        break;
}

// Skip if no value.
if ( empty( $formatted_value ) && '0' !== $formatted_value ) {
    return;
}

// Get label and icon.
$label = get_additional_data_label( $option_key );
$icon = get_additional_data_icon( $option_key );

// Check if icon should be shown.
$show_icon = isset( $show_additional_data_icon ) ? (bool) $show_additional_data_icon : true;

// Check if colon should be shown.
$show_colon = isset( $display_colon ) ? (bool) $display_colon : false;
$colon = $show_colon ? ':' : '';

// CSS classes.
$wrapper_classes = array( 'pw-data-item', 'pw-data-' . $option_key );

if ( isset( $is_swiper_layout ) && $is_swiper_layout ) {
    $wrapper_classes[] = 'swiper-slide';
}

if ( ! empty( $value_class ) ) {
    $wrapper_classes[] = $value_class;
}

if ( ! empty( $attributes['additionalDataItemCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['additionalDataItemCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     data-option="<?php echo esc_attr( $option_key ); ?>"
     title="<?php echo esc_attr( $label ); ?>">
    
    <!-- Label and Icon Wrapper -->
    <div class="pw-data-label-wrapper">
        
        <!-- Icon -->
        <?php if ( $show_icon && ! empty( $icon ) ) : ?>
            <span class="pw-data-icon" aria-hidden="true">
                <?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </span>
        <?php endif; ?>
        
        <!-- Label -->
        <span class="pw-data-label">
            <?php echo esc_html( $label ); ?><?php echo esc_html( $colon ); ?>
        </span>
        
    </div>
    
    <!-- Value -->
    <div class="pw-data-value-wrapper">
        <span class="pw-data-value">
            <?php 
            // Special handling for wind (may contain HTML).
            if ( 'wind' === $option_key || 'gust' === $option_key ) {
                echo wp_kses_post( $formatted_value );
            } else {
                echo esc_html( $formatted_value );
            }
            ?>
        </span>
        
        <!-- Unit -->
        <?php if ( ! empty( $unit ) && 'wind' !== $option_key && 'gust' !== $option_key ) : ?>
            <span class="pw-data-unit"><?php echo esc_html( $unit ); ?></span>
        <?php endif; ?>
    </div>
    
</div>

<style>
/* Single Weather Data Item Styles */
.pw-data-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 12px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.pw-data-item:hover {
    background: rgba(0, 0, 0, 0.05);
    transform: translateY(-1px);
}

/* Label Wrapper */
.pw-data-label-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.pw-data-icon {
    display: inline-flex;
    align-items: center;
    line-height: 0;
}

.pw-data-icon svg {
    width: 18px;
    height: 18px;
    opacity: 0.7;
}

.pw-data-label {
    font-size: 13px;
    opacity: 0.7;
}

/* Value Wrapper */
.pw-data-value-wrapper {
    display: flex;
    align-items: baseline;
    gap: 4px;
    flex-shrink: 0;
}

.pw-data-value {
    font-size: 15px;
    font-weight: 600;
}

.pw-data-unit {
    font-size: 11px;
    opacity: 0.6;
}

/* Wind Specific */
.pw-wind-value .pw-data-value {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Swiper Slide Overrides */
.swiper-slide .pw-data-item {
    height: 100%;
    flex-direction: column;
    text-align: center;
    justify-content: center;
}

.swiper-slide .pw-data-label-wrapper {
    flex-direction: column;
}

.swiper-slide .pw-data-value-wrapper {
    justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-data-item {
        padding: 6px 10px;
    }
    
    .pw-data-label {
        font-size: 11px;
    }
    
    .pw-data-value {
        font-size: 13px;
    }
    
    .pw-data-icon svg {
        width: 16px;
        height: 16px;
    }
}

/* Animation */
.pw-data-item {
    animation: pw-fade-in 0.3s ease forwards;
}

@keyframes pw-fade-in {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php
/**
 * Helper function to get additional data label (if not already defined).
 */
if ( ! function_exists( 'get_additional_data_label' ) ) {
    /**
     * Get label for additional data option.
     *
     * @param string $option Option key.
     * @return string
     */
    function get_additional_data_label( $option ) {
        $labels = array(
            'humidity'       => __( 'Humidity', 'pearl-weather' ),
            'pressure'       => __( 'Pressure', 'pearl-weather' ),
            'wind'           => __( 'Wind', 'pearl-weather' ),
            'gust'           => __( 'Wind Gust', 'pearl-weather' ),
            'visibility'     => __( 'Visibility', 'pearl-weather' ),
            'clouds'         => __( 'Clouds', 'pearl-weather' ),
            'uv_index'       => __( 'UV Index', 'pearl-weather' ),
            'dew_point'      => __( 'Dew Point', 'pearl-weather' ),
            'precipitation'  => __( 'Precipitation', 'pearl-weather' ),
            'rain_chance'    => __( 'Rain Chance', 'pearl-weather' ),
            'sunrise'        => __( 'Sunrise', 'pearl-weather' ),
            'sunset'         => __( 'Sunset', 'pearl-weather' ),
            'sunrise_time'   => __( 'Sunrise', 'pearl-weather' ),
            'sunset_time'    => __( 'Sunset', 'pearl-weather' ),
            'feels_like'     => __( 'Feels Like', 'pearl-weather' ),
        );
        
        return isset( $labels[ $option ] ) ? $labels[ $option ] : ucfirst( str_replace( '_', ' ', $option ) );
    }
}

/**
 * Helper function to get additional data icon (if not already defined).
 */
if ( ! function_exists( 'get_additional_data_icon' ) ) {
    /**
     * Get SVG icon for additional data option.
     *
     * @param string $option Option key.
     * @return string
     */
    function get_additional_data_icon( $option ) {
        $icons = array(
            'humidity' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M12 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor"/></svg>',
            'pressure' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M12 8v4l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'wind' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h15a3 3 0 1 0-3-3M3 8h9M3 16h12a3 3 0 1 1-3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
            'gust' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h12a3 3 0 1 0-3-3M3 8h6M3 16h9a3 3 0 1 1-3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M18 12L21 15L18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'visibility' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
            'clouds' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 10C18 6.686 15.314 4 12 4C8.686 4 6 6.686 6 10C6 10.548 6.07 11.08 6.2 11.59" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="M17 16C19.209 16 21 14.209 21 12C21 9.791 19.209 8 17 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="M7 16C4.791 16 3 14.209 3 12C3 9.791 4.791 8 7 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>',
            'sunrise' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 16h16M4 20h16M12 4v4m-4-2 4-4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="14" r="3" stroke="currentColor" stroke-width="1.5"/></svg>',
            'sunset' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 16h16M4 20h16M12 4v4m-4-2 4-4 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="14" r="3" stroke="currentColor" stroke-width="1.5"/></svg>',
        );
        
        return isset( $icons[ $option ] ) ? $icons[ $option ] : '';
    }
}