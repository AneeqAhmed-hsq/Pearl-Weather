<?php
/**
 * Weather Description Template Part
 *
 * Displays the weather short description with optional icon,
 * custom styling, and multiple display formats.
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
 * - $template: Template variant name
 * - $block_name: Block name
 */

// Check if weather conditions should be displayed.
$show_description = isset( $attributes['displayWeatherConditions'] ) ? (bool) $attributes['displayWeatherConditions'] : true;

if ( ! $show_description ) {
    return;
}

// Get weather description.
$description = isset( $weather_data['description'] ) ? $weather_data['description'] : ( isset( $weather_data['desc'] ) ? $weather_data['desc'] : '' );

// If no description, exit.
if ( empty( $description ) ) {
    return;
}

// Description settings.
$description_format = isset( $attributes['descriptionFormat'] ) ? sanitize_text_field( $attributes['descriptionFormat'] ) : 'capitalize';
$show_icon = isset( $attributes['showDescriptionIcon'] ) ? (bool) $attributes['showDescriptionIcon'] : false;
$icon_position = isset( $attributes['descriptionIconPosition'] ) ? sanitize_text_field( $attributes['descriptionIconPosition'] ) : 'left';
$show_feels_like = isset( $attributes['showFeelsLikeInDescription'] ) ? (bool) $attributes['showFeelsLikeInDescription'] : false;
$show_condition_emoji = isset( $attributes['showConditionEmoji'] ) ? (bool) $attributes['showConditionEmoji'] : false;

// Get feels-like temperature if available.
$feels_like = isset( $weather_data['feels_like'] ) ? $weather_data['feels_like'] : '';
$temp_unit = isset( $weather_data['temp_unit'] ) ? $weather_data['temp_unit'] : '°C';

// Format the description text.
switch ( $description_format ) {
    case 'uppercase':
        $description = strtoupper( $description );
        break;
    case 'lowercase':
        $description = strtolower( $description );
        break;
    case 'capitalize':
    default:
        $description = ucwords( $description );
        break;
}

// Get condition emoji based on description.
$condition_emoji = '';
if ( $show_condition_emoji ) {
    $condition_emoji = get_weather_emoji( $description );
}

// Layout orientation.
$is_vertical = isset( $attributes['layoutOrientation'] ) && 'vertical' === $attributes['layoutOrientation'];

// Additional CSS classes.
$wrapper_classes = array( 'pw-weather-description' );

if ( $is_vertical ) {
    $wrapper_classes[] = 'pw-description-vertical';
}

if ( ! empty( $attributes['descriptionCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['descriptionCustomClass'] );
}

// Font size variant.
$font_size = isset( $attributes['descriptionFontSize'] ) ? sanitize_text_field( $attributes['descriptionFontSize'] ) : 'medium';
$size_classes = array(
    'small'  => 'pw-desc-small',
    'medium' => 'pw-desc-medium',
    'large'  => 'pw-desc-large',
);
$size_class = isset( $size_classes[ $font_size ] ) ? $size_classes[ $font_size ] : 'pw-desc-medium';

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    
    <!-- Description with Icon -->
    <div class="pw-description-wrapper <?php echo esc_attr( $size_class ); ?>">
        
        <!-- Icon Left -->
        <?php if ( $show_icon && 'left' === $icon_position ) : ?>
            <span class="pw-desc-icon pw-icon-left" aria-hidden="true">
                <?php echo get_weather_condition_icon( $description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </span>
        <?php endif; ?>
        
        <!-- Condition Emoji -->
        <?php if ( $show_condition_emoji && ! empty( $condition_emoji ) ) : ?>
            <span class="pw-desc-emoji" aria-hidden="true"><?php echo esc_html( $condition_emoji ); ?></span>
        <?php endif; ?>
        
        <!-- Description Text -->
        <span class="pw-desc-text"><?php echo esc_html( $description ); ?></span>
        
        <!-- Icon Right -->
        <?php if ( $show_icon && 'right' === $icon_position ) : ?>
            <span class="pw-desc-icon pw-icon-right" aria-hidden="true">
                <?php echo get_weather_condition_icon( $description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </span>
        <?php endif; ?>
        
        <!-- Feels Like Temperature -->
        <?php if ( $show_feels_like && ! empty( $feels_like ) ) : ?>
            <span class="pw-feels-like-badge">
                <span class="pw-feels-like-label"><?php esc_html_e( 'Feels like', 'pearl-weather' ); ?></span>
                <span class="pw-feels-like-value"><?php echo esc_html( $feels_like ) . esc_html( $temp_unit ); ?></span>
            </span>
        <?php endif; ?>
        
    </div>
    
</div>

<style>
/* Weather Description Styles */
.pw-weather-description {
    display: flex;
    justify-content: center;
    align-items: center;
}

.pw-description-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    justify-content: center;
}

/* Font Sizes */
.pw-desc-small .pw-desc-text {
    font-size: 12px;
}

.pw-desc-medium .pw-desc-text {
    font-size: 16px;
}

.pw-desc-large .pw-desc-text {
    font-size: 20px;
}

/* Description Text */
.pw-desc-text {
    font-weight: 500;
    text-transform: capitalize;
    color: inherit;
}

/* Description Icon */
.pw-desc-icon {
    display: inline-flex;
    align-items: center;
    line-height: 1;
}

.pw-desc-icon svg {
    width: 18px;
    height: 18px;
}

/* Condition Emoji */
.pw-desc-emoji {
    font-size: 1.2em;
}

/* Feels Like Badge */
.pw-feels-like-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: 8px;
    padding: 2px 8px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 20px;
    font-size: 11px;
    font-weight: normal;
}

.pw-feels-like-label {
    opacity: 0.7;
}

.pw-feels-like-value {
    font-weight: 600;
}

/* Vertical Layout */
.pw-description-vertical {
    flex-direction: column;
}

.pw-description-vertical .pw-description-wrapper {
    flex-direction: column;
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-desc-medium .pw-desc-text {
        font-size: 14px;
    }
    
    .pw-feels-like-badge {
        margin-left: 0;
        margin-top: 4px;
    }
}
</style>

<?php
/**
 * Helper function to get weather condition icon SVG.
 *
 * @param string $condition Weather condition description.
 * @return string
 */
if ( ! function_exists( 'get_weather_condition_icon' ) ) {
    /**
     * Get SVG icon for weather condition.
     *
     * @param string $condition Weather condition.
     * @return string
     */
    function get_weather_condition_icon( $condition ) {
        $condition_lower = strtolower( $condition );
        
        if ( strpos( $condition_lower, 'clear' ) !== false || strpos( $condition_lower, 'sunny' ) !== false ) {
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="5" fill="currentColor" stroke="currentColor" stroke-width="1"/>
                        <line x1="12" y1="2" x2="12" y2="4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="12" y1="20" x2="12" y2="22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="2" y1="12" x2="4" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="20" y1="12" x2="22" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>';
        }
        
        if ( strpos( $condition_lower, 'cloud' ) !== false ) {
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 10C18 6.686 15.314 4 12 4C8.686 4 6 6.686 6 10C6 10.548 6.07 11.08 6.2 11.59" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        <path d="M17 16C19.209 16 21 14.209 21 12C21 9.791 19.209 8 17 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        <path d="M7 16C4.791 16 3 14.209 3 12C3 9.791 4.791 8 7 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                    </svg>';
        }
        
        if ( strpos( $condition_lower, 'rain' ) !== false ) {
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 10C18 6.686 15.314 4 12 4C8.686 4 6 6.686 6 10C6 10.548 6.07 11.08 6.2 11.59" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        <path d="M8 18L9 20M12 17L13 19M16 18L17 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>';
        }
        
        if ( strpos( $condition_lower, 'snow' ) !== false ) {
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 10C18 6.686 15.314 4 12 4C8.686 4 6 6.686 6 10C6 10.548 6.07 11.08 6.2 11.59" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        <path d="M12 16L12 20M10 18L14 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>';
        }
        
        if ( strpos( $condition_lower, 'thunder' ) !== false ) {
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 10C18 6.686 15.314 4 12 4C8.686 4 6 6.686 6 10C6 10.548 6.07 11.08 6.2 11.59" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        <path d="M12 13L10 17H14L12 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>';
        }
        
        // Default icon.
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M12 8V12L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>';
    }
}

/**
 * Helper function to get weather emoji.
 *
 * @param string $condition Weather condition.
 * @return string
 */
if ( ! function_exists( 'get_weather_emoji' ) ) {
    /**
     * Get emoji for weather condition.
     *
     * @param string $condition Weather condition.
     * @return string
     */
    function get_weather_emoji( $condition ) {
        $condition_lower = strtolower( $condition );
        
        $emoji_map = array(
            'clear'     => '☀️',
            'sunny'     => '☀️',
            'cloud'     => '☁️',
            'rain'      => '🌧️',
            'drizzle'   => '🌦️',
            'thunder'   => '⛈️',
            'snow'      => '❄️',
            'mist'      => '🌫️',
            'fog'       => '🌫️',
            'haze'      => '🌫️',
            'wind'      => '💨',
            'tornado'   => '🌪️',
            'hurricane' => '🌀',
        );
        
        foreach ( $emoji_map as $key => $emoji ) {
            if ( strpos( $condition_lower, $key ) !== false ) {
                return $emoji;
            }
        }
        
        return '🌡️';
    }
}