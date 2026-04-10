<?php
/**
 * AtmoPress Template: Grid
 * A grid-based multi-stat layout with prominent weather data cells.
 */

use AtmoPress\TemplateLoader;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$cur   = $weather['current'];
$daily = $weather['daily'];
$unit  = TemplateLoader::unit_label( $config['units'] );
$speed = TemplateLoader::speed_label( $config['units'] );
$id    = TemplateLoader::widget_id( 'ap-grid' );
?>
<div id="<?php echo esc_attr( $id ); ?>"
     class="ap-widget ap-grid-layout <?php echo esc_attr( $config['custom_class'] ); ?>"
     style="<?php echo esc_attr( TemplateLoader::css_vars( $config ) ); ?>"
     data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">

    <?php if ( $config['show_search'] || $config['show_geolocation'] ) : ?>
    <div class="ap-search-bar">
        <?php if ( $config['show_search'] ) : ?>
        <form class="ap-search-form" onsubmit="return false;">
            <input class="ap-search-input" type="text" placeholder="<?php esc_attr_e( 'Search city…', 'atmopress-weather' ); ?>" value="<?php echo esc_attr( $cur['city'] . ', ' . $cur['country'] ); ?>" autocomplete="off" />
            <button class="ap-search-btn" type="submit">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
        </form>
        <?php endif; ?>
        <?php if ( $config['show_geolocation'] ) : ?>
        <button class="ap-geo-btn">⊙</button>
        <?php endif; ?>
        <button class="ap-unit-toggle" data-unit="<?php echo esc_attr( $config['units'] ); ?>"><?php echo 'metric' === $config['units'] ? '°F' : '°C'; ?></button>
    </div>
    <?php endif; ?>

    <!-- Hero cell -->
    <div class="ap-grid-hero">
        <div class="ap-grid-hero-left">
            <div class="ap-location-name"><?php echo esc_html( $cur['city'] ); ?>, <span><?php echo esc_html( $cur['country'] ); ?></span></div>
            <div class="ap-grid-temp"><?php echo esc_html( $cur['temp'] ); ?><sup><?php echo esc_html( $unit ); ?></sup></div>
            <div class="ap-grid-desc"><?php echo esc_html( $cur['description'] ); ?></div>
            <div class="ap-grid-range">H:<?php echo esc_html( $cur['temp_max'] . $unit ); ?> &nbsp; L:<?php echo esc_html( $cur['temp_min'] . $unit ); ?></div>
        </div>
        <div class="ap-grid-hero-right">
            <img src="<?php echo esc_url( $cur['icon'] ); ?>" alt="<?php echo esc_attr( $cur['condition'] ); ?>" width="90" height="90" class="ap-weather-icon" />
        </div>
    </div>

    <!-- Stat cells grid -->
    <div class="ap-stat-cells">
        <?php if ( $config['show_humidity'] ) : ?>
        <div class="ap-stat-cell">
            <div class="ap-cell-icon">💧</div>
            <div class="ap-cell-val"><?php echo esc_html( $cur['humidity'] ); ?>%</div>
            <div class="ap-cell-lbl"><?php esc_html_e( 'Humidity', 'atmopress-weather' ); ?></div>
        </div>
        <?php endif; ?>

        <?php if ( $config['show_wind'] ) : ?>
        <div class="ap-stat-cell">
            <div class="ap-cell-icon">🌬️</div>
            <div class="ap-cell-val"><?php echo esc_html( $cur['wind_speed'] ); ?></div>
            <div class="ap-cell-lbl"><?php printf( esc_html__( '%s Wind', 'atmopress-weather' ), esc_html( $speed ) ); ?></div>
        </div>
        <?php endif; ?>

        <?php if ( $config['show_pressure'] ) : ?>
        <div class="ap-stat-cell">
            <div class="ap-cell-icon">🔵</div>
            <div class="ap-cell-val"><?php echo esc_html( $cur['pressure'] ); ?></div>
            <div class="ap-cell-lbl"><?php esc_html_e( 'hPa', 'atmopress-weather' ); ?></div>
        </div>
        <?php endif; ?>

        <?php if ( $config['show_feels_like'] ) : ?>
        <div class="ap-stat-cell">
            <div class="ap-cell-icon">🌡️</div>
            <div class="ap-cell-val"><?php echo esc_html( $cur['feels_like'] . $unit ); ?></div>
            <div class="ap-cell-lbl"><?php esc_html_e( 'Feels like', 'atmopress-weather' ); ?></div>
        </div>
        <?php endif; ?>

        <?php if ( $config['show_visibility'] && null !== $cur['visibility'] ) : ?>
        <div class="ap-stat-cell">
            <div class="ap-cell-icon">👁️</div>
            <div class="ap-cell-val"><?php echo esc_html( $cur['visibility'] ); ?></div>
            <div class="ap-cell-lbl"><?php esc_html_e( 'km Visibility', 'atmopress-weather' ); ?></div>
        </div>
        <?php endif; ?>

        <?php if ( ! empty( $cur['clouds'] ) ) : ?>
        <div class="ap-stat-cell">
            <div class="ap-cell-icon">☁️</div>
            <div class="ap-cell-val"><?php echo esc_html( $cur['clouds'] ); ?>%</div>
            <div class="ap-cell-lbl"><?php esc_html_e( 'Cloud Cover', 'atmopress-weather' ); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ( $config['show_hourly'] && ! empty( $weather['hourly'] ) ) : ?>
    <div class="ap-section-title"><?php esc_html_e( 'Hourly', 'atmopress-weather' ); ?></div>
    <div class="ap-hourly-strip">
        <?php foreach ( array_slice( $weather['hourly'], 0, $config['hourly_count'] ) as $h ) : ?>
        <div class="ap-hourly-item">
            <span class="ap-hourly-time"><?php echo esc_html( $h['time'] ); ?></span>
            <img src="<?php echo esc_url( $h['icon'] ); ?>" alt="" width="30" height="30" />
            <span class="ap-hourly-temp"><?php echo esc_html( $h['temp'] . $unit ); ?></span>
            <?php if ( $h['pop'] > 0 ) : ?><span class="ap-hourly-pop"><?php echo esc_html( $h['pop'] ); ?>%</span><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ( $config['show_daily'] && ! empty( $daily ) ) : ?>
    <div class="ap-section-title"><?php esc_html_e( 'Forecast', 'atmopress-weather' ); ?></div>
    <div class="ap-daily-grid">
        <?php foreach ( array_slice( $daily, 0, $config['forecast_days'] ) as $d ) : ?>
        <div class="ap-daily-card">
            <div class="ap-daily-day"><?php echo esc_html( $d['day_label'] ); ?></div>
            <img src="<?php echo esc_url( $d['icon'] ); ?>" alt="<?php echo esc_attr( $d['condition'] ); ?>" width="40" height="40" />
            <div class="ap-daily-temp-hi"><?php echo esc_html( $d['temp_max'] . $unit ); ?></div>
            <div class="ap-daily-temp-lo"><?php echo esc_html( $d['temp_min'] . $unit ); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
