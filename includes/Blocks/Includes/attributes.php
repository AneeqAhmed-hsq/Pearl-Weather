<?php
/**
 * Gutenberg Block Attributes Definition
 *
 * Defines all attributes for Pearl Weather Gutenberg blocks including
 * responsive controls, color settings, typography, spacing, and API config.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks
 * @since      1.0.0
 */

namespace PearlWeather\Blocks;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class BlockAttributes
 *
 * Defines and manages block attributes for weather widgets.
 *
 * @since 1.0.0
 */
class BlockAttributes {

    /**
     * Primary brand color.
     */
    const BRAND_COLOR = '#F26C0D';

    /**
     * Default API source.
     */
    const DEFAULT_API_SOURCE = 'openweather_api';

    /**
     * Get all block attributes.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_all_attributes() {
        return array_merge(
            self::get_color_attributes(),
            self::get_required_attributes(),
            self::get_shared_attributes()
        );
    }

    /**
     * Get color attributes for block.
     *
     * @since 1.0.0
     * @param string $default_color Default color value.
     * @return array
     */
    public static function get_color_attributes( $default_color = '' ) {
        $color = ! empty( $default_color ) ? $default_color : self::BRAND_COLOR;
        
        return array(
            'locationNameColor'         => array( 'type' => 'string', 'default' => $color ),
            'dateTimeColor'             => array( 'type' => 'string', 'default' => $color ),
            'temperatureScaleColor'     => array( 'type' => 'string', 'default' => $color ),
            'weatherDescColor'          => array( 'type' => 'string', 'default' => $color ),
            'additionalDataIconColor'   => array( 'type' => 'string', 'default' => $color ),
            'additionalDataLabelColor'  => array( 'type' => 'string', 'default' => $color ),
            'additionalDataValueColor'  => array( 'type' => 'string', 'default' => $color ),
            'forecastDataColor'         => array( 'type' => 'string', 'default' => $color ),
            'forecastLabelColor'        => array( 'type' => 'string', 'default' => $color ),
            'weatherAttributionColor'   => array( 'type' => 'string', 'default' => $color ),
            'weatherConditionIconColor' => array( 'type' => 'string', 'default' => $color ),
            'forecastDataIconColor'     => array( 'type' => 'string', 'default' => $color ),
        );
    }

    /**
     * Get required block attributes.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_required_attributes() {
        return array(
            'uniqueId'          => array( 'type' => 'string', 'default' => '' ),
            'blockName'         => array( 'type' => 'string', 'default' => '' ),
            'pluginUrl'         => array( 'type' => 'string', 'default' => '' ),
            'dynamicClassNames' => array( 'type' => 'object', 'default' => array() ),
            'fontLists'         => array( 'type' => 'string', 'default' => '' ),
            'iconUrl'           => array( 'type' => 'string', 'default' => '' ),
            'customCss'         => array( 'type' => 'string', 'default' => '' ),
            'customClassName'   => array( 'type' => 'string', 'default' => '' ),
            'align'             => array( 'type' => 'string', 'default' => 'wide' ),
            'template'          => array( 'type' => 'string', 'default' => '' ),
            'isPreview'         => array( 'type' => 'boolean', 'default' => false ),
        );
    }

    /**
     * Get responsive single value attribute.
     *
     * @since 1.0.0
     * @param string $desktop Desktop default value.
     * @param string $tablet  Tablet default value.
     * @param string $mobile  Mobile default value.
     * @param string $unit    Unit (px, em, rem, %).
     * @return array
     */
    public static function responsive_value_attr( $desktop = '', $tablet = '', $mobile = '', $unit = 'px' ) {
        return array(
            'type'    => 'object',
            'default' => array(
                'device' => array(
                    'Desktop' => $desktop,
                    'Tablet'  => $tablet,
                    'Mobile'  => $mobile,
                ),
                'unit'   => array(
                    'Desktop' => $unit,
                    'Tablet'  => $unit,
                    'Mobile'  => $unit,
                ),
            ),
        );
    }

    /**
     * Get responsive spacing attribute (top, right, bottom, left).
     *
     * @since 1.0.0
     * @param string $top        Top default value.
     * @param string $right      Right default value.
     * @param string $bottom     Bottom default value.
     * @param string $left       Left default value.
     * @param string $unit       Unit (px, em, rem).
     * @param bool   $all_change Whether all sides change together.
     * @return array
     */
    public static function responsive_spacing_attr( $top = '', $right = '', $bottom = '', $left = '', $unit = 'px', $all_change = false ) {
        return array(
            'type'    => 'object',
            'default' => array(
                'device'    => array(
                    'Desktop' => compact( 'top', 'right', 'bottom', 'left' ),
                    'Tablet'  => array( 'top' => '', 'right' => '', 'bottom' => '', 'left' => '' ),
                    'Mobile'  => array( 'top' => '', 'right' => '', 'bottom' => '', 'left' => '' ),
                ),
                'unit'      => array(
                    'Desktop' => $unit,
                    'Tablet'  => $unit,
                    'Mobile'  => $unit,
                ),
                'allChange' => $all_change,
            ),
        );
    }

    /**
     * Get non-responsive spacing attribute.
     *
     * @since 1.0.0
     * @param string $top        Top default value.
     * @param string $right      Right default value.
     * @param string $bottom     Bottom default value.
     * @param string $left       Left default value.
     * @param string $unit       Unit.
     * @param bool   $all_change Whether all sides change together.
     * @return array
     */
    public static function spacing_attr( $top = '', $right = '', $bottom = '', $left = '', $unit = 'px', $all_change = true ) {
        return array(
            'type'    => 'object',
            'default' => array(
                'value'     => compact( 'top', 'right', 'bottom', 'left' ),
                'unit'      => $unit,
                'allChange' => $all_change,
            ),
        );
    }

    /**
     * Get typography attribute.
     *
     * @since 1.0.0
     * @param string $weight Default font weight.
     * @param string $style  Default font style.
     * @param string $transform Default text transform.
     * @param string $decoration Default text decoration.
     * @return array
     */
    public static function typography_attr( $weight = '400', $style = 'normal', $transform = 'none', $decoration = 'none' ) {
        return array(
            'type'    => 'object',
            'default' => array(
                'family'     => '',
                'fontWeight' => $weight,
                'style'      => $style,
                'transform'  => $transform,
                'decoration' => $decoration,
            ),
        );
    }

    /**
     * Get box shadow attribute.
     *
     * @since 1.0.0
     * @param string $top    Top shadow.
     * @param string $right  Right shadow.
     * @param string $bottom Bottom shadow.
     * @param string $left   Left shadow.
     * @param string $color  Shadow color.
     * @return array
     */
    public static function box_shadow_attr( $top = '0', $right = '3', $bottom = '6', $left = '0', $color = '#00000026' ) {
        return array(
            'type'    => 'object',
            'default' => array(
                'value'      => compact( 'top', 'right', 'bottom', 'left' ),
                'unit'       => 'Outset',
                'color'      => $color,
                'hoverColor' => '',
            ),
        );
    }

    /**
     * Get border attribute.
     *
     * @since 1.0.0
     * @param string $style Border style.
     * @param string $color Border color.
     * @return array
     */
    public static function border_attr( $style = 'solid', $color = '#e2e2e2' ) {
        return array(
            'type'    => 'object',
            'default' => array(
                'style'      => $style,
                'color'      => $color,
                'hoverColor' => '',
            ),
        );
    }

    /**
     * Get API configuration attributes.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_api_attributes() {
        return array(
            'lw_api_type'              => array( 'type' => 'string', 'default' => self::DEFAULT_API_SOURCE ),
            'searchWeatherBy'          => array( 'type' => 'string', 'default' => 'city_name' ),
            'getDataByCityName'        => array( 'type' => 'string', 'default' => 'London, GB' ),
            'getDataByCityID'          => array( 'type' => 'string', 'default' => '2643743' ),
            'getDataByZIPCode'         => array( 'type' => 'string', 'default' => '77070,US' ),
            'getDataByCoordinates'     => array( 'type' => 'string', 'default' => '51.509865,-0.118092' ),
            'customCityName'           => array( 'type' => 'string', 'default' => '' ),
            'displayTemperatureUnit'   => array( 'type' => 'string', 'default' => 'metric' ),
            'displayPressureUnit'      => array( 'type' => 'string', 'default' => 'hpa' ),
            'displayPrecipitationUnit' => array( 'type' => 'string', 'default' => 'mm' ),
            'displayWindSpeedUnit'     => array( 'type' => 'string', 'default' => 'kmh' ),
            'displayVisibilityUnit'    => array( 'type' => 'string', 'default' => 'km' ),
            'splwLanguage'             => array( 'type' => 'string', 'default' => 'en' ),
            'splwTimeZone'             => array( 'type' => 'string', 'default' => 'auto' ),
            'splwDateFormat'           => array( 'type' => 'string', 'default' => 'M j, Y' ),
            'splwCustomDateFormat'     => array( 'type' => 'string', 'default' => 'F j, Y' ),
            'splwTimeFormat'           => array( 'type' => 'string', 'default' => 'g:i A' ),
        );
    }

    /**
     * Get forecast attributes.
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_forecast_attributes() {
        return array(
            'displayWeatherForecastData' => array( 'type' => 'boolean', 'default' => true ),
            'weatherForecastType'        => array( 'type' => 'string', 'default' => 'hourly' ),
            'hourlyTitle'                => array( 'type' => 'string', 'default' => 'Hourly Forecast' ),
            'hourlyForecastType'         => array( 'type' => 'string', 'default' => '3' ),
            'numberOfForecastHours'      => array( 'type' => 'string', 'default' => '8' ),
            'numOfForecastThreeHours'    => array( 'type' => 'string', 'default' => '8' ),
            'forecastDataIcon'           => array( 'type' => 'boolean', 'default' => true ),
            'forecastDataIconType'       => array( 'type' => 'string', 'default' => 'forecast_icon_set_one' ),
            'forecastDataIconSize'       => self::responsive_value_attr( 48 ),
        );
    }

    /**
     * Get shared attributes (all combined).
     *
     * @since 1.0.0
     * @return array
     */
    private static function get_shared_attributes() {
        return array_merge(
            self::get_api_attributes(),
            self::get_forecast_attributes(),
            array(
                // Visibility.
                'splwHideOnDesktop' => array( 'type' => 'boolean', 'default' => false ),
                'splwHideOnTablet'  => array( 'type' => 'boolean', 'default' => false ),
                'splwHideOnMobile'  => array( 'type' => 'boolean', 'default' => false ),
                
                // Additional data options.
                'displayAdditionalData' => array( 'type' => 'boolean', 'default' => true ),
                'additionalDataOptions' => array(
                    'type'    => 'array',
                    'default' => self::get_default_additional_data_options(),
                ),
                
                // Forecast data options.
                'forecastData' => array(
                    'type'    => 'array',
                    'default' => self::get_default_forecast_options(),
                ),
                
                // Main container styles.
                'bgColorType'   => array( 'type' => 'string', 'default' => 'bgColor' ),
                'bgColor'       => array( 'type' => 'string', 'default' => '#FFFFFF' ),
                'bgGradient'    => array( 'type' => 'string', 'default' => 'linear-gradient(135deg, #8E2DE2 0%, #4A00E0 100%)' ),
                'splwPadding'   => self::responsive_spacing_attr( '20', '20', '20', '20' ),
                'splwBorder'    => self::border_attr( 'none', '#ddd' ),
                'splwBorderRadius' => self::spacing_attr( '8', '8', '8', '8' ),
                'enableSplwBoxShadow' => array( 'type' => 'boolean', 'default' => false ),
                'splwBoxShadow' => self::box_shadow_attr(),
                'splwMaxWidth'  => self::responsive_value_attr( 1200 ),
                
                // Typography defaults.
                'locationNameTypography' => self::typography_attr( '600' ),
                'locationNameFontSize'   => self::responsive_value_attr( 27 ),
                'locationNameLineHeight' => self::responsive_value_attr( 38 ),
                
                'dateTimeTypography' => self::typography_attr( '500' ),
                'dateTimeFontSize'   => self::responsive_value_attr( 14 ),
                'dateTimeLineHeight' => self::responsive_value_attr( 16 ),
                
                'temperatureScaleTypography' => self::typography_attr( '600' ),
                'temperatureScaleFontSize'   => self::responsive_value_attr( 48 ),
                'temperatureScaleLineHeight' => self::responsive_value_attr( 56 ),
                
                'weatherDescTypography' => self::typography_attr( '600', 'normal', 'capitalize' ),
                'weatherDescFontSize'   => self::responsive_value_attr( 16 ),
                'weatherDescLineHeight' => self::responsive_value_attr( 20 ),
                
                // Additional data typography.
                'additionalDataLabelTypography' => self::typography_attr( '400' ),
                'additionalDataLabelFontSize'   => self::responsive_value_attr( 14 ),
                'additionalDataLabelLineHeight' => self::responsive_value_attr( 20 ),
                
                'additionalDataValueTypography' => self::typography_attr( '600' ),
                'additionalDataValueFontSize'   => self::responsive_value_attr( 14 ),
                'additionalDataValueLineHeight' => self::responsive_value_attr( 20 ),
                
                // Forecast typography.
                'forecastLabelTypography' => self::typography_attr( '400' ),
                'forecastLabelFontSize'   => self::responsive_value_attr( 14 ),
                'forecastLabelLineHeight' => self::responsive_value_attr( 24 ),
                
                'forecastDataTypography' => self::typography_attr( '400' ),
                'forecastDataFontSize'   => self::responsive_value_attr( 14 ),
                'forecastDataLineHeight' => self::responsive_value_attr( 24 ),
                
                // Weather attribution.
                'displayWeatherAttribution' => array( 'type' => 'boolean', 'default' => true ),
                'weatherAttributionTypography' => self::typography_attr( '400' ),
                'weatherAttributionFontSize'   => self::responsive_value_attr( 12 ),
                'weatherAttributionLineHeight' => self::responsive_value_attr( 26 ),
                
                // Feature toggles.
                'showLocationName'     => array( 'type' => 'boolean', 'default' => true ),
                'showCurrentDate'      => array( 'type' => 'boolean', 'default' => true ),
                'showCurrentTime'      => array( 'type' => 'boolean', 'default' => true ),
                'showNationalAlerts'   => array( 'type' => 'boolean', 'default' => false ),
                'showPreloader'        => array( 'type' => 'boolean', 'default' => true ),
                'displayTemperature'   => array( 'type' => 'boolean', 'default' => true ),
                'displayWeatherConditions' => array( 'type' => 'boolean', 'default' => true ),
                'weatherConditionIcon' => array( 'type' => 'boolean', 'default' => true ),
                'weatherConditionIconSize' => self::responsive_value_attr( 60 ),
                'disableWeatherIconAnimation' => array( 'type' => 'boolean', 'default' => false ),
            )
        );
    }

    /**
     * Get default additional data options.
     *
     * @since 1.0.0
     * @return array
     */
    private static function get_default_additional_data_options() {
        return array(
            array( 'id' => 1,  'value' => 'humidity',       'isActive' => true ),
            array( 'id' => 2,  'value' => 'pressure',       'isActive' => true ),
            array( 'id' => 3,  'value' => 'wind',           'isActive' => true ),
            array( 'id' => 4,  'value' => 'clouds',         'isActive' => true ),
            array( 'id' => 5,  'value' => 'gust',           'isActive' => true ),
            array( 'id' => 6,  'value' => 'visibility',     'isActive' => true ),
            array( 'id' => 7,  'value' => 'sunriseSunset',  'isActive' => true ),
            array( 'id' => 8,  'value' => 'uv_index',       'isActive' => false ),
            array( 'id' => 9,  'value' => 'precipitation',  'isActive' => false ),
            array( 'id' => 10, 'value' => 'dew_point',      'isActive' => false ),
            array( 'id' => 11, 'value' => 'rain_chance',    'isActive' => false ),
            array( 'id' => 12, 'value' => 'snow',           'isActive' => false ),
            array( 'id' => 13, 'value' => 'air_quality',    'isActive' => false ),
            array( 'id' => 14, 'value' => 'moonriseMoonset','isActive' => false ),
            array( 'id' => 15, 'value' => 'moon_phase',     'isActive' => false ),
        );
    }

    /**
     * Get default forecast data options.
     *
     * @since 1.0.0
     * @return array
     */
    private static function get_default_forecast_options() {
        return array(
            array( 'id' => 1, 'name' => 'temperature',   'value' => true ),
            array( 'id' => 2, 'name' => 'precipitation', 'value' => true ),
            array( 'id' => 3, 'name' => 'rain_chance',   'value' => true ),
            array( 'id' => 4, 'name' => 'wind',          'value' => true ),
            array( 'id' => 5, 'name' => 'humidity',      'value' => true ),
            array( 'id' => 6, 'name' => 'pressure',      'value' => true ),
            array( 'id' => 7, 'name' => 'snow',          'value' => false ),
        );
    }
}

// Helper functions for backward compatibility.
if ( ! function_exists( 'pearl_weather_get_color_attributes' ) ) {
    /**
     * Get color attributes.
     *
     * @param string $color Default color.
     * @return array
     */
    function pearl_weather_get_color_attributes( $color = '' ) {
        return BlockAttributes::get_color_attributes( $color );
    }
}

if ( ! function_exists( 'pearl_weather_responsive_value_attr' ) ) {
    /**
     * Get responsive value attribute.
     *
     * @param string $desktop Desktop value.
     * @param string $tablet  Tablet value.
     * @param string $mobile  Mobile value.
     * @param string $unit    Unit.
     * @return array
     */
    function pearl_weather_responsive_value_attr( $desktop = '', $tablet = '', $mobile = '', $unit = 'px' ) {
        return BlockAttributes::responsive_value_attr( $desktop, $tablet, $mobile, $unit );
    }
}

if ( ! function_exists( 'pearl_weather_responsive_spacing_attr' ) ) {
    /**
     * Get responsive spacing attribute.
     *
     * @param string $top    Top value.
     * @param string $right  Right value.
     * @param string $bottom Bottom value.
     * @param string $left   Left value.
     * @param string $unit   Unit.
     * @param bool   $all_change All sides change together.
     * @return array
     */
    function pearl_weather_responsive_spacing_attr( $top = '', $right = '', $bottom = '', $left = '', $unit = 'px', $all_change = false ) {
        return BlockAttributes::responsive_spacing_attr( $top, $right, $bottom, $left, $unit, $all_change );
    }
}