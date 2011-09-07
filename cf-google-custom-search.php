<?php
/* 
Plugin Name: CF Google Custom Search
Plugin URI:
Description: Utilize Google's Custom Search API instead of WordPress' search functionality.
Version: 1.3
Author: Crowd Favorite
Author URI: http://crowdfavorite.com/
*/

// Enqueue the styles in the plugin
wp_register_style('cf_google_custom_search', plugins_url('cf-google-custom-search.css', __FILE__));
wp_enqueue_style('cf_google_custom_search');

/**
 * Output the HTML and JavaScript that does the Google Search
 *
 * @return string 
 */
function cf_get_google_search_form(){
	// Get the parent element that the JavaScript uses
	$parent_id = get_option('cf_google_search_parent');
	if (!$parent_id) {
		$parent_id = 'searchcontrol';
	}
	
	// Bring in the Google Search JavaScript API
	$google_search_script = '<script type="text/javascript" src="http://www.google.com/jsapi"></script>';
	
	/* Get config and replace necessary items (domain and search terms) */
	$google_search_config = 
		'<script type="text/javascript">'.
			apply_filters('cf_google_search_config', get_option('cf_google_search_config')).
		'</script>';

	return '<div id="'.esc_attr($parent_id).'"></div>'.$google_search_script.$google_search_config;
}
add_shortcode('cf_google_custom_search_results', 'cf_get_google_search_form');

function cf_google_custom_search_admin_form() {
	$updated_string = '';
	if (isset($_POST['cf_action']) && $_POST['cf_action'] == 'save_google_search_info') {
		if (!check_admin_referer('cfgcs', 'cfgcs_settings_nonce')) {
			wp_die('You should not be here.');
		}
		update_option('cf_google_search_config', stripslashes_deep(trim($_POST['google_search_config'])));
		update_option('cf_google_search_domain', stripslashes_deep(trim($_POST['google_search_domain'])));
		update_option('cf_google_search_parent', stripslashes_deep(trim($_POST['google_search_parent'])));
		$updated_string = '<div class="updated fade" id="message" style="background-color: rgb(255, 251, 204);"><p><strong>Settings saved.</strong></p></div>';
	}
	$google_search_domain = get_option('cf_google_search_domain');
	$google_search_parent = get_option('cf_google_search_parent');
	$google_search_config = get_option('cf_google_search_config');
	
	// Default notes' HTML
	$notes = '';
	?>
	<div class="wrap">
		<?php echo $updated_string; ?>
		<h2>Google Custom Search Admin</h2>
		<form method="post">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="google_search_domain">Google Search Domain<br />(default "<?php echo esc_html($_SERVER['SERVER_NAME']); ?>")</label></th>
						<td><input type="text" name="google_search_domain" value="<?php echo esc_attr($google_search_domain); ?>" id="google_search_domain" style="width: 500px;"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="google_search_parent">Results Parent Element ID<br />(default "searchcontrol")</label></th>
						<td><input type="text" name="google_search_parent" vlaue="<?php echo esc_attr($google_search_parent); ?>" id="google_search_parent" style="width: 500px;"/></td>
					</tr>
					<tr>
						<th scope="row"><label for="google_search_config">Custom Google Search Script<br />(Advanced Use Only)<?php echo apply_filters('cf_google_search_custom_notes', $notes); ?></label></th>
						<td><textarea name="google_search_config" id="google_search_config" cols="100" rows="20"><?php echo esc_textarea($google_search_config); ?></textarea><br /></td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="hidden" name="cf_action" value="save_google_search_info" id="cf_action">
				<?php echo wp_nonce_field('cfgcs', 'cfgcs_settings_nonce', true, false).wp_referer_field(false); ?>
				<input class="button-primary" type="submit" value="Save Changes" name="Submit"/>
			</p>
		</form>
	</div>
	<?php
}

function cf_add_custom_search_menu_item() {
	add_options_page(
		'CF Google Custom Search',
		'CF Google Search',
		'manage_options',
		'cf_google_custom_search',
		'cf_google_custom_search_admin_form'
	);
}
add_action('admin_menu', 'cf_add_custom_search_menu_item');

/**
 * Output the configuration JavaScript code, with proper 
 * replacements for the domain, parent element id, and 
 * search terms
 *
 * @param string $config 
 * @return string
 */
function cf_custom_google_search_get_config_script($config) {
	if (!$config) {
		$config =
/** Default config script **/
'google.load("search", "1");

function OnLoad() {
	var searchControl = new google.search.SearchControl();
	options = new google.search.SearcherOptions();
	options.setExpandMode(google.search.SearchControl.EXPAND_MODE_OPEN);
	var thisDomainSearch = new google.search.WebSearch();
	thisDomainSearch.setUserDefinedLabel("###DOMAIN###");
	thisDomainSearch.setSiteRestriction("###DOMAIN###");
	searchControl.addSearcher(thisDomainSearch, options);
	searchControl.draw(document.getElementById("###PARENT###"));
	searchControl.setResultSetSize(google.search.Search.LARGE_RESULTSET);
	searchControl.execute("###SEARCH_TERMS###");
}
google.setOnLoadCallback(OnLoad);';
/** End default config script **/
	}
	$domain = get_option('cf_google_search_domain');
	$domain = $domain ? $domain : $_SERVER['SERVER_NAME'];
	$parent_div = get_option('cf_google_search_parent');
	$parent_div = $parent_div ? $parent_div : 'searchcontrol';
	
	$config = str_replace('###SEARCH_TERMS###', esc_js(get_search_query()), $config);
	$config = str_replace('###DOMAIN###', esc_js($domain), $config);
	$config = str_replace('###PARENT###', esc_js($parent_div), $config);
	return $config;
}
add_filter('cf_google_search_config', 'cf_custom_google_search_get_config_script');

/**
 * Adds some help text to the admin settings page
 *
 * @param string $note 
 * @return void
 */
function cf_add_google_search_custom_notes($note = '') {
	$note = '
		<div style="
			background:#f1f1f1; 
			border:1px solid #dfdfdf;
			-moz-border-radius:4px;
			-webkit-border-radius:4px;
			-khtml-border-radius:4px;
			border-radius:4px;
			padding:8px;
			margin: 10px 0px;
			">
			Custom Variables:
			   <p><code>###DOMAIN###</code><br />Search domain variable</p>
			   <p><code>###SEARCH_TERMS###</code><br />Terms passed in through the form</p>
			   <p><code>###PARENT###</code><br />The parent element ID for search results</p>
		</div>';
	return $note;
}
add_filter('cf_google_search_custom_notes', 'cf_add_google_search_custom_notes');


/**
 * No need to trigger WordPress search since Google Custom Search is being used
 * (props VIP support)
 * 
 * @param string $where 
 * @param object $query 
 * @return string
 */
function cf_google_search_kill_wp_search( $where, $query ) {
	if(!is_admin() && $query->is_search()) {
	    $where = ' AND 1=0';
	}
	
	// Only need to do this for the main query
	remove_filter('posts_where', 'cf_google_search_kill_wp_search', 10, 2);
	
	return $where;
}
add_filter('posts_where', 'cf_google_search_kill_wp_search', 10, 2);

/**
 * Keeping this function and shortcode around for backwards compat.  Really 
 * shouldn't be used though.
 *
 * @param array $atts 
 * @return string - HTML
 */
function cf_google_custom_search_shortcode($atts = array()) {
	return get_search_form(false);
}
add_shortcode('cf_google_custom_search', 'cf_google_custom_search_shortcode');
?>