import { fireEvent, render } from '@testing-library/svelte';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import ArticleEditor from '../article-editor/article-editor.svelte';

vi.mock('../../../api/client', () => ({
  api: {
    post: vi.fn(() => ({ json: async () => ({ id: '123', title: 'T' }) })),
    put: vi.fn(() => ({ json: async () => ({ id: '123', title: 'T' }) })),
  }
}));

import { api } from '../../../api/client';

describe('ArticleEditor', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('saves a new article via API and emits saved', async () => {
    const { getByPlaceholderText, getByText, component } = render(ArticleEditor, {});

    const titleInput = getByPlaceholderText('Article title');
    const contentTextarea = getByPlaceholderText('Write article content here...');

    await fireEvent.input(titleInput, { target: { value: 'My title' } });
    await fireEvent.input(contentTextarea, { target: { value: 'Content' } });

    const saved = vi.fn();
    component.$on('saved', saved);

    const button = getByText('Create');
    await fireEvent.click(button);

    expect(api.post).toHaveBeenCalled();
    expect(saved).toHaveBeenCalled();
  });

  it('updates existing article via API and emits saved', async () => {
    const article = { id: '1', title: 'Old', content: 'Old content' };
    const { getByText, component } = render(ArticleEditor, { article });

    const saved = vi.fn();
    component.$on('saved', saved);

    const button = getByText('Update');
    await fireEvent.click(button);

    expect(api.put).toHaveBeenCalled();
    expect(saved).toHaveBeenCalled();
  });
});
