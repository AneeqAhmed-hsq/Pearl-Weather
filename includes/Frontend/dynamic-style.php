<?php
/**
 * Dynamic CSS Generator for Weather Widgets
 *
 * Generates custom CSS for individual weather widgets based on
 * saved meta settings including colors, spacing, borders, and shadows.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Public
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class DynamicCSSGenerator
 *
 * Handles generation of dynamic CSS for weather widgets.
 *
 * @since 1.0.0
 */
class DynamicCSSGenerator {

    /**
     * Widget ID.
     *
     * @var int
     */
    private $widget_id;

    /**
     * Widget meta settings.
     *
     * @var array
     */
    private $settings;

    /**
     * Generated CSS string.
     *
     * @var string
     */
    private $css = '';

    /**
     * Constructor.
     *
     * @param int   $widget_id Widget post ID.
     * @param array $settings  Widget meta settings.
     */
    public function __construct( $widget_id, $settings ) {
        $this->widget_id = absint( $widget_id );
        $this->settings  = $settings;
        $this->generate();
    }

    /**
     * Generate all CSS rules.
     */
    private function generate() {
        $this->generate_container_styles();
        $this->generate_border_styles();
        $this->generate_background_styles();
        $this->generate_typography_styles();
        $this->generate_spacing_styles();
        $this->generate_box_shadow();
        $this->generate_preloader_styles();
        $this->generate_layout_specific_styles();
    }

    /**
     * Get the generated CSS.
     *
     * @return string
     */
    public function get_css() {
        return $this->css;
    }

    /**
     * Generate container styles.
     */
    private function generate_container_styles() {
        $max_width = isset( $this->settings['max_width'] ) ? (int) $this->settings['max_width'] : 320;
        $view = isset( $this->settings['weather_view'] ) ? $this->settings['weather_view'] : 'vertical';

        $this->add_rule( ".pw-weather-widget-{$this->widget_id}", array(
            'max-width' => $max_width . 'px',
            'margin'    => '0 auto 2em auto',
        ) );

        if ( 'horizontal' === $view ) {
            $this->add_rule( ".pw-weather-widget-{$this->widget_id}.pw-layout-horizontal", array(
                'max-width' => '800px',
            ) );
        }
    }

    /**
     * Generate border styles.
     */
    private function generate_border_styles() {
        $border_width = isset( $this->settings['border_width'] ) ? (int) $this->settings['border_width'] : 0;
        $border_style = isset( $this->settings['border_style'] ) ? $this->settings['border_style'] : 'solid';
        $border_color = isset( $this->settings['border_color'] ) ? $this->settings['border_color'] : '#e2e2e2';
        $border_radius = isset( $this->settings['border_radius'] ) ? (int) $this->settings['border_radius'] : 8;
        $radius_unit = isset( $this->settings['border_radius_unit'] ) ? $this->settings['border_radius_unit'] : 'px';

        if ( $border_width > 0 ) {
            $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-weather-card", array(
                'border' => "{$border_width}px {$border_style} {$border_color}",
            ) );
        }

        $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-weather-card", array(
            'border-radius' => $border_radius . $radius_unit,
        ) );
    }

    /**
     * Generate background styles.
     */
    private function generate_background_styles() {
        $bg_type = isset( $this->settings['background_type'] ) ? $this->settings['background_type'] : 'solid';
        $bg_color = isset( $this->settings['bg_color'] ) ? $this->settings['bg_color'] : '#f26c0d';
        $bg_image = isset( $this->settings['bg_image'] ) ? $this->settings['bg_image'] : '';
        $bg_gradient = isset( $this->settings['bg_gradient'] ) ? $this->settings['bg_gradient'] : '';

        $bg_css = array();

        switch ( $bg_type ) {
            case 'solid':
                $bg_css['background'] = $bg_color;
                break;
            case 'gradient':
                if ( ! empty( $bg_gradient ) ) {
                    $bg_css['background'] = $bg_gradient;
                }
                break;
            case 'image':
                if ( ! empty( $bg_image ) ) {
                    $bg_css['background-image'] = "url({$bg_image})";
                    $bg_css['background-size'] = 'cover';
                    $bg_css['background-position'] = 'center';
                }
                break;
        }

        if ( ! empty( $bg_css ) ) {
            $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-weather-card", $bg_css );
        }
    }

    /**
     * Generate typography and color styles.
     */
    private function generate_typography_styles() {
        $styles = array(
            '.pw-weather-title' => array(
                'color' => 'title_color',
                'margin-top' => 'title_margin_top',
                'margin-bottom' => 'title_margin_bottom',
            ),
            '.pw-location-name' => array(
                'color' => 'location_color',
                'margin-top' => 'location_margin_top',
                'margin-bottom' => 'location_margin_bottom',
            ),
            '.pw-datetime' => array(
                'color' => 'datetime_color',
                'margin-top' => 'datetime_margin_top',
                'margin-bottom' => 'datetime_margin_bottom',
            ),
            '.pw-temperature' => array(
                'color' => 'temperature_color',
                'margin-top' => 'temperature_margin_top',
                'margin-bottom' => 'temperature_margin_bottom',
            ),
            '.pw-weather-description' => array(
                'color' => 'description_color',
                'margin-top' => 'description_margin_top',
                'margin-bottom' => 'description_margin_bottom',
            ),
            '.pw-additional-data' => array(
                'color' => 'additional_data_color',
                'margin-top' => 'additional_data_margin_top',
                'margin-bottom' => 'additional_data_margin_bottom',
            ),
            '.pw-footer' => array(
                'margin-top' => 'footer_margin_top',
                'margin-bottom' => 'footer_margin_bottom',
            ),
            '.pw-footer a' => array(
                'color' => 'footer_color',
            ),
        );

        foreach ( $styles as $selector => $props ) {
            $css = array();
            foreach ( $props as $css_prop => $setting_key ) {
                $value = $this->get_setting( $setting_key );
                if ( ! empty( $value ) || '0' === $value ) {
                    if ( strpos( $css_prop, 'margin' ) !== false ) {
                        $css[ $css_prop ] = $value . 'px';
                    } else {
                        $css[ $css_prop ] = $value;
                    }
                }
            }
            if ( ! empty( $css ) ) {
                $this->add_rule( ".pw-weather-widget-{$this->widget_id} {$selector}", $css );
            }
        }

        // Icon color.
        $icon_color = $this->get_setting( 'icon_color' );
        if ( ! empty( $icon_color ) ) {
            $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-icon-color i", array(
                'color' => $icon_color,
            ) );
        }
    }

    /**
     * Generate spacing styles.
     */
    private function generate_spacing_styles() {
        $padding = isset( $this->settings['content_padding'] ) ? $this->settings['content_padding'] : array(
            'top' => 16,
            'right' => 20,
            'bottom' => 10,
            'left' => 20,
        );
        $padding_unit = isset( $this->settings['padding_unit'] ) ? $this->settings['padding_unit'] : 'px';

        $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-weather-card", array(
            'padding' => "{$padding['top']}{$padding_unit} {$padding['right']}{$padding_unit} {$padding['bottom']}{$padding_unit} {$padding['left']}{$padding_unit}",
        ) );

        // Weather icon size.
        $icon_size = $this->get_setting( 'weather_icon_size', 58 );
        $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-weather-icon-img", array(
            'width' => $icon_size . 'px',
        ) );
    }

    /**
     * Generate box shadow styles.
     */
    private function generate_box_shadow() {
        $shadow_type = isset( $this->settings['box_shadow_type'] ) ? $this->settings['box_shadow_type'] : 'none';

        if ( 'none' === $shadow_type ) {
            return;
        }

        $shadow = isset( $this->settings['box_shadow'] ) ? $this->settings['box_shadow'] : array(
            'vertical'   => 4,
            'horizontal' => 4,
            'blur'       => 16,
            'spread'     => 0,
            'color'      => 'rgba(0,0,0,0.3)',
        );

        $shadow_css = sprintf(
            '%dpx %dpx %dpx %dpx %s %s',
            absint( $shadow['vertical'] ),
            absint( $shadow['horizontal'] ),
            absint( $shadow['blur'] ),
            absint( $shadow['spread'] ),
            $shadow['color'],
            'outset' !== $shadow_type ? $shadow_type : ''
        );

        $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-weather-card", array(
            'box-shadow' => trim( $shadow_css ),
        ) );
    }

    /**
     * Generate preloader styles.
     */
    private function generate_preloader_styles() {
        $show_preloader = isset( $this->settings['show_preloader'] ) ? (bool) $this->settings['show_preloader'] : true;

        if ( ! $show_preloader ) {
            return;
        }

        $this->add_rule( ".pw-weather-widget-{$this->widget_id}", array(
            'position' => 'relative',
        ) );

        $this->add_rule( ".pw-preloader-{$this->widget_id}", array(
            'position' => 'absolute',
            'left' => '0',
            'top' => '0',
            'height' => '100%',
            'width' => '100%',
            'text-align' => 'center',
            'display' => 'flex',
            'align-items' => 'center',
            'justify-content' => 'center',
            'background' => '#fff',
            'z-index' => '9999',
        ) );
    }

    /**
     * Generate layout-specific styles.
     */
    private function generate_layout_specific_styles() {
        $view = isset( $this->settings['weather_view'] ) ? $this->settings['weather_view'] : 'vertical';
        $additional_layout = isset( $this->settings['additional_data_layout'] ) ? $this->settings['additional_data_layout'] : 'center';

        if ( 'vertical' === $view ) {
            switch ( $additional_layout ) {
                case 'center':
                    $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-additional-data.pw-layout-center .pw-data-item", array(
                        'text-align' => 'center',
                        'justify-content' => 'center',
                    ) );
                    break;
                case 'left':
                    $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-additional-data.pw-layout-left .pw-data-item", array(
                        'text-align' => 'left',
                        'justify-content' => 'flex-start',
                    ) );
                    break;
                case 'justified':
                    $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-additional-data.pw-layout-justified .pw-data-item", array(
                        'display' => 'flex',
                        'justify-content' => 'space-between',
                        'align-items' => 'center',
                    ) );
                    break;
            }
        }

        // Forecast styles.
        $show_forecast = isset( $this->settings['enable_forecast'] ) ? (bool) $this->settings['enable_forecast'] : true;
        if ( $show_forecast ) {
            $forecast_color = $this->get_setting( 'forecast_color', '#fff' );
            $forecast_margin_top = $this->get_setting( 'forecast_margin_top', 0 );
            $forecast_margin_bottom = $this->get_setting( 'forecast_margin_bottom', 0 );

            $this->add_rule( ".pw-weather-widget-{$this->widget_id} .pw-forecast-section", array(
                'color' => $forecast_color,
                'margin-top' => $forecast_margin_top . 'px',
                'margin-bottom' => $forecast_margin_bottom . 'px',
            ) );
        }
    }

    /**
     * Get a setting value.
     *
     * @param string $key     Setting key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    private function get_setting( $key, $default = '' ) {
        return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
    }

    /**
     * Add a CSS rule.
     *
     * @param string $selector CSS selector.
     * @param array  $rules    CSS rules.
     */
    private function add_rule( $selector, $rules ) {
        if ( empty( $rules ) ) {
            return;
        }

        $css = $selector . " {\n";
        foreach ( $rules as $property => $value ) {
            if ( ! empty( $value ) || '0' === $value ) {
                $css .= "    {$property}: {$value};\n";
            }
        }
        $css .= "}\n";

        $this->css .= $css;
    }
}

/**
 * Helper function to generate dynamic CSS for a widget.
 *
 * @param int   $widget_id Widget post ID.
 * @param array $settings  Widget meta settings.
 * @return string
 */
function pearl_weather_generate_dynamic_css( $widget_id, $settings ) {
    $generator = new DynamicCSSGenerator( $widget_id, $settings );
    return $generator->get_css();
}