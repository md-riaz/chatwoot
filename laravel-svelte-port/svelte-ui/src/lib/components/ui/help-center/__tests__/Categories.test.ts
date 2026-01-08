import { fireEvent, render } from '@testing-library/svelte';
import { describe, expect, it, vi } from 'vitest';
import Categories from '../categories/categories.svelte';

describe('Categories component', () => {
  const sample = [
    { id: 'c1', name: 'One', description: '', color: '#3B82F6', articleCount: 1, order: 0 },
    { id: 'c2', name: 'Two', description: '', color: '#10B981', articleCount: 2, order: 1 }
  ];

  it('calls onCreate when creating a category', async () => {
    const onCreate = vi.fn();
    const { getByText, getByLabelText } = render(Categories, { categories: sample, onCreate });

    await fireEvent.click(getByText('New Category'));

    const name = getByLabelText('Name*');
    await fireEvent.input(name, { target: { value: 'New Cat' } });

    await fireEvent.click(getByText('Create'));

    expect(onCreate).toHaveBeenCalled();
  });

  it('calls onUpdate when editing a category', async () => {
    const onUpdate = vi.fn();
    const { getAllByText, getByLabelText } = render(Categories, { categories: sample, onUpdate });

    await fireEvent.click(getAllByText('Edit')[0]);

    const name = getByLabelText('Name*');
    await fireEvent.input(name, { target: { value: 'Updated' } });

    await fireEvent.click(getAllByText('Save')[0]);

    expect(onUpdate).toHaveBeenCalled();
  });

  it('calls onDelete when deleting a category', async () => {
    const onDelete = vi.fn();
    // stub confirm
    const original = global.confirm;
    // @ts-ignore
    global.confirm = () => true;

    const { getAllByText } = render(Categories, { categories: sample, onDelete });

    await fireEvent.click(getAllByText('Delete')[0]);

    expect(onDelete).toHaveBeenCalled();

    global.confirm = original;
  });

  it('reorders categories via moveItem helper when dropped', async () => {
    // basic sanity: import and test moveItem separately
    const { moveItem } = await import('../categories/dnd-utils');
    const arr = ['a', 'b', 'c'];
    const res = moveItem(arr, 0, 2);
    expect(res).toEqual(['b', 'c', 'a']);
  });
});
