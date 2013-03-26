## CF Custom Google Search

The CF Custom Google Search plugin allows a site owner to replace the default WordPress search functionality with Google Site Search functionality. Use of this plugin does require a configured [Google Custom Search](http://www.google.com/cse/) Engine (likely a Google Site Search account to raise the API limits) and [API key](https://code.google.com/apis/console/) to function.

### Usage
The CF Custom Google Search admin page allows for the configuration of the Google Search Engine and API key setup. Search Engine ID and API key are required fields.

To display results on the page, edit your search.php template in the theme, and add in a call to use the shortcode "cf-google-search-results". This will get the results' markup from the plugin.


#### EXAMPLE Template File

	<?php do_shortcode('[cf-google-search-results]'); ?>
