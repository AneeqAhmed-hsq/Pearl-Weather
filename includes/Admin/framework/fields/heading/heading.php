<?php
/**
 * Framework Heading Field
 *
 * Renders a heading section with optional image, version badge,
 * and help/support dropdown.
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
 * Class HeadingField
 *
 * Handles heading field rendering in the framework.
 *
 * @since 1.0.0
 */
class HeadingField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'content'     => '',
        'image'       => '',
        'version'     => '',
        'after'       => '',
        'link'        => '',
        'doc_url'     => 'https://pearlweather.com/docs/',
        'support_url' => 'https://pearlweather.com/support/',
        'feature_url' => 'https://pearlweather.com/contact/',
    );

    /**
     * Render the heading field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        // Render content.
        if ( ! empty( $args['content'] ) ) {
            echo '<div class="pw-heading-content">' . wp_kses_post( $args['content'] ) . '</div>';
        }

        // Render image with version badge.
        if ( ! empty( $args['image'] ) ) {
            $this->render_image_section( $args );
        }

        // Render help/support area.
        if ( ! empty( $args['after'] ) && ! empty( $args['link'] ) ) {
            $this->render_help_area( $args );
        }

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render image section with version badge.
     *
     * @since 1.0.0
     * @param array $args Field arguments.
     */
    private function render_image_section( $args ) {
        ?>
        <div class="pw-heading-image-wrapper">
            <img src="<?php echo esc_url( $args['image'] ); ?>" alt="<?php esc_attr_e( 'Plugin Logo', 'pearl-weather' ); ?>" class="pw-heading-image" />
            <?php if ( ! empty( $args['version'] ) ) : ?>
                <span class="pw-version-badge">v<?php echo esc_html( $args['version'] ); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render help/support area with dropdown.
     *
     * @since 1.0.0
     * @param array $args Field arguments.
     */
    private function render_help_area( $args ) {
        ?>
        <div class="pw-help-area">
            <span class="pw-help-trigger">
                <?php echo wp_kses_post( $args['after'] ); ?>
                <span class="pw-help-icon">▼</span>
            </span>
            <div class="pw-help-dropdown">
                <div class="pw-help-section">
                    <div class="pw-help-label"><?php esc_html_e( 'Documentation', 'pearl-weather' ); ?></div>
                    <p><?php esc_html_e( 'Check out our documentation to learn more about using Pearl Weather.', 'pearl-weather' ); ?></p>
                    <a href="<?php echo esc_url( $args['doc_url'] ); ?>" class="pw-help-link" target="_blank">
                        <?php esc_html_e( 'Browse Docs', 'pearl-weather' ); ?> →
                    </a>
                </div>
                <div class="pw-help-section">
                    <div class="pw-help-label"><?php esc_html_e( 'Need Help?', 'pearl-weather' ); ?></div>
                    <p><?php esc_html_e( 'Get help from our friendly support team.', 'pearl-weather' ); ?></p>
                    <a href="<?php echo esc_url( $args['support_url'] ); ?>" class="pw-help-link" target="_blank">
                        <?php esc_html_e( 'Get Help', 'pearl-weather' ); ?> →
                    </a>
                </div>
                <div class="pw-help-section">
                    <div class="pw-help-label"><?php esc_html_e( 'Request a Feature', 'pearl-weather' ); ?></div>
                    <p><?php esc_html_e( 'Suggest a feature to make the plugin better.', 'pearl-weather' ); ?></p>
                    <a href="<?php echo esc_url( $args['feature_url'] ); ?>" class="pw-help-link" target="_blank">
                        <?php esc_html_e( 'Request Feature', 'pearl-weather' ); ?> →
                    </a>
                </div>
            </div>
        </div>

        <style>
            .pw-help-area {
                position: relative;
                display: inline-block;
            }
            .pw-help-trigger {
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 5px 10px;
                background: #f0f0f1;
                border-radius: 4px;
            }
            .pw-help-icon {
                font-size: 10px;
                transition: transform 0.2s;
            }
            .pw-help-area:hover .pw-help-icon {
                transform: rotate(180deg);
            }
            .pw-help-dropdown {
                display: none;
                position: absolute;
                top: 100%;
                right: 0;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                width: 280px;
                z-index: 100;
                margin-top: 5px;
            }
            .pw-help-area:hover .pw-help-dropdown {
                display: block;
            }
            .pw-help-section {
                padding: 15px;
                border-bottom: 1px solid #eee;
            }
            .pw-help-section:last-child {
                border-bottom: none;
            }
            .pw-help-label {
                font-weight: 600;
                margin-bottom: 8px;
                color: #1e1e1e;
            }
            .pw-help-section p {
                margin: 0 0 10px 0;
                font-size: 12px;
                color: #666;
            }
            .pw-help-link {
                color: #f26c0d;
                text-decoration: none;
                font-size: 12px;
                font-weight: 500;
            }
            .pw-help-link:hover {
                text-decoration: underline;
            }
        </style>
        <?php
    }
}