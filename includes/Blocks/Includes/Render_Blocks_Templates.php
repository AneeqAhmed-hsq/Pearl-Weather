<?php
/**
 * Weather Block Template Renderer
 *
 * Handles rendering of all weather block layouts including template loading,
 * icon URL generation, and error handling for API failures.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Includes
 * @since      1.0.0
 */

namespace PearlWeather\Blocks\Includes;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PearlWeather\Blocks\WeatherDataProcessor;

/**
 * Class TemplateRenderer
 *
 * Renders weather block templates with proper error handling and icon management.
 *
 * @since 1.0.0
 */
class TemplateRenderer {

    /**
     * Icon set folder mappings.
     *
     * @var array
     */
    private $icon_folders = array(
        'icon_set_one'   => 'weather-icons',
        'icon_set_two'   => 'weather-static-icons',
        'icon_set_three' => 'light-line',
        'icon_set_four'  => 'fill-icon',
        'icon_set_five'  => 'weather-glassmorphism',
        'icon_set_six'   => 'animated-line',
        'icon_set_seven' => 'animated',
        'icon_set_eight' => 'medium-line',
    );

    /**
     * Default icon set.
     *
     * @var string
     */
    private $default_icon_set = 'icon_set_one';

    /**
     * Get the icon URL for a weather condition.
     *
     * @since 1.0.0
     * @param string $icon_code The weather icon code from API.
     * @param string $icon_type The icon set type.
     * @return string Full URL to the icon SVG.
     */
    public function get_icon_url( $icon_code, $icon_type = '' ) {
        if ( empty( $icon_code ) ) {
            return '';
        }

        // Use default icon set if none specified.
        if ( empty( $icon_type ) || ! isset( $this->icon_folders[ $icon_type ] ) ) {
            $icon_type = $this->default_icon_set;
        }

        $folder_name = $this->icon_folders[ $icon_type ];
        
        // Map OpenWeatherMap icon codes to our icon set.
        $mapped_icon = $this->map_icon_code( $icon_code );
        
        $icon_url = PEARL_WEATHER_ASSETS_URL . 'images/icons/' . $folder_name . '/' . $mapped_icon . '.svg';
        
        /**
         * Filter the weather icon URL.
         *
         * @since 1.0.0
         * @param string $icon_url  Full icon URL.
         * @param string $icon_code Original icon code.
         * @param string $icon_type Icon set type.
         */
        return apply_filters( 'pearl_weather_icon_url', $icon_url, $icon_code, $icon_type );
    }

    /**
     * Map OpenWeatherMap icon codes to our icon set.
     *
     * @since 1.0.0
     * @param string $icon_code Original icon code.
     * @return string Mapped icon name.
     */
    private function map_icon_code( $icon_code ) {
        // Remove the 'd' or 'n' suffix (day/night).
        $base_code = preg_replace( '/[dn]$/', '', $icon_code );
        
        $mapping = array(
            '01' => 'clear-sky',
            '02' => 'few-clouds',
            '03' => 'scattered-clouds',
            '04' => 'broken-clouds',
            '09' => 'shower-rain',
            '10' => 'rain',
            '11' => 'thunderstorm',
            '13' => 'snow',
            '50' => 'mist',
        );
        
        return isset( $mapping[ $base_code ] ) ? $mapping[ $base_code ] : 'clear-sky';
    }

    /**
     * Get template file path.
     *
     * @since 1.0.0
     * @param string $template_name   Template name.
     * @param bool   $is_main_template Whether this is a main template.
     * @return string|false Template path or false if not found.
     */
    public function get_template_path( $template_name, $is_main_template = false ) {
        $base_path = PEARL_WEATHER_INCLUDES_PATH . 'templates/blocks/';
        
        if ( ! $is_main_template ) {
            $base_path .= 'parts/';
        }
        
        $template_path = $base_path . $template_name;
        
        if ( file_exists( $template_path ) ) {
            return $template_path;
        }
        
        // Fallback to default template if available.
        $default_path = $base_path . 'default.php';
        if ( file_exists( $default_path ) ) {
            return $default_path;
        }
        
        return false;
    }

    /**
     * Render the weather block.
     *
     * @since 1.0.0
     * @param array  $attributes Block attributes.
     * @param string $content    Inner block content.
     * @return string Rendered HTML.
     */
    public function render_block( $attributes, $content = '' ) {
        $block_name = isset( $attributes['blockName'] ) ? sanitize_text_field( $attributes['blockName'] ) : 'vertical';
        $unique_id  = isset( $attributes['uniqueId'] ) ? sanitize_html_class( $attributes['uniqueId'] ) : 'pw-weather-' . uniqid();
        $align      = isset( $attributes['align'] ) ? sanitize_text_field( $attributes['align'] ) : 'wide';
        $custom_class = isset( $attributes['customClassName'] ) ? sanitize_html_class( $attributes['customClassName'] ) : '';
        
        // Check if we should show preloader.
        $show_preloader = isset( $attributes['showPreloader'] ) ? (bool) $attributes['showPreloader'] : true;
        
        // Get weather data.
        $data_processor = new WeatherDataProcessor( $attributes );
        $weather_data = $data_processor->get_weather_data();
        $forecast_data = $data_processor->get_forecast_data();
        $aqi_data = $data_processor->get_aqi_data();
        $error_message = $data_processor->get_error_message();
        
        // Handle API errors.
        if ( $data_processor->has_error() ) {
            return $this->render_error_message( $unique_id, $block_name, $align, $custom_class, $error_message );
        }
        
        // Prepare wrapper classes.
        $wrapper_classes = $this->get_wrapper_classes( $block_name, $align, $custom_class );
        
        // Start output buffer.
        ob_start();
        
        ?>
        <div id="<?php echo esc_attr( $unique_id ); ?>" class="<?php echo esc_attr( $wrapper_classes ); ?>">
            
            <?php if ( $show_preloader ) : ?>
                <?php $this->render_preloader(); ?>
            <?php endif; ?>
            
            <?php
            // Render the main template for this block type.
            $template_path = $this->get_template_path( $block_name . '/render.php', true );
            if ( $template_path ) {
                // Extract variables for template.
                $attributes = $this->prepare_attributes_for_template( $attributes, $weather_data, $forecast_data, $aqi_data );
                include $template_path;
            } else {
                // Fallback rendering.
                $this->render_fallback( $attributes, $weather_data, $forecast_data );
            }
            ?>
            
        </div>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Get wrapper CSS classes.
     *
     * @since 1.0.0
     * @param string $block_name   Block name.
     * @param string $align        Alignment.
     * @param string $custom_class Custom class.
     * @return string
     */
    private function get_wrapper_classes( $block_name, $align, $custom_class ) {
        $classes = array(
            'pw-weather-block',
            "pw-weather-{$block_name}-card",
            "align{$align}",
        );
        
        if ( ! empty( $custom_class ) ) {
            $classes[] = $custom_class;
        }
        
        // Add weather-based background class if applicable.
        $bg_blocks = array( 'vertical', 'horizontal', 'grid' );
        if ( in_array( $block_name, $bg_blocks, true ) ) {
            $classes[] = 'pw-has-weather-bg';
        }
        
        return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
    }

    /**
     * Render preloader HTML.
     *
     * @since 1.0.0
     */
    private function render_preloader() {
        ?>
        <div class="pw-weather-preloader">
            <div class="pw-preloader-spinner">
                <svg class="pw-preloader-sun" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="20" fill="#F26C0D"/>
                    <line x1="50" y1="5" x2="50" y2="15" stroke="#F26C0D" stroke-width="3" stroke-linecap="round"/>
                    <line x1="50" y1="85" x2="50" y2="95" stroke="#F26C0D" stroke-width="3" stroke-linecap="round"/>
                    <line x1="5" y1="50" x2="15" y2="50" stroke="#F26C0D" stroke-width="3" stroke-linecap="round"/>
                    <line x1="85" y1="50" x2="95" y2="50" stroke="#F26C0D" stroke-width="3" stroke-linecap="round"/>
                    <line x1="18.4" y1="18.4" x2="25.5" y2="25.5" stroke="#F26C0D" stroke-width="3" stroke-linecap="round"/>
                    <line x1="74.5" y1="74.5" x2="81.6" y2="81.6" stroke="#F26C0D" stroke-width="3" stroke-linecap="round"/>
                    <line x1="81.6" y1="18.4" x2="74.5" y2="25.5" stroke="#F26C0D" stroke-width="3" stroke-linecap="round"/>
                    <line x1="25.5" y1="74.5" x2="18.4" y2="81.6" stroke="#F26C0D" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <div class="pw-preloader-rain">
                    <?php for ( $i = 1; $i <= 10; $i++ ) : ?>
                        <span class="pw-preloader-drop" style="animation-delay: -<?php echo esc_attr( $i * 0.13 ); ?>s;"></span>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render error message when API fails.
     *
     * @since 1.0.0
     * @param string $unique_id     Block unique ID.
     * @param string $block_name    Block name.
     * @param string $align         Alignment.
     * @param string $custom_class  Custom class.
     * @param string $error_message Error message.
     * @return string
     */
    private function render_error_message( $unique_id, $block_name, $align, $custom_class, $error_message ) {
        $classes = array(
            'pw-weather-block',
            "pw-weather-{$block_name}-card",
            'pw-weather-api-error',
            "align{$align}",
        );
        
        if ( ! empty( $custom_class ) ) {
            $classes[] = $custom_class;
        }
        
        $classes = implode( ' ', array_map( 'sanitize_html_class', $classes ) );
        
        return sprintf(
            '<div id="%s" class="%s"><div class="pw-api-error-message">%s</div></div>',
            esc_attr( $unique_id ),
            esc_attr( $classes ),
            wp_kses_post( $error_message )
        );
    }

    /**
     * Fallback rendering when template not found.
     *
     * @since 1.0.0
     * @param array $attributes   Block attributes.
     * @param array $weather_data Weather data.
     * @param array $forecast_data Forecast data.
     */
    private function render_fallback( $attributes, $weather_data, $forecast_data ) {
        ?>
        <div class="pw-weather-fallback">
            <div class="pw-current-weather">
                <div class="pw-location-name">
                    <?php echo esc_html( $weather_data['city'] ?? '' ); ?>, <?php echo esc_html( $weather_data['country'] ?? '' ); ?>
                </div>
                <div class="pw-temperature">
                    <?php echo esc_html( $weather_data['temperature'] ?? '--' ); ?>
                    <?php echo esc_html( $weather_data['temp_unit'] ?? '°C' ); ?>
                </div>
                <div class="pw-weather-description">
                    <?php echo esc_html( $weather_data['description'] ?? '' ); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Prepare attributes for template consumption.
     *
     * @since 1.0.0
     * @param array $attributes    Raw attributes.
     * @param array $weather_data  Weather data.
     * @param array $forecast_data Forecast data.
     * @param array $aqi_data      AQI data.
     * @return array Prepared attributes.
     */
    private function prepare_attributes_for_template( $attributes, $weather_data, $forecast_data, $aqi_data ) {
        // Add computed data to attributes for template access.
        $attributes['weather_data'] = $weather_data;
        $attributes['forecast_data'] = $forecast_data;
        $attributes['aqi_data'] = $aqi_data;
        
        // Add helper methods.
        $attributes['get_icon_url'] = array( $this, 'get_icon_url' );
        
        return $attributes;
    }

    /**
     * Get icon folder mapping.
     *
     * @since 1.0.0
     * @return array
     */
    public function get_icon_folders() {
        return $this->icon_folders;
    }

    /**
     * Register custom icon set.
     *
     * @since 1.0.0
     * @param string $key        Icon set key.
     * @param string $folder_name Folder name.
     */
    public function register_icon_set( $key, $folder_name ) {
        $this->icon_folders[ $key ] = $folder_name;
    }
}

// Initialize the template renderer.
if ( ! function_exists( 'pearl_weather_render_block' ) ) {
    /**
     * Helper function to render a weather block.
     *
     * @since 1.0.0
     * @param array  $attributes Block attributes.
     * @param string $content    Inner content.
     * @return string
     */
    function pearl_weather_render_block( $attributes, $content = '' ) {
        $renderer = new TemplateRenderer();
        return $renderer->render_block( $attributes, $content );
    }
}