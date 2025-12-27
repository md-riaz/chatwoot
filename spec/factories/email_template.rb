FactoryBot.define do
  factory :email_template do
    sequence(:name) { |n| "Email Template #{n}" }
  end
end
