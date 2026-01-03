import { describe, it, expect, render, screen } from '$lib/test-utils';
import { createMockMessage, createMockUser } from '$lib/test-utils/mocks';
import MessageBubble from '../MessageBubble.svelte';

describe('MessageBubble', () => {
  it('renders message content', () => {
    const message = createMockMessage({ 
      content: 'Hello, this is a test message'
    });
    
    render(MessageBubble, { props: { message } });
    
    expect(screen.getByText(/Hello, this is a test message/)).toBeInTheDocument();
  });
  
  it('displays sender name for incoming messages', () => {
    const sender = createMockUser({ name: 'John Doe' });
    const message = createMockMessage({ 
      sender,
      message_type: 1 // Incoming
    });
    
    render(MessageBubble, { props: { message, isOutgoing: false } });
    
    expect(screen.getByText('John Doe')).toBeInTheDocument();
  });
  
  it('hides sender name for outgoing messages', () => {
    const sender = createMockUser({ name: 'Agent Smith' });
    const message = createMockMessage({ 
      sender,
      message_type: 0 // Outgoing
    });
    
    render(MessageBubble, { props: { message, isOutgoing: true } });
    
    expect(screen.queryByText('Agent Smith')).not.toBeInTheDocument();
  });
  
  it('displays timestamp when created_at is provided', () => {
    const createdAt = Math.floor(Date.now() / 1000); // Unix timestamp in seconds
    const message = createMockMessage({ 
      created_at: createdAt
    });
    
    render(MessageBubble, { props: { message } });
    
    // Check that some time format is displayed (matches format like "10:30 AM")
    const { container } = render(MessageBubble, { props: { message } });
    const timeElements = container.querySelectorAll('.text-xs.text-muted-foreground');
    expect(timeElements.length).toBeGreaterThan(0);
  });
  
  it('applies correct styling for outgoing messages', () => {
    const message = createMockMessage({ 
      message_type: 0 // Outgoing
    });
    
    const { container } = render(MessageBubble, { props: { message, isOutgoing: true } });
    
    // Check for primary background color class
    const messageBubble = container.querySelector('.bg-primary');
    expect(messageBubble).toBeInTheDocument();
  });
  
  it('applies correct styling for incoming messages', () => {
    const message = createMockMessage({ 
      message_type: 1 // Incoming
    });
    
    const { container } = render(MessageBubble, { props: { message, isOutgoing: false } });
    
    // Check for muted background color class
    const messageBubble = container.querySelector('.bg-muted');
    expect(messageBubble).toBeInTheDocument();
  });
  
  it('displays avatar fallback with first letter of sender name', () => {
    const sender = createMockUser({ name: 'Alice' });
    const message = createMockMessage({ sender });
    
    render(MessageBubble, { props: { message } });
    
    // Should show 'A' as avatar fallback
    expect(screen.getByText('A')).toBeInTheDocument();
  });
  
  it('renders message with newlines as HTML breaks', () => {
    const message = createMockMessage({ 
      content: 'Line 1\nLine 2\nLine 3'
    });
    
    const { container } = render(MessageBubble, { props: { message } });
    
    // Check that br tags are present in the rendered HTML
    const messageContent = container.querySelector('.rounded-lg.p-3');
    expect(messageContent?.innerHTML).toContain('<br>');
  });
  
  it('displays image attachments', () => {
    const message = createMockMessage({ 
      content: 'Check this image',
      attachments: [
        {
          id: 1,
          file_type: 'image',
          file_name: 'test-image.jpg',
          data_url: 'https://example.com/image.jpg',
          file_size: 102400,
          accountId: 1,
          extension: 'jpg'
        }
      ]
    });
    
    const { container } = render(MessageBubble, { props: { message } });
    
    // Check for image element
    const image = container.querySelector('img[src="https://example.com/image.jpg"]');
    expect(image).toBeInTheDocument();
  });
  
  it('displays file attachments with download link', () => {
    const message = createMockMessage({ 
      content: 'File attached',
      attachments: [
        {
          id: 1,
          file_type: 'file',
          file_name: 'document.pdf',
          data_url: 'https://example.com/document.pdf',
          file_size: 204800, // 200 KB
          accountId: 1,
          extension: 'pdf'
        }
      ]
    });
    
    render(MessageBubble, { props: { message } });
    
    expect(screen.getByText('document.pdf')).toBeInTheDocument();
    expect(screen.getByText('200.0 KB')).toBeInTheDocument();
  });
  
  it('handles missing sender gracefully', () => {
    const message = createMockMessage({ 
      sender: null as any
    });
    
    render(MessageBubble, { props: { message, isOutgoing: false } });
    
    // Should display 'Unknown' for missing sender
    expect(screen.getByText('Unknown')).toBeInTheDocument();
  });
  
  it('handles invalid timestamp gracefully', () => {
    const message = createMockMessage({ 
      created_at: null as any
    });
    
    const { container } = render(MessageBubble, { props: { message } });
    
    // Should not crash, component should still render
    expect(container).toBeInTheDocument();
  });
});
