<?php

// The mo file for this plugin has not been created yet
load_textdomain('cfgcse', '.');
if (!class_exists('CFGoogleCustomSearchEngineEndpoint')) {
class CFGoogleCustomSearchEndpoint {
	private static $_result;

	static function onSearch() {
		global $wp_query;
		if (!get_query_var('s')) {
			return;
		}
		$page = intval(get_query_var('paged'));
		if ($page == 0) {
			$page = 1;
		}
		$search_term = get_query_var('s');
		$normalized_hash = md5(trim(strtolower($search_term.$page)));
		$result = json_decode(get_transient("_cf_gcse_result_{$normalized_hash}"));
		if (!$result) {
			// Request information from Google
			$api_key = get_option('_cf_gcse_api_key', null, true);
			$cse_id = get_option('_cf_gcse_engine_id', null, true);
			$results = intval(get_option('_cf_gcse_num_results', 10, true));
			if ($results > 10 || $results < 1) {
				$results = 10;
			}
			$offset = (($page-1)*$results)+1;
			/*if ($offset > (100-$results)) {
				$offset = 100-$results;
			}*/

			if (!($api_key && $cse_id)) {
				return;
			}

			$target = 'https://www.googleapis.com/customsearch/v1';

			$args = array(
				'key' => $api_key,
				'q' => $search_term,
				'num' => $results,
				'start' => $offset,
				'cx' => $cse_id,
				'filter' => 0,
			);

			$response = wp_remote_get(add_query_arg($args, $target), array('sslverify' => false));

			if ($response && $response['response']['code'] == 200) {
				$result = json_decode($response['body']);
				if (apply_filters('cf_gcse_unset_pagemap', true, $result)) {
					foreach ($result->items as &$item_record) {
						unset($item_record->pagemap);
					}
				}
				set_transient("_cf_gcse_result_{$normalized_hash}", json_encode($result));
			}
		}
		// We now have a valid v1 REST API resposne from Google to parse and generate code
		// Store it in the private member variable of the class to be processed on the display shortcode
		self::$_result = $result;
	}

	public static function onShortcode($atts) {
		global $paged;
		if ($paged == 0) {
			$paged = 1;
		}
		$next_page = $paged + 1;
		$prev_page = $paged - 1;
		// Get meta information about query
		$results = self::$_result;
		$request_data = $results->queries->request[0];
		// totalResults returned by google is inconsistent and buggy. Removing it for now.
		//$total_results = $request_data->totalResults;
		$start = $request_data->startIndex;
		$end = $start + $request_data->count - 1;
		$title = $request_data->title;
		$search_term = $request_data->searchTerms;

		$html = '';
		if ($results->items && !empty($results->items)) {
			// We have items to display
			$items_markup = '';
			foreach ($results->items as $item_record) {
				$item_link = apply_filters('cf_gcse_item_link', sprintf('<a class="search_item_link" href="%s">%s</a>', $item_record->link, $item_record->title), $item_record->link, $item_record->title, $item_record, $results);
				$item_snippet = apply_filters('cf_gcse_item_snippet', sprintf('<p class="search_item_snippet">%s</p>', $item_record->htmlSnippet), $item_record->htmlSnippet, $item_record, $results);
				$display_link = apply_filters('cf_gcse_item_display_link', sprintf('<p class="search_item_display_link">%s</p>', $item_record->displayLink), $item_record->displayLink, $item_record, $results);
				$item_content = apply_filters('cf_gcse_item_content_markup', $item_link.$item_snippet.$display_link, $item_record, $results);
				$items_markup .= apply_filters('cf_gcse_item_record_markup', "<li class=\"search_item_wrapper\">$item_content</li>", $item_content, $item_record, $results);
			}
			$list_markup = apply_filters('cf_gcse_result_list_markup', "<ol class=\"search_results_wrapper\">$items_markup</ol>", $items_markup, $results);
			$results_desc = apply_filters('cf_gcse_results_desc_markup', "<h4 class=\"search_results_description\">".__('Showing results', 'cfgcse')." $start - $end ".__('for search', 'cfgcse')." '$search_term'</h4>", $start, $end, $search_term, $results);
			$title_markup = apply_filters('cf_gcse_results_title_markup', '<h3 class="search_results_title">'.__('Search Results', 'cfgcse').'</h3>', $results);
			$next_link = '';
			if ($results->queries->nextPage) {
				$next_link = apply_filters('cf_gcse_results_next_page_link', '<a class="search_results_next_page" href="'.get_pagenum_link($next_page).'">Next Page &gt;</a>', $end, $total_results, $results);
			}
			$prev_link = '';
			if ($results->queries->previousPage) {
				$prev_link = apply_filters('cf_gcse_results_prev_page_link', '<a class="search_results_prev_page" href="'.get_pagenum_link($prev_page).'">&lt; Previous Page</a>', $start, $total_results, $results);
			}
			$html = apply_filters('cf_gcse_results_markup', "<div id=\"cf_gcse_search_results\">{$title_markup}{$results_desc}{$list_markup}{$prev_link}{$next_link}</div>", $title_markup, $results_desc, $list_markup, $results);
		}
		else {
			$html = apply_filters('cf_gcse_no_results_markup',
				apply_filters('cf_gcse_no_results_title', '<h3>'.__('Search Results', 'cfgcse').'</h3>', $results).
				apply_filters('cf_gcse_no_results_message', "<p>".__('Sorry, no results were found for', 'cfgcse')." '$search_term'</p>", $search_term, $results),
				$results
			);
		}
		echo $html;
	}

}

add_action('template_redirect', 'CFGoogleCustomSearchEndpoint::onSearch');
add_shortcode('cf-google-search-results', 'CFGoogleCustomSearchEndpoint::onShortcode');

}
