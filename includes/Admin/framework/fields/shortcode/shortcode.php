<?php
/**
 * Framework Shortcode Field
 *
 * Displays the widget shortcode with copy functionality and
 * pro upgrade notice with feature highlights.
 *
 * @package    PearlWeather
 * @subpackage PearlWeather/Framework/Fields
 * @since      1.0.0
 */

namespace PearlWeather\Framework\Fields;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ShortcodeField
 *
 * Handles shortcode display and pro upgrade notice.
 *
 * @since 1.0.0
 */
class ShortcodeField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'shortcode' => 'shortcode', // 'shortcode' or 'pro_notice'
        'copy_text' => 'Shortcode Copied to Clipboard!',
    );

    /**
     * Pro features list.
     *
     * @var array
     */
    private $pro_features = array(
        '25+ Premium Weather Templates',
        'Daily & Hourly Forecasts',
        'Interactive Radar & Weather Maps',
        'Weather Graph Charts',
        'Weather Data Carousel',
        'Custom Weather Search',
        '18+ Detailed Weather Metrics',
        '120+ Advanced Customizations',
    );

    /**
     * Pro feature links.
     *
     * @var array
     */
    private $pro_feature_links = array(
        '25+ Premium Weather Templates' => 'https://pearlweather.com/#templates',
        'Interactive Radar & Weather Maps' => 'https://pearlweather.com/#maps',
        'Weather Graph Charts' => 'https://pearlweather.com/demos/weather-graph/',
        'Custom Weather Search' => 'https://pearlweather.com/demos/custom-search/',
    );

    /**
     * Render the shortcode field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $post_id = get_the_ID();

        if ( empty( $post_id ) ) {
            return;
        }

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ( 'shortcode' === $args['shortcode'] ) {
            $this->render_shortcode_section( $post_id, $args );
        } elseif ( 'pro_notice' === $args['shortcode'] ) {
            $this->render_pro_notice();
        } else {
            $this->render_integration_notice();
        }

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render shortcode section with copy functionality.
     *
     * @since 1.0.0
     * @param int   $post_id Current post ID.
     * @param array $args    Field arguments.
     */
    private function render_shortcode_section( $post_id, $args ) {
        $shortcode = '[pearl-weather id="' . $post_id . '"]';
        $copy_text = $args['copy_text'];

        ?>
        <div class="pw-shortcode-area">
            <p>
                <?php esc_html_e( 'To display the weather widget, copy and paste this shortcode into your post, page, or widget area.', 'pearl-weather' ); ?>
                <a href="https://pearlweather.com/docs/shortcode-usage/" target="_blank">
                    <?php esc_html_e( 'Learn more', 'pearl-weather' ); ?>
                </a>
            </p>
            <div class="pw-shortcode-wrapper">
                <code class="pw-shortcode-code selectable"><?php echo esc_html( $shortcode ); ?></code>
                <button type="button" class="button pw-copy-shortcode" data-shortcode="<?php echo esc_attr( $shortcode ); ?>">
                    <?php esc_html_e( 'Copy', 'pearl-weather' ); ?>
                </button>
            </div>
            <div class="pw-copy-notice" style="display: none;">
                <i class="fa fa-check-circle"></i> <?php echo esc_html( $copy_text ); ?>
            </div>
        </div>

        <style>
            .pw-shortcode-area {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 6px;
                margin: 10px 0;
            }
            .pw-shortcode-wrapper {
                display: flex;
                align-items: center;
                gap: 10px;
                margin: 12px 0;
            }
            .pw-shortcode-code {
                flex: 1;
                padding: 10px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-family: monospace;
                font-size: 13px;
                cursor: pointer;
            }
            .pw-copy-notice {
                color: #46b450;
                font-size: 12px;
                margin-top: 8px;
            }
        </style>

        <script>
        (function($) {
            $('.pw-copy-shortcode, .pw-shortcode-code').on('click', function() {
                var shortcode = $('.pw-shortcode-code').text();
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(shortcode).select();
                document.execCommand('copy');
                $temp.remove();
                
                $('.pw-copy-notice').fadeIn().delay(2000).fadeOut();
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Render pro upgrade notice.
     *
     * @since 1.0.0
     */
    private function render_pro_notice() {
        ?>
        <div class="pw-pro-notice-wrapper">
            <div class="pw-pro-notice-heading">
                <?php esc_html_e( 'Unlock More Power with', 'pearl-weather' ); ?>
                <span><?php esc_html_e( 'PRO', 'pearl-weather' ); ?></span>
            </div>
            
            <p class="pw-pro-notice-desc">
                <?php esc_html_e( 'Help visitors with advanced weather data by upgrading to Pearl Weather Pro!', 'pearl-weather' ); ?>
            </p>
            
            <ul class="pw-pro-features-list">
                <?php foreach ( $this->pro_features as $feature ) : ?>
                    <li>
                        <i class="pw-icon-check">✓</i>
                        <?php if ( isset( $this->pro_feature_links[ $feature ] ) ) : ?>
                            <a href="<?php echo esc_url( $this->pro_feature_links[ $feature ] ); ?>" target="_blank" class="pw-feature-link">
                                <?php echo esc_html( $feature ); ?>
                            </a>
                        <?php else : ?>
                            <?php echo esc_html( $feature ); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="pw-pro-notice-button">
                <a href="https://pearlweather.com/pricing/" class="pw-upgrade-btn" target="_blank">
                    <?php esc_html_e( 'Upgrade to Pro Now', 'pearl-weather' ); ?>
                    <span class="pw-arrow">→</span>
                </a>
            </div>
        </div>

        <style>
            .pw-pro-notice-wrapper {
                background: linear-gradient(135deg, #fff9f0 0%, #fff 100%);
                border: 1px solid #ffe0b3;
                border-radius: 12px;
                padding: 25px;
                margin: 15px 0;
                text-align: center;
            }
            .pw-pro-notice-heading {
                font-size: 22px;
                font-weight: 600;
                margin-bottom: 12px;
            }
            .pw-pro-notice-heading span {
                color: #f26c0d;
            }
            .pw-pro-notice-desc {
                color: #666;
                margin-bottom: 20px;
            }
            .pw-pro-features-list {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                list-style: none;
                margin: 20px 0;
                padding: 0;
                text-align: left;
            }
            .pw-pro-features-list li {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
            }
            .pw-icon-check {
                color: #f26c0d;
                font-style: normal;
                font-weight: bold;
            }
            .pw-feature-link {
                color: #333;
                text-decoration: none;
            }
            .pw-feature-link:hover {
                color: #f26c0d;
                text-decoration: underline;
            }
            .pw-upgrade-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: #f26c0d;
                color: #fff;
                padding: 12px 24px;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            .pw-upgrade-btn:hover {
                background: #e05a00;
                transform: translateY(-2px);
            }
            @media (max-width: 768px) {
                .pw-pro-features-list {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <?php
    }

    /**
     * Render integration notice.
     *
     * @since 1.0.0
     */
    private function render_integration_notice() {
        ?>
        <div class="pw-integration-notice">
            <p>
                <?php
                printf(
                    /* translators: %1$s: strong opening tag, %2$s: strong closing tag */
                    esc_html__( 'Pearl Weather has seamless integration with Gutenberg, Classic Editor, %1$sElementor%2$s, Divi, Bricks, Beaver Builder, Oxygen, WPBakery, and more.', 'pearl-weather' ),
                    '<strong>',
                    '</strong>'
                );
                ?>
            </p>
        </div>
        <style>
            .pw-integration-notice {
                background: #e8f0fe;
                padding: 12px 16px;
                border-radius: 6px;
                margin: 10px 0;
                font-size: 13px;
            }
        </style>
        <?php
    }
}