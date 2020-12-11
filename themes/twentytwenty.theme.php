<?php
/**
* @package WPSH
*/
defined('ABSPATH') or die();

$css .= '
  body, input, textarea, button, .button, .faux-button, .wp-block-button__link, .wp-block-file__button, .has-drop-cap:not(:focus)::first-letter, .has-drop-cap:not(:focus)::first-letter, .entry-content .wp-block-archives, .entry-content .wp-block-categories, .entry-content .wp-block-cover-image, .entry-content .wp-block-latest-comments, .entry-content .wp-block-latest-posts, .entry-content .wp-block-pullquote, .entry-content .wp-block-quote.is-large, .entry-content .wp-block-quote.is-style-large, .entry-content .wp-block-archives *, .entry-content .wp-block-categories *, .entry-content .wp-block-latest-posts *, .entry-content .wp-block-latest-comments *, .entry-content p, .entry-content ol, .entry-content ul, .entry-content dl, .entry-content dt, .entry-content cite, .entry-content figcaption, .entry-content .wp-caption-text, .comment-content p, .comment-content ol, .comment-content ul, .comment-content dl, .comment-content dt, .comment-content cite, .comment-content figcaption, .comment-content .wp-caption-text, .widget_text p, .widget_text ol, .widget_text ul, .widget_text dl, .widget_text dt, .widget-content .rssSummary, .widget-content cite, .widget-content figcaption, .widget-content .wp-caption-text {
      font-family: Vazir, tahoma, sans-serif, arial;
  }
  select {
      font-family: Vazir, tahoma, sans-serif, arial;
  }
  .entry-content, .entry-content h1, .entry-content h2, .entry-content h3, .entry-content h4, .entry-content h5, .entry-content h6, .entry-content cite, .entry-content figcaption, .entry-content table, .entry-content address, .entry-content .wp-caption-text, .entry-content .wp-block-file {
      font-family: Vazir, tahoma, sans-serif, arial;
  }

  ';

$css .= '
  @media (min-width: 700px){
    h2.entry-title, h1.entry-title {
        font-size: 4.4rem;
    }
  }
  @media (min-width: 1220px) {
    h1, .heading-size-1 {
        font-size: 4.4rem;
    }
  }
  ';
