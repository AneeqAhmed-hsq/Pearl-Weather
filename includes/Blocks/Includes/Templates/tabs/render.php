<?php
/**
 * Weather Tabs Template Main Renderer
 *
 * Renders weather data in a tabbed interface with panels for
 * current weather, hourly forecast, and radar map.
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
 * - $template: Template variant name ('tabs-one', 'tabs-two')
 * - $unique_id: Unique block identifier
 */

// Tab settings.
$default_tab = isset( $attributes['splwDefaultOpenTab'] ) ? sanitize_text_field( $attributes['splwDefaultOpenTab'] ) : 'current_weather';
$tab_alignment = isset( $attributes['splwTabAlignment'] ) ? sanitize_text_field( $attributes['splwTabAlignment'] ) : 'left';
$tab_orientation = isset( $attributes['splwTabOrientation'] ) ? sanitize_text_field( $attributes['splwTabOrientation'] ) : 'horizontal';
$show_forecast = isset( $attributes['displayWeatherForecastData'] ) ? (bool) $attributes['displayWeatherForecastData'] : true;
$show_map = isset( $attributes['displayWeatherMap'] ) ? (bool) $attributes['displayWeatherMap'] : true;
$show_additional = isset( $attributes['displayAdditionalData'] ) ? (bool) $attributes['displayAdditionalData'] : true;
$show_attribution = isset( $attributes['displayWeatherAttribution'] ) ? (bool) $attributes['displayWeatherAttribution'] : true;
$show_last_update = isset( $attributes['displayDateUpdateTime'] ) ? (bool) $attributes['displayDateUpdateTime'] : false;
$show_sun_orbit = isset( $attributes['showSunOrbit'] ) ? (bool) $attributes['showSunOrbit'] : true;

// Forecast settings.
$forecast_title = isset( $attributes['hourlyTitle'] ) ? sanitize_text_field( $attributes['hourlyTitle'] ) : __( 'Hourly Forecast', 'pearl-weather' );
$forecast_type = 'hourly';

// Tab alignment class.
$alignment_class = '';
if ( 'horizontal' === $tab_orientation ) {
    switch ( $tab_alignment ) {
        case 'left':
            $alignment_class = 'pw-tabs-align-left';
            break;
        case 'center':
            $alignment_class = 'pw-tabs-align-center';
            break;
        case 'right':
            $alignment_class = 'pw-tabs-align-right';
            break;
        default:
            $alignment_class = 'pw-tabs-align-left';
    }
} else {
    $alignment_class = 'pw-tabs-vertical';
}

// Tab navigation items.
$tab_items = array();

// Current Weather Tab.
$tab_items[] = array(
    'id'    => 'current_weather',
    'label' => __( 'Current Weather', 'pearl-weather' ),
    'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="5" fill="currentColor"/><path d="M12 2L12 4M12 20L12 22M2 12L4 12M20 12L22 12M4.929 4.929L6.343 6.343M17.657 17.657L19.071 19.071M19.071 4.929L17.657 6.343M6.343 17.657L4.929 19.071" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
);

// Forecast Tab.
if ( $show_forecast && ! empty( $forecast_data ) ) {
    $tab_items[] = array(
        'id'    => 'forecast',
        'label' => $forecast_title,
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12H21M12 3V21M7 7L17 17M7 17L17 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/></svg>',
    );
}

// Map Tab.
if ( $show_map ) {
    $tab_items[] = array(
        'id'    => 'map',
        'label' => __( 'Radar Map', 'pearl-weather' ),
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 10.5C21 16 12 23 12 23C12 23 3 16 3 10.5C3 6.35786 7.02944 3 12 3C16.9706 3 21 6.35786 21 10.5Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/></svg>',
    );
}

// CSS classes.
$wrapper_classes = array( 'pw-weather-tabs-wrapper', "pw-tabs-{$tab_orientation}", $alignment_class );

if ( ! empty( $attributes['tabsCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['tabsCustomClass'] );
}

// Get updated time.
$updated_time = isset( $weather_data['updated_time'] ) ? $weather_data['updated_time'] : '';

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     data-default-tab="<?php echo esc_attr( $default_tab ); ?>">
    
    <!-- Tab Navigation -->
    <div class="pw-tabs-navigation">
        <ul class="pw-tab-list" role="tablist">
            <?php foreach ( $tab_items as $index => $tab ) : ?>
                <li class="pw-tab-item <?php echo $tab['id'] === $default_tab ? 'pw-active' : ''; ?>" 
                    role="presentation">
                    <button class="pw-tab-button"
                            id="pw-tab-<?php echo esc_attr( $tab['id'] ); ?>"
                            role="tab"
                            aria-selected="<?php echo $tab['id'] === $default_tab ? 'true' : 'false'; ?>"
                            aria-controls="pw-panel-<?php echo esc_attr( $tab['id'] ); ?>"
                            data-tab="<?php echo esc_attr( $tab['id'] ); ?>">
                        <span class="pw-tab-icon"><?php echo $tab['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <span class="pw-tab-label"><?php echo esc_html( $tab['label'] ); ?></span>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="pw-tab-indicator"></div>
    </div>
    
    <!-- Tab Panels -->
    <div class="pw-tabs-content">
        
        <!-- Current Weather Panel -->
        <div class="pw-tab-panel <?php echo 'current_weather' === $default_tab ? 'pw-active' : ''; ?>"
             id="pw-panel-current_weather"
             role="tabpanel"
             aria-labelledby="pw-tab-current_weather">
            
            <div class="pw-panel-content">
                
                <!-- Current Weather + Sun Orbit (for tabs-one) -->
                <div class="pw-current-weather-row">
                    <div class="pw-current-weather-col">
                        <?php
                        $current_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/current-weather.php';
                        if ( file_exists( $current_template ) ) {
                            include $current_template;
                        } else {
                            $this->render_current_weather_fallback( $weather_data, $attributes );
                        }
                        ?>
                    </div>
                    
                    <?php if ( 'tabs-one' === $template && $show_sun_orbit ) : ?>
                        <div class="pw-sun-orbit-col">
                            <?php
                            $sun_orbit_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/sun-orbit.php';
                            if ( file_exists( $sun_orbit_template ) ) {
                                include $sun_orbit_template;
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Additional Data -->
                <?php if ( $show_additional ) : ?>
                    <div class="pw-additional-data-section">
                        <?php
                        $additional_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/additional-data/additional-data.php';
                        if ( file_exists( $additional_template ) ) {
                            include $additional_template;
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <!-- Last Updated -->
                <?php if ( $show_last_update && ! empty( $updated_time ) ) : ?>
                    <div class="pw-last-updated">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <span class="pw-updated-label"><?php esc_html_e( 'Last updated:', 'pearl-weather' ); ?></span>
                        <span class="pw-updated-time"><?php echo esc_html( $updated_time ); ?></span>
                    </div>
                <?php endif; ?>
                
                <!-- Footer -->
                <?php if ( $show_attribution ) : ?>
                    <div class="pw-footer-section">
                        <?php
                        $footer_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/footer.php';
                        if ( file_exists( $footer_template ) ) {
                            include $footer_template;
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
        
        <!-- Forecast Panel -->
        <?php if ( $show_forecast && ! empty( $forecast_data ) ) : ?>
            <div class="pw-tab-panel <?php echo 'forecast' === $default_tab ? 'pw-active' : ''; ?>"
                 id="pw-panel-forecast"
                 role="tabpanel"
                 aria-labelledby="pw-tab-forecast">
                
                <div class="pw-panel-content">
                    <?php
                    $forecast_table_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/forecast-data/forecast-table.php';
                    if ( file_exists( $forecast_table_template ) ) {
                        include $forecast_table_template;
                    } else {
                        $this->render_forecast_table_fallback( $forecast_data, $attributes );
                    }
                    ?>
                </div>
                
            </div>
        <?php endif; ?>
        
        <!-- Map Panel -->
        <?php if ( $show_map ) : ?>
            <div class="pw-tab-panel <?php echo 'map' === $default_tab ? 'pw-active' : ''; ?>"
                 id="pw-panel-map"
                 role="tabpanel"
                 aria-labelledby="pw-tab-map">
                
                <div class="pw-panel-content">
                    <?php
                    $map_template = PEARL_WEATHER_TEMPLATE_PATH . 'blocks/parts/weather-map.php';
                    if ( file_exists( $map_template ) ) {
                        include $map_template;
                    }
                    ?>
                </div>
                
            </div>
        <?php endif; ?>
        
    </div>
    
</div>

<style>
/* Tabs Template Styles */
.pw-weather-tabs-wrapper {
    width: 100%;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* Tab Navigation */
.pw-tabs-navigation {
    position: relative;
    background: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-tab-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
}

/* Tab Alignment - Horizontal */
.pw-tabs-align-left .pw-tab-list {
    justify-content: flex-start;
}

.pw-tabs-align-center .pw-tab-list {
    justify-content: center;
}

.pw-tabs-align-right .pw-tab-list {
    justify-content: flex-end;
}

/* Vertical Tabs */
.pw-tabs-vertical {
    display: flex;
}

.pw-tabs-vertical .pw-tabs-navigation {
    width: 200px;
    flex-shrink: 0;
    border-bottom: none;
    border-right: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-tabs-vertical .pw-tab-list {
    flex-direction: column;
}

.pw-tabs-vertical .pw-tabs-content {
    flex: 1;
}

/* Tab Item */
.pw-tab-item {
    margin: 0;
}

.pw-tab-button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    transition: all 0.2s ease;
    width: 100%;
    text-align: left;
}

.pw-tab-button:hover {
    background: rgba(0, 0, 0, 0.04);
    color: #333;
}

.pw-tab-item.pw-active .pw-tab-button {
    color: var(--pw-primary-color, #f26c0d);
    background: #fff;
}

.pw-tab-icon svg {
    width: 18px;
    height: 18px;
}

/* Tab Indicator */
.pw-tab-indicator {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 2px;
    background: var(--pw-primary-color, #f26c0d);
    transition: transform 0.3s ease, width 0.3s ease;
}

.pw-tabs-vertical .pw-tab-indicator {
    top: 0;
    bottom: auto;
    width: 2px;
    height: auto;
}

/* Tab Panels */
.pw-tabs-content {
    padding: 20px;
}

.pw-tab-panel {
    display: none;
}

.pw-tab-panel.pw-active {
    display: block;
}

/* Current Weather Row */
.pw-current-weather-row {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
}

.pw-current-weather-col {
    flex: 1;
    min-width: 250px;
}

.pw-sun-orbit-col {
    flex-shrink: 0;
}

/* Additional Data Section */
.pw-additional-data-section {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

/* Last Updated */
.pw-last-updated {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    font-size: 11px;
    color: #757575;
    justify-content: flex-end;
}

/* Footer Section */
.pw-footer-section {
    margin-top: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-tabs-vertical {
        flex-direction: column;
    }
    
    .pw-tabs-vertical .pw-tabs-navigation {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }
    
    .pw-tabs-vertical .pw-tab-list {
        flex-direction: row;
        overflow-x: auto;
    }
    
    .pw-tabs-vertical .pw-tab-button {
        white-space: nowrap;
    }
    
    .pw-tab-button {
        padding: 10px 16px;
        font-size: 13px;
    }
    
    .pw-tabs-content {
        padding: 16px;
    }
    
    .pw-current-weather-row {
        flex-direction: column;
    }
}
</style>

<script>
/**
 * Tabs functionality for weather tabs template.
 */
(function() {
    const tabsWrapper = document.querySelector('.pw-weather-tabs-wrapper');
    if (!tabsWrapper) return;
    
    const tabButtons = tabsWrapper.querySelectorAll('.pw-tab-button');
    const tabPanels = tabsWrapper.querySelectorAll('.pw-tab-panel');
    const indicator = tabsWrapper.querySelector('.pw-tab-indicator');
    const isVertical = tabsWrapper.classList.contains('pw-tabs-vertical');
    
    function updateIndicator(activeTab) {
        if (!indicator) return;
        
        if (isVertical) {
            const tabRect = activeTab.getBoundingClientRect();
            const navRect = tabsWrapper.querySelector('.pw-tabs-navigation').getBoundingClientRect();
            indicator.style.transform = `translateY(${activeTab.offsetTop}px)`;
            indicator.style.height = `${activeTab.offsetHeight}px`;
        } else {
            const tabRect = activeTab.getBoundingClientRect();
            const navRect = tabsWrapper.querySelector('.pw-tab-list').getBoundingClientRect();
            indicator.style.transform = `translateX(${activeTab.offsetLeft}px)`;
            indicator.style.width = `${activeTab.offsetWidth}px`;
        }
    }
    
    function activateTab(tabId) {
        // Update button states
        tabButtons.forEach(btn => {
            const btnTab = btn.getAttribute('data-tab');
            const parent = btn.closest('.pw-tab-item');
            if (btnTab === tabId) {
                parent.classList.add('pw-active');
                btn.setAttribute('aria-selected', 'true');
                updateIndicator(btn);
            } else {
                parent.classList.remove('pw-active');
                btn.setAttribute('aria-selected', 'false');
            }
        });
        
        // Update panel states
        tabPanels.forEach(panel => {
            const panelId = panel.getAttribute('id').replace('pw-panel-', '');
            if (panelId === tabId) {
                panel.classList.add('pw-active');
            } else {
                panel.classList.remove('pw-active');
            }
        });
        
        // Trigger resize for map if needed
        if (tabId === 'map') {
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 100);
        }
    }
    
    // Add click handlers
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.getAttribute('data-tab');
            activateTab(tabId);
        });
    });
    
    // Initialize indicator position
    const activeTab = tabsWrapper.querySelector('.pw-tab-item.pw-active .pw-tab-button');
    if (activeTab) {
        updateIndicator(activeTab);
    }
})();
</script>

<?php
/**
 * Fallback renderers for tabs template.
 */
if ( ! function_exists( 'render_current_weather_fallback' ) ) {
    function render_current_weather_fallback( $weather_data, $attributes ) {
        $temperature = isset( $weather_data['temperature'] ) ? $weather_data['temperature'] : '--';
        $temp_unit = isset( $weather_data['temp_unit'] ) ? $weather_data['temp_unit'] : '°C';
        $city = isset( $weather_data['city'] ) ? $weather_data['city'] : '';
        ?>
        <div class="pw-current-fallback">
            <?php if ( ! empty( $city ) ) : ?>
                <div class="pw-location"><?php echo esc_html( $city ); ?></div>
            <?php endif; ?>
            <div class="pw-temp"><?php echo esc_html( $temperature ); ?><?php echo esc_html( $temp_unit ); ?></div>
        </div>
        <?php
    }
}

if ( ! function_exists( 'render_forecast_table_fallback' ) ) {
    function render_forecast_table_fallback( $forecast_data, $attributes ) {
        if ( empty( $forecast_data ) ) return;
        ?>
        <div class="pw-forecast-fallback">
            <table class="pw-fallback-table">
                <thead>
                    <tr><th><?php esc_html_e( 'Time', 'pearl-weather' ); ?></th><th><?php esc_html_e( 'Temp', 'pearl-weather' ); ?></th><th><?php esc_html_e( 'Condition', 'pearl-weather' ); ?></th></tr>
                </thead>
                <tbody>
                    <?php foreach ( array_slice( $forecast_data, 0, 6 ) as $item ) : ?>
                        <tr><td><?php echo esc_html( $item['time'] ?? '' ); ?></td><td><?php echo esc_html( $item['temp'] ?? '--' ); ?>°</td><td><?php echo esc_html( $item['condition'] ?? '' ); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}