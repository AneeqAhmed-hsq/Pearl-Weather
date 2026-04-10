<?php
/**
 * AtmoPress Template: Card
 * A clean card layout with current weather, hourly strip, and daily forecast.
 *
 * Available variables:
 * @var array  $weather   Normalized weather data (current, hourly, daily).
 * @var array  $config    Widget configuration (show_*, colors, etc.).
 */

use AtmoPress\TemplateLoader;

if ( ! defined( 'ABSPATH' ) ) { exit; }

$cur    = $weather['current'];
$hourly = $weather['hourly'];
$daily  = $weather['daily'];
$unit   = TemplateLoader::unit_label( $config['units'] );
$speed  = TemplateLoader::speed_label( $config['units'] );
$days   = (int) $config['forecast_days'];
$hours  = (int) $config['hourly_count'];
$id     = TemplateLoader::widget_id( 'ap-card' );
?>
<div id="<?php echo esc_attr( $id ); ?>"
     class="ap-widget ap-card <?php echo esc_attr( $config['custom_class'] ); ?>"
     style="<?php echo esc_attr( TemplateLoader::css_vars( $config ) ); ?>"
     data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">

    <?php if ( $config['show_search'] || $config['show_geolocation'] ) : ?>
    <div class="ap-search-bar">
        <?php if ( $config['show_search'] ) : ?>
        <form class="ap-search-form" onsubmit="return false;">
            <input class="ap-search-input" type="text" placeholder="<?php esc_attr_e( 'Search city…', 'atmopress-weather' ); ?>" value="<?php echo esc_attr( $cur['city'] . ', ' . $cur['country'] ); ?>" autocomplete="off" />
            <button class="ap-search-btn" type="submit" aria-label="<?php esc_attr_e( 'Search', 'atmopress-weather' ); ?>">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
        </form>
        <?php endif; ?>
        <?php if ( $config['show_geolocation'] ) : ?>
        <button class="ap-geo-btn" title="<?php esc_attr_e( 'Use my location', 'atmopress-weather' ); ?>">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3"/><circle cx="12" cy="12" r="8" stroke-dasharray="3 3"/></svg>
        </button>
        <?php endif; ?>
        <button class="ap-unit-toggle" data-unit="<?php echo esc_attr( $config['units'] ); ?>"><?php echo 'metric' === $config['units'] ? '°F' : '°C'; ?></button>
    </div>
    <?php endif; ?>

    <div class="ap-card-main">
        <div class="ap-location">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" class="ap-pin"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
            <span class="ap-city"><?php echo esc_html( $cur['city'] ); ?></span>
            <span class="ap-country"><?php echo esc_html( $cur['country'] ); ?></span>
        </div>

        <div class="ap-current">
            <img class="ap-weather-icon" src="<?php echo esc_url( $cur['icon'] ); ?>" alt="<?php echo esc_attr( $cur['condition'] ); ?>" width="80" height="80" />
            <div class="ap-temp-block">
                <span class="ap-temp"><?php echo esc_html( $cur['temp'] ); ?><sup><?php echo esc_html( $unit ); ?></sup></span>
                <span class="ap-description"><?php echo esc_html( $cur['description'] ); ?></span>
                <?php if ( $config['show_feels_like'] ) : ?>
                <span class="ap-feels"><?php printf( esc_html__( 'Feels like %s%s', 'atmopress-weather' ), esc_html( $cur['feels_like'] ), esc_html( $unit ) ); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="ap-temp-range">
            <span class="ap-hi"><?php echo esc_html( $cur['temp_max'] . $unit ); ?></span>
            <span class="ap-sep">·</span>
            <span class="ap-lo"><?php echo esc_html( $cur['temp_min'] . $unit ); ?></span>
        </div>
    </div>

    <?php if ( $config['show_humidity'] || $config['show_wind'] || $config['show_pressure'] || $config['show_visibility'] ) : ?>
    <div class="ap-stats">
        <?php if ( $config['show_humidity'] ) : ?>
        <div class="ap-stat">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/></svg>
            <span class="ap-stat-val"><?php echo esc_html( $cur['humidity'] ); ?>%</span>
            <span class="ap-stat-lbl"><?php esc_html_e( 'Humidity', 'atmopress-weather' ); ?></span>
        </div>
        <?php endif; ?>
        <?php if ( $config['show_wind'] ) : ?>
        <div class="ap-stat">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.59 4.59A2 2 0 1 1 11 8H2m10.59 11.41A2 2 0 1 0 14 16H2m15.73-8.27A2.5 2.5 0 1 1 19.5 12H2"/></svg>
            <span class="ap-stat-val"><?php echo esc_html( $cur['wind_speed'] . ' ' . $speed ); ?></span>
            <span class="ap-stat-lbl"><?php printf( esc_html__( 'Wind %s', 'atmopress-weather' ), esc_html( TemplateLoader::wind_direction( $cur['wind_deg'] ) ) ); ?></span>
        </div>
        <?php endif; ?>
        <?php if ( $config['show_pressure'] ) : ?>
        <div class="ap-stat">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span class="ap-stat-val"><?php echo esc_html( $cur['pressure'] ); ?> hPa</span>
            <span class="ap-stat-lbl"><?php esc_html_e( 'Pressure', 'atmopress-weather' ); ?></span>
        </div>
        <?php endif; ?>
        <?php if ( $config['show_visibility'] && null !== $cur['visibility'] ) : ?>
        <div class="ap-stat">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <span class="ap-stat-val"><?php echo esc_html( $cur['visibility'] ); ?> km</span>
            <span class="ap-stat-lbl"><?php esc_html_e( 'Visibility', 'atmopress-weather' ); ?></span>
        </div>
        <?php endif; ?>
        <?php if ( $config['show_sunrise'] && $cur['sunrise'] ) : ?>
        <div class="ap-stat">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 18a5 5 0 0 0-10 0"/><line x1="12" y1="2" x2="12" y2="9"/><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/><line x1="1" y1="18" x2="3" y2="18"/><line x1="21" y1="18" x2="23" y2="18"/><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/></svg>
            <span class="ap-stat-val"><?php echo esc_html( TemplateLoader::format_time( $cur['sunrise'], $cur['timezone'] ) ); ?></span>
            <span class="ap-stat-lbl"><?php esc_html_e( 'Sunrise', 'atmopress-weather' ); ?></span>
        </div>
        <div class="ap-stat">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 18a5 5 0 0 0-10 0"/><line x1="12" y1="9" x2="12" y2="2"/><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/><line x1="1" y1="18" x2="3" y2="18"/><line x1="21" y1="18" x2="23" y2="18"/><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/></svg>
            <span class="ap-stat-val"><?php echo esc_html( TemplateLoader::format_time( $cur['sunset'], $cur['timezone'] ) ); ?></span>
            <span class="ap-stat-lbl"><?php esc_html_e( 'Sunset', 'atmopress-weather' ); ?></span>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ( $config['show_hourly'] && ! empty( $hourly ) ) : ?>
    <div class="ap-section-title"><?php esc_html_e( 'Hourly', 'atmopress-weather' ); ?></div>
    <div class="ap-hourly-strip">
        <?php foreach ( array_slice( $hourly, 0, $hours ) as $h ) : ?>
        <div class="ap-hourly-item">
            <span class="ap-hourly-time"><?php echo esc_html( $h['time'] ); ?></span>
            <img src="<?php echo esc_url( $h['icon'] ); ?>" alt="<?php echo esc_attr( $h['condition'] ); ?>" width="32" height="32" />
            <span class="ap-hourly-temp"><?php echo esc_html( $h['temp'] . $unit ); ?></span>
            <?php if ( $h['pop'] > 0 ) : ?>
            <span class="ap-hourly-pop">💧<?php echo esc_html( $h['pop'] ); ?>%</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ( $config['show_daily'] && ! empty( $daily ) ) : ?>
    <div class="ap-section-title"><?php esc_html_e( 'Forecast', 'atmopress-weather' ); ?></div>
    <div class="ap-daily">
        <?php foreach ( array_slice( $daily, 0, $days ) as $d ) : ?>
        <div class="ap-daily-row">
            <span class="ap-daily-day"><?php echo esc_html( $d['day_label'] ); ?></span>
            <img src="<?php echo esc_url( $d['icon'] ); ?>" alt="<?php echo esc_attr( $d['condition'] ); ?>" width="28" height="28" />
            <span class="ap-daily-desc"><?php echo esc_html( $d['description'] ); ?></span>
            <span class="ap-daily-range">
                <span class="ap-hi"><?php echo esc_html( $d['temp_max'] . $unit ); ?></span>
                <span class="ap-sep">/</span>
                <span class="ap-lo"><?php echo esc_html( $d['temp_min'] . $unit ); ?></span>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
