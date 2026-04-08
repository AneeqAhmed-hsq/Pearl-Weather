<?php
/**
 * Weather Section Title Template
 *
 * Displays the weather widget section title.
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
 * - $show_weather_title: Whether to show the title
 * - $attributes: Widget attributes/settings
 */

// Check if title should be displayed.
$show_title = isset( $show_weather_title ) ? (bool) $show_weather_title : true;

if ( ! $show_title ) {
    return;
}

// Get the widget title.
$widget_title = get_the_title( $shortcode_id );

// Allow custom title from settings.
if ( isset( $attributes['custom_widget_title'] ) && ! empty( $attributes['custom_widget_title'] ) ) {
    $widget_title = sanitize_text_field( $attributes['custom_widget_title'] );
}

// If no title, don't display anything.
if ( empty( $widget_title ) ) {
    return;
}

// Title settings.
$title_tag = isset( $attributes['title_tag'] ) ? sanitize_text_field( $attributes['title_tag'] ) : 'div';
$title_alignment = isset( $attributes['title_alignment'] ) ? sanitize_text_field( $attributes['title_alignment'] ) : 'center';
$show_icon = isset( $attributes['show_title_icon'] ) ? (bool) $attributes['show_title_icon'] : true;

// CSS classes.
$wrapper_classes = array( 'pw-widget-title' );
$wrapper_classes[] = 'pw-title-align-' . $title_alignment;

if ( ! empty( $attributes['title_custom_class'] ) ) {
    $wrapper_classes[] = sanitize_html_class( $attributes['title_custom_class'] );
}

// Title icon.
$title_icon = '';
if ( $show_icon ) {
    $title_icon = '<span class="pw-title-icon" aria-hidden="true">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="5" fill="currentColor"/>
            <path d="M12 2L12 4M12 20L12 22M2 12L4 12M20 12L22 12M4.929 4.929L6.343 6.343M17.657 17.657L19.071 19.071M19.071 4.929L17.657 6.343M6.343 17.657L4.929 19.071" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
    </span>';
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
    <<?php echo esc_attr( $title_tag ); ?> class="pw-title">
        <?php if ( $show_icon ) : ?>
            <?php echo $title_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php endif; ?>
        <span class="pw-title-text"><?php echo esc_html( $widget_title ); ?></span>
    </<?php echo esc_attr( $title_tag ); ?>>
</div>

<style>
/* Widget Title Styles */
.pw-widget-title {
    margin-bottom: 16px;
}

.pw-title {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Title Alignment */
.pw-title-align-left .pw-title {
    justify-content: flex-start;
}

.pw-title-align-center .pw-title {
    justify-content: center;
}

.pw-title-align-right .pw-title {
    justify-content: flex-end;
}

/* Title Icon */
.pw-title-icon {
    display: inline-flex;
    align-items: center;
    color: var(--pw-primary-color, #f26c0d);
}

.pw-title-icon svg {
    width: 20px;
    height: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .pw-title {
        font-size: 18px;
    }
}
</style>