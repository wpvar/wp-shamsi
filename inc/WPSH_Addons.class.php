<?php
/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Addon System
 *
 * Run addon system
 *
 * @since 1.2.0
 */
class WPSH_Addons extends WPSH_Core
{

    /**
     * Construction
     *
     * Construct Addon class.
     *
     * @since 1.2.0
     *
     */
    function __construct()
    {
        global $wpsh_addon;
        $wpsh_addon = array();
        foreach (glob(WPSH_PATH . 'addons/*.php') as $filename)
        {
            include $filename;

        }
    }

    /**
     * Validate addon
     *
     * Check if addon is active or not.
     *
     * @since 1.2.0
     *
     * @param string $slug Slug of addon.
     * @return bool return true or false.
     */
    public function validate($slug)
    {
        $validate = parent::option($slug, true);

        return $validate;
    }
}
new WPSH_Addons();