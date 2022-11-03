<?php
defined('ABSPATH') or die();

function wpsh_custom_html_tags()
{
    $tags = array(
    'input'  => array(
        'type'   => array(),
        'class' => array(),
        'id'    => array(),
        'name'  => array()
    ));

    return $tags;
}
