<?php if (! defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Password
 *
 */
if (! class_exists('Exopite_Simple_Options_Framework_Field_password')) {
    class Exopite_Simple_Options_Framework_Field_password extends Exopite_Simple_Options_Framework_Fields
    {
        public function __construct($field, $value = '', $unique = '', $config = array())
        {
            parent::__construct($field, $value, $unique, $config);
        }

        public function output()
        {
            echo wp_kses_post($this->element_before());

            echo wp_kses_post($this->element_prepend());

            echo '<input type="' . esc_attr($this->element_type()) . '" name="' . esc_attr($this->element_name()) . '" value="' . esc_html($this->element_value()) . '"' . esc_attr($this->element_class()) . wp_kses_post($this->element_attributes()) . '/>';

            echo wp_kses_post($this->element_append());

            echo wp_kses_post($this->element_after());
        }
    }
}
