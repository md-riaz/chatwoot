<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'q' => 'required|string|min:2|max:255',
            'type' => 'sometimes|string|in:all,conversations,contacts,messages,articles',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|string|in:created_at,updated_at,relevance',
            'sort_order' => 'sometimes|string|in:asc,desc',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'q.required' => 'Search query is required.',
            'q.min' => 'Search query must be at least 2 characters.',
            'q.max' => 'Search query cannot exceed 255 characters.',
            'type.in' => 'Search type must be one of: all, conversations, contacts, messages, articles.',
            'per_page.max' => 'Results per page cannot exceed 100.',
            'sort_by.in' => 'Sort field must be one of: created_at, updated_at, relevance.',
            'sort_order.in' => 'Sort order must be either asc or desc.',
        ];
    }

    /**
     * Get the search query.
     */
    public function getQuery(): string
    {
        return $this->validated('q');
    }

    /**
     * Get the search type.
     */
    public function getType(): string
    {
        return $this->validated('type', 'all');
    }

    /**
     * Get the page number.
     */
    public function getPage(): int
    {
        return $this->validated('page', 1);
    }

    /**
     * Get the number of results per page.
     */
    public function getPerPage(): int
    {
        return $this->validated('per_page', 15);
    }

    /**
     * Get the sort field.
     */
    public function getSortBy(): string
    {
        return $this->validated('sort_by', 'created_at');
    }

    /**
     * Get the sort order.
     */
    public function getSortOrder(): string
    {
        return $this->validated('sort_order', 'desc');
    }
}