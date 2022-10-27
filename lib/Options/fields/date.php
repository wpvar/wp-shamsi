<?php if (! defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Date
 *
 */
if (! class_exists('Exopite_Simple_Options_Framework_Field_date')) {
    class Exopite_Simple_Options_Framework_Field_date extends Exopite_Simple_Options_Framework_Fields
    {
        public function __construct($field, $value = '', $unique = '', $config = array())
        {
            parent::__construct($field, $value, $unique, $config);
        }

        public function output()
        {
            $date_format = (! empty($this->field['format'])) ? $this->field['format'] : 'mm/dd/yy';
            $classes     = (isset($this->field['class'])) ? implode(' ', explode(' ', $this->field['class'])) : '';

            echo wp_kses_post($this->element_before());

            echo wp_kses_post($this->element_prepend());

            if (isset($this->field['picker']) && $this->field['picker'] == 'html5') {
                echo '<input type="date" ';
            } else {
                echo '<input type="text" ';
                echo 'class="datepicker ' . esc_attr($classes) . '" ';
            }
            echo 'name="' . esc_attr($this->element_name()) . '" ';
            if (isset($this->field['picker']) && $this->field['picker'] == 'html5') {
                echo 'value="' . esc_html($this->element_value()) . '"' . esc_attr($this->element_class()) . esc_attr($this->element_attributes()) . ' ';
            } else {
                echo 'value="' . esc_html($this->element_value()) . '"' . esc_attr($this->element_attributes()) . ' ';
                echo 'data-format="' . esc_html($date_format) . '"';
            }
            echo '>';

            echo wp_kses_post($this->element_append());

            echo wp_kses_post($this->element_after());
        }

        public static function enqueue($args)
        {
            $resources = array(
                array(
                    'name'       => 'exopite-sof-datepicker-loader',
                    'fn'         => 'loader-datepicker.min.js',
                    'type'       => 'script',
                    'dependency' => array( 'jquery' ),
                    'version'    => '',
                    'attr'       => true,
                ),
            );

            parent::do_enqueue($resources, $args);
        }
    }
}
