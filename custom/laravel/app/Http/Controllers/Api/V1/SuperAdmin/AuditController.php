<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    /**
     * List audit logs with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Audit::with(['user:id,name,email']);

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by event type
        if ($request->has('event')) {
            $query->where('action', $request->input('event'));
        }

        // Filter by auditable type (model)
        if ($request->has('auditable_type')) {
            $query->where('auditable_type', $request->input('auditable_type'));
        }

        // Filter by auditable id
        if ($request->has('auditable_id')) {
            $query->where('auditable_id', $request->input('auditable_id'));
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->input('from_date'));
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->input('to_date'));
        }

        // Filter by IP address
        if ($request->has('ip_address')) {
            $query->where('remote_address', $request->input('ip_address'));
        }

        // Search in old/new values
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereJsonContains('audited_changes', $search)
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $audits = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

        // Add human-readable event names
        $audits->getCollection()->transform(function ($audit) {
            $audit->event_name = $this->getEventName($audit->action);
            $audit->model_name = $this->getModelName($audit->auditable_type);
            return $audit;
        });

        return response()->json($audits);
    }

    /**
     * Show specific audit log details.
     */
    public function show(Audit $audit): JsonResponse
    {
        $audit->load(['user:id,name,email,display_name']);
        
        $audit->event_name = $this->getEventName($audit->action);
        $audit->model_name = $this->getModelName($audit->auditable_type);

        // Add related audits for the same record
        $relatedAudits = Audit::where('auditable_type', $audit->auditable_type)
            ->where('auditable_id', $audit->auditable_id)
            ->where('id', '!=', $audit->id)
            ->with(['user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $audit->related_audits = $relatedAudits;

        return response()->json(['data' => $audit]);
    }

    /**
     * Get audit statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $dateFrom = $request->input('from_date', now()->subDays(30));
        $dateTo = $request->input('to_date', now());

        $stats = [
            'total_audits' => Audit::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'by_event' => $this->getEventStats($dateFrom, $dateTo),
            'by_model' => $this->getModelStats($dateFrom, $dateTo),
            'by_user' => $this->getUserStats($dateFrom, $dateTo),
            'by_date' => $this->getDateStats($dateFrom, $dateTo),
            'top_ip_addresses' => $this->getTopIpAddresses($dateFrom, $dateTo),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Export audit logs.
     */
    public function export(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'format' => 'in:csv,json',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'filters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        // Queue export job (to be implemented)
        // ExportAuditLogsJob::dispatch(auth()->user(), $request->all());

        return response()->json([
            'message' => 'Audit log export has been queued',
            'format' => $request->input('format', 'csv'),
        ]);
    }

    /**
     * Delete old audit logs.
     */
    public function cleanup(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'older_than_days' => 'required|integer|min:1',
            'confirm' => 'required|boolean|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        $olderThanDays = $request->input('older_than_days');
        $cutoffDate = now()->subDays($olderThanDays);

        $count = Audit::where('created_at', '<', $cutoffDate)->count();
        
        if ($count === 0) {
            return response()->json([
                'message' => 'No audit logs found older than ' . $olderThanDays . ' days',
                'deleted_count' => 0,
            ]);
        }

        // Delete in chunks to avoid memory issues
        $deleted = 0;
        Audit::where('created_at', '<', $cutoffDate)->chunkById(1000, function ($audits) use (&$deleted) {
            $audits->each->delete();
            $deleted += $audits->count();
        });

        return response()->json([
            'message' => "Deleted {$deleted} audit logs older than {$olderThanDays} days",
            'deleted_count' => $deleted,
            'cutoff_date' => $cutoffDate->toDateTimeString(),
        ]);
    }

    /**
     * Get event statistics.
     */
    private function getEventStats(string $dateFrom, string $dateTo): array
    {
        return Audit::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->pluck('count', 'action')
            ->toArray();
    }

    /**
     * Get model statistics.
     */
    private function getModelStats(string $dateFrom, string $dateTo): array
    {
        return Audit::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('auditable_type', DB::raw('count(*) as count'))
            ->groupBy('auditable_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'auditable_type')
            ->mapWithKeys(function ($count, $type) {
                return [$this->getModelName($type) => $count];
            })
            ->toArray();
    }

    /**
     * Get user statistics.
     */
    private function getUserStats(string $dateFrom, string $dateTo): array
    {
        return Audit::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('user_id')
            ->with('user:id,name,email')
            ->select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->mapWithKeys(function ($audit) {
                $userName = $audit->user ? $audit->user->name : 'Unknown User';
                return [$userName => $audit->count];
            })
            ->toArray();
    }

    /**
     * Get date-based statistics.
     */
    private function getDateStats(string $dateFrom, string $dateTo): array
    {
        return Audit::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Get top IP addresses.
     */
    private function getTopIpAddresses(string $dateFrom, string $dateTo): array
    {
        return Audit::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('remote_address')
            ->select('remote_address', DB::raw('count(*) as count'))
            ->groupBy('remote_address')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'remote_address')
            ->toArray();
    }

    /**
     * Get human-readable event name.
     */
    private function getEventName(string $event): string
    {
        return match ($event) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            default => ucfirst($event),
        };
    }

    /**
     * Get human-readable model name.
     */
    private function getModelName(?string $type): string
    {
        if (!$type) {
            return 'Unknown';
        }

        $className = class_basename($type);
        
        return match ($className) {
            'User' => 'User',
            'Account' => 'Account',
            'Conversation' => 'Conversation',
            'Message' => 'Message',
            'Contact' => 'Contact',
            'Inbox' => 'Inbox',
            'AgentBot' => 'Agent Bot',
            'InstallationConfig' => 'Installation Config',
            'PlatformApp' => 'Platform App',
            default => $className,
        };
    }
}