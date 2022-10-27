<?php if (! defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
/**
 *
 * Field: Card
 *
 */
if (! class_exists('Exopite_Simple_Options_Framework_Field_card')) {
    class Exopite_Simple_Options_Framework_Field_card extends Exopite_Simple_Options_Framework_Fields
    {
        public function __construct($field, $value = '', $unique = '', $config = array())
        {
            parent::__construct($field, $value, $unique, $config);
        }

        public function output()
        {
            $classes = (isset($this->field['class'])) ? implode(' ', explode(' ', $this->field['class'])) : '';

            echo wp_kses_post($this->element_before());

            echo '<div class="card ' . esc_attr($classes) . '">';

            if (! empty($this->field['header'])) {
                echo '<div class="card-header">';
                echo wp_kses_post($this->field['header']);
                echo '</div>';
            }

            echo '<div class="card-body">';

            if (! empty($this->field['title'])) {
                echo '<h4 class="card-title">' . wp_kses_post($this->field['title']) . '</h4>';
            }

            echo '<div class="card-text">' . wp_kses_post($this->field['content']) . '</div>';

            echo '</div>';

            if (! empty($this->field['footer'])) {
                echo '<div class="card-footer text-muted">';
                echo wp_kses_post($this->field['footer']);
                echo '</div>';
            }

            echo '</div>';

            echo wp_kses_post($this->element_after());
        }
    }
}
