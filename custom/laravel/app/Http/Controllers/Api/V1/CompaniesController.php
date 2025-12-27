<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompaniesController extends Controller
{
    /**
     * Display a listing of companies for an account.
     */
    public function index(Request $request, Account $account): JsonResource
    {
        $query = Company::where('account_id', $account->id);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $companies = $query->paginate($request->get('per_page', 15));

        return JsonResource::collection($companies);
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:companies,domain,NULL,id,account_id,' . $account->id,
            'description' => 'nullable|string',
        ]);

        $company = Company::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $company], 201);
    }

    /**
     * Display the specified company.
     */
    public function show(Account $account, Company $company): JsonResponse
    {
        abort_unless($company->account_id === $account->id, 404);

        return response()->json(['data' => $company->load('contacts')]);
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, Account $account, Company $company): JsonResponse
    {
        abort_unless($company->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'domain' => 'nullable|string|max:255|unique:companies,domain,' . $company->id . ',id,account_id,' . $account->id,
            'description' => 'nullable|string',
        ]);

        $company->update($validated);

        return response()->json(['data' => $company]);
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Account $account, Company $company): JsonResponse
    {
        abort_unless($company->account_id === $account->id, 404);

        $company->delete();

        return response()->json(null, 204);
    }

    /**
     * Search for companies.
     */
    public function search(Request $request, Account $account): JsonResource
    {
        $query = Company::where('account_id', $account->id);

        if ($request->has('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('domain', 'like', '%' . $searchTerm . '%');
            });
        }

        $companies = $query->limit(10)->get();

        return JsonResource::collection($companies);
    }
}
