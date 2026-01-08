<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records.
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a record by ID.
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find a record by ID or throw exception.
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new record.
     */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    /**
     * Update a record by ID.
     */
    public function update(int $id, array $attributes): bool
    {
        $record = $this->model->find($id);
        if (! $record) {
            return false;
        }

        return $record->update($attributes);
    }

    /**
     * Delete a record by ID.
     */
    public function delete(int $id): bool
    {
        $record = $this->model->find($id);
        if (! $record) {
            return false;
        }

        return $record->delete();
    }

    /**
     * Paginate records.
     */
    public function paginate(int $perPage = 25): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Get records by a specific column.
     */
    public function findBy(string $column, mixed $value): Collection
    {
        return $this->model->where($column, $value)->get();
    }

    /**
     * Get the first record matching the criteria.
     */
    public function findFirstBy(string $column, mixed $value): ?Model
    {
        return $this->model->where($column, $value)->first();
    }
}
