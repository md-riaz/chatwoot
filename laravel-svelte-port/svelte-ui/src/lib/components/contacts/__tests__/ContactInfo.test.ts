import { describe, it, expect, render, screen } from '$lib/test-utils';
import { createMockContact } from '$lib/test-utils/mocks';
import ContactInfo from '../ContactInfo.svelte';

describe('ContactInfo', () => {
  it('renders contact name', () => {
    const contact = createMockContact({ name: 'John Smith' });
    
    render(ContactInfo, { props: { contact } });
    
    expect(screen.getByText('John Smith')).toBeInTheDocument();
  });
  
  it('displays avatar fallback with first letter', () => {
    const contact = createMockContact({ name: 'Alice' });
    
    render(ContactInfo, { props: { contact } });
    
    expect(screen.getByText('A')).toBeInTheDocument();
  });
  
  it('shows "Unknown" for contacts without name', () => {
    const contact = createMockContact({ name: '' });
    
    render(ContactInfo, { props: { contact } });
    
    expect(screen.getByText('Unknown')).toBeInTheDocument();
  });
  
  it('displays availability status when showStatus is true', () => {
    const contact = createMockContact({ 
      name: 'Bob',
      availability_status: 'online'
    });
    
    render(ContactInfo, { props: { contact, showStatus: true } });
    
    expect(screen.getByText('online')).toBeInTheDocument();
  });
  
  it('hides availability status when showStatus is false', () => {
    const contact = createMockContact({ 
      name: 'Bob',
      availability_status: 'online'
    });
    
    render(ContactInfo, { props: { contact, showStatus: false } });
    
    expect(screen.queryByText('online')).not.toBeInTheDocument();
  });
  
  it('applies small size class when size="sm"', () => {
    const contact = createMockContact();
    
    const { container } = render(ContactInfo, { props: { contact, size: 'sm' } });
    
    // Check for small avatar class
    const avatar = container.querySelector('.h-8.w-8');
    expect(avatar).toBeInTheDocument();
  });
  
  it('applies medium size class when size="md" (default)', () => {
    const contact = createMockContact();
    
    const { container } = render(ContactInfo, { props: { contact } });
    
    // Check for medium avatar class
    const avatar = container.querySelector('.h-10.w-10');
    expect(avatar).toBeInTheDocument();
  });
  
  it('applies large size class when size="lg"', () => {
    const contact = createMockContact();
    
    const { container } = render(ContactInfo, { props: { contact, size: 'lg' } });
    
    // Check for large avatar class
    const avatar = container.querySelector('.h-12.w-12');
    expect(avatar).toBeInTheDocument();
  });
  
  it('truncates long contact names', () => {
    const contact = createMockContact({ 
      name: 'Very Long Contact Name That Should Be Truncated'
    });
    
    const { container } = render(ContactInfo, { props: { contact } });
    
    // Check for truncate class
    const nameElement = container.querySelector('.truncate');
    expect(nameElement).toBeInTheDocument();
    expect(nameElement?.textContent).toBe('Very Long Contact Name That Should Be Truncated');
  });
  
  it('renders without status badge when availability_status is null', () => {
    const contact = createMockContact({ 
      name: 'John',
      availability_status: undefined
    });
    
    const { container } = render(ContactInfo, { props: { contact, showStatus: true } });
    
    // Should not render badge
    const badge = container.querySelector('.text-xs.mt-1');
    expect(badge).not.toBeInTheDocument();
  });
});
