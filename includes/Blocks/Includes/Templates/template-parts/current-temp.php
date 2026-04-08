<?php
/**
 * Current Temperature Template Part
 *
 * Displays the current temperature with unit and optional
 * feels-like temperature.
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
 * - $weather_units: Temperature unit ('metric' or 'imperial')
 * - $template: Template variant name
 * - $block_name: Block name
 */

// Check if temperature display is enabled.
$show_temperature = isset( $attributes['displayTemperature'] ) ? (bool) $attributes['displayTemperature'] : true;

if ( ! $show_temperature ) {
    return;
}

// Determine layout type (vertical or horizontal).
$vertical_templates = array( 'vertical-one', 'vertical-two', 'vertical-three', 'tabs-one', 'tabs-two' );
$is_vertical_layout = in_array( $template, $vertical_templates, true ) || 'grid' === $block_name;

// Get temperature value.
$temperature = isset( $weather_data['temperature'] ) ? $weather_data['temperature'] : ( isset( $weather_data['temp'] ) ? $weather_data['temp'] : '--' );

// Get temperature unit.
$unit = isset( $weather_units ) ? $weather_units : ( isset( $attributes['displayTemperatureUnit'] ) ? $attributes['displayTemperatureUnit'] : 'metric' );
$unit_symbol = 'metric' === $unit ? '°C' : '°F';

// Override with weather data unit if available.
if ( ! empty( $weather_data['temp_unit'] ) ) {
    $unit_symbol = $weather_data['temp_unit'];
}

// Optional: Show feels-like temperature.
$show_feels_like = isset( $attributes['showFeelsLike'] ) ? (bool) $attributes['showFeelsLike'] : false;
$feels_like = isset( $weather_data['feels_like'] ) ? $weather_data['feels_like'] : '';

// Optional: Show both units (Celsius and Fahrenheit).
$show_both_units = isset( $attributes['showBothUnits'] ) ? (bool) $attributes['showBothUnits'] : false;

// CSS classes.
$wrapper_classes = array(
    'pw-current-temperature',
    $is_vertical_layout ? 'pw-temp-vertical' : 'pw-temp-horizontal',
);

// Add custom class if provided.
if ( ! empty( $attributes['temperatureCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['temperatureCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     data-temperature="<?php echo esc_attr( $temperature ); ?>"
     data-unit="<?php echo esc_attr( $unit ); ?>">
    
    <!-- Main Temperature Display -->
    <div class="pw-temperature-main">
        
        <!-- Temperature Value -->
        <span class="pw-temp-value">
            <?php echo esc_html( $temperature ); ?>
        </span>
        
        <!-- Temperature Unit(s) -->
        <div class="pw-temp-units">
            
            <?php if ( $show_both_units ) : ?>
                <!-- Display both Celsius and Fahrenheit -->
                <div class="pw-both-units">
                    <?php
                    // Calculate Fahrenheit if currently showing Celsius, or vice versa.
                    $celsius = 'metric' === $unit ? $temperature : $this->convert_to_celsius( $temperature );
                    $fahrenheit = 'imperial' === $unit ? $temperature : $this->convert_to_fahrenheit( $temperature );
                    ?>
                    <span class="pw-unit-celsius <?php echo 'metric' === $unit ? 'pw-active' : ''; ?>">
                        <?php echo esc_html( $celsius ); ?>°C
                    </span>
                    <span class="pw-unit-separator">/</span>
                    <span class="pw-unit-fahrenheit <?php echo 'imperial' === $unit ? 'pw-active' : ''; ?>">
                        <?php echo esc_html( $fahrenheit ); ?>°F
                    </span>
                </div>
            <?php else : ?>
                <!-- Single unit display -->
                <span class="pw-temp-unit">
                    <?php echo esc_html( $unit_symbol ); ?>
                </span>
            <?php endif; ?>
            
        </div>
        
    </div>
    
    <!-- Feels Like Temperature (Optional) -->
    <?php if ( $show_feels_like && ! empty( $feels_like ) ) : ?>
        <div class="pw-feels-like">
            <span class="pw-feels-like-label">
                <?php esc_html_e( 'Feels like', 'pearl-weather' ); ?>
            </span>
            <span class="pw-feels-like-value">
                <?php echo esc_html( $feels_like ); ?><?php echo esc_html( $unit_symbol ); ?>
            </span>
        </div>
    <?php endif; ?>
    
    <!-- Temperature Trend Indicator (Optional) -->
    <?php if ( isset( $attributes['showTrendIndicator'] ) && $attributes['showTrendIndicator'] ) : ?>
        <?php
        $trend = isset( $weather_data['temp_trend'] ) ? $weather_data['temp_trend'] : 'stable';
        $trend_icon = '';
        $trend_class = '';
        
        if ( 'rising' === $trend ) {
            $trend_icon = '↑';
            $trend_class = 'pw-trend-rising';
        } elseif ( 'falling' === $trend ) {
            $trend_icon = '↓';
            $trend_class = 'pw-trend-falling';
        } else {
            $trend_icon = '→';
            $trend_class = 'pw-trend-stable';
        }
        ?>
        <div class="pw-temperature-trend <?php echo esc_attr( $trend_class ); ?>">
            <span class="pw-trend-icon"><?php echo esc_html( $trend_icon ); ?></span>
        </div>
    <?php endif; ?>
    
</div>

<?php
/**
 * Helper functions for temperature conversion.
 * These would normally be in a utilities class.
 */

if ( ! function_exists( 'convert_to_celsius' ) ) {
    /**
     * Convert Fahrenheit to Celsius.
     *
     * @param float $fahrenheit Temperature in Fahrenheit.
     * @return int
     */
    function convert_to_celsius( $fahrenheit ) {
        return round( ( $fahrenheit - 32 ) * 5 / 9 );
    }
}

if ( ! function_exists( 'convert_to_fahrenheit' ) ) {
    /**
     * Convert Celsius to Fahrenheit.
     *
     * @param float $celsius Temperature in Celsius.
     * @return int
     */
    function convert_to_fahrenheit( $celsius ) {
        return round( $celsius * 9 / 5 + 32 );
    }
}
?>

<style>
/* Current Temperature Styles */
.pw-current-temperature {
    display: inline-flex;
    align-items: baseline;
    gap: 4px;
}

/* Vertical Layout (stacked) */
.pw-temp-vertical {
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.pw-temp-vertical .pw-temperature-main {
    flex-direction: column;
    align-items: center;
}

/* Horizontal Layout (inline) */
.pw-temp-horizontal {
    flex-direction: row;
    align-items: baseline;
}

.pw-temperature-main {
    display: flex;
    align-items: baseline;
    gap: 4px;
    flex-wrap: wrap;
}

.pw-temp-value {
    font-size: 48px;
    font-weight: 700;
    line-height: 1.2;
}

.pw-temp-unit {
    font-size: 20px;
    font-weight: 500;
}

/* Both Units Display */
.pw-both-units {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
}

.pw-unit-celsius,
.pw-unit-fahrenheit {
    opacity: 0.6;
    transition: opacity 0.2s ease;
}

.pw-unit-celsius.pw-active,
.pw-unit-fahrenheit.pw-active {
    opacity: 1;
    font-weight: 600;
}

.pw-unit-separator {
    opacity: 0.5;
}

/* Feels Like */
.pw-feels-like {
    font-size: 14px;
    opacity: 0.7;
    margin-top: 4px;
}

.pw-feels-like-label {
    margin-right: 4px;
}

/* Trend Indicator */
.pw-temperature-trend {
    display: inline-flex;
    margin-left: 8px;
    font-size: 18px;
}

.pw-trend-rising {
    color: #e74c3c;
}

.pw-trend-falling {
    color: #3498db;
}

.pw-trend-stable {
    color: #95a5a6;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-temp-value {
        font-size: 36px;
    }
    
    .pw-temp-unit {
        font-size: 16px;
    }
    
    .pw-both-units {
        font-size: 12px;
    }
}
</style>