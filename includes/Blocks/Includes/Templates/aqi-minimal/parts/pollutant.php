<?php
/**
 * AQI Pollutant Details Template Part
 *
 * Displays detailed pollutant information including PM2.5, PM10,
 * NO2, O3, CO, and SO2 with their values and condition indicators.
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
 * - $current_aqi_data: Current AQI data object/array
 * - $aqi_data: Air quality data array
 */

// Get pollutant data definitions.
$pollutants = get_pollutant_definitions();

// Display settings.
$show_indicator = isset( $attributes['enablePollutantIndicator'] ) ? (bool) $attributes['enablePollutantIndicator'] : true;
$show_units = isset( $attributes['enablePollutantMeasurementUnit'] ) ? (bool) $attributes['enablePollutantMeasurementUnit'] : true;
$symbol_style = isset( $attributes['displaySymbolDisplayStyle'] ) ? sanitize_text_field( $attributes['displaySymbolDisplayStyle'] ) : 'subscript';
$name_format = isset( $attributes['displayPollutantNameFormat'] ) ? sanitize_text_field( $attributes['displayPollutantNameFormat'] ) : 'abbreviation';
$layout = isset( $attributes['pollutantLayout'] ) ? sanitize_text_field( $attributes['pollutantLayout'] ) : 'grid';
$columns = isset( $attributes['pollutantColumns'] ) ? (int) $attributes['pollutantColumns'] : 2;

// CSS classes.
$wrapper_classes = array( 'pw-pollutant-details' );
$wrapper_classes[] = 'pw-pollutant-layout-' . $layout;

if ( 'grid' === $layout ) {
    $wrapper_classes[] = 'pw-pollutant-cols-' . $columns;
}

if ( ! empty( $attributes['pollutantCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['pollutantCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    
    <!-- Pollutant Header (optional) -->
    <?php if ( isset( $attributes['showPollutantHeader'] ) && $attributes['showPollutantHeader'] ) : ?>
        <div class="pw-pollutant-header">
            <h5 class="pw-pollutant-title"><?php esc_html_e( 'Pollutant Details', 'pearl-weather' ); ?></h5>
            <?php if ( isset( $attributes['showPollutantDescription'] ) && $attributes['showPollutantDescription'] ) : ?>
                <p class="pw-pollutant-description"><?php esc_html_e( 'Concentration levels of key air pollutants', 'pearl-weather' ); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Pollutant List -->
    <div class="pw-pollutant-list">
        <?php foreach ( $pollutants as $pollutant ) : 
            $key = $pollutant['key'];
            $label = format_pollutant_display_name( $pollutant['name'], $pollutant['symbol'], $name_format, $symbol_style );
            
            // Get pollutant value from data.
            $value = 0;
            if ( is_object( $current_aqi_data ) && isset( $current_aqi_data->$key ) ) {
                $value = (float) $current_aqi_data->$key;
            } elseif ( is_array( $current_aqi_data ) && isset( $current_aqi_data[ $key ] ) ) {
                $value = (float) $current_aqi_data[ $key ];
            } elseif ( isset( $aqi_data['pollutants'][ $key ] ) ) {
                $value = (float) $aqi_data['pollutants'][ $key ];
            }
            
            // Skip if value is 0 or invalid.
            if ( $value <= 0 ) {
                continue;
            }
            
            // Get pollutant AQI data.
            $pollutant_data = get_pollutant_aqi_data( $value, $key );
            $condition = $pollutant_data['condition'];
            $color = get_aqi_color( $condition );
            $condition_label = ucfirst( $condition );
            $description = get_pollutant_description( $key, $value );
            
            // Determine value class based on condition.
            $value_class = 'pw-pollutant-value-' . $condition;
            ?>
            
            <div class="pw-pollutant-item pw-pollutant-<?php echo esc_attr( $key ); ?>"
                 data-pollutant="<?php echo esc_attr( $key ); ?>"
                 data-value="<?php echo esc_attr( $value ); ?>"
                 data-condition="<?php echo esc_attr( $condition ); ?>"
                 style="--pw-pollutant-color: <?php echo esc_attr( $color ); ?>">
                
                <!-- Pollutant Label with Indicator -->
                <div class="pw-pollutant-info">
                    <div class="pw-pollutant-label-wrapper">
                        <?php if ( $show_indicator ) : ?>
                            <span class="pw-pollutant-indicator" style="background: <?php echo esc_attr( $color ); ?>"></span>
                        <?php endif; ?>
                        <span class="pw-pollutant-label" title="<?php echo esc_attr( $pollutant['name'] ); ?>">
                            <?php echo wp_kses_post( $label ); ?>
                        </span>
                    </div>
                    
                    <!-- Pollutant Value -->
                    <div class="pw-pollutant-value-wrapper">
                        <span class="pw-pollutant-value <?php echo esc_attr( $value_class ); ?>">
                            <?php echo esc_html( round( $value, 1 ) ); ?>
                        </span>
                        <?php if ( $show_units ) : ?>
                            <span class="pw-pollutant-unit"><?php echo esc_html( $pollutant['unit'] ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Condition Badge (for list layout) -->
                <?php if ( 'list' === $layout ) : ?>
                    <div class="pw-pollutant-condition-badge" style="background: <?php echo esc_attr( $color ); ?>20; color: <?php echo esc_attr( $color ); ?>">
                        <?php echo esc_html( $condition_label ); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Progress Bar (for list layout) -->
                <?php if ( 'list' === $layout && isset( $attributes['showPollutantProgressBar'] ) && $attributes['showPollutantProgressBar'] ) : ?>
                    <div class="pw-pollutant-progress-bar">
                        <div class="pw-pollutant-progress-fill" style="width: <?php echo esc_attr( min( 100, ( $value / 100 ) * 100 ) ); ?>%; background: <?php echo esc_attr( $color ); ?>"></div>
                    </div>
                <?php endif; ?>
                
                <!-- Tooltip with description (optional) -->
                <?php if ( isset( $attributes['showPollutantTooltip'] ) && $attributes['showPollutantTooltip'] && ! empty( $description ) ) : ?>
                    <div class="pw-pollutant-tooltip">
                        <span class="pw-tooltip-icon" aria-label="<?php echo esc_attr( $description ); ?>">ⓘ</span>
                        <span class="pw-tooltip-text"><?php echo esc_html( $description ); ?></span>
                    </div>
                <?php endif; ?>
                
            </div>
        <?php endforeach; ?>
    </div>
    
</div>

<style>
/* Pollutant Details Styles */
.pw-pollutant-details {
    margin-top: 16px;
}

/* Grid Layout */
.pw-pollutant-layout-grid .pw-pollutant-list {
    display: grid;
    gap: 12px;
}

.pw-pollutant-cols-1 .pw-pollutant-list {
    grid-template-columns: repeat(1, 1fr);
}

.pw-pollutant-cols-2 .pw-pollutant-list {
    grid-template-columns: repeat(2, 1fr);
}

.pw-pollutant-cols-3 .pw-pollutant-list {
    grid-template-columns: repeat(3, 1fr);
}

.pw-pollutant-cols-4 .pw-pollutant-list {
    grid-template-columns: repeat(4, 1fr);
}

/* List Layout */
.pw-pollutant-layout-list .pw-pollutant-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Pollutant Item */
.pw-pollutant-item {
    background: rgba(0, 0, 0, 0.02);
    border-radius: 8px;
    padding: 12px;
    transition: all 0.2s ease;
}

.pw-pollutant-item:hover {
    background: rgba(0, 0, 0, 0.04);
    transform: translateY(-1px);
}

/* Grid Layout Item */
.pw-pollutant-layout-grid .pw-pollutant-item {
    text-align: center;
}

.pw-pollutant-layout-grid .pw-pollutant-info {
    flex-direction: column;
    gap: 6px;
    text-align: center;
}

.pw-pollutant-layout-grid .pw-pollutant-label-wrapper {
    justify-content: center;
}

/* List Layout Item */
.pw-pollutant-layout-list .pw-pollutant-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

/* Label Wrapper */
.pw-pollutant-label-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

/* Indicator */
.pw-pollutant-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

/* Label */
.pw-pollutant-label {
    font-size: 13px;
    font-weight: 500;
}

/* Value Wrapper */
.pw-pollutant-value-wrapper {
    display: inline-flex;
    align-items: baseline;
    gap: 4px;
}

/* Value */
.pw-pollutant-value {
    font-size: 15px;
    font-weight: 600;
}

/* Value Colors by Condition */
.pw-pollutant-value-good { color: #00B150; }
.pw-pollutant-value-moderate { color: #EEC631; }
.pw-pollutant-value-poor { color: #EA8B34; }
.pw-pollutant-value-unhealthy { color: #E95378; }
.pw-pollutant-value-severe { color: #B33FB9; }
.pw-pollutant-value-hazardous { color: #C91F33; }

/* Unit */
.pw-pollutant-unit {
    font-size: 10px;
    opacity: 0.6;
}

/* Condition Badge */
.pw-pollutant-condition-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 500;
    margin-top: 6px;
}

/* Progress Bar */
.pw-pollutant-progress-bar {
    height: 4px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 8px;
}

.pw-pollutant-progress-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s ease;
}

/* Tooltip */
.pw-pollutant-tooltip {
    position: relative;
    display: inline-flex;
    margin-left: 6px;
}

.pw-tooltip-icon {
    cursor: help;
    font-size: 12px;
    opacity: 0.5;
}

.pw-tooltip-text {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: #fff;
    font-size: 10px;
    padding: 4px 8px;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    z-index: 10;
}

.pw-pollutant-tooltip:hover .pw-tooltip-text {
    opacity: 1;
    visibility: visible;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-pollutant-cols-2,
    .pw-pollutant-cols-3,
    .pw-pollutant-cols-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .pw-pollutant-cols-2,
    .pw-pollutant-cols-3,
    .pw-pollutant-cols-4 {
        grid-template-columns: repeat(1, 1fr);
    }
}
</style>

<?php
/**
 * Helper functions for pollutant display.
 */
if ( ! function_exists( 'get_pollutant_definitions' ) ) {
    /**
     * Get pollutant definitions with keys, names, symbols, and units.
     *
     * @return array
     */
    function get_pollutant_definitions() {
        return array(
            array(
                'key'    => 'pm2_5',
                'name'   => __( 'Particulate Matter 2.5', 'pearl-weather' ),
                'symbol' => 'PM2.5',
                'unit'   => 'µg/m³',
            ),
            array(
                'key'    => 'pm10',
                'name'   => __( 'Particulate Matter 10', 'pearl-weather' ),
                'symbol' => 'PM10',
                'unit'   => 'µg/m³',
            ),
            array(
                'key'    => 'no2',
                'name'   => __( 'Nitrogen Dioxide', 'pearl-weather' ),
                'symbol' => 'NO₂',
                'unit'   => 'µg/m³',
            ),
            array(
                'key'    => 'o3',
                'name'   => __( 'Ozone', 'pearl-weather' ),
                'symbol' => 'O₃',
                'unit'   => 'µg/m³',
            ),
            array(
                'key'    => 'co',
                'name'   => __( 'Carbon Monoxide', 'pearl-weather' ),
                'symbol' => 'CO',
                'unit'   => 'mg/m³',
            ),
            array(
                'key'    => 'so2',
                'name'   => __( 'Sulfur Dioxide', 'pearl-weather' ),
                'symbol' => 'SO₂',
                'unit'   => 'µg/m³',
            ),
        );
    }
}

if ( ! function_exists( 'format_pollutant_display_name' ) ) {
    /**
     * Format pollutant display name based on settings.
     *
     * @param string $name        Full name.
     * @param string $symbol      Symbol/abbreviation.
     * @param string $format      Format ('abbreviation', 'name', 'both').
     * @param string $style       Style for symbols ('subscript' or '').
     * @return string
     */
    function format_pollutant_display_name( $name, $symbol, $format = 'abbreviation', $style = 'subscript' ) {
        if ( 'subscript' === $style ) {
            $symbol = convert_to_subscript( $symbol );
        }
        
        switch ( $format ) {
            case 'abbreviation':
                return $symbol;
            case 'name':
                return $name;
            case 'both':
                return sprintf( '%s (%s)', $name, $symbol );
            default:
                return $symbol;
        }
    }
}

if ( ! function_exists( 'convert_to_subscript' ) ) {
    /**
     * Convert numbers in string to subscript.
     *
     * @param string $string Input string.
     * @return string
     */
    function convert_to_subscript( $string ) {
        $subscripts = array(
            '0' => '₀',
            '1' => '₁',
            '2' => '₂',
            '3' => '₃',
            '4' => '₄',
            '5' => '₅',
            '6' => '₆',
            '7' => '₇',
            '8' => '₈',
            '9' => '₉',
        );
        return str_replace( array_keys( $subscripts ), array_values( $subscripts ), $string );
    }
}

if ( ! function_exists( 'get_pollutant_aqi_data' ) ) {
    /**
     * Get AQI data for a specific pollutant.
     *
     * @param float  $value    Pollutant concentration.
     * @param string $pollutant Pollutant key.
     * @return array
     */
    function get_pollutant_aqi_data( $value, $pollutant ) {
        $breakpoints = array(
            'pm2_5' => array( 0, 10, 25, 50, 75, 100, INF ),
            'pm10'  => array( 0, 20, 50, 100, 200, 250, INF ),
            'no2'   => array( 0, 40, 70, 150, 200, 250, INF ),
            'o3'    => array( 0, 60, 100, 140, 180, 220, INF ),
            'co'    => array( 0, 4400, 9400, 12400, 15400, 18000, INF ),
            'so2'   => array( 0, 20, 80, 250, 350, 400, INF ),
        );
        
        $levels = isset( $breakpoints[ $pollutant ] ) ? $breakpoints[ $pollutant ] : array( 0, 10, 25, 50, 75, 100, INF );
        $conditions = array( 'good', 'moderate', 'poor', 'unhealthy', 'severe', 'hazardous' );
        
        for ( $i = 0; $i < count( $levels ) - 1; $i++ ) {
            if ( $value >= $levels[ $i ] && $value < $levels[ $i + 1 ] ) {
                return array(
                    'condition' => $conditions[ $i ],
                    'level'     => $i,
                );
            }
        }
        
        return array( 'condition' => 'hazardous', 'level' => 5 );
    }
}

if ( ! function_exists( 'get_pollutant_description' ) ) {
    /**
     * Get description for a pollutant based on its value.
     *
     * @param string $pollutant Pollutant key.
     * @param float  $value     Pollutant value.
     * @return string
     */
    function get_pollutant_description( $pollutant, $value ) {
        $descriptions = array(
            'pm2_5' => array(
                'low'    => __( 'Fine particulate matter concentration is at a healthy level.', 'pearl-weather' ),
                'medium' => __( 'Fine particulate matter may cause issues for sensitive individuals.', 'pearl-weather' ),
                'high'   => __( 'Fine particulate matter is elevated. Limit outdoor exposure.', 'pearl-weather' ),
            ),
            'pm10' => array(
                'low'    => __( 'Coarse particulate matter is within healthy limits.', 'pearl-weather' ),
                'medium' => __( 'Coarse particulate matter may irritate respiratory systems.', 'pearl-weather' ),
                'high'   => __( 'Coarse particulate matter is high. Reduce outdoor activity.', 'pearl-weather' ),
            ),
        );
        
        if ( $value < 50 ) {
            return isset( $descriptions[ $pollutant ]['low'] ) ? $descriptions[ $pollutant ]['low'] : '';
        } elseif ( $value < 100 ) {
            return isset( $descriptions[ $pollutant ]['medium'] ) ? $descriptions[ $pollutant ]['medium'] : '';
        }
        return isset( $descriptions[ $pollutant ]['high'] ) ? $descriptions[ $pollutant ]['high'] : '';
    }
}