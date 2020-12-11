<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * RankMath Compability
 *
 * RankMath Compability Class
 *
 * @since 2.1.0
 */
class WPSH_Rankmath extends WPSH_Core

{

    function __construct()
    {

        if (!parent::option('activate-rankmath', true, true)) {
            return;
        }
    }
}

new WPSH_Rankmath();
