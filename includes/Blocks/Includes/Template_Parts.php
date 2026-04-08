<?php
/**
 * Weather Block Template Parts
 *
 * Provides reusable template part methods for weather blocks including
 * weather descriptions, slider navigation buttons, and arrow icons.
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

/**
 * Class TemplateParts
 *
 * Static methods for rendering reusable template components.
 *
 * @since 1.0.0
 */
class TemplateParts {

    /**
     * Default icon color.
     */
    const DEFAULT_ICON_COLOR = '#2f2f2f';

    /**
     * Default icon size.
     */
    const DEFAULT_ICON_SIZE = 24;

    /**
     * Render weather description HTML.
     *
     * @since 1.0.0
     * @param array $attributes   Block attributes.
     * @param array $weather_data Weather data array.
     * @return string HTML output.
     */
    public static function render_weather_description( $attributes, $weather_data ) {
        $show_description = isset( $attributes['displayWeatherConditions'] ) 
            ? (bool) $attributes['displayWeatherConditions'] 
            : true;
        
        if ( ! $show_description ) {
            return '';
        }
        
        $description = isset( $weather_data['description'] ) 
            ? sanitize_text_field( $weather_data['description'] ) 
            : '';
        
        if ( empty( $description ) ) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="pw-weather-description">
            <span class="pw-description-text">
                <?php echo esc_html( $description ); ?>
            </span>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render custom slider navigation buttons.
     *
     * @since 1.0.0
     * @param array $args Optional arguments (prev_text, next_text, button_class).
     * @return string HTML output.
     */
    public static function render_slider_navigation( $args = array() ) {
        $defaults = array(
            'prev_text'    => '',
            'next_text'    => '',
            'button_class' => 'pw-slider-nav',
            'show_always'  => false,
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        // Default chevron icons.
        if ( empty( $args['prev_text'] ) ) {
            $args['prev_text'] = self::get_chevron_icon( 'left' );
        }
        if ( empty( $args['next_text'] ) ) {
            $args['next_text'] = self::get_chevron_icon( 'right' );
        }
        
        ob_start();
        ?>
        <button class="<?php echo esc_attr( $args['button_class'] ); ?> pw-slider-nav-prev" aria-label="<?php esc_attr_e( 'Previous', 'pearl-weather' ); ?>">
            <?php echo $args['prev_text']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </button>
        <button class="<?php echo esc_attr( $args['button_class'] ); ?> pw-slider-nav-next" aria-label="<?php esc_attr_e( 'Next', 'pearl-weather' ); ?>">
            <?php echo $args['next_text']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </button>
        <?php
        return ob_get_clean();
    }

    /**
     * Get chevron icon SVG.
     *
     * @since 1.0.0
     * @param string $direction Direction ('left' or 'right').
     * @param string $color     Icon color.
     * @param int    $size      Icon size in pixels.
     * @return string SVG markup.
     */
    public static function get_chevron_icon( $direction = 'right', $color = self::DEFAULT_ICON_COLOR, $size = self::DEFAULT_ICON_SIZE ) {
        $direction_class = 'right' === $direction ? '' : 'pw-chevron-left';
        
        ob_start();
        ?>
        <span class="pw-chevron-icon <?php echo esc_attr( $direction_class ); ?>">
            <svg width="<?php echo esc_attr( $size ); ?>" height="<?php echo esc_attr( $size ); ?>" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <?php if ( 'right' === $direction ) : ?>
                    <path d="M9 18L15 12L9 6" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <?php else : ?>
                    <path d="M15 18L9 12L15 6" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <?php endif; ?>
            </svg>
        </span>
        <?php
        return ob_get_clean();
    }

    /**
     * Render up/down arrow icon.
     *
     * @since 1.0.0
     * @param string $fill    Icon fill color.
     * @param bool   $is_down Whether this is a down arrow (false = up arrow).
     * @param int    $size    Icon size in pixels.
     * @return string SVG markup.
     */
    public static function render_arrow_icon( $fill = self::DEFAULT_ICON_COLOR, $is_down = false, $size = self::DEFAULT_ICON_SIZE ) {
        $direction_class = $is_down ? 'pw-arrow-down' : 'pw-arrow-up';
        
        ob_start();
        ?>
        <span class="pw-arrow-icon <?php echo esc_attr( $direction_class ); ?>">
            <svg width="<?php echo esc_attr( $size ); ?>" height="<?php echo esc_attr( $size ); ?>" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <?php if ( $is_down ) : ?>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.43779 9.68689L5.14489 8.97979L7.62467 11.4596L7.62467 2.66675L8.62467 2.66675L8.62467 11.4596L11.1045 8.97979L11.8116 9.68689L8.12467 13.3738L4.43779 9.68689Z" fill="<?php echo esc_attr( $fill ); ?>"/>
                <?php else : ?>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5622 6.31311L11.8551 7.02021L9.37536 4.54043L9.37536 13.3333H8.37536L8.37536 4.54043L5.89558 7.02021L5.18848 6.31311L8.87536 2.62622L12.5622 6.31311Z" fill="<?php echo esc_attr( $fill ); ?>"/>
                <?php endif; ?>
            </svg>
        </span>
        <?php
        return ob_get_clean();
    }

    /**
     * Render loading spinner.
     *
     * @since 1.0.0
     * @param string $size Size class ('small', 'medium', 'large').
     * @return string
     */
    public static function render_spinner( $size = 'medium' ) {
        $size_class = 'pw-spinner-' . $size;
        
        ob_start();
        ?>
        <div class="pw-spinner <?php echo esc_attr( $size_class ); ?>">
            <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                <circle class="pw-spinner-path" cx="25" cy="25" r="20" fill="none" stroke-width="4"/>
            </svg>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render temperature display with unit.
     *
     * @since 1.0.0
     * @param string|int $temperature Temperature value.
     * @param string     $unit        Temperature unit (°C, °F, K).
     * @param array      $args        Additional arguments.
     * @return string
     */
    public static function render_temperature( $temperature, $unit = '°C', $args = array() ) {
        $defaults = array(
            'show_unit'    => true,
            'wrapper_class' => 'pw-temperature',
            'value_class'   => 'pw-temp-value',
            'unit_class'    => 'pw-temp-unit',
        );
        
        $args = wp_parse_args( $args, $defaults );
        
        if ( empty( $temperature ) && '0' !== $temperature ) {
            $temperature = '--';
        }
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr( $args['wrapper_class'] ); ?>">
            <span class="<?php echo esc_attr( $args['value_class'] ); ?>">
                <?php echo esc_html( $temperature ); ?>
            </span>
            <?php if ( $args['show_unit'] ) : ?>
                <span class="<?php echo esc_attr( $args['unit_class'] ); ?>">
                    <?php echo esc_html( $unit ); ?>
                </span>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render humidity display.
     *
     * @since 1.0.0
     * @param string|int $humidity Humidity value.
     * @param string     $unit     Humidity unit (default '%').
     * @return string
     */
    public static function render_humidity( $humidity, $unit = '%' ) {
        if ( empty( $humidity ) && '0' !== $humidity ) {
            $humidity = '--';
        }
        
        ob_start();
        ?>
        <div class="pw-humidity">
            <span class="pw-humidity-value"><?php echo esc_html( $humidity ); ?></span>
            <span class="pw-humidity-unit"><?php echo esc_html( $unit ); ?></span>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render wind display.
     *
     * @since 1.0.0
     * @param string|int $speed     Wind speed.
     * @param string     $unit      Wind speed unit.
     * @param string     $direction Wind direction.
     * @return string
     */
    public static function render_wind( $speed, $unit = 'm/s', $direction = '' ) {
        if ( empty( $speed ) && '0' !== $speed ) {
            $speed = '--';
        }
        
        ob_start();
        ?>
        <div class="pw-wind">
            <span class="pw-wind-speed"><?php echo esc_html( $speed ); ?></span>
            <span class="pw-wind-unit"><?php echo esc_html( $unit ); ?></span>
            <?php if ( ! empty( $direction ) ) : ?>
                <span class="pw-wind-direction"><?php echo esc_html( $direction ); ?></span>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render forecast item.
     *
     * @since 1.0.0
     * @param array  $item       Forecast item data.
     * @param string $icon_type  Icon set type.
     * @param array  $attributes Block attributes.
     * @return string
     */
    public static function render_forecast_item( $item, $icon_type = '', $attributes = array() ) {
        $time = isset( $item['time'] ) ? $item['time'] : '';
        $temp = isset( $item['temp'] ) ? $item['temp'] : '';
        $icon = isset( $item['icon'] ) ? $item['icon'] : '';
        $condition = isset( $item['condition'] ) ? $item['condition'] : '';
        
        // Get icon URL if renderer is available.
        $icon_url = '';
        if ( ! empty( $icon ) && class_exists( 'PearlWeather\Blocks\Includes\TemplateRenderer' ) ) {
            $renderer = new TemplateRenderer();
            $icon_url = $renderer->get_icon_url( $icon, $icon_type );
        }
        
        ob_start();
        ?>
        <div class="pw-forecast-item">
            <div class="pw-forecast-time"><?php echo esc_html( $time ); ?></div>
            <?php if ( ! empty( $icon_url ) ) : ?>
                <div class="pw-forecast-icon">
                    <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $condition ); ?>">
                </div>
            <?php endif; ?>
            <div class="pw-forecast-temp"><?php echo esc_html( $temp ); ?></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render separator.
     *
     * @since 1.0.0
     * @param string $type Separator type ('solid', 'dashed', 'dotted', 'gradient').
     * @return string
     */
    public static function render_separator( $type = 'solid' ) {
        $class = 'pw-separator pw-separator-' . $type;
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr( $class ); ?>"></div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render tooltip.
     *
     * @since 1.0.0
     * @param string $content Tooltip content.
     * @param string $position Tooltip position ('top', 'bottom', 'left', 'right').
     * @return string
     */
    public static function render_tooltip( $content, $position = 'top' ) {
        ob_start();
        ?>
        <span class="pw-tooltip pw-tooltip-<?php echo esc_attr( $position ); ?>" aria-label="<?php echo esc_attr( $content ); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                <path d="M12 16V12M12 8H12.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span class="pw-tooltip-text"><?php echo esc_html( $content ); ?></span>
        </span>
        <?php
        return ob_get_clean();
    }
}

// Global helper functions for backward compatibility.
if ( ! function_exists( 'pearl_weather_render_weather_description' ) ) {
    /**
     * Render weather description.
     *
     * @param array $attributes   Block attributes.
     * @param array $weather_data Weather data.
     * @return string
     */
    function pearl_weather_render_weather_description( $attributes, $weather_data ) {
        return TemplateParts::render_weather_description( $attributes, $weather_data );
    }
}

if ( ! function_exists( 'pearl_weather_render_slider_navigation' ) ) {
    /**
     * Render slider navigation buttons.
     *
     * @param array $args Arguments.
     * @return string
     */
    function pearl_weather_render_slider_navigation( $args = array() ) {
        return TemplateParts::render_slider_navigation( $args );
    }
}

if ( ! function_exists( 'pearl_weather_render_arrow_icon' ) ) {
    /**
     * Render arrow icon.
     *
     * @param string $fill    Fill color.
     * @param bool   $is_down Is down arrow.
     * @param int    $size    Size.
     * @return string
     */
    function pearl_weather_render_arrow_icon( $fill = '#2f2f2f', $is_down = false, $size = 24 ) {
        return TemplateParts::render_arrow_icon( $fill, $is_down, $size );
    }
}