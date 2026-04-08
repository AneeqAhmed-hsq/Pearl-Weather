<?php
/**
 * Gutenberg Block Registration Handler
 *
 * Registers all weather blocks with WordPress, manages block visibility
 * settings, and configures block assets and render callbacks.
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
 * Class BlockRegistrar
 *
 * Handles registration of all Gutenberg blocks for the plugin.
 *
 * @since 1.0.0
 */
class BlockRegistrar {

    /**
     * Option name for block visibility settings.
     */
    const BLOCK_VISIBILITY_OPTION = 'pearl_weather_block_visibility';

    /**
     * Block renderer instance.
     *
     * @var TemplateRenderer
     */
    private $renderer;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->renderer = new TemplateRenderer();
    }

    /**
     * Register all weather blocks.
     *
     * @since 1.0.0
     */
    public function register_blocks() {
        // Load block attributes.
        $block_attributes = $this->load_block_attributes();
        
        // Prepare block definitions.
        $blocks = $this->get_block_definitions( $block_attributes );
        
        // Add blocks to settings option (for visibility management).
        $this->update_block_visibility_settings( $blocks );
        
        // Register only active blocks.
        $this->register_active_blocks( $blocks );
    }

    /**
     * Load block attributes from configuration files.
     *
     * @since 1.0.0
     * @return array
     */
    private function load_block_attributes() {
        // Base shared options for all blocks.
        $shared_options = array(
            'editor_script' => 'pearl-weather-block-editor',
            'editor_style'  => 'pearl-weather-block-editor',
            'style'         => 'pearl-weather-block-frontend',
            'script'        => 'pearl-weather-block-frontend',
            'render_callback' => array( $this->renderer, 'render_block' ),
        );
        
        // Block-specific attributes.
        // Note: In a real implementation, these would be loaded from separate files.
        $attributes = array(
            'vertical' => $this->get_vertical_attributes(),
            'horizontal' => $this->get_horizontal_attributes(),
            'grid' => $this->get_grid_attributes(),
            'tabs' => $this->get_tabs_attributes(),
            'table' => $this->get_table_attributes(),
            'aqi_minimal' => $this->get_aqi_minimal_attributes(),
        );
        
        return array(
            'shared_options' => $shared_options,
            'attributes' => $attributes,
        );
    }

    /**
     * Get block definitions.
     *
     * @since 1.0.0
     * @param array $block_attributes Loaded attributes.
     * @return array
     */
    private function get_block_definitions( $block_attributes ) {
        $shared_options = $block_attributes['shared_options'];
        $attrs = $block_attributes['attributes'];
        
        return array(
            array(
                'name'          => 'pearl-weather/vertical-card',
                'block_options' => array_merge( $shared_options, array(
                    'attributes' => $attrs['vertical'],
                ) ),
            ),
            array(
                'name'          => 'pearl-weather/horizontal-card',
                'block_options' => array_merge( $shared_options, array(
                    'attributes' => $attrs['horizontal'],
                ) ),
            ),
            array(
                'name'          => 'pearl-weather/grid-card',
                'block_options' => array_merge( $shared_options, array(
                    'attributes' => $attrs['grid'],
                ) ),
            ),
            array(
                'name'          => 'pearl-weather/tabs-card',
                'block_options' => array_merge( $shared_options, array(
                    'attributes' => $attrs['tabs'],
                ) ),
            ),
            array(
                'name'          => 'pearl-weather/table-card',
                'block_options' => array_merge( $shared_options, array(
                    'attributes' => $attrs['table'],
                ) ),
            ),
            array(
                'name'          => 'pearl-weather/aqi-minimal',
                'block_options' => array_merge( $shared_options, array(
                    'attributes' => $attrs['aqi_minimal'],
                ) ),
            ),
            array(
                'name'          => 'pearl-weather/section-heading',
                'block_options' => array(
                    'editor_script'   => 'pearl-weather-block-editor',
                    'editor_style'    => 'pearl-weather-block-editor',
                    'style'           => 'pearl-weather-block-frontend',
                    'render_callback' => array( $this, 'render_section_heading' ),
                ),
            ),
        );
    }

    /**
     * Update block visibility settings in database.
     *
     * @since 1.0.0
     * @param array $blocks Block definitions.
     */
    private function update_block_visibility_settings( $blocks ) {
        $new_settings = array_map( function( $block ) {
            return array(
                'name' => $block['name'],
                'show' => true,
            );
        }, $blocks );
        
        $existing_settings = get_option( self::BLOCK_VISIBILITY_OPTION, array() );
        
        // If no existing settings, save new ones.
        if ( empty( $existing_settings ) ) {
            update_option( self::BLOCK_VISIBILITY_OPTION, $new_settings );
            return;
        }
        
        // Merge existing settings with new blocks.
        $final_settings = array();
        foreach ( $new_settings as $new_block ) {
            $existing = $this->find_block_in_settings( $existing_settings, $new_block['name'] );
            $final_settings[] = $existing ? $existing : $new_block;
        }
        
        update_option( self::BLOCK_VISIBILITY_OPTION, $final_settings );
    }

    /**
     * Find a block in existing settings.
     *
     * @since 1.0.0
     * @param array  $settings Existing settings.
     * @param string $block_name Block name to find.
     * @return array|null
     */
    private function find_block_in_settings( $settings, $block_name ) {
        foreach ( $settings as $setting ) {
            if ( isset( $setting['name'] ) && $setting['name'] === $block_name ) {
                return $setting;
            }
        }
        return null;
    }

    /**
     * Register only blocks that are marked as active.
     *
     * @since 1.0.0
     * @param array $blocks Block definitions.
     */
    private function register_active_blocks( $blocks ) {
        $visibility_settings = get_option( self::BLOCK_VISIBILITY_OPTION, array() );
        $active_blocks = $this->get_active_blocks_map( $visibility_settings );
        
        foreach ( $blocks as $block ) {
            $block_name = $block['name'];
            
            // Register block if active (or if no visibility setting exists).
            if ( ! isset( $active_blocks[ $block_name ] ) || $active_blocks[ $block_name ] ) {
                register_block_type( $block_name, $block['block_options'] );
            }
        }
    }

    /**
     * Get map of active blocks from visibility settings.
     *
     * @since 1.0.0
     * @param array $settings Visibility settings.
     * @return array
     */
    private function get_active_blocks_map( $settings ) {
        $map = array();
        
        foreach ( $settings as $setting ) {
            if ( isset( $setting['name'] ) ) {
                $map[ $setting['name'] ] = isset( $setting['show'] ) ? (bool) $setting['show'] : true;
            }
        }
        
        return $map;
    }

    /**
     * Render callback for section heading block.
     *
     * @since 1.0.0
     * @param array  $attributes Block attributes.
     * @param string $content    Inner block content.
     * @return string
     */
    public function render_section_heading( $attributes, $content ) {
        $heading = isset( $attributes['heading'] ) ? sanitize_text_field( $attributes['heading'] ) : '';
        $subheading = isset( $attributes['subheading'] ) ? sanitize_text_field( $attributes['subheading'] ) : '';
        $level = isset( $attributes['level'] ) ? absint( $attributes['level'] ) : 2;
        $align = isset( $attributes['align'] ) ? sanitize_text_field( $attributes['align'] ) : 'left';
        
        if ( empty( $heading ) && empty( $content ) ) {
            return '';
        }
        
        $tag = "h{$level}";
        $classes = array(
            'pw-section-heading',
            "pw-align-{$align}",
        );
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
            <?php if ( ! empty( $heading ) ) : ?>
                <<?php echo esc_attr( $tag ); ?> class="pw-section-title">
                    <?php echo esc_html( $heading ); ?>
                </<?php echo esc_attr( $tag ); ?>>
            <?php endif; ?>
            
            <?php if ( ! empty( $subheading ) ) : ?>
                <div class="pw-section-subtitle">
                    <?php echo esc_html( $subheading ); ?>
                </div>
            <?php endif; ?>
            
            <?php if ( ! empty( $content ) ) : ?>
                <div class="pw-section-content">
                    <?php echo wp_kses_post( $content ); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get vertical card block attributes.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_vertical_attributes() {
        return array(
            'blockName' => array( 'type' => 'string', 'default' => 'vertical' ),
            'uniqueId'  => array( 'type' => 'string', 'default' => '' ),
            'align'     => array( 'type' => 'string', 'default' => 'wide' ),
            'bgColor'   => array( 'type' => 'string', 'default' => '#FFFFFF' ),
            'showLocationName' => array( 'type' => 'boolean', 'default' => true ),
            'showCurrentDate'  => array( 'type' => 'boolean', 'default' => true ),
            'showCurrentTime'  => array( 'type' => 'boolean', 'default' => true ),
            'displayTemperature' => array( 'type' => 'boolean', 'default' => true ),
            'displayWeatherConditions' => array( 'type' => 'boolean', 'default' => true ),
            'displayTemperatureUnit' => array( 'type' => 'string', 'default' => 'metric' ),
            'displayWeatherForecastData' => array( 'type' => 'boolean', 'default' => true ),
            'numberOfForecastHours' => array( 'type' => 'string', 'default' => '8' ),
            'showPreloader' => array( 'type' => 'boolean', 'default' => true ),
            'splwPadding'   => array( 'type' => 'object', 'default' => array() ),
            'splwBorder'    => array( 'type' => 'object', 'default' => array() ),
        );
    }

    /**
     * Get horizontal card block attributes.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_horizontal_attributes() {
        return array_merge( $this->get_vertical_attributes(), array(
            'blockName' => array( 'type' => 'string', 'default' => 'horizontal' ),
        ) );
    }

    /**
     * Get grid card block attributes.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_grid_attributes() {
        return array_merge( $this->get_vertical_attributes(), array(
            'blockName' => array( 'type' => 'string', 'default' => 'grid' ),
            'forecastCarouselColumns' => array(
                'type'    => 'object',
                'default' => array(
                    'device' => array(
                        'Desktop' => 5,
                        'Tablet'  => 3,
                        'Mobile'  => 2,
                    ),
                ),
            ),
        ) );
    }

    /**
     * Get tabs card block attributes.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_tabs_attributes() {
        return array_merge( $this->get_vertical_attributes(), array(
            'blockName' => array( 'type' => 'string', 'default' => 'tabs' ),
            'splwDefaultOpenTab' => array( 'type' => 'string', 'default' => 'current_weather' ),
            'splwTabOrientation' => array( 'type' => 'string', 'default' => 'horizontal' ),
        ) );
    }

    /**
     * Get table card block attributes.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_table_attributes() {
        return array_merge( $this->get_vertical_attributes(), array(
            'blockName' => array( 'type' => 'string', 'default' => 'table' ),
            'tableHeaderColor' => array( 'type' => 'string', 'default' => '' ),
            'tableHeaderBgColor' => array( 'type' => 'string', 'default' => '#e7ecf1' ),
            'tableEvenRowColor' => array( 'type' => 'string', 'default' => '#FFFFFF' ),
            'tableOddRowColor'  => array( 'type' => 'string', 'default' => '#F4F4F4' ),
        ) );
    }

    /**
     * Get AQI minimal card block attributes.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_aqi_minimal_attributes() {
        return array(
            'blockName' => array( 'type' => 'string', 'default' => 'aqi-minimal' ),
            'uniqueId'  => array( 'type' => 'string', 'default' => '' ),
            'align'     => array( 'type' => 'string', 'default' => 'wide' ),
            'aqiSummaryHeadingLabel' => array(
                'type'    => 'string',
                'default' => __( 'Today\'s Air Quality', 'pearl-weather' ),
            ),
            'enableSummaryAqiCondition' => array( 'type' => 'boolean', 'default' => true ),
            'enableSummaryAqiDesc'      => array( 'type' => 'boolean', 'default' => true ),
            'enablePollutantDetails'    => array( 'type' => 'boolean', 'default' => true ),
            'showPreloader' => array( 'type' => 'boolean', 'default' => true ),
        );
    }
}

// Hook into WordPress init action.
if ( ! function_exists( 'pearl_weather_register_blocks' ) ) {
    /**
     * Initialize block registration.
     *
     * @since 1.0.0
     */
    function pearl_weather_register_blocks() {
        $registrar = new BlockRegistrar();
        $registrar->register_blocks();
    }
    add_action( 'init', 'pearl_weather_register_blocks' );
}