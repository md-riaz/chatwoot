<script lang="ts">
  import { Search } from './index.js';
  export let Hst: any;

  const searchResults = [
    {
      id: '1',
      title: 'Getting Started with Chatwoot',
      content: 'Learn how to set up your first inbox and start receiving messages from your customers...',
      category: 'Getting Started',
      locale: 'en',
      relevance: 0.95
    },
    {
      id: '2',
      title: 'How to start a conversation',
      content: 'Starting conversations is easy. Click the new conversation button and select a customer...',
      category: 'Features',
      locale: 'en',
      relevance: 0.87
    },
    {
      id: '3',
      title: 'Getting help when you need it',
      content: 'Our support team is here to help. Contact us through the chat widget or email support@chatwoot.com...',
      category: 'Support',
      locale: 'en',
      relevance: 0.72
    }
  ];

  const recentSearches = ['inbox setup', 'automation', 'billing', 'api integration'];
  const categories = ['Getting Started', 'Features', 'Billing', 'Troubleshooting', 'API'];
  const locales = ['en', 'es', 'fr', 'de'];
</script>

<Hst.Story title="Help Center/Search" icon="lucide:search">
  <Hst.Variant title="With Results">
    <Search
      results={searchResults}
      {recentSearches}
      {categories}
      {locales}
      onSearch={(query, filters) => console.log('Search:', query, filters)}
      onResultClick={(id) => console.log('Result clicked:', id)}
    />
  </Hst.Variant>

  <Hst.Variant title="No Results">
    <Search
      results={[]}
      {recentSearches}
      {categories}
      {locales}
      onSearch={(query, filters) => console.log('Search:', query, filters)}
      onResultClick={(id) => console.log('Result clicked:', id)}
    />
  </Hst.Variant>

  <Hst.Variant title="Recent Searches">
    <Search
      results={[]}
      {recentSearches}
      {categories}
      {locales}
      onSearch={(query, filters) => console.log('Search:', query, filters)}
      onResultClick={(id) => console.log('Result clicked:', id)}
    />
  </Hst.Variant>

  <Hst.Variant title="Searching State">
    <Search
      results={[]}
      {recentSearches}
      {categories}
      {locales}
      searching={true}
      onSearch={(query, filters) => console.log('Search:', query, filters)}
      onResultClick={(id) => console.log('Result clicked:', id)}
    />
  </Hst.Variant>

  <Hst.Variant title="Single Result">
    <Search
      results={[searchResults[0]]}
      {recentSearches}
      {categories}
      {locales}
      onSearch={(query, filters) => console.log('Search:', query, filters)}
      onResultClick={(id) => console.log('Result clicked:', id)}
    />
  </Hst.Variant>
</Hst.Story>
