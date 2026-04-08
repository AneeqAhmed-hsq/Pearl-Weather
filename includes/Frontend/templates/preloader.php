<?php
/**
 * Preloader Template (Legacy)
 *
 * Displays a preloader image while weather data is loading.
 * This template can be overridden by themes.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Templates
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template variables:
 * - $shortcode_id: Shortcode/widget ID
 * - $splw_meta: Widget meta settings
 * - $is_admin: Whether in admin context
 */

// Check if preloader is enabled.
$show_preloader = isset( $splw_meta['lw-preloader'] ) ? (bool) $splw_meta['lw-preloader'] : true;
$preloader_class = $show_preloader ? 'pw-preloader-wrapper' : '';

// Don't show preloader in admin area.
if ( ! $show_preloader || is_admin() || ( isset( $is_admin ) && $is_admin ) ) {
    return;
}

// Preloader image URL.
$preloader_image = isset( $splw_meta['lw-preloader-image'] ) 
    ? esc_url( $splw_meta['lw-preloader-image'] ) 
    : PEARL_WEATHER_ASSETS_URL . 'images/spinner.svg';

// Preloader settings.
$preloader_width = isset( $splw_meta['lw-preloader-width'] ) ? (int) $splw_meta['lw-preloader-width'] : 50;
$preloader_height = isset( $splw_meta['lw-preloader-height'] ) ? (int) $splw_meta['lw-preloader-height'] : 50;
$preloader_bg = isset( $splw_meta['lw-preloader-bg'] ) ? sanitize_hex_color( $splw_meta['lw-preloader-bg'] ) : 'rgba(255, 255, 255, 0.9)';
$preloader_z_index = isset( $splw_meta['lw-preloader-z-index'] ) ? (int) $splw_meta['lw-preloader-z-index'] : 9999;

// CSS classes.
$wrapper_classes = array( 'pw-preloader', 'pw-preloader-legacy' );

if ( ! empty( $preloader_class ) ) {
    $wrapper_classes[] = $preloader_class;
}

// Inline styles.
$inline_styles = array(
    '--pw-preloader-bg: ' . $preloader_bg,
    '--pw-preloader-z-index: ' . $preloader_z_index,
);

?>

<div id="pw-preloader-<?php echo esc_attr( $shortcode_id ); ?>" 
     class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
     style="<?php echo esc_attr( implode( '; ', $inline_styles ) ); ?>">
    
    <div class="pw-preloader-inner">
        <img src="<?php echo esc_url( $preloader_image ); ?>" 
             class="pw-preloader-image skip-lazy" 
             alt="<?php esc_attr_e( 'Loading weather data...', 'pearl-weather' ); ?>"
             width="<?php echo esc_attr( $preloader_width ); ?>"
             height="<?php echo esc_attr( $preloader_height ); ?>"
             loading="eager" />
    </div>
    
</div>

<style>
/* Legacy Preloader Styles */
.pw-preloader-legacy {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--pw-preloader-bg, rgba(255, 255, 255, 0.95));
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: var(--pw-preloader-z-index, 9999);
    border-radius: inherit;
}

.pw-preloader-legacy .pw-preloader-inner {
    text-align: center;
}

.pw-preloader-legacy .pw-preloader-image {
    max-width: 100%;
    height: auto;
    animation: pw-preloader-pulse 1.5s ease-in-out infinite;
}

@keyframes pw-preloader-pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.7;
        transform: scale(0.95);
    }
}

/* Fade out animation */
.pw-preloader-legacy.pw-fade-out {
    animation: pw-fade-out 0.4s ease forwards;
}

@keyframes pw-fade-out {
    to {
        opacity: 0;
        visibility: hidden;
    }
}

/* Reduced Motion Preference */
@media (prefers-reduced-motion: reduce) {
    .pw-preloader-legacy .pw-preloader-image {
        animation: none;
    }
}
</style>

<script>
/**
 * Preloader fade out functionality.
 */
(function() {
    const preloader = document.getElementById('pw-preloader-<?php echo esc_js( $shortcode_id ); ?>');
    if (!preloader) return;
    
    const widget = document.getElementById('pw-weather-<?php echo esc_js( $shortcode_id ); ?>');
    const card = widget ? widget.querySelector('.pw-weather-card') : null;
    
    if (card) {
        card.style.opacity = '0';
        card.style.transition = 'opacity 0.4s ease';
        
        const removePreloader = function() {
            card.style.opacity = '1';
            preloader.classList.add('pw-fade-out');
            setTimeout(function() {
                if (preloader.parentNode) {
                    preloader.remove();
                }
            }, 500);
        };
        
        // Check if data is already loaded
        if (widget.classList.contains('pw-data-loaded')) {
            removePreloader();
        } else {
            // Wait for data to load
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class' && widget.classList.contains('pw-data-loaded')) {
                        removePreloader();
                        observer.disconnect();
                    }
                });
            });
            observer.observe(widget, { attributes: true });
            
            // Fallback timeout
            setTimeout(removePreloader, 8000);
        }
    }
})();
</script>