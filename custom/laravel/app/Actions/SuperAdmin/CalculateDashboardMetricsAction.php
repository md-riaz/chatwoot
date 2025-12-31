<?php

namespace App\Actions\SuperAdmin;

use App\Data\SuperAdmin\DashboardData;
use App\Models\Account;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Contact;
use App\Models\Inbox;
use App\Models\AgentBot;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateDashboardMetricsAction
{
    use AsAction;

    public function handle(): DashboardData
    {
        // Basic counts
        $accounts_count = Account::count();
        $users_count = User::count();
        $conversations_count = Conversation::count();
        $messages_count = Message::count();
        $contacts_count = Contact::count();
        $inboxes_count = Inbox::count();
        $agent_bots_count = AgentBot::count();

        // Active accounts (accounts with activity in last 30 days)
        $active_accounts = Account::whereHas('conversations', function ($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        })->count();

        // Recent signups (users created in last 7 days)
        $recent_signups = User::where('created_at', '>=', now()->subDays(7))->count();

        // Account status breakdown
        $account_status = [
            'active' => Account::where('status', 1)->count(),
            'suspended' => Account::where('status', 0)->count(),
        ];

        // User role breakdown
        $user_roles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as count'))
            ->where('model_type', User::class)
            ->groupBy('roles.name')
            ->pluck('count', 'name')
            ->toArray();

        // Growth metrics
        $growth = $this->calculateGrowthMetrics();

        // System health
        $system_health = $this->getSystemHealth();

        // Recent activity
        $recent_activity = $this->getRecentActivity();

        return new DashboardData(
            accounts_count: $accounts_count,
            users_count: $users_count,
            conversations_count: $conversations_count,
            messages_count: $messages_count,
            contacts_count: $contacts_count,
            inboxes_count: $inboxes_count,
            agent_bots_count: $agent_bots_count,
            active_accounts: $active_accounts,
            recent_signups: $recent_signups,
            account_status: $account_status,
            user_roles: $user_roles,
            growth: $growth,
            system_health: $system_health,
            recent_activity: $recent_activity,
        );
    }

    private function calculateGrowthMetrics(): array
    {
        $currentPeriod = now()->subDays(30);
        $previousPeriod = now()->subDays(60);

        $currentAccounts = Account::where('created_at', '>=', $currentPeriod)->count();
        $previousAccounts = Account::whereBetween('created_at', [$previousPeriod, $currentPeriod])->count();

        $currentUsers = User::where('created_at', '>=', $currentPeriod)->count();
        $previousUsers = User::whereBetween('created_at', [$previousPeriod, $currentPeriod])->count();

        $currentConversations = Conversation::where('created_at', '>=', $currentPeriod)->count();
        $previousConversations = Conversation::whereBetween('created_at', [$previousPeriod, $currentPeriod])->count();

        return [
            'accounts' => [
                'current' => $currentAccounts,
                'previous' => $previousAccounts,
                'growth_rate' => $previousAccounts > 0 ? round((($currentAccounts - $previousAccounts) / $previousAccounts) * 100, 2) : 0,
            ],
            'users' => [
                'current' => $currentUsers,
                'previous' => $previousUsers,
                'growth_rate' => $previousUsers > 0 ? round((($currentUsers - $previousUsers) / $previousUsers) * 100, 2) : 0,
            ],
            'conversations' => [
                'current' => $currentConversations,
                'previous' => $previousConversations,
                'growth_rate' => $previousConversations > 0 ? round((($currentConversations - $previousConversations) / $previousConversations) * 100, 2) : 0,
            ],
        ];
    }

    private function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'redis' => $this->checkRedisHealth(),
            'storage' => $this->checkStorageHealth(),
            'queues' => $this->checkQueueHealth(),
        ];
    }

    private function checkDatabaseHealth(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    private function checkRedisHealth(): array
    {
        try {
            \Cache::store('redis')->put('health_check', 'ok', 10);
            $result = \Cache::store('redis')->get('health_check');
            
            if ($result === 'ok') {
                return ['status' => 'healthy', 'message' => 'Redis connection successful'];
            }
            
            return ['status' => 'unhealthy', 'message' => 'Redis health check failed'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'Redis connection failed: ' . $e->getMessage()];
        }
    }

    private function checkStorageHealth(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            \Storage::put($testFile, 'health check');
            
            if (\Storage::exists($testFile)) {
                \Storage::delete($testFile);
                return ['status' => 'healthy', 'message' => 'Storage is accessible'];
            }
            
            return ['status' => 'unhealthy', 'message' => 'Storage write test failed'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'Storage check failed: ' . $e->getMessage()];
        }
    }

    private function checkQueueHealth(): array
    {
        try {
            $driver = config('queue.default');
            return ['status' => 'healthy', 'message' => "Queue driver '{$driver}' is configured"];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'Queue check failed: ' . $e->getMessage()];
        }
    }

    private function getRecentActivity(): array
    {
        return [
            'recent_accounts' => Account::latest()->limit(5)->get(['id', 'name', 'created_at']),
            'recent_users' => User::latest()->limit(5)->get(['id', 'name', 'email', 'created_at']),
            'recent_conversations' => Conversation::with(['account:id,name', 'contact:id,name'])
                ->latest()
                ->limit(5)
                ->get(['id', 'account_id', 'contact_id', 'status', 'created_at']),
        ];
    }
}