FactoryBot.define do
  factory :agent_bot do
    sequence(:name) { |n| "AgentBot #{n}" }
    description { Faker::Lorem.sentence }
    outgoing_url { Faker::Internet.url }
    bot_config { {} }
    bot_type { 'webhook' }

    trait :skip_validate do
      to_create { |instance| instance.save(validate: false) }
    end

    trait :with_avatar do
      avatar { fixture_file_upload(Rails.root.join('spec/assets/avatar.png'), 'image/png') }
    end
  end
end
