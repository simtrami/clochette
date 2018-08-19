var search = instantsearch({
    // Replace with your own values
    appId: '1ZO7CPQOBJ',
    apiKey: '7fe454f3c66329bdf0687b9586b350e7', // search only API key, no ADMIN key
    indexName: 'dev_comptes',
    urlSync: true,
    searchParameters: {
        hitsPerPage: 10
    }
});

search.addWidget(
    instantsearch.widgets.searchBox({
        container: '#search-input'
    })
);

search.addWidget(
    instantsearch.widgets.hits({
        container: '#hits',
        templates: {
            item: document.getElementById('hit-template').innerHTML,
            empty: "Il n'y a pas de compte associé à la recherche : <em>\"{{query}}\"</em>"
        }
    })
);

search.addWidget(
  instantsearch.widgets.pagination({
    container: '#pagination'
  })
);

search.addWidget(
  instantsearch.widgets.sortBySelector({
    container: '#sort-by',
    autoHideContainer: true,
    indices: [
      {
        name: 'comptes',
        label: 'Most relevant'
      },
      {
        name: 'comptes_pseudo_asc',
        label: 'Pseudos A-Z'
      }
    ]
  })
);

search.start();
