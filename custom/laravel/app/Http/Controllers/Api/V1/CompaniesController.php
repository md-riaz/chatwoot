<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class CompaniesController extends Controller
{
    const RESULTS_PER_PAGE = 25;

    public function __construct()
    {
        $this->middleware('permission:manage_companies')->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request, Account $account): JsonResource
    {
        $query = $this->resolvedCompanies($account);

        // Apply sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

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
                $query->orderedByName();
        }

        $companies = $query->paginate($request->get('per_page', self::RESULTS_PER_PAGE));

        return CompanyResource::collection($companies)->additional([
            'meta' => [
                'companies_count' => $companies->total()
            ]
        ]);
    }

    public function search(Request $request, Account $account): JsonResource
    {
        if (empty($request->get('q'))) {
            throw ValidationException::withMessages([
                'q' => ['Search query is required']
            ]);
        }

        $query = $this->resolvedCompanies($account)
            ->searchByNameOrDomain($request->get('q'));

        $companies = $query->paginate($request->get('per_page', self::RESULTS_PER_PAGE));

        return CompanyResource::collection($companies)->additional([
            'meta' => [
                'companies_count' => $companies->total()
            ]
        ]);
    }

    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => [
                'nullable',
                'string',
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?)+$/',
                Rule::unique('companies', 'domain')->where('account_id', $account->id)->whereNotNull('domain')
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        $company = Company::create(array_merge($validated, ['account_id' => $account->id]));

        return (new CompanyResource($company))->response()->setStatusCode(201);
    }

    public function show(Account $account, Company $company): JsonResponse
    {
        abort_unless($company->account_id === $account->id, 404);

        return new CompanyResource($company->load('contacts'));
    }

    public function update(Request $request, Account $account, Company $company): JsonResponse
    {
        abort_unless($company->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'domain' => [
                'nullable',
                'string',
                'regex:/^[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?)+$/',
                Rule::unique('companies', 'domain')
                    ->where('account_id', $account->id)
                    ->ignore($company->id)
                    ->whereNotNull('domain')
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        $company->update($validated);

        return new CompanyResource($company);
    }

    public function destroy(Account $account, Company $company): JsonResponse
    {
        abort_unless($company->account_id === $account->id, 404);

        $company->delete();

        return response()->json(null, 204);
    }

    private function resolvedCompanies(Account $account)
    {
        return Company::where('account_id', $account->id);
    }
}
