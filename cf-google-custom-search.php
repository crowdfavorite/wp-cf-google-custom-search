<?php
/* 
Plugin Name: CF Google Custom Search
Plugin URI:
Description: Utilize Google's Custom Search API instead of WordPress' search functionality.
Version: 1.2.2
Author: Crowd Favorite
Author URI: http://crowdfavorite.com/
*/

/*******************************************************
-------------------------------------
EXAMPLE ADMIN CONFIGURATION
-------------------------------------
google.load("search", "1");

function OnLoad() {
	// Create a search control
	var searchControl = new google.search.SearchControl();

	// Have the search results display more than one initially
	options = new google.search.SearcherOptions();
	options.setExpandMode(google.search.SearchControl.EXPAND_MODE_OPEN);

	// Add this domain as separate search
	// ###DOMAIN### is a variable that will be swapped out with the current
	//    domain.  You can manually put a domain in here if you want instead.
	var thisDomainSearch = new google.search.WebSearch();
	thisDomainSearch.setUserDefinedLabel("###DOMAIN###");
	thisDomainSearch.setSiteRestriction("###DOMAIN###");
	searchControl.addSearcher(thisDomainSearch, options);

	// This second 'searcher' is completely optional.....
	// Instead of a domain, add a Google Custom Search Engine as another
	//   place to search (handy if you need to query more than 1 domain)
	var allDomainSearch = new google.search.WebSearch();
	allDomainSearch.setUserDefinedLabel("example.com");
	allDomainSearch.setSiteRestriction("example.com");
	searchControl.addSearcher(allDomainSearch, options);

	// Tell the searcher to draw itself and tell it where to attach
	searchControl.draw(document.getElementById("searchcontrol"));

	// Tell the search control how many items to return
	searchControl.setResultSetSize(google.search.Search.LARGE_RESULTSET);

	// Execute an inital search
	// ###SEARCH_TERMS### is a variable that will be replaced by 
	//    WordPress' search terms.  If you needed some hard-coded search
	//    term, you could put it there.
	searchControl.execute("###SEARCH_TERMS###");
}
google.setOnLoadCallback(OnLoad);
*******************************************************/
wp_register_style('cf_google_custom_search', plugins_url('cf-google-custom-search.css', __FILE__));
wp_enqueue_style('cf_google_custom_search');
function cf_add_google_custom_search($form){
	$form = cf_google_custom_search_form();
	return $form;
}
add_filter('get_search_form', 'cf_add_google_custom_search', 100);

function cf_google_custom_search_shortcode($atts) {
	return cf_google_custom_search_form();
}
add_shortcode('cf_google_custom_search', 'cf_google_custom_search_shortcode');

function cf_google_custom_search_form() {
	$form = '
	<form id="searchform" class="google-custom-search-form" action="'.esc_url(site_url('/')).'" method="post">
		<div>
			<input id="s" type="text" name="s" class="cf_google_search_terms" size="20" />
			<button id="searchsubmit" type="submit" name="submit_button" class="submit_button">Search</button>
			<input type="hidden" name="cf_action" value="do_google_search" />
		</div>
	</form>';
	return $form;
}

function cf_add_google_search_results_page() {
	/* Only do this page population if we're really searching for something */
	if (isset($_POST['cf_action']) && $_POST['cf_action'] == 'do_google_search') {
		if (function_exists('cfct_template_file')) {
			cfct_template_file('posts', 'search');
			exit;
		}
	}
}
add_action('template_redirect', 'cf_add_google_search_results_page', 5);

function cf_get_google_search_form(){
	$google_search_config = get_option('cf_google_search_config');
	$parent_id = get_option('cf_google_search_parent');
	if (!$parent_id) {
		$parent_id = 'searchcontrol';
	}
	$google_search_script = '<script type="text/javascript" src="http://www.google.com/jsapi"></script>';
	
	/* Get config and replace necessary items (domain and search terms) */
	$google_search_config = '<script type="text/javascript">'.apply_filters('cf_google_search_config', $google_search_config).'</script>';
	return '<div id="'.esc_attr($parent_id).'"></div>'.$google_search_script.$google_search_config;
}
add_shortcode('cf_google_custom_search_results', 'cf_get_google_search_form');

function cf_google_custom_search_admin_form() {
	$updated_string = '';
	if (isset($_POST['cf_action']) && $_POST['cf_action'] == 'save_google_search_info') {
		if (!check_admin_referer('cfgcs', 'cfgcs_settings_nonce')) {
			die();
		}
		update_option('cf_google_search_config', stripslashes_deep(trim($_POST['google_search_config'])));
		update_option('cf_google_search_domain', stripslashes_deep(trim($_POST['google_search_domain'])));
		update_option('cf_google_search_parent', stripslashes_deep(trim($_POST['google_search_parent'])));
		$updated_string = '<div class="updated fade" id="message" style="background-color: rgb(255, 251, 204);"><p><strong>Settings saved.</strong></p></div>';
	}
	$google_search_domain = get_option('cf_google_search_domain');
	$google_search_parent = get_option('cf_google_search_parent');
	$google_search_config = get_option('cf_google_search_config');
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
	
	$config = str_replace('###SEARCH_TERMS###', esc_js($_POST['s']), $config);
	$config = str_replace('###DOMAIN###', esc_js($domain), $config);
	$config = str_replace('###PARENT###', esc_js($parent_div), $config);
	return $config;
}
add_filter('cf_google_search_config', 'cf_custom_google_search_get_config_script');

function cf_add_google_search_custom_notes($note) {
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
?>
