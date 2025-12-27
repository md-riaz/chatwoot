FactoryBot.define do
  factory :team do
    sequence(:name) { |n| "Team #{n}" }
    description { Faker::Lorem.sentence }
    allow_auto_assign { true }
    account
  end
end
