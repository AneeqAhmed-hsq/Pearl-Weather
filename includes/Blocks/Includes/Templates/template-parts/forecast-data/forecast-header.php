<?php
/**
 * Forecast Header with Live Filter Template Part
 *
 * Renders the forecast header with tabs or dropdown select
 * for switching between different forecast data types.
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
 * - $forecast_options: Array of active forecast options
 * - $active_forecast: Currently active forecast option
 * - $weather_item_labels: Array of item labels
 * - $template: Template variant name
 * - $pre_defined_type: Pre-defined filter type (for popups, etc.)
 * - $forecast_popup_title: Title for popup mode
 */

// Determine live filter type.
$filter_type = 'tabs'; // Default.

$templates_for_tabs = array( 'horizontal-one', 'grid-one', 'grid-card', 'vertical-one' );
$templates_for_select = array( 'vertical-three', 'tabs-one', 'table-one', 'horizontal-two' );

if ( in_array( $template, $templates_for_tabs, true ) ) {
    $filter_type = 'tabs';
} elseif ( in_array( $template, $templates_for_select, true ) ) {
    $filter_type = 'select';
}

// Override with pre-defined type if provided.
$filter_type = isset( $pre_defined_type ) ? $pre_defined_type : $filter_type;

// Get forecast title.
$forecast_title = isset( $attributes['hourlyTitle'] ) ? sanitize_text_field( $attributes['hourlyTitle'] ) : __( 'Hourly Forecast', 'pearl-weather' );

// Popup title (for modal views).
$popup_title = isset( $forecast_popup_title ) ? sanitize_text_field( $forecast_popup_title ) : '';

// Additional settings.
$show_icons_in_tabs = isset( $attributes['forecastTabsShowIcons'] ) ? (bool) $attributes['forecastTabsShowIcons'] : false;
$tabs_alignment = isset( $attributes['forecastTabsAlignment'] ) ? sanitize_text_field( $attributes['forecastTabsAlignment'] ) : 'left';
$select_width = isset( $attributes['forecastSelectWidth'] ) ? (int) $attributes['forecastSelectWidth'] : 140;

// CSS classes.
$wrapper_classes = array( 'pw-forecast-header', 'pw-forecast-filter-' . $filter_type );

if ( ! empty( $attributes['forecastHeaderCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['forecastHeaderCustomClass'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     data-filter-type="<?php echo esc_attr( $filter_type ); ?>"
     data-active-forecast="<?php echo esc_attr( $active_forecast ); ?>">
    
    <?php if ( 'select' === $filter_type ) : ?>
        <!-- Dropdown Select Filter -->
        <div class="pw-forecast-select-wrapper">
            
            <!-- Title Section -->
            <div class="pw-forecast-title-section">
                <?php if ( ! empty( $popup_title ) ) : ?>
                    <span class="pw-forecast-popup-title"><?php echo esc_html( $popup_title ); ?></span>
                <?php endif; ?>
                <span class="pw-forecast-title"><?php echo esc_html( $forecast_title ); ?></span>
            </div>
            
            <!-- Custom Select Dropdown -->
            <div class="pw-forecast-select" style="--pw-select-width: <?php echo esc_attr( $select_width ); ?>px;">
                
                <!-- Select Trigger -->
                <button class="pw-select-trigger" 
                        aria-label="<?php esc_attr_e( 'Select forecast data type', 'pearl-weather' ); ?>"
                        aria-expanded="false">
                    <span class="pw-select-selected-option" data-value="<?php echo esc_attr( $active_forecast ); ?>">
                        <?php echo esc_html( $weather_item_labels[ $active_forecast ] ); ?>
                    </span>
                    <span class="pw-select-icon" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
                
                <!-- Dropdown Options -->
                <ul class="pw-select-options" role="listbox">
                    <?php foreach ( $forecast_options as $value ) : ?>
                        <li class="pw-select-option <?php echo ( $active_forecast === $value ) ? 'pw-active' : ''; ?>"
                            data-value="<?php echo esc_html( $value ); ?>"
                            role="option"
                            aria-selected="<?php echo ( $active_forecast === $value ) ? 'true' : 'false'; ?>">
                            <?php echo esc_html( $weather_item_labels[ $value ] ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
            </div>
            
        </div>
        
    <?php else : ?>
        <!-- Tabs Filter -->
        <div class="pw-forecast-tabs-wrapper pw-tabs-align-<?php echo esc_attr( $tabs_alignment ); ?>">
            
            <!-- Title (optional) -->
            <?php if ( ! empty( $popup_title ) ) : ?>
                <div class="pw-forecast-popup-title-wrapper">
                    <span class="pw-forecast-popup-title"><?php echo esc_html( $popup_title ); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Tabs Navigation -->
            <ul class="pw-forecast-tabs" role="tablist">
                <?php foreach ( $forecast_options as $item ) : ?>
                    <li class="pw-forecast-tab-item <?php echo ( $active_forecast === $item ) ? 'pw-active' : ''; ?>"
                        role="tab"
                        aria-selected="<?php echo ( $active_forecast === $item ) ? 'true' : 'false'; ?>">
                        <button class="pw-forecast-tab-btn"
                                data-value="<?php echo esc_html( $item ); ?>">
                            
                            <?php if ( $show_icons_in_tabs ) : ?>
                                <span class="pw-tab-icon">
                                    <i class="<?php echo esc_attr( get_forecast_tab_icon( $item ) ); ?>"></i>
                                </span>
                            <?php endif; ?>
                            
                            <span class="pw-tab-label">
                                <?php echo esc_html( $weather_item_labels[ $item ] ); ?>
                            </span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Active Tab Indicator Line -->
            <div class="pw-tabs-indicator"></div>
            
        </div>
    <?php endif; ?>
    
</div>

<style>
/* Forecast Header Styles */
.pw-forecast-header {
    margin-bottom: 16px;
}

/* Select Dropdown Styles */
.pw-forecast-select-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.pw-forecast-title-section {
    display: flex;
    align-items: baseline;
    gap: 6px;
}

.pw-forecast-popup-title {
    font-size: 12px;
    font-weight: 500;
    opacity: 0.7;
}

.pw-forecast-title {
    font-size: 16px;
    font-weight: 600;
}

/* Custom Select */
.pw-forecast-select {
    position: relative;
    width: var(--pw-select-width, 140px);
}

.pw-select-trigger {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s ease;
}

.pw-select-trigger:hover {
    border-color: var(--pw-primary-color, #f26c0d);
}

.pw-select-icon svg {
    transition: transform 0.2s ease;
}

.pw-select-trigger[aria-expanded="true"] .pw-select-icon svg {
    transform: rotate(180deg);
}

.pw-select-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-top: 4px;
    list-style: none;
    padding: 4px 0;
    z-index: 100;
    display: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.pw-select-options.show {
    display: block;
}

.pw-select-option {
    padding: 8px 12px;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.2s ease;
}

.pw-select-option:hover {
    background: rgba(0, 0, 0, 0.05);
}

.pw-select-option.pw-active {
    background: var(--pw-primary-color, #f26c0d);
    color: #fff;
}

/* Tabs Styles */
.pw-forecast-tabs-wrapper {
    position: relative;
}

.pw-tabs-align-left .pw-forecast-tabs {
    justify-content: flex-start;
}

.pw-tabs-align-center .pw-forecast-tabs {
    justify-content: center;
}

.pw-tabs-align-right .pw-forecast-tabs {
    justify-content: flex-end;
}

.pw-forecast-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.pw-forecast-tab-item {
    margin: 0;
}

.pw-forecast-tab-btn {
    background: transparent;
    border: none;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border-radius: 20px;
    transition: all 0.2s ease;
    color: #666;
}

.pw-forecast-tab-btn:hover {
    background: rgba(0, 0, 0, 0.05);
}

.pw-forecast-tab-item.pw-active .pw-forecast-tab-btn {
    background: var(--pw-primary-color, #f26c0d);
    color: #fff;
}

/* Tabs with Icons */
.pw-tab-icon {
    margin-right: 6px;
    display: inline-flex;
    align-items: center;
}

.pw-tab-icon i {
    font-size: 14px;
}

/* Tab Indicator Line */
.pw-tabs-indicator {
    position: absolute;
    bottom: -4px;
    left: 0;
    height: 2px;
    background: var(--pw-primary-color, #f26c0d);
    transition: left 0.3s ease, width 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-forecast-select-wrapper {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .pw-forecast-tabs {
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 4px;
    }
    
    .pw-forecast-tab-btn {
        white-space: nowrap;
    }
}
</style>

<script>
/**
 * Forecast header JavaScript for live filtering.
 */
(function() {
    // Initialize select dropdowns
    const selects = document.querySelectorAll('.pw-forecast-select');
    selects.forEach(select => {
        const trigger = select.querySelector('.pw-select-trigger');
        const options = select.querySelector('.pw-select-options');
        
        if (trigger && options) {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const expanded = trigger.getAttribute('aria-expanded') === 'true';
                trigger.setAttribute('aria-expanded', !expanded);
                options.classList.toggle('show');
            });
            
            // Option selection
            const optionItems = select.querySelectorAll('.pw-select-option');
            optionItems.forEach(option => {
                option.addEventListener('click', () => {
                    const value = option.getAttribute('data-value');
                    const selectedText = option.textContent;
                    
                    // Update trigger
                    trigger.querySelector('.pw-select-selected-option').textContent = selectedText;
                    trigger.querySelector('.pw-select-selected-option').setAttribute('data-value', value);
                    
                    // Update active class
                    optionItems.forEach(opt => opt.classList.remove('pw-active'));
                    option.classList.add('pw-active');
                    
                    // Close dropdown
                    trigger.setAttribute('aria-expanded', 'false');
                    options.classList.remove('show');
                    
                    // Trigger forecast data update
                    const header = select.closest('.pw-forecast-header');
                    if (header) {
                        const event = new CustomEvent('pw-forecast-change', { detail: { value: value } });
                        header.dispatchEvent(event);
                    }
                });
            });
        }
    });
    
    // Initialize tabs
    const tabs = document.querySelectorAll('.pw-forecast-tab-btn');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const value = tab.getAttribute('data-value');
            const tabItem = tab.closest('.pw-forecast-tab-item');
            const tabsContainer = tab.closest('.pw-forecast-tabs');
            
            if (tabsContainer) {
                // Update active states
                tabsContainer.querySelectorAll('.pw-forecast-tab-item').forEach(item => {
                    item.classList.remove('pw-active');
                    item.setAttribute('aria-selected', 'false');
                });
                tabItem.classList.add('pw-active');
                tabItem.setAttribute('aria-selected', 'true');
                
                // Trigger forecast data update
                const header = tabsContainer.closest('.pw-forecast-header');
                if (header) {
                    const event = new CustomEvent('pw-forecast-change', { detail: { value: value } });
                    header.dispatchEvent(event);
                }
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        const allSelects = document.querySelectorAll('.pw-forecast-select');
        allSelects.forEach(select => {
            const trigger = select.querySelector('.pw-select-trigger');
            const options = select.querySelector('.pw-select-options');
            if (trigger && options) {
                trigger.setAttribute('aria-expanded', 'false');
                options.classList.remove('show');
            }
        });
    });
})();
</script>

<?php
/**
 * Helper function for forecast tab icons.
 */
if ( ! function_exists( 'get_forecast_tab_icon' ) ) {
    /**
     * Get icon class for forecast tab.
     *
     * @param string $type Forecast type.
     * @return string
     */
    function get_forecast_tab_icon( $type ) {
        $icons = array(
            'temperature'  => 'pw-icon-temperature',
            'humidity'     => 'pw-icon-humidity',
            'wind'         => 'pw-icon-wind',
            'pressure'     => 'pw-icon-pressure',
            'precipitation'=> 'pw-icon-precipitation',
            'rain_chance'  => 'pw-icon-rain-chance',
            'clouds'       => 'pw-icon-clouds',
            'uv_index'     => 'pw-icon-uv-index',
        );
        
        return isset( $icons[ $type ] ) ? $icons[ $type ] : 'pw-icon-default';
    }
}