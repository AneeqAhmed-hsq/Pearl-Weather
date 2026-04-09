<?php
/**
 * Framework Image Select Field
 *
 * Renders a grid of image options for selecting layouts or designs.
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
 * Class ImageSelectField
 *
 * Handles image select field rendering in the framework.
 *
 * @since 1.0.0
 */
class ImageSelectField extends BaseField {

    /**
     * Default field arguments.
     *
     * @var array
     */
    private $default_args = array(
        'multiple' => false,
        'inline'   => false,
        'options'  => array(),
        'columns'  => 3,
    );

    /**
     * Render the image select field.
     *
     * @since 1.0.0
     */
    public function render() {
        $args = wp_parse_args( $this->field, $this->default_args );

        $inline_class = $args['inline'] ? ' pw-image-select-inline' : '';
        $value = is_array( $this->value ) ? $this->value : array_filter( (array) $this->value );

        echo $this->field_before(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ( ! empty( $args['options'] ) ) {
            $columns_class = ' pw-cols-' . $args['columns'];
            
            echo '<div class="pw-image-select-group' . esc_attr( $inline_class . $columns_class ) . '" data-multiple="' . esc_attr( $args['multiple'] ? 'true' : 'false' ) . '">';

            foreach ( $args['options'] as $key => $option ) {
                $this->render_option( $key, $option, $args, $value );
            }

            echo '</div>';
        }

        echo $this->field_after(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render a single image select option.
     *
     * @since 1.0.0
     * @param string $key    Option key.
     * @param mixed  $option Option data.
     * @param array  $args   Field arguments.
     * @param array  $value  Current values.
     */
    private function render_option( $key, $option, $args, $value ) {
        $type = $args['multiple'] ? 'checkbox' : 'radio';
        $extra = $args['multiple'] ? '[]' : '';
        
        $is_active = in_array( $key, $value, true );
        $active_class = $is_active ? ' pw-active' : '';
        
        $is_pro = isset( $option['pro_only'] ) && $option['pro_only'];
        $pro_class = $is_pro ? ' pw-pro-option' : '';
        $disabled_attr = $is_pro ? ' disabled' : '';
        
        $image_url = isset( $option['image'] ) ? $option['image'] : $option;
        $image_alt = isset( $option['name'] ) ? $option['name'] : 'Option ' . $key;
        
        $checked = $is_active ? ' checked' : '';

        ?>
        <div class="pw-image-select-option<?php echo esc_attr( $active_class . $pro_class ); ?>">
            <figure>
                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
                <input type="<?php echo esc_attr( $type ); ?>" 
                       name="<?php echo esc_attr( $this->field_name( $extra ) ); ?>" 
                       value="<?php echo esc_attr( $key ); ?>" 
                       <?php echo $disabled_attr; ?>
                       <?php echo $this->field_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                       <?php echo esc_attr( $checked ); ?> />
            </figure>
            
            <?php if ( isset( $option['name'] ) ) : ?>
                <div class="pw-option-label">
                    <span><?php echo esc_html( $option['name'] ); ?></span>
                    <?php if ( isset( $option['demo_url'] ) ) : ?>
                        <a href="<?php echo esc_url( $option['demo_url'] ); ?>" 
                           class="pw-demo-link" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           title="<?php esc_attr_e( 'View Demo', 'pearl-weather' ); ?>">
                            <span class="pw-demo-icon">🔗</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ( $is_pro ) : ?>
                <div class="pw-pro-badge"><?php esc_html_e( 'PRO', 'pearl-weather' ); ?></div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Enqueue field-specific assets.
     *
     * @since 1.0.0
     */
    public function enqueue() {
        wp_add_inline_style( 'pearl-weather-framework', '
            .pw-image-select-group {
                display: grid;
                gap: 20px;
                margin-top: 10px;
            }
            .pw-image-select-group.pw-cols-2 {
                grid-template-columns: repeat(2, 1fr);
            }
            .pw-image-select-group.pw-cols-3 {
                grid-template-columns: repeat(3, 1fr);
            }
            .pw-image-select-group.pw-cols-4 {
                grid-template-columns: repeat(4, 1fr);
            }
            .pw-image-select-group.pw-image-select-inline {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
            }
            .pw-image-select-option {
                position: relative;
                cursor: pointer;
                border: 2px solid transparent;
                border-radius: 8px;
                transition: all 0.2s ease;
                overflow: hidden;
                background: #fff;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .pw-image-select-option:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            }
            .pw-image-select-option.pw-active {
                border-color: #f26c0d;
                box-shadow: 0 0 0 2px rgba(242,108,13,0.2);
            }
            .pw-image-select-option figure {
                margin: 0;
                position: relative;
            }
            .pw-image-select-option img {
                width: 100%;
                height: auto;
                display: block;
            }
            .pw-image-select-option input {
                position: absolute;
                bottom: 10px;
                right: 10px;
                width: 20px;
                height: 20px;
                cursor: pointer;
                z-index: 2;
            }
            .pw-option-label {
                padding: 10px;
                text-align: center;
                font-size: 13px;
                font-weight: 500;
                background: #f8f9fa;
                border-top: 1px solid #eee;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            .pw-demo-link {
                color: #f26c0d;
                text-decoration: none;
                font-size: 12px;
            }
            .pw-demo-link:hover {
                text-decoration: underline;
            }
            .pw-pro-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                background: #f26c0d;
                color: #fff;
                font-size: 10px;
                font-weight: 600;
                padding: 3px 8px;
                border-radius: 20px;
                z-index: 3;
            }
            .pw-pro-option {
                opacity: 0.8;
                cursor: not-allowed;
            }
            .pw-pro-option input {
                cursor: not-allowed;
            }
            @media (max-width: 768px) {
                .pw-image-select-group.pw-cols-2,
                .pw-image-select-group.pw-cols-3,
                .pw-image-select-group.pw-cols-4 {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            @media (max-width: 480px) {
                .pw-image-select-group.pw-cols-2,
                .pw-image-select-group.pw-cols-3,
                .pw-image-select-group.pw-cols-4 {
                    grid-template-columns: repeat(1, 1fr);
                }
            }
        ' );
    }
}