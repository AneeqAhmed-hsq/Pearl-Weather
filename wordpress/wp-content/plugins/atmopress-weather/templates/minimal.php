<?php
/**
 * AtmoPress Template: Minimal
 * A super-compact, text-first weather display.
 */

use AtmoPress\TemplateLoader;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$cur  = $weather['current'];
$unit = TemplateLoader::unit_label( $config['units'] );
$speed = TemplateLoader::speed_label( $config['units'] );
$id   = TemplateLoader::widget_id( 'ap-minimal' );
?>
<div id="<?php echo esc_attr( $id ); ?>"
     class="ap-widget ap-minimal <?php echo esc_attr( $config['custom_class'] ); ?>"
     style="<?php echo esc_attr( TemplateLoader::css_vars( $config ) ); ?>"
     data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">

    <?php if ( $config['show_search'] ) : ?>
    <form class="ap-search-form ap-search-form--inline" onsubmit="return false;">
        <input class="ap-search-input" type="text" placeholder="<?php esc_attr_e( 'City…', 'atmopress-weather' ); ?>" value="<?php echo esc_attr( $cur['city'] ); ?>" autocomplete="off" />
        <button class="ap-search-btn" type="submit">›</button>
        <?php if ( $config['show_geolocation'] ) : ?>
        <button class="ap-geo-btn" type="button" title="<?php esc_attr_e( 'Detect', 'atmopress-weather' ); ?>">⊙</button>
        <?php endif; ?>
    </form>
    <?php endif; ?>

    <div class="ap-minimal-body">
        <img class="ap-weather-icon ap-weather-icon--sm" src="<?php echo esc_url( $cur['icon'] ); ?>" alt="<?php echo esc_attr( $cur['condition'] ); ?>" width="48" height="48" />
        <div class="ap-minimal-info">
            <div class="ap-minimal-temp">
                <?php echo esc_html( $cur['temp'] . $unit ); ?>
                <button class="ap-unit-toggle" data-unit="<?php echo esc_attr( $config['units'] ); ?>"><?php echo 'metric' === $config['units'] ? '°F' : '°C'; ?></button>
            </div>
            <div class="ap-minimal-location"><?php echo esc_html( $cur['city'] . ', ' . $cur['country'] ); ?></div>
            <div class="ap-minimal-desc"><?php echo esc_html( $cur['description'] ); ?></div>
        </div>
    </div>

    <div class="ap-minimal-meta">
        <?php if ( $config['show_humidity'] ) : ?>
        <span>💧 <?php echo esc_html( $cur['humidity'] ); ?>%</span>
        <?php endif; ?>
        <?php if ( $config['show_wind'] ) : ?>
        <span>💨 <?php echo esc_html( $cur['wind_speed'] . ' ' . $speed ); ?></span>
        <?php endif; ?>
        <?php if ( $config['show_feels_like'] ) : ?>
        <span><?php printf( esc_html__( 'Feels %s%s', 'atmopress-weather' ), esc_html( $cur['feels_like'] ), esc_html( $unit ) ); ?></span>
        <?php endif; ?>
    </div>

    <?php if ( $config['show_daily'] && ! empty( $weather['daily'] ) ) : ?>
    <div class="ap-minimal-forecast">
        <?php foreach ( array_slice( $weather['daily'], 0, min( 5, $config['forecast_days'] ) ) as $d ) : ?>
        <span class="ap-minimal-day">
            <b><?php echo esc_html( $d['day_label'] ); ?></b>
            <img src="<?php echo esc_url( $d['icon'] ); ?>" alt="" width="20" height="20" />
            <?php echo esc_html( $d['temp_max'] . '/' . $d['temp_min'] . $unit ); ?>
        </span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
