<script lang="ts">
  import { ContactList } from './index.js';
  export let Hst: any;

  const sampleContacts = [
    {
      id: '1',
      name: 'Alice Johnson',
      email: 'alice.johnson@example.com',
      phone: '+1 (555) 123-4567',
      avatar: '',
      company: 'Acme Corp',
      tags: ['vip', 'customer'],
      lastActivity: '2024-01-27T14:30:00Z',
      createdAt: '2024-01-15T10:00:00Z',
      conversationCount: 12,
      status: 'active' as const,
    },
    {
      id: '2',
      name: 'Bob Smith',
      email: 'bob.smith@techstart.io',
      phone: '+1 (555) 234-5678',
      company: 'TechStart',
      tags: ['lead', 'enterprise'],
      lastActivity: '2024-01-26T09:15:00Z',
      createdAt: '2024-01-20T08:00:00Z',
      conversationCount: 5,
      status: 'active' as const,
    },
    {
      id: '3',
      name: 'Carol Williams',
      email: 'carol.w@email.com',
      company: 'Design Studio',
      tags: ['customer'],
      lastActivity: '2024-01-25T16:45:00Z',
      createdAt: '2024-01-10T12:00:00Z',
      conversationCount: 18,
      status: 'active' as const,
    },
    {
      id: '4',
      name: 'David Brown',
      email: 'david.brown@startup.com',
      phone: '+1 (555) 456-7890',
      company: 'Innovation Labs',
      tags: ['lead', 'vip', 'tech'],
      lastActivity: '2024-01-15T11:00:00Z',
      createdAt: '2024-01-05T09:00:00Z',
      conversationCount: 3,
      status: 'inactive' as const,
    },
    {
      id: '5',
      name: 'Emma Davis',
      email: 'emma.d@company.org',
      company: 'Global Solutions',
      tags: ['customer', 'support'],
      lastActivity: '2024-01-27T10:00:00Z',
      createdAt: '2024-01-22T14:30:00Z',
      conversationCount: 8,
      status: 'active' as const,
    },
  ];
</script>

<Hst.Story title="Contact Management/Contact List" icon="lucide:users">
  <Hst.Variant title="Default">
    <div class="h-[700px] p-4">
      <ContactList
        contacts={sampleContacts}
        onContactSelect={(id) => console.log('Contact selected:', id)}
        onContactCreate={() => console.log('Create contact')}
        onBulkAction={(action, ids) => console.log('Bulk action:', action, ids)}
        onExport={() => console.log('Export contacts')}
        onFilterOpen={() => console.log('Open filters')}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Search">
    <div class="h-[700px] p-4">
      <ContactList
        contacts={sampleContacts}
        searchQuery="alice"
        onContactSelect={(id) => console.log('Contact selected:', id)}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Filtered by Tag">
    <div class="h-[700px] p-4">
      <ContactList
        contacts={sampleContacts}
        selectedTag="vip"
        onContactSelect={(id) => console.log('Contact selected:', id)}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Empty State">
    <div class="h-[700px] p-4">
      <ContactList
        contacts={[]}
        onContactCreate={() => console.log('Create contact')}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Sorted by Last Activity">
    <div class="h-[700px] p-4">
      <ContactList
        contacts={sampleContacts}
        sortBy="lastActivity"
        sortOrder="desc"
        onContactSelect={(id) => console.log('Contact selected:', id)}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
