<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Blocks
 *
 * WP Shamsi Blocks class
 *
 * @since 4.0.0
 */
class WPSH_Blocks
{

    /**
     * Construction
     *
     * Construct Blocks class.
     *
     * @since 4.0.0
     *
     */
    function __construct()
    {
        add_action('init', array($this, 'wpsh_blocks_aparat'));
        add_filter('block_categories', array($this, 'wpsh_block_category'));
    }

    /**
     * Register
     *
     * Register blocks.
     *
     * @since 4.0.0
     *
     */
    public function wpsh_blocks_aparat()
    {
        if(function_exists('register_block_type_from_metadata')) {
            register_block_type_from_metadata(WPSH_PATH . 'blocks/aparat', array('render_callback' => array($this, 'wpsh_blocks_aparat_render_callback')));
            register_block_type_from_metadata(WPSH_PATH . 'blocks/justify');
            register_block_type_from_metadata(WPSH_PATH . 'blocks/shamsi', array('render_callback' => array($this, 'wpsh_blocks_shamsi_render_callback')));
        }
    }

    /**
     * Render Aparat block
     *
     * Aparat block server side rendering.
     *
     * @since 4.0.0
     *
     * @param mixed $block_attributes
     * @param mixed $content
     * @return mixed return rendered html. 
     */
    public function wpsh_blocks_aparat_render_callback($block_attributes, $content)
    {
        if ($block_attributes['aparat'] == '') {
            return __('لطفا لینک ویدیو را وارد کنید', 'wpsh');
        }

        if (!mb_strpos($block_attributes['aparat'], 'aparat.com/v/')) {
            return __('لینک ویدیو آپارات معتبر نیست', 'wpsh');
        }

        $id = explode('/', $block_attributes['aparat']);

        ob_start();
        echo '
            <div class="wpsh-blocks_aparat wpsh-blocks_aparat_align_' . $block_attributes['alignment'] . ' wpsh-blocks_aparat_size_' . $block_attributes['size'] . '">
        ';
        echo '
            <div class="h_iframe-aparat_embed_frame"><span style="display: block;padding-top: 57%"></span><iframe src="https://www.aparat.com/video/video/embed/videohash/' . $id[4] . '/vt/frame" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" frameBorder="0"></iframe></div>
        ';
        echo '
            </div>
        ';
        return ob_get_clean();
    }

    /**
     * Render Shamsi block
     *
     * Shamsi block server side rendering.
     *
     * @since 4.0.0
     *
     * @param mixed $block_attributes
     * @param mixed $content
     * @return mixed return rendered html. 
     */
    public function wpsh_blocks_shamsi_render_callback($block_attributes, $content)
    {
        $date = wp_date('امروز: l، j F سال Y', strtotime($block_attributes['date']));
        ob_start();
            echo '
                <p class="wpsh-blocks_shamsi wpsh-blocks_shamsi_align_' . $block_attributes['alignment'] . '">' . $date . '<p>
            ';
        return ob_get_clean();
    }

    /**
     * Add block category
     *
     * Adds WP Shamsi blocks category.
     *
     * @since 4.0.0
     *
     * @param mixed $categories
     * @return mixed return updated categories.
     */
    public function wpsh_block_category($categories)
    {
        $category_slugs = wp_list_pluck($categories, 'slug');
        return in_array('wpsh', $category_slugs, true) ? $categories : array_merge(
            $categories,
            array(
                array(
                    'slug'  => 'wpsh',
                    'title' => __('وردپرس فارسی', 'wpsh'),
                    'icon'  => null,
                ),
            )
        );
    }
}
