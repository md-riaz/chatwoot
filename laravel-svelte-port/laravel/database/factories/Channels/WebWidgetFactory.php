<?php

namespace Database\Factories\Channels;

use App\Models\Account;
use App\Models\Channels\WebWidget;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebWidgetFactory extends Factory
{
    protected $model = WebWidget::class;

    public function definition(): array
    {
        return [
            'website_url' => $this->faker->url(),
            'website_token' => $this->faker->uuid(),
            'widget_color' => $this->faker->hexColor(),
            'welcome_title' => $this->faker->sentence(3),
            'welcome_tagline' => $this->faker->sentence(6),
            'feature_flags' => $this->faker->boolean(),
            'pre_chat_form_options' => [
                'pre_chat_message' => $this->faker->sentence(),
                'require_email' => $this->faker->boolean(),
            ],
            'pre_chat_form_enabled' => $this->faker->boolean(),
        ];
    }

    public function withPreChatForm(): static
    {
        return $this->state(fn (array $attributes) => [
            'pre_chat_form_enabled' => true,
            'pre_chat_form_options' => [
                'pre_chat_message' => 'Please provide your details',
                'require_email' => true,
            ],
        ]);
    }

    public function withoutPreChatForm(): static
    {
        return $this->state(fn (array $attributes) => [
            'pre_chat_form_enabled' => false,
            'pre_chat_form_options' => [],
        ]);
    }
}