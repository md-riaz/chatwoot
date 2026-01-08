<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\AssignmentPolicy;
use App\Models\Inbox;
use App\Models\InboxAssignmentPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentPoliciesControllerTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;
    private User $admin;
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->admin = User::factory()->create();
        $this->agent = User::factory()->create();

        // Attach users to account with appropriate roles
        $this->account->users()->attach($this->admin->id, ['role' => 1]); // administrator
        $this->account->users()->attach($this->agent->id, ['role' => 0]); // agent
    }

    public function test_index_returns_unauthorized_for_unauthenticated_user()
    {
        $response = $this->getJson("/api/v1/accounts/{$this->account->id}/assignment_policies");

        $response->assertStatus(401);
    }

    public function test_index_returns_assignment_policies_for_admin()
    {
        $policy1 = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);
        $policy2 = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/accounts/{$this->account->id}/assignment_policies");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $policy1->id])
            ->assertJsonFragment(['id' => $policy2->id]);
    }

    public function test_show_returns_assignment_policy_for_admin()
    {
        $policy = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/accounts/{$this->account->id}/assignment_policies/{$policy->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $policy->id,
                'name' => $policy->name,
                'account_id' => $this->account->id,
            ]);
    }

    public function test_show_returns_404_for_non_existent_policy()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/accounts/{$this->account->id}/assignment_policies/999999");

        $response->assertStatus(404);
    }

    public function test_store_creates_assignment_policy_for_admin()
    {
        $data = [
            'name' => 'Test Policy',
            'description' => 'Test Description',
            'assignment_order' => 0,
            'conversation_priority' => 1,
            'fair_distribution_limit' => 10,
            'fair_distribution_window' => 7200,
            'enabled' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/accounts/{$this->account->id}/assignment_policies", $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Test Policy',
                'description' => 'Test Description',
                'account_id' => $this->account->id,
            ]);

        $this->assertDatabaseHas('assignment_policies', [
            'name' => 'Test Policy',
            'account_id' => $this->account->id,
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/accounts/{$this->account->id}/assignment_policies", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_unique_name_per_account()
    {
        AssignmentPolicy::factory()->create([
            'account_id' => $this->account->id,
            'name' => 'Existing Policy',
        ]);

        $data = [
            'name' => 'Existing Policy',
            'assignment_order' => 0,
            'conversation_priority' => 0,
            'fair_distribution_limit' => 10,
            'fair_distribution_window' => 3600,
            'enabled' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/accounts/{$this->account->id}/assignment_policies", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_modifies_assignment_policy_for_admin()
    {
        $policy = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);

        $data = [
            'name' => 'Updated Policy',
            'description' => 'Updated Description',
            'enabled' => false,
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/accounts/{$this->account->id}/assignment_policies/{$policy->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Policy',
                'description' => 'Updated Description',
                'enabled' => false,
            ]);

        $this->assertDatabaseHas('assignment_policies', [
            'id' => $policy->id,
            'name' => 'Updated Policy',
            'enabled' => false,
        ]);
    }

    public function test_destroy_deletes_assignment_policy_for_admin()
    {
        $policy = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/accounts/{$this->account->id}/assignment_policies/{$policy->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('assignment_policies', ['id' => $policy->id]);
    }

    public function test_inboxes_returns_associated_inboxes()
    {
        $policy = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);
        $inbox1 = Inbox::factory()->create(['account_id' => $this->account->id]);
        $inbox2 = Inbox::factory()->create(['account_id' => $this->account->id]);

        InboxAssignmentPolicy::factory()->create([
            'inbox_id' => $inbox1->id,
            'assignment_policy_id' => $policy->id,
        ]);

        InboxAssignmentPolicy::factory()->create([
            'inbox_id' => $inbox2->id,
            'assignment_policy_id' => $policy->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/accounts/{$this->account->id}/assignment_policies/{$policy->id}/inboxes");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $inbox1->id])
            ->assertJsonFragment(['id' => $inbox2->id]);
    }

    public function test_add_inbox_associates_inbox_with_policy()
    {
        $policy = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);
        $inbox = Inbox::factory()->create(['account_id' => $this->account->id]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/accounts/{$this->account->id}/assignment_policies/{$policy->id}/inboxes", [
                'inbox_id' => $inbox->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Inbox associated successfully']);

        $this->assertDatabaseHas('inbox_assignment_policies', [
            'inbox_id' => $inbox->id,
            'assignment_policy_id' => $policy->id,
        ]);
    }

    public function test_add_inbox_removes_existing_assignment_policy_for_inbox()
    {
        $policy1 = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);
        $policy2 = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);
        $inbox = Inbox::factory()->create(['account_id' => $this->account->id]);

        // Associate inbox with policy1 first
        InboxAssignmentPolicy::factory()->create([
            'inbox_id' => $inbox->id,
            'assignment_policy_id' => $policy1->id,
        ]);

        // Now associate with policy2
        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/accounts/{$this->account->id}/assignment_policies/{$policy2->id}/inboxes", [
                'inbox_id' => $inbox->id,
            ]);

        $response->assertStatus(201);

        // Should only have association with policy2
        $this->assertDatabaseMissing('inbox_assignment_policies', [
            'inbox_id' => $inbox->id,
            'assignment_policy_id' => $policy1->id,
        ]);

        $this->assertDatabaseHas('inbox_assignment_policies', [
            'inbox_id' => $inbox->id,
            'assignment_policy_id' => $policy2->id,
        ]);
    }

    public function test_remove_inbox_dissociates_inbox_from_policy()
    {
        $policy = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);
        $inbox = Inbox::factory()->create(['account_id' => $this->account->id]);

        InboxAssignmentPolicy::factory()->create([
            'inbox_id' => $inbox->id,
            'assignment_policy_id' => $policy->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/accounts/{$this->account->id}/assignment_policies/{$policy->id}/inboxes", [
                'inbox_id' => $inbox->id,
            ]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('inbox_assignment_policies', [
            'inbox_id' => $inbox->id,
            'assignment_policy_id' => $policy->id,
        ]);
    }

    public function test_agent_cannot_access_assignment_policies()
    {
        $policy = AssignmentPolicy::factory()->create(['account_id' => $this->account->id]);

        $response = $this->actingAs($this->agent)
            ->getJson("/api/v1/accounts/{$this->account->id}/assignment_policies");

        $response->assertStatus(403);

        $response = $this->actingAs($this->agent)
            ->getJson("/api/v1/accounts/{$this->account->id}/assignment_policies/{$policy->id}");

        $response->assertStatus(403);
    }
}