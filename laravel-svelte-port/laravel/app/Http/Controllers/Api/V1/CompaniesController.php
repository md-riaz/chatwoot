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

use App\Repositories\Company\CompanyRepository;

class CompaniesController extends Controller
{
    const RESULTS_PER_PAGE = 25;

    protected $companyRepository;

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->middleware('permission:manage_companies')->only(['store', 'update', 'destroy']);
        $this->companyRepository = $companyRepository;
    }

    public function index(Request $request, Account $account): JsonResource
    {
        $query = $this->companyRepository->getCompaniesForAccount($account->id, [
            'sort_by' => $request->get('sort_by'),
            'sort_order' => $request->get('sort_order')
        ]);

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

        $query = $this->companyRepository->search($account->id, $request->get('q'));

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
            'avatar_url' => 'nullable|url',
        ]);

        $company = $this->companyRepository->create($validated, $account->id);

        return (new CompanyResource($company))->response()->setStatusCode(201);
    }

    public function show(Account $account, Company $company): CompanyResource
    {
        abort_unless($company->account_id === $account->id, 404);

        return new CompanyResource($company->load('contacts'));
    }

    public function update(Request $request, Account $account, Company $company): CompanyResource
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
            'avatar_url' => 'nullable|url',
        ]);

        $company = $this->companyRepository->update($company, $validated);

        return new CompanyResource($company);
    }

    public function destroy(Account $account, Company $company): JsonResponse
    {
        abort_unless($company->account_id === $account->id, 404);

        $this->companyRepository->delete($company);

        return response()->json(null, 204);
    }
}
