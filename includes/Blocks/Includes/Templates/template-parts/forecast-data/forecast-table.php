<?php
/**
 * Forecast Table Layout Renderer
 *
 * Renders forecast data in an HTML table format for table and tabs blocks.
 * Supports both hourly and daily forecasts with customizable columns.
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
 * - $forecast_data: Forecast data array
 * - $table_forecast: Forecast data for table (alias)
 * - $forecast_key: Forecast type key ('hourly' or 'daily')
 * - $template: Template variant name
 * - $block_name: Block name
 * - $measurement_units: Measurement units array
 * - $time_settings: Time settings array
 */

// Check if forecast should be displayed.
$show_forecast = isset( $attributes['displayWeatherForecastData'] ) ? (bool) $attributes['displayWeatherForecastData'] : true;

if ( ! $show_forecast ) {
    return;
}

// Get forecast data.
$forecast_items = isset( $table_forecast ) ? $table_forecast : ( isset( $forecast_data ) ? $forecast_data : array() );

if ( empty( $forecast_items ) ) {
    return;
}

// Process forecast options.
$forecast_options = array();

if ( isset( $attributes['forecastData'] ) && is_array( $attributes['forecastData'] ) ) {
    foreach ( $attributes['forecastData'] as $option ) {
        if ( isset( $option['value'] ) && true === $option['value'] ) {
            $forecast_options[] = isset( $option['name'] ) ? $option['name'] : '';
        }
    }
}

// Add extra options (day/hour and weather condition).
$extra_options = array( ( 'daily' === $forecast_key ? 'day' : 'hour' ), 'weather' );
$all_columns = array_merge( $extra_options, $forecast_options );

// Table settings.
$show_table_header = in_array( $template, array( 'table-one', 'table-card' ), true );
$table_title = isset( $attributes['tableTitle'] ) ? sanitize_text_field( $attributes['tableTitle'] ) : __( 'Hourly Forecast', 'pearl-weather' );
$sticky_header = isset( $attributes['tableStickyHeader'] ) ? (bool) $attributes['tableStickyHeader'] : false;
$striped_rows = isset( $attributes['tableStripedRows'] ) ? (bool) $attributes['tableStripedRows'] : true;
$table_bordered = isset( $attributes['tableBordered'] ) ? (bool) $attributes['tableBordered'] : true;
$table_hover = isset( $attributes['tableHover'] ) ? (bool) $attributes['tableHover'] : true;
$compact_mode = isset( $attributes['tableCompact'] ) ? (bool) $attributes['tableCompact'] : false;

// CSS classes.
$wrapper_classes = array( 'pw-forecast-table-wrapper' );

if ( $sticky_header ) {
    $wrapper_classes[] = 'pw-table-sticky-header';
}

if ( $compact_mode ) {
    $wrapper_classes[] = 'pw-table-compact';
}

if ( ! empty( $attributes['tableCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['tableCustomClass'] );
}

$table_classes = array( 'pw-forecast-table' );

if ( $striped_rows ) {
    $table_classes[] = 'pw-table-striped';
}

if ( $table_bordered ) {
    $table_classes[] = 'pw-table-bordered';
}

if ( $table_hover ) {
    $table_classes[] = 'pw-table-hover';
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    
    <!-- Table Title -->
    <?php if ( 'table' === $block_name && ! empty( $table_title ) ) : ?>
        <div class="pw-table-title">
            <h4><?php echo esc_html( $table_title ); ?></h4>
        </div>
    <?php endif; ?>
    
    <!-- Forecast Table -->
    <div class="pw-table-responsive">
        <table class="<?php echo esc_attr( implode( ' ', $table_classes ) ); ?>">
            
            <!-- Table Header -->
            <thead>
                <?php if ( $show_table_header ) : ?>
                    <!-- Grouped Header Row -->
                    <tr class="pw-table-group-header">
                        <th rowspan="2"><?php esc_html_e( 'Time', 'pearl-weather' ); ?></th>
                        <th colspan="2"><?php esc_html_e( 'Weather Condition', 'pearl-weather' ); ?></th>
                        <th colspan="3"><?php esc_html_e( 'Atmospheric', 'pearl-weather' ); ?></th>
                        <th colspan="2"><?php esc_html_e( 'Precipitation', 'pearl-weather' ); ?></th>
                    </tr>
                <?php endif; ?>
                
                <!-- Column Header Row -->
                <tr class="pw-table-column-header">
                    <?php foreach ( $all_columns as $column ) : ?>
                        <th scope="col" data-column="<?php echo esc_attr( $column ); ?>">
                            <div class="pw-column-header-content">
                                <?php
                                // Get icon for column.
                                $icon_class = get_table_column_icon( $column );
                                if ( ! empty( $icon_class ) ) :
                                ?>
                                    <span class="pw-column-icon">
                                        <i class="<?php echo esc_attr( $icon_class ); ?>"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <span class="pw-column-label">
                                    <?php
                                    // Special handling for precipitation amount label.
                                    if ( $show_table_header && 'precipitation' === $column ) {
                                        echo esc_html__( 'Amount', 'pearl-weather' );
                                    } elseif ( 'weather' === $column ) {
                                        echo esc_html__( 'Condition', 'pearl-weather' );
                                    } elseif ( 'day' === $column ) {
                                        echo esc_html__( 'Day', 'pearl-weather' );
                                    } elseif ( 'hour' === $column ) {
                                        echo esc_html__( 'Hour', 'pearl-weather' );
                                    } else {
                                        echo esc_html( get_forecast_additional_label( $column ) );
                                    }
                                    ?>
                                </span>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            
            <!-- Table Body -->
            <tbody>
                <?php foreach ( $forecast_items as $index => $single_forecast ) : ?>
                    <?php
                    // Format forecast data.
                    $formatted_forecast = format_forecast_for_table( $single_forecast, $measurement_units, $time_settings );
                    $row_class = ( $index % 2 === 0 ) ? 'even' : 'odd';
                    ?>
                    <tr class="pw-forecast-row pw-row-<?php echo esc_attr( $row_class ); ?>" data-index="<?php echo esc_attr( $index ); ?>">
                        
                        <!-- Date/Time Column -->
                        <td class="pw-col-datetime" data-label="<?php esc_attr_e( 'Time', 'pearl-weather' ); ?>">
                            <?php
                            if ( 'daily' === $forecast_key ) {
                                echo esc_html( $formatted_forecast['day'] ?? $formatted_forecast['date'] ?? '' );
                            } else {
                                echo esc_html( $formatted_forecast['hour'] ?? $formatted_forecast['time'] ?? '' );
                            }
                            ?>
                        </td>
                        
                        <!-- Weather Condition Column -->
                        <td class="pw-col-weather-icon" data-label="<?php esc_attr_e( 'Icon', 'pearl-weather' ); ?>">
                            <?php if ( ! empty( $formatted_forecast['icon_url'] ) ) : ?>
                                <img src="<?php echo esc_url( $formatted_forecast['icon_url'] ); ?>" 
                                     alt="<?php echo esc_attr( $formatted_forecast['condition'] ?? '' ); ?>"
                                     width="32" height="32"
                                     loading="lazy">
                            <?php endif; ?>
                        </td>
                        <td class="pw-col-weather-condition" data-label="<?php esc_attr_e( 'Condition', 'pearl-weather' ); ?>">
                            <?php echo esc_html( $formatted_forecast['condition'] ?? '' ); ?>
                        </td>
                        
                        <!-- Temperature Column -->
                        <?php if ( in_array( 'temperature', $forecast_options, true ) ) : ?>
                            <td class="pw-col-temperature" data-label="<?php esc_attr_e( 'Temperature', 'pearl-weather' ); ?>">
                                <div class="pw-temp-display">
                                    <span class="pw-temp-min"><?php echo esc_html( $formatted_forecast['temp_min'] ?? $formatted_forecast['temp'] ?? '--' ); ?></span>
                                    <?php if ( isset( $formatted_forecast['temp_max'] ) ) : ?>
                                        <span class="pw-temp-sep">/</span>
                                        <span class="pw-temp-max"><?php echo esc_html( $formatted_forecast['temp_max'] ); ?></span>
                                    <?php endif; ?>
                                    <span class="pw-temp-unit"><?php echo esc_html( $formatted_forecast['temp_unit'] ?? '°C' ); ?></span>
                                </div>
                            </td>
                        <?php endif; ?>
                        
                        <!-- Wind Column -->
                        <?php if ( in_array( 'wind', $forecast_options, true ) ) : ?>
                            <td class="pw-col-wind" data-label="<?php esc_attr_e( 'Wind', 'pearl-weather' ); ?>">
                                <div class="pw-wind-display">
                                    <span class="pw-wind-speed"><?php echo esc_html( $formatted_forecast['wind_speed'] ?? '--' ); ?></span>
                                    <span class="pw-wind-unit"><?php echo esc_html( $formatted_forecast['wind_unit'] ?? 'm/s' ); ?></span>
                                    <?php if ( ! empty( $formatted_forecast['wind_direction_icon'] ) ) : ?>
                                        <?php echo wp_kses_post( $formatted_forecast['wind_direction_icon'] ); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                        
                        <!-- Humidity Column -->
                        <?php if ( in_array( 'humidity', $forecast_options, true ) ) : ?>
                            <td class="pw-col-humidity" data-label="<?php esc_attr_e( 'Humidity', 'pearl-weather' ); ?>">
                                <?php echo esc_html( $formatted_forecast['humidity'] ?? '--' ); ?>%
                            </td>
                        <?php endif; ?>
                        
                        <!-- Pressure Column -->
                        <?php if ( in_array( 'pressure', $forecast_options, true ) ) : ?>
                            <td class="pw-col-pressure" data-label="<?php esc_attr_e( 'Pressure', 'pearl-weather' ); ?>">
                                <?php echo esc_html( $formatted_forecast['pressure'] ?? '--' ); ?>
                                <span class="pw-pressure-unit"><?php echo esc_html( $formatted_forecast['pressure_unit'] ?? 'hPa' ); ?></span>
                            </td>
                        <?php endif; ?>
                        
                        <!-- Precipitation Column -->
                        <?php if ( in_array( 'precipitation', $forecast_options, true ) ) : ?>
                            <td class="pw-col-precipitation" data-label="<?php esc_attr_e( 'Precipitation', 'pearl-weather' ); ?>">
                                <?php echo esc_html( $formatted_forecast['precipitation'] ?? '--' ); ?>
                                <span class="pw-precip-unit"><?php echo esc_html( $formatted_forecast['precip_unit'] ?? 'mm' ); ?></span>
                            </td>
                        <?php endif; ?>
                        
                        <!-- Rain Chance Column -->
                        <?php if ( in_array( 'rain_chance', $forecast_options, true ) || in_array( 'rainchance', $forecast_options, true ) ) : ?>
                            <td class="pw-col-rainchance" data-label="<?php esc_attr_e( 'Rain Chance', 'pearl-weather' ); ?>">
                                <?php echo esc_html( $formatted_forecast['rain_chance'] ?? $formatted_forecast['rain'] ?? '--' ); ?>%
                            </td>
                        <?php endif; ?>
                        
                        <!-- Snow Column -->
                        <?php if ( in_array( 'snow', $forecast_options, true ) ) : ?>
                            <td class="pw-col-snow" data-label="<?php esc_attr_e( 'Snow', 'pearl-weather' ); ?>">
                                <?php echo esc_html( $formatted_forecast['snow'] ?? '--' ); ?>
                                <span class="pw-snow-unit"><?php echo esc_html( $formatted_forecast['precip_unit'] ?? 'mm' ); ?></span>
                            </td>
                        <?php endif; ?>
                        
                        <!-- UV Index Column -->
                        <?php if ( in_array( 'uv_index', $forecast_options, true ) ) : ?>
                            <td class="pw-col-uv-index" data-label="<?php esc_attr_e( 'UV Index', 'pearl-weather' ); ?>">
                                <?php echo esc_html( $formatted_forecast['uv_index'] ?? '--' ); ?>
                            </td>
                        <?php endif; ?>
                        
                        <!-- Clouds Column -->
                        <?php if ( in_array( 'clouds', $forecast_options, true ) ) : ?>
                            <td class="pw-col-clouds" data-label="<?php esc_attr_e( 'Clouds', 'pearl-weather' ); ?>">
                                <?php echo esc_html( $formatted_forecast['clouds'] ?? '--' ); ?>%
                            </td>
                        <?php endif; ?>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
            
        </table>
    </div>
    
</div>

<style>
/* Forecast Table Styles */
.pw-forecast-table-wrapper {
    width: 100%;
    margin: 16px 0;
    overflow-x: auto;
}

.pw-table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Table */
.pw-forecast-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

/* Bordered Table */
.pw-table-bordered th,
.pw-table-bordered td {
    border: 1px solid rgba(0, 0, 0, 0.08);
}

/* Striped Rows */
.pw-table-striped tbody tr:nth-child(odd) {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Hover Effect */
.pw-table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.04);
}

/* Compact Mode */
.pw-table-compact th,
.pw-table-compact td {
    padding: 8px 10px;
}

/* Default Padding */
.pw-forecast-table th,
.pw-forecast-table td {
    padding: 12px 12px;
    text-align: left;
    vertical-align: middle;
}

/* Header Styles */
.pw-forecast-table th {
    font-weight: 600;
    background-color: #f8f9fa;
    border-bottom: 2px solid rgba(0, 0, 0, 0.1);
}

/* Column Header Content */
.pw-column-header-content {
    display: flex;
    align-items: center;
    gap: 6px;
}

.pw-column-icon i {
    font-size: 16px;
}

/* Sticky Header */
.pw-table-sticky-header thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 10;
}

/* Group Header */
.pw-table-group-header th {
    text-align: center;
    font-weight: 600;
    background-color: #f0f1f3;
}

/* Temperature Display */
.pw-temp-display {
    display: inline-flex;
    align-items: baseline;
    gap: 3px;
}

.pw-temp-min,
.pw-temp-max {
    font-weight: 500;
}

.pw-temp-sep {
    opacity: 0.5;
}

.pw-temp-unit {
    font-size: 11px;
    opacity: 0.6;
}

/* Wind Display */
.pw-wind-display {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

/* Responsive Table (Mobile) */
@media (max-width: 768px) {
    .pw-forecast-table,
    .pw-forecast-table thead,
    .pw-forecast-table tbody,
    .pw-forecast-table th,
    .pw-forecast-table td,
    .pw-forecast-table tr {
        display: block;
    }
    
    .pw-forecast-table thead tr {
        position: absolute;
        top: -9999px;
        left: -9999px;
    }
    
    .pw-forecast-table tr {
        margin-bottom: 16px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .pw-forecast-table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        text-align: right;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .pw-forecast-table td:last-child {
        border-bottom: none;
    }
    
    .pw-forecast-table td:before {
        content: attr(data-label);
        font-weight: 600;
        text-align: left;
        margin-right: 12px;
    }
    
    .pw-table-group-header {
        display: none;
    }
}
</style>

<?php
/**
 * Helper function to format forecast data for table display.
 */
if ( ! function_exists( 'format_forecast_for_table' ) ) {
    /**
     * Format forecast data for table rendering.
     *
     * @param array $forecast         Raw forecast data.
     * @param array $measurement_units Measurement units.
     * @param array $time_settings    Time settings.
     * @return array
     */
    function format_forecast_for_table( $forecast, $measurement_units, $time_settings ) {
        $formatted = array();
        
        // Date/Time.
        $formatted['time'] = isset( $forecast['time'] ) ? $forecast['time'] : '';
        $formatted['hour'] = isset( $forecast['hour'] ) ? $forecast['hour'] : '';
        $formatted['day'] = isset( $forecast['day'] ) ? $forecast['day'] : '';
        $formatted['date'] = isset( $forecast['date'] ) ? $forecast['date'] : '';
        
        // Temperature.
        $formatted['temp'] = isset( $forecast['temp'] ) ? round( $forecast['temp'] ) : '';
        $formatted['temp_min'] = isset( $forecast['min'] ) ? round( $forecast['min'] ) : ( isset( $forecast['temp_min'] ) ? round( $forecast['temp_min'] ) : '' );
        $formatted['temp_max'] = isset( $forecast['max'] ) ? round( $forecast['max'] ) : ( isset( $forecast['temp_max'] ) ? round( $forecast['temp_max'] ) : '' );
        $formatted['temp_unit'] = 'metric' === ( $measurement_units['temperature_scale'] ?? 'metric' ) ? '°C' : '°F';
        
        // Wind.
        $formatted['wind_speed'] = isset( $forecast['wind'] ) ? $forecast['wind'] : '';
        $formatted['wind_unit'] = isset( $measurement_units['wind_speed_unit'] ) ? $measurement_units['wind_speed_unit'] : 'm/s';
        $formatted['wind_direction'] = isset( $forecast['wind_direction'] ) ? $forecast['wind_direction'] : '';
        $formatted['wind_direction_icon'] = get_wind_direction_icon_svg( $forecast['wind_direction'] ?? 270 );
        
        // Other metrics.
        $formatted['humidity'] = isset( $forecast['humidity'] ) ? $forecast['humidity'] : '';
        $formatted['pressure'] = isset( $forecast['pressure'] ) ? $forecast['pressure'] : '';
        $formatted['pressure_unit'] = isset( $measurement_units['pressure_unit'] ) ? $measurement_units['pressure_unit'] : 'hPa';
        $formatted['precipitation'] = isset( $forecast['precipitation'] ) ? $forecast['precipitation'] : '';
        $formatted['precip_unit'] = isset( $measurement_units['precipitation_unit'] ) ? $measurement_units['precipitation_unit'] : 'mm';
        $formatted['rain_chance'] = isset( $forecast['rain'] ) ? $forecast['rain'] : ( isset( $forecast['rain_chance'] ) ? $forecast['rain_chance'] : '' );
        $formatted['snow'] = isset( $forecast['snow'] ) ? $forecast['snow'] : '';
        $formatted['uv_index'] = isset( $forecast['uvi'] ) ? $forecast['uvi'] : ( isset( $forecast['uv_index'] ) ? $forecast['uv_index'] : '' );
        $formatted['clouds'] = isset( $forecast['clouds'] ) ? $forecast['clouds'] : '';
        $formatted['condition'] = isset( $forecast['description'] ) ? $forecast['description'] : ( isset( $forecast['desc'] ) ? $forecast['desc'] : '' );
        
        // Icon URL.
        $icon_code = isset( $forecast['icon'] ) ? $forecast['icon'] : '';
        if ( ! empty( $icon_code ) && function_exists( 'pearl_weather_get_forecast_icon_url' ) ) {
            $formatted['icon_url'] = pearl_weather_get_forecast_icon_url( $icon_code );
        }
        
        return $formatted;
    }
}

/**
 * Helper function for table column icons.
 */
if ( ! function_exists( 'get_table_column_icon' ) ) {
    /**
     * Get icon class for table column.
     *
     * @param string $column Column name.
     * @return string
     */
    function get_table_column_icon( $column ) {
        $icons = array(
            'temperature'  => 'pw-icon-temperature',
            'humidity'     => 'pw-icon-humidity',
            'wind'         => 'pw-icon-wind',
            'pressure'     => 'pw-icon-pressure',
            'precipitation'=> 'pw-icon-precipitation',
            'rain_chance'  => 'pw-icon-rain-chance',
            'clouds'       => 'pw-icon-clouds',
            'snow'         => 'pw-icon-snow',
            'uv_index'     => 'pw-icon-uv-index',
            'weather'      => 'pw-icon-weather',
            'day'          => 'pw-icon-calendar',
            'hour'         => 'pw-icon-clock',
        );
        
        return isset( $icons[ $column ] ) ? $icons[ $column ] : '';
    }
}

/**
 * Helper function for wind direction icon.
 */
if ( ! function_exists( 'get_wind_direction_icon_svg' ) ) {
    /**
     * Get wind direction icon SVG.
     *
     * @param int $degrees Wind direction in degrees.
     * @return string
     */
    function get_wind_direction_icon_svg( $degrees ) {
        $angle = ( $degrees - 90 ) % 360;
        return sprintf(
            '<span class="pw-wind-direction-icon" style="transform: rotate(%ddeg); display: inline-block;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L12 22M12 2L5 9M12 2L19 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>',
            esc_attr( $angle )
        );
    }
}