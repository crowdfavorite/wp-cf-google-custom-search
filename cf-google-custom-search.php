<?php
/* 
Plugin Name: CF Google Custom Search
Plugin URI:
Description: Utilize Google's Custom Search API instead of WordPress' search functionality.
Version: 2.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com/
*/

// A mo file has not been generated for this plugin yet
load_textdomain('cfgcse', '');
define('CF_GCSE_URL', apply_filters('cf_gcse_url', plugins_url(basename(dirname(__file__))), basename(dirname(__file__))));
include dirname(__file__).'/models/CFGoogleCustomSearchEndpoint.php';

if (is_admin()) {
include dirname(__file__).'/models/CFGoogleCustomSearchAdmin.php';
}

wp_enqueue_style('cf-gcse-stylesheet', apply_filters('cf-gcse-stylesheet-url', CF_GCSE_URL.'/css/cf-google-custom-search.css'), basename(dirname(__file__)));

?>
