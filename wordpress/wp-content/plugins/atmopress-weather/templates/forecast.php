<?php
/**
 * AtmoPress Template: Forecast
 * A forecast-first layout with full 7-day breakdown and hourly tabs.
 */

use AtmoPress\TemplateLoader;
if ( ! defined( 'ABSPATH' ) ) { exit; }

$cur   = $weather['current'];
$daily = $weather['daily'];
$hour  = $weather['hourly'];
$unit  = TemplateLoader::unit_label( $config['units'] );
$speed = TemplateLoader::speed_label( $config['units'] );
$id    = TemplateLoader::widget_id( 'ap-fcast' );
?>
<div id="<?php echo esc_attr( $id ); ?>"
     class="ap-widget ap-forecast-tpl <?php echo esc_attr( $config['custom_class'] ); ?>"
     style="<?php echo esc_attr( TemplateLoader::css_vars( $config ) ); ?>"
     data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">

    <!-- Header -->
    <div class="ap-fcast-header">
        <div class="ap-fcast-now">
            <div class="ap-fcast-city">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                <?php echo esc_html( $cur['city'] . ', ' . $cur['country'] ); ?>
            </div>
            <div class="ap-fcast-temp"><?php echo esc_html( $cur['temp'] . $unit ); ?></div>
            <div class="ap-fcast-desc"><?php echo esc_html( $cur['description'] ); ?></div>
        </div>
        <img class="ap-weather-icon" src="<?php echo esc_url( $cur['icon'] ); ?>" alt="<?php echo esc_attr( $cur['condition'] ); ?>" width="72" height="72" />
        <div class="ap-fcast-controls">
            <?php if ( $config['show_search'] ) : ?>
            <form class="ap-search-form" onsubmit="return false;">
                <input class="ap-search-input" type="text" placeholder="<?php esc_attr_e( 'Search city…', 'atmopress-weather' ); ?>" autocomplete="off" />
                <button class="ap-search-btn" type="submit">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
            </form>
            <?php endif; ?>
            <?php if ( $config['show_geolocation'] ) : ?>
            <button class="ap-geo-btn">⊙</button>
            <?php endif; ?>
            <button class="ap-unit-toggle" data-unit="<?php echo esc_attr( $config['units'] ); ?>"><?php echo 'metric' === $config['units'] ? '°F' : '°C'; ?></button>
        </div>
    </div>

    <!-- Quick stats -->
    <?php if ( $config['show_humidity'] || $config['show_wind'] || $config['show_pressure'] ) : ?>
    <div class="ap-fcast-stats">
        <?php if ( $config['show_humidity'] ) : ?><span>💧 <?php echo esc_html( $cur['humidity'] ); ?>%</span><?php endif; ?>
        <?php if ( $config['show_wind'] ) : ?><span>🌬️ <?php echo esc_html( $cur['wind_speed'] . ' ' . $speed ); ?> <?php echo esc_html( TemplateLoader::wind_direction( $cur['wind_deg'] ) ); ?></span><?php endif; ?>
        <?php if ( $config['show_pressure'] ) : ?><span>🔵 <?php echo esc_html( $cur['pressure'] ); ?> hPa</span><?php endif; ?>
        <?php if ( $config['show_feels_like'] ) : ?><span>🌡️ <?php printf( esc_html__( 'Feels %s%s', 'atmopress-weather' ), esc_html( $cur['feels_like'] ), esc_html( $unit ) ); ?></span><?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Hourly scroll -->
    <?php if ( $config['show_hourly'] && ! empty( $hour ) ) : ?>
    <div class="ap-section-title"><?php esc_html_e( 'Hourly Forecast', 'atmopress-weather' ); ?></div>
    <div class="ap-hourly-strip ap-hourly-strip--full">
        <?php foreach ( array_slice( $hour, 0, $config['hourly_count'] ) as $h ) : ?>
        <div class="ap-hourly-item ap-hourly-item--detailed">
            <span class="ap-hourly-time"><?php echo esc_html( $h['time'] ); ?></span>
            <img src="<?php echo esc_url( $h['icon'] ); ?>" alt="" width="32" height="32" />
            <span class="ap-hourly-temp"><?php echo esc_html( $h['temp'] . $unit ); ?></span>
            <span class="ap-hourly-feels"><?php echo esc_html( $h['feels_like'] . $unit ); ?></span>
            <span class="ap-hourly-hum">💧<?php echo esc_html( $h['humidity'] ); ?>%</span>
            <?php if ( $h['pop'] > 0 ) : ?><span class="ap-hourly-pop">🌧️<?php echo esc_html( $h['pop'] ); ?>%</span><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Daily rows with bar graph -->
    <?php if ( $config['show_daily'] && ! empty( $daily ) ) : ?>
    <div class="ap-section-title"><?php esc_html_e( 'Daily Forecast', 'atmopress-weather' ); ?></div>
    <?php
    $all_highs = array_column( $daily, 'temp_max' );
    $all_lows  = array_column( $daily, 'temp_min' );
    $max_t     = max( $all_highs );
    $min_t     = min( $all_lows );
    $range     = max( 1, $max_t - $min_t );
    ?>
    <div class="ap-daily-detail">
        <?php foreach ( array_slice( $daily, 0, $config['forecast_days'] ) as $d ) : ?>
        <?php
            $bar_lo   = round( ( $d['temp_min'] - $min_t ) / $range * 60 );
            $bar_len  = max( 4, round( ( $d['temp_max'] - $d['temp_min'] ) / $range * 60 ) );
        ?>
        <div class="ap-dd-row">
            <span class="ap-dd-day"><?php echo esc_html( $d['day_label'] ); ?></span>
            <img src="<?php echo esc_url( $d['icon'] ); ?>" alt="<?php echo esc_attr( $d['condition'] ); ?>" width="28" height="28" />
            <span class="ap-dd-desc"><?php echo esc_html( $d['description'] ); ?></span>
            <span class="ap-dd-lo"><?php echo esc_html( $d['temp_min'] . $unit ); ?></span>
            <div class="ap-dd-bar-wrap">
                <div class="ap-dd-bar" style="margin-left:<?php echo esc_attr( $bar_lo ); ?>px;width:<?php echo esc_attr( $bar_len ); ?>px;"></div>
            </div>
            <span class="ap-dd-hi"><?php echo esc_html( $d['temp_max'] . $unit ); ?></span>
            <?php if ( $d['pop'] > 0 ) : ?>
            <span class="ap-dd-pop">💧<?php echo esc_html( $d['pop'] ); ?>%</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ( $config['show_sunrise'] && $cur['sunrise'] ) : ?>
    <div class="ap-fcast-sun">
        <span>🌅 <?php echo esc_html( TemplateLoader::format_time( $cur['sunrise'], $cur['timezone'] ) ); ?></span>
        <span>🌇 <?php echo esc_html( TemplateLoader::format_time( $cur['sunset'], $cur['timezone'] ) ); ?></span>
    </div>
    <?php endif; ?>

</div>
