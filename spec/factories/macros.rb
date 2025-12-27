FactoryBot.define do
  factory :macro do
    account
    sequence(:name) { |n| "Macro #{n}" }
    actions do
      [
        { 'action_name' => 'add_label', 'action_params' => %w[wrong_chat] }
      ]
    end
  end
end
