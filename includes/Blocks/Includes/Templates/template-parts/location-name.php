<?php
/**
 * Location Name Template Part
 *
 * Displays the weather location name with optional icon, custom name support,
 * and various display formats (full, short, city-only, etc.).
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

// Check if location name should be displayed.
$show_location = isset( $attributes['showLocationName'] ) ? (bool) $attributes['showLocationName'] : true;

if ( ! $show_location ) {
    return;
}

// Get location data.
$city = isset( $weather_data['city'] ) ? $weather_data['city'] : '';
$country = isset( $weather_data['country'] ) ? $weather_data['country'] : '';
$city_id = isset( $weather_data['city_id'] ) ? $weather_data['city_id'] : '';

// Custom city name override.
$custom_city_name = isset( $attributes['customCityName'] ) ? sanitize_text_field( $attributes['customCityName'] ) : '';

// Search method (for determining display format).
$search_weather_by = isset( $attributes['searchWeatherBy'] ) ? sanitize_text_field( $attributes['searchWeatherBy'] ) : 'city_name';

// Display format options.
$display_format = isset( $attributes['locationDisplayFormat'] ) ? sanitize_text_field( $attributes['locationDisplayFormat'] ) : 'full';
$show_icon = isset( $attributes['showLocationIcon'] ) ? (bool) $attributes['showLocationIcon'] : true;
$show_country = isset( $attributes['showCountryCode'] ) ? (bool) $attributes['showCountryCode'] : true;
$show_link = isset( $attributes['linkLocationToMap'] ) ? (bool) $attributes['linkLocationToMap'] : false;

// Determine if icon should be shown based on template context.
$templates_with_icon = array( 'vertical-three', 'horizontal-one', 'horizontal-two', 'grid-card' );
$blocks_with_icon = array( 'horizontal', 'grid' );

$should_show_icon = $show_icon && (
    in_array( $template, $templates_with_icon, true ) || 
    in_array( $block_name, $blocks_with_icon, true )
);

// Format the location name based on display format.
$location_name = '';

if ( ! empty( $custom_city_name ) ) {
    // Use custom city name.
    $location_name = $custom_city_name;
} else {
    // Build from weather data.
    switch ( $display_format ) {
        case 'city_only':
            $location_name = $city;
            break;
        case 'country_only':
            $location_name = $country;
            break;
        case 'city_country':
            $location_name = ! empty( $city ) && ! empty( $country ) ? $city . ', ' . $country : $city . $country;
            break;
        case 'country_city':
            $location_name = ! empty( $country ) && ! empty( $city ) ? $country . ', ' . $city : $city . $country;
            break;
        case 'full':
        default:
            $location_name = ! empty( $city ) && ! empty( $country ) ? $city . ', ' . $country : $city . $country;
            break;
    }
}

// If both city and country are empty, show fallback.
if ( empty( $location_name ) ) {
    $location_name = __( 'Location not set', 'pearl-weather' );
}

// Map link URL (for detailed location view).
$map_link = '';
if ( $show_link && ! empty( $city_id ) ) {
    $map_link = 'https://openweathermap.org/city/' . $city_id;
} elseif ( $show_link && ! empty( $city ) && ! empty( $country ) ) {
    $map_link = 'https://www.google.com/maps/search/' . urlencode( $city . ', ' . $country );
}

// Additional CSS classes.
$wrapper_classes = array( 'pw-location-name' );

if ( $should_show_icon ) {
    $wrapper_classes[] = 'pw-has-icon';
}

if ( ! empty( $attributes['locationCustomClass'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['locationCustomClass'] );
}

$icon_position = isset( $attributes['locationIconPosition'] ) ? sanitize_text_field( $attributes['locationIconPosition'] ) : 'left';

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
     data-icon-position="<?php echo esc_attr( $icon_position ); ?>">
    
    <!-- Location Icon (left position) -->
    <?php if ( $should_show_icon && 'left' === $icon_position ) : ?>
        <span class="pw-location-icon" aria-hidden="true">
            <?php echo get_location_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </span>
    <?php endif; ?>
    
    <!-- Location Name (with optional link) -->
    <?php if ( $show_link && ! empty( $map_link ) ) : ?>
        <a href="<?php echo esc_url( $map_link ); ?>" 
           class="pw-location-link"
           target="_blank" 
           rel="noopener noreferrer"
           aria-label="<?php echo esc_attr( sprintf( __( 'View map for %s', 'pearl-weather' ), $location_name ) ); ?>">
            <span class="pw-location-text"><?php echo esc_html( $location_name ); ?></span>
        </a>
    <?php else : ?>
        <span class="pw-location-text"><?php echo esc_html( $location_name ); ?></span>
    <?php endif; ?>
    
    <!-- Location Icon (right position) -->
    <?php if ( $should_show_icon && 'right' === $icon_position ) : ?>
        <span class="pw-location-icon pw-icon-right" aria-hidden="true">
            <?php echo get_location_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </span>
    <?php endif; ?>
    
    <!-- Optional: Badge for custom location -->
    <?php if ( ! empty( $custom_city_name ) && isset( $attributes['showCustomBadge'] ) && $attributes['showCustomBadge'] ) : ?>
        <span class="pw-custom-badge" title="<?php esc_attr_e( 'Custom location name', 'pearl-weather' ); ?>">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L15 8.5L22 9.5L17 14L18.5 21L12 17.5L5.5 21L7 14L2 9.5L9 8.5L12 2Z" fill="currentColor"/>
            </svg>
        </span>
    <?php endif; ?>
    
</div>

<style>
/* Location Name Styles */
.pw-location-name {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 16px;
    font-weight: 500;
}

/* With icon */
.pw-location-name.pw-has-icon {
    display: inline-flex;
}

/* Icon positioning */
[data-icon-position="left"] {
    flex-direction: row;
}

[data-icon-position="right"] {
    flex-direction: row-reverse;
}

/* Icon styles */
.pw-location-icon {
    display: inline-flex;
    align-items: center;
    line-height: 1;
}

.pw-location-icon svg {
    width: 16px;
    height: 16px;
}

/* Link styles */
.pw-location-link {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
}

.pw-location-link:hover {
    color: var(--pw-primary-color, #f26c0d);
    text-decoration: underline;
}

/* Location text */
.pw-location-text {
    font-weight: inherit;
}

/* Custom badge */
.pw-custom-badge {
    display: inline-flex;
    align-items: center;
    margin-left: 4px;
    color: #f39c12;
    cursor: help;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-location-name {
        font-size: 14px;
        gap: 4px;
    }
    
    .pw-location-icon svg {
        width: 14px;
        height: 14px;
    }
}

/* Animation on hover (optional) */
.pw-location-link:hover .pw-location-icon svg {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}
</style>

<?php
/**
 * Helper function to get location icon SVG.
 *
 * @return string
 */
if ( ! function_exists( 'get_location_icon_svg' ) ) {
    /**
     * Get location icon SVG markup.
     *
     * @param string $type Icon type ('pin', 'marker', 'dot').
     * @return string
     */
    function get_location_icon_svg( $type = 'pin' ) {
        switch ( $type ) {
            case 'marker':
                return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="3" fill="currentColor"/>
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        </svg>';
            case 'dot':
                return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="4" fill="currentColor"/>
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        </svg>';
            case 'pin':
            default:
                return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        </svg>';
        }
    }
}

/**
 * Helper function to get country flag emoji or SVG.
 *
 * @param string $country_code ISO country code.
 * @return string
 */
if ( ! function_exists( 'get_country_flag' ) ) {
    /**
     * Get country flag (emoji or SVG).
     *
     * @param string $country_code 2-letter ISO country code.
     * @return string
     */
    function get_country_flag( $country_code ) {
        if ( empty( $country_code ) || strlen( $country_code ) !== 2 ) {
            return '';
        }
        
        // Convert country code to regional indicator symbols.
        $flag = '';
        for ( $i = 0; $i < 2; $i++ ) {
            $flag .= mb_chr( 127397 + ord( $country_code[ $i ] ), 'UTF-8' );
        }
        
        return $flag;
    }
}

/**
 * JavaScript for dynamic location detection (optional).
 * This allows the location to update based on user's geolocation.
 */
if ( isset( $attributes['enableGeolocation'] ) && $attributes['enableGeolocation'] ) : ?>
<script>
(function() {
    const locationElement = document.querySelector('.pw-location-text');
    if (!locationElement) return;
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            // Fetch city name from coordinates via reverse geocoding.
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data.address && data.address.city) {
                        locationElement.textContent = data.address.city;
                        if (data.address.country_code) {
                            locationElement.textContent += ', ' + data.address.country_code.toUpperCase();
                        }
                    }
                })
                .catch(console.error);
        });
    }
})();
</script>
<?php endif;