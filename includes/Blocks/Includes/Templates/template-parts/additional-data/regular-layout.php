<?php
/**
 * Additional Data Regular Layout Renderer
 *
 * Displays additional weather data in a regular grid/flex layout.
 * Supports separate comport data section for pressure, humidity, and wind.
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
 * - $additional_data_options: Array of active data options
 * - $display_comport_layout: Whether to separate comport data
 * - $comport_data: Array of comport data options (pressure, humidity, wind)
 * - $is_swiper_layout: Whether this is a carousel layout (skip if true)
 * - $data_layout: Current layout type
 * - $show_colon: Whether to show colon after labels
 */

// Skip if this is a carousel layout.
if ( $is_swiper_layout ) {
    return;
}

// Get layout classes.
$layout_class = isset( $data_layout ) ? 'pw-data-layout-' . $data_layout : 'pw-data-layout-default';

?>

<!-- Main Additional Data Section -->
<div class="pw-regular-data-wrapper" data-layout="<?php echo esc_attr( $data_layout ?? 'default' ); ?>">
    
    <!-- Primary Data Items -->
    <div class="pw-data-items-container <?php echo esc_attr( $layout_class ); ?>">
        <div class="pw-data-items-grid">
            
            <?php foreach ( $additional_data_options as $option ) : ?>
                <?php
                // Skip comport data if being handled separately.
                if ( $display_comport_layout && in_array( $option, array( 'pressure', 'humidity', 'wind' ), true ) ) {
                    continue;
                }
                
                // Get the value for this option.
                $value = '';
                $unit = '';
                
                switch ( $option ) {
                    case 'humidity':
                        $value = isset( $weather_data['humidity'] ) 
                            ? ( is_array( $weather_data['humidity'] ) ? $weather_data['humidity']['value'] : $weather_data['humidity'] ) 
                            : '';
                        $unit = '%';
                        break;
                    case 'pressure':
                        $value = isset( $weather_data['pressure'] ) ? $weather_data['pressure'] : '';
                        $unit = isset( $weather_data['pressure_unit'] ) ? $weather_data['pressure_unit'] : 'hPa';
                        break;
                    case 'wind':
                        $value = isset( $weather_data['wind'] ) ? $weather_data['wind'] : '';
                        break;
                    case 'visibility':
                        $value = isset( $weather_data['visibility'] ) ? $weather_data['visibility'] : '';
                        break;
                    case 'clouds':
                        $value = isset( $weather_data['clouds'] ) ? $weather_data['clouds'] : '';
                        $unit = '%';
                        break;
                    case 'uv_index':
                        $value = isset( $weather_data['uv_index'] ) ? $weather_data['uv_index'] : '';
                        break;
                    case 'dew_point':
                        $value = isset( $weather_data['dew_point'] ) ? $weather_data['dew_point'] : '';
                        $unit = isset( $weather_data['temp_unit'] ) ? $weather_data['temp_unit'] : '°C';
                        break;
                    case 'precipitation':
                        $value = isset( $weather_data['precipitation'] ) ? $weather_data['precipitation'] : '';
                        $unit = isset( $weather_data['precipitation_unit'] ) ? $weather_data['precipitation_unit'] : 'mm';
                        break;
                    case 'rain_chance':
                        $value = isset( $weather_data['rain_chance'] ) ? $weather_data['rain_chance'] : '';
                        $unit = '%';
                        break;
                    case 'sunrise':
                        $value = isset( $weather_data['sunrise'] ) ? $weather_data['sunrise'] : '';
                        break;
                    case 'sunset':
                        $value = isset( $weather_data['sunset'] ) ? $weather_data['sunset'] : '';
                        break;
                    default:
                        $value = isset( $weather_data[ $option ] ) ? $weather_data[ $option ] : '';
                        break;
                }
                
                // Skip if no value.
                if ( empty( $value ) && '0' !== $value ) {
                    continue;
                }
                
                // Get label and icon.
                $label = get_additional_data_label( $option );
                $icon = get_additional_data_icon( $option );
                $colon = isset( $show_colon ) && $show_colon ? ':' : '';
                ?>
                
                <div class="pw-data-item pw-item-<?php echo esc_attr( $option ); ?>" 
                     data-option="<?php echo esc_attr( $option ); ?>"
                     title="<?php echo esc_attr( $label ); ?>">
                    
                    <?php if ( 'justified' === $data_layout ) : ?>
                        <!-- Justified Layout: Label left, Value right -->
                        <div class="pw-data-label-wrapper">
                            <?php if ( ! empty( $icon ) ) : ?>
                                <span class="pw-data-icon"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                            <?php endif; ?>
                            <span class="pw-data-label"><?php echo esc_html( $label ); ?><?php echo esc_html( $colon ); ?></span>
                        </div>
                        <div class="pw-data-value-wrapper">
                            <span class="pw-data-value"><?php echo esc_html( $value ); ?></span>
                            <?php if ( ! empty( $unit ) ) : ?>
                                <span class="pw-data-unit"><?php echo esc_html( $unit ); ?></span>
                            <?php endif; ?>
                        </div>
                        
                    <?php else : ?>
                        <!-- Standard Layout: Icon, Label, Value stacked or inline -->
                        <div class="pw-data-content">
                            <?php if ( ! empty( $icon ) ) : ?>
                                <div class="pw-data-icon"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                            <?php endif; ?>
                            
                            <div class="pw-data-info">
                                <span class="pw-data-label"><?php echo esc_html( $label ); ?><?php echo esc_html( $colon ); ?></span>
                                <span class="pw-data-value">
                                    <?php echo esc_html( $value ); ?>
                                    <?php if ( ! empty( $unit ) ) : ?>
                                        <span class="pw-data-unit"><?php echo esc_html( $unit ); ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>
            <?php endforeach; ?>
            
        </div>
    </div>
    
    <!-- Comport Data Section (Pressure, Humidity, Wind) -->
    <?php if ( $display_comport_layout && ! empty( $comport_data ) ) : ?>
        <div class="pw-comport-data-section">
            <div class="pw-comport-header">
                <div class="pw-comport-title"><?php esc_html_e( 'Weather Details', 'pearl-weather' ); ?></div>
                <div class="pw-comport-divider"></div>
            </div>
            
            <div class="pw-comport-items">
                <?php foreach ( $comport_data as $option ) : ?>
                    <?php
                    // Get value based on option type.
                    $value = '';
                    $unit = '';
                    
                    switch ( $option ) {
                        case 'humidity':
                            $value = isset( $weather_data['humidity'] ) 
                                ? ( is_array( $weather_data['humidity'] ) ? $weather_data['humidity']['value'] : $weather_data['humidity'] ) 
                                : '';
                            $unit = '%';
                            break;
                        case 'pressure':
                            $value = isset( $weather_data['pressure'] ) ? $weather_data['pressure'] : '';
                            $unit = isset( $weather_data['pressure_unit'] ) ? $weather_data['pressure_unit'] : 'hPa';
                            break;
                        case 'wind':
                            $value = isset( $weather_data['wind'] ) ? $weather_data['wind'] : '';
                            break;
                    }
                    
                    if ( empty( $value ) && '0' !== $value ) {
                        continue;
                    }
                    
                    $label = get_additional_data_label( $option );
                    $icon = get_additional_data_icon( $option );
                    ?>
                    
                    <div class="pw-comport-item pw-item-<?php echo esc_attr( $option ); ?>">
                        <div class="pw-comport-icon"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                        <div class="pw-comport-info">
                            <span class="pw-comport-label"><?php echo esc_html( $label ); ?></span>
                            <span class="pw-comport-value">
                                <?php echo esc_html( $value ); ?>
                                <?php if ( ! empty( $unit ) ) : ?>
                                    <span class="pw-comport-unit"><?php echo esc_html( $unit ); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Regular Data Layout Styles */
.pw-regular-data-wrapper {
    width: 100%;
}

/* Data Items Grid */
.pw-data-items-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

/* Layout Variations */
.pw-data-layout-center .pw-data-items-grid {
    justify-content: center;
}

.pw-data-layout-center .pw-data-item {
    text-align: center;
    flex: 0 1 auto;
    min-width: 100px;
}

.pw-data-layout-left .pw-data-items-grid {
    justify-content: flex-start;
}

.pw-data-layout-justified .pw-data-item {
    flex: 1;
    min-width: 150px;
}

.pw-data-layout-column-two .pw-data-items-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.pw-data-layout-column-two-justified .pw-data-items-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.pw-data-layout-column-two-justified .pw-data-item {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
}

.pw-data-layout-grid-one .pw-data-items-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
}

.pw-data-layout-grid-two .pw-data-items-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
}

.pw-data-layout-grid-three .pw-data-items-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
}

.pw-data-layout-horizontal-list .pw-data-items-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
}

/* Data Item */
.pw-data-item {
    display: flex;
    align-items: center;
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

.pw-data-content {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
}

.pw-data-icon svg {
    width: 20px;
    height: 20px;
}

.pw-data-info {
    display: flex;
    flex-direction: column;
}

.pw-data-label {
    font-size: 11px;
    opacity: 0.6;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.pw-data-value {
    font-size: 16px;
    font-weight: 600;
    line-height: 1.3;
}

.pw-data-unit {
    font-size: 12px;
    font-weight: 400;
    margin-left: 2px;
}

/* Comport Data Section */
.pw-comport-data-section {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-comport-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.pw-comport-title {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    opacity: 0.5;
}

.pw-comport-divider {
    flex: 1;
    height: 1px;
    background: rgba(0, 0, 0, 0.08);
}

.pw-comport-items {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.pw-comport-item {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 120px;
}

.pw-comport-icon svg {
    width: 24px;
    height: 24px;
    opacity: 0.7;
}

.pw-comport-info {
    display: flex;
    flex-direction: column;
}

.pw-comport-label {
    font-size: 11px;
    opacity: 0.6;
}

.pw-comport-value {
    font-size: 15px;
    font-weight: 600;
}

.pw-comport-unit {
    font-size: 11px;
    font-weight: 400;
    margin-left: 2px;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-data-layout-column-two .pw-data-items-grid,
    .pw-data-layout-column-two-justified .pw-data-items-grid,
    .pw-data-layout-grid-two .pw-data-items-grid,
    .pw-data-layout-grid-three .pw-data-items-grid {
        grid-template-columns: 1fr;
    }
    
    .pw-comport-items {
        flex-direction: column;
        gap: 12px;
    }
    
    .pw-comport-item {
        width: 100%;
    }
}
</style>