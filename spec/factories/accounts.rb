# frozen_string_literal: true

FactoryBot.define do
  factory :account do
    sequence(:name) { |n| "Account #{n}" }
    status { 'active' }
    domain { Faker::Internet.domain_name }
    support_email { Faker::Internet.email }
  end
end
