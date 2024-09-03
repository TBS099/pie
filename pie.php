<?php

//Plugin Name: Pie
//Description: A simple pie categorization plugin
//Version: 1.0
//Author: Tanush Bikram Shah
//text domain: pie

if (!defined('ABSPATH')) {
    echo 'ACCESS DENIED';
    exit;
} else {
    /* css for plugin  */
    wp_enqueue_style('pie', plugin_dir_url(__FILE__) . 'assets/css/pie.css', array(), false, 'all');

    /* Loads required files */
    require_once('inc/pie-admin.php');
    require_once('inc/pie-shortcodes.php');
}
