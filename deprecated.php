<?php 
/**
 * This file includes items that are deprecated, and will eventually be removed.
 */

function cf_google_custom_search_form() {
	_deprecated_function(__FUNCTION__, '1.2.2', 'get_search_form()');
	return get_search_form(false);
}
?>