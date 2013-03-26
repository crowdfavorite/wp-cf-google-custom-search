<?php

// The mo file for this plugin has not been created yet
load_textdomain('cfgcse', '.');
class CFGoogleCustomSearchAdmin {

	public static function adminMenu() {
		add_submenu_page('options-general.php', __('CF Google Search', 'cfgcse'), __('CF Google Search', 'cfgcse'), 10, 'cf-google-custom-search', 'CFGoogleCustomSearchAdmin::adminPage');
	}

	public static function adminPage() {
		if (array_key_exists('cf_google_custom_search_save', $_POST)) {
			update_option('_cf_gcse_api_key', $_POST['cf_gcse_api_key']);
			update_option('_cf_gcse_engine_id', $_POST['cf_gcse_engine_id']);
			$results = intval($_POST['cf_gcse_num_results']);
			if (!$results) {
				$results = 10;
			}
			update_option('_cf_gcse_num_results', $results);
		}
		$api_key = get_option('_cf_gcse_api_key', null);
		$cse_id = get_option('_cf_gcse_engine_id', null);
		$results = intval(get_option('_cf_gcse_num_results', 10)); ?>
<h1><?php echo __('CF Google Custom Search Engine Configuration', 'cfgcse'); ?></h1>
<form action="" method="post" id="cf_google_custom_search_admin">
<table class="form-table">
	<tbody>
	<tr>
		<th scope="row"><label for="cf_gcse_api_key"><?php echo __('Google API Key', 'cfgcse'); ?>:</label></th>
		<td><input type="text" id="cf_gcse_api_key" name="cf_gcse_api_key" value="<?php echo $api_key; ?>" class="widefat" /></td>
	</tr>
	<tr>
		<th scope="row"><label for="cf_gcse_num_results"><?php echo __('Results Per Page', 'cfgcse'); ?> (1 - 10):</label></th>
		<td><input type="text" id="cf_gcse_num_results" name="cf_gcse_num_results" value="<?php echo $results; ?>" class="widefat" /></td>
	</tr>
	<tr>
		<th scope="row"><label for="cf_gcse_remote_id"><?php echo __('Search Engine ID', 'cfgcse'); ?>:</label></th>
		<td><input type="text" id="cf_gcse_engine_id" name="cf_gcse_engine_id" value="<?php echo $cse_id; ?>" class="widefat" /></td>
	</tr>
	<tr>
		<th><input type="submit" name="cf_google_custom_search_save" value="<?php echo __('Save', 'cfgcse'); ?>" class="button-primary" /></th>
		<td></td>
	</tr>
	</tbody>
</table>
</form>
<?php
	}

}

add_action('admin_menu', 'CFGoogleCustomSearchAdmin::adminMenu');
