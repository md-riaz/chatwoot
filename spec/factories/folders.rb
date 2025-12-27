FactoryBot.define do
  factory :folder, class: 'Folder' do
    account_id { 1 }
    sequence(:name) { |n| "Folder #{n}" }
    description { Faker::Lorem.sentence }
    category_id { 1 }
  end
end
