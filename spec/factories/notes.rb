# frozen_string_literal: true

FactoryBot.define do
  factory :note do
    content { Faker::Lorem.paragraph }
    account
    user
    contact
  end
end
