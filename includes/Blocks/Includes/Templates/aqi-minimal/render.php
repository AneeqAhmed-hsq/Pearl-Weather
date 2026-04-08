<?php
/**
 * Air Quality Index (AQI) Minimal Card Template
 *
 * Displays air quality data including AQI summary, pollutant details,
 * last updated time, and attribution.
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
 * - $aqi_data: Air quality data array
 * - $weather_data: Current weather data array
 * - $template: Template variant name
 * - $unique_id: Unique block identifier
 */

// Check if AQI data is available.
if ( empty( $aqi_data ) || ! is_array( $aqi_data ) ) {
    // Display message if AQI data is not available.
    ?>
    <div class="pw-aqi-unavailable">
        <p><?php esc_html_e( 'Air quality data is not available for this location.', 'pearl-weather' ); ?></p>
    </div>
    <?php
    return;
}

// AQI summary settings.
$show_summary = isset( $attributes['enableAqiSummary'] ) ? (bool) $attributes['enableAqiSummary'] : true;
$summary_heading = isset( $attributes['aqiSummaryHeadingLabel'] ) 
    ? sanitize_text_field( $attributes['aqiSummaryHeadingLabel'] ) 
    : __( 'Today\'s Air Quality Index (AQI)', 'pearl-weather' );
$show_condition = isset( $attributes['enableSummaryAqiCondition'] ) ? (bool) $attributes['enableSummaryAqiCondition'] : true;
$show_description = isset( $attributes['enableSummaryAqiDesc'] ) ? (bool) $attributes['enableSummaryAqiDesc'] : true;
$show_scale_bar = isset( $attributes['enableScaleBar'] ) ? (bool) $attributes['enableScaleBar'] : true;
$aqi_card_style = isset( $attributes['aqiCardStyle'] ) ? sanitize_text_field( $attributes['aqiCardStyle'] ) : 'default';

// Pollutant settings.
$show_pollutants = isset( $attributes['enablePollutantDetails'] ) ? (bool) $attributes['enablePollutantDetails'] : true;
$pollutant_layout = isset( $attributes['aqiForecastStyle'] ) ? sanitize_text_field( $attributes['aqiForecastStyle'] ) : 'list';
$pollutant_columns = isset( $attributes['pollutantColumns'] ) ? (int) $attributes['pollutantColumns'] : 2;
$show_pollutant_unit = isset( $attributes['enablePollutantMeasurementUnit'] ) ? (bool) $attributes['enablePollutantMeasurementUnit'] : true;
$show_pollutant_indicator = isset( $attributes['enablePollutantIndicator'] ) ? (bool) $attributes['enablePollutantIndicator'] : true;
$pollutant_name_format = isset( $attributes['displayPollutantNameFormat'] ) ? sanitize_text_field( $attributes['displayPollutantNameFormat'] ) : 'both';

// Last update settings.
$show_last_update = isset( $attributes['displayDateUpdateTime'] ) ? (bool) $attributes['displayDateUpdateTime'] : true;
$updated_time = isset( $weather_data['updated_time'] ) ? $weather_data['updated_time'] : '';

// Extract AQI data.
$aqi_value = isset( $aqi_data['iaqi'] ) ? (int) $aqi_data['iaqi'] : ( isset( $aqi_data['aqi'] ) ? (int) $aqi_data['aqi'] : 0 );
$aqi_condition = isset( $aqi_data['condition'] ) ? $aqi_data['condition'] : get_aqi_condition_from_value( $aqi_value );
$aqi_color = get_aqi_color( $aqi_condition );
$aqi_description = isset( $aqi_data['report'] ) ? $aqi_data['report'] : get_aqi_description( $aqi_condition );
$aqi_detailed_desc = isset( $aqi_data['detailed_report'] ) ? $aqi_data['detailed_report'] : '';

// Pollutant data.
$pollutants = isset( $aqi_data['pollutants'] ) ? $aqi_data['pollutants'] : array();
$pollutant_items = array(
    'pm2_5' => array( 'name' => 'PM2.5', 'unit' => 'µg/m³' ),
    'pm10'  => array( 'name' => 'PM10', 'unit' => 'µg/m³' ),
    'no2'   => array( 'name' => 'NO₂', 'unit' => 'µg/m³' ),
    'o3'    => array( 'name' => 'O₃', 'unit' => 'µg/m³' ),
    'co'    => array( 'name' => 'CO', 'unit' => 'mg/m³' ),
    'so2'   => array( 'name' => 'SO₂', 'unit' => 'µg/m³' ),
);

// CSS classes.
$wrapper_classes = array( 'pw-aqi-card', 'pw-aqi-style-' . $aqi_card_style );

if ( ! empty( $attributes['aqiCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['aqiCustomClass'] );
}

$pollutant_classes = array( 'pw-pollutant-list' );
$pollutant_classes[] = 'pw-pollutant-layout-' . $pollutant_layout;

if ( 'grid' === $pollutant_layout ) {
    $pollutant_classes[] = 'pw-pollutant-grid-cols-' . $pollutant_columns;
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     data-aqi-value="<?php echo esc_attr( $aqi_value ); ?>"
     data-aqi-condition="<?php echo esc_attr( $aqi_condition ); ?>"
     style="--pw-aqi-color: <?php echo esc_attr( $aqi_color ); ?>">
    
    <!-- AQI Summary Section -->
    <?php if ( $show_summary ) : ?>
        <div class="pw-aqi-summary">
            
            <!-- Heading -->
            <h4 class="pw-aqi-heading"><?php echo esc_html( $summary_heading ); ?></h4>
            
            <!-- AQI Value and Condition -->
            <div class="pw-aqi-value-container">
                <div class="pw-aqi-value" style="color: <?php echo esc_attr( $aqi_color ); ?>">
                    <?php echo esc_html( $aqi_value ); ?>
                </div>
                
                <?php if ( $show_condition ) : ?>
                    <div class="pw-aqi-condition" style="background: <?php echo esc_attr( $aqi_color ); ?>20; border-color: <?php echo esc_attr( $aqi_color ); ?>">
                        <?php echo esc_html( ucfirst( $aqi_condition ) ); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Scale Bar -->
            <?php if ( $show_scale_bar ) : ?>
                <div class="pw-aqi-scale-bar">
                    <div class="pw-scale-segment pw-scale-good" style="width: 16.6%"></div>
                    <div class="pw-scale-segment pw-scale-moderate" style="width: 16.6%"></div>
                    <div class="pw-scale-segment pw-scale-poor" style="width: 16.6%"></div>
                    <div class="pw-scale-segment pw-scale-unhealthy" style="width: 16.6%"></div>
                    <div class="pw-scale-segment pw-scale-severe" style="width: 16.6%"></div>
                    <div class="pw-scale-segment pw-scale-hazardous" style="width: 16.6%"></div>
                    <div class="pw-scale-indicator" style="left: <?php echo esc_attr( min( 100, ( $aqi_value / 300 ) * 100 ) ); ?>%"></div>
                </div>
                <div class="pw-scale-labels">
                    <span>Good</span>
                    <span>Moderate</span>
                    <span>Poor</span>
                    <span>Unhealthy</span>
                    <span>Severe</span>
                    <span>Hazardous</span>
                </div>
            <?php endif; ?>
            
            <!-- Description -->
            <?php if ( $show_description && ! empty( $aqi_description ) ) : ?>
                <div class="pw-aqi-description">
                    <?php echo esc_html( $aqi_description ); ?>
                </div>
            <?php endif; ?>
            
            <!-- Detailed Description (optional) -->
            <?php if ( ! empty( $aqi_detailed_desc ) && isset( $attributes['showDetailedDescription'] ) && $attributes['showDetailedDescription'] ) : ?>
                <div class="pw-aqi-detailed-description">
                    <?php echo esc_html( $aqi_detailed_desc ); ?>
                </div>
            <?php endif; ?>
            
        </div>
    <?php endif; ?>
    
    <!-- Pollutant Details Section -->
    <?php if ( $show_pollutants && ! empty( $pollutants ) ) : ?>
        <div class="pw-aqi-pollutants">
            
            <!-- Pollutant Heading -->
            <div class="pw-pollutant-header">
                <h5 class="pw-pollutant-title"><?php esc_html_e( 'Pollutant Details', 'pearl-weather' ); ?></h5>
            </div>
            
            <!-- Pollutant List -->
            <div class="<?php echo esc_attr( implode( ' ', $pollutant_classes ) ); ?>">
                <?php foreach ( $pollutant_items as $key => $item ) : ?>
                    <?php
                    $value = isset( $pollutants[ $key ] ) ? (float) $pollutants[ $key ] : 0;
                    
                    if ( $value <= 0 ) {
                        continue;
                    }
                    
                    $pollutant_aqi = get_pollutant_aqi( $value, $key );
                    $pollutant_color = get_aqi_color( $pollutant_aqi['condition'] );
                    $display_name = format_pollutant_name( $item['name'], $item['unit'], $pollutant_name_format, $show_pollutant_unit );
                    ?>
                    
                    <div class="pw-pollutant-item" data-pollutant="<?php echo esc_attr( $key ); ?>">
                        <div class="pw-pollutant-info">
                            <div class="pw-pollutant-name-wrapper">
                                <?php if ( $show_pollutant_indicator ) : ?>
                                    <span class="pw-pollutant-indicator" style="background: <?php echo esc_attr( $pollutant_color ); ?>"></span>
                                <?php endif; ?>
                                <span class="pw-pollutant-name"><?php echo wp_kses_post( $display_name ); ?></span>
                            </div>
                            <div class="pw-pollutant-value-wrapper">
                                <span class="pw-pollutant-value"><?php echo esc_html( round( $value, 1 ) ); ?></span>
                                <?php if ( $show_pollutant_unit ) : ?>
                                    <span class="pw-pollutant-unit"><?php echo esc_html( $item['unit'] ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ( 'list' === $pollutant_layout ) : ?>
                            <div class="pw-pollutant-bar">
                                <div class="pw-pollutant-progress" style="width: <?php echo esc_attr( min( 100, ( $value / 100 ) * 100 ) ); ?>%; background: <?php echo esc_attr( $pollutant_color ); ?>"></div>
                            </div>
                            <div class="pw-pollutant-condition"><?php echo esc_html( ucfirst( $pollutant_aqi['condition'] ) ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
        </div>
    <?php endif; ?>
    
    <!-- Last Updated Time -->
    <?php if ( $show_last_update && ! empty( $updated_time ) ) : ?>
        <div class="pw-aqi-last-updated">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span class="pw-updated-label"><?php esc_html_e( 'Last updated:', 'pearl-weather' ); ?></span>
            <span class="pw-updated-time"><?php echo esc_html( $updated_time ); ?></span>
        </div>
    <?php endif; ?>
    
    <!-- Footer (Attribution) -->
    <?php
    $footer_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/footer.php';
    if ( file_exists( $footer_template ) ) {
        include $footer_template;
    } else {
        ?>
        <div class="pw-aqi-attribution">
            <span><?php esc_html_e( 'Air quality data by OpenWeatherMap', 'pearl-weather' ); ?></span>
        </div>
        <?php
    }
    ?>
    
</div>

<style>
/* AQI Card Styles */
.pw-aqi-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* AQI Summary */
.pw-aqi-heading {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 16px 0;
    color: #333;
}

.pw-aqi-value-container {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}

.pw-aqi-value {
    font-size: 48px;
    font-weight: 700;
    line-height: 1;
}

.pw-aqi-condition {
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
    background: rgba(0, 0, 0, 0.05);
}

/* Scale Bar */
.pw-aqi-scale-bar {
    position: relative;
    display: flex;
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    margin: 16px 0 8px;
}

.pw-scale-segment {
    height: 100%;
}

.pw-scale-good { background: #00B150; }
.pw-scale-moderate { background: #EEC631; }
.pw-scale-poor { background: #EA8B34; }
.pw-scale-unhealthy { background: #E95378; }
.pw-scale-severe { background: #B33FB9; }
.pw-scale-hazardous { background: #C91F33; }

.pw-scale-indicator {
    position: absolute;
    top: -4px;
    width: 12px;
    height: 16px;
    background: #333;
    border-radius: 2px;
    transform: translateX(-50%);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.pw-scale-labels {
    display: flex;
    justify-content: space-between;
    font-size: 9px;
    color: #666;
    margin-bottom: 16px;
}

/* AQI Description */
.pw-aqi-description {
    font-size: 14px;
    line-height: 1.5;
    color: #555;
    margin: 12px 0;
}

/* Pollutant Details */
.pw-pollutant-header {
    margin: 20px 0 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-pollutant-title {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
}

/* Pollutant List - List Layout */
.pw-pollutant-layout-list .pw-pollutant-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.pw-pollutant-layout-list .pw-pollutant-item:last-child {
    border-bottom: none;
}

.pw-pollutant-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pw-pollutant-name-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pw-pollutant-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.pw-pollutant-name {
    font-size: 13px;
    font-weight: 500;
}

.pw-pollutant-value-wrapper {
    display: flex;
    align-items: baseline;
    gap: 4px;
}

.pw-pollutant-value {
    font-size: 14px;
    font-weight: 600;
}

.pw-pollutant-unit {
    font-size: 10px;
    opacity: 0.6;
}

.pw-pollutant-bar {
    height: 4px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 2px;
    overflow: hidden;
}

.pw-pollutant-progress {
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s ease;
}

.pw-pollutant-condition {
    font-size: 11px;
    opacity: 0.7;
}

/* Pollutant List - Grid Layout */
.pw-pollutant-layout-grid {
    display: grid;
    gap: 12px;
}

.pw-pollutant-grid-cols-2 {
    grid-template-columns: repeat(2, 1fr);
}

.pw-pollutant-grid-cols-3 {
    grid-template-columns: repeat(3, 1fr);
}

.pw-pollutant-grid-cols-4 {
    grid-template-columns: repeat(4, 1fr);
}

.pw-pollutant-layout-grid .pw-pollutant-item {
    background: rgba(0, 0, 0, 0.02);
    border-radius: 8px;
    padding: 12px;
    text-align: center;
}

.pw-pollutant-layout-grid .pw-pollutant-info {
    flex-direction: column;
    gap: 6px;
}

/* Last Updated */
.pw-aqi-last-updated {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #757575;
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    justify-content: flex-end;
}

/* Attribution */
.pw-aqi-attribution {
    margin-top: 12px;
    font-size: 10px;
    text-align: center;
    color: #999;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-aqi-card {
        padding: 16px;
    }
    
    .pw-aqi-value {
        font-size: 36px;
    }
    
    .pw-pollutant-grid-cols-2,
    .pw-pollutant-grid-cols-3,
    .pw-pollutant-grid-cols-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .pw-pollutant-grid-cols-2,
    .pw-pollutant-grid-cols-3,
    .pw-pollutant-grid-cols-4 {
        grid-template-columns: repeat(1, 1fr);
    }
}
</style>

<?php
/**
 * Helper functions for AQI display.
 */
if ( ! function_exists( 'get_aqi_condition_from_value' ) ) {
    function get_aqi_condition_from_value( $aqi ) {
        if ( $aqi <= 50 ) return 'good';
        if ( $aqi <= 100 ) return 'moderate';
        if ( $aqi <= 150 ) return 'poor';
        if ( $aqi <= 200 ) return 'unhealthy';
        if ( $aqi <= 250 ) return 'severe';
        return 'hazardous';
    }
}

if ( ! function_exists( 'get_aqi_color' ) ) {
    function get_aqi_color( $condition ) {
        $colors = array(
            'good'      => '#00B150',
            'moderate'  => '#EEC631',
            'poor'      => '#EA8B34',
            'unhealthy' => '#E95378',
            'severe'    => '#B33FB9',
            'hazardous' => '#C91F33',
        );
        return isset( $colors[ $condition ] ) ? $colors[ $condition ] : '#757575';
    }
}

if ( ! function_exists( 'get_aqi_description' ) ) {
    function get_aqi_description( $condition ) {
        $descriptions = array(
            'good'      => __( 'Air quality is excellent. No health concerns for any group.', 'pearl-weather' ),
            'moderate'  => __( 'Air is acceptable. Sensitive individuals should monitor symptoms.', 'pearl-weather' ),
            'poor'      => __( 'Air may cause discomfort. Sensitive groups should reduce outdoor exposure.', 'pearl-weather' ),
            'unhealthy' => __( 'Health risks increase. Everyone should limit outdoor time.', 'pearl-weather' ),
            'severe'    => __( 'Air is very unhealthy. Avoid outdoor activity.', 'pearl-weather' ),
            'hazardous' => __( 'Serious health threat. Stay indoors.', 'pearl-weather' ),
        );
        return isset( $descriptions[ $condition ] ) ? $descriptions[ $condition ] : '';
    }
}

if ( ! function_exists( 'get_pollutant_aqi' ) ) {
    function get_pollutant_aqi( $value, $pollutant ) {
        $breakpoints = array(
            'pm2_5' => array( 0, 10, 25, 50, 75, 100 ),
            'pm10'  => array( 0, 20, 50, 100, 200, 250 ),
            'no2'   => array( 0, 40, 70, 150, 200, 250 ),
            'o3'    => array( 0, 60, 100, 140, 180, 220 ),
            'co'    => array( 0, 4400, 9400, 12400, 15400, 18000 ),
            'so2'   => array( 0, 20, 80, 250, 350, 400 ),
        );
        
        $levels = isset( $breakpoints[ $pollutant ] ) ? $breakpoints[ $pollutant ] : array( 0, 10, 25, 50, 75, 100 );
        $aqi = 0;
        
        for ( $i = 0; $i < count( $levels ) - 1; $i++ ) {
            if ( $value >= $levels[ $i ] && $value <= $levels[ $i + 1 ] ) {
                $aqi = 50 + ( $i * 50 );
                break;
            }
        }
        
        if ( $aqi === 0 ) $aqi = 300;
        
        return array(
            'iaqi'      => $aqi,
            'condition' => get_aqi_condition_from_value( $aqi ),
        );
    }
}

if ( ! function_exists( 'format_pollutant_name' ) ) {
    function format_pollutant_name( $name, $unit, $format = 'both', $show_unit = true ) {
        if ( 'abbreviation' === $format ) {
            return $name;
        }
        if ( 'name' === $format ) {
            $names = array(
                'PM2.5' => 'Particulate Matter 2.5',
                'PM10'  => 'Particulate Matter 10',
                'NO₂'   => 'Nitrogen Dioxide',
                'O₃'    => 'Ozone',
                'CO'    => 'Carbon Monoxide',
                'SO₂'   => 'Sulfur Dioxide',
            );
            return isset( $names[ $name ] ) ? $names[ $name ] : $name;
        }
        // Both format
        if ( $show_unit ) {
            return sprintf( '%s (%s)', $name, $unit );
        }
        return $name;
    }
}