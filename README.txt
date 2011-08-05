## CF Custom Google Search

The CF Custom Google Search plugin allows a site owner to replace the default WordPress search functionality with Google Site Search functionality. Use of this plugin does require a configured Google Site Search account and API key to function.

### Usage

The CF Custom Google Search admin page allows for the configuration of the Google Site Search code. Only the Google API key is required for the function to work. Other fields have default values, and will use those values if left empty.

The Custom Script field allows the user to write the complete script used to populate the search results. This will replace the default script, and should only be modified by users with experience customizing the Google Site Search API.


#### EXAMPLE ADMIN CONFIGURATION

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