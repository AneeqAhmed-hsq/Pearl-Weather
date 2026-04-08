<?php
/**
 * Weather Table Template Main Renderer
 *
 * Renders weather data in a table layout with current weather,
 * additional data, and forecast table sections.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Templates
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
 * - $forecast_data: Forecast data array
 * - $template: Template variant name ('table-one', 'table-two')
 * - $unique_id: Unique block identifier
 */

// Display settings.
$show_additional = isset( $attributes['displayAdditionalData'] ) ? (bool) $attributes['displayAdditionalData'] : true;
$show_forecast = isset( $attributes['displayWeatherForecastData'] ) ? (bool) $attributes['displayWeatherForecastData'] : true;
$show_attribution = isset( $attributes['displayWeatherAttribution'] ) ? (bool) $attributes['displayWeatherAttribution'] : true;
$show_last_update = isset( $attributes['displayDateUpdateTime'] ) ? (bool) $attributes['displayDateUpdateTime'] : false;
$show_location = isset( $attributes['showLocationName'] ) ? (bool) $attributes['showLocationName'] : true;
$show_datetime = isset( $attributes['showCurrentDate'] ) || isset( $attributes['showCurrentTime'] ) ? true : false;

// Table variant.
$table_variant = isset( $template ) ? $template : 'table-one';

// CSS classes.
$wrapper_classes = array( 'pw-weather-table-wrapper', "pw-table-{$table_variant}" );

if ( ! empty( $attributes['tableWrapperCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['tableWrapperCustomClass'] );
}

// Table styling.
$table_bordered = isset( $attributes['tableBordered'] ) ? (bool) $attributes['tableBordered'] : true;
$table_striped = isset( $attributes['tableStriped'] ) ? (bool) $attributes['tableStriped'] : true;
$table_hover = isset( $attributes['tableHover'] ) ? (bool) $attributes['tableHover'] : true;
$table_compact = isset( $attributes['tableCompact'] ) ? (bool) $attributes['tableCompact'] : false;

// Current weather data.
$temperature = isset( $weather_data['temperature'] ) ? $weather_data['temperature'] : '--';
$temp_unit = isset( $weather_data['temp_unit'] ) ? $weather_data['temp_unit'] : '°C';
$city = isset( $weather_data['city'] ) ? $weather_data['city'] : '';
$country = isset( $weather_data['country'] ) ? $weather_data['country'] : '';
$description = isset( $weather_data['description'] ) ? $weather_data['description'] : '';
$icon_url = isset( $weather_data['icon'] ) ? $weather_data['icon'] : '';
$date = isset( $weather_data['date'] ) ? $weather_data['date'] : '';
$time = isset( $weather_data['time'] ) ? $weather_data['time'] : '';
$updated_time = isset( $weather_data['updated_time'] ) ? $weather_data['updated_time'] : '';

// Additional data items.
$additional_items = array(
    'humidity'   => __( 'Humidity', 'pearl-weather' ),
    'pressure'   => __( 'Pressure', 'pearl-weather' ),
    'wind'       => __( 'Wind', 'pearl-weather' ),
    'visibility' => __( 'Visibility', 'pearl-weather' ),
    'clouds'     => __( 'Clouds', 'pearl-weather' ),
);

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ); ?>">
    
    <div class="pw-table-container">
        
        <!-- Current Weather Table -->
        <div class="pw-current-weather-table">
            <table class="pw-weather-table <?php echo $table_bordered ? 'pw-table-bordered' : ''; ?> <?php echo $table_striped ? 'pw-table-striped' : ''; ?> <?php echo $table_hover ? 'pw-table-hover' : ''; ?> <?php echo $table_compact ? 'pw-table-compact' : ''; ?>">
                
                <?php if ( 'table-one' === $table_variant ) : ?>
                    <!-- Table Header -->
                    <thead>
                        <tr class="pw-table-header-row">
                            <th class="pw-col-current" colspan="1">
                                <div class="pw-header-content">
                                    <span class="pw-header-icon">🌤️</span>
                                    <span class="pw-header-label"><?php esc_html_e( 'Current Weather', 'pearl-weather' ); ?></span>
                                </div>
                            </th>
                            <?php if ( $show_additional ) : ?>
                                <th class="pw-col-additional" colspan="1">
                                    <div class="pw-header-content">
                                        <span class="pw-header-icon">📊</span>
                                        <span class="pw-header-label"><?php esc_html_e( 'Additional Data', 'pearl-weather' ); ?></span>
                                    </div>
                                </th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                <?php endif; ?>
                
                <!-- Table Body -->
                <tbody>
                    <tr class="pw-weather-row">
                        
                        <!-- Current Weather Column -->
                        <td class="pw-current-weather-cell">
                            <div class="pw-current-weather-content">
                                
                                <!-- Location -->
                                <?php if ( $show_location && ! empty( $city ) ) : ?>
                                    <div class="pw-location-item">
                                        <span class="pw-item-label"><?php esc_html_e( 'Location', 'pearl-weather' ); ?>:</span>
                                        <span class="pw-item-value">
                                            <?php echo esc_html( $city ); ?>
                                            <?php if ( ! empty( $country ) ) : ?>
                                                , <?php echo esc_html( $country ); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Date & Time -->
                                <?php if ( $show_datetime ) : ?>
                                    <div class="pw-datetime-item">
                                        <span class="pw-item-label"><?php esc_html_e( 'Date & Time', 'pearl-weather' ); ?>:</span>
                                        <span class="pw-item-value">
                                            <?php if ( ! empty( $date ) ) : ?>
                                                <?php echo esc_html( $date ); ?>
                                            <?php endif; ?>
                                            <?php if ( ! empty( $time ) ) : ?>
                                                <?php echo esc_html( $time ); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Weather Icon & Temperature -->
                                <div class="pw-weather-main">
                                    <?php if ( ! empty( $icon_url ) ) : ?>
                                        <div class="pw-weather-icon">
                                            <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $description ); ?>" width="50" height="50">
                                        </div>
                                    <?php endif; ?>
                                    <div class="pw-temperature">
                                        <span class="pw-temp-value"><?php echo esc_html( $temperature ); ?></span>
                                        <span class="pw-temp-unit"><?php echo esc_html( $temp_unit ); ?></span>
                                    </div>
                                </div>
                                
                                <!-- Weather Description -->
                                <?php if ( ! empty( $description ) ) : ?>
                                    <div class="pw-description-item">
                                        <span class="pw-item-label"><?php esc_html_e( 'Condition', 'pearl-weather' ); ?>:</span>
                                        <span class="pw-item-value"><?php echo esc_html( $description ); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Last Updated (if additional data not shown) -->
                                <?php if ( $show_last_update && ! $show_additional && ! empty( $updated_time ) ) : ?>
                                    <div class="pw-updated-item">
                                        <span class="pw-updated-label"><?php esc_html_e( 'Last updated:', 'pearl-weather' ); ?></span>
                                        <span class="pw-updated-time"><?php echo esc_html( $updated_time ); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                            </div>
                        </td>
                        
                        <!-- Additional Data Column -->
                        <?php if ( $show_additional ) : ?>
                            <td class="pw-additional-data-cell">
                                <div class="pw-additional-data-content">
                                    <?php foreach ( $additional_items as $key => $label ) : ?>
                                        <?php
                                        $value = isset( $weather_data[ $key ] ) ? $weather_data[ $key ] : '';
                                        if ( empty( $value ) && '0' !== $value ) {
                                            continue;
                                        }
                                        ?>
                                        <div class="pw-additional-item pw-item-<?php echo esc_attr( $key ); ?>">
                                            <span class="pw-item-label"><?php echo esc_html( $label ); ?>:</span>
                                            <span class="pw-item-value"><?php echo esc_html( $value ); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- Last Updated (in additional column) -->
                                    <?php if ( $show_last_update && ! empty( $updated_time ) ) : ?>
                                        <div class="pw-updated-item">
                                            <span class="pw-updated-label"><?php esc_html_e( 'Last updated:', 'pearl-weather' ); ?></span>
                                            <span class="pw-updated-time"><?php echo esc_html( $updated_time ); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                        
                    </tr>
                    
                    <!-- Attribution Row -->
                    <?php if ( $show_attribution ) : ?>
                        <tr class="pw-attribution-row">
                            <td colspan="2" class="pw-attribution-cell">
                                <?php
                                $footer_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/footer.php';
                                if ( file_exists( $footer_template ) ) {
                                    include $footer_template;
                                } else {
                                    ?>
                                    <div class="pw-attribution">
                                        <a href="https://openweathermap.org/" target="_blank" rel="noopener noreferrer">
                                            <?php esc_html_e( 'Weather data by OpenWeatherMap', 'pearl-weather' ); ?>
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                </tbody>
                
            </table>
        </div>
        
        <!-- Forecast Table Section -->
        <?php if ( $show_forecast && ! empty( $forecast_data ) ) : ?>
            <div class="pw-forecast-table-section">
                <?php
                $forecast_table_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/forecast-table.php';
                if ( file_exists( $forecast_table_template ) ) {
                    include $forecast_table_template;
                } else {
                    // Fallback forecast table.
                    $this->render_forecast_table_fallback( $forecast_data, $attributes );
                }
                ?>
            </div>
        <?php endif; ?>
        
    </div>
    
</div>

<style>
/* Table Template Styles */
.pw-weather-table-wrapper {
    width: 100%;
    margin: 0 auto;
}

.pw-table-container {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* Weather Table */
.pw-weather-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
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
    padding: 8px 12px;
}

/* Default Padding */
.pw-weather-table th,
.pw-weather-table td {
    padding: 16px;
    vertical-align: top;
}

/* Table Header */
.pw-table-header-row th {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid rgba(0, 0, 0, 0.1);
}

.pw-header-content {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pw-header-icon {
    font-size: 18px;
}

/* Current Weather Cell */
.pw-current-weather-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.pw-location-item,
.pw-datetime-item,
.pw-description-item,
.pw-updated-item {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.pw-item-label {
    font-weight: 500;
    color: #666;
    min-width: 100px;
}

.pw-item-value {
    color: #333;
}

/* Weather Main Display */
.pw-weather-main {
    display: flex;
    align-items: center;
    gap: 16px;
    margin: 8px 0;
}

.pw-weather-icon img {
    width: 50px;
    height: 50px;
}

.pw-temperature {
    display: flex;
    align-items: baseline;
    gap: 4px;
}

.pw-temp-value {
    font-size: 32px;
    font-weight: 700;
}

.pw-temp-unit {
    font-size: 16px;
    opacity: 0.7;
}

/* Additional Data Cell */
.pw-additional-data-content {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
}

.pw-additional-item {
    display: flex;
    justify-content: space-between;
    padding: 8px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 6px;
}

/* Updated Item */
.pw-updated-item {
    display: flex;
    gap: 8px;
    font-size: 11px;
    color: #757575;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

/* Attribution Row */
.pw-attribution-cell {
    padding: 12px 16px !important;
    background: #f8f9fa;
    text-align: center;
}

/* Forecast Table Section */
.pw-forecast-table-section {
    margin-top: 20px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

/* Responsive */
@media (max-width: 768px) {
    .pw-weather-table th,
    .pw-weather-table td {
        padding: 12px;
    }
    
    .pw-additional-data-content {
        grid-template-columns: 1fr;
    }
    
    .pw-temp-value {
        font-size: 28px;
    }
    
    .pw-item-label {
        min-width: 80px;
    }
}

@media (max-width: 576px) {
    .pw-weather-table,
    .pw-weather-table tbody,
    .pw-weather-table tr,
    .pw-weather-table td {
        display: block;
    }
    
    .pw-weather-table thead {
        display: none;
    }
    
    .pw-weather-table tr {
        margin-bottom: 16px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .pw-weather-table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .pw-weather-table td:last-child {
        border-bottom: none;
    }
    
    .pw-weather-table td:before {
        content: attr(data-label);
        font-weight: 600;
        margin-right: 12px;
    }
}
</style>

<?php
/**
 * Fallback forecast table renderer.
 */
if ( ! function_exists( 'render_forecast_table_fallback' ) ) {
    function render_forecast_table_fallback( $forecast_data, $attributes ) {
        if ( empty( $forecast_data ) ) return;
        ?>
        <div class="pw-forecast-table-fallback">
            <h4 class="pw-fallback-title"><?php esc_html_e( 'Hourly Forecast', 'pearl-weather' ); ?></h4>
            <div class="pw-fallback-scroll">
                <table class="pw-fallback-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Time', 'pearl-weather' ); ?></th>
                            <th><?php esc_html_e( 'Temp', 'pearl-weather' ); ?></th>
                            <th><?php esc_html_e( 'Condition', 'pearl-weather' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( array_slice( $forecast_data, 0, 8 ) as $item ) : ?>
                            <tr>
                                <td><?php echo esc_html( $item['time'] ?? '' ); ?></td>
                                <td><?php echo esc_html( $item['temp'] ?? '--' ); ?>°</td>
                                <td><?php echo esc_html( $item['condition'] ?? '' ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <style>
            .pw-forecast-table-fallback { margin-top: 20px; padding: 16px; }
            .pw-fallback-title { margin: 0 0 16px 0; font-size: 16px; }
            .pw-fallback-scroll { overflow-x: auto; }
            .pw-fallback-table { width: 100%; border-collapse: collapse; }
            .pw-fallback-table th,
            .pw-fallback-table td { padding: 8px; text-align: left; border-bottom: 1px solid #eee; }
        </style>
        <?php
    }
}