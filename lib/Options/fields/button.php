<?php if (! defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Button
 *
 */
if (! class_exists('Exopite_Simple_Options_Framework_Field_button')) {
    class Exopite_Simple_Options_Framework_Field_button extends Exopite_Simple_Options_Framework_Fields
    {
        public function __construct($field, $value = '', $unique = '', $config = array())
        {
            parent::__construct($field, $value, $unique, $config);

            $defaults = array(
                'href'      => '',
                'target'    => '_self',
                'value'     => 'button',
                'btn-class' => 'exopite-sof-btn',
                'btn-id'    => '',
            );

            $options = (! empty($this->field['options'])) ? $this->field['options'] : array();

            $this->field['options'] = wp_parse_args($options, $defaults);
        }

        public function output()
        {
            $classes = (isset($this->field['class'])) ? implode(' ', explode(' ', $this->field['class'])) : '';

            echo wp_kses_post($this->element_before());
            echo '<a ';
            if (! empty($this->field['options']['href'])) {
                echo 'href="' . esc_url($this->field['options']['href']) . '"';
            }
            if (! empty($this->field['options']['btn-id'])) {
                echo ' id="' . esc_attr($this->field['options']['btn-id']) . '"';
            }
            echo ' target="' . esc_html($this->field['options']['target']) . '"';
            echo ' class="' . esc_attr($this->field['options']['btn-class']) . ' ' . esc_attr($classes) . '"';
            echo wp_kses_post($this->element_attributes()) . '/>' . esc_html($this->field['options']['value']) . '</a>';
            echo wp_kses_post($this->element_after());
        }
    }
}
