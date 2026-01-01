<script lang="ts">
  import * as Accordion from './index.js';
  
  export let Hst: any;
  
  const faqItems = [
    {
      question: "What is Chatwoot?",
      answer: "Chatwoot is an open-source customer engagement suite that helps businesses manage conversations across multiple channels."
    },
    {
      question: "How do I set up an inbox?",
      answer: "Go to Settings > Inboxes and click 'Add Inbox'. Choose your channel type and follow the setup wizard."
    },
    {
      question: "Can I use Chatwoot with my existing CRM?",
      answer: "Yes, Chatwoot supports integrations with popular CRMs through webhooks and API."
    },
    {
      question: "Is there a mobile app?",
      answer: "Yes, Chatwoot has mobile apps available for both iOS and Android."
    }
  ];
</script>

<Hst.Story title="Components/Accordion" icon="lucide:chevrons-down">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-md">
      <Accordion.Root type="single" collapsible>
        <Accordion.Item value="item-1">
          <Accordion.Trigger>Is it accessible?</Accordion.Trigger>
          <Accordion.Content>
            Yes. It adheres to the WAI-ARIA design pattern.
          </Accordion.Content>
        </Accordion.Item>
        <Accordion.Item value="item-2">
          <Accordion.Trigger>Is it styled?</Accordion.Trigger>
          <Accordion.Content>
            Yes. It comes with default styles that matches the other components' aesthetic.
          </Accordion.Content>
        </Accordion.Item>
        <Accordion.Item value="item-3">
          <Accordion.Trigger>Is it animated?</Accordion.Trigger>
          <Accordion.Content>
            Yes. It's animated by default, but you can disable it if you prefer.
          </Accordion.Content>
        </Accordion.Item>
      </Accordion.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="FAQ">
    <div class="p-4 max-w-lg">
      <h3 class="text-lg font-semibold mb-4">Frequently Asked Questions</h3>
      <Accordion.Root type="single" collapsible>
        {#each faqItems as item, i}
          <Accordion.Item value="faq-{i}">
            <Accordion.Trigger>{item.question}</Accordion.Trigger>
            <Accordion.Content>
              <p class="text-muted-foreground">{item.answer}</p>
            </Accordion.Content>
          </Accordion.Item>
        {/each}
      </Accordion.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Multiple Open">
    <div class="p-4 max-w-md">
      <p class="text-sm text-muted-foreground mb-4">Multiple items can be open at once.</p>
      <Accordion.Root type="multiple">
        <Accordion.Item value="section-1">
          <Accordion.Trigger>Section 1</Accordion.Trigger>
          <Accordion.Content>
            Content for section 1. This can contain any content you want.
          </Accordion.Content>
        </Accordion.Item>
        <Accordion.Item value="section-2">
          <Accordion.Trigger>Section 2</Accordion.Trigger>
          <Accordion.Content>
            Content for section 2. Multiple sections can be open simultaneously.
          </Accordion.Content>
        </Accordion.Item>
        <Accordion.Item value="section-3">
          <Accordion.Trigger>Section 3</Accordion.Trigger>
          <Accordion.Content>
            Content for section 3. Try opening all three at once!
          </Accordion.Content>
        </Accordion.Item>
      </Accordion.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Settings Sections">
    <div class="p-4 max-w-lg">
      <Accordion.Root type="single" collapsible>
        <Accordion.Item value="profile">
          <Accordion.Trigger>
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Profile Settings
            </div>
          </Accordion.Trigger>
          <Accordion.Content>
            <p class="text-muted-foreground">Manage your profile information, avatar, and personal details.</p>
          </Accordion.Content>
        </Accordion.Item>
        <Accordion.Item value="notifications">
          <Accordion.Trigger>
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              Notifications
            </div>
          </Accordion.Trigger>
          <Accordion.Content>
            <p class="text-muted-foreground">Configure email, push, and in-app notification preferences.</p>
          </Accordion.Content>
        </Accordion.Item>
        <Accordion.Item value="security">
          <Accordion.Trigger>
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Security
            </div>
          </Accordion.Trigger>
          <Accordion.Content>
            <p class="text-muted-foreground">Manage passwords, two-factor authentication, and security settings.</p>
          </Accordion.Content>
        </Accordion.Item>
      </Accordion.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
