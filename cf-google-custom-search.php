<?php
/* 
Plugin Name: CF Google Custom Search
Plugin URI:
Description: Utilize Google's Custom Search API instead of WordPress' search functionality.
Version: 2.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com/
*/

//load_plugin_domain('cfgcse');

include dirname(__file__).'/models/CFGoogleCustomSearchEndpoint.php';

if (is_admin()) {
include dirname(__file__).'/models/CFGoogleCustomSearchAdmin.php';
}

wp_enqueue_style('cf-gcse-stylesheet', plugins_url(basename(dirname(__file__))).'/css/cf-google-custom-search.css');

?>