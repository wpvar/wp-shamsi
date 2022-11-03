<?php if (! defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Image Select
 *
 */
if (! class_exists('Exopite_Simple_Options_Framework_Field_image_select')) {
    class Exopite_Simple_Options_Framework_Field_image_select extends Exopite_Simple_Options_Framework_Fields
    {
        public function __construct($field, $value = '', $unique = '', $config = array())
        {
            parent::__construct($field, $value, $unique, $config);
        }

        public function output()
        {
            $input_type = (! empty($this->field['radio'])) ? 'radio' : 'checkbox';
            $input_attr = ($input_type == 'checkbox') ? '[]' : '';
            $layout = (isset($this->field['layout']) && $this->field['layout'] == 'vertical') ? 'exopite-sof-field-image-selector-vertical' : 'exopite-sof-field-image-selector-horizontal';

            echo wp_kses_post($this->element_before());
            echo '<div class="exopite-sof-field-image-selector ' . esc_attr($layout) . '">';

            if (isset($this->field['options'])) {
                $options = $this->field['options'];
                foreach ($options as $key => $value) {
                    echo '<label><input type="' . esc_attr($input_type) . '" name="' . esc_attr($this->element_name($input_attr)) . '" value="' . esc_html($key) . '"' . esc_attr($this->element_class()) . wp_kses_post($this->element_attributes($key)) . esc_attr($this->checked($this->element_value(), $key)) . '/>';
                    echo (! empty($this->field['text_select'])) ? '<span class="exopite-sof-' . esc_attr(sanitize_title($value)) . '">' . esc_html($value) . '</span>' : '<img src="' . esc_url($value) . '"   alt="' . esc_html($key) . '" />';
                    echo '</label>';
                }
            }

            echo '</div>';
            echo wp_kses_post($this->element_after());
        }
    }
}
