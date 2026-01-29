<?php

namespace App\Repositories\Company;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

class CompanyRepository
{
    /**
     * @var Company
     */
    protected $company;

    /**
     * CompanyRepository constructor.
     *
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Get companies for account
     *
     * @param int $accountId
     * @param array $sort
     * @return Builder
     */
    public function getCompaniesForAccount(int $accountId, array $sort = [])
    {
        $query = $this->company->where('account_id', $accountId);

        $sortBy = $sort['sort_by'] ?? 'name';
        $sortOrder = $sort['sort_order'] ?? 'asc';

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'domain':
                $query->orderBy('domain', $sortOrder);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortOrder);
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        return $query;
    }

    /**
     * Create a new company
     *
     * @param array $data
     * @param int $accountId
     * @return Company
     */
    public function create(array $data, int $accountId)
    {
        return $this->company->create(array_merge($data, ['account_id' => $accountId]));
    }

    /**
     * Find company by ID
     *
     * @param int $id
     * @return Company
     */
    public function find(int $id)
    {
        return $this->company->find($id);
    }

    /**
     * Search companies
     *
     * @param int $accountId
     * @param string $search
     * @return Builder
     */
    public function search(int $accountId, string $search)
    {
        return $this->company->where('account_id', $accountId)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('domain', 'like', '%' . $search . '%');
            });
    }

    /**
     * Update company
     *
     * @param Company $company
     * @param array $data
     * @return Company
     */
    public function update(Company $company, array $data)
    {
        $company->update($data);
        return $company;
    }

    /**
     * Delete company
     *
     * @param Company $company
     * @return bool|null
     */
    public function delete(Company $company)
    {
        return $company->delete();
    }
}
