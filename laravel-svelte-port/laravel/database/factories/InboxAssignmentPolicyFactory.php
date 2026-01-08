<?php

namespace Database\Factories;

use App\Models\AssignmentPolicy;
use App\Models\Inbox;
use App\Models\InboxAssignmentPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InboxAssignmentPolicy>
 */
class InboxAssignmentPolicyFactory extends Factory
{
    protected $model = InboxAssignmentPolicy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inbox_id' => Inbox::factory(),
            'assignment_policy_id' => AssignmentPolicy::factory(),
        ];
    }
}