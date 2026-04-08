<?php
/**
 * AQI Card Summary Template Part
 *
 * Displays the main AQI summary including heading, location,
 * AQI gauge, condition label, and description.
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
 * - $current_aqi_data: Current AQI data object/array
 * - $weather_data: Current weather data array
 * - $aqi_summary_heading_label: Summary heading text
 */

// Display settings.
$show_condition = isset( $attributes['enableSummaryAqiCondition'] ) ? (bool) $attributes['enableSummaryAqiCondition'] : true;
$show_description = isset( $attributes['enableSummaryAqiDesc'] ) ? (bool) $attributes['enableSummaryAqiDesc'] : true;
$show_gauge = isset( $attributes['showAqiGauge'] ) ? (bool) $attributes['showAqiGauge'] : true;
$show_heading = isset( $attributes['showAqiHeading'] ) ? (bool) $attributes['showAqiHeading'] : true;
$gauge_size = isset( $attributes['aqiGaugeSize'] ) ? (int) $attributes['aqiGaugeSize'] : 120;

// Get primary pollutant data (using PM2.5 as primary indicator).
$primary_pollutant = 'pm2_5';
$primary_value = 0;

if ( is_object( $current_aqi_data ) && isset( $current_aqi_data->pm2_5 ) ) {
    $primary_value = (float) $current_aqi_data->pm2_5;
} elseif ( is_array( $current_aqi_data ) && isset( $current_aqi_data['pm2_5'] ) ) {
    $primary_value = (float) $current_aqi_data['pm2_5'];
} elseif ( isset( $aqi_data['pollutants']['pm2_5'] ) ) {
    $primary_value = (float) $aqi_data['pollutants']['pm2_5'];
}

// Get AQI condition based on primary pollutant.
$pollutant_data = get_pollutant_aqi_data( $primary_value, 'pm2_5' );
$condition = $pollutant_data['condition'];
$condition_label = ucfirst( $condition );
$color = get_aqi_color( $condition );
$rgba_color = hex_to_rgba( $color, 0.2 );
$description = get_aqi_description( $condition );
$detailed_description = get_aqi_detailed_description( $condition );

// AQI value (overall).
$aqi_value = isset( $aqi_data['iaqi'] ) ? (int) $aqi_data['iaqi'] : 0;
if ( $aqi_value === 0 && $primary_value > 0 ) {
    $aqi_value = get_aqi_from_pollutant( $primary_value, 'pm2_5' );
}

// CSS classes.
$wrapper_classes = array( 'pw-aqi-summary-card' );
$wrapper_classes[] = 'pw-aqi-condition-' . $condition;

if ( ! empty( $attributes['aqiSummaryCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['aqiSummaryCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     data-aqi-value="<?php echo esc_attr( $aqi_value ); ?>"
     data-aqi-condition="<?php echo esc_attr( $condition ); ?>"
     style="--pw-aqi-color: <?php echo esc_attr( $color ); ?>; --pw-aqi-bg: <?php echo esc_attr( $rgba_color ); ?>;">
    
    <!-- Header Section -->
    <div class="pw-aqi-summary-header">
        
        <!-- Heading -->
        <?php if ( $show_heading && ! empty( $aqi_summary_heading_label ) ) : ?>
            <h4 class="pw-aqi-summary-title">
                <?php echo esc_html( $aqi_summary_heading_label ); ?>
            </h4>
        <?php endif; ?>
        
        <!-- Location Name (from location-name template part) -->
        <?php
        $location_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/aqi-minimal/parts/location-name.php';
        if ( file_exists( $location_template ) ) {
            include $location_template;
        }
        ?>
        
    </div>
    
    <!-- AQI Content Section -->
    <div class="pw-aqi-summary-content">
        
        <!-- AQI Gauge -->
        <?php if ( $show_gauge ) : ?>
            <div class="pw-aqi-gauge-wrapper">
                <?php render_aqi_gauge( array(
                    'value'        => $primary_value,
                    'pollutant'    => 'pm2_5',
                    'size'         => $gauge_size,
                    'stroke_width' => 8,
                    'label'        => __( 'AQI', 'pearl-weather' ),
                    'show_labels'  => false,
                ) ); ?>
            </div>
        <?php endif; ?>
        
        <!-- Condition Section -->
        <?php if ( $show_condition ) : ?>
            <div class="pw-aqi-condition-section">
                <span class="pw-aqi-condition-label"><?php esc_html_e( 'Air Quality', 'pearl-weather' ); ?></span>
                <div class="pw-aqi-condition-value" style="background: <?php echo esc_attr( $rgba_color ); ?>; border-color: <?php echo esc_attr( $color ); ?>;">
                    <?php echo esc_html( $condition_label ); ?>
                </div>
                <?php if ( ! empty( $aqi_value ) ) : ?>
                    <div class="pw-aqi-number">
                        <span class="pw-aqi-value"><?php echo esc_html( $aqi_value ); ?></span>
                        <span class="pw-aqi-unit">AQI</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Description Section -->
    <?php if ( $show_description && ! empty( $description ) ) : ?>
        <div class="pw-aqi-summary-description">
            <p class="pw-aqi-description-text"><?php echo esc_html( $description ); ?></p>
            <?php if ( isset( $attributes['showDetailedDescription'] ) && $attributes['showDetailedDescription'] && ! empty( $detailed_description ) ) : ?>
                <p class="pw-aqi-detailed-description"><?php echo esc_html( $detailed_description ); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Health Recommendation (Optional) -->
    <?php if ( isset( $attributes['showHealthRecommendation'] ) && $attributes['showHealthRecommendation'] ) : ?>
        <div class="pw-aqi-health-recommendation">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 8V12M12 16H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="pw-recommendation-text">
                <?php echo esc_html( get_health_recommendation( $condition ) ); ?>
            </span>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* AQI Summary Card Styles */
.pw-aqi-summary-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

/* Header Section */
.pw-aqi-summary-header {
    margin-bottom: 20px;
}

.pw-aqi-summary-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 8px 0;
    color: #333;
}

/* Content Section */
.pw-aqi-summary-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

/* Gauge Wrapper */
.pw-aqi-gauge-wrapper {
    flex-shrink: 0;
}

/* Condition Section */
.pw-aqi-condition-section {
    text-align: center;
}

.pw-aqi-condition-label {
    font-size: 13px;
    opacity: 0.7;
    display: block;
    margin-bottom: 6px;
}

.pw-aqi-condition-value {
    display: inline-block;
    padding: 6px 20px;
    border-radius: 30px;
    font-size: 16px;
    font-weight: 600;
    background: rgba(0, 0, 0, 0.05);
    margin-bottom: 8px;
}

.pw-aqi-number {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 4px;
}

.pw-aqi-value {
    font-size: 32px;
    font-weight: 700;
}

.pw-aqi-unit {
    font-size: 12px;
    opacity: 0.6;
}

/* Description Section */
.pw-aqi-summary-description {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-aqi-description-text {
    font-size: 14px;
    line-height: 1.5;
    color: #555;
    margin: 0;
}

.pw-aqi-detailed-description {
    font-size: 12px;
    color: #666;
    margin: 8px 0 0 0;
}

/* Health Recommendation */
.pw-aqi-health-recommendation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 16px;
    padding: 10px;
    background: rgba(0, 0, 0, 0.03);
    border-radius: 8px;
    font-size: 12px;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-aqi-summary-card {
        padding: 16px;
    }
    
    .pw-aqi-summary-title {
        font-size: 16px;
    }
    
    .pw-aqi-summary-content {
        gap: 16px;
    }
    
    .pw-aqi-value {
        font-size: 28px;
    }
    
    .pw-aqi-condition-value {
        font-size: 14px;
        padding: 4px 16px;
    }
}
</style>

<?php
/**
 * Helper functions for AQI summary display.
 */
if ( ! function_exists( 'render_aqi_gauge' ) ) {
    /**
     * Render a simple AQI gauge.
     *
     * @param array $args Gauge arguments.
     */
    function render_aqi_gauge( $args = array() ) {
        $defaults = array(
            'value'        => 0,
            'pollutant'    => 'pm2_5',
            'size'         => 120,
            'stroke_width' => 8,
            'label'        => '',
            'show_labels'  => true,
        );
        
        $args = wp_parse_args( $args, $defaults );
        $value = (float) $args['value'];
        $size = (int) $args['size'];
        $stroke = (int) $args['stroke_width'];
        $radius = ( $size - $stroke ) / 2;
        $circumference = 2 * M_PI * $radius;
        
        // Calculate percentage (0-300 scale, cap at 100%).
        $percentage = min( 100, ( $value / 300 ) * 100 );
        $dashoffset = $circumference * ( 1 - $percentage / 100 );
        
        // Get color based on value.
        $color = get_aqi_color( get_aqi_condition_from_value( $value ) );
        
        ?>
        <div class="pw-aqi-gauge" style="width: <?php echo esc_attr( $size ); ?>px;">
            <svg width="<?php echo esc_attr( $size ); ?>" height="<?php echo esc_attr( $size ); ?>" viewBox="0 0 <?php echo esc_attr( $size ); ?> <?php echo esc_attr( $size ); ?>">
                <!-- Background Circle -->
                <circle cx="<?php echo esc_attr( $size / 2 ); ?>" 
                        cy="<?php echo esc_attr( $size / 2 ); ?>" 
                        r="<?php echo esc_attr( $radius ); ?>" 
                        fill="none" 
                        stroke="#e0e0e0" 
                        stroke-width="<?php echo esc_attr( $stroke ); ?>"/>
                
                <!-- Progress Circle -->
                <circle cx="<?php echo esc_attr( $size / 2 ); ?>" 
                        cy="<?php echo esc_attr( $size / 2 ); ?>" 
                        r="<?php echo esc_attr( $radius ); ?>" 
                        fill="none" 
                        stroke="<?php echo esc_attr( $color ); ?>" 
                        stroke-width="<?php echo esc_attr( $stroke ); ?>"
                        stroke-dasharray="<?php echo esc_attr( $circumference ); ?>"
                        stroke-dashoffset="<?php echo esc_attr( $dashoffset ); ?>"
                        transform="rotate(-90 <?php echo esc_attr( $size / 2 ); ?> <?php echo esc_attr( $size / 2 ); ?>)"
                        stroke-linecap="round"/>
                
                <!-- Center Text -->
                <?php if ( ! empty( $args['label'] ) ) : ?>
                    <text x="<?php echo esc_attr( $size / 2 ); ?>" 
                          y="<?php echo esc_attr( $size / 2 - 8 ); ?>" 
                          text-anchor="middle" 
                          font-size="<?php echo esc_attr( $size * 0.12 ); ?>" 
                          fill="#666">
                        <?php echo esc_html( $args['label'] ); ?>
                    </text>
                <?php endif; ?>
                
                <text x="<?php echo esc_attr( $size / 2 ); ?>" 
                      y="<?php echo esc_attr( $size / 2 + 10 ); ?>" 
                      text-anchor="middle" 
                      font-size="<?php echo esc_attr( $size * 0.2 ); ?>" 
                      font-weight="bold" 
                      fill="<?php echo esc_attr( $color ); ?>">
                    <?php echo esc_html( round( $value ) ); ?>
                </text>
            </svg>
        </div>
        <style>
            .pw-aqi-gauge circle:last-child {
                transition: stroke-dashoffset 0.5s ease;
            }
        </style>
        <?php
    }
}

if ( ! function_exists( 'get_aqi_from_pollutant' ) ) {
    /**
     * Calculate AQI from pollutant value.
     *
     * @param float  $value     Pollutant concentration.
     * @param string $pollutant Pollutant key.
     * @return int
     */
    function get_aqi_from_pollutant( $value, $pollutant ) {
        $breakpoints = array(
            'pm2_5' => array( 0, 10, 25, 50, 75, 100, INF ),
            'pm10'  => array( 0, 20, 50, 100, 200, 250, INF ),
            'no2'   => array( 0, 40, 70, 150, 200, 250, INF ),
            'o3'    => array( 0, 60, 100, 140, 180, 220, INF ),
            'co'    => array( 0, 4400, 9400, 12400, 15400, 18000, INF ),
            'so2'   => array( 0, 20, 80, 250, 350, 400, INF ),
        );
        
        $levels = isset( $breakpoints[ $pollutant ] ) ? $breakpoints[ $pollutant ] : $breakpoints['pm2_5'];
        
        for ( $i = 0; $i < count( $levels ) - 1; $i++ ) {
            if ( $value >= $levels[ $i ] && $value < $levels[ $i + 1 ] ) {
                return 50 + ( $i * 50 );
            }
        }
        
        return 300;
    }
}

if ( ! function_exists( 'get_aqi_detailed_description' ) ) {
    /**
     * Get detailed AQI description.
     *
     * @param string $condition AQI condition.
     * @return string
     */
    function get_aqi_detailed_description( $condition ) {
        $descriptions = array(
            'good'      => __( 'Air quality is excellent. No health concerns for any group. Enjoy outdoor activities!', 'pearl-weather' ),
            'moderate'  => __( 'Air is acceptable for most. However, sensitive individuals may experience minor symptoms from prolonged exposure.', 'pearl-weather' ),
            'poor'      => __( 'Air may cause discomfort. Sensitive groups should reduce outdoor exposure. Consider wearing a mask if outdoors.', 'pearl-weather' ),
            'unhealthy' => __( 'Health risks increase for all. Everyone should limit time spent outdoors, especially during peak hours.', 'pearl-weather' ),
            'severe'    => __( 'Air is very unhealthy. Avoid outdoor activity whenever possible. Keep windows closed.', 'pearl-weather' ),
            'hazardous' => __( 'Serious health threat. Stay indoors with air purifiers if possible. Follow public health recommendations.', 'pearl-weather' ),
        );
        
        return isset( $descriptions[ $condition ] ) ? $descriptions[ $condition ] : '';
    }
}

if ( ! function_exists( 'get_health_recommendation' ) ) {
    /**
     * Get health recommendation based on AQI condition.
     *
     * @param string $condition AQI condition.
     * @return string
     */
    function get_health_recommendation( $condition ) {
        $recommendations = array(
            'good'      => __( 'Great day to be outside! No health precautions needed.', 'pearl-weather' ),
            'moderate'  => __( 'Sensitive individuals should limit prolonged outdoor exertion.', 'pearl-weather' ),
            'poor'      => __( 'Sensitive groups: Reduce outdoor activity. Others: Limit prolonged exertion.', 'pearl-weather' ),
            'unhealthy' => __( 'Everyone: Reduce outdoor activity. Sensitive groups: Avoid outdoor activity.', 'pearl-weather' ),
            'severe'    => __( 'Everyone: Avoid outdoor activity. Stay indoors with windows closed.', 'pearl-weather' ),
            'hazardous' => __( 'Emergency conditions! Stay indoors. Use air purifiers if available.', 'pearl-weather' ),
        );
        
        return isset( $recommendations[ $condition ] ) ? $recommendations[ $condition ] : '';
    }
}

if ( ! function_exists( 'hex_to_rgba' ) ) {
    /**
     * Convert hex color to rgba.
     *
     * @param string $hex   Hex color code.
     * @param float  $alpha Alpha value.
     * @return string
     */
    function hex_to_rgba( $hex, $alpha = 1 ) {
        $hex = ltrim( $hex, '#' );
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
        return "rgba($r, $g, $b, $alpha)";
    }
}