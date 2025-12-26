<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CustomAttributeDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomAttributeDefinitionsController extends Controller
{
    /**
     * Display a listing of custom attribute definitions for an account.
     */
    public function index(Account $account, Request $request): JsonResource
    {
        $query = CustomAttributeDefinition::where('account_id', $account->id);

        if ($request->has('attribute_model')) {
            $query->where('attribute_model', $request->attribute_model);
        }

        return JsonResource::collection($query->paginate());
    }

    /**
     * Store a newly created custom attribute definition.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'attribute_display_name' => 'required|string|max:255',
            'attribute_key' => 'required|string|max:255',
            'attribute_display_type' => 'required|string|in:text,number,currency,percent,link,date,list,checkbox',
            'attribute_model' => 'required|string|in:contact_attribute,conversation_attribute',
            'attribute_description' => 'nullable|string',
            'default_value' => 'nullable|string',
            'attribute_values' => 'nullable|array',
            'regex_pattern' => 'nullable|string',
            'regex_cue' => 'nullable|string',
        ]);

        $definition = CustomAttributeDefinition::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $definition], 201);
    }

    /**
     * Display the specified custom attribute definition.
     */
    public function show(Account $account, CustomAttributeDefinition $customAttributeDefinition): JsonResponse
    {
        abort_unless($customAttributeDefinition->account_id === $account->id, 404);

        return response()->json(['data' => $customAttributeDefinition]);
    }

    /**
     * Update the specified custom attribute definition.
     */
    public function update(Request $request, Account $account, CustomAttributeDefinition $customAttributeDefinition): JsonResponse
    {
        abort_unless($customAttributeDefinition->account_id === $account->id, 404);

        $validated = $request->validate([
            'attribute_display_name' => 'string|max:255',
            'attribute_key' => 'string|max:255',
            'attribute_display_type' => 'string|in:text,number,currency,percent,link,date,list,checkbox',
            'attribute_model' => 'string|in:contact_attribute,conversation_attribute',
            'attribute_description' => 'nullable|string',
            'default_value' => 'nullable|string',
            'attribute_values' => 'nullable|array',
            'regex_pattern' => 'nullable|string',
            'regex_cue' => 'nullable|string',
        ]);

        $customAttributeDefinition->update($validated);

        return response()->json(['data' => $customAttributeDefinition]);
    }

    /**
     * Remove the specified custom attribute definition.
     */
    public function destroy(Account $account, CustomAttributeDefinition $customAttributeDefinition): JsonResponse
    {
        abort_unless($customAttributeDefinition->account_id === $account->id, 404);

        $customAttributeDefinition->delete();

        return response()->json(null, 204);
    }
}
