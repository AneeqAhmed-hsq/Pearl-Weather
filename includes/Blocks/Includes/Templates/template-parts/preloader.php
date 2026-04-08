<?php
/**
 * Preloader Template Part
 *
 * Displays an animated weather-themed preloader while weather data is loading.
 * Features rotating sun, floating cloud, and animated raindrops.
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
 * - $unique_id: Unique block identifier
 * - $block_name: Block name
 */

// Check if preloader should be shown.
$show_preloader = isset( $attributes['showPreloader'] ) ? (bool) $attributes['showPreloader'] : true;

if ( ! $show_preloader ) {
    return;
}

// Preloader settings.
$preloader_type = isset( $attributes['preloaderType'] ) ? sanitize_text_field( $attributes['preloaderType'] ) : 'default';
$preloader_color = isset( $attributes['preloaderColor'] ) ? sanitize_hex_color( $attributes['preloaderColor'] ) : '#F26C0D';
$preloader_bg_color = isset( $attributes['preloaderBgColor'] ) ? sanitize_hex_color( $attributes['preloaderBgColor'] ) : 'rgba(255, 255, 255, 0.9)';
$preloader_size = isset( $attributes['preloaderSize'] ) ? sanitize_text_field( $attributes['preloaderSize'] ) : 'medium';

// Size mapping.
$size_classes = array(
    'small'  => 'pw-preloader-small',
    'medium' => 'pw-preloader-medium',
    'large'  => 'pw-preloader-large',
);
$size_class = isset( $size_classes[ $preloader_size ] ) ? $size_classes[ $preloader_size ] : 'pw-preloader-medium';

?>

<div class="pw-weather-preloader <?php echo esc_attr( $size_class ); ?>" 
     data-preloader-id="<?php echo esc_attr( $unique_id ); ?>"
     data-preloader-type="<?php echo esc_attr( $preloader_type ); ?>"
     style="--pw-preloader-color: <?php echo esc_attr( $preloader_color ); ?>; --pw-preloader-bg: <?php echo esc_attr( $preloader_bg_color ); ?>;">
    
    <div class="pw-preloader-wrapper">
        
        <?php if ( 'default' === $preloader_type ) : ?>
            <!-- Default Weather Preloader -->
            <div class="pw-preloader-animation">
                
                <!-- Rotating Sun -->
                <div class="pw-preloader-sun-container">
                    <svg class="pw-preloader-sun" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="5" fill="currentColor" stroke="currentColor" stroke-width="1"/>
                        <line x1="12" y1="2" x2="12" y2="4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="12" y1="20" x2="12" y2="22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="2" y1="12" x2="4" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="20" y1="12" x2="22" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="4.929" y1="4.929" x2="6.343" y2="6.343" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="17.657" y1="17.657" x2="19.071" y2="19.071" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="19.071" y1="4.929" x2="17.657" y2="6.343" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="6.343" y1="17.657" x2="4.929" y2="19.071" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                
                <!-- Floating Cloud -->
                <div class="pw-preloader-cloud-container">
                    <svg class="pw-preloader-cloud" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 10C18 6.686 15.314 4 12 4C8.686 4 6 6.686 6 10C6 10.548 6.07 11.08 6.2 11.59" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        <path d="M17 16C19.209 16 21 14.209 21 12C21 9.791 19.209 8 17 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        <path d="M7 16C4.791 16 3 14.209 3 12C3 9.791 4.791 8 7 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        <path d="M8 16H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                
                <!-- Animated Raindrops -->
                <div class="pw-preloader-rain">
                    <?php for ( $i = 1; $i <= 12; $i++ ) : ?>
                        <span class="pw-preloader-drop" style="animation-delay: -<?php echo esc_attr( $i * 0.12 ); ?>s;"></span>
                    <?php endfor; ?>
                </div>
                
            </div>
            
        <?php elseif ( 'simple' === $preloader_type ) : ?>
            <!-- Simple Spinner Preloader -->
            <div class="pw-preloader-simple">
                <div class="pw-spinner"></div>
                <span class="pw-loading-text"><?php esc_html_e( 'Loading weather data...', 'pearl-weather' ); ?></span>
            </div>
            
        <?php elseif ( 'pulse' === $preloader_type ) : ?>
            <!-- Pulse Animation Preloader -->
            <div class="pw-preloader-pulse">
                <div class="pw-pulse-ring"></div>
                <div class="pw-pulse-ring"></div>
                <div class="pw-pulse-ring"></div>
                <svg class="pw-pulse-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                </svg>
            </div>
            
        <?php elseif ( 'skeleton' === $preloader_type ) : ?>
            <!-- Skeleton Screen Preloader -->
            <div class="pw-preloader-skeleton">
                <div class="pw-skeleton-header">
                    <div class="pw-skeleton-line pw-skeleton-title"></div>
                    <div class="pw-skeleton-line pw-skeleton-subtitle"></div>
                </div>
                <div class="pw-skeleton-body">
                    <div class="pw-skeleton-icon"></div>
                    <div class="pw-skeleton-temp"></div>
                </div>
                <div class="pw-skeleton-footer">
                    <?php for ( $i = 0; $i < 4; $i++ ) : ?>
                        <div class="pw-skeleton-card"></div>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
    
</div>

<style>
/* Preloader Styles */
.pw-weather-preloader {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--pw-preloader-bg, rgba(255, 255, 255, 0.95));
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    border-radius: inherit;
}

/* Size variants */
.pw-preloader-small .pw-preloader-wrapper {
    width: 60px;
    height: 60px;
}

.pw-preloader-medium .pw-preloader-wrapper {
    width: 100px;
    height: 100px;
}

.pw-preloader-large .pw-preloader-wrapper {
    width: 140px;
    height: 140px;
}

/* Animation Container */
.pw-preloader-animation {
    position: relative;
    width: 100%;
    height: 100%;
}

/* Sun Animation */
.pw-preloader-sun-container {
    position: absolute;
    top: 15%;
    left: 15%;
    width: 40%;
    height: 40%;
}

.pw-preloader-sun {
    width: 100%;
    height: 100%;
    color: var(--pw-preloader-color, #f26c0d);
    animation: pw-sun-rotate 8s linear infinite;
}

@keyframes pw-sun-rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Cloud Animation */
.pw-preloader-cloud-container {
    position: absolute;
    bottom: 25%;
    right: 10%;
    width: 50%;
    height: 35%;
}

.pw-preloader-cloud {
    width: 100%;
    height: 100%;
    color: var(--pw-preloader-color, #f26c0d);
    animation: pw-cloud-float 3s ease-in-out infinite;
}

@keyframes pw-cloud-float {
    0%, 100% { transform: translateX(0) translateY(0); }
    50% { transform: translateX(5px) translateY(-3px); }
}

/* Raindrops */
.pw-preloader-rain {
    position: absolute;
    bottom: 20%;
    left: 30%;
    right: 30%;
    display: flex;
    justify-content: space-around;
}

.pw-preloader-drop {
    width: 2px;
    height: 8px;
    background: var(--pw-preloader-color, #f26c0d);
    border-radius: 0 0 2px 2px;
    animation: pw-drop-fall 1.2s linear infinite;
    opacity: 0;
}

@keyframes pw-drop-fall {
    0% {
        transform: translateY(-20px);
        opacity: 0;
    }
    30% {
        opacity: 1;
    }
    70% {
        opacity: 1;
    }
    100% {
        transform: translateY(30px);
        opacity: 0;
    }
}

/* Simple Spinner Preloader */
.pw-preloader-simple {
    text-align: center;
}

.pw-spinner {
    width: 40px;
    height: 40px;
    margin: 0 auto 12px;
    border: 3px solid rgba(0, 0, 0, 0.1);
    border-top-color: var(--pw-preloader-color, #f26c0d);
    border-radius: 50%;
    animation: pw-spin 0.8s linear infinite;
}

@keyframes pw-spin {
    to { transform: rotate(360deg); }
}

.pw-loading-text {
    font-size: 12px;
    color: #757575;
}

/* Pulse Preloader */
.pw-preloader-pulse {
    position: relative;
    width: 60px;
    height: 60px;
    margin: 0 auto;
}

.pw-pulse-ring {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 2px solid var(--pw-preloader-color, #f26c0d);
    border-radius: 50%;
    animation: pw-pulse 1.5s ease-out infinite;
}

.pw-pulse-ring:nth-child(1) {
    animation-delay: 0s;
}

.pw-pulse-ring:nth-child(2) {
    animation-delay: 0.5s;
}

.pw-pulse-ring:nth-child(3) {
    animation-delay: 1s;
}

.pw-pulse-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 24px;
    height: 24px;
    transform: translate(-50%, -50%);
    color: var(--pw-preloader-color, #f26c0d);
}

@keyframes pw-pulse {
    0% {
        transform: scale(0.5);
        opacity: 1;
    }
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* Skeleton Screen Preloader */
.pw-preloader-skeleton {
    width: 100%;
    padding: 20px;
}

.pw-skeleton-header {
    margin-bottom: 20px;
}

.pw-skeleton-line {
    background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%);
    background-size: 200% 100%;
    border-radius: 4px;
    animation: pw-skeleton-loading 1.5s infinite;
}

.pw-skeleton-title {
    width: 60%;
    height: 24px;
    margin-bottom: 8px;
}

.pw-skeleton-subtitle {
    width: 40%;
    height: 16px;
}

.pw-skeleton-body {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-bottom: 20px;
}

.pw-skeleton-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%);
    background-size: 200% 100%;
    animation: pw-skeleton-loading 1.5s infinite;
}

.pw-skeleton-temp {
    width: 80px;
    height: 40px;
    border-radius: 4px;
    background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%);
    background-size: 200% 100%;
    animation: pw-skeleton-loading 1.5s infinite;
}

.pw-skeleton-footer {
    display: flex;
    gap: 12px;
    justify-content: space-between;
}

.pw-skeleton-card {
    flex: 1;
    height: 80px;
    border-radius: 8px;
    background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%);
    background-size: 200% 100%;
    animation: pw-skeleton-loading 1.5s infinite;
}

@keyframes pw-skeleton-loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* Fade out animation */
.pw-weather-preloader.fade-out {
    animation: pw-fade-out 0.5s ease forwards;
}

@keyframes pw-fade-out {
    to {
        opacity: 0;
        visibility: hidden;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .pw-preloader-medium .pw-preloader-wrapper {
        width: 70px;
        height: 70px;
    }
}
</style>

<script>
// Preloader fade out functionality
(function() {
    const preloader = document.querySelector('.pw-weather-preloader[data-preloader-id="<?php echo esc_js( $unique_id ); ?>"]');
    if (!preloader) return;
    
    const wrapper = document.querySelector('#<?php echo esc_js( $unique_id ); ?> .pw-weather-template-wrapper');
    
    if (wrapper) {
        wrapper.style.opacity = '0';
        wrapper.style.transition = 'opacity 0.5s ease';
        
        const removePreloader = function() {
            wrapper.style.opacity = '1';
            preloader.classList.add('fade-out');
            setTimeout(function() {
                if (preloader.parentNode) {
                    preloader.remove();
                }
            }, 500);
        };
        
        // Check if weather data is already loaded
        if (wrapper.classList.contains('pw-data-loaded')) {
            removePreloader();
        } else {
            // Wait for weather data to load
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class' && wrapper.classList.contains('pw-data-loaded')) {
                        removePreloader();
                        observer.disconnect();
                    }
                });
            });
            observer.observe(wrapper, { attributes: true });
            
            // Fallback timeout (5 seconds max)
            setTimeout(function() {
                removePreloader();
                observer.disconnect();
            }, 5000);
        }
    }
})();
</script>