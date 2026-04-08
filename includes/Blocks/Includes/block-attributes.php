<?php
/**
 * Block-Specific Attribute Definitions
 *
 * Defines attribute overrides for each weather block variant
 * including vertical, horizontal, tabs, table, grid, map, and AQI layouts.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Blocks/Attributes
 * @since      1.0.0
 */

namespace PearlWeather\Blocks\Attributes;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use function PearlWeather\Blocks\BlockAttributes\get_color_attributes;
use function PearlWeather\Blocks\BlockAttributes\responsive_value_attr;
use function PearlWeather\Blocks\BlockAttributes\responsive_spacing_attr;
use function PearlWeather\Blocks\BlockAttributes\spacing_attr;
use function PearlWeather\Blocks\BlockAttributes\box_shadow_attr;
use function PearlWeather\Blocks\BlockAttributes\border_attr;

/**
 * Class BlockAttributeDefinitions
 *
 * Provides attribute definitions for each block variant.
 *
 * @since 1.0.0
 */
class BlockAttributeDefinitions {

    /**
     * Brand color constant.
     */
    const BRAND_COLOR = '#F26C0D';

    /**
     * Get vertical block attributes.
     *
     * @since 1.0.0
     * @param array $shared_attrs Shared attributes from main file.
     * @return array
     */
    public static function get_vertical_attributes( $shared_attrs ) {
        $overrides = array(
            'blockName'    => array( 'type' => 'string', 'default' => 'vertical' ),
            'align'        => array( 'type' => 'string', 'default' => 'none' ),
            'bgColor'      => array( 'type' => 'string', 'default' => self::BRAND_COLOR ),
            'stripedColor' => array( 'type' => 'string', 'default' => '' ),
            
            'splwMaxWidth' => responsive_value_attr( 360, 360, 360 ),
            
            'additionalCarouselColumns' => array(
                'type'    => 'object',
                'default' => array(
                    'device' => array(
                        'Desktop' => 2,
                        'Tablet'  => 2,
                        'Mobile'  => 1,
                    ),
                ),
            ),
            
            'splwPadding' => array(
                'type'    => 'object',
                'default' => array(
                    'device'    => array(
                        'Desktop' => array( 'top' => '20', 'right' => '20', 'bottom' => '0', 'left' => '20' ),
                        'Tablet'  => array( 'top' => '20', 'right' => '20', 'bottom' => '0', 'left' => '20' ),
                        'Mobile'  => array( 'top' => '20', 'right' => '20', 'bottom' => '0', 'left' => '20' ),
                    ),
                    'unit'      => array( 'Desktop' => 'px', 'Tablet' => 'px', 'Mobile' => 'px' ),
                    'allChange' => false,
                ),
            ),
            
            'regionalPreferenceMargin' => responsive_spacing_attr( '0', '0', '18', '0' ),
            'weatherAttributionBgColor' => array( 'type' => 'string', 'default' => '#00000036' ),
            'additionalDataPadding' => responsive_spacing_attr( '3', '2', '3', '2' ),
            'detailedWeatherAndUpdateLineHeight' => responsive_value_attr( 12 ),
            
            // Tabs filter.
            'forecastTabsGap' => responsive_value_attr( 20 ),
            'forecastTabsLabelColor' => array(
                'type'    => 'object',
                'default' => array( 'color' => '', 'active' => '' ),
            ),
            'forecastActiveTabsBottomLineColor' => array( 'type' => 'string', 'default' => '#FFFFFF' ),
            'forecastTabsBottomLineWidth' => array(
                'type'    => 'object',
                'default' => array( 'value' => 2, 'unit' => 'px' ),
            ),
            'forecastTabsBottomLineColor' => array( 'type' => 'object', 'default' => 'rgba(236, 234, 233, 0.5)' ),
            'forecastTabsFullWidthBottomLine' => array(
                'type'    => 'object',
                'default' => array( 'value' => 1, 'unit' => 'px' ),
            ),
        );
        
        return array_merge( $shared_attrs, get_color_attributes(), $overrides );
    }

    /**
     * Get horizontal block attributes.
     *
     * @since 1.0.0
     * @param array $shared_attrs Shared attributes.
     * @return array
     */
    public static function get_horizontal_attributes( $shared_attrs ) {
        $overrides = array(
            'blockName' => array( 'type' => 'string', 'default' => 'horizontal' ),
            'bgColor'   => array( 'type' => 'string', 'default' => self::BRAND_COLOR ),
            
            'splwMaxWidth' => responsive_value_attr( 800 ),
            
            'forecastTabsGap' => responsive_value_attr( 20 ),
            'forecastTabsLabelColor' => array(
                'type'    => 'object',
                'default' => array( 'color' => '', 'active' => '' ),
            ),
            'forecastActiveTabsBottomLineColor' => array( 'type' => 'string', 'default' => '#FFFFFF' ),
            'forecastTabsBottomLineWidth' => array(
                'type'    => 'object',
                'default' => array( 'value' => 2, 'unit' => 'px' ),
            ),
            'forecastTabsBottomLineColor' => array( 'type' => 'object', 'default' => 'rgba(236, 234, 233, 0.5)' ),
            'forecastTabsFullWidthBottomLine' => array(
                'type'    => 'object',
                'default' => array( 'value' => 1, 'unit' => 'px' ),
            ),
            'forecastCarouselHorizontalGap' => responsive_value_attr( 0, 0, 0, 'px' ),
            'weatherAttributionBgColor' => array( 'type' => 'string', 'default' => '#00000036' ),
            'locationNameFontSize' => responsive_value_attr( 14 ),
            'locationNameLineHeight' => responsive_value_attr( 20 ),
        );
        
        return array_merge( $shared_attrs, get_color_attributes(), $overrides );
    }

    /**
     * Get tabs block attributes.
     *
     * @since 1.0.0
     * @param array $shared_attrs Shared attributes.
     * @param array $table_overrides Table overrides.
     * @param array $map_attrs Map attributes.
     * @return array
     */
    public static function get_tabs_attributes( $shared_attrs, $table_overrides, $map_attrs ) {
        $overrides = array(
            'templatePrimaryColor'   => array( 'type' => 'string', 'default' => '#2F2F2F' ),
            'templateSecondaryColor' => array( 'type' => 'string', 'default' => '#757575' ),
            'blockName'              => array( 'type' => 'string', 'default' => 'tabs' ),
            'displayWeatherMap'      => array( 'type' => 'boolean', 'default' => false ),
            'splwDefaultOpenTab'     => array( 'type' => 'string', 'default' => 'current_weather' ),
            'splwTabOrientation'     => array( 'type' => 'string', 'default' => 'horizontal' ),
            'splwTabAlignment'       => array( 'type' => 'string', 'default' => 'left' ),
            
            'tabTitleColors' => array(
                'type'    => 'object',
                'default' => array( 'color' => '#FFFFFF', 'activeColor' => self::BRAND_COLOR ),
            ),
            'tabTitleBgColors' => array(
                'type'    => 'object',
                'default' => array( 'color' => self::BRAND_COLOR, 'activeColor' => '#fff' ),
            ),
            'tabTopBorderColor' => array( 'type' => 'object', 'default' => self::BRAND_COLOR ),
            'tabTopBorderWidth' => responsive_value_attr( 4 ),
            
            'splwBorder' => border_attr( 'solid', self::BRAND_COLOR ),
            'splwPadding' => responsive_spacing_attr( '26', '26', '26', '26' ),
            
            'temperatureScaleTypography' => array(
                'type'    => 'object',
                'default' => array( 'family' => '', 'fontWeight' => '700', 'style' => 'normal', 'transform' => 'none', 'decoration' => 'none' ),
            ),
            'forecastDataIconSize' => responsive_value_attr( 32 ),
            'temperatureScaleFontSize' => responsive_value_attr( 64 ),
            'temperatureScaleLineHeight' => responsive_value_attr( 72 ),
            'weatherDescTypography' => array(
                'type'    => 'object',
                'default' => array( 'family' => '', 'fontWeight' => '600', 'style' => 'normal', 'transform' => 'capitalize', 'decoration' => 'none' ),
            ),
            'additionalDataPadding' => array(
                'type'    => 'object',
                'default' => array(
                    'device'    => array(
                        'Desktop' => array( 'top' => '8', 'right' => '14', 'bottom' => '8', 'left' => '14' ),
                        'Tablet'  => array( 'top' => '', 'right' => '', 'bottom' => '', 'left' => '' ),
                        'Mobile'  => array( 'top' => '5', 'right' => '10', 'bottom' => '5', 'left' => '10' ),
                    ),
                    'unit'      => array( 'Desktop' => 'px', 'Tablet' => 'px', 'Mobile' => 'px' ),
                    'allChange' => false,
                ),
            ),
            'additionalDataLabelFontSize' => responsive_value_attr( 14, '', 12 ),
            'additionalDataValueFontSize' => responsive_value_attr( 14, '', 12 ),
            'additionalDataMargin' => array(
                'type'    => 'object',
                'default' => array(
                    'device'    => array(
                        'Desktop' => array( 'top' => '14', 'right' => '0', 'bottom' => '8', 'left' => '0' ),
                        'Tablet'  => array( 'top' => '', 'right' => '', 'bottom' => '', 'left' => '' ),
                        'Mobile'  => array( 'top' => '', 'right' => '', 'bottom' => '', 'left' => '' ),
                    ),
                    'unit'      => array( 'Desktop' => 'px', 'Tablet' => 'px', 'Mobile' => 'px' ),
                    'allChange' => false,
                ),
            ),
            'temperatureUnitFontSize' => responsive_value_attr( 21 ),
            'temperatureUnitLineHeight' => responsive_value_attr( 27 ),
            'splwBorderWidth' => spacing_attr( '2', '2', '2', '2' ),
        );
        
        return array_merge( $shared_attrs, $table_overrides, $map_attrs, $overrides );
    }

    /**
     * Get table block attributes.
     *
     * @since 1.0.0
     * @param array $shared_attrs Shared attributes.
     * @return array
     */
    public static function get_table_attributes( $shared_attrs ) {
        $overrides = array(
            'templatePrimaryColor'   => array( 'type' => 'string', 'default' => '#2F2F2F' ),
            'templateSecondaryColor' => array( 'type' => 'string', 'default' => '#757575' ),
            'blockName'              => array( 'type' => 'string', 'default' => 'table' ),
            
            'forecastData' => array(
                'type'    => 'array',
                'default' => array(
                    array( 'id' => 1, 'name' => 'temperature',   'value' => true ),
                    array( 'id' => 4, 'name' => 'wind',          'value' => true ),
                    array( 'id' => 5, 'name' => 'humidity',      'value' => true ),
                    array( 'id' => 6, 'name' => 'pressure',      'value' => true ),
                    array( 'id' => 2, 'name' => 'precipitation', 'value' => true ),
                    array( 'id' => 3, 'name' => 'rainchance',    'value' => true ),
                    array( 'id' => 7, 'name' => 'snow',          'value' => false ),
                ),
            ),
            
            'temperatureScaleTypography' => array(
                'type'    => 'object',
                'default' => array( 'family' => '', 'fontWeight' => '700', 'style' => 'normal', 'transform' => 'none', 'decoration' => 'none' ),
            ),
            
            'tablePreferenceBorder' => array(
                'type'    => 'object',
                'default' => array( 'style' => 'solid', 'color' => '#DDDDDD' ),
            ),
            'tableHeaderColor'   => array( 'type' => 'string', 'default' => '' ),
            'tableHeaderBgColor' => array( 'type' => 'string', 'default' => '#e7ecf1' ),
            'tableEvenRowColor'  => array( 'type' => 'string', 'default' => '#FFFFFF' ),
            'tableOddRowColor'   => array( 'type' => 'string', 'default' => '#F4F4F4' ),
            
            'temperatureUnitFontSize'   => responsive_value_attr( 21 ),
            'temperatureUnitLineHeight' => responsive_value_attr( 27 ),
            'tablePreferenceBorderWidth' => spacing_attr( '1', '1', '1', '1' ),
            
            'splwBorder'       => border_attr( 'solid', '#E2E2E2' ),
            'splwBorderRadius' => spacing_attr( '4', '4', '4', '4' ),
            'additionalDataPadding' => responsive_spacing_attr( '8', '14', '8', '14' ),
            'additionalDataMargin'  => responsive_spacing_attr( '0', '0', '-2', '0' ),
            'forecastDataIconSize'  => responsive_value_attr( 32 ),
            'temperatureScaleFontSize'   => responsive_value_attr( 64 ),
            'temperatureScaleLineHeight' => responsive_value_attr( 72 ),
            'additionalDataIconSize' => responsive_value_attr( 14 ),
            'weatherAttributionBgColor' => array( 'type' => 'string', 'default' => '#00000036' ),
        );
        
        return array_merge( $shared_attrs, get_color_attributes(), $overrides );
    }

    /**
     * Get grid block attributes.
     *
     * @since 1.0.0
     * @param array $shared_attrs Shared attributes.
     * @param array $map_attrs Map attributes.
     * @param array $carousel_attrs Carousel attributes.
     * @param array $current_weather_attrs Current weather attributes.
     * @return array
     */
    public static function get_grid_attributes( $shared_attrs, $map_attrs, $carousel_attrs, $current_weather_attrs ) {
        $overrides = array(
            'blockName' => array( 'type' => 'string', 'default' => 'grid' ),
            'currentWeatherCardWidth' => responsive_value_attr( 50, 100, 100, '%' ),
            'currentWeatherMargin' => responsive_spacing_attr( '0', '0', '24', '0' ),
            
            'additionalDataColor' => array(
                'type'    => 'object',
                'default' => array( 'color' => '', 'hover' => '#FFFFFF' ),
            ),
            'additionalDataBgType' => array(
                'type'    => 'object',
                'default' => array( 'color' => 'bgColor', 'hover' => 'bgColor' ),
            ),
            'additionalDataBgColor' => array(
                'type'    => 'object',
                'default' => array( 'color' => '#f2f7fc', 'hover' => '#131F49' ),
            ),
            'additionalDataBgGradient' => array(
                'type'    => 'object',
                'default' => array(
                    'color' => 'linear-gradient(135deg, #A1C4FD 0%, #C2E9FB 50%, #E0EAFC 100%)',
                    'hover' => 'linear-gradient(135deg, #A1C4FD 0%, #C2E9FB 50%, #E0EAFC 100%)',
                ),
            ),
            'additionalDataBorder' => array(
                'type'    => 'object',
                'default' => array( 'style' => 'solid', 'color' => '#E2E2E2' ),
            ),
            'additionalDataBorderWidth'  => spacing_attr( '1', '1', '1', '1' ),
            'additionalDataBorderRadius' => spacing_attr( '8', '8', '8', '8' ),
            'enableAdditionalDataBoxShadow' => array( 'type' => 'boolean', 'default' => false ),
            'additionalDataBoxShadow' => box_shadow_attr( '4', '4', '8', '0', '#E0E0E0' ),
            'additionalDataPadding' => responsive_spacing_attr( '20', '20', '20', '20' ),
            
            'forecastLabelTypography' => array(
                'type'    => 'object',
                'default' => array( 'family' => '', 'fontWeight' => '600', 'style' => 'normal', 'transform' => 'none', 'decoration' => 'none' ),
            ),
            'forecastCarouselColumns' => array(
                'type'    => 'object',
                'default' => array(
                    'device' => array( 'Desktop' => 11, 'Tablet' => 5, 'Mobile' => 3 ),
                ),
            ),
            'forecastDataIconSize' => responsive_value_attr( 32 ),
            'forecastTabsGap' => responsive_value_attr( 20 ),
            'forecastActiveTabsBottomLineColor' => array( 'type' => 'string', 'default' => '#2F2F2F' ),
        );
        
        return array_merge( $shared_attrs, $map_attrs, $carousel_attrs, $current_weather_attrs, $overrides );
    }

    /**
     * Get windy map block attributes.
     *
     * @since 1.0.0
     * @param array $required_attrs Required attributes.
     * @param array $map_attrs Map attributes.
     * @return array
     */
    public static function get_windy_map_attributes( $required_attrs, $map_attrs ) {
        $overrides = array(
            'blockName'           => array( 'type' => 'string', 'default' => 'windy-map' ),
            'align'               => array( 'type' => 'string', 'default' => 'wide' ),
            'searchWeatherBy'     => array( 'type' => 'string', 'default' => 'latlong' ),
            'weatherMapPadding'   => responsive_spacing_attr( '20', '20', '20', '20' ),
            'weatherMapBgColorType' => array( 'type' => 'string', 'default' => 'bgColor' ),
            'weatherMapBgColor'   => array( 'type' => 'string', 'default' => '' ),
            'defaultDataLayerSelection' => array( 'type' => 'string', 'default' => 'wind' ),
        );
        
        return array_merge( $required_attrs, $map_attrs, $overrides );
    }

    /**
     * Get AQI minimal card attributes.
     *
     * @since 1.0.0
     * @param array $required_attrs Required attributes.
     * @param array $common_attrs Common attributes.
     * @param array $aqi_attrs AQI attributes.
     * @return array
     */
    public static function get_aqi_minimal_attributes( $required_attrs, $common_attrs, $aqi_attrs ) {
        $overrides = array(
            'blockName'    => array( 'type' => 'string', 'default' => 'aqi-minimal' ),
            'splwMaxWidth' => responsive_value_attr( 360 ),
            'splwPadding'  => responsive_spacing_attr( '20', '20', '0', '20' ),
            'splwBorder'   => border_attr( 'solid', '#e2e2e2' ),
            
            'imageType' => array( 'type' => 'string', 'default' => 'custom' ),
            'bgImage'   => array( 'type' => 'object', 'default' => array() ),
            'bgImagePosition'   => array( 'type' => 'string', 'default' => 'center' ),
            'bgImageAttachment' => array( 'type' => 'string', 'default' => 'scroll' ),
            'bgImageRepeat'     => array( 'type' => 'string', 'default' => 'no-repeat' ),
            'bgImageSize'       => array( 'type' => 'string', 'default' => 'cover' ),
            
            'showCurrentDate' => array( 'type' => 'boolean', 'default' => false ),
            'dateTimeColor'   => array( 'type' => 'string', 'default' => '' ),
            'locationNameFontSize' => responsive_value_attr( 14 ),
            'dateTimeFontSize'     => responsive_value_attr( 14 ),
            
            'aqiSummaryHeadingLabel' => array(
                'type'    => 'string',
                'default' => __( 'Today\'s Air Quality', 'pearl-weather' ),
            ),
            'aqiSummaryLabelFontSize' => responsive_value_attr( 16, 16, 16 ),
            
            'enableSummaryAqiCondition' => array( 'type' => 'boolean', 'default' => true ),
            'enableSummaryAqiDesc'      => array( 'type' => 'boolean', 'default' => true ),
            
            'aqiSummaryDescTypography' => array(
                'type'    => 'object',
                'default' => array( 'family' => '', 'fontWeight' => '400', 'style' => 'normal', 'transform' => 'none', 'decoration' => 'none' ),
            ),
            'aqiSummaryDescFontSize' => responsive_value_attr( 14 ),
            'aqiSummaryDescLineHeight' => responsive_value_attr( 18 ),
            
            'enablePollutantMeasurementUnit' => array( 'type' => 'boolean', 'default' => false ),
            'enablePollutantIndicator'       => array( 'type' => 'boolean', 'default' => true ),
            
            'pollutantConditionLabelFontSize' => responsive_value_attr( 14, 14, 14 ),
            'pollutantValueFontSize'          => responsive_value_attr( 14, 14, 14 ),
            
            'pollutantParametersHorizontalGap' => responsive_value_attr( 12, 12, 12 ),
            'pollutantParametersVerticalGap'   => responsive_value_attr( 12, 12, 12 ),
            
            'displayWeatherAttribution'   => array( 'type' => 'boolean', 'default' => false ),
            'displayLinkToOpenWeatherMap' => array( 'type' => 'boolean', 'default' => false ),
            'displayDateUpdateTime'       => array( 'type' => 'boolean', 'default' => false ),
            
            'weatherAttributionBgColor' => array( 'type' => 'string', 'default' => 'rgba(0, 0, 0, 0.2)' ),
            'bgOverlay'                 => array( 'type' => 'string', 'default' => '#00000075' ),
            'videoType'                 => array( 'type' => 'string', 'default' => 'youtube' ),
            'bgVideo'                   => array( 'type' => 'object', 'default' => array() ),
            'youtubeVideo' => array(
                'type'    => 'string',
                'default' => 'https://www.youtube.com/watch?v=CEh5Ej6LSSQ',
            ),
        );
        
        return array_merge( $required_attrs, $common_attrs, $aqi_attrs, $overrides );
    }
}