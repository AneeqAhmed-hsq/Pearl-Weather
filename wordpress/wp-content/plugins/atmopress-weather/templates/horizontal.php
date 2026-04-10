<?php
/**
 * AtmoPress Template: Horizontal
 * A single-row compact layout ideal for headers or sidebars.
 */

use AtmoPress\TemplateLoader;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$cur   = $weather['current'];
$unit  = TemplateLoader::unit_label( $config['units'] );
$speed = TemplateLoader::speed_label( $config['units'] );
$id    = TemplateLoader::widget_id( 'ap-h' );
?>
<div id="<?php echo esc_attr( $id ); ?>"
     class="ap-widget ap-horizontal <?php echo esc_attr( $config['custom_class'] ); ?>"
     style="<?php echo esc_attr( TemplateLoader::css_vars( $config ) ); ?>"
     data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">

    <div class="ap-h-main">
        <img class="ap-weather-icon" src="<?php echo esc_url( $cur['icon'] ); ?>" alt="<?php echo esc_attr( $cur['condition'] ); ?>" width="56" height="56" />

        <div class="ap-h-center">
            <div class="ap-h-temp"><?php echo esc_html( $cur['temp'] . $unit ); ?></div>
            <div class="ap-h-desc"><?php echo esc_html( $cur['description'] ); ?></div>
        </div>

        <div class="ap-h-location">
            <div class="ap-h-city"><?php echo esc_html( $cur['city'] ); ?></div>
            <div class="ap-h-country"><?php echo esc_html( $cur['country'] ); ?></div>
        </div>

        <?php if ( $config['show_humidity'] || $config['show_wind'] ) : ?>
        <div class="ap-h-meta">
            <?php if ( $config['show_humidity'] ) : ?>
            <span>💧 <?php echo esc_html( $cur['humidity'] ); ?>%</span>
            <?php endif; ?>
            <?php if ( $config['show_wind'] ) : ?>
            <span>🌬️ <?php echo esc_html( $cur['wind_speed'] . ' ' . $speed ); ?></span>
            <?php endif; ?>
            <?php if ( $config['show_feels_like'] ) : ?>
            <span>🌡️ <?php printf( esc_html__( 'Feels %s%s', 'atmopress-weather' ), esc_html( $cur['feels_like'] ), esc_html( $unit ) ); ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="ap-h-controls">
            <?php if ( $config['show_search'] ) : ?>
            <form class="ap-search-form ap-search-form--compact" onsubmit="return false;">
                <input class="ap-search-input" type="text" placeholder="<?php esc_attr_e( 'City…', 'atmopress-weather' ); ?>" autocomplete="off" />
                <button class="ap-search-btn" type="submit">›</button>
            </form>
            <?php endif; ?>
            <?php if ( $config['show_geolocation'] ) : ?>
            <button class="ap-geo-btn" title="<?php esc_attr_e( 'Detect location', 'atmopress-weather' ); ?>">⊙</button>
            <?php endif; ?>
            <button class="ap-unit-toggle" data-unit="<?php echo esc_attr( $config['units'] ); ?>"><?php echo 'metric' === $config['units'] ? '°F' : '°C'; ?></button>
        </div>
    </div>

    <?php if ( $config['show_daily'] && ! empty( $weather['daily'] ) ) : ?>
    <div class="ap-h-forecast">
        <?php foreach ( array_slice( $weather['daily'], 0, min( 5, $config['forecast_days'] ) ) as $d ) : ?>
        <div class="ap-h-day">
            <span class="ap-h-day-name"><?php echo esc_html( $d['day_label'] ); ?></span>
            <img src="<?php echo esc_url( $d['icon'] ); ?>" alt="" width="24" height="24" />
            <span class="ap-h-day-hi"><?php echo esc_html( $d['temp_max'] . $unit ); ?></span>
            <span class="ap-h-day-lo"><?php echo esc_html( $d['temp_min'] . $unit ); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
