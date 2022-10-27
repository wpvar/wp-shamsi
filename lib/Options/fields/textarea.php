<?php if (! defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Textarea
 *
 */
if (! class_exists('Exopite_Simple_Options_Framework_Field_textarea')) {
    class Exopite_Simple_Options_Framework_Field_textarea extends Exopite_Simple_Options_Framework_Fields
    {
        public function __construct($field, $value = '', $unique = '', $config = array())
        {
            parent::__construct($field, $value, $unique, $config);
        }

        public function output()
        {
            echo wp_kses_post($this->element_before());
            echo '<textarea name="' . esc_attr($this->element_name()) . '"' . esc_attr($this->element_class()) . esc_attr($this->element_attributes()) . '>' . wp_kses_post($this->element_value()) . '</textarea>';
            echo wp_kses_post($this->element_after());
        }
    }
}
