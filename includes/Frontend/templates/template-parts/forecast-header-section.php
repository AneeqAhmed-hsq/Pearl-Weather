<?php
/**
 * Forecast Header Template
 *
 * Displays forecast navigation controls (tabs or dropdown) for
 * switching between different forecast data types.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Templates/Parts
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template variables:
 * - $layout: Layout type ('vertical' or 'horizontal')
 * - $forecast_data_sortable: Array of forecast data options with visibility flags
 * - $hourly_forecast_section_title: Section title for forecast
 */

// Forecast type labels.
$labels = array(
    'temperature'   => __( 'Temperature', 'pearl-weather' ),
    'humidity'      => __( 'Humidity', 'pearl-weather' ),
    'pressure'      => __( 'Pressure', 'pearl-weather' ),
    'wind'          => __( 'Wind', 'pearl-weather' ),
    'precipitation' => __( 'Precipitation', 'pearl-weather' ),
    'rain_chance'   => __( 'Rain Chance', 'pearl-weather' ),
    'rainchance'    => __( 'Rain Chance', 'pearl-weather' ),
    'snow'          => __( 'Snow', 'pearl-weather' ),
    'gust'          => __( 'Wind Gust', 'pearl-weather' ),
    'clouds'        => __( 'Clouds', 'pearl-weather' ),
    'uv_index'      => __( 'UV Index', 'pearl-weather' ),
    'dew_point'     => __( 'Dew Point', 'pearl-weather' ),
);

// Check if any forecast options are available.
$has_options = false;
if ( ! empty( $forecast_data_sortable ) && is_array( $forecast_data_sortable ) ) {
    foreach ( $forecast_data_sortable as $value ) {
        if ( $value ) {
            $has_options = true;
            break;
        }
    }
}

if ( ! $has_options ) {
    return;
}

// CSS classes.
$wrapper_classes = array( 'pw-forecast-header' );

if ( 'horizontal' === $layout && ! wp_is_mobile() ) {
    $wrapper_classes[] = 'pw-header-tabs';
} else {
    $wrapper_classes[] = 'pw-header-select';
}

if ( ! empty( $attributes['forecast_header_custom_class'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['forecast_header_custom_class'] );
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    
    <?php if ( 'horizontal' === $layout && ! wp_is_mobile() ) : ?>
        
        <!-- Tabs Navigation (Desktop Horizontal) -->
        <div class="pw-forecast-tabs-container">
            <ul class="pw-forecast-tabs" role="tablist">
                <?php
                $first = true;
                foreach ( $forecast_data_sortable as $key => $enabled ) :
                    if ( ! $enabled ) {
                        continue;
                    }
                    $label = isset( $labels[ $key ] ) ? $labels[ $key ] : ucfirst( str_replace( '_', ' ', $key ) );
                    $data_type = ( 'temperature' === $key ) ? 'temp' : $key;
                    ?>
                    <li class="pw-forecast-tab-item <?php echo $first ? 'pw-active' : ''; ?>" 
                        role="presentation">
                        <button class="pw-forecast-tab-btn"
                                data-forecast-type="<?php echo esc_attr( $data_type ); ?>"
                                role="tab"
                                aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
                            <?php echo esc_html( $label ); ?>
                        </button>
                    </li>
                    <?php
                    $first = false;
                endforeach;
                ?>
            </ul>
            <div class="pw-tab-indicator"></div>
        </div>
        
    <?php else : ?>
        
        <!-- Dropdown Select (Mobile or Vertical Layout) -->
        <div class="pw-forecast-select-container">
            
            <?php if ( ! empty( $hourly_forecast_section_title ) ) : ?>
                <span class="pw-forecast-title"><?php echo esc_html( $hourly_forecast_section_title ); ?></span>
            <?php endif; ?>
            
            <div class="pw-forecast-select-wrapper">
                <select class="pw-forecast-select" id="pw-forecast-select-<?php echo esc_attr( uniqid() ); ?>">
                    <?php foreach ( $forecast_data_sortable as $key => $enabled ) : ?>
                        <?php if ( ! $enabled ) continue; ?>
                        <?php
                        $label = isset( $labels[ $key ] ) ? $labels[ $key ] : ucfirst( str_replace( '_', ' ', $key ) );
                        $value = ( 'temperature' === $key ) ? 'temp' : $key;
                        ?>
                        <option value="<?php echo esc_attr( $value ); ?>">
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="pw-select-arrow" aria-hidden="true">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>
            
        </div>
        
    <?php endif; ?>
    
</div>

<style>
/* Forecast Header Styles */
.pw-forecast-header {
    margin-bottom: 16px;
}

/* Tabs Container */
.pw-forecast-tabs-container {
    position: relative;
}

.pw-forecast-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    list-style: none;
    margin: 0;
    padding: 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.pw-forecast-tab-item {
    margin: 0;
}

.pw-forecast-tab-btn {
    background: transparent;
    border: none;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    color: #666;
    transition: all 0.2s ease;
}

.pw-forecast-tab-btn:hover {
    color: var(--pw-primary-color, #f26c0d);
}

.pw-forecast-tab-item.pw-active .pw-forecast-tab-btn {
    color: var(--pw-primary-color, #f26c0d);
    position: relative;
}

.pw-forecast-tab-item.pw-active .pw-forecast-tab-btn::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--pw-primary-color, #f26c0d);
}

/* Dropdown Select */
.pw-forecast-select-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.pw-forecast-title {
    font-size: 16px;
    font-weight: 600;
}

.pw-forecast-select-wrapper {
    position: relative;
    min-width: 140px;
}

.pw-forecast-select {
    width: 100%;
    padding: 8px 32px 8px 12px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
}

.pw-forecast-select:focus {
    outline: none;
    border-color: var(--pw-primary-color, #f26c0d);
}

.pw-select-arrow {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #666;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-forecast-tabs {
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 4px;
    }
    
    .pw-forecast-tab-btn {
        white-space: nowrap;
        padding: 6px 12px;
        font-size: 13px;
    }
    
    .pw-forecast-select-container {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .pw-forecast-select-wrapper {
        width: 100%;
    }
}
</style>

<script>
/**
 * Forecast header tab/select functionality.
 */
(function() {
    const header = document.querySelector('.pw-forecast-header');
    if (!header) return;
    
    // Handle tabs
    const tabs = header.querySelectorAll('.pw-forecast-tab-btn');
    if (tabs.length) {
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const forecastType = tab.getAttribute('data-forecast-type');
                const tabItem = tab.closest('.pw-forecast-tab-item');
                const container = tab.closest('.pw-forecast-tabs-container');
                
                // Update active states
                container.querySelectorAll('.pw-forecast-tab-item').forEach(item => {
                    item.classList.remove('pw-active');
                    item.querySelector('button')?.setAttribute('aria-selected', 'false');
                });
                tabItem.classList.add('pw-active');
                tab.setAttribute('aria-selected', 'true');
                
                // Update forecast values
                const forecastItems = document.querySelectorAll('.pw-forecast-item');
                forecastItems.forEach(item => {
                    const values = item.querySelectorAll('.pw-forecast-value');
                    values.forEach(value => {
                        const type = value.getAttribute('data-forecast-type');
                        if (type === forecastType) {
                            value.classList.add('active');
                        } else {
                            value.classList.remove('active');
                        }
                    });
                });
            });
        });
    }
    
    // Handle select dropdown
    const select = header.querySelector('.pw-forecast-select');
    if (select) {
        select.addEventListener('change', (e) => {
            const forecastType = e.target.value;
            
            // Update forecast values
            const forecastItems = document.querySelectorAll('.pw-forecast-item');
            forecastItems.forEach(item => {
                const values = item.querySelectorAll('.pw-forecast-value');
                values.forEach(value => {
                    const type = value.getAttribute('data-forecast-type');
                    if (type === forecastType) {
                        value.classList.add('active');
                    } else {
                        value.classList.remove('active');
                    }
                });
            });
        });
    }
})();
</script>