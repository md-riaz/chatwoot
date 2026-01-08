<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { Copilot } from './index';

  const supportAgent = {
    available_name: 'John Doe',
    avatar_url: 'https://i.pravatar.cc/300?u=agent1',
  };

  let messages = $state([
    {
      id: 1,
      role: 'user' as const,
      content: 'Hi there! How can I help you today?',
    },
    {
      id: 2,
      role: 'assistant' as const,
      content: "Hello! I'm the AI assistant. I'll be helping the support team today.",
    },
  ]);

  let isCaptainTyping = $state(false);

  function handleSendMessage(message: string) {
    // Add user message
    messages = [
      ...messages,
      {
        id: messages.length + 1,
        role: 'user' as const,
        content: message,
      },
    ];

    // Simulate AI response
    isCaptainTyping = true;
    setTimeout(() => {
      isCaptainTyping = false;
      messages = [
        ...messages,
        {
          id: messages.length + 1,
          role: 'assistant' as const,
          content: 'This is a simulated AI response. In production, this would be powered by your AI backend.',
        },
      ];
    }, 2000);
  }
</script>

<Hst.Story title="Captain/Copilot" icon="lucide:bot">
  <Hst.Variant title="Interactive Copilot">
    <div class="p-4 bg-background h-[600px]">
      <Copilot
        {supportAgent}
        {messages}
        {isCaptainTyping}
        onSendMessage={handleSendMessage}
        class="h-full"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Empty State">
    <div class="p-4 bg-background h-[600px]">
      <Copilot
        {supportAgent}
        messages={[]}
        isCaptainTyping={false}
        onSendMessage={handleSendMessage}
        class="h-full"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Typing State">
    <div class="p-4 bg-background h-[600px]">
      <Copilot
        {supportAgent}
        messages={[
          { id: 1, role: 'user', content: 'What are the common issues customers face?' }
        ]}
        isCaptainTyping={true}
        onSendMessage={handleSendMessage}
        class="h-full"
      />
    </div>
  </Hst.Variant>
</Hst.Story>
