## CF Custom Google Search

The CF Custom Google Search plugin allows a site owner to replace the default WordPress search functionality with Google Site Search functionality. Use of this plugin does require a configured Google Site Search account and API key to function.

### Usage

The CF Custom Google Search admin page allows for the configuration of the Google Site Search code. Only the Google API key is required for the function to work. Other fields have default values, and will use those values if left empty.

The Custom Script field allows the user to write the complete script used to populate the search results. This will replace the default script, and should only be modified by users with familiarity interacting with the Google Site Search API.

When this plugin is activated, it will apply a filter to the 'get_search_form' action, replacing that content with a Google Site Search interface instead.

### Shortcodes

The CF Custom Google Search plugin also provides shortcodes, both for the single form and for the full search form with responses.

To display the simple form, add:

[cf_google_custom_search]

And to add a full result set with search bar, add:

[cf_google_custom_search_results]
