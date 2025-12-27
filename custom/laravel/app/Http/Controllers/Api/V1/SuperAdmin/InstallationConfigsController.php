<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\InstallationConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstallationConfigsController extends Controller
{
    /**
     * List all installation configs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = InstallationConfig::query();

        if ($request->boolean('editable_only')) {
            $query->editable();
        }

        $configs = $query->orderBy('name')
            ->get()
            ->mapWithKeys(function ($config) {
                return [$config->name => $config->value];
            });

        return response()->json(['data' => $configs]);
    }

    /**
     * Show config by group.
     */
    public function showByGroup(Request $request, string $group): JsonResponse
    {
        $groups = InstallationConfig::getConfigGroups();

        if (! isset($groups[$group])) {
            return response()->json([
                'error' => 'Invalid config group.',
                'valid_groups' => array_keys($groups),
            ], 400);
        }

        $allowedConfigs = $groups[$group];

        $configs = InstallationConfig::whereIn('name', $allowedConfigs)
            ->get()
            ->mapWithKeys(function ($config) {
                return [$config->name => $config->value];
            });

        return response()->json([
            'data' => $configs,
            'group' => $group,
            'allowed_configs' => $allowedConfigs,
        ]);
    }

    /**
     * Show a single config.
     */
    public function show(InstallationConfig $installationConfig): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $installationConfig->id,
                'name' => $installationConfig->name,
                'value' => $installationConfig->value,
                'locked' => $installationConfig->locked,
                'created_at' => $installationConfig->created_at,
                'updated_at' => $installationConfig->updated_at,
            ],
        ]);
    }

    /**
     * Create or update configs.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'configs' => 'required|array',
            'configs.*.name' => 'required|string',
            'configs.*.value' => 'required',
        ]);

        $updated = [];

        foreach ($validated['configs'] as $config) {
            $installConfig = InstallationConfig::setConfig(
                $config['name'],
                $config['value'],
                false
            );
            $updated[] = $installConfig->name;
        }

        return response()->json([
            'message' => 'Configs updated successfully.',
            'updated' => $updated,
        ]);
    }

    /**
     * Update a single config.
     */
    public function update(Request $request, InstallationConfig $installationConfig): JsonResponse
    {
        if ($installationConfig->locked) {
            return response()->json([
                'error' => 'This config is locked and cannot be modified.',
            ], 403);
        }

        $validated = $request->validate([
            'value' => 'required',
        ]);

        $installationConfig->value = $validated['value'];
        $installationConfig->save();

        return response()->json([
            'data' => [
                'id' => $installationConfig->id,
                'name' => $installationConfig->name,
                'value' => $installationConfig->value,
            ],
        ]);
    }

    /**
     * Delete a config.
     */
    public function destroy(InstallationConfig $installationConfig): JsonResponse
    {
        if ($installationConfig->locked) {
            return response()->json([
                'error' => 'This config is locked and cannot be deleted.',
            ], 403);
        }

        $installationConfig->delete();

        return response()->json(null, 204);
    }

    /**
     * Get available config groups.
     */
    public function groups(): JsonResponse
    {
        return response()->json([
            'data' => array_keys(InstallationConfig::getConfigGroups()),
        ]);
    }
}
