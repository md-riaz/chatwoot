<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CompaniesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_companies')->only(['store', 'update', 'destroy']);
    }
    public function index(Request $request, Account $account): JsonResource
    {
        $query = Company::where('account_id', $account->id);

        if ($request->has('q')) {
            $term = $request->get('q');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('domain', 'like', '%' . $term . '%')
                    ->orWhere('identifier', 'like', '%' . $term . '%');
            });
        }

        $companies = $query->paginate($request->get('per_page', 25));

        return CompanyResource::collection($companies);
    }

    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'nullable|string|max:255',
            'domain' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'custom_attributes' => 'nullable|array',
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
            'name' => 'sometimes|string|max:255',
            'identifier' => 'nullable|string|max:255',
            'domain' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'custom_attributes' => 'nullable|array',
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

    public function search(Request $request, Account $account): JsonResource
    {
        $query = Company::where('account_id', $account->id);

        if ($request->has('q')) {
            $searchTerm = $request->get('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('domain', 'like', '%' . $searchTerm . '%');
            });
        }

        $companies = $query->limit(10)->get();

        return CompanyResource::collection($companies);
    }
}
